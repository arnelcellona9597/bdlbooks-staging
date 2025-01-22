<?php
/**
 * Central file for admin 
 * 
 * @package fb-all
 * @subpackage Admin
 * @since 1.0
 * 
 * subpackage Admin loads only on wp-admin 
 */


if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'HTCC_Pro' ) ) :

class HTCC_Pro {

    public function __construct() {
        $this->features();
    }

    public function features() {

        $features = get_option( 'htcc_pro_features' );

        require_once HTCC_PLUGIN_DIR . 'admin/pro/class-admin-htcc-pro-features.php';
        require_once HTCC_PLUGIN_DIR . 'admin/pro/class-admin-htcc-pro-actions.php';

        if ( isset( $features['enable_woo'] ) ) {
            require_once HTCC_PLUGIN_DIR . 'admin/pro/class-admin-htcc-pro-woo.php';
        }

        // require_once HTCC_PLUGIN_DIR . 'admin/pro/class-admin-htcc-pro-enqueue.php';

    }




}

new HTCC_pro();

endif; // END class_exists check