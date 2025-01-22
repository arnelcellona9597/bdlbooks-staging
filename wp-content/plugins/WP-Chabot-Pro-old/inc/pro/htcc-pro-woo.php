<?php
/**
 * template - WooCommerce - update values 
 * 
 * @uses htcc-chatbot.php -> customer_chat() 
 * 
 * @package htcc
 * @subpackage pro
 * 
 */

if ( ! defined( 'ABSPATH' ) ) exit; 


$htcc_pro_woo = get_option( 'htcc_pro_woo' );

$htcc_pro_features = get_option( 'htcc_pro_features' );

$woo_features = '';
if ( isset( $htcc_pro_features['enable_woo'] ) ) {
    $woo_features = 'true';
}


$woo_update = 'false';


// check if woocommerce is activated && if woo features are enabled
if ( function_exists( 'is_woocommerce' )  && ( 'true' == $woo_features )  ) {

    if ( isset( $htcc_pro_woo['is_woocommerce'] ) ) {
        if ( is_woocommerce() ) {
            $woo_update = 'true';
        }
    }
    
    if ( isset( $htcc_pro_woo['is_product'] ) ) {
        if ( is_product() ) {
            $woo_update = 'true';
        }
    }
    
    if ( isset( $htcc_pro_woo['is_shop'] ) ) {
        if ( is_shop() ) {
            $woo_update = 'true';
        }
    }
    
}


$woo_fb_page_id = esc_attr( $htcc_pro_woo['fb_page_id'] );
$woo_fb_color = esc_attr( $htcc_pro_woo['fb_color'] );
$woo_fb_greeting_login = esc_attr( $htcc_pro_woo['fb_greeting_login'] );
$woo_fb_greeting_logout = esc_attr( $htcc_pro_woo['fb_greeting_logout'] );
$woo_fb_ref = esc_attr( $htcc_pro_woo['ref'] );

// update main page option values
if ( 'true' == $woo_update ) {

    if ( '' !== $woo_fb_page_id ) {
        $fb_page_id = $woo_fb_page_id;
    }

    if ( '' !== $woo_fb_color ) {
        $fb_color = $woo_fb_color;
    }

    if ( '' !== $woo_fb_greeting_login ) {
        $fb_greeting_login = $woo_fb_greeting_login;
    }

    if ( '' !== $woo_fb_greeting_logout ) {
        $fb_greeting_logout = $woo_fb_greeting_logout;
    }

    if ( '' !== $woo_fb_ref ) {
        $fb_ref = $woo_fb_ref;
    }


}