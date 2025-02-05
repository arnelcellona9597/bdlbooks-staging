<?php
/**
 * Helper functions for accessing waitlist elements
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Add given email to the waiting list for the given product ID
 *
 * @param string $email      user's email.
 * @param int    $product_id simple/variation product ID.
 * @param string $lang       user's language (if applicable).
 *
 * @return string|WP_Error
 */
function wcwl_add_user_to_waitlist( $email, $product_id, $lang = '' ) {
	if ( ! is_email( $email ) ) {
		$error = 'Failed to add user to waitlist: Email is not valid';
		wcwl_add_log( $error, $product_id, $email );

		return new WP_Error( 'woocommerce-waitlist', wcwl_get_generic_error_message( $error ) );
	}
	$product = wc_get_product( $product_id );
	if ( ! $product ) {
		$error = 'Failed to add user to waitlist: Product not found';
		wcwl_add_log( $error, $product_id, $email );

		return new WP_Error( 'woocommerce-waitlist', wcwl_get_generic_error_message( $error ) );
	}
	$waitlist = new Pie_WCWL_Waitlist( $product );
	return $waitlist->register_user( $email, $lang );
}

/**
 * Remove given email from waiting list from given product ID
 *
 * @param string $email      user's email.
 * @param int    $product_id simple/variation product ID.
 *
 * @return string|WP_Error
 */
function wcwl_remove_user_from_waitlist( $email, $product_id ) {
	global $sitepress;
	if ( isset( $sitepress ) ) {
		$product_id = wcwl_get_translated_main_product_id( $product_id );
	}
	$product = wc_get_product( $product_id );
	if ( ! $product ) {
		$error = 'Failed to remove user from waitlist: Product not found';
		wcwl_add_log( $error, $product_id, $email );

		return new WP_Error( 'woocommerce-waitlist', wcwl_get_generic_error_message( $error ) );
	}
	$waitlist = new Pie_WCWL_Waitlist( $product );

	return $waitlist->unregister_user( $email );
}

/**
 * Remove given email from product's waitlist archive
 *
 * @param string $email
 * @param int    $product_id
 */
function wcwl_remove_user_from_archive( $email, $product_id ) {
	$old_archive = is_array( get_post_meta( $product_id, 'wcwl_waitlist_archive', true ) ) ? get_post_meta( $product_id, 'wcwl_waitlist_archive', true ) : array();
	$new_archive = $old_archive;
	foreach ( $old_archive as $timestamp => $users ) {
		if ( empty( $users ) ) {
			unset( $new_archive[ $timestamp ] );
		} else {
			unset( $new_archive[ $timestamp ][ $email ] );
		}
	}
	update_post_meta( $product_id, 'wcwl_waitlist_archive', $new_archive );
}

/**
 * Returns the HTML markup for the waitlist elements for the given product ID
 *
 * @param int    $product_id simple/variation/grouped product ID.
 * @param string $context    join/leave/update - determines which button to show.
 * @param string $notice     notice to display as the intro text (useful after button is pressed).
 *
 * @return string|WP_Error
 */
function wcwl_get_waitlist_fields( $product_id, $context = '', $notice = '', $lang = '', $is_archive = false ) {
	$html = '';
	global $sitepress;
	if ( isset( $sitepress ) && function_exists( 'wpml_get_language_information' ) ) {
		$lang       = $lang ? $lang : wpml_get_language_information( null, $product_id )['language_code'];
		$product_id = wcwl_get_translated_main_product_id( $product_id );
	}
	$product = wc_get_product( $product_id );
	if ( ! $product ) {
		$error = 'Failed to load waitlist template: Product not found';
		wcwl_add_log( $error, $product_id );
	} elseif ( wcwl_waitlist_should_show( $product ) ) {
		$data         = wcwl_get_data_for_template( $product, $context, $notice, $is_archive);
		$data['lang'] = $lang;
		ob_start();
		wc_get_template( 'waitlist-single.php', $data, '', WooCommerce_Waitlist_Plugin::$path . 'templates/' );
		$html = ob_get_clean();
	}

	return $html;
}

