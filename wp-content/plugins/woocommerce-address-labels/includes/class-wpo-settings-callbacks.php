<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !class_exists( 'WPO_Settings_Callbacks' ) ) :

class WPO_Settings_Callbacks {
	/**
	 * Section null callback.
	 *
	 * @return void.
	 */
	public function section() {
	}

	/**
	 * Checkbox callback.
	 *
	 * args:
	 *   option_name - name of the main option
	 *   id          - key of the setting
	 *   value       - value if not 1 (optional)
	 *   default     - default setting (optional)
	 *   description - description (optional)
	 *
	 * @return void.
	 */
	public function checkbox( $args ) {
		extract( $this->normalize_settings_args( $args ) );

		// output checkbox	
		printf( '<input type="checkbox" id="%1$s" name="%2$s[%1$s]" value="%3$s"%4$s />', $id, $option_name, $value, checked( $value, $current, false ) );
	
		// output description.
		if ( isset( $description ) ) {
			printf( '<p class="description">%s</p>', $description );
		}
	}

	/**
	 * Text input callback.
	 *
	 * args:
	 *   option_name - name of the main option
	 *   id          - key of the setting
	 *   size        - size of the text input (em)
	 *   default     - default setting (optional)
	 *   description - description (optional)
	 *   type        - type (optional)
	 *
	 * @return void.
	 */
	public function text_input( $args ) {
		extract( $this->normalize_settings_args( $args ) );

		if (empty($type)) {
			$type = 'text';
		}

		printf( '<input type="%1$s" id="%2$s" name="%3$s[%2$s]" value="%4$s" size="%5$s"/>', $type, $id, $option_name, $current, $size );
	
		// output description.
		if ( isset( $description ) ) {
			printf( '<p class="description">%s</p>', $description );
		}
	}

	/**
	 * Textarea callback.
	 *
	 * args:
	 *   option_name - name of the main option
	 *   id          - key of the setting
	 *   width       - width of the text input (em)
	 *   height      - height of the text input (lines)
	 *   default     - default setting (optional)
	 *   description - description (optional)
	 *
	 * @return void.
	 */
	public function textarea( $args ) {
		extract( $this->normalize_settings_args( $args ) );
	
		printf( '<textarea id="%1$s" name="%2$s[%1$s]" cols="%4$s" rows="%5$s"/>%3$s</textarea>', $id, $option_name, $current, $width, $height );
	
		// output description.
		if ( isset( $description ) ) {
			printf( '<p class="description">%s</p>', $description );
		}
	}

	/**
	 * Select element callback.
	 *
	 * @param  array $args Field arguments.
	 *
	 * @return string	  Select field.
	 */
	public function select( $args ) {
		extract( $this->normalize_settings_args( $args ) );
	
		printf( '<select id="%1$s" name="%2$s[%1$s]">', $id, $option_name );

		foreach ( $options as $key => $label ) {
			printf( '<option value="%s"%s>%s</option>', $key, selected( $current, $key, false ), $label );
		}

		echo '</select>';

		if (isset($custom)) {
			printf( '<div class="%1$s_custom custom">', $id );

			switch ($custom['type']) {
				case 'text_element_callback':
					$this->text_input( $custom['args'] );
					break;		
				case 'multiple_text_element_callback':
					$this->multiple_text_input( $custom['args'] );
					break;		
				default:
					break;
			}
			echo '</div>';
		}
	
		// Displays option description.
		if ( isset( $args['description'] ) ) {
			printf( '<p class="description">%s</p>', $args['description'] );
		}

	}

	/**
	 * Multiple text element callback.
	 * @param  array $args Field arguments.
	 * @return string	   Text input field.
	 */
	public function multiple_text_input( $args ) {
		extract( $this->normalize_settings_args( $args ) );

		if (!empty($header)) {
			echo "<p><strong>{$header}</strong>:</p>";
		}

		foreach ($fields as $name => $field) {
			$label = $field['label'];
			$size = $field['size'];

			if (isset($field['label_width'])) {
				$style = sprintf( 'style="display:inline-block; width:%1$s;"', $field['label_width'] );
			} else {
				$style = '';
			}

			$suffix = isset($field['suffix']) ? $field['suffix'] : '';

			// output field label
			printf( '<label for="%1$s_%2$s" %3$s>%4$s</label>', $id, $name, $style, $label );

			// output field
			$field_current = isset($current[$name]) ? $current[$name] : '';
			printf( '<input type="text" id="%1$s_%3$s" name="%2$s[%1$s][%3$s]" value="%4$s" size="%5$s"/>%6$s<br/>', $id, $option_name, $name, $field_current, $size, $suffix );

		}
	
		// Displays option description.
		if ( isset( $args['description'] ) ) {
			printf( '<p class="description">%s</p>', $args['description'] );
		}
	}

