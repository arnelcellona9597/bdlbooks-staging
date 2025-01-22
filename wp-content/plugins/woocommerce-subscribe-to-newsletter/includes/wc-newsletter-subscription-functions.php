<?php
/**
 * Useful functions for the plugin
 *
 * @package WC_Newsletter_Subscription/Functions
 * @since   2.5.0
 */

defined( 'ABSPATH' ) || exit;

require_once 'wc-newsletter-subscription-order-functions.php';

/**
 * Gets the suffix for the script filenames.
 *
 * @since 2.5.0
 *
 * @return string The scripts suffix.
 */
function wc_newsletter_subscription_get_scripts_suffix() {
	return ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min' );
}

/**
 * Gets the specified admin url.
 *
 * @since 2.6.0
 *
 * @param array $extra_params Optional. Additional parameters in pairs key => value.
 * @return string The admin page url.
 */
function wc_newsletter_subscription_get_settings_url( $extra_params = array() ) {
	$url = 'admin.php?page=wc-settings&tab=newsletter';

	if ( ! empty( $extra_params ) ) {
		$url = add_query_arg( $extra_params, $url );
	}

	return admin_url( $url );
}

/**
 * What type of request is this?
 *
 * @since 2.9.0
 *
 * @param string $type admin, ajax, cron, rest_api or frontend.
 * @return bool
 */
function wc_newsletter_subscription_is_request( $type ) {
	$is_request = false;

	switch ( $type ) {
		case 'admin':
			$is_request = is_admin();
			break;
		case 'ajax':
			$is_request = defined( 'DOING_AJAX' );
			break;
		case 'cron':
			$is_request = defined( 'DOING_CRON' );
			break;
		case 'frontend':
			$is_request = ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' ) && ! wc_newsletter_subscription_is_request( 'rest_api' );
			break;
		case 'rest_api':
			$is_request = ( defined( 'REST_REQUEST' ) && REST_REQUEST );
			break;
	}

	/**
	 * Filters if the request is of the specified type.
	 *
	 * @since 2.9.0
	 *
	 * @param bool   $is_request Whether the request is of the specified type.
	 * @param string $type       The request type.
	 */
	return apply_filters( 'wc_newsletter_subscription_is_request', $is_request, $type );
}

/**
 * Gets templates passing attributes and including the file.
 *
 * @since 2.9.0
 *
 * @param string $template_name The template name.
 * @param array  $args          Optional. The template arguments.
 */
function wc_newsletter_subscription_get_template( $template_name, $args = array() ) {
	wc_get_template( $template_name, $args, '', WC_NEWSLETTER_SUBSCRIPTION_PATH . 'templates/' );
}

/**
 * Gets if the specified plugin is active.
 *
 * @since 2.9.0
 *
 * @param string $plugin Base plugin path from plugins directory.
 * @return bool
 */
function wc_newsletter_subscription_is_plugin_active( $plugin ) {
	$active_plugins = (array) get_option( 'active_plugins', array() );

	if ( is_multisite() ) {
		$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
	}

	return ( in_array( $plugin, $active_plugins, true ) || array_key_exists( $plugin, $active_plugins ) );
}

/**
 * Logs a message.
 *
 * @since 3.0.0
 *
 * @param string $message The message to log.
 * @param string $level   Optional. The log level. Default 'notice'.
 * @param string $handle  Optional. The file handle. Default 'wc_newsletter_subscription'.
 */
function wc_newsletter_subscription_log( $message, $level = 'notice', $handle = 'wc_newsletter_subscription' ) {
	$logger = wc_get_logger();

	if ( method_exists( $logger, $level ) ) {
		call_user_func( array( $logger, $level ), $message, array( 'source' => $handle ) );
	} else {
		$logger->add( $handle, $message );
	}
}

/**
 * Logs an error.
 *
 * Logs the error and returns a `WP_Error` object.
 *
 * @since 3.0.0
 *
 * @param mixed  $error  Error string or WP_Error object.
 * @param string $handle Optional. The file handle. Default 'wc_newsletter_subscription'.
 * @return WP_Error
 */
function wc_newsletter_subscription_log_error( $error, $handle = 'wc_newsletter_subscription' ) {
	if ( ! is_wp_error( $error ) ) {
		$error = new WP_Error( 'error', $error );
	}

	$error_data = $error->get_error_data();

	$error_log = sprintf(
		'[%1$s] %2$s %3$s',
		$error->get_error_code(),
		$error->get_error_message(),
		( is_array( $error_data ) ? wc_print_r( $error_data, true ) : '' )
	);

	wc_newsletter_subscription_log( $error_log, 'error', $handle );

	return $error;
}

