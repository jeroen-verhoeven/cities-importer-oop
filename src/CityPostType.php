<?php

class CityPostType{

    public function registerPostType() : void{
    
        $labels = [
            'name'          => __('Cities', 'textdomain'),
            'singular_name' => __('City', 'textdomain'),
            'menu_name'     => __('Cities', 'textdomain'),
            'add_new_item'  => __('Add city', 'textdomain'),
            'edit_item'     => __('Edit city', 'textdomain'),
            'view_item'     => __('View city', 'textdomain'),
        ];
    
        $args = [
            'label'         => __('city', 'textdomain'),
            'labels'        => $labels,
            'public'        => true,
            'show_in_rest'  => true,
            'has_archive'   => true,
            'rewrite'       => ['slug' => 'city'],
            'supports'      => ['title', 'editor', 'thumbnail', 'custom-fields'],
            'menu_icon'     => 'dashicons-admin-site',
        ];

        register_post_type('city_pt', $args);
    }
}