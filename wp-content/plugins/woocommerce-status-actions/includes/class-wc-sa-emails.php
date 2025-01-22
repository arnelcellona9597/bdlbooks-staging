<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Transactional Emails Controller
 *
 * Emails Class which handles the sending on transactional emails and email templates. This class loads in available emails.
 *
 */
class WC_SA_Emails {

	/** @var array Array of email notification classes */
	public $emails;

	/** @var WC_SA_Emails The single instance of the class */
	protected static $_instance = null;
	private $core_emails = array(
        'processing' => 'customer_processing_order',
        'cancelled'  => 'cancelled_order',
        'failed'     => 'failed_order',
        'refunded'   => 'customer_refunded_order',
        'completed'  => 'customer_completed_order',
        'on-hold'    => 'customer_on_hold_order'
    );

	/**
	 * Main WC_SA_Emails Instance.
	 *
	 * Ensures only one instance of WC_SA_Emails is loaded or can be loaded.
	 *
	 * @static
	 * @return WC_SA_Emails Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 2.1
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), '2.1' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 2.1
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), '2.1' );
	}

	/**
	 * Constructor for the email class hooks in all emails that can be sent.
	 *
	 */
	public function __construct() {
		add_filter( 'woocommerce_email_classes', array($this , 'email_classes') );
		add_filter( 'woocommerce_email_actions', array($this , 'email_actions') );
		$this->core_email_filters();
	}

	/**
	 * Init email classes.
	 */
	public function email_classes($emails) {
		include( 'emails/class-wc-sa-email.php' );
		$statuses = wc_sa_get_statuses();
		foreach ($statuses as $id => $value) {
			$emails['WC_SA_Email_'.$value->label] = new WC_SA_Email($id);
		}
		return $emails;
	}

	/**
	 * Hook in all transactional emails.
	 */
	public function email_actions($actions) {
		$statuses = wc_sa_get_statuses();
		foreach ($statuses as $id => $value) {
            $actions[] = 'woocommerce_order_status_' . $value->label;
		}
		return $actions;
	}

    public function core_email_filters()
    {
        // foreach ($this->core_emails as $status => $email){
        //     add_filter('woocommerce_email_recipient_' . $email, array($this, 'filter_recipient'), 10, 2);
        //     add_filter('woocommerce_email_subject_' . $email, array($this, 'filter_subject'), 10, 2);
        //     add_filter('woocommerce_email_heading_' . $email, array($this, 'filter_heading'), 10, 2);
        //     add_filter('woocommerce_email_enabled_' . $email, array($this, 'filter_enabled'), 10, 2);
        // }
        add_filter('woocommerce_email_enabled_new_order', array($this, 'filter_new_order_enabled'), 10, 2);
        add_filter('woocommerce_email_attachments', array($this, 'filter_attachments'), 10, 3);
        add_filter('woocommerce_email_from_name', array($this, 'filter_from_name'), 10, 2);
        add_filter('woocommerce_email_from_address', array($this, 'filter_from_address'), 10, 2);
        add_filter('woocommerce_email_get_option', array($this, 'filter_option'), 10, 5);
        add_action('woocommerce_email_settings_before', array($this, 'setup_email_settings'));
        add_action('updated_option', array($this, 'update_email_settings'), 10, 3);
	}

    /**
     * @param $recipient
     * @param WC_Order $order
     * @return mixed
     */
    public function filter_recipient($recipient, $order)
    {
        // Do not override email notifications for core statuses.
        if ( $order && wc_sa_is_core_status( $order->get_status() ) ) {
            return $recipient;
        }

        $status = $order ? wc_sa_get_status_by_name($order->get_status()) : false;
        $_recipient = "";
        if($status){
            $notification = get_post_meta($status->id, '_email_notification', true);
            if(!empty($notification)){
                switch ($status->email_recipients){
                    case 'custom':
                        $_recipient = $status->email_custom_address;
                        break;
                    case 'customer':
                        $_recipient = $order->get_billing_email();
                        break;
                    case 'admin':
                        $_recipient = get_bloginfo('admin_email');
                        break;
                    case 'both':
                        $_recipient = implode(',', array(get_bloginfo('admin_email'), $order->get_billing_email()));
                }
                if(!empty($_recipient)){
                    $recipient = $_recipient;
                }
            }
        }
        return $recipient;
	}

