<?php 
/**
 * Creates sub level menu
 * and options page 
 * 
 * @package ht-cc
 * @subpackage pro
 * @since 3.0
 * 
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Admin_HTCC_Pro_Settings' ) ) :

class Admin_HTCC_Pro_Settings {


    // wp-chatbot pro menu
    public function menu() {
        add_submenu_page(
            'wp-chatbot',
            'WP-Chabot Actions',
            'Actions',
            'manage_options',
            'wp-chatbot-actions',
            array( $this, 'settings_page' )
        );
    }


    /**
     * 
     * Call back from - $this->menu -  add_submenu_page
     *
     * @since 3.0
     */
    public function settings_page() {
        
        if ( ! current_user_can('manage_options') ) {
            return;
        }

        ?>

        <div class="wrap">
            
            <?php settings_errors(); ?>
            
            <div class="row">
                <div class="col s12 m12 xl8 options">
                    <form action="options.php" method="post" class="">
                        <?php settings_fields( 'htcc_pro_settings_fields' ); ?>
                        <?php do_settings_sections( 'htcc_pro_settings_sections' ) ?>
                        <?php submit_button() ?>
                    </form>
                </div>
            </div>


            <div class="row">
                <div class="col s12 m12 xl8 options">
                    <p class="description">Show Never hides any thing and Hide never shows any thing.</p>
                    <p class="description">E.g. for "Show Icon Only" won't hide already displaying Greeting Dialog, If Icon, Greeting Dialog are hidden then it shows Icon only based on given time</p>
                    
                    <br>
                    <p class="description"><a target="_blank" href="https://holithemes.com/wp-chatbot/click-actions/">Click Actions</a></p>
                    
                    <!-- <p class="description">First come First Serve</p> -->
                    <!-- <p class="description">This settings might be in any order - but if you added an action to run at 10 seconds and another action at 50% page scroll and other action at 20 seconds, </p> -->
                    <!-- <p class="description">what ever first passes it will run, it might to update greetings, hide icon, show icon and greetings ... </p> -->
                </div>
            </div>

        </div>

        <?php
    }


    /**
     * Options page - Regsiter, add section and add setting fields
     *
     * @uses action hook - admin_init
     * 
     * @since 3.0
     * @return void
     */
    public function settings() {

        register_setting( 'htcc_pro_settings_fields', 'htcc_pro_options' , array( $this, 'options_sanitize' ) );
        
        add_settings_section( 'ht_fb_all_customer_chat_section', '', array( $this, 'section_cb' ), 'htcc_pro_settings_sections' );
        
        
        add_settings_field( 'parse', __( 'When to load the Messenger' , 'wp-chatbot' ), array( $this, 'parse_cb' ), 'htcc_pro_settings_sections', 'ht_fb_all_customer_chat_section' );
        
        add_settings_field( 'update_greetings_on_fly', __( 'Update Greetings on Fly' , 'wp-chatbot' ), array( $this, 'update_greetings_on_fly_cb' ), 'htcc_pro_settings_sections', 'ht_fb_all_customer_chat_section' );
        add_settings_field( 'show_time', __( 'Show Icon, Greetings' , 'wp-chatbot' ), array( $this, 'show_time_cb' ), 'htcc_pro_settings_sections', 'ht_fb_all_customer_chat_section' );
        add_settings_field( 'show_icon_time', __( 'Show Icon only' , 'wp-chatbot' ), array( $this, 'show_icon_time_cb' ), 'htcc_pro_settings_sections', 'ht_fb_all_customer_chat_section' );
        add_settings_field( 'gd_show_time', __( 'Show Greeting Dialog' , 'wp-chatbot' ), array( $this, 'gd_show_time_cb' ), 'htcc_pro_settings_sections', 'ht_fb_all_customer_chat_section' );
        add_settings_field( 'gd_hide_time', __( 'Hide Greeting Dialog' , 'wp-chatbot' ), array( $this, 'gd_hide_time_cb' ), 'htcc_pro_settings_sections', 'ht_fb_all_customer_chat_section' );
        add_settings_field( 'hide_time', __( 'Hide Icon, Greetings' , 'wp-chatbot' ), array( $this, 'hide_time_cb' ), 'htcc_pro_settings_sections', 'ht_fb_all_customer_chat_section' );
        
    }


    // section heading
    function section_cb() {
        ?>
        <h1>WP-Chatbot - Actions</h1>
        <?php
    }




    // Parse - show / hide intial .. 
    public function parse_cb() {
        $options = get_option('htcc_pro_options');
        ?>
        <p class="description"><?php _e( 'Set When to load the Messenger for the First Time - ' , 'wp-chatbot' ) ?><a target="_blank" href="https://holithemes.com/wp-chatbot/init-messenger/"><?php _e( 'more info' , 'wp-chatbot' ) ?></a></p>
        <!-- <p class="description"><?php _e( 'All the other action settings will start effect only after the first time messenger loaded ' , 'wp-chatbot' ) ?></p> -->
        <br>
        <p class="description"><?php _e( 'By default Messenger will load when page loads ( no-delay ) ' , 'wp-chatbot' ) ?></p>
        <!-- <p class="description"><?php _e( 'If Time, page scroll down percentage added Messenger will load based on the given values ' , 'wp-chatbot' ) ?></p> -->
        <br>
        
    
        <!-- Mobile -->
        <ul class="collapsible" data-collapsible="accordion">
        <li>
        <div id="style-6" class="collapsible-header">Mobile</div>
        <div class="collapsible-body">

        <p class="description"><b><?php _e( 'Mobile: ' , 'wp-chatbot' ) ?></b></p>

        
        <!-- Mobile time -->
        <div class="row mobile-time">
            <div class="input-field col s10 m9 l9">
                <input type="number" min="1" name="htcc_pro_options[parse_time_mobile]" id="parse_time_mobile" value="<?php echo esc_attr( $options['parse_time_mobile'] ) ?>">
                <label for="parse_time_mobile">Seconds</label>
                <p class="description"><?php _e( 'set time to load the Messenger ( e.g. 30 for 30 seconds ) ' , 'wp-chatbot' ) ?> </p>
                <!-- <p class="description"><?php _e( 'If Time is set, Messenger will load based on the given value ( e.g. 30 for 30 seconds ) ' , 'wp-chatbot' ) ?></p> -->
                <p class="description"><?php _e( 'Leave blank to not take any effect based on time ' , 'wp-chatbot' ) ?> </p>
            </div>
        </div>

        <!-- Mobile scroll -->
        <div class="row mobile-scroll">
            <div class="input-field col s10 m9 l9">
                <input type="number" min="1" name="htcc_pro_options[parse_scroll_mobile]" id="parse_scroll_mobile" value="<?php echo esc_attr( $options['parse_scroll_mobile'] ) ?>">
                <label for="parse_scroll_mobile">Scroll down percentage</label>
                <p class="description"><?php _e( 'Set the Page scroll down percentage to load the Messenger ( e.g 50 for 50% of page scroll down ) ' , 'wp-chatbot' ) ?> </p>
                <p class="description"><?php _e( 'Leave blank to not take any effect based on Page scroll ' , 'wp-chatbot' ) ?> </p>
            </div>
        </div>

        
        <p class="description"><?php _e( 'If Custom Image is enabled - Messenger will load when clicked on Custom image or time, page scroll down percentage ' , 'wp-chatbot' ) ?></p>


        </div>
        </div>
        </li>
        </ul>



        <!-- Desktop -->
        <ul class="collapsible" data-collapsible="accordion">
        <li>
        <div id="style-6" class="collapsible-header">Dekstop</div>
        <div class="collapsible-body">

        <p class="description"><b><?php _e( 'Desktop: ' , 'wp-chatbot' ) ?> </b></p>
        

        <!-- time -->
        <div class="row desktop-time">
            <div class="input-field col s10 m9 l9">
                <input type="number" min="1" name="htcc_pro_options[parse_time]" id="parse_time" value="<?php echo esc_attr( $options['parse_time'] ) ?>">
                <label for="parse_time">Seconds</label>
                <p class="description"><?php _e( 'set time to load the Messenger ( e.g. 30 for 30 seconds ) ' , 'wp-chatbot' ) ?> </p>
                <p class="description"><?php _e( 'Leave blank to not take any effect based on time ' , 'wp-chatbot' ) ?> </p>
            </div>
        </div>

        <!-- scroll -->
        <div class="row desktop-scroll">
            <div class="input-field col s10 m9 l9">
                <input type="number" min="1" name="htcc_pro_options[parse_scroll]" id="parse_scroll" value="<?php echo esc_attr( $options['parse_scroll'] ) ?>">
                <label for="parse_scroll">Scroll down percentage</label>
                <p class="description"><?php _e( 'Set the Page scroll down percentage to load the Messenger ( e.g 50 for 50% of page scroll down ) ' , 'wp-chatbot' ) ?> </p>
                <p class="description"><?php _e( 'Leave blank to not take any effect based on Page scroll ' , 'wp-chatbot' ) ?> </p>
            </div>
        </div>


        <p class="description"><?php _e( 'If Custom Image is enabled - Messenger will load when clicked on Custom image or time, page scroll down percentage ' , 'wp-chatbot' ) ?></p>


        </div>
        </div>
        </li>
        </ul>


        <br>
        <p class="description"><?php _e( '- All the below settings will start effect only after the first time messenger loaded ' , 'wp-chatbot' ) ?></p>

        <?php
    }
    




    // update_greetings_on_fly
    public function update_greetings_on_fly_cb() {
        $options = get_option('htcc_pro_options');

        ?>
        <p class="description"><?php _e( 'Update Greetings on Fly - ' , 'wp-chatbot' ) ?><a target="_blank" href="https://holithemes.com/wp-chatbot/update-greetings-and-ref/"><?php _e( 'more info' , 'wp-chatbot' ) ?></a> </p>
        <br>
        <?php

        if ( isset( $options['ug_enable'] ) ) {
            ?>
            <p>
                <label>
                    <input name="htcc_pro_options[ug_enable]" type="checkbox" value="1" <?php checked( $options['ug_enable'], 1 ); ?> id="ug_enable" />
                    <span>Enable ( Update Greetings Dialog based on this settings )</span>
                </label>
            </p>
            <?php
        } else {
            ?>
            <p>
                <label>
                    <input name="htcc_pro_options[ug_enable]" type="checkbox" value="1" id="enable_update_greetings" />
                    <span>Enable ( Update Greetings Dialog based on this settings )</span>
                </label>
            </p>
            <?php
        }

        ?>
        <br>

        <div class="row">
            <div class="input-field col s10 m9 l9">
                <input type="text" name="htcc_pro_options[ug_lig]" id="" value="<?php echo esc_attr( $options['ug_lig'] ) ?>">
                <label for="fb_greeting_login">Logged in Greetings</label>
                <!-- <p class="description"><?php _e( 'Logged in Greetings ' , 'wp-chatbot' ) ?> </p> -->
            </div>
        </div>
        <!-- <br> -->


        <div class="row">
            <div class="input-field col s10 m9 l9">
                <input type="text" name="htcc_pro_options[ug_log]" id="" value="<?php echo esc_attr( $options['ug_log'] ) ?>">
                <label for="fb_greeting_login">Logged out Greetings</label>
                <!-- <p class="description"><?php _e( 'Logged out Greetings ' , 'wp-chatbot' ) ?> </p> -->
            </div>
        </div>
        <!-- <br> -->


        <div class="row">
            <div class="input-field col s10 m9 l9">
                <input type="text" name="htcc_pro_options[ug_ref]" id="" value="<?php echo esc_attr( $options['ug_ref'] ) ?>">
                <label for="fb_greeting_login">REF</label>
                <!-- <p class="description"><?php _e( 'Ref ' , 'wp-chatbot' ) ?> </p> -->
            </div>
        </div>
        <!-- <br> -->



        <!-- time -->
        <div class="row">
            <div class="input-field col s10 m9 l9">
                <input type="number" min="0" name="htcc_pro_options[ug_time]" id="" value="<?php echo esc_attr( $options['ug_time'] ) ?>">
                <label for="fb_greeting_login">Set time to update</label>
                <p class="description"><?php _e( 'Greeting Dialogs will update and display after selected seconds ( e.g. 30 for 30 seconds ) ' , 'wp-chatbot' ) ?> </p>
                <p class="description"><?php _e( 'Leave blank for not to update based on time ' , 'wp-chatbot' ) ?> </p>
            </div>
        </div>
        <br>

        <!-- scroll -->
        <div class="row">
            <div class="input-field col s10 m9 l9">
                <input type="number" min="0" name="htcc_pro_options[ug_scroll]" id="ug_scroll" value="<?php echo esc_attr( $options['ug_scroll'] ) ?>">
                <label for="ug_scroll">Set Scroll down percentage to update</label>
                <p class="description"><?php _e( 'Greeting Dialogs will update and display when user scroll down this Percent of page ( e.g 70 for 70% of page scroll down ) ' , 'wp-chatbot' ) ?> </p>
                <p class="description"><?php _e( 'Leave blank for not to update based on page scroll ' , 'wp-chatbot' ) ?> </p>
            </div>
        </div>
        <br>


        <br>
        <?php
    }






    
    // Show Greeting Dialog after __ Seconds
    public function gd_show_time_cb() {
        $options = get_option('htcc_pro_options');
        ?>
        <div class="row">
            <div class="input-field col s10 m9 l9">
                <input type="number" min="0" name="htcc_pro_options[gd_show_time]" id="gd_show_time" value="<?php echo esc_attr( $options['gd_show_time'] ) ?>">
                <label for="gd_show_time">Show Greetings Dialog</label>
                <p class="description"><?php _e( 'Show Greeting Dialog after Selected Seconds - ' , 'wp-chatbot' ) ?><a target="_blank" href="https://holithemes.com/wp-chatbot/time-action-show-greetings/"><?php _e( 'more info' , 'wp-chatbot' ) ?></a> </p>
            </div>
        </div>
        <?php
    }


    // Hide Greeting Dialog after Selected Seconds 
    public function gd_hide_time_cb() {
        $options = get_option('htcc_pro_options');
        ?>
        <div class="row">
            <div class="input-field col s10 m9 l9">
                <input type="number" min="0" name="htcc_pro_options[gd_hide_time]" id="gd_hide_time" value="<?php echo esc_attr( $options['gd_hide_time'] ) ?>">
                <label for="gd_hide_time">Hide Greetings Dialog </label>
                <p class="description"><?php _e( 'Hide Greeting Dialog after Selected Seconds - ' , 'wp-chatbot' ) ?><a target="_blank" href="https://holithemes.com/wp-chatbot/time-action-hide-greetings/"><?php _e( 'more info' , 'wp-chatbot' ) ?></a> </p>
            </div>
        </div>
        <?php
    }


    // completly hide the plugin - icon, greetings dialog
    public function hide_time_cb() {
        $options = get_option('htcc_pro_options');
        ?>
        <div class="row">
            <div class="input-field col s10 m9 l9">
                <input type="number" min="0" name="htcc_pro_options[hide_time]" id="" value="<?php echo esc_attr( $options['hide_time'] ) ?>">
                <label for="fb_greeting_login">Hide Icon, Greeting Dialog </label>
                <p class="description"><?php _e( 'Hide Messenger Icon, Greeting Dialog after Selected Seconds - ' , 'wp-chatbot' ) ?><a target="_blank" href="https://holithemes.com/wp-chatbot/time-action-hide-icon-greetings/"><?php _e( 'more info' , 'wp-chatbot' ) ?></a> </p>
            </div>
        </div>
        <?php
    }


    // Show - icon, greetings dialog
    public function show_time_cb() {
        $options = get_option('htcc_pro_options');
        ?>
        <div class="row">
            <div class="input-field col s10 m9 l9">
                <input type="number" min="0" name="htcc_pro_options[show_time]" id="" value="<?php echo esc_attr( $options['show_time'] ) ?>">
                <label for="fb_greeting_login">Show Icon, Greetings Dialog </label>
                <p class="description"><?php _e( 'Show Messenger Icon, Greeting Dialog after Selected Seconds - ' , 'wp-chatbot' ) ?><a target="_blank" href="https://holithemes.com/wp-chatbot/time-action-show-icon-greetings/"><?php _e( 'more info' , 'wp-chatbot' ) ?></a> </p>
            </div>
        </div>
        <?php
    }


    // Show - icon alone
    public function show_icon_time_cb() {
        $options = get_option('htcc_pro_options');
        ?>
        <div class="row">
            <div class="input-field col s10 m9 l9">
                <input type="number" min="0" name="htcc_pro_options[show_icon_time]" id="" value="<?php echo esc_attr( $options['show_icon_time'] ) ?>">
                <label for="fb_greeting_login">Show Icon </label>
                <p class="description"><?php _e( 'Show Messenger Icon after Selected Seconds - ' , 'wp-chatbot' ) ?><a target="_blank" href="https://holithemes.com/wp-chatbot/time-action-show-icon/"><?php _e( 'more info' , 'wp-chatbot' ) ?></a> </p>
            </div>
        </div>
        <?php
    }













    



    







    /**
     * Sanitize each setting field as needed
     *
     * @since 1.0
     * @param array $input Contains all settings fields as array keys
     */
    public function options_sanitize( $input ) {

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'not allowed to modify - please contact admin ' );
        }

        $new_input = array();

        foreach ($input as $key => $value) {
            if( isset( $input[$key] ) ) {
                $new_input[$key] = sanitize_text_field( $input[$key] );
            }
        }


        return $new_input;
    }




}



$admin_htcc_pro_Settings = new Admin_HTCC_Pro_Settings();
add_action('admin_menu', array($admin_htcc_pro_Settings, 'menu') );
add_action('admin_init', array($admin_htcc_pro_Settings, 'settings') );


endif; // END class_exists check