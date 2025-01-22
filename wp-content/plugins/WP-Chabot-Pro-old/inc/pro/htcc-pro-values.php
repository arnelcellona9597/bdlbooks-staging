<?php
/**
 * template -  update greetings, ref .. based on placeholders, title .. 
 * call this after woocommerce update values .. 
 * 
 * {{product}}  - for single product page - to get product name
 * {{title}} - title name - modified - use in REF 
 * 
 * @uses htcc-chatbot.php
 *  adds in customer chat code ..  
 *      fb_greeting_login
 *      fb_greeting_logout
 *      fb_ref
 * 
 * localize scripts - passes values  - htcc_values
 * 
 * @package htcc
 * @subpackage pro
 * 
 * @source class-htcc-chatbot.php
 * 
 */

if ( ! defined( 'ABSPATH' ) ) exit; 

$is_sdk_added = $this->sdk_added;

$shortcode_added = $this->sdk_added_for_shortcode;

$product_title = "";

// update greetings with product name -  woocommerce single product pages
if ( function_exists( 'is_woocommerce' )  ) {
    if ( is_product() ) {
        $product_title = wc_get_product()->get_title();
    }
}



$fb_greeting_login = str_replace( '{{product}}', $product_title, $fb_greeting_login );
$fb_greeting_logout = str_replace( '{{product}}', $product_title, $fb_greeting_logout );


// ref - placehoders .. {{product}}, {{title}}
// for ref -  remove spaces and adds '-' in page, product title

$ref_title = "";
$ref_id = "";

if ( is_home() || is_front_page() ) {
    $ref_title = 'Home';
    $ref_id = 'Home';
} else if ( is_singular() )  {
    $ref_title = get_the_title();
    $ref_id = get_the_ID();
} else if ( is_404() )  {
    $ref_title = '404-page';
    $ref_id = '404-page';
} else if ( is_archive() ) {
    $ref_title = get_the_archive_title();
    $ref_id = 'archive';
}

$ref_title =  str_replace( ' ', '-', $ref_title );
$ref_title =  str_replace( ':', '', $ref_title );

$ref_product =  str_replace( ' ', '-', $product_title );

$fb_ref = str_replace( '{{product}}', $ref_product, $fb_ref );
$fb_ref = str_replace( '{{title}}', $ref_title, $fb_ref );
$fb_ref = str_replace( '{{id}}', $ref_id, $fb_ref );



/**
 * insted of getting produt name in other place ..  localize from here .. 
 * placeholders, woo .. related variables .. localize here .. 
 * htcc_var  - another vairable added in htcc-pro-enqueue.php 
 * 
 * greeting_login, greeting_logout, ref  -  to add directly to customer chat code
 * 
 * Actions may need this to use in REF, Greeting
 *      product  -  to use in greeting
 *      ref_product - to use in ref
 *      ref_title - to use in ref
 */
$htcc_values = array(
    
    'sdk_added' => $is_sdk_added,
    'shortcode_added' => $shortcode_added,

    'page_id' => $fb_page_id,
    'color' => $fb_color,
    'greeting_login' => $fb_greeting_login,
    'greeting_logout' => $fb_greeting_logout,
    'ref' => $fb_ref,
    'greeting_dialog_display' => $fb_greeting_dialog_display,
    'greeting_dialog_delay' => $fb_greeting_dialog_delay,
    
    'product' => $product_title, // use in greetings

    'ref_id' => $ref_id, // use in ref
    'ref_title' => $ref_title,  // use in ref
    'ref_product' => $ref_product, // use in ref
    
);

wp_localize_script( 'htcc_appjs_pro', 'htcc_values', $htcc_values );



$wp_chatbot_log = array(
    
    'product' => $product_title,

    'ref_id' => $ref_id,
    'ref_title' => $ref_title,
    'ref_product' => $ref_product,
    
);

wp_localize_script( 'htcc_appjs_pro', 'wp_chatbot_log', $wp_chatbot_log );