/**
 * Gets the service provider used to subscribe users to the newsletter.
 *
 * @since 2.5.0
 *
 * @return mixed
 */
function wc_newsletter_subscription_get_provider() {
	return WC_Subscribe_To_Newsletter::instance()->provider();
}

/**
 * Subscribes a user to the newsletter.
 *
 * @since 2.5.0
 *
 * @param string $email The email address to subscribe.
 * @param array  $args  Optional. Additional arguments.
 * @return bool
 */
function wc_newsletter_subscription_subscribe( $email, $args = array() ) {
	$provider = wc_newsletter_subscription_get_provider();

	// No service provider defined.
	if ( ! $provider ) {
		return false;
	}

	$args = wp_parse_args(
		$args,
		[
			'first_name'   => '',
			'last_name'    => '',
			'list_id'      => false,
			'tags'         => array(),
			'phone'        => '',
			'country_code' => '',
		]
	);

	$list_id = ( $args['list_id'] ? $args['list_id'] : wc_newsletter_subscription_get_provider_list() );

	// No list defined.
	if ( ! $list_id ) {
		return false;
	}

	$subscriber = new WC_Newsletter_Subscription_Subscriber(
		[
			'email'        => $email,
			'first_name'   => $args['first_name'],
			'last_name'    => $args['last_name'],
			'tags'         => $args['tags'],
			'phone'        => $args['phone'],
			'country_code' => $args['country_code'],
		]
	);

	$result = $provider->subscribe( $list_id, $subscriber );

	if ( is_wp_error( $result ) ) {
		/**
		 * Fires when the subscription to the newsletter fails.
		 *
		 * @since 3.0.0
		 *
		 * @param WP_Error                              $error      The WP Error.
		 * @param WC_Newsletter_Subscription_Subscriber $subscriber Subscriber object.
		 */
		do_action( 'wc_newsletter_subscription_failed', $result, $subscriber );

		return false;
	}

	/**
	 * Fires after subscribing a user to the newsletter.
	 *
	 * @since 2.2.0
	 * @since 3.0.0 Added parameter `$subscriber`.
	 *
	 * @param string                                $email      The subscriber email.
	 * @param WC_Newsletter_Subscription_Subscriber $subscriber Subscriber object.
	 */
	do_action( 'wc_subscribed_to_newsletter', $email, $result );

	return true;
}

/**
 * Unsubscribes a user from the newsletter.
 *
 * @since 4.0.0
 *
 * @param string $email The email address to subscribe.
 * @param mixed  $list  Optional. The list identifier. Default false.
 * @return bool
 */
function wc_newsletter_subscription_unsubscribe( $email, $list = false ) {
	$provider = wc_newsletter_subscription_get_provider();

	if ( ! $provider || ! $provider->supports( 'manage_subscription' ) ) {
		return false;
	}

	// Use the default list.
	if ( ! $list ) {
		$list = wc_newsletter_subscription_get_provider_list( $provider );
	}

	if ( ! $list ) {
		return false;
	}

	$subscriber = new WC_Newsletter_Subscription_Subscriber( compact( 'email' ) );

	$result = $provider->unsubscribe( $list, $subscriber );

	if ( is_wp_error( $result ) ) {
		/**
		 * Fires when the unsubscription from the newsletter fails.
		 *
		 * @since 4.0.0
		 *
		 * @param WP_Error                              $error      The WP Error.
		 * @param WC_Newsletter_Subscription_Subscriber $subscriber Subscriber object.
		 * @param mixed                                 $list       The list identifier.
		 */
		do_action( 'wc_newsletter_unsubscription_failed', $result, $subscriber, $list );

		return false;
	}

	/**
	 * Fires after unsubscribing a user from the newsletter.
	 *
	 * @since 4.0.0
	 *
	 * @param string                                $email      The subscriber email.
	 * @param WC_Newsletter_Subscription_Subscriber $subscriber Subscriber object.
	 * @param mixed                                 $list       The list identifier.
	 */
	do_action( 'wc_unsubscribed_from_newsletter', $email, $result, $list );

	return true;
}

/**
 * Checks if the email is subscribed to the newsletter.
 *
 * @since 4.0.0
 *
 * @param string $email The email address.
 * @param mixed  $list  Optional. The list identifier. Default false.
 * @return bool
 */
