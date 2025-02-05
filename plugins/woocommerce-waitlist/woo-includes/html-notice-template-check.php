<?php

/**
 * Admin View: Notice - Template Check
 *
 * @package WooCommerce\Views
 */

if (!defined('ABSPATH')) {
	exit;
}

$theme = wp_get_theme();
?>
<p>
	<?php
	/* translators: %1$s opening <strong> tag, %2$s theme name, %3$s closing <strong> tag */
	printf( esc_html__('%1$sYour theme (%2$s) contains outdated copies of some WooCommerce Waitlist template files.%3$s These files may need updating to ensure they are compatible with the current version of WooCommerce Waitlist. Suggestions to fix this:', 'woocommerce-waitlist'), '<strong>', esc_html( $theme['Name'] ), '</strong>' );
	?>
<ol>
	<li><?php esc_html_e('Update your theme to the latest version. If no update is available contact your theme author asking about compatibility with the current WooCommerce Waitlist version.', 'woocommerce-waitlist'); ?></li>
	<li><?php esc_html_e('If you copied over a template file to change something, then you will need to copy the new version of the template and apply your changes again.', 'woocommerce'); ?></li>
</ol>
</p>
<p class="submit">
	<a class="button-primary" href="https://docs.woocommerce.com/document/template-structure/" target="_blank"><?php esc_html_e('Learn more about templates', 'woocommerce'); ?></a>
</p>