/**
 * Retrieve template for displaying waitlist elements on archive pages (e.g. shop, product-category pages)
 *
 * @param int    $product_id product ID.
 * @param bool   $is_archive is this an archive page.
 * @param string $context    join/leave etc.
 * @param string $notice     notice to display.
 *
 * @return string|WP_Error
 */
function wcwl_get_waitlist_for_archive( $product_id, $is_archive, $context = '', $notice = '' ) {
	$html = '';
	$lang = '';
	global $sitepress;
	if ( isset( $sitepress ) && function_exists( 'wpml_get_language_information' ) ) {
		$lang       = wpml_get_language_information( null, $product_id )['language_code'];
		$product_id = wcwl_get_translated_main_product_id( $product_id );
	}
	$product = wc_get_product( $product_id );
	if ( ! $product ) {
		$error = 'Failed to load waitlist template: Product not found';
		wcwl_add_log( $error, $product_id );
	} elseif ( wcwl_waitlist_should_show( $product ) ) {
		$data         = wcwl_get_data_for_template( $product, $context, $notice, $is_archive );
		$data['lang'] = $lang;
		ob_start();
		wc_get_template( 'waitlist-archive.php', $data, '', WooCommerce_Waitlist_Plugin::$path . 'templates/' );
		$html = ob_get_clean();
	}

	return $html;
}

/**
 * Retrieve template for displaying waitlist elements on event pages
 *
 * @param int    $event_id event ID.
 * @param string $context  join/leave etc.
 * @param string $notice   notice to display.
 *
 * @return string|WP_Error
 */
function wcwl_get_waitlist_for_event( $event_id, $context = 'update', $notice = '', $is_archive = false ) {
	$html = '';
	if ( ! wcwl_is_event( $event_id ) ) {
		$error = 'Failed to load waitlist template: Event not found';
		wcwl_add_log( $error, $event_id );
	} elseif ( function_exists( 'tribe_events_has_tickets' ) && ! tribe_events_has_tickets( $event_id ) ) {
		$error = 'Failed to load waitlist template: No tickets found';
		wcwl_add_log( $error, $event_id );
	} else {
		$data = wcwl_get_data_for_event_template( $event_id, $is_archive, $context, $notice );
		ob_start();
		wc_get_template( 'waitlist-event.php', $data, '', WooCommerce_Waitlist_Plugin::$path . 'templates/' );
		$html = ob_get_clean();
	}

	return $html;
}

/**
 * Get the HTML to display a checkbox for the given product
 *
 * Used in conjunction with "wcwl_get_waitlist_fields( $product_id, 'update' )" to handle grouped products
 * Can be used for any page that displays a list of products (user checks desired products and can sign up to multiple waitlists)
 *
 * @param WC_Product $product product object.
 * @param string     $lang    user's language (if applicable).
 *
 * @return string
 */
function wcwl_get_waitlist_checkbox( WC_Product $product, $lang ) {
	if ( ! $product ) {
		return '';
	}
	$user     = get_user_by( 'id', get_current_user_id() );
	$waitlist = new Pie_WCWL_Waitlist( $product );
	$checked  = '';
	if ( $user && $waitlist->user_is_registered( $user->user_email ) ) {
		$checked = 'checked';
	}
	ob_start();
	wc_get_template(
		'waitlist-grouped-checkbox.php',
		array(
			'product_id'  => $product->get_id(),
			'lang'        => $lang,
			'user'        => $user,
			/**
			 * Filter the text to display for the checkbox on grouped products
			 * 
			 * @since 2.4.0
			 */
			'button_text' => apply_filters( 'wcwl_waitlist_checkbox_text', __( 'Join Waitlist', 'woocommerce-waitlist' ), $product ),
			'checked'     => $checked,
		),
		'',
		WooCommerce_Waitlist_Plugin::$path . 'templates/'
	);

	return ob_get_clean();
}

