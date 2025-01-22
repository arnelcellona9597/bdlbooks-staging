<?php
/**
 * Enqueue sytles, scripts - pro ..
 * 
 * app.js
 * 
 */


if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'HTCC_Pro_Enqueue' ) ) :
    
class HTCC_Pro_Enqueue {



    function htcc_values() {
        
        $htcc_options = get_option( 'htcc_options' );
        $htcc_pro_features = get_option( 'htcc_pro_features' );
        $htcc_pro_options = get_option( 'htcc_pro_options' );
        $htcc_pro_woo = get_option( 'htcc_pro_woo' );

        // php_is_mobile;
        $php_is_mobile = ht_cc()->device_type->is_mobile;

        $hide_on_mobile = "no";
        $hide_on_desktop = "no";

        if ( isset ( $htcc_options['hideon_mobile'] ) ) {
            $hide_on_mobile = "yes";
        }

        if ( isset ( $htcc_options['hideon_desktop'] ) ) {
            $hide_on_desktop = "yes";
        }

        $ug_enable = 'no';
        if ( isset ( $htcc_pro_options['ug_enable'] ) ) {
            $ug_enable = 'yes';
        }


        // timezone from WordPress general settings.
        $time_zone = get_option('gmt_offset');


        /**
         * positions
         */
        $htcc_ci =  get_option('htcc_ci');

        $ci_enable = 'no';
        $ci_enable_mobile = 'no';

        if ( isset( $htcc_ci['ci_enable'] ) ) {
            $ci_enable = 'yes';
        }

        if ( isset( $htcc_ci['ci_enable_mobile'] ) ) {
            $ci_enable_mobile = 'yes';
        }

        $position = $htcc_ci['ci_position'];
        $position_mobile = $htcc_ci['ci_position_mobile'];

        // positions - generate css  - Desktop
        $p1 = '';
        $p1_value = '';
        $p2 = '';
        $p2_value = '';
        if( 1 == $position ) {
            $p1 = 'bottom';
            $p1_value = $htcc_ci['p1_bottom'];
            $p2 = 'right';
            $p2_value = $htcc_ci['p1_right'];
        } elseif( 2 == $position ) {
            $p1 = 'bottom';
            $p1_value = $htcc_ci['p2_bottom'];
            $p2 = 'left';
            $p2_value = $htcc_ci['p2_left'];
        } elseif( 3 == $position ) {
            $p1 = 'top';
            $p1_value = $htcc_ci['p3_top'];
            $p2 = 'left';
            $p2_value = $htcc_ci['p3_left'];
        } elseif( 4 == $position ) {
            $p1 = 'top';
            $p1_value = $htcc_ci['p4_top'];
            $p2 = 'right';
            $p2_value = $htcc_ci['p4_right'];
        }

        // positions - generate css  - mobile
        $m_p1 = '';
        $m_p1_value = '';
        $m_p2 = '';
        $m_p2_value = '';
        if( 1 == $position_mobile ) {
            $m_p1 = 'bottom';
            $m_p1_value = $htcc_ci['mobile_p1_bottom'];
            $m_p2 = 'right';
            $m_p2_value = $htcc_ci['mobile_p1_right'];
        } elseif( 2 == $position_mobile ) {
            $m_p1 = 'bottom';
            $m_p1_value = $htcc_ci['mobile_p2_bottom'];
            $m_p2 = 'left';
            $m_p2_value = $htcc_ci['mobile_p2_left'];
        } elseif( 3 == $position_mobile ) {
            $m_p1 = 'top';
            $m_p1_value = $htcc_ci['mobile_p3_top'];
            $m_p2 = 'left';
            $m_p2_value = $htcc_ci['mobile_p3_left'];
        } elseif( 4 == $position_mobile ) {
            $m_p1 = 'top';
            $m_p1_value = $htcc_ci['mobile_p4_top'];
            $m_p2 = 'right';
            $m_p2_value = $htcc_ci['mobile_p4_right'];
        }



        /**
         * 
         * hide_time  - time action - seconds - hide icon, messener
         */
        $htcc_var = array(

            
            'hide_on_days' => esc_attr( $htcc_pro_features['hide_on_days'] ),
            'hide_time_start' => esc_attr( $htcc_pro_features['hide_time_start'] ),
            'hide_time_end' => esc_attr( $htcc_pro_features['hide_time_end'] ),
            'detect_device' => esc_attr( $htcc_pro_features['detect_device'] ),
            'mobile_screen_width' => esc_attr( $htcc_pro_features['mobile_screen_width'] ),
            
            'php_is_mobile' => $php_is_mobile,
            'time_zone' => $time_zone,
            'hide_on_mobile' => $hide_on_mobile,
            'hide_on_desktop' => $hide_on_desktop,


            'parse_time' => esc_attr( $htcc_pro_options['parse_time'] ),
            'parse_scroll' => esc_attr( $htcc_pro_options['parse_scroll'] ),
            'parse_time_mobile' => esc_attr( $htcc_pro_options['parse_time_mobile'] ),
            'parse_scroll_mobile' => esc_attr( $htcc_pro_options['parse_scroll_mobile'] ),
            
            'ug_enable' => $ug_enable,
            'ug_lig' => esc_attr( $htcc_pro_options['ug_lig'] ),
            'ug_log' => esc_attr( $htcc_pro_options['ug_log'] ),
            'ug_ref' => esc_attr( $htcc_pro_options['ug_ref'] ),
            'ug_time' => esc_attr( $htcc_pro_options['ug_time'] ),
            'ug_scroll' => esc_attr( $htcc_pro_options['ug_scroll'] ),

            
            'gd_show_time' => esc_attr( $htcc_pro_options['gd_show_time'] ),
            'gd_hide_time' => esc_attr( $htcc_pro_options['gd_hide_time'] ),
            'hide_time' => esc_attr( $htcc_pro_options['hide_time'] ),
            'show_time' => esc_attr( $htcc_pro_options['show_time'] ),
            'show_icon_time' => esc_attr( $htcc_pro_options['show_icon_time'] ),


            'ci_enable' => $ci_enable,
            'ci_enable_mobile' => $ci_enable_mobile,

            'p1' => $p1,
            'p2' => $p2,
            'p1_value' => $p1_value,
            'p2_value' => $p2_value,
            'm_p1' => $m_p1,
            'm_p2' => $m_p2,
            'm_p1_value' => $m_p1_value,
            'm_p2_value' => $m_p2_value,


        );





        wp_localize_script( 'htcc_appjs_pro', 'htcc_var', $htcc_var );

    }

    // Messenger positions
    function htcc_m() {
        include_once HTCC_PLUGIN_DIR . 'inc/pro/htcc-pro-positions.php';
    }


    /**
	 * Enqueue styles, scripts
	 *
	 * @since 3.0
	 */
    function enqueue() {
        
        wp_enqueue_script( 'htcc_appjs_pro', plugins_url( 'inc/pro/assets/js/app.js', HTCC_PLUGIN_FILE ), array('jquery'), HTCC_VERSION, true );
        
        // lacalize script
        // htcc_var, wp_chatbot_log - variable added in htcc-pro-values.php
        
        // htcc_values 
        $this->htcc_values();
        
        // htcc_m
        $this->htcc_m();


    }

}

endif; // END class_exists check

$htcc_pro_enqueue = new HTCC_Pro_Enqueue();

add_action('wp_enqueue_scripts', array( $htcc_pro_enqueue, 'enqueue' ) );



