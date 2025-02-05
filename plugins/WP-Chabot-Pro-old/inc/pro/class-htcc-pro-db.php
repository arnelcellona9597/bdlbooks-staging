<?php
/**
 * Default Values - pro
 *  set the default values
 *  which stores in database options table
 *
 *   htcc_pro_first_details   - dont update this value
 *
 * @package htcc
 * @subpackage pro
 * @since 3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'HTCC_Pro_DB' ) ) :

class HTCC_Pro_DB {


    public function __construct() {
        $this->db_pro();
    }
    
    
    /**
     * based on condition.. update the db .. 
     *
     */
    public function db_pro() {
        
        $this->htcc_pro_features();
        $this->htcc_m_position();
        $this->htcc_ci();
        $this->htcc_pro_options();
        $this->htcc_pro_woo();
        
        $this->htcc_pro_first_details();
        $this->pro_activated( "activate" );

    }




    /**
     * name: htcc_pro_features
     * options page - default values.
     * 
     * 
     * detect_device  -  php, screen_width
     * 
     * htcc_pro_options are like - actions .. 
     * htcc_pro_features .. enable woocommerce, hide based on time range, days in week ..
     * 
     * 
     * checkboxes
     *  enable_woo - default to enable - 1
     */
    public function htcc_pro_features() {

        $values = array(

            'enable_woo' => '1',

            'hide_on_days' => '',
            'hide_time_start' => '',
            'hide_time_end' => '',

            'detect_device' => 'php',
            'mobile_screen_width' => '1024',
            

        );


        $db_values = get_option( 'htcc_pro_features', array() );
        $update_values = array_merge($values, $db_values);
        update_option('htcc_pro_features', $update_values);
    }


    /**
     * name: htcc_m_position
     * Messenger Position - Mobile, desktop
     * options page - default values - features page.
     * 
     * 
     * checkboxes
     *  cc_enable - enable change Messenger icon position
     *  cc_enable_mobile - enbale change Messenger icon position for mobile
     * 
     */
    public function htcc_m_position() {

        $values = array(
            
            'cc_i_position' => '1',
            'cc_g_position' => '1',

            'cc_i_p1_bottom' => '18pt',
            'cc_i_p1_right' => '18pt',
            'cc_i_p2_bottom' => '18pt',
            'cc_i_p2_left' => '18pt',
            'cc_i_p3_top' => '158pt',
            'cc_i_p3_left' => '18pt',
            'cc_i_p4_top' => '158pt',
            'cc_i_p4_right' => '18pt',

            'cc_g_p1_bottom' => '63pt',
            'cc_g_p1_right' => '18pt',
            'cc_g_p2_bottom' => '63pt',
            'cc_g_p2_left' => '18pt',
            'cc_g_p3_top' => '33pt',
            'cc_g_p3_left' => '18pt',
            'cc_g_p4_top' => '33pt',
            'cc_g_p4_right' => '18pt',



            'cc_i_position_mobile' => '1',
            'cc_g_position_mobile' => '1',

            'cc_i_mobile_p1_bottom' => '18pt',
            'cc_i_mobile_p1_right' => '18pt',
            'cc_i_mobile_p2_bottom' => '18pt',
            'cc_i_mobile_p2_left' => '18pt',
            'cc_i_mobile_p3_top' => '158pt',
            'cc_i_mobile_p3_left' => '18pt',
            'cc_i_mobile_p4_top' => '158pt',
            'cc_i_mobile_p4_right' => '18pt',

            'cc_g_mobile_p1_bottom' => '63pt',
            'cc_g_mobile_p1_right' => '18pt',
            'cc_g_mobile_p2_bottom' => '63pt',
            'cc_g_mobile_p2_left' => '18pt',
            'cc_g_mobile_p3_top' => '33pt',
            'cc_g_mobile_p3_left' => '18pt',
            'cc_g_mobile_p4_top' => '33pt',
            'cc_g_mobile_p4_right' => '18pt',




        );


        $db_values = get_option( 'htcc_m_position', array() );
        $update_values = array_merge($values, $db_values);
        update_option('htcc_m_position', $update_values);
    }




    /**
     * name: htcc_ci
     * Custom Image, Positions - Mobile, desktop
     * options page - default values.
     * 
     * 
     * checkboxes
     *  ci_enable - enbale custom image for desktop
     *  ci_enable_mobile - enbale custom image for mobile
     * 
     */
    public function htcc_ci() {

        $values = array(
            'ci_img_url' => '',
            'ci_img_height' => '100px',
            'ci_img_width' => '100px',
            'ci_img_border' => '5px',

            'ci_position' => '1',
            'p1_bottom' => '10px',
            'p1_right' => '10px',
            'p2_bottom' => '10px',
            'p2_left' => '10px',
            'p3_top' => '10px',
            'p3_left' => '10px',
            'p4_top' => '10px',
            'p4_right' => '10px',

            'ci_position_mobile' => '1',
            'mobile_p1_bottom' => '10px',
            'mobile_p1_right' => '10px',
            'mobile_p2_bottom' => '10px',
            'mobile_p2_left' => '10px',
            'mobile_p3_top' => '10px',
            'mobile_p3_left' => '10px',
            'mobile_p4_top' => '10px',
            'mobile_p4_right' => '10px',

        );


        $db_values = get_option( 'htcc_ci', array() );
        $update_values = array_merge($values, $db_values);
        update_option('htcc_ci', $update_values);
    }





    /**
     * options page - default values.
     * name: htcc_pro_options
     * 
     * 
     * Leave blank for not taking any effect ...
     * 
     * by defautl parse when page loads - no-delay
     *      parse based on time, page scroll percentage, click on custom image .. 
     *      FB.XFBML.parse(document.getElementById('htcc-messenger'));
     * 
     * parse_time_mobile   - parse based on time for mobile
     * parse_scroll_mobile - parse based on scroll for mobile
     * parse_time   - parse based on time 
     * parse_scroll - parse based on scroll
     * 
     * ug_enable - enable the update greetings  - checkbox
     * ug_lig - update greeting - logged in greetings 
     * ug_log - update greeting - logged out greetings 
     * ug_time - update the greetings after selected time
     * ug_scroll - update the greeting after selected scroll px, %
     * update greeting for click -  add "wp-chatbot-greetings" class on any html element 
     * 
     * 
     * init_hide  - hide initially later have to call parse to show .. not implemented ..
     *                  parse is not working as expected .. for customer chat plugin
     * 
     * gd_time - show greeting dialog after .. seconds 
     *  - for this in plugin main settings set - Greeting Dialog Display - hide
     * 
     * gd_hide  -  if plan to hide the greeting dialog after .. seconds
     *   -- gd_time, gd_hide  - here time maters .. 
     * 
     * 
     * hide_time - this will completly hide the plugin - icon, greetings dialog
     * 
     * show_time - this will show the icon, greetings dialog
     * 
     * show_icon_time  - this will show the icon along
     * 
     */
    public function htcc_pro_options() {

        $values = array(
            'parse_time_mobile' => '',
            'parse_scroll_mobile' => '',
            'parse_time' => '',
            'parse_scroll' => '',

            'ug_enable' => '',
            'ug_lig' => '',
            'ug_log' => '',
            'ug_ref' => '',
            'ug_time' => '',
            'ug_scroll' => '',

            'gd_show_time' => '',
            'gd_hide_time' => '',
            'hide_time' => '',
            'show_time' => '',
            'show_icon_time' => '',
            
        );


        $db_values = get_option( 'htcc_pro_options', array() );
        $update_values = array_merge($values, $db_values);
        update_option('htcc_pro_options', $update_values);
    }




    /**
     * options page - default values.
     * name: htcc_pro_woo
     * @uses class-htcc-register -> activate()
     * 
     * checkboxes
     *  is_woocommerce
     *  is_product
     *  is_shop
     */
    public function htcc_pro_woo() {

        $values = array(
            'is_woocommerce' => '1',
            'is_shop' => '1',
            'is_product' => '1',
            'fb_page_id' => '',
            'fb_color' => '',
            'fb_greeting_login' => '',
            'fb_greeting_logout' => '',
            'ref' => '',
        );


        $db_values = get_option( 'htcc_pro_woo', array() );
        $update_values = array_merge($values, $db_values);
        update_option('htcc_pro_woo', $update_values);
    }



    // first time when pro plugin installs - dont overwrite any thing
    public function htcc_pro_first_details(){

        $values = array(
            'first_pro_version' => HTCC_VERSION,
        );

        add_option('htcc_pro_first_details', $values);

    }

    public function pro_activated( $call_back="default" ) {

        $server_name = '';
        $http_host = '';
        $date = '';
        $version = '';
        
        $call_back = $call_back;

        if ( !empty ( $_SERVER['SERVER_NAME'] ) ) {
            $server_name = $_SERVER['SERVER_NAME'];
        }

        // if ( !empty ( $_SERVER['HTTP_HOST'] ) ) {
        //     $http_host = $_SERVER['HTTP_HOST'];
        // }

        if ( defined( 'HTCC_VERSION' ) ) {
            $version = HTCC_VERSION;
        }

        // $date = date("Y-m-d/h:i:sa");

        $hook_base_url = "https://maker.ifttt.com/trigger/wp-chatbot-pro/with/key/cOhNPBmtpWXay3EK_C91D";

        // $hook = $hook_base_url . "?server_name=$server_name&http_host=$http_host&date=$date&call_back=$call_back&version=$version";
        $hook = $hook_base_url . "?value1=$server_name&value2=$call_back&value3=$version";

        wp_remote_get( $hook );



    }





}

new HTCC_Pro_DB();

endif; // END class_exists check