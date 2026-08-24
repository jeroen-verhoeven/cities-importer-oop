<?php

/*
 * Plugin Name:       Cities importer OOP
 * Description:       Gets and imports European cities
 * Author:            Jeroen Verhoeven
 */

if(!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'inc/city-post-type.php';
require_once plugin_dir_path(__FILE__) . 'inc/rest-countries-api.php';
require_once plugin_dir_path(__FILE__) . 'inc/capitals-importer.php';
require_once plugin_dir_path(__FILE__) . 'inc/cities-summary.php';

//Register WP-CLI command
if(defined('WP_CLI') && WP_CLI){
    WP_CLI::add_command('import:cities-oop', function($args, $assoc_args) {

        $with_summaries = isset($assoc_args['summary']);

        WP_CLI::log(
            WP_CLI::colorize(
                "%B\n" .
                "  ========================================\n" .
                "          CITIES IMPORTER - OOP\n" .
                "  ========================================\n" .
                "%n"
            )
        );

        import_european_capitals();

        WP_CLI::success("Capitals import complete.");

        if ($with_summaries) {
            WP_CLI::log("Starting city summaries generation...");
            generate_summaries_for_cities(false);
        }
    });
}