	/**
	 * Wrapper function to create tabs for settings in different languages
	 * @param  [type] $args     [description]
	 * @param  [type] $callback [description]
	 * @return [type]           [description]
	 */
	public function i18n_wrap ( $args ) {
		extract( $this->normalize_settings_args( $args ) );

		if ( $languages = $this->get_languages() ) {
			printf( '<div id="%s-%s-translations" class="translations">', $option_name, $id)
			?>
				<ul>
					<?php foreach ( $languages as $lang_code => $language_name ) {
						$translation_id = "{$option_name}_{$id}_{$lang_code}";
						printf('<li><a href="#%s">%s</a></li>', $translation_id, $language_name );
					}
					?>
				</ul>
				<?php foreach ( $languages as $lang_code => $language_name ) {
					$translation_id = "{$option_name}_{$id}_{$lang_code}";
					printf( '<div id="%s">', $translation_id );
					$args['lang'] = $lang_code;
					call_user_func( array( $this, $callback ), $args );
					echo '</div>';
				}
				?>
			
			</div>
			<?php
		} else {
			call_user_func( $callback, $args );
		}
	}

	public function get_languages () {
		$wpml = class_exists('SitePress');
		// $wpml = true; // for development

		if ($wpml) {
			// use this instead of function call for development outside of WPML
			// $icl_get_languages = 'a:3:{s:2:"en";a:8:{s:2:"id";s:1:"1";s:6:"active";s:1:"1";s:11:"native_name";s:7:"English";s:7:"missing";s:1:"0";s:15:"translated_name";s:7:"English";s:13:"language_code";s:2:"en";s:16:"country_flag_url";s:43:"http://yourdomain/wpmlpath/res/flags/en.png";s:3:"url";s:23:"http://yourdomain/about";}s:2:"fr";a:8:{s:2:"id";s:1:"4";s:6:"active";s:1:"0";s:11:"native_name";s:9:"Français";s:7:"missing";s:1:"0";s:15:"translated_name";s:6:"French";s:13:"language_code";s:2:"fr";s:16:"country_flag_url";s:43:"http://yourdomain/wpmlpath/res/flags/fr.png";s:3:"url";s:29:"http://yourdomain/fr/a-propos";}s:2:"it";a:8:{s:2:"id";s:2:"27";s:6:"active";s:1:"0";s:11:"native_name";s:8:"Italiano";s:7:"missing";s:1:"0";s:15:"translated_name";s:7:"Italian";s:13:"language_code";s:2:"it";s:16:"country_flag_url";s:43:"http://yourdomain/wpmlpath/res/flags/it.png";s:3:"url";s:26:"http://yourdomain/it/circa";}}';
			// $icl_get_languages = unserialize($icl_get_languages);
			
			$icl_get_languages = icl_get_languages('skip_missing=0');
			$languages = array();
			foreach ($icl_get_languages as $lang => $data) {
				$languages[$data['language_code']] = $data['native_name'];
			}
		} else {
			return false;
		}

		return $languages;
	}

	public function normalize_settings_args ( $args ) {
		$args['value'] = isset( $args['value'] ) ? $args['value'] : 1;
	
		// get main settings array
		$option = get_option( $args['option_name'] );
	
		$args['setting_name'] = "{$args['option_name']}[{$args['id']}]";

		if (isset($args['lang'])) {
			// i18n settings name
			$args['setting_name'] = "{$args['setting_name']}[{$args['lang']}]";
			// copy current option value if set
			if ( isset( $option[$args['id']][$args['lang']] ) ) {
				$args['current'] = $option[$args['id']][$args['lang']];
			}			
		} else {
			// copy current option value if set
			if ( isset( $option[$args['id']] ) ) {
				$args['current'] = $option[$args['id']];
			}
		}

		// falback to default or empty if no value in option
		if ( !isset($args['current']) ) {
			$args['current'] = isset( $args['default'] ) ? $args['default'] : '';
		}		

		return $args;
	}

	/**
	 * Validate options.
	 *
	 * @param  array $input options to valid.
	 *
	 * @return array		validated options.
	 */
	public function validate( $input ) {
		// echo '<pre>';var_dump($input);die('</pre>');
		// Create our array for storing the validated options.
		$output = array();

		if (empty($input) || !is_array($input)) {
			return $input;
		}
	
		// Loop through each of the incoming options.
		foreach ( $input as $key => $value ) {
	
			// Check to see if the current option has a value. If so, process it.
			if ( isset( $input[$key] ) ) {
				if ( is_array( $input[$key] ) ) {
					foreach ( $input[$key] as $sub_key => $sub_value ) {
						$output[$key][$sub_key] = $input[$key][$sub_key];
					}
				} else {
					$output[$key] = $input[$key];
				}
			}
		}
	
		// Return the array processing any additional functions filtered by this action.
		return apply_filters( 'wpo_wcpdf_validate_input', $output, $input );
	}
}


endif; // class_exists

return new WPO_Settings_Callbacks();