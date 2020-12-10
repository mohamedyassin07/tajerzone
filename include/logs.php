<?php
function cptui_register_my_cpts__log() {
	
	$labels = [
		"name" => __( "TJR Log", "tjr" ),
		"singular_name" => __( "TJR Logs", "tjr" ),
	];
	$args = [
		"label" => __( "TJR Log", "tjr" ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"has_archive" => false,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"delete_with_user" => false,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"rewrite" => [ "slug" => "tjr_log", "with_front" => true ],
        "query_var" => true,
        "menu_icon" => "dashicons-text-page",
		"supports" => [ "title", "editor", "thumbnail" ],
	];

	register_post_type( "tjr_log", $args );
}

add_action( 'init', 'cptui_register_my_cpts__log' );

    add_action('init' , function(){
        register_post_type('tjr_log' ,
            [
                'labels' => [
                    'name' => __('TajerZone Logs') ,
                    'singular_name' => __('Log') ,
                ] ,
                'public' => false ,
                'has_archive' => false ,
                'supports' => [ 'title' , 'excerpt' , 'custom-fields' , ] ,
                'hierarchical' => false ,
                'show_ui' => true ,
                'show_in_menu' => true ,
                'show_in_nav_menus' => false ,
                'show_in_admin_bar' => false ,
                'menu_position' => 5 ,
                'can_export' => false ,
                'exclude_from_search' => false ,
                'publicly_queryable' => false ,
                'show_in_rest' => false ,
                'rest_base' => false ,
                'rest_controller_class' => false ,
                "capability_type" => "post" ,
                'map_meta_cap' => true ,
                'capabilities' => [
                    'create_posts' => false ,
                ] ,
            ]
        );
    });
    
    
    add_filter('manage_tjr_log_posts_columns' , function($columns){
        
        $columns = [
            'id' => __('id') ,
            'order_id' => __('order_id') ,
            'customer_id' => __('customer_id') ,
            'customer_name' => __('customer_name') ,
            'response' => __('response') ,
            '_date' => __('date') ,
        ];
        
        return $columns;
    });
    
    
    add_action('manage_tjr_log_posts_custom_column' , function($column , $post_id){
        
        switch($column)
        {
            case 'id' :
                echo $post_id;
                break;
            
            case '_date' :
                echo get_the_date('Y-m-d H:i:s' , $post_id);
                break;
            
            case 'order_id' :
                $order_id = get_post_meta($post_id , 'order_id' , true);
                $url = get_admin_url('' , "/post.php?post={$order_id}&action=edit");
                echo "<a href='{$url}'>{$order_id}</a></a>";
                break;
            
            case 'customer_id' :
                $customer_id = get_post_meta($post_id , 'customer_id' , true);
                $url = get_admin_url('' , "/user-edit.php?user_id={$customer_id}");
                echo "<a href='{$url}'>{$customer_id}</a></a>";
                break;
            
            case 'customer_name' :
                echo get_post_meta($post_id , 'customer_name' , true);
                break;
            
            case 'response' :
                echo get_the_excerpt($post_id);
                break;
            
            
        }
    } , 10 , 2);
    
    
    function disable_new_posts()
    {
        $global_settings = get_posts('post_type=tjr_log');
        
        if(count($global_settings) != 0)
        {
            
            // Hide sidebar link
            global $submenu;
            unset($submenu['edit.php?post_type=tjr_log'][10]);
            
            // Hide link on listing page
            if(isset($_GET['post_type']) && $_GET['post_type'] == 'tjr_log')
            {
                echo "<style type='text/css'> .row-actions .editinline, .row-actions .edit ,.tablenav,.subsubsub{ display:none !important; }</style>";
            }
        }
    }
    
    add_action('admin_menu' , 'disable_new_posts');