/**
 * Return waitlist data required for template
 *
 * @param WC_Product $product product object.
 * @param string     $context join/leave etc.
 * @param string     $notice  notice to display.
 *
 * @return array
 */
function wcwl_get_data_for_template( WC_Product $product, $context, $notice, $is_archive ) {
	if ( ! $product ) {
		return array();
	}
	$waitlist            = new Pie_WCWL_Waitlist( $product );
	$user                = get_user_by( 'id', get_current_user_id() );
	$user_is_on_waitlist = $user ? $waitlist->user_is_registered( $user->user_email ) : false;
	$on_waitlist         = $product->is_type( 'grouped' ) ? false : $user_is_on_waitlist;
	if ( ! $context ) {
		$context = $on_waitlist ? 'leave' : 'join';
	}
	$data                = wcwl_get_default_template_values( $user, $product->get_id(), $context, $notice, $is_archive );
	$data['on_waitlist'] = $on_waitlist;
	$data['intro']       = wcwl_get_intro_text( $product->get_type(), $on_waitlist, $product->get_id() );
	$data['product']     = $product;

	return $data;
}

/**
 * Return waitlist data required for template when displaying elements on an event page
 *
 * @param int    $event_id   event ID.
 * @param bool   $is_archive is this an archive page.
 * @param string $context    join/leave etc.
 * @param string $notice     notice to display.
 *
 * @return array
 */
function wcwl_get_data_for_event_template( $event_id, $is_archive, $context = 'update', $notice = '' ) {
	$user                = get_user_by( 'id', get_current_user_id() );
	$data                = wcwl_get_default_template_values( $user, $event_id, $context, $notice, $is_archive );
	$data['on_waitlist'] = false;
	$data['intro']       = wcwl_get_intro_text( 'event', false, $event_id );
	$lang                = '';
	global $sitepress;
	if ( isset( $sitepress ) && function_exists( 'wpml_get_language_information' ) ) {
		$lang = wpml_get_language_information( null, $event_id )['language_code'];
	}
	$data['lang'] = $lang;

	return $data;
}

/**
 * Get default shared values for waitlist template
 *
 * @param WP_User/false $user    user object.
 * @param int           $id      product ID.
 * @param string        $context join/leave etc.
 * @param string        $notice  notice to display.
 *
 * @return array
 */
function wcwl_get_default_template_values( $user, $id, $context, $notice, $is_archive ) {
	global $wp;
	$current_url = home_url( add_query_arg( array(), $wp->request ) );
	/* translators: %1$s: login link, %2$s: closing link tag */
	$required_to_register_text = sprintf( esc_html__( 'You must register to use the waitlist feature. Please %1$slogin or create an account%2$s', 'woocommerce-waitlist' ), '<a href="' . esc_url( wc_get_page_permalink( 'myaccount' ) . '?wcwl_redirect=' . urlencode( $current_url ) ) . '">', '</a>' );

	return array(
		'user'                           => $user,
		'email_class'                    => $user ? 'wcwl_hide' : '',
		'product_id'                     => $id,
		'context'                        => $context,
		/**
		 * Filter the URL for the waitlist button
		 * 
		 * @since 2.4.0
		 */
		'url'                            => apply_filters( 'wcwl_waitlist_button_url', '#', $id ),
		'notice'                         => $notice,
		'opt_in'                         => wcwl_is_optin_enabled( $user ),
		'opt_in_text'                    => wcwl_get_optin_text( $user ),
		/**
		 * Filter the label text for the email field
		 * 
		 * @since 2.4.0
		 */
		'email_address_label_text'       => apply_filters( 'wcwl_email_field_label', __( 'Enter your email address to join the waitlist for this product', 'woocommerce-waitlist' ), $id ),
		/**
		 * Filter the placeholder text for the email field
		 * 
		 * @since 2.4.0
		 */
		'email_address_placeholder_text' => apply_filters( 'wcwl_email_field_placeholder', __( 'Email Address', 'woocommerce-waitlist' ), $id ),
		'is_archive'                     => $is_archive,
		/**
		 * Filter the text for the dismiss text on the notification
		 * 
		 * @since 2.4.0
		 */
		'dismiss_notification_text'      => apply_filters( 'wcwl_dismiss_notification_text', __( 'Dismiss notification', 'woocommerce-waitlist' ), $id ),
		/**
		 * Filter the text to display when a user is required to register
		 * 
		 * @since 2.4.0
		 */
		'registration_required_text'     => apply_filters( 'wcwl_join_waitlist_user_requires_registration_message_text', $required_to_register_text, $id ),
	);
}

