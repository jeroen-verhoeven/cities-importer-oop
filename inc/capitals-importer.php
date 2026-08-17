<?php

/**
 * Format population with thousands separator.
 * @param int|null $population Number of inhabitants.
 * @return string|null Formatted population with thousands separator, or null if input is null.
 */
function format_population(?int $population): ?string {
    if ($population === null) {
        WP_CLI::log('Population is null, skipping formatting.');
        return null;
    }
    return number_format($population, 0, ',', '.');
}

/**
 * Get a post by meta key/value.
 * @param string $meta_key The meta key to search for.
 * @param mixed $meta_value The meta value to match.
 * @param string $post_type Post type to search in. Default 'city_pt'.
 * @return int|false Post ID if found, false otherwise.
 */
function get_post_by_meta(string $meta_key, $meta_value, string $post_type = 'city_pt') {
    $query = new WP_Query([
        'post_type'      => $post_type,
        'meta_key'       => $meta_key,
        'meta_value'     => $meta_value,
        'fields'         => 'ids',
        'posts_per_page' => 1,
    ]);

    $post_id = $query->posts[0] ?? false;

    if ($post_id) {
        WP_CLI::log("Found existing post with {$meta_key}={$meta_value}, post ID: {$post_id}");
    }

    return $post_id;
}

/**
 * Create or update a city post.
 * @param string $capital Name of the capital.
 * @param string $cca2 Two-letter country code.
 * @return int|false Post ID if created or updated, false on failure.
 */
function create_or_update_city(string $capital, string $cca2) {
    $post_id = get_post_by_meta('cca2', $cca2, 'city_pt');

    if (!$post_id) {
        // Create new post
        $post_id = wp_insert_post([
            'post_title'  => $capital,
            'post_type'   => 'city_pt',
            'post_status' => 'publish',
        ]);

        if (!$post_id) {
            WP_CLI::warning("Failed to create post for capital {$capital}");
            return false;
        }

        update_post_meta($post_id, 'cca2', $cca2);
        WP_CLI::log("Created new post for capital {$capital}, post ID: {$post_id}");
    } else {
        // Update existing post title
        wp_update_post([
            'ID'         => $post_id,
            'post_title' => $capital,
        ]);
        WP_CLI::log("Updated existing post title for capital {$capital}, post ID: {$post_id}");
    }

    return $post_id;
}

/**
 * Set a featured image from URL for a post.
 * @param int $post_id Post ID.
 * @param string $image_url Image URL.
 * @param string|null $filename Optional filename for the image.
 * @return void
 */
function set_featured_image_from_url(int $post_id, string $image_url, ?string $filename = null) {
    if (empty($image_url)) {
        WP_CLI::log("No image URL provided for post ID {$post_id}, skipping featured image.");
        return;
    }

    if (!function_exists('media_sideload_image')) {
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
    }

    add_filter('wp_handle_sideload_prefilter', function ($file) use ($filename) {
        if ($filename) {
            $file['name'] = $filename;
        }
        return $file;
    });

    $attachment_id = media_sideload_image($image_url, $post_id, null, 'id');

    remove_filter('wp_handle_sideload_prefilter', '__return_null');

    if (is_wp_error($attachment_id)) {
        WP_CLI::warning("Failed to set featured image for post ID {$post_id}: " . $attachment_id->get_error_message());
    } else {
        set_post_thumbnail($post_id, $attachment_id);
        WP_CLI::log("Featured image set for post ID {$post_id}, attachment ID: {$attachment_id}");
    }
}

/**
 * Import European capitals into WordPress.
 * Loops through REST Countries API, creates or updates city posts,
 * updates ACF fields, sets featured images, and logs all actions.
 *
 * @return void
 */
function import_european_capitals() {
    $countries = get_european_countries_from_api();

    if (empty($countries)) {
        WP_CLI::warning('No countries retrieved from API, import aborted.');
        return;
    }

    $total = count($countries);
    $counter = 1;

    foreach ($countries as $country) {
        $capital = $country['capitals'][0]['name'] ?? null;
        if (!$capital) {
            WP_CLI::log("[{$counter}/{$total}] No capital found for country " . ($country['name']['common'] ?? 'Unknown') . ", skipping.");
            $counter++;
            continue;
        }

        $country_name = $country['names']['common'] ?? '';
        $lat = $country['capitals'][0]['coordinates']['lat'] ?? null;
        $lng = $country['capitals'][0]['coordinates']['lng'] ?? null;
        $population = format_population($country['population'] ?? null);

        $currency = '';
        if (!empty($country['currencies'])) {
            $currency = ucfirst($country['currencies'][0]['name'] ?? '');
        }

        $cca2 = $country['codes']['alpha_2']?? '';

        $post_id = create_or_update_city($capital, $cca2);
        if (!$post_id) {
            WP_CLI::log("[{$counter}/{$total}] Skipping capital {$capital} due to post creation failure.");
            $counter++;
            continue;
        }

        update_field('country', $country_name, $post_id);
        update_field('latitude', $lat, $post_id);
        update_field('longitude', $lng, $post_id);
        update_field('population', $population, $post_id);
        update_field('currency', $currency, $post_id);

        WP_CLI::success("[{$counter}/{$total}] Imported/Updated capital {$capital}, post ID: {$post_id}");

        $flag_url = $country['flag']['url_png'] ?? '';
        if ($flag_url) {
            $filename = strtolower($cca2) . '.png';
            set_featured_image_from_url($post_id, $flag_url, $filename);
        }

        $counter++;
    }

    WP_CLI::success('European capitals import complete.');
}