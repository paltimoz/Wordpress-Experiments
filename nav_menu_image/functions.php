<?php


// Add post image support for Bottom Menu
add_theme_support( 'post-thumbnails' );
add_theme_support( 'menu' );

// Featured Image Title Change (Optional)
add_action('do_meta_boxes', 'pz_replace_featured_image_box');  
function pz_replace_featured_image_box()  
{  
    remove_meta_box( 'postimagediv', 'page', 'side' );  
    add_meta_box('postimagediv', 'Menu Image', 'post_thumbnail_meta_box', 'page', 'side', 'low');  
} 

add_action( 'after_setup_theme', 'pz_register_nav_menu' );
function pz_register_nav_menu(){
    register_nav_menus( array(
      'custom_image_menu' => 'Custom Image Menu'
    ));
}



function pz_create_nav_menu( $theme_location ) {
    if ( ($theme_location) && ($locations = get_nav_menu_locations()) && isset($locations[$theme_location]) ) {
         
        $menu = get_term( $locations[$theme_location], 'nav_menu' );
        $menu_items = wp_get_nav_menu_items($menu->term_id);
        $menu_list .= '';
          
        foreach( $menu_items as $menu_item ) {
            $menu_list .= '<a id="' . $menu_item->ID . '" href="' . $menu_item->url . '" target="blank">
								<img src="'.get_the_post_thumbnail_url($menu_item->object_id).'" alt="" /><br>
								<span>' . $menu_item->title . '</span>
							</a>';
        }
  
    }
    return $menu_list;
}
