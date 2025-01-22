<?php
/**
 * Newsletter Subscriber
 *
 * This class represents a newsletter subscriber.
 *
 * @package WC_Newsletter_Subscription
 * @since   3.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class WC_Newsletter_Subscription_Subscriber.
 */
class WC_Newsletter_Subscription_Subscriber extends WC_Data {

	/**
	 * Data array.
	 *
	 * @var array
	 */
	protected $data = [
		'email'        => '',
		'first_name'   => '',
		'last_name'    => '',
		'phone'        => '',
		'country_code' => '',
		'tags'         => array(),
	];

	/**
	 * Object type.
	 *
	 * @var string
	 */
	protected $object_type = 'newsletter_subscriber';

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param array $subscriber Subscriber data.
	 */
	public function __construct( $subscriber = array() ) {
		parent::__construct( $subscriber );

		$this->set_props( $subscriber );
		$this->set_object_read( true );
	}

	/*
	 * --------------------------------------------------------------------------
	 * Getters
	 * --------------------------------------------------------------------------
	 */

	/**
	 * Gets the subscriber's email.
	 *
	 * @since 3.0.0
	 *
	 * @param string $context View or edit context.
	 * @return string
	 */
	public function get_email( $context = 'view' ) {
		return $this->get_prop( 'email', $context );
	}

	/**
	 * Gets the subscriber's first name.
	 *
	 * @since 3.0.0
	 *
	 * @param string $context View or edit context.
	 * @return string
	 */
	public function get_first_name( $context = 'view' ) {
		return $this->get_prop( 'first_name', $context );
	}

	/**
	 * Gets the subscriber's last name.
	 *
	 * @since 3.0.0
	 *
	 * @param string $context View or edit context.
	 * @return string
	 */
	public function get_last_name( $context = 'view' ) {
		return $this->get_prop( 'last_name', $context );
	}

	/**
	 * Gets the subscriber's full name.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_full_name() {
		$first_name = $this->get_first_name();
		$last_name  = $this->get_last_name();

		return trim( $first_name . ' ' . $last_name );
	}

	/**
	 * Gets tags.
	 *
	 * @since 3.6.0
	 *
	 * @param string $context View or edit context.
	 * @return array
	 */
	public function get_tags( $context = 'view' ) {
		return $this->get_prop( 'tags', $context );
	}

	/**
	 * Gets the subscriber's phone number.
	 *
	 * @since 4.4.1
	 * @param string $context View or edit context.
	 * @return mixed|null
	 */
	public function get_phone( $context = 'view' ) {
		return $this->get_prop( 'phone', $context );
	}

	/**
	 * Gets the subscriber's country code.
	 *
	 * @since 4.4.1
	 * @param string $context View or edit context.
	 * @return mixed|null
	 */
	public function get_country_code( $context = 'view' ) {
		return $this->get_prop( 'country_code', $context );
	}

	/*
	 * --------------------------------------------------------------------------
	 * Setters
	 * --------------------------------------------------------------------------
	 */

	/**
	 * Sets the subscriber's email.
	 *
	 * @since 3.0.0
	 *
	 * @param string $email The email.
	 */
	public function set_email( $email ) {
		$this->set_prop( 'email', strtolower( sanitize_email( $email ) ) );
	}

	/**
	 * Sets the subscriber's first name.
	 *
	 * @since 3.0.0
	 *
	 * @param string $first_name The first name.
	 */
	public function set_first_name( $first_name ) {
		$this->set_prop( 'first_name', sanitize_text_field( $first_name ) );
	}

	/**
	 * Sets the subscriber's last name.
	 *
	 * @since 3.0.0
	 *
	 * @param string $last_name The last name.
	 */
	public function set_last_name( $last_name ) {
		$this->set_prop( 'last_name', sanitize_text_field( $last_name ) );
	}

	/**
	 * Sets tags.
	 *
	 * @since 3.6.0
	 *
	 * @param array $tags Tags.
	 */
	public function set_tags( $tags ) {
		$this->set_prop( 'tags', $tags );
	}

	/**
	 * Sets subscribers phone number.
	 *
	 * @param string $phone phone number.
	 */
	public function set_phone( $phone ) {
		$this->set_prop( 'phone', $phone );
	}

	/**
	 * Sets subscriber's country code.
	 *
	 * @param string $country_code country code
	 */
	public function set_country_code( string $country_code ) {
		$this->set_prop( 'country_code', $country_code );
	}
}
