<?php
/**
 * Custom Image  ( ci )
 * 
 * 
 */

if ( ! defined( 'ABSPATH' ) ) exit; 


$htcc_ci =  get_option('htcc_ci');

$ci_img_url = $htcc_ci['ci_img_url'];
$ci_img_height = $htcc_ci['ci_img_height'];
$ci_img_width = $htcc_ci['ci_img_width'];
$ci_img_border = $htcc_ci['ci_img_border'];


if ( '' == $ci_img_url ) {
    // todo - change image url.. 
    $ci_img_url = 'https://www.holithemes.com/plugins/click/wp-content/uploads/2018/08/profile-962x1024-1.jpg';
}

$ci_img_styles = '';

if ( '' !== $ci_img_height ) {
    $ci_img_styles .= "height: $ci_img_height; ";
}

if ( '' !== $ci_img_width ) {
    $ci_img_styles .= "width: $ci_img_width; ";
}

if ( '' !== $ci_img_border ) {
    $ci_img_styles .= "border-radius: $ci_img_border; ";
}

/**
 * detect device and display ci is done in js
 * If enabled - load the ci and display based on settings and time range, days, device
 * display image after fb loaded ..
 * load messenger when user clicks on image or when to load messenger settings
 */
if ( isset( $htcc_ci['ci_enable'] ) || isset( $htcc_ci['ci_enable_mobile'] ) ) {
    
    ?>

    <div class="htcc-custom-image" style="cursor: pointer; z-index: 999999; display: none;">
        <img src="<?php echo $ci_img_url ?>" style="<?php echo $ci_img_styles ?>" alt="Messenger">
    </div>

    <?php
}

