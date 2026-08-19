<?php    
    //register custom post types
    function register_city_post_type(){

        //city 
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

    add_action('init', 'register_city_post_type');