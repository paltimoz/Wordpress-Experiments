<?php


// Add the Meta Box
function pz_add_custom_meta_box()
{
    add_meta_box(
        'custom_meta_box',
        'Custom Image Metabox',
        'pz_custom_meta_box_callbacks',
        'page', // page, post etc.
        'normal',
        'high');
}
add_action('add_meta_boxes', 'pz_add_custom_meta_box');

// Field Array
$prefix = '_pz_field_';
$custom_meta_fields = array(
    array(
        'label' => 'Custom Text Field',
        'desc' => '',
        'id' => $prefix . 'custom_text',
        'type' => 'text',
    ),
    array(
        'label' => 'Single Image',
        'desc' => '',
        'id' => $prefix . 'single_image',
        'type' => 'image',
    ),
    array(
        'label' => 'Multiple Image',
        'desc' => '',
        'id' => $prefix . 'multiple_image',
        'type' => 'gallery',
    )
);

// The Callback
function pz_custom_meta_box_callbacks($object)
{
    global $custom_meta_fields, $post;
    // Use nonce for verification
    echo '<input type="hidden" name="custom_meta_box_nonce" value="' . wp_create_nonce(basename(__FILE__)) . '" />';

    // Begin the field table and loop
    echo '<table class="form-table">';
    foreach ($custom_meta_fields as $field) {
        // get value of this field if it exists for this post
        $meta = get_post_meta($post->ID, $field['id'], true);
        // begin a table row with
        echo '<tr>
            <th><label for="' . $field['id'] . '">' . $field['label'] . '</label></th>
            <td>';
        switch ($field['type']) {
            case 'text':
                echo '<input id="' . $field['id'] . '" type="text" name="' . $field['id'] . '" value="' . esc_attr($meta) . '" size="100" />';
                break;

            case 'image':
                $hidden = 'hidden';
                $close_button = null;
                if ($meta) {
                    $hidden = null;
                    $close_button = '<span class="pz_img_close" data-field-id="' . $field['id'] . '"></span>';
                }
                echo '<input id="' . $field['id'] . '" type="hidden" name="' . $field['id'] . '" value="' . esc_attr($meta) . '" />
                    <div class="pz_image_container '.$hidden.'" id="' . $field['id'] . '_container">' . $close_button . '
                    <img id="' . $field['id'] . '_src" src="' . wp_get_attachment_thumb_url(pz_get_image_id($meta)) . '"></div>
                    <input class="btn-upload-img" type="button" value="Add Image" data-field-id="' . $field['id'] . '" />';
                break;
            case 'gallery':
                $meta_html = null;
                if ($meta) {
                    $meta_html .= '<ul class="pz_gallery_list">';
                    $meta_array = explode(',', $meta);
                    foreach ($meta_array as $meta_gall_item) {
                        $meta_html .= '<li>
                        <div class="pz_gallery_container">
                        <span class="pz_gallery_close" data-field-id="' . $field['id'] . '">
                        <img id="' . esc_attr($meta_gall_item) . '" src="' . wp_get_attachment_thumb_url($meta_gall_item) . '">
                        </span>
                        </div>
                        </li>';
                    }
                    $meta_html .= '</ul>';
                }
                echo '<input id="' . $field['id'] . '" type="hidden" name="' . $field['id'] . '" value="' . esc_attr($meta) . '" />
                    <span id="pz_gallery_src">' . $meta_html . '</span>
                    <div class="pz_gallery_button_container">
                    <input class="btn-upload-gallery" type="button" value="Add Images" data-field-id="' . $field['id'] . '" /></div>';
                break;
        } //end switch
        echo '</td></tr>';
    } // end foreach
    echo '</table>'; // end table
}

// Register admin scripts for custom fields
function pz_load_wp_admin_style()
{
    wp_enqueue_media();
    wp_enqueue_script('media-upload');
    wp_enqueue_style('pz_admin_css', get_template_directory_uri() . '/pz_admin.css');
    wp_enqueue_script('pz_admin_script', get_template_directory_uri() . '/pz_admin.js');
}
add_action('admin_enqueue_scripts', 'pz_load_wp_admin_style');

// Get image ID from URL
function pz_get_image_id($image_url)
{
    global $wpdb;
    $attachment = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='%s';", $image_url));
    return $attachment[0];
}

// Save the Data
function pz_save_custom_meta($post_id)
{
    global $custom_meta_fields;
    // Verify nonce
    if (!wp_verify_nonce($_POST['custom_meta_box_nonce'], basename(__FILE__))) {
        return $post_id;
    }

    // Check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }

    // Check permissions
    if ('page' == $_POST['post_type']) {
        if (!current_user_can('edit_page', $post_id)) {
            return $post_id;
        }

    } elseif (!current_user_can('edit_post', $post_id)) {
        return $post_id;
    }
    // Loop through meta fields
    foreach ($custom_meta_fields as $field) {
        $new_meta_value = esc_attr($_POST[$field['id']]);
        $meta_key = $field['id'];
        $meta_value = get_post_meta($post_id, $meta_key, true);
        // If theres a new meta value and the existing meta value is empty
        if ($new_meta_value && $meta_value == null) {
            add_post_meta($post_id, $meta_key, $new_meta_value, true);
            // If theres a new meta value and the existing meta value is different
        } elseif ($new_meta_value && $new_meta_value != $meta_value) {
            update_post_meta($post_id, $meta_key, $new_meta_value);
        } elseif ($new_meta_value == null && $meta_value) {
            delete_post_meta($post_id, $meta_key, $meta_value);
        }
    }
}
add_action('save_post', 'pz_save_custom_meta');


function get_custom_text($page_id)
{
    return get_post_meta($page_id, '_pz_field_custom_text', true);
}

function get_single_image($page_id)
{
    return get_post_meta($page_id, '_pz_field_single_image', true);
}

function get_multiple_image_with_html($page_id)
{
    $meta = get_post_meta($page_id, '_pz_field_multiple_image', true);
    $meta_array = explode(',', $meta);
    foreach ($meta_array as $meta_gall_item) {
        $meta_html .= '<img src="' . wp_get_attachment_thumb_url($meta_gall_item) . '" alt="" />';
    }
    return $meta_html;
}