    /**
     * @param $subject
     * @param WC_Order $order
     * @return mixed
     */
    public function filter_subject($subject, $order)
    {
        // Do not override email notifications for core statuses.
        if ( $order && wc_sa_is_core_status( $order->get_status() ) ) {
            return $subject;
        }

        $status = $order ? wc_sa_get_status_by_name($order->get_status()) :  false;
        if($status){
            $subject = !empty($status->email_subject) ? $status->email_subject : $subject;
        }
        return $subject;
    }

    public function filter_heading($heading, $order)
    {
        // Do not override email notifications for core statuses.
        if ( $order && wc_sa_is_core_status( $order->get_status() ) ) {
            return $heading;
        }

        if(!empty($heading)){
            $status = $order ? wc_sa_get_status_by_name($order->get_status()) : false;
            if($status){
                $heading = !empty($status->email_heading) ? $status->email_heading : $heading;
            }
        }
        return $heading;
    }

    public function filter_attachments($attachments, $id, $order)
    {
        $id = array_search($id, $this->core_emails);
        if(!empty($id)){
            $status = wc_sa_get_status_by_name($id);
            if($status && wc_sa_is_core_status($id)) {
                $_attachments = $status->get_attachments();
                $attachments = is_array($_attachments) ? array_merge($attachments, $_attachments) : $attachments;
            }
        }
        return $attachments;
    }

    public function filter_from_name($from_name, $email)
    {
        $status = isset($email->object) && ($email->object instanceof WC_Order) ? wc_sa_get_status_by_name($email->object->get_status()) : false;
        if($status){
            // Do not override email notifications for core statuses.
            if ( wc_sa_is_core_status( $email->object->get_status() ) ) {
                return $from_name;
            }

            $from_name = $status->email_from_name;
        }
        return $from_name;
    }

    public function filter_from_address($from_address, $email)
    {
        $status = isset($email->object) && ($email->object instanceof WC_Order)  ? wc_sa_get_status_by_name($email->object->get_status()) : false;
        if($status){
            // Do not override email notifications for core statuses.
            if ( wc_sa_is_core_status( $email->object->get_status() ) ) {
                return $from_address;
            }

            $from_address = $status->email_from_address;
        }
        return $from_address;
    }

    public function filter_enabled($enabled, $order)
    {
        // Do not override email notifications for core statuses.
        if ( $order && wc_sa_is_core_status( $order->get_status() ) ) {
            return $enabled;
        }

        $status = $order ? wc_sa_get_status_by_name($order->get_status()) : false;
        if($status){
            $notification = get_post_meta($status->id, '_email_notification', true);
            if(!empty($notification)){
                $enabled = $status->email_notification == "yes";
            }
        }
        return $enabled;
    }

    public function filter_new_order_enabled($enabled, $order)
    {
        // Do not override email notifications for core statuses.
        if ( $order && wc_sa_is_core_status( $order->get_status() ) ) {
            return $enabled;
        }

        $status = $order ? wc_sa_get_status_by_name($order->get_status()) : false;
        if($status){
            $notification = get_post_meta($status->id, '_email_notification', true);
            if(!empty($notification)){
                $enabled =  false;
            }
        }
        return $enabled;
    }

    /**
     * @param $val
     * @param WC_Email $instance
     * @param $value
     * @param $key
     * @param $empty_value
     * @return bool
     */
    function filter_option($val, $instance, $value, $key, $empty_value)
    {
        // Do not override email notifications for core statuses.
        if ( wc_sa_is_core_status( array_search($instance->id, $this->core_emails) ) ) {
            return $val;
        }
    
        if($key === 'enabled' && in_array($instance->id, $this->core_emails)){
            $slug = array_search($instance->id, $this->core_emails);
            if($slug){
                $status = wc_sa_get_status_by_name($slug);
                if($status){
                    $val = $status->email_notification;
                }
            }
        }

        return $val;
    }

    function update_email_settings($option, $old_value, $value)
    {
        foreach ($this->core_emails as $name => $email){
            if($option === 'woocommerce_' . $email . '_settings' && is_array($value)){
                $status = wc_sa_get_status_by_name($name);
                if($status){
                    update_post_meta($status->id, '_email_notification', $value['enabled']);
                }
            }
        }
    }


    /**
     * @param WC_Email $email_instance
     */
    function setup_email_settings($email_instance)
    {
        foreach ($this->core_emails as $name => $email){
            if($email_instance->id === $email){
                $status = wc_sa_get_status_by_name($name);
                if($status && $email_instance->settings){
                    $settings = $email_instance->settings;
                    $settings['enabled'] = $status->email_notification;
                    update_option($email_instance->plugin_id . $email_instance->id . '_settings', $settings);
                }
            }
        }
    }
}

return new WC_SA_Emails();