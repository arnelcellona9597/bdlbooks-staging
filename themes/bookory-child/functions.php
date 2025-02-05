<?php
/**
 * Theme functions and definitions.
 */

function enqueue_all_css_files() {
    // Define the path to the folder containing the CSS files
    $css_folder = get_stylesheet_directory() . '/assets/css/';
    
    // Use glob to get all CSS files in the folder
    $css_files = glob($css_folder . '*.css');
    
    // Loop through each file and enqueue it
    foreach ($css_files as $css_file) {
        $css_url = get_stylesheet_directory_uri() . '/assets/css/' . basename($css_file);
        $file_time = filemtime($css_file); // Get the file's last modified time
        wp_enqueue_style("bdl-".basename($css_file, '.css'), $css_url, array(), $file_time); // Add $file_time as version
    }
}
add_action('wp_footer', 'enqueue_all_css_files');

add_filter('pre_get_posts', function($query) {
    if (!is_admin() && $query->is_main_query() && is_shop()) {
        if (isset($_GET['per_page']) && is_numeric($_GET['per_page'])) {
            $query->set('posts_per_page', absint($_GET['per_page']));
        } else {
            $query->set('posts_per_page', 24); // Default to 24
        }
    }
});