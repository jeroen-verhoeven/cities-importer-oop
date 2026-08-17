<?php

/**
 * Generate city summaries using OpenAI GPT
 * @package CitiesImporter
 */

if (!defined('ABSPATH')) exit; // Security check

/**
 * Generate a city summary using OpenAI API.
 * @param string $capital Name of the capital city.
 * @param string|null $population Population, formatted or null.
 * @param string|null $currency Currency name, or null.
 * @param string|null $country_name Country name, or null.
 * @return string|false Generated summary, or false if generation failed.
 */
function generate_city_summary(string $capital, ?string $population = null, ?string $currency = null, string $country_name) {
    $api_key = defined('OPENAI_API_KEY') ? OPENAI_API_KEY : null;

    if (!$api_key) {
        WP_CLI::warning("OpenAI API key not defined. Skipping summary for {$capital}.");
        return false;
    }

    // Fallback for empty values
    $capital = $capital ?: 'Unknown city';

    // Remove HTML/special characters
    $capital = wp_strip_all_tags($capital);

    $prompt = "Write a short 2-3 sentence summary about the city {$capital} in {$country_name}, Europe.";
    $prompt .= " Keep it concise, informative, and friendly.";

    WP_CLI::log("Prompt for {$capital}: {$prompt}");

    $request_body = [
        'model' => 'gpt-5.2',
        'messages' => [
            ['role' => 'system', 'content' => 'You are a helpful assistant.'],
            ['role' => 'user', 'content' => $prompt],
        ],
        'temperature' => 0.7,
        'max_completion_tokens' => 1500,
    ];

    $max_retries = 3;
    $retry_count = 0;

    do {
        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
            ],
            'body' => wp_json_encode($request_body),
            'timeout' => 20,
        ]);

        $code = wp_remote_retrieve_response_code($response);

        if (is_wp_error($response)) {
            WP_CLI::warning("OpenAI request failed for {$capital}: " . $response->get_error_message());
            return false;
        }

        if ($code === 429) {
            $retry_count++;
            $wait = pow(2, $retry_count);
            WP_CLI::log("Rate limited (429) for {$capital}, retry {$retry_count} in {$wait}s...");
            sleep($wait);
        } elseif ($code !== 200) {
            WP_CLI::warning("OpenAI API returned HTTP {$code} for {$capital}: " . wp_remote_retrieve_body($response));
            return false;
        } else {
            break;
        }
    } while ($retry_count < $max_retries);

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (!isset($data['choices'][0]['message']['content'])) {
        WP_CLI::warning("No content returned from OpenAI for {$capital}");
        return false;
    }

    $summary = trim($data['choices'][0]['message']['content']);

    WP_CLI::log("Generated summary for {$capital}");

    // Throttle to avoid rate limits
    sleep(1);

    return $summary;
}

/**
 * Generate summaries for all cities if they don't exist yet.
 * @param bool $force If true, regenerate summaries even if they exist.
 * @return void
 */
function generate_summaries_for_cities(bool $force = false) {
    $query = new WP_Query([
        'post_type'      => 'city_pt',
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ]);

    if (empty($query->posts)) {
        WP_CLI::log("No cities found for summary generation.");
        return;
    }

    $total = count($query->posts);
    $counter = 1;

    foreach ($query->posts as $post_id) {
        $country_name = get_field('country', $post_id);
        $capital = get_the_title($post_id);
        $population = get_field('population', $post_id) ?? null;
        $currency = get_field('currency', $post_id) ?? null;
        $existing_summary = get_field('summary', $post_id);

        if ($existing_summary && !$force) {
            WP_CLI::log("[{$counter}/{$total}] Summary already exists for {$capital}, skipping.");
            $counter++;
            continue;
        }

        $summary = generate_city_summary($capital, $population, $currency, $country_name);

        if ($summary) {
            update_field('summary', $summary, $post_id);
            WP_CLI::log("[{$counter}/{$total}] Summary saved for {$capital}");
        } else {
            WP_CLI::warning("[{$counter}/{$total}] Failed to generate summary for {$capital}");
        }

        $counter++;
    }

    WP_CLI::success("City summaries generation complete.");
}