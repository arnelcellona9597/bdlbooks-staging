<?php
/**
 * Change Messenger position
 */

if ( ! defined( 'ABSPATH' ) ) exit;


$messenger_position = get_option( 'htcc_m_position' );

$cc_enable = 'no';
$cc_enable_mobile = 'no';

if ( isset( $messenger_position['cc_enable'] ) ) {
    $cc_enable = 'yes';
}

if ( isset( $messenger_position['cc_enable_mobile'] ) ) {
    $cc_enable_mobile = 'yes';
}

// icon - mobile
$cc_i_position_mobile = $messenger_position['cc_i_position_mobile'];

if( 1 == $cc_i_position_mobile ) {
    $cc_i_m_p1 = 'bottom';
    $cc_i_m_p1_value = $messenger_position['cc_i_mobile_p1_bottom'];
    $cc_i_m_p2 = 'right';
    $cc_i_m_p2_value = $messenger_position['cc_i_mobile_p1_right'];
} elseif( 2 == $cc_i_position_mobile ) {
    $cc_i_m_p1 = 'bottom';
    $cc_i_m_p1_value = $messenger_position['cc_i_mobile_p2_bottom'];
    $cc_i_m_p2 = 'left';
    $cc_i_m_p2_value = $messenger_position['cc_i_mobile_p2_left'];
} elseif( 3 == $cc_i_position_mobile ) {
    $cc_i_m_p1 = 'top';
    $cc_i_m_p1_value = $messenger_position['cc_i_mobile_p3_top'];
    $cc_i_m_p2 = 'left';
    $cc_i_m_p2_value = $messenger_position['cc_i_mobile_p3_left'];
} elseif( 4 == $cc_i_position_mobile ) {
    $cc_i_m_p1 = 'top';
    $cc_i_m_p1_value = $messenger_position['cc_i_mobile_p4_top'];
    $cc_i_m_p2 = 'right';
    $cc_i_m_p2_value = $messenger_position['cc_i_mobile_p4_right'];
}


// Greetings, Chat Window - mobile
$cc_g_position_mobile = $messenger_position['cc_g_position_mobile'];


if( 1 == $cc_g_position_mobile ) {
    $cc_g_m_p1 = 'bottom';
    $cc_g_m_p1_value = $messenger_position['cc_g_mobile_p1_bottom'];
    $cc_g_m_p2 = 'right';
    $cc_g_m_p2_value = $messenger_position['cc_g_mobile_p1_right'];
} elseif( 2 == $cc_g_position_mobile ) {
    $cc_g_m_p1 = 'bottom';
    $cc_g_m_p1_value = $messenger_position['cc_g_mobile_p2_bottom'];
    $cc_g_m_p2 = 'left';
    $cc_g_m_p2_value = $messenger_position['cc_g_mobile_p2_left'];
} elseif( 3 == $cc_g_position_mobile ) {
    $cc_g_m_p1 = 'top';
    $cc_g_m_p1_value = $messenger_position['cc_g_mobile_p3_top'];
    $cc_g_m_p2 = 'left';
    $cc_g_m_p2_value = $messenger_position['cc_g_mobile_p3_left'];
} elseif( 4 == $cc_g_position_mobile ) {
    $cc_g_m_p1 = 'top';
    $cc_g_m_p1_value = $messenger_position['cc_g_mobile_p4_top'];
    $cc_g_m_p2 = 'right';
    $cc_g_m_p2_value = $messenger_position['cc_g_mobile_p4_right'];
}



// icon - Desktop
$cc_i_position = $messenger_position['cc_i_position'];

if( 1 == $cc_i_position ) {
    $cc_i_p1 = 'bottom';
    $cc_i_p1_value = $messenger_position['cc_i_p1_bottom'];
    $cc_i_p2 = 'right';
    $cc_i_p2_value = $messenger_position['cc_i_p1_right'];
} elseif( 2 == $cc_i_position ) {
    $cc_i_p1 = 'bottom';
    $cc_i_p1_value = $messenger_position['cc_i_p2_bottom'];
    $cc_i_p2 = 'left';
    $cc_i_p2_value = $messenger_position['cc_i_p2_left'];
} elseif( 3 == $cc_i_position ) {
    $cc_i_p1 = 'top';
    $cc_i_p1_value = $messenger_position['cc_i_p3_top'];
    $cc_i_p2 = 'left';
    $cc_i_p2_value = $messenger_position['cc_i_p3_left'];
} elseif( 4 == $cc_i_position ) {
    $cc_i_p1 = 'top';
    $cc_i_p1_value = $messenger_position['cc_i_p4_top'];
    $cc_i_p2 = 'right';
    $cc_i_p2_value = $messenger_position['cc_i_p4_right'];
}


// Greetings, Chat Window - Desktop
$cc_g_position = $messenger_position['cc_g_position'];


if( 1 == $cc_g_position ) {
    $cc_g_p1 = 'bottom';
    $cc_g_p1_value = $messenger_position['cc_g_p1_bottom'];
    $cc_g_p2 = 'right';
    $cc_g_p2_value = $messenger_position['cc_g_p1_right'];
} elseif( 2 == $cc_g_position ) {
    $cc_g_p1 = 'bottom';
    $cc_g_p1_value = $messenger_position['cc_g_p2_bottom'];
    $cc_g_p2 = 'left';
    $cc_g_p2_value = $messenger_position['cc_g_p2_left'];
} elseif( 3 == $cc_g_position ) {
    $cc_g_p1 = 'top';
    $cc_g_p1_value = $messenger_position['cc_g_p3_top'];
    $cc_g_p2 = 'left';
    $cc_g_p2_value = $messenger_position['cc_g_p3_left'];
} elseif( 4 == $cc_g_position ) {
    $cc_g_p1 = 'top';
    $cc_g_p1_value = $messenger_position['cc_g_p4_top'];
    $cc_g_p2 = 'right';
    $cc_g_p2_value = $messenger_position['cc_g_p4_right'];
}


// icon - Desktop - class names
// fb_dialog fb_dialog_advanced

// icon - mobile - class names
// fb_dialog fb_dialog_mobile

// greetings text
// fb-customerchat > iframe


/**
 * messenger position
 */
$htcc_m = array(

    'cc_enable' => $cc_enable,
    'cc_enable_mobile' => $cc_enable_mobile,

    'cc_i_p1' => $cc_i_p1,
    'cc_i_p2' => $cc_i_p2,
    'cc_g_p1' => $cc_g_p1,
    'cc_g_p2' => $cc_g_p2,

    'cc_i_m_p1' => $cc_i_m_p1,
    'cc_i_m_p2' => $cc_i_m_p2,
    'cc_g_m_p1' => $cc_g_m_p1,
    'cc_g_m_p2' => $cc_g_m_p2,

    'cc_i_p1_value' => $cc_i_p1_value,
    'cc_i_p2_value' => $cc_i_p2_value,
    'cc_g_p1_value' => $cc_g_p1_value,
    'cc_g_p2_value' => $cc_g_p2_value,
    
    'cc_i_m_p1_value' => $cc_i_m_p1_value,
    'cc_i_m_p2_value' => $cc_i_m_p2_value,
    'cc_g_m_p1_value' => $cc_g_m_p1_value,
    'cc_g_m_p2_value' => $cc_g_m_p2_value,

);

wp_localize_script( 'htcc_appjs_pro', 'htcc_m', $htcc_m );