/**
 * Get the text to display on the waitlist button
 *
 * @param string $context join/leave/update depending on product type and user
 * @param int    $product_id product ID
 *
 * @return mixed|void
 */
function wcwl_get_button_text( $context = 'join', $product_id = 0 ) {
	switch ( $context ) {
		case 'join':
			$text = __( 'Join Waitlist', 'woocommerce-waitlist' );
			break;
		case 'leave':
			$text = __( 'Leave Waitlist', 'woocommerce-waitlist' );
			break;
		case 'update':
			$text = __( 'Update Waitlist', 'woocommerce-waitlist' );
			break;
		case 'confirm':
			$text = __( 'Confirm', 'woocommerce-waitlist' );
			break;
		default:
			$text = ucwords( $context );
	}

	/**
	 * Filter the text to display on the waitlist button
	 * 
	 * @since 2.4.0
	 */
	return apply_filters( 'wcwl_' . $context . '_waitlist_button_text', $text, $product_id );
}

/**
 * Get the default intro text to display above the waitlist dependent on product type
 *
 * @param string $product_type        simple/variation/grouped (variation is the same as simple by default).
 * @param bool   $user_is_on_waitlist is user on waitlist.
 * @param int    $object_id           product ID/event ID.
 *
 * @return mixed|void
 */
function wcwl_get_intro_text( $product_type = 'simple', $user_is_on_waitlist = false, $object_id = 0 ) {
	$context = 'join';
	$text    = __( 'Join the waitlist to be emailed when this product becomes available', 'woocommerce-waitlist' );
	if ( $user_is_on_waitlist ) {
		$context = 'leave';
		$text    = __( 'You are on the waitlist for this product', 'woocommerce-waitlist' );
	} elseif ( 'grouped' === $product_type || 'event' === $product_type ) {
		$context = $product_type;
		$text    = __( 'Check the box alongside any Out of Stock products and update the waitlist to be emailed when those products become available', 'woocommerce-waitlist' );
	}

	/**
	 * Filter the intro text to display above the waitlist
	 * 
	 * @since 2.4.0
	 */
	return apply_filters( 'wcwl_' . $context . '_waitlist_message_text', $text, $object_id );
}

/**
 * Are all conditions met to show the waitlist for the given product?
 *
 * @param WC_Product $product product object.
 */
function wcwl_waitlist_should_show( WC_Product $product ) {
	if ( ! $product ) {
		return false;
	}
	$waitlist_is_required = false;
	if ( ! wcwl_waitlist_is_enabled_for_product( $product->get_id() ) ) {
		$waitlist_is_required = false;
	} elseif ( $product->is_on_backorder() && WooCommerce_Waitlist_Plugin::enable_waitlist_for_backorder_products( $product->get_id() ) ) {
		$waitlist_is_required = true;
	} elseif ( ! $product->is_in_stock() ) {
		$waitlist_is_required = true;
	} elseif ( $product->is_type( 'bundle' ) || $product->is_type( 'grouped' ) ) {
		$waitlist_is_required = true;
	}

	/**
	 * Filter whether the waitlist should be shown for the given product
	 * 
	 * @since 2.4.0
	 */
	return apply_filters( 'wcwl_waitlist_is_required', $waitlist_is_required, $product );
}

