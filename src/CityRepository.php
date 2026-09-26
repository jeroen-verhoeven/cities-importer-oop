<?php

class CityRepository{

    public function __construct(){

    }

    public function findIdByCca2(string $cca2) : ?int {
        $query = new WP_Query([
            'post_type'      => 'city_pt',
            'meta_key'       => 'cca2',
            'meta_value'     => $cca2,
            'fields'         => 'ids',
            'posts_per_page' => 1,
        ]);

        $result = $query->posts[0] ?? null;

        return $result;
    }

    public function createOrUpdate(CapitalData $capital) : ?int {
        $cityId = $this->findIdByCca2($capital->cca2);

        if($cityId !== null){
            $postId = wp_update_post([
                'ID'            => $cityId,
                'post_title'    => $capital->cityName,
            ]);

            return is_int($postId) ? $postId : null;
        } else {
            $postId = wp_insert_post([
                'post_title'    => $capital->cityName,
                'post_type'     => 'city_pt',
                'post_status'   => 'publish',
            ]);

            return is_int($postId) ? $postId : null;
        }
    }

    public function saveFields(int $postId, CapitalData $capital) : void {

        $population = is_int($capital->population) ? number_format($capital->population, 0, ',', '.') : null;

        update_field('country', $capital->countryName, $postId);
        update_field('latitude', $capital->latitude, $postId);
        update_field('longitude', $capital->longitude, $postId);
        update_field('population', $population, $postId);
        update_field('currency', ucfirst($capital->currency), $postId);
    }
}