function wc_newsletter_subscription_is_subscribed( $email, $list = false ) {
	$provider = wc_newsletter_subscription_get_provider();

	if ( ! $provider || ! $provider->supports( 'manage_subscription' ) ) {
		return false;
	}

	// Use the default list.
	if ( ! $list ) {
		$list = wc_newsletter_subscription_get_provider_list( $provider );
	}

	if ( ! $list ) {
		return false;
	}

	$subscriber = new WC_Newsletter_Subscription_Subscriber( compact( 'email' ) );

	$result = $provider->is_subscribed( $list, $subscriber );

	if ( is_wp_error( $result ) ) {
		return false;
	}

	return $result;
}

/**
 * Emails the admin when a subscription to the newsletter fails.
 *
 * @since 3.0.0
 *
 * @param WP_Error $error The WP Error.
 */
function wc_newsletter_subscription_email_on_failure( $error ) {
	wp_mail(
		get_option( 'admin_email' ),
		esc_html__( 'Email subscription failed', 'woocommerce-subscribe-to-newsletter' ),
		'(' . esc_html( $error->get_error_message() ) . ') ' . wc_print_r( $error->get_error_data(), true )
	);
}
add_action( 'wc_newsletter_subscription_failed', 'wc_newsletter_subscription_email_on_failure' );

/**
 * Processes the subscription widget.
 *
 * @since 2.5.0
 */
function wc_newsletter_subscription_process_widget() {
	check_ajax_referer( 'wc_subscribe_to_newsletter_widget' );

	$list_id = ( ! empty( $_POST['list_id'] ) ? wc_clean( wp_unslash( $_POST['list_id'] ) ) : '' );

	if ( ! $list_id ) {
		wp_send_json_error( array( 'message' => _x( 'An unexpected error happened.', 'widget error', 'woocommerce-subscribe-to-newsletter' ) ) );
	}

	$email = ( ! empty( $_POST['newsletter_email'] ) ? wc_clean( wp_unslash( $_POST['newsletter_email'] ) ) : '' );

	if ( ! $email || ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => _x( 'Please, enter a valid email address.', 'widget error', 'woocommerce-subscribe-to-newsletter' ) ) );
	}

	// Honeypot field.
	if ( ! empty( $_POST['newsletter_phone'] ) ) {
		wp_send_json_error( array( 'message' => _x( 'Are you a robot?', 'widget error', 'woocommerce-subscribe-to-newsletter' ) ) );
	}

	// Check too many attempts.
	$transient_key = 'wc_newsletter_subscription_widget_attempts_' . md5( WC_Geolocation::get_ip_address() );
	$attempts      = get_transient( $transient_key );

	if ( ! is_array( $attempts ) ) {
		$attempts = array(
			'count'     => 0,
			'expire_at' => time() + 300, // Expires in 5 minutes.
		);
	}

	if ( 3 <= $attempts['count'] ) {
		wp_send_json_error( array( 'message' => _x( 'Too many attempts to subscribe. Try it again later.', 'widget error', 'woocommerce-subscribe-to-newsletter' ) ) );
	}

	$name_fields = array();

	foreach ( array( 'name', 'first_name', 'last_name' ) as $key ) {
		$field_key = "newsletter_{$key}";

		$name_fields[ $key ] = ( ! empty( $_POST[ $field_key ] ) ? wc_clean( wp_unslash( $_POST[ $field_key ] ) ) : '' );
	}

	// Split the name into two parts.
	if ( ! empty( $name_fields['name'] ) ) {
		$name_parts = explode( ' ', trim( $name_fields['name'] ) );

		$name_fields['first_name'] = current( $name_parts );
		$name_fields['last_name']  = '';

		if ( 1 < count( $name_parts ) ) {
			$name_fields['last_name'] = join( ' ', array_slice( $name_parts, 1 ) );
		}
	}

	$subscribed = wc_newsletter_subscription_subscribe(
		$email,
		array(
			'first_name' => $name_fields['first_name'],
			'last_name'  => $name_fields['last_name'],
			'list_id'    => $list_id,
		)
	);

	// Set attempts transient.
	$attempts['count'] += 1;
	$expiration         = ( $attempts['expire_at'] - time() );

	if ( 0 < $expiration ) {
		set_transient( $transient_key, $attempts, $expiration );
	}

	if ( $subscribed ) {
		wp_send_json_success( array( 'message' => _x( 'Thanks for subscribing.', 'widget success', 'woocommerce-subscribe-to-newsletter' ) ) );
	} else {
		wp_send_json_error( array( 'message' => _x( 'Oops! Something went wrong.', 'widget error', 'woocommerce-subscribe-to-newsletter' ) ) );
	}
}
add_action( 'wp_ajax_subscribe_to_newsletter', 'wc_newsletter_subscription_process_widget' );
add_action( 'wp_ajax_nopriv_subscribe_to_newsletter', 'wc_newsletter_subscription_process_widget' );

