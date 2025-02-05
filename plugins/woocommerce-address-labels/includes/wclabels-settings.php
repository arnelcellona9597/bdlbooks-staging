<?php
if ( !class_exists( 'WooCommerce_Address_Labels_Settings' ) ) {

	class WooCommerce_Address_Labels_Settings {

		/**
		 * @var WPO_WCLabels_Settings_Callbacks
		 */
		public $callbacks;

		/**
		 * @var array
		 */
		public $layout_settings;

		/**
		 * @var array
		 */
		public $interface_settings;

		/**
		 * @var array
		 */
		public $label_settings;

		/**
		 * @var string|false
		 */
		public $options_page_hook;

		public function __construct() {
			$this->callbacks = include( 'class-wclabels-settings-callbacks.php' );
			add_action( 'admin_menu', array( $this, 'menu' ) );
			add_action( 'admin_init', array( $this, 'layout_settings' ) );
			add_action( 'admin_init', array( $this, 'label_settings' ) );

			// set options capability to allow shop managers to edit settings
			add_filter( 'option_page_capability_wpo_wclabels_layout_settings', array( $this, 'settings_capabilities' ) );
			add_filter( 'option_page_capability_wpo_wclabels_label_settings', array( $this, 'settings_capabilities' ) );


			add_filter( 'plugin_action_links_'.WPO_WCLABELS()->plugin_basename, array( $this, 'add_settings_link' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts_styles' ) ); // Load scripts & styles

			$this->layout_settings    = get_option( 'wpo_wclabels_layout_settings', array() );
			$this->interface_settings = get_option( 'wpo_wclabels_interface_settings', array() );
			$this->label_settings     = get_option( 'wpo_wclabels_label_settings', array() );
		}

		public function menu() {
			$parent_slug = 'woocommerce';

			$this->options_page_hook = add_submenu_page(
				$parent_slug,
				__( 'Address Labels', 'wpo_wclabels' ),
				__( 'Address Labels', 'wpo_wclabels' ),
				'manage_woocommerce',
				'wpo_wclabels_options_page',
				array( $this, 'settings_page' )
			);
		}
		
		/**
		 * Set capability for settings page
		 */
		public function settings_capabilities() {
			return 'manage_woocommerce';
		}

		/**
		 * Scrips & styles for settings page
		 */
		public function load_scripts_styles($hook) {
			if( $hook != $this->options_page_hook ) 
				return;

			wp_enqueue_style(
				'wclabels-admin-styles', // handle
				WPO_WCLABELS()->plugin_url() . '/assets/css/wclabels-admin-styles.css', // source
				array(), // dependencies
				WPO_WCLABELS_VERSION, // version
				'all' // media
			);

			wp_enqueue_script(
				'wclabels-admin-scripts',
				WPO_WCLABELS()->plugin_url() . '/assets/js/wclabels-admin-scripts.js',
				array( 'jquery' ),
				WPO_WCLABELS_VERSION
			);

		
			$url_prefix = is_ssl() ? 'https://' : 'http://';
			wp_enqueue_style('jquery-style', $url_prefix.'ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');

			wp_enqueue_style( 'wclabels-admin-google-fonts', $this->callbacks->get_fonts_url() );
		}

		/**
		 * Add settings link to plugins page
		 */
		public function add_settings_link( $links ) {
			$settings_link = '<a href="admin.php?page=wpo_wclabels_options_page">'. __( 'Settings', 'woocommerce' ) . '</a>';
			array_push( $links, $settings_link );
			return $links;
		}
		
		public function settings_page() {
			settings_errors();
			$current_tab   = $_REQUEST['tab'] = $_REQUEST['tab'] ?? 'export';
			$settings_tabs = apply_filters( 'wpo_wclabels_settings_tabs', array (
					'export' => __( 'Export','wpo_wclabels' ),
					'layout' => __( 'Page layout','wpo_wclabels' ),
					'label'  => __( 'Label contents','wpo_wclabels' ),
				)
			);
			?>
			<div class="wrap">
				<div class="wpo_wclabels_settings">
					<h2><?php _e( 'WooCommerce Print Address Labels', 'wpo_wclabels' ); ?></h2>
					<!-- Main menu -->
					<?php do_action( 'wpo_wclabels_before_settings_tabs', $current_tab ); ?>
					<?php do_action_deprecated( 'wpo_wclabels_before_settings_page', array( $current_tab ), '1.8.1', 'wpo_wclabels_before_settings_tabs' ); ?>
					<h2 class="nav-tab-wrapper">
						<?php
						foreach ( $settings_tabs as $key => $label ) {
							$class = 'nav-tab';
							if ( $current_tab == $key ) {
								$class .= ' nav-tab-active';
							}
							$tab_url = add_query_arg( array(
								'page' => 'wpo_wclabels_options_page',
								'tab'  => $key,
							), admin_url( 'admin.php' ) );
							printf( '<a href="%s" class="%s">%s</a>', esc_url( $tab_url ), $class, $label );
						}
						?>
					</h2>
					<?php do_action( 'wpo_wclabels_after_settings_tabs', $current_tab ); ?>
					<div class="wpo_wclabels_settings_container">
						<?php do_action( 'wpo_wclabels_before_settings_tab_content', $current_tab ); ?>
						<?php do_action_deprecated( 'wpo_wclabels_before_settings', array( $current_tab ), '1.8.1', 'wpo_wclabels_before_settings_tab_content' ); ?>
						<div class="wpo_wclabels_settings_tab">
							<?php if ( $current_tab == 'export' ) : ?>
								<?php include( 'wclabels-status-export.php' ); ?>
							<?php else: ?>
								<form method="post" action="options.php" id="wpo-wclabels-settings">
									<?php settings_fields( 'wpo_wclabels_'.$current_tab.'_settings' ); ?>
									<?php do_settings_sections( 'wpo_wclabels_'.$current_tab.'_settings' ); ?>
									<?php submit_button(); ?>
								</form>
							<?php endif; ?>
						</div>
						<?php do_action( 'wpo_wclabels_settings_tab_content', $current_tab ); ?>
						<?php do_action( 'wpo_wclabels_after_settings_tab_content', $current_tab ); ?>
						<?php do_action_deprecated( 'wpo_wclabels_after_settings', array( $current_tab ), '1.8.1', 'wpo_wclabels_after_settings_tab_content' ); ?>
						<?php do_action_deprecated( 'wpo_wclabels_after_settings_page', array( $current_tab ), '1.8.1', 'wpo_wclabels_after_settings_tab_content' ); ?>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Layout settings.
		 */
		public function layout_settings() {
			$option_group = 'wpo_wclabels_layout_settings';

			// Register settings.
			$option_name = 'wpo_wclabels_layout_settings';
			register_setting( $option_group, $option_name, array( $this->callbacks, 'validate' ) );

			// Create option in wp_options.
			if ( false === get_option( $option_name ) ) {
				$this->default_settings( $option_name );
			}
		
			// Section.
			add_settings_section(
				'template_settings',
				__( 'Template settings', 'wpo_wclabels' ),
				array( $this->callbacks, 'section' ),
				$option_group
			);

			add_settings_field(
				'paper_size',
				__( 'Paper format', 'wpo_wclabels' ),
				array( $this->callbacks, 'select' ),
				$option_group,
				'template_settings',
				array(
					'option_name'	=> $option_name,
					'id'			=> 'paper_size',
					'options' 		=> array(
						'a4'		=> __( 'A4' , 'wpo_wclabels' ),
						'letter'	=> __( 'Letter' , 'wpo_wclabels' ),
						'custom'	=> __( 'Custom size (enter below)' , 'wpo_wclabels' ),
					),
					'custom'		=> array(
						'type'		=> 'multiple_text_element_callback',
						'args'		=> array(
							'option_name'	=> $option_name,
							'id'			=> 'custom_paper_size',
							'fields'		=> array(
								'width'		=> array(
									'label'			=> __( 'Width:' , 'wpo_wclabels' ),
									'suffix'		=> 'mm',
									'label_width'	=> '100px',
									'size'			=> '5',
								),
								'height'	=> array(
									'label'			=> __( 'Height:' , 'wpo_wclabels' ),
									'suffix'		=> 'mm',
									'label_width'	=> '100px',
									'size'			=> '5',
								),
							),
						),
					),
				)
			);

			add_settings_field(
				'paper_orientation',
				__( 'Paper orientation', 'wpo_wclabels' ),
				array( $this->callbacks, 'select' ),
				$option_group,
				'template_settings',
				array(
					'option_name'	=> $option_name,
					'id'			=> 'paper_orientation',
					'options' 		=> array(
						'portrait'	=> __( 'Portrait' , 'wpo_wclabels' ),
						'landscape'	=> __( 'Landscape' , 'wpo_wclabels' ),
					),
				)
			);

			add_settings_field(
				'page_margins',
				__( 'Page layout', 'wpo_wclabels' ),
				array( $this->callbacks, 'multiple_text_input' ),
				$option_group,
				'template_settings',
				array(
					'option_name'	=> $option_name,
					'id'			=> 'page_margins',
					'header'		=> __( 'Margins' , 'wpo_wclabels' ),
					'fields'		=> array(
						'top'		=> array(
							'label'			=> __( 'Top' , 'wpo_wclabels' ),
							'suffix'		=> 'mm',
							'label_width'	=> '100px',
							'size'			=> '5',
						),
						'bottom'	=> array(
							'label'			=> __( 'Bottom' , 'wpo_wclabels' ),
							'suffix'		=> 'mm',
							'label_width'	=> '100px',
							'size'			=> '5',
						),
						'left'	=> array(
							'label'			=> __( 'Left' , 'wpo_wclabels' ),
							'suffix'		=> 'mm',
							'label_width'	=> '100px',
							'size'			=> '5',
						),
						'right'	=> array(
							'label'			=> __( 'Right' , 'wpo_wclabels' ),
							'suffix'		=> 'mm',
							'label_width'	=> '100px',
							'size'			=> '5',
						),
					),
				)
			);

			add_settings_field(
				'page_layout',
				__( 'Page layout', 'wpo_wclabels' ),
				array( $this->callbacks, 'page_layout' ),
				$option_group,
				'template_settings',
				array(
					'option_name'	=> $option_name,
				)
			);

			add_settings_field(
				'custom_styles',
				__( 'Custom styles', 'wpo_wclabels' ),
				array( $this->callbacks, 'textarea' ),
				$option_group,
				'template_settings',
				array(
					'option_name'	=> $option_name,
					'id'			=> 'custom_styles',
					'width'			=> '42',
					'height'		=> '8',
					'description'	=> __( 'Enter custom CSS styles for the address labels here', 'wpo_wclabels' ),
				)
			);

			add_settings_field(
				'ignore_page_size_orientation',
				__( 'Let printer determine media size', 'wpo_wclabels' ),
				array( $this->callbacks, 'checkbox' ),
				$option_group,
				'template_settings',
				array(
					'option_name'	=> $option_name,
					'id'			=> 'ignore_page_size_orientation',
					'description'	=> __( 'Enable this to set the final media size in the print dialog rather than passing the above size/orientation settings', 'wpo_wclabels' ),
				)
			);


			// Section.
			add_settings_section(
				'interface_settings',
				__( 'Interface settings', 'wpo_wclabels' ),
				array( $this->callbacks, 'section' ),
				$option_group
			);

			// separate option name within this group
			$option_name = 'wpo_wclabels_interface_settings';
			register_setting( $option_group, $option_name, array( $this->callbacks, 'validate' ) );

			add_settings_field(
				'offset',
				__( 'Ask for offset', 'wpo_wclabels' ),
				array( $this->callbacks, 'checkbox' ),
				$option_group,
				'interface_settings',
				array(
					'option_name'	=> $option_name,
					'id'			=> 'offset',
					'description'	=>  __( 'This option enables you to start printing on for example the 3rd label', 'wpo_wclabels' ),
				)
			);

			add_settings_field(
				'preview',
				__( 'Enable preview', 'wpo_wclabels' ),
				array( $this->callbacks, 'checkbox' ),
				$option_group,
				'interface_settings',
				array(
					'option_name'	=> $option_name,
					'id'			=> 'preview',
					'description'	=> __( 'Open the address labels in a new browser tab instead of printing directly', 'wpo_wclabels' ),
				)
			);
		}

		
		/**
		 * Label settings.
		 */
		public function label_settings() {
			$option_group = 'wpo_wclabels_label_settings';

			// Register settings.
			$option_name = 'wpo_wclabels_label_settings';
			register_setting( $option_group, $option_name, array( $this->callbacks, 'validate' ) );
		
			// Create option in wp_options.
			if ( false === get_option( $option_name ) ) {
				$this->default_settings( $option_name );
			}

			// Section.
			add_settings_section(
				'label_contents',
				__( 'Label contents', 'wpo_wclabels' ),
				array( $this->callbacks, 'contents_section' ),
				$option_group
			);

			add_settings_field(
				'image_placeholders',
				__( 'Image placeholders', 'wpo_wclabels' ),
				array( $this->callbacks, 'image_placeholders' ),
				$option_group,
				'label_contents',
				array(
					'option_name'	=> $option_name,
					'id'			=> 'image_placeholders',
				)
			);

			add_settings_field(
				'address_data',
				__( 'Address/order data', 'wpo_wclabels' ),
				array( $this->callbacks, 'textarea' ),
				$option_group,
				'label_contents',
				array(
					'option_name'	=> $option_name,
					'id'			=> 'address_data',
					'width'			=> '42',
					'height'		=> '8',
					'default'		=> '[shipping_address]',
					'description'	=> __( 'Leave empty to use the default formatting.', 'wpo_wclabels'),
				)
			);

			add_settings_field(
				'disable_nl2br',
				__( 'Format without line breaks', 'wpo_wclabels' ),
				array( $this->callbacks, 'checkbox' ),
				$option_group,
				'label_contents',
				array(
					'option_name'	=> $option_name,
					'id'			=> 'disable_nl2br',
					'description'	=> __( 'Enable this when you want to use HTML for the address data - line breaks will otherwise be converted to <code>&lt;br&gt;</code> tags', 'wpo_wclabels' ),
				)
			);

			add_settings_field(
				'remove_whitespace',
				__( 'Remove empty lines', 'wpo_wclabels' ),
				array( $this->callbacks, 'checkbox' ),
				$option_group,
				'label_contents',
				array(
					'option_name'	=> $option_name,
					'id'			=> 'remove_whitespace',
					'description'	=> __( 'Enable this option if you want to remove empty lines left over from empty address/placeholder replacements', 'wpo_wclabels' ),
				)
			);

			add_settings_field(
				'block_width',
				__( 'Address block width', 'wpo_wclabels' ) . '<br>' . sprintf('<img src="%s"/ style="width:100px;margin-top:5px;">', WPO_WCLABELS()->plugin_url() . '/assets/images/label-width-explanation.png' ),
				array( $this->callbacks, 'text_input' ),
				$option_group,
				'label_contents',
				array(
					'option_name'	=> $option_name,
					'id'			=> 'block_width',
					'size'			=> '5',
					'default'		=> '5cm',
					'description'	=> __( 'For consistent output, the address is placed in a fixed width block that is centered on the label.', 'wpo_wclabels' ) . '<br/>'
										. __( 'Enter any value in cm, mm, px or in - use a dot (and not a comma!) as the decimal separator!', 'wpo_wclabels' ),
				)
			);

			// Section.
			add_settings_section(
				'text_formatting',
				__( 'Text formatting', 'wpo_wclabels' ),
				array( $this->callbacks, 'section' ),
				$option_group
			);

			add_settings_field(
				'font',
				__( 'Font', 'wpo_wclabels' ),
				array( $this->callbacks, 'google_webfonts' ),
				$option_group,
				'text_formatting',
				array(
					'option_name'	=> $option_name,
					'id'			=> 'font',
				)
			);

			add_settings_field(
				'font_size',
				__( 'Font size', 'wpo_wclabels' ),
				array( $this->callbacks, 'select' ),
				$option_group,
				'text_formatting',
				array(
					'option_name'	=> $option_name,
					'id'			=> 'font_size',
					'options' 		=> array(
						'8'		=> '8pt',
						'9'		=> '9pt',
						'10'	=> '10pt',
						'11'	=> '11pt',
						'12'	=> '12pt',
						'14'	=> '14pt',
						'16'	=> '16pt',
						'18'	=> '18pt',
						'20'	=> '20pt',
						'24'	=> '24pt',
					),
					'default'		=> '12',
				)
			);
		}

		/**
		 * Set default settings.
		 */
		public function default_settings( $option ) {
			switch ( $option ) {
				case 'wpo_wclabels_layout_settings':
					$default = array(
						'cols'				=> '2',
						'rows'				=> '2',
						'paper_size'		=> 'a4',
						'paper_orientation'	=> 'portrait',
					);
					break;
				case 'wpo_wclabels_label_settings':
					$default = array(
						'address_data'		=> '[shipping_address]',
						'font_size'			=> '12',
						'block_width'		=> '5cm',
					);
					break;
				default:
					$default = array();
					break;
			}

			if ( false === get_option( $option ) ) {
				add_option( $option, $default );
			} else {
				update_option( $option, $default );

			}
		}

		
	} // end class
} // end class_exists