<?php
/**
 * starting to pro features ..
 */


if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'HTCC_Pro' ) ) :
    
class HTCC_Pro {

    public function __construct() {
        $this->features();
    }


    // Enable / Add each functionality
    public function features() {

        // Enqueue
        require_once HTCC_PLUGIN_DIR . 'inc/pro/class-htcc-pro-enqueue.php';

        // shortcode - wp-chatbot-pro
        // require_once HTCC_PLUGIN_DIR . 'inc/pro/class-htcc-pro-shortcode.php';
        
    }



}

new HTCC_Pro();

endif; // END class_exists check