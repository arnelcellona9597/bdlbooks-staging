<?php
/**
 * update sdk values .. 
 * 
 * xfbml
 */

if ( ! defined( 'ABSPATH' ) ) exit; 


$htcc_pro_options = get_option( 'htcc_pro_options' );

$parse_init = $htcc_pro_options['parse_init'];


if ( 'no' == $parse_init ) {
    $xfbml = false;
}