/**
 * Is waitlist enabled for the given product ID?
 *
 * @param int $product_id product ID.
 *
 * @return bool
 */
function wcwl_waitlist_is_enabled_for_product( $product_id ) {
	$enabled = true;
	$options = get_post_meta( $product_id, 'wcwl_options', true );
	if ( isset( $options['enable_waitlist'] ) && 'false' === $options['enable_waitlist'] ) {
		$enabled = false;
	}

	/**
	 * Filter whether the waitlist is enabled for the given product
	 * 
	 * @since 2.4.0
	 */
	return apply_filters( 'wcwl_show_waitlist', $enabled, $product_id );
}

/**
 * Is the opt-in functionality currently enabled?
 *
 * @param object $user user object.
 *
 * @return bool
 */
function wcwl_is_optin_enabled( $user ) {
	if ( ( ! $user && 'yes' == get_option( 'woocommerce_waitlist_new_user_opt-in' ) ) || ( $user && 'yes' == get_option( 'woocommerce_waitlist_registered_user_opt-in' ) ) ) {
		return true;
	}

	return false;
}

/**
 * Check given chained parent products and perform mailout if required
 *
 * @param array $chained_products
 */
function wcwl_perform_mailout_for_chained_products( $chained_products ) {
	foreach ( $chained_products as $product_id ) {
		if ( 'yes' !== get_post_meta( $product_id, '_chained_product_manage_stock', true ) ) {
			continue;
		}
		$product = wc_get_product( $product_id );
		/**
		 * Filter whether the we should send waitlist mailout for this product
		 * 
		 * @since 2.4.0
		 */
		if ( $product && $product->is_in_stock() && apply_filters( 'wcwl_waitlist_should_do_mailout', true, $product ) ) {
			$waitlist = new Pie_WCWL_Waitlist( $product );
			$waitlist->waitlist_mailout();
		}
	}
}

/**
 * Check given bundled parent products and perform mailout if required
 *
 * @param array $bundle_products
 */
function wcwl_perform_mailout_for_bundle_products( $bundle_products ) {
	foreach ( $bundle_products as $product_id ) {
		$product = wc_get_product( $product_id );
		if ( ! $product || ! $product->is_type( 'bundle' ) || $product->get_manage_stock() ) {
			continue;
		}
		/**
		 * Filter whether the we should send waitlist mailout for this product
		 * 
		 * @since 2.4.0
		 */
		if ( $product->is_in_stock() && apply_filters( 'wcwl_waitlist_should_do_mailout', true, $product ) ) {
			$waitlist = new Pie_WCWL_Waitlist( $product );
			$waitlist->waitlist_mailout();
		}
	}
}

/**
 * Get the text to display for the opt-in checkbox
 *
 * @param object $user user object.
 *
 * @return mixed|void
 */
function wcwl_get_optin_text( $user ) {
	if ( ! $user && 'yes' === get_option( 'woocommerce_waitlist_create_account' ) ) {
		/**
		 * Filter the text to display for the opt-in checkbox for new users
		 * 
		 * @since 2.4.0
		 */
		return apply_filters( 'wcwl_new_user_opt-in_text', __( 'By ticking this box you agree to an account being created using the given email address and to receive waitlist communications by email', 'woocommerce-waitlist' ) );
	} else {
		/**
		 * Filter the text to display for the opt-in checkbox for registered users
		 * 
		 * @since 2.4.0
		 */
		return apply_filters( 'wcwl_registered_user_opt-in_text', __( 'By ticking this box you agree to receive waitlist communications by email', 'woocommerce-waitlist' ) );
	}
}

/**
 * Return the main product for the given translated product ID
 * Required to support WPML as all meta data is saved to the original/main product
 *
 * @param int $product_id product ID.
 *
 * @return int
 */
function wcwl_get_translated_main_product_id( $product_id ) {
	global $woocommerce_wpml;
	$master_post_id = $product_id;
	if ( isset( $woocommerce_wpml->products ) && $woocommerce_wpml->products ) {
		$master_post_id = $woocommerce_wpml->products->get_original_product_id( $product_id );
	}

	return $master_post_id;
}

