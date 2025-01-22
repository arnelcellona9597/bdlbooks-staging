<?php 
/**
 * Features - options page ..
 * Creates sub level menu
 * 
 * @package ht-cc
 * @subpackage pro
 * @since 3.2
 * 
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Admin_HTCC_Pro_Features' ) ) :

class Admin_HTCC_Pro_Features {


    // wp-chatbot pro menu
    public function menu() {
        add_submenu_page(
            'wp-chatbot',
            'WP-Chabot Features',
            'Features',
            'manage_options',
            'wp-chatbot-features',
            array( $this, 'settings_page' )
        );
    }


    /**
     * 
     * Call back from - $this->menu -  add_submenu_page
     *
     * @since 3.2
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
                        <?php settings_fields( 'htcc_pro_settings_fields_features' ); ?>
                        <?php do_settings_sections( 'htcc_pro_settings_sections_features' ) ?>
                        <?php submit_button() ?>
                    </form>
                </div>
            </div>


            <div class="row">
                <div class="col s12 m12 xl8 options">
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
        
        register_setting( 'htcc_pro_settings_fields_features', 'htcc_pro_features' , array( $this, 'options_sanitize' ) );
        register_setting( 'htcc_pro_settings_fields_features', 'htcc_ci' , array( $this, 'options_sanitize' ) );
        register_setting( 'htcc_pro_settings_fields_features', 'htcc_m_position' , array( $this, 'options_sanitize' ) );
        
        add_settings_section( 'ht_fb_all_customer_chat_section_features', '', array( $this, 'section_cb' ), 'htcc_pro_settings_sections_features' );
        
        
        add_settings_field( 'enable_woo', __( 'WooCommerce' , 'wp-chatbot' ), array( $this, 'enable_woo_cb' ), 'htcc_pro_settings_sections_features', 'ht_fb_all_customer_chat_section_features' );


        add_settings_field( 'hide_on_days', __( 'Hide on This Days' , 'wp-chatbot' ), array( $this, 'hide_on_days_cb' ), 'htcc_pro_settings_sections_features', 'ht_fb_all_customer_chat_section_features' );
        add_settings_field( 'hide_on_time_range', __( 'Hide on This Time Range' , 'wp-chatbot' ), array( $this, 'hide_on_time_range_cb' ), 'htcc_pro_settings_sections_features', 'ht_fb_all_customer_chat_section_features' );
        add_settings_field( 'detect_device', __( 'Detect Device' , 'wp-chatbot' ), array( $this, 'detect_device_cb' ), 'htcc_pro_settings_sections_features', 'ht_fb_all_customer_chat_section_features' );
        add_settings_field( 'mobile_max_screen_width', __( 'Mobile - Max Screen width' , 'wp-chatbot' ), array( $this, 'mobile_screen_width_cb' ), 'htcc_pro_settings_sections_features', 'ht_fb_all_customer_chat_section_features' );
        
        
        add_settings_field( 'htcc_m_position', __( 'Change Messenger Position' , 'wp-chatbot' ), array( $this, 'messenger_position_cb' ), 'htcc_pro_settings_sections_features', 'ht_fb_all_customer_chat_section_features' );

        add_settings_field( 'htcc_ci', __( 'Custom Image' , 'wp-chatbot' ), array( $this, 'custom_image_cb' ), 'htcc_pro_settings_sections_features', 'ht_fb_all_customer_chat_section_features' );
        

    }


    // section heading
    function section_cb() {
        ?>
        <h1>WP-Chatbot - Features</h1>
        <?php
    }


    // change Messenger Position
    // cc - customer chat
    // cc - i - icon
    // cc - g - greeting dialog, chat window
    function messenger_position_cb() {
        $options = get_option('htcc_m_position');
        $cc_i_position_value = esc_attr( $options['cc_i_position'] );
        $cc_g_position_value = esc_attr( $options['cc_g_position'] );
        $cc_i_position_value_mobile = esc_attr( $options['cc_i_position_mobile'] );
        $cc_g_position_value_mobile = esc_attr( $options['cc_g_position_mobile'] );

        ?>
        <p class="description"><?php _e( 'Change Messenger Position - ' , 'wp-chatbot' ) ?><a target="_blank" href="https://holithemes.com/wp-chatbot/customer-chat-change-messenger-position/"><?php _e( 'more info' , 'wp-chatbot' ) ?></a> </p>
        <!-- <p class="description">Facebook Recommends not to change the position. <a target="_blank" href="https://holithemes.com/wp-chatbot/change-position-messenger-customer-chat/"><?php _e( 'more info' , 'wp-chatbot' ) ?></a> </p> -->
        <!-- <p class="description">Facebook Recommends not to change the position, and not provided API to change the position, If FB provide a better way, we may remove this feature and update with the new way. </p> -->
        <!-- <p class="description">Facebook Recommends not to change the position </p> -->
        <br>
        
        


        <!-- Messenger Position Mobile -->

        <ul class="collapsible" data-collapsible="accordion">
        <li>
        <div id="style-6" class="collapsible-header">Mobile</div>
        <div class="collapsible-body">


        <p class="description"><b><?php _e( 'Mobile: ' , 'wp-chatbot' ) ?> </b></p>
        <br>
        <p class="description">In Mobile Devices, its recommend to change Greeting Dialog only from bottom-left or bottom-right </p>
        <br>

        <?php

        // enable - Change Messenger Positions - Mobile
        if ( isset( $options['cc_enable_mobile'] ) ) {
            ?>
            <p>
                <label>
                    <input name="htcc_m_position[cc_enable_mobile]" type="checkbox" value="1" <?php checked( $options['cc_enable_mobile'], 1 ); ?> id="cc_enable_mobile" />
                    <span>Enable - to change positions on Mobile Devices</span>
                </label>
            </p>
            <?php
        } else {
            ?>
            <p>
                <label>
                    <input name="htcc_m_position[cc_enable_mobile]" type="checkbox" value="1" id="cc_enable_mobile" />
                    <span>Enable - to change positions on Mobile Devices</span>
                </label>
            </p>
            <?php
        }

        ?>
        <p class="description"><?php _e( 'Enable to change positions of Messenger Icon, Greetings Dilog with Chat Window on Mobile Devices ' , 'wp-chatbot' ) ?> </p>
        <br>
        
        <!-- select icon position - mobile -->
        <!-- <p class="description"><b><?php _e( 'Icon' , 'wp-chatbot' ) ?></b></p> <br> -->

        <div class="row">
            <div class="input-field col s12">
                <select name="htcc_m_position[cc_i_position_mobile]" class="cc_i_select-mobile">
                <option value="1"  <?php echo $cc_i_position_value_mobile == 1 ? 'SELECTED' : ''; ?> ><?php _e( 'bottom right' , 'wp-chatbot' ) ?></option>
                <option value="2"  <?php echo $cc_i_position_value_mobile == 2 ? 'SELECTED' : ''; ?> ><?php _e( 'bottom left' , 'wp-chatbot' ) ?></option>
                <option value="3"  <?php echo $cc_i_position_value_mobile == 3 ? 'SELECTED' : ''; ?> ><?php _e( 'top left' , 'wp-chatbot' ) ?></option>
                <option value="4"  <?php echo $cc_i_position_value_mobile == 4 ? 'SELECTED' : ''; ?> ><?php _e( 'top right' , 'wp-chatbot' ) ?></option>
                </select>
                <label><?php _e( 'Icon Position - Mobile' , 'wp-chatbot' ) ?></label>
                <p class="description"><?php _e( 'e.g. 18pt, 10% - add css units as suffix ' , 'wp-chatbot' ) ?> </p>
            </div>
        </div>

        <div class="row display-none cc_i_position-mobile cc_i_position-1-mobile bottom-right">
            <div class="input-field col s6">
                <label for="position_bottom_1_mobile"><?php _e( 'position_bottom' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_m_position[cc_i_mobile_p1_bottom]" value="<?php echo esc_attr( $options['cc_i_mobile_p1_bottom'] ) ?>" id="position_bottom_1_mobile" type="text" >
            </div>
            <div class="input-field col s6">
                <label for="position_right_1"><?php _e( 'position_right' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_m_position[cc_i_mobile_p1_right]" value="<?php echo esc_attr( $options['cc_i_mobile_p1_right'] ) ?>" id="position_right_1" type="text" >
            </div>
        </div>

        <div class="row display-none cc_i_position-mobile cc_i_position-2-mobile bottom-left">
            <div class="input-field col s6">
                <label for="position_bottom_2_mobile"><?php _e( 'position_bottom' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_m_position[cc_i_mobile_p2_bottom]" value="<?php echo esc_attr( $options['cc_i_mobile_p2_bottom'] ) ?>" id="position_bottom_2_mobile" type="text" >
            </div>
            <div class="input-field col s6">
                <label for="position_left_2"><?php _e( 'position_left' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_m_position[cc_i_mobile_p2_left]" value="<?php echo esc_attr( $options['cc_i_mobile_p2_left'] ) ?>" id="position_left_2" type="text" >
            </div>
        </div>



        <div class="row display-none cc_i_position-mobile cc_i_position-3-mobile top-left">
            <div class="input-field col s6">
                <label for="position_top_3_mobile"><?php _e( 'position_top' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_m_position[cc_i_mobile_p3_top]" value="<?php echo esc_attr( $options['cc_i_mobile_p3_top'] ) ?>" id="position_top_3_mobile" type="text" >
            </div>
            <div class="input-field col s6">
                <label for="position_left_3_mobile"><?php _e( 'position_left' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_m_position[cc_i_mobile_p3_left]" value="<?php echo esc_attr( $options['cc_i_mobile_p3_left'] ) ?>" id="position_left_3_mobile" type="text" >
            </div>
        </div>

        <div class="row display-none cc_i_position-mobile cc_i_position-4-mobile top-right">
            <div class="input-field col s6">
                <label for="position_top_4_mobile"><?php _e( 'position_top' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_m_position[cc_i_mobile_p4_top]" value="<?php echo esc_attr( $options['cc_i_mobile_p4_top'] ) ?>" id="position_top_4_mobile" type="text" >
            </div>
            <div class="input-field col s6">
                <label for="position_right_4_mobile"><?php _e( 'position_right' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_m_position[cc_i_mobile_p4_right]" value="<?php echo esc_attr( $options['cc_i_mobile_p4_right'] ) ?>" id="position_right_4_mobile" type="text" >
            </div>
        </div>




        <!-- select Greetings Dialog, Chat Window position -->
        <!-- <p class="description"><b><?php _e( 'Greetings Dialog' , 'wp-chatbot' ) ?></b></p> <br> -->

        <div class="row">
            <div class="input-field col s12">
                <select name="htcc_m_position[cc_g_position_mobile]" class="cc_g_select-mobile">
                <option value="1"  <?php echo $cc_g_position_value_mobile == 1 ? 'SELECTED' : ''; ?> ><?php _e( 'bottom right' , 'wp-chatbot' ) ?></option>
                <option value="2"  <?php echo $cc_g_position_value_mobile == 2 ? 'SELECTED' : ''; ?> ><?php _e( 'bottom left' , 'wp-chatbot' ) ?></option>
                <option value="3"  <?php echo $cc_g_position_value_mobile == 3 ? 'SELECTED' : ''; ?> ><?php _e( 'top left' , 'wp-chatbot' ) ?></option>
                <option value="4"  <?php echo $cc_g_position_value_mobile == 4 ? 'SELECTED' : ''; ?> ><?php _e( 'top right' , 'wp-chatbot' ) ?></option>
                </select>
                <label><?php _e( 'Greetings Dialog and Chat Window Position - Mobile' , 'wp-chatbot' ) ?></label>
                <p class="description"><?php _e( ' e.g. 63pt - add css units as suffix' , 'wp-chatbot' ) ?> </p>
            </div>
        </div>

        <div class="row display-none cc_g_position-mobile cc_g_position-1-mobile bottom-right">
            <div class="input-field col s6">
                <label for="position_bottom_1_mobile"><?php _e( 'position_bottom' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_m_position[cc_g_mobile_p1_bottom]" value="<?php echo esc_attr( $options['cc_g_mobile_p1_bottom'] ) ?>" id="position_bottom_1_mobile" type="text" >
            </div>
            <div class="input-field col s6">
                <label for="position_right_1"><?php _e( 'position_right' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_m_position[cc_g_mobile_p1_right]" value="<?php echo esc_attr( $options['cc_g_mobile_p1_right'] ) ?>" id="position_right_1" type="text" >
            </div>
        </div>

        <div class="row display-none cc_g_position-mobile cc_g_position-2-mobile bottom-left">
            <div class="input-field col s6">
                <label for="position_bottom_2_mobile"><?php _e( 'position_bottom' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_m_position[cc_g_mobile_p2_bottom]" value="<?php echo esc_attr( $options['cc_g_mobile_p2_bottom'] ) ?>" id="position_bottom_2_mobile" type="text" >
            </div>
            <div class="input-field col s6">
                <label for="position_left_2"><?php _e( 'position_left' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_m_position[cc_g_mobile_p2_left]" value="<?php echo esc_attr( $options['cc_g_mobile_p2_left'] ) ?>" id="position_left_2" type="text" >
            </div>
        </div>



        <div class="row display-none cc_g_position-mobile cc_g_position-3-mobile top-left">
            <div class="input-field col s6">
                <label for="position_top_3_mobile"><?php _e( 'position_top' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_m_position[cc_g_mobile_p3_top]" value="<?php echo esc_attr( $options['cc_g_mobile_p3_top'] ) ?>" id="position_top_3_mobile" type="text" >
            </div>
            <div class="input-field col s6">
                <label for="position_left_3_mobile"><?php _e( 'position_left' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_m_position[cc_g_mobile_p3_left]" value="<?php echo esc_attr( $options['cc_g_mobile_p3_left'] ) ?>" id="position_left_3_mobile" type="text" >
            </div>
        </div>

        <div class="row display-none cc_g_position-mobile cc_g_position-4-mobile top-right">
            <div class="input-field col s6">
                <label for="position_top_4_mobile"><?php _e( 'position_top' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_m_position[cc_g_mobile_p4_top]" value="<?php echo esc_attr( $options['cc_g_mobile_p4_top'] ) ?>" id="position_top_4_mobile" type="text" >
            </div>
            <div class="input-field col s6">
                <label for="position_right_4_mobile"><?php _e( 'position_right' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_m_position[cc_g_mobile_p4_right]" value="<?php echo esc_attr( $options['cc_g_mobile_p4_right'] ) ?>" id="position_right_4_mobile" type="text" >
            </div>
        </div>

        </div>
        </div>
        </li>
        </ul>



        <!-- Messenger Position Desktop -->

        <ul class="collapsible" data-collapsible="accordion">
        <li>
        <div id="style-6" class="collapsible-header">Desktop</div>
        <div class="collapsible-body">


        <p class="description"><b><?php _e( 'Desktop: ' , 'wp-chatbot' ) ?> </b></p>
        <br>
        <?php

        // enable - Change Messenger Positions - Desktop
        if ( isset( $options['cc_enable'] ) ) {
            ?>
            <p>
                <label>
                    <input name="htcc_m_position[cc_enable]" type="checkbox" value="1" <?php checked( $options['cc_enable'], 1 ); ?> id="cc_enable" />
                    <span>Enable - to change positions on Desktop Devices</span>
                </label>
            </p>
            <?php
        } else {
            ?>
            <p>
                <label>
                    <input name="htcc_m_position[cc_enable]" type="checkbox" value="1" id="cc_enable" />
                    <span>Enable - to change positions on Desktop Devices</span>
                </label>
            </p>
            <?php
        }

        ?>
        <p class="description"><?php _e( 'Enable to change positions of Messenger Icon, Greetings Dilog with Chat Window on Desktop Devices ' , 'wp-chatbot' ) ?> </p>
        <br>
        
        <!-- select icon position -->
        <!-- <p class="description"><b><?php _e( 'Icon' , 'wp-chatbot' ) ?></b></p> <br> -->

        <div class="row">
            <div class="input-field col s12">
                <select name="htcc_m_position[cc_i_position]" class="cc_i_select">
                <option value="1"  <?php echo $cc_i_position_value == 1 ? 'SELECTED' : ''; ?> ><?php _e( 'bottom right' , 'wp-chatbot' ) ?></option>
                <option value="2"  <?php echo $cc_i_position_value == 2 ? 'SELECTED' : ''; ?> ><?php _e( 'bottom left' , 'wp-chatbot' ) ?></option>
                <option value="3"  <?php echo $cc_i_position_value == 3 ? 'SELECTED' : ''; ?> ><?php _e( 'top left' , 'wp-chatbot' ) ?></option>
                <option value="4"  <?php echo $cc_i_position_value == 4 ? 'SELECTED' : ''; ?> ><?php _e( 'top right' , 'wp-chatbot' ) ?></option>
                </select>
                <label><?php _e( 'Icon Position' , 'wp-chatbot' ) ?></label>
                <p class="description"><?php _e( 'e.g. 18pt, 10% - add css units as suffix ' , 'wp-chatbot' ) ?> </p>
            </div>
        </div>

        <div class="row display-none cc_i_position cc_i_position-1 bottom-right">
            <div class="input-field col s6">
                <label for="position_bottom_1"><?php _e( 'position_bottom' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_m_position[cc_i_p1_bottom]" value="<?php echo esc_attr( $options['cc_i_p1_bottom'] ) ?>" id="position_bottom_1" type="text" >
            </div>
            <div class="input-field col s6">
                <label for="position_right_1"><?php _e( 'position_right' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_m_position[cc_i_p1_right]" value="<?php echo esc_attr( $options['cc_i_p1_right'] ) ?>" id="position_right_1" type="text" >
            </div>
        </div>

        <div class="row display-none cc_i_position cc_i_position-2 bottom-left">
            <div class="input-field col s6">
                <label for="position_bottom_2"><?php _e( 'position_bottom' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_m_position[cc_i_p2_bottom]" value="<?php echo esc_attr( $options['cc_i_p2_bottom'] ) ?>" id="position_bottom_2" type="text" >
            </div>
            <div class="input-field col s6">
                <label for="position_left_2"><?php _e( 'position_left' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_m_position[cc_i_p2_left]" value="<?php echo esc_attr( $options['cc_i_p2_left'] ) ?>" id="position_left_2" type="text" >
            </div>
        </div>



        <div class="row display-none cc_i_position cc_i_position-3 top-left">
            <div class="input-field col s6">
                <label for="position_top_3"><?php _e( 'position_top' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_m_position[cc_i_p3_top]" value="<?php echo esc_attr( $options['cc_i_p3_top'] ) ?>" id="position_top_3" type="text" >
            </div>
            <div class="input-field col s6">
                <label for="position_left_3"><?php _e( 'position_left' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_m_position[cc_i_p3_left]" value="<?php echo esc_attr( $options['cc_i_p3_left'] ) ?>" id="position_left_3" type="text" >
            </div>
        </div>

        <div class="row display-none cc_i_position cc_i_position-4 top-right">
            <div class="input-field col s6">
                <label for="position_top_4"><?php _e( 'position_top' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_m_position[cc_i_p4_top]" value="<?php echo esc_attr( $options['cc_i_p4_top'] ) ?>" id="position_top_4" type="text" >
            </div>
            <div class="input-field col s6">
                <label for="position_right_4"><?php _e( 'position_right' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_m_position[cc_i_p4_right]" value="<?php echo esc_attr( $options['cc_i_p4_right'] ) ?>" id="position_right_4" type="text" >
            </div>
        </div>




        <!-- select Greetings Dialog, Chat Window position -->
        <!-- <p class="description"><b><?php _e( 'Greetings Dialog' , 'wp-chatbot' ) ?></b></p> <br> -->

        <div class="row">
            <div class="input-field col s12">
                <select name="htcc_m_position[cc_g_position]" class="cc_g_select">
                <option value="1"  <?php echo $cc_g_position_value == 1 ? 'SELECTED' : ''; ?> ><?php _e( 'bottom right' , 'wp-chatbot' ) ?></option>
                <option value="2"  <?php echo $cc_g_position_value == 2 ? 'SELECTED' : ''; ?> ><?php _e( 'bottom left' , 'wp-chatbot' ) ?></option>
                <option value="3"  <?php echo $cc_g_position_value == 3 ? 'SELECTED' : ''; ?> ><?php _e( 'top left' , 'wp-chatbot' ) ?></option>
                <option value="4"  <?php echo $cc_g_position_value == 4 ? 'SELECTED' : ''; ?> ><?php _e( 'top right' , 'wp-chatbot' ) ?></option>
                </select>
                <label><?php _e( 'Greetings Dialog and Chat Window Position' , 'wp-chatbot' ) ?></label>
                <p class="description"><?php _e( ' e.g. 63pt - add css units as suffix' , 'wp-chatbot' ) ?> </p>
            </div>
        </div>

        <div class="row display-none cc_g_position cc_g_position-1 bottom-right">
            <div class="input-field col s6">
                <label for="position_bottom_1"><?php _e( 'position_bottom' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_m_position[cc_g_p1_bottom]" value="<?php echo esc_attr( $options['cc_g_p1_bottom'] ) ?>" id="position_bottom_1" type="text" >
            </div>
            <div class="input-field col s6">
                <label for="position_right_1"><?php _e( 'position_right' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_m_position[cc_g_p1_right]" value="<?php echo esc_attr( $options['cc_g_p1_right'] ) ?>" id="position_right_1" type="text" >
            </div>
        </div>

        <div class="row display-none cc_g_position cc_g_position-2 bottom-left">
            <div class="input-field col s6">
                <label for="position_bottom_2"><?php _e( 'position_bottom' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_m_position[cc_g_p2_bottom]" value="<?php echo esc_attr( $options['cc_g_p2_bottom'] ) ?>" id="position_bottom_2" type="text" >
            </div>
            <div class="input-field col s6">
                <label for="position_left_2"><?php _e( 'position_left' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_m_position[cc_g_p2_left]" value="<?php echo esc_attr( $options['cc_g_p2_left'] ) ?>" id="position_left_2" type="text" >
            </div>
        </div>



        <div class="row display-none cc_g_position cc_g_position-3 top-left">
            <div class="input-field col s6">
                <label for="position_top_3"><?php _e( 'position_top' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_m_position[cc_g_p3_top]" value="<?php echo esc_attr( $options['cc_g_p3_top'] ) ?>" id="position_top_3" type="text" >
            </div>
            <div class="input-field col s6">
                <label for="position_left_3"><?php _e( 'position_left' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_m_position[cc_g_p3_left]" value="<?php echo esc_attr( $options['cc_g_p3_left'] ) ?>" id="position_left_3" type="text" >
            </div>
        </div>

        <div class="row display-none cc_g_position cc_g_position-4 top-right">
            <div class="input-field col s6">
                <label for="position_top_4"><?php _e( 'position_top' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_m_position[cc_g_p4_top]" value="<?php echo esc_attr( $options['cc_g_p4_top'] ) ?>" id="position_top_4" type="text" >
            </div>
            <div class="input-field col s6">
                <label for="position_right_4"><?php _e( 'position_right' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_m_position[cc_g_p4_right]" value="<?php echo esc_attr( $options['cc_g_p4_right'] ) ?>" id="position_right_4" type="text" >
            </div>
        </div>

        </div>
        </div>
        </li>
        </ul>

        <?php
    }

    //  Custom Image
    // ci - custom image
    function custom_image_cb() {
        $options = get_option('htcc_ci');
        $ci_position_value = esc_attr( $options['ci_position'] );
        $ci_position_value_mobile = esc_attr( $options['ci_position_mobile'] );

        ?>
        <p class="description"><?php _e( 'Custom Image at custom position - ' , 'wp-chatbot' ) ?><a target="_blank" href="https://holithemes.com/wp-chatbot/custom-image/"><?php _e( 'more info' , 'wp-chatbot' ) ?></a> </p>
        <p class="description">This feature is not to change the original Messenger position, this feature adds another image at given position. When the user clicks on that image, 
        <br> Messenger will load at the original position or at the given position at "Change Messenger Position" Settings  </p>
        <br>
        
        <ul class="collapsible" data-collapsible="accordion">
        <li>
        <div id="style-6" class="collapsible-header">Custom Image at Custom Position</div>
        <div class="collapsible-body">

        <!-- Mobile -->
        <p class="description"><b><?php _e( 'Mobile: ' , 'wp-chatbot' ) ?> </b></p>
        <br>

        <?php

        // enable - custom image
        if ( isset( $options['ci_enable_mobile'] ) ) {
            ?>
            <p>
                <label>
                    <input name="htcc_ci[ci_enable_mobile]" type="checkbox" value="1" <?php checked( $options['ci_enable_mobile'], 1 ); ?> id="ci_enable_mobile" />
                    <span>Enable ( Display custom Image, Instead of Default Messenger Icon )</span>
                </label>
            </p>
            <?php
        } else {
            ?>
            <p>
                <label>
                    <input name="htcc_ci[ci_enable_mobile]" type="checkbox" value="1" id="ci_enable_mobile" />
                    <span>Enable ( Display custom Image, Instead of Default Messenger Icon )</span>
                </label>
            </p>
            <?php
        }

        ?>
        <p class="description"><?php _e( 'If Enabled, Default Messenger will load when user clicks on custom Image or based on settings "when to load messenger"  ' , 'wp-chatbot' ) ?> </p>
        <br>
        

        <div class="row">
            <div class="input-field col s12">
                <select name="htcc_ci[ci_position_mobile]" class="select-mobile">
                <option value="1"  <?php echo $ci_position_value_mobile == 1 ? 'SELECTED' : ''; ?> ><?php _e( 'bottom right' , 'wp-chatbot' ) ?></option>
                <option value="2"  <?php echo $ci_position_value_mobile == 2 ? 'SELECTED' : ''; ?> ><?php _e( 'bottom left' , 'wp-chatbot' ) ?></option>
                <option value="3"  <?php echo $ci_position_value_mobile == 3 ? 'SELECTED' : ''; ?> ><?php _e( 'top left' , 'wp-chatbot' ) ?></option>
                <option value="4"  <?php echo $ci_position_value_mobile == 4 ? 'SELECTED' : ''; ?> ><?php _e( 'top right' , 'wp-chatbot' ) ?></option>
                </select>
                <label><?php _e( 'Fixed position to place - Mobile' , 'wp-chatbot' ) ?></label>
                <p class="description"><?php _e( ' e.g. 10px - please add css units as suffix, e.g. 10px, 10%, 10rem, 10em' , 'wp-chatbot' ) ?> </p>
            </div>
        </div>

        <div class="row display-none ci_position-mobile ci_position-1-mobile bottom-right">
            <div class="input-field col s6">
                <label for="position_bottom_1_mobile"><?php _e( 'position_bottom' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_ci[mobile_p1_bottom]" value="<?php echo esc_attr( $options['mobile_p1_bottom'] ) ?>" id="position_bottom_1_mobile" type="text" >
            </div>
            <div class="input-field col s6">
                <label for="position_right_1"><?php _e( 'position_right' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_ci[mobile_p1_right]" value="<?php echo esc_attr( $options['mobile_p1_right'] ) ?>" id="position_right_1" type="text" >
            </div>
        </div>

        <div class="row display-none ci_position-mobile ci_position-2-mobile bottom-left">
            <div class="input-field col s6">
                <label for="position_bottom_2_mobile"><?php _e( 'position_bottom' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_ci[mobile_p2_bottom]" value="<?php echo esc_attr( $options['mobile_p2_bottom'] ) ?>" id="position_bottom_2_mobile" type="text" >
            </div>
            <div class="input-field col s6">
                <label for="position_left_2"><?php _e( 'position_left' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_ci[mobile_p2_left]" value="<?php echo esc_attr( $options['mobile_p2_left'] ) ?>" id="position_left_2" type="text" >
            </div>
        </div>



        <div class="row display-none ci_position-mobile ci_position-3-mobile top-left">
            <div class="input-field col s6">
                <label for="position_top_3_mobile"><?php _e( 'position_top' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_ci[mobile_p3_top]" value="<?php echo esc_attr( $options['mobile_p3_top'] ) ?>" id="position_top_3_mobile" type="text" >
            </div>
            <div class="input-field col s6">
                <label for="position_left_3_mobile"><?php _e( 'position_left' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_ci[mobile_p3_left]" value="<?php echo esc_attr( $options['mobile_p3_left'] ) ?>" id="position_left_3_mobile" type="text" >
            </div>
        </div>

        <div class="row display-none ci_position-mobile ci_position-4-mobile top-right">
            <div class="input-field col s6">
                <label for="position_top_4_mobile"><?php _e( 'position_top' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_ci[mobile_p4_top]" value="<?php echo esc_attr( $options['mobile_p4_top'] ) ?>" id="position_top_4_mobile" type="text" >
            </div>
            <div class="input-field col s6">
                <label for="position_right_4_mobile"><?php _e( 'position_right' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_ci[mobile_p4_right]" value="<?php echo esc_attr( $options['mobile_p4_right'] ) ?>" id="position_right_4_mobile" type="text" >
            </div>
        </div>


        <!-- Desktop -->
        <p class="description"><b><?php _e( 'Desktop: ' , 'wp-chatbot' ) ?> </b></p>
        <br>
        <?php

        // enable - custom image
        if ( isset( $options['ci_enable'] ) ) {
            ?>
            <p>
                <label>
                    <input name="htcc_ci[ci_enable]" type="checkbox" value="1" <?php checked( $options['ci_enable'], 1 ); ?> id="ci_enable" />
                    <span>Enable ( Display custom Image, Instead of Default Messenger Icon )</span>
                </label>
            </p>
            <?php
        } else {
            ?>
            <p>
                <label>
                    <input name="htcc_ci[ci_enable]" type="checkbox" value="1" id="enable_update_greetings" />
                    <span>Enable ( Display custom Image, Instead of Default Messenger Icon )</span>
                </label>
            </p>
            <?php
        }

        ?>
        <p class="description"><?php _e( 'If Enabled, Default Messenger will load when user clicks on custom Image or based on settings "when to load messenger"  ' , 'wp-chatbot' ) ?> </p>
        <br>


        <!-- Image position -->
        <div class="row">
            <div class="input-field col s12">
                <select name="htcc_ci[ci_position]" class="select">
                <option value="1"  <?php echo $ci_position_value == 1 ? 'SELECTED' : ''; ?> ><?php _e( 'bottom right' , 'wp-chatbot' ) ?></option>
                <option value="2"  <?php echo $ci_position_value == 2 ? 'SELECTED' : ''; ?> ><?php _e( 'bottom left' , 'wp-chatbot' ) ?></option>
                <option value="3"  <?php echo $ci_position_value == 3 ? 'SELECTED' : ''; ?> ><?php _e( 'top left' , 'wp-chatbot' ) ?></option>
                <option value="4"  <?php echo $ci_position_value == 4 ? 'SELECTED' : ''; ?> ><?php _e( 'top right' , 'wp-chatbot' ) ?></option>
                </select>
                <label><?php _e( 'Fixed position to place - Desktop' , 'wp-chatbot' ) ?></label>
                <p class="description"><?php _e( ' e.g. 10px - please add css units as suffix, e.g. 10px, 10%, 10rem, 10em' , 'wp-chatbot' ) ?> </p>
            </div>
        </div>

        <div class="row display-none ci_position ci_position-1 bottom-right">
            <div class="input-field col s6">
                <label for="position_bottom_1"><?php _e( 'position_bottom' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_ci[p1_bottom]" value="<?php echo esc_attr( $options['p1_bottom'] ) ?>" id="position_bottom_1" type="text" >
            </div>
            <div class="input-field col s6">
                <label for="position_right_1"><?php _e( 'position_right' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_ci[p1_right]" value="<?php echo esc_attr( $options['p1_right'] ) ?>" id="position_right_1" type="text" >
            </div>
        </div>

        <div class="row display-none ci_position ci_position-2 bottom-left">
            <div class="input-field col s6">
                <label for="position_bottom_2"><?php _e( 'position_bottom' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_ci[p2_bottom]" value="<?php echo esc_attr( $options['p2_bottom'] ) ?>" id="position_bottom_2" type="text" >
            </div>
            <div class="input-field col s6">
                <label for="position_left_2"><?php _e( 'position_left' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_ci[p2_left]" value="<?php echo esc_attr( $options['p2_left'] ) ?>" id="position_left_2" type="text" >
            </div>
        </div>



        <div class="row display-none ci_position ci_position-3 top-left">
            <div class="input-field col s6">
                <label for="position_top_3"><?php _e( 'position_top' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_ci[p3_top]" value="<?php echo esc_attr( $options['p3_top'] ) ?>" id="position_top_3" type="text" >
            </div>
            <div class="input-field col s6">
                <label for="position_left_3"><?php _e( 'position_left' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_ci[p3_left]" value="<?php echo esc_attr( $options['p3_left'] ) ?>" id="position_left_3" type="text" >
            </div>
        </div>

        <div class="row display-none ci_position ci_position-4 top-right">
            <div class="input-field col s6">
                <label for="position_top_4_mobile"><?php _e( 'position_top' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_ci[p4_top]" value="<?php echo esc_attr( $options['p4_top'] ) ?>" id="position_top_4_mobile" type="text" >
            </div>
            <div class="input-field col s6">
                <label for="position_right_4"><?php _e( 'position_right' , 'wp-chatbot' ) ?>: </label>
                <input name="htcc_ci[p4_right]" value="<?php echo esc_attr( $options['p4_right'] ) ?>" id="position_right_4" type="text" >
            </div>
        </div>

        <br>
        <p class="description"><b><?php _e( 'Image: ' , 'wp-chatbot' ) ?> </b></p>
        <br>

        <!-- Image url -->
        <!-- for perfomance reason - load one image on both devices ..  -->
        <div class="row">
            <div class="input-field col s12">
                <input type="text" name="htcc_ci[ci_img_url]" id="ci_img_url" value="<?php echo esc_attr( $options['ci_img_url'] ) ?>">
                <label for="ci_img_url">Image URL</label>
                <p class="description"><?php _e( 'If blank, Pre-defined custom image will load' , 'wp-chatbot' ) ?> </p>
            </div>
        </div>

        <br>
        <p class="description"><?php _e( 'Instead on adding Image - Height, Width and Border Radius - add exact size image' , 'wp-chatbot' ) ?> </p>
        <br>

        <!-- Image Height -->
        <div class="row">
            <div class="input-field col s10 m6">
                <input type="text" name="htcc_ci[ci_img_height]" id="ci_img_height" value="<?php echo esc_attr( $options['ci_img_height'] ) ?>">
                <label for="ci_img_height">Image Height</label>
                <p class="description"><?php _e( 'Image Height - e.g. 50px' , 'wp-chatbot' ) ?> </p>
            </div>
        </div>


        <!-- Image Width -->
        <div class="row">
            <div class="input-field col s10 m6">
                <input type="text" name="htcc_ci[ci_img_width]" id="ci_img_width" value="<?php echo esc_attr( $options['ci_img_width'] ) ?>">
                <label for="ci_img_width">Image Width</label>
                <p class="description"><?php _e( 'Image Width - e.g. 50px' , 'wp-chatbot' ) ?> </p>
            </div>
        </div>

        <!-- Image border radius -->
        <div class="row">
            <div class="input-field col s10 m6">
                <input type="text" name="htcc_ci[ci_img_border]" id="ci_img_border" value="<?php echo esc_attr( $options['ci_img_border'] ) ?>">
                <label for="ci_img_border">Image border radius</label>
                <p class="description"><?php _e( 'Image Border Radius - e.g. 5px, 50%' , 'wp-chatbot' ) ?> </p>
            </div>
        </div>
        
        </div>
        </div>
        </li>
        </ul>


        <?php
    }






    // woocommerce enable ..
    function enable_woo_cb() {
        $options = get_option('htcc_pro_features');


        if ( isset( $options['enable_woo'] ) ) {
            ?>
            <p>
                <label>
                    <input name="htcc_pro_features[enable_woo]" type="checkbox" value="1" <?php checked( $options['enable_woo'], 1 ); ?> id="enable_woo" />
                    <span>Enable WooCommerce features</span>
                </label>
            </p>
            <?php
        } else {
            ?>
            <p>
                <label>
                    <input name="htcc_pro_features[enable_woo]" type="checkbox" value="1" id="enable_woo" />
                    <span>Enable WooCommerce features</span>
                </label>
            </p>
            <?php
        }
        ?>
        <p class="description"> <?php _e( 'Enable WooCommerce features' , 'wp-chatbot' ) ?> - <a target="_blank" href="https://www.holithemes.com/wp-chatbot/woocommerce/"><?php _e( 'more info' , 'wp-chatbot' ) ?></a> </p>
        <?php
    }






    //  Hide on this days
    function hide_on_days_cb() {
        $options = get_option('htcc_pro_features');
        ?>
        <p class="description">Hide on Time Range, Hide on Days in a Week - works based on WordPress Timezone Settings. <br> Current Site Time: <code><?php echo current_time( 'mysql' ); ?></code> ( Settings -> General - Timezone )</p>
        <br>
        <div class="row">
            <div class="input-field col s12">
                <input name="htcc_pro_features[hide_on_days]" value="<?php echo esc_attr( $options['hide_on_days'] ) ?>" id="hide_on_days" type="text" >
                <label for="hide_on_days"><?php _e( 'Hide on this days' , 'wp-chatbot' ) ?> </label>
                <p class="description"><?php _e( 'Hide on this days, leave blank to display on all days, add multiple days separate with comma ( , )' , 'wp-chatbot' ) ?> </p>
                <p class="description"><?php _e( ' 0 = Sunday, 1 = Monday, 6 = Saturday.  - e.g.  to hide on Saturday, Sunday add 0, 6' , 'wp-chatbot' ) ?> - <a target="_blank" href="https://www.holithemes.com/wp-chatbot/hide-on-this-days/"><?php _e( 'more info' , 'wp-chatbot' ) ?></a> </p>
            </div>
        </div>
        <?php
    }




    // Hide on this time range ..
    function hide_on_time_range_cb() {
        $options = get_option('htcc_pro_features');
        ?>

        <div class="row">
            
            <!-- start time -->
            <div class="input-field col s6">
                <input name="htcc_pro_features[hide_time_start]" value="<?php echo esc_attr( $options['hide_time_start'] ) ?>" type="text" class="timepicker" id="start_time">
                <label for="start_time">Start time</label>
            </div>
            
            <!-- End time -->
            <div class="input-field col s6">
                <input name="htcc_pro_features[hide_time_end]" value="<?php echo esc_attr( $options['hide_time_end'] ) ?>" type="text" class="timepicker" id="end_time">
                <label for="end_time">End time</label>
            </div>

            <p class="description"><?php _e( 'Hide Messenger on this time range' , 'wp-chatbot' ) ?>- <a target="_blank" href="https://www.holithemes.com/wp-chatbot/hide-on-time-range"><?php _e( 'more info' , 'wp-chatbot' ) ?></a>  </p>
            <p class="description"><?php _e( 'Leave blank to display all the time' , 'wp-chatbot' ) ?> </p>
            <p class="description"><?php _e( 'e.g. Start time - 18:20, End time 08:00 - Hide Messenger from time rage today 06:20PM to tomorrow 08:00AM' , 'wp-chatbot' ) ?> </p>
        </div>
        
        <?php
    }




    // Detect Device
    function detect_device_cb() {
        $options = get_option('htcc_pro_features');
        $value = esc_attr( $options['detect_device'] );
        ?>
        <select name="htcc_pro_features[detect_device]" class="select-2">
        <option value="php" <?php echo $value == 'php' ? 'SELECTED' : ''; ?> >HTTP User Agent</option>
        <option value="screen_width" <?php echo $value == 'screen_width' ? 'SELECTED' : ''; ?> >Screen Size</option>
        </select>
        <p class="description"> <?php _e( 'Detect Device ( Mobile, Desktop ) - Hide based on Device, When to load Messenger - uses this values' , 'wp-chatbot' ) ?> - <a target="_blank" href="https://www.holithemes.com/wp-chatbot/detect-device"><?php _e( 'more info' , 'wp-chatbot' ) ?></a> </p>
        <?php
    }


    // Screen Width for mobile
    function mobile_screen_width_cb() {
        $options = get_option('htcc_pro_features');
        ?>
        <span id="max-screen-width"></span>
        <input name="htcc_pro_features[mobile_screen_width]" value="<?php echo esc_attr( $options['mobile_screen_width'] ) ?>" id="mobile_screen_width" type="text" class="input-margin">
        <p class="description"><?php _e( 'If "Device Detect" is "Screen size" ' , 'wp-chatbot' ) ?>  </p>
        <p class="description"> <?php _e( 'Max Screen Width of Mobile device, e.g. 1024 ' , 'wp-chatbot' ) ?> - <a target="_blank" href="https://www.holithemes.com/wp-chatbot/screen-width-for-mobile"><?php _e( 'more info' , 'wp-chatbot' ) ?></a> </p>
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



$admin_htcc_pro_features = new Admin_HTCC_Pro_Features();
add_action('admin_menu', array($admin_htcc_pro_features, 'menu') );
add_action('admin_init', array($admin_htcc_pro_features, 'settings') );


endif; // END class_exists check