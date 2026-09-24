<?php

class RestCountriesProvider implements CapitalProvider{

    public function __construct(
        private string $apiKey
    ){

    }

    public function fetchCapitals() : array{
        $url = 'https://api.restcountries.com/countries/v5?q=europe&limit=100';

        if (!$this->apiKey) {
            WP_CLI::warning('RESTCOUNTRIES_API_KEY is not defined.');
            return [];
        }

        WP_CLI::log("Fetching European countries from REST API...");

        $response = wp_remote_get($url, [
            'headers'   => [
                'Authorization' => 'Bearer ' . $this->apiKey,
            ],
        ]);

        if (is_wp_error($response)) {
            WP_CLI::warning("REST API request failed: " . $response->get_error_message());
            return [];
        }

        $status = wp_remote_retrieve_response_code($response);
        if ($status !== 200) {
            WP_CLI::warning("REST API returned status code {$status}");
            return [];
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!is_array($data) || !isset($data['data']['objects'])) {
            WP_CLI::warning("Failed to decode REST API response or 'objects' array is missing.");
            return [];
        }

        $countries = $data['data']['objects'];

        if (!is_array($countries)) {
            WP_CLI::warning("Failed to decode REST API response to array.");
            return [];
        }

        WP_CLI::log("Successfully retrieved " . count($countries) . " countries.");
        return $countries;
    }
}