/**
 * Checks if we should be displaying a translated product name and returns it if required
 * Otherwise returns the original product name
 *
 * @param WC_Product $product
 * @return string
 */
function wcwl_get_product_name( $product ) {
	global $sitepress;
	if ( isset( $sitepress ) && function_exists( 'wpml_object_id_filter' ) ) {
		// Get the product ID for the current selected language, returns the original product ID if no translation exists if true is passed as third argument
		$product_id = wpml_object_id_filter( $product->get_id(), 'post', true, $sitepress->get_current_language() );
		if ( $product_id !== $product->get_id() ) {
			$translated_product = wc_get_product( $product_id );
			return $translated_product->get_name();
		}
	}

	return $product->get_name();
}

/**
 * Get the language required for the given email address
 *
 * @param  string/int $user      user's email address or ID
 * @param  int        $product_id legacy method for finding language per product.
 * @return string             language code
 */
function wcwl_get_user_language( $user, $product_id = 0 ) {
	if ( ! function_exists( 'wpml_get_default_language' ) ) {
		return '';
	}
	$lang_option = get_option( '_' . WCWL_SLUG . '_languages' );
	$user_object = get_user_by( 'id', $user );
	if ( ! $user_object ) {
		$user_object = get_user_by( 'email', $user );
	}
	if ( $user_object && $product_id ) {
		$languages = get_user_meta( $user_object->ID, 'wcwl_languages', true );
		if ( isset( $languages[ $product_id ] ) ) {
			return $languages[ $product_id ];
		}
	}
	if ( isset( $lang_option[ $user ] ) ) {
		return $lang_option[ $user ];
	}
	return wpml_get_default_language();
}

/**
 * Check whether given post ID is of type "event"
 *
 * @param int $post_id post ID.
 *
 * @return bool
 */
function wcwl_is_event( $post_id ) {
	if ( function_exists( 'tribe_events_get_event' ) && tribe_events_get_event( $post_id ) ) {
		return true;
	}

	return false;
}

/**
 * Return a generic, filterable message for the given error
 *
 * @param string $error error message.
 *
 * @return mixed|void
 */
function wcwl_get_generic_error_message( $error ) {
	/**
	 * Filter the generic error message
	 * 
	 * @since 2.4.0
	 */
	return apply_filters( 'wcwl_generic_error_message', __( 'I\'m afraid something went wrong with your request. Please try again or contact us for help', 'woocommerce-waitlist' ), $error );
}

/**
 * Add a message to the WC logs
 *
 * @param string $message    error message.
 * @param int    $product_id product ID.
 * @param string $email      user email.
 */
function wcwl_add_log( $message, $product_id = 0, $email = '' ) {
	$logger = wc_get_logger();
	$logger->debug( $message . ' (Post ID: ' . $product_id . '; User email: ' . $email . ')', array( 'source' => 'woocommerce-waitlist' ) );
}

/**
 * Switches the locale to utilise the globally set locale
 * This is used for AJAX calls to prevent the frontend showing a user set locale that is different for returning HTML
 */
function wcwl_switch_locale() {
	/**
	 * Filter whether to allow the locale to be switched
	 * 
	 * @since 2.4.0
	 */
	if ( ! apply_filters( 'wcwl_allow_locale_switch', false ) ) {
		return;
	}
	$locale = get_locale();
	switch_to_locale( $locale );
	add_filter( 'plugin_locale', 'get_locale' );
	unload_textdomain( 'woocommerce-waitlist' );
	/**
	 * Filter the path to the language file
	 * 
	 * @since 2.4.0
	 */
	load_textdomain( 'woocommerce-waitlist', apply_filters( 'wcwl_language_path', WP_LANG_DIR . '/plugins/woocommerce-waitlist-' . $locale . '.mo' ) );
}
