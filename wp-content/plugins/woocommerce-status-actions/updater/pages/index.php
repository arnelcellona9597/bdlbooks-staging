<?php if ( ! defined( 'ABSPATH' ) ) { return; } /*#!-- Do not allow this file to be loaded unless in WP context*/
/**
 * This is the plugin's default page
 */
global $aebaseapi;
$purchase_codes = get_option(AEBaseApi::PURCHASE_CODES_OPTION_KEY, array());
$products = $products = array(WP_PLUGIN_DIR . '/woocommerce-status-actions/woocommerce-status-actions.php');
?>
<style>
    #ae-update-plugins-form table input{ width: 100%; }
    #ae-update-plugins-form .status{
        text-align: center;
        vertical-align: middle;
        width: 30px;
    }
    #ae-update-plugins-form .status .dashicons-dismiss{
        color: #a00;
    }
    #ae-update-plugins-form .status .dashicons-yes{
        color: #73a724;
    }
    
</style>
<div class="wrap" style="margin: 25px 40px 0 20px;">
<?php
    $rm = strtoupper($_SERVER['REQUEST_METHOD']);
    if('POST' == $rm)
    {
        if (! isset( $_POST['ae_save_credentials'] )|| ! wp_verify_nonce( $_POST['ae_save_credentials'], 'ae_save_credentials_action' )) { ?>
            <div class="error below-h2">
                <p><?php _e('Invalid request.', 'envato-update-plugins');?></p>
            </div>
        <?php }
        else if(isset($_POST['envato-update-plugins_purchase_code']) ){
            $purchase_codes = array_map('trim', $_POST['envato-update-plugins_purchase_code']);
            update_option(AEBaseApi::PURCHASE_CODES_OPTION_KEY, $purchase_codes);
        }
    }
?>
</div>
<div class="wrap about-wrap" id="ae-update-plugins-form">
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e( 'Product', 'wc_point_of_sale' ); ?></th>
                <th><?php _e( 'Purchase Code', 'wc_point_of_sale' ); ?></th>
                <th class="status"></th>
            </tr>
        </thead>
        <tbody>
        <?php 
        foreach ($products as $file ) {
            $plugin_slug = basename($file, '.php');
            $pluginData = get_plugin_data($file);
            $purchase_code = isset($purchase_codes[$plugin_slug]) ? $purchase_codes[$plugin_slug] : '';
            if( $pluginData ){
                ?>
                <tr>
                    <th scope="row"><strong><?php echo $pluginData['Name']; ?></strong></th>
                    <td><input type="text" placeholder="<?php _e( 'Place your purchase code here', 'wc_point_of_sale' ); ?>" class="regular-text" name="envato-update-plugins_purchase_code[<?php echo $plugin_slug;?>]"
                                value="<?php echo $purchase_code;?>" /></td>
                        <td class="status">
                            <?php
                        $code_validation = ae_updater_validate_code( $plugin_slug, $purchase_code );

                            if( !isset($code_validation->error) &&  ae_updater_validate_code( $plugin_slug, $purchase_code ) ){
                                ?>
                                <span class="dashicons dashicons-yes"></span>
                                <?php
                            }else{
                                ?>
                                <span class="dashicons dashicons-dismiss" title="<?php echo $code_validation->error ?>"></span>
                                <?php
                            }
                            ?>
                        </td>
                </tr>
                <?php
            }
        }
        ?>
        </tbody>
    </table>
    <p class="submit">
        <input type="submit"class="button button-large button-primary" id="envato-update-plugins_submit"
                value="<?php _e( 'Save Settings', 'envato-update-plugins');?>" />
    </p>
    <?php wp_nonce_field( 'ae_save_credentials_action', 'ae_save_credentials');?>
</div>