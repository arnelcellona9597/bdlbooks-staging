<?php 
/**
 * Creates sub level menu
 * and options page 
 * 
 * @package ht-cc
 * @subpackage pro woo
 * @since 3.0
 * 
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Admin_HTCC_Pro_Woo' ) ) :

class Admin_HTCC_Pro_Woo {


    // wp-chatbot pro menu
    public function menu() {
        add_submenu_page(
            'wp-chatbot',
            'WP-Chabot pro WooCommerce',
            'WooCommerce',
            'manage_options',
            'wp-chatbot-pro-woo',
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
                        <?php settings_fields( 'htcc_pro_settings_fields_woo' ); ?>
                        <?php do_settings_sections( 'htcc_pro_settings_sections_woo' ) ?>
                        <?php submit_button() ?>
                    </form>
                </div>
                <!-- <div class="col s12 m12 xl6 ht-cc-admin-sidebar">
                </div> -->
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

        register_setting( 'htcc_pro_settings_fields_woo', 'htcc_pro_woo' , array( $this, 'options_sanitize' ) );
        
        add_settings_section( 'ht_fb_all_woocommerce_section', '', array( $this, 'section_cb' ), 'htcc_pro_settings_sections_woo' );
        
        add_settings_field( 'woo_options', __( 'Update Values' , 'wp-chatbot' ), array( $this, 'woo_select_cb' ), 'htcc_pro_settings_sections_woo', 'ht_fb_all_woocommerce_section' );
        add_settings_field( 'fb_page_id', __( 'Facebook Page ID' , 'wp-chatbot' ), array( $this, 'fb_page_id_cb' ), 'htcc_pro_settings_sections_woo', 'ht_fb_all_woocommerce_section' );
        add_settings_field( 'fb_color', __( 'Color' , 'wp-chatbot' ), array( $this, 'fb_color_cb' ), 'htcc_pro_settings_sections_woo', 'ht_fb_all_woocommerce_section' );
        add_settings_field( 'fb_greeting_login', __( 'Logged in Greeting' , 'wp-chatbot' ), array( $this, 'fb_greeting_login_cb' ), 'htcc_pro_settings_sections_woo', 'ht_fb_all_woocommerce_section' );
        add_settings_field( 'fb_greeting_logout', __( 'Logged out Greeting' , 'wp-chatbot' ), array( $this, 'fb_greeting_logout_cb' ), 'htcc_pro_settings_sections_woo', 'ht_fb_all_woocommerce_section' );
        add_settings_field( 'ref', __( 'Ref' , 'wp-chatbot' ), array( $this, 'ref_cb' ), 'htcc_pro_settings_sections_woo', 'ht_fb_all_woocommerce_section' );
        
    }


    // section heading
    function section_cb() {
        echo '<h1>WP-Chatbot - WooCommerce Settings</h1>';
        ?>
        <p class="description"><?php _e( 'Separate Settings for WooCommerce pages - leave fields blank to get the value from plugin main settings ' , 'wp-chatbot' ) ?>  <a target="_blank" href="<?php echo admin_url( 'admin.php?page=wp-chatbot' ); ?>"><?php _e( '( WP-Chatbot )' , 'wp-chatbot' ) ?></a> </p>
        <p class="description"><?php _e( 'Documentation: ' , 'wp-chatbot' ) ?>  <a target="_blank" href="https://www.holithemes.com/wp-chatbot/woocommerce/"><?php _e( 'WP-Chatbot WooCommerce' , 'wp-chatbot' ) ?></a> </p>
        <?php
    }
    
    
    
    // checkboxes - where to run / update the values ..
    public function woo_select_cb() {
        $woo_select = get_option('htcc_pro_woo');
        
        ?>
        <?php
        
        // is_woocommerce()
        if ( isset( $woo_select['is_woocommerce'] ) ) {
            ?>
            <p>
                <label>
                    <input name="htcc_pro_woo[is_woocommerce]" type="checkbox" value="1" <?php checked( $woo_select['is_woocommerce'], 1 ); ?> id="is_woocommerce" />
                    <span><?php _e( 'WooCommerce pages - which uses WooCommerce templates <br> (cart, checkout, some static home page template are standard pages thus are not included).' , 'wp-chatbot' ) ?></span>
                </label>
            </p>
            <?php
        } else {
            ?>
            <p>
                <label>
                    <input name="htcc_pro_woo[is_woocommerce]" type="checkbox" value="1" id="is_woocommerce" />
                    <span><?php _e( 'WooCommerce pages - which uses WooCommerce templates <br> (cart, checkout, some static home page template are standard pages thus are not included).' , 'wp-chatbot' ) ?></span>
                </label>
            </p>
            <?php
        }
        


        // is_shop()
        if ( isset( $woo_select['is_shop'] ) ) {
            ?>
            <p>
                <label>
                    <input name="htcc_pro_woo[is_shop]" type="checkbox" value="1" <?php checked( $woo_select['is_shop'], 1 ); ?> id="is_shop" />
                    <span><?php _e( 'WooCommerce product archive page (shop)' , 'wp-chatbot' ) ?></span>
                </label>
            </p>
            <?php
        } else {
            ?>
            <p>
                <label>
                    <input name="htcc_pro_woo[is_shop]" type="checkbox" value="1" id="is_shop" />
                    <span><?php _e( 'WooCommerce product archive page (shop)' , 'wp-chatbot' ) ?></span>
                </label>
            </p>
            <?php
        }


        // is_product()
        if ( isset( $woo_select['is_product'] ) ) {
            ?>
            <p>
                <label>
                    <input name="htcc_pro_woo[is_product]" type="checkbox" value="1" <?php checked( $woo_select['is_product'], 1 ); ?> id="is_product" />
                    <span><?php _e( 'WooCommerce Single Product Pages' , 'wp-chatbot' ) ?></span>
                </label>
            </p>
            <?php
        } else {
            ?>
            <p>
                <label>
                    <input name="htcc_pro_woo[is_product]" type="checkbox" value="1" id="is_product" />
                    <span><?php _e( 'WooCommerce Single Product Pages' , 'wp-chatbot' ) ?></span>
                </label>
            </p>
            <?php
        }
        ?>
        <p class="description"><?php _e( 'Overwrite the default values from this selected WooCommerce page types ' , 'wp-chatbot' ) ?> </p>
        <p class="description"><?php _e( 'From this checked list - if any one is true for the current page, then the values will update ' , 'wp-chatbot' ) ?> </p>
        <br><br>
        <?php
        
    }



    // page id
    public function fb_page_id_cb() {
        $options = get_option('htcc_pro_woo');
        ?>
        <div class="row">
            <div class="input-field col s10 m9 l9">
                <input type="text" name="htcc_pro_woo[fb_page_id]" id="fb_page_id" value="<?php echo esc_attr( $options['fb_page_id'] ) ?>">
                <label for="fb_page_id"><?php _e( 'Facebook Page ID' , 'ht-click' ) ?></label>
                <p class="description"><?php _e( 'Facebook Page ID - ' , 'wp-chatbot' ) ?><a target="_blank" href="https://holithemes.com/wp-chatbot/find-facebook-page-id/"><?php _e( 'more info' , 'wp-chatbot' ) ?></a> </p>
            </div>
        </div>        
        <?php
    }


    // color - old, new verstion added next
    public function fb_color_cb_old() {

        $options = get_option('htcc_pro_woo');
        ?>
        <div class="row">
            <div class="input-field col s10 m9 l9">
                <input name="htcc_pro_woo[fb_color]" value="<?php echo esc_attr( $options['fb_color'] ) ?>" type="text" class="htcc-color-wp" style="width: 5rem; height: 1.5rem;" >
                <p class="description"><?php _e( 'messenger theme color , leave empty for default color - ' , 'wp-chatbot' ) ?><a target="_blank" href="https://www.holithemes.com/wp-chatbot/messenger-theme-color/"><?php _e( 'more info' , 'wp-chatbot' ) ?></a> </p>
            </div>
        </div>
        <?php
    }


    // color
    public function fb_color_cb() {

        $options = get_option('htcc_pro_woo');
        ?>
        <div class="row">
            <div class="input-field col s10 m9 l9">
                <input name="htcc_pro_woo[fb_color]" value="<?php echo esc_attr( $options['fb_color'] ) ?>" type="color" class="htcc-color-wp" style="width: 5rem; height: 1.5rem;" >
                <p class="description"><?php _e( 'messenger theme color , leave empty for default color - ' , 'wp-chatbot' ) ?><a target="_blank" href="https://www.holithemes.com/wp-chatbot/messenger-theme-color/"><?php _e( 'more info' , 'wp-chatbot' ) ?></a> </p>
            </div>
        </div>
        <?php
    }


    // Greeting for logged in user
    public function fb_greeting_login_cb() {

        $options = get_option('htcc_pro_woo');
        ?>
        <div class="row">
            <div class="input-field col s10 m9 l9">
                <input type="text" name="htcc_pro_woo[fb_greeting_login]" id="fb_greeting_login" value="<?php echo esc_attr( $options['fb_greeting_login'] ) ?>">
                <label for="fb_greeting_login">Logged in Greetings</label>
                <p class="description"><?php _e( 'Greetings text - If Facebook logged in the current browser, leave empty for default message - ' , 'wp-chatbot' ) ?><a target="_blank" href="https://www.holithemes.com/wp-chatbot/change-facebook-messenger-greetings-text/"><?php _e( 'more info' , 'wp-chatbot' ) ?></a> </p>
            </div>
        </div>
        <?php
    }

    // Greeting for logged out user
    public function fb_greeting_logout_cb() {

        $options = get_option('htcc_pro_woo');
        ?>
        <div class="row">
            <div class="input-field col s10 m9 l9">
                <input type="text" name="htcc_pro_woo[fb_greeting_logout]" id="fb_greeting_logout" value="<?php echo esc_attr( $options['fb_greeting_logout'] ) ?>">
                <label for="fb_greeting_logout">Logged out Greetings</label>
                <p class="description"><?php _e( 'Greetings text - If Facebook logged out in the current browser, leave empty for default message - ' , 'wp-chatbot' ) ?><a target="_blank" href="https://www.holithemes.com/wp-chatbot/change-facebook-messenger-greetings-text/"><?php _e( 'more info' , 'wp-chatbot' ) ?></a> </p>
            </div>
        </div>
        <?php
    }


    // ref 
    public function ref_cb() {

        $options = get_option('htcc_pro_woo');
        ?>
        <div class="row">
            <div class="input-field col s10 m9 l9">
                <input type="text" name="htcc_pro_woo[ref]" id="ref" value="<?php echo esc_attr( $options['ref'] ) ?>">
                <label for="ref">REF</label>
                <p class="description"><?php _e( 'Make your bot start the message from a selected Entry point - ' , 'wp-chatbot' ) ?><a target="_blank" href="https://www.holithemes.com/wp-chatbot/messenger-ref/"><?php _e( 'more info' , 'wp-chatbot' ) ?></a> </p>
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



$admin_htcc_pro_woo = new Admin_HTCC_Pro_Woo();
add_action('admin_menu', array($admin_htcc_pro_woo, 'menu') );
add_action('admin_init', array($admin_htcc_pro_woo, 'settings') );


endif; // END class_exists check