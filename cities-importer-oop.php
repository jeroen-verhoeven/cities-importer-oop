<?php

/*
 * Plugin Name:       Cities importer (OOP)
 * Description:       Gets and imports European cities
 * Author:            Jeroen Verhoeven
 */

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'src/CapitalData.php';
require_once plugin_dir_path(__FILE__) . 'src/CapitalProvider.php';
require_once plugin_dir_path(__FILE__) . 'src/RestCountriesProvider.php';
require_once plugin_dir_path(__FILE__) . 'src/CityRepository.php';
require_once plugin_dir_path(__FILE__) . 'src/CapitalsImporter.php';
require_once plugin_dir_path(__FILE__) . 'src/CityPostType.php';

// Post type registreren
$cityPostType = new CityPostType();
add_action('init', [$cityPostType, 'registerPostType']);

// WP-CLI command
if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('import:cities', function ($args, $assoc_args) {
        $apiKey = defined('RESTCOUNTRIES_API_KEY') ? RESTCOUNTRIES_API_KEY : '';

        $provider = new RestCountriesProvider($apiKey);
        $cities = new CityRepository();
        $importer = new CapitalsImporter($provider, $cities);

        $count = $importer->run();

        WP_CLI::success("Capitals import complete. {$count} cities imported/updated.");
    });
}