# Cities importer plugin (OOP)

A custom WordPress plugin for fetching and importing European capitals. The plugin fetches data from the REST Countries API and creates or updates a `city_pt` post for each capital.

This is an object-oriented rewrite of an originally procedural plugin. The procedural version remains available at [jeroen-verhoeven/cities-importer](https://github.com/jeroen-verhoeven/cities-importer).

Notes: this is a demo only. It has a dependency on ACF for storing meta data.

## 1. Purpose

This plugin is for demo purposes only. It is used in combination with a custom ACF block, using MapBox to show the location of each city on the map.

## 2. See the imported cities

You can see the results of the imported cities on: https://www.jeroen-verhoeven.com/map-block/

## 3. Requirements

- PHP 8.1 or higher
- SSH access to WordPress installation
- WP CLI
- ACF
- A REST Countries API key, defined as `RESTCOUNTRIES_API_KEY` in `wp-config.php`

## 4. Usage

Enable an SSH connection and navigate to the WordPress root folder. Use this command to start the import process:

`wp import:cities`