/**
 * Get if the newsletter provider is connected or not.
 *
 * @since 2.8.0
 *
 * @return bool
 */
function wc_newsletter_subscription_is_connected() {
	$provider = wc_newsletter_subscription_get_provider();

	return ( $provider instanceof WC_Newsletter_Subscription_Provider && $provider->is_enabled() );
}

/**
 * Gets if the newsletter provider supports the specified feature.
 *
 * @since 3.1.0
 *
 * @param string $feature The feature to test support for.
 * @return bool
 */
function wc_newsletter_subscription_provider_supports( $feature ) {
	$provider = wc_newsletter_subscription_get_provider();

	return ( $provider && $provider->supports( $feature ) );
}

/**
 * Gets the provider's list.
 *
 * @since 3.0.0
 * @since 3.3.3 Added parameter `$provider`.
 *
 * @param WC_Newsletter_Subscription_Provider $provider Optional. Provider instance. Default null.
 * @return mixed
 */
function wc_newsletter_subscription_get_provider_list( $provider = null ) {
	if ( ! $provider ) {
		$provider = wc_newsletter_subscription_get_provider();
	}

	if ( $provider instanceof WC_Newsletter_Subscription_Provider ) {
		return get_option( 'woocommerce_' . $provider->get_id() . '_list' );
	}

	return false;
}

/**
 * Gets if the provider has a list set.
 *
 * @since 2.9.0
 * @since 3.3.3 Added parameter `$provider`.
 *
 * @param WC_Newsletter_Subscription_Provider $provider Optional. Provider instance. Default null.
 * @return bool
 */
function wc_newsletter_subscription_provider_has_list( $provider = null ) {
	$list = wc_newsletter_subscription_get_provider_list( $provider );

	return ( ! empty( $list ) );
}

/**
 * Disconnect the current provider.
 *
 * @since 2.8.0
 *
 * @return bool
 */
function wc_newsletter_subscription_disconnect_provider() {
	$provider = wc_newsletter_subscription_get_provider();

	if ( ! $provider instanceof WC_Newsletter_Subscription_Provider ) {
		return false;
	}

	delete_option( 'woocommerce_newsletter_service' );

	if ( method_exists( $provider, 'get_api_key' ) ) {
		delete_option( 'woocommerce_' . $provider->get_id() . '_api_key' );
	}

	return true;
}

/**
 * Gets the checkout location choices to use them in a select field.
 *
 * @since 3.3.3
 *
 * @return array
 */
function wc_newsletter_subscription_get_checkout_location_choices() {
	/**
	 * Filters the checkout location choices.
	 *
	 * @since 3.3.3
	 *
	 * @param array $choices The checkout location choices.
	 */
	return apply_filters(
		'wc_newsletter_subscription_checkout_location_choices',
		array(
			'after_terms'   => __( 'After the Terms and Conditions', 'woocommerce-subscribe-to-newsletter' ),
			'after_billing' => __( 'After billing details', 'woocommerce-subscribe-to-newsletter' ),
		)
	);
}

/**
 * Converts a bool to 'yes' or 'no'.
 *
 * The returned string is translatable.
 *
 * @since 3.3.5
 *
 * @see wc_string_to_bool()
 *
 * @param bool $value Bool to convert.
 * @return string
 */
function wc_newsletter_subscription_bool_to_string( $value ) {
	return ( wc_string_to_bool( $value ) ? __( 'Yes', 'woocommerce-subscribe-to-newsletter' ) : __( 'No', 'woocommerce-subscribe-to-newsletter' ) );
}

/**
 * Gets the label for the subscription checkbox field.
 *
 * @since 3.4.0
 *
 * @return string
 */
function wc_newsletter_subscription_get_checkbox_label() {
	$label = get_option( 'woocommerce_newsletter_label' );

	if ( ! $label ) {
		$label = _x( 'Subscribe to our newsletter', 'subscription checkbox label', 'woocommerce-subscribe-to-newsletter' );
	}

	return $label;
}
