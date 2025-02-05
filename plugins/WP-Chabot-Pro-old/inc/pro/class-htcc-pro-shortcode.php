<?php
/**
 * Shortcode - pro:  wp-chatbot-pro
 * 
 * -- not yet implemented .. 
 * 
 * @package wp-chatbot
 * @subpackage pro
 * @since 3.0
 */


if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'HTCC_Pro_Shortcode' ) ) :
    
class HTCC_Pro_Shortcode {

    public function __construct() {
        $this->init();
    }

    public function init() {
        add_action('init', array( $this, 'shortcode_init' ) );
    }
    
    public function shortcode_init() {
        
        add_shortcode( 'wp-chatbot-pro', array( $this, 'shortcode' ) );
    }
    
    
    /**
     * shortcode attributes .. 
     *  action - action type - time, scroll
     *  do   -  What to do - show, hide ....
     *  when -  when to take action - base on action - time, scroll
     *
     */
    public function shortcode( $atts = [], $content = null, $shortcode = '' ) {
        

        $a = shortcode_atts(
            array(
                'action' => '',
                'when' => '',
                'do' => '',
                
            ), $atts, $shortcode );
        // use like -  '.$a["title"].'   
        

        $action   = $a["action"];
        $when = $a["when"];
        $do = $a["do"];
        
        
        ?>

        <div class="wp_chatbot_pro shortcode">
            wp-chatbot pro
        </div>
        
        <?php
        

    }




}

new HTCC_Pro_Shortcode();

endif; // END class_exists check