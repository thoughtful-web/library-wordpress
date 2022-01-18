<?php
/**
 * The file that provides a class which serves as a base for creating Field classes for the Settings API.
 *
 * @package    ThoughtfulWeb\LibraryWP
 * @subpackage Settings
 * @author     Zachary Kendall Watkins <watkinza@gmail.com>
 * @copyright  Zachary Kendall Watkins 2022
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0-or-later
 * @link       https://github.com/thoughtful-web/settings-page-wp/blob/master/admin/page/settings/field.php
 * @since      0.1.0
 */

declare(strict_types=1);
namespace ThoughtfulWeb\LibraryWP\Admin\Page\Settings;

use \ThoughtfulWeb\LibraryWP\Admin\Page\Settings\Sanitize;

/**
 * The Field class.
 *
 * @since 0.1.0
 */
class Field {

	/**
	 * The default values for required $field members.
	 *
	 * @var array $default_field The default field parameter member values.
	 */
	protected $default_field = array(
		'type'        => 'text',
		'placeholder' => '',
		'data_args'   => array(
			'sanitize_callback' => true,
			'show_in_rest'      => false,
			'type'              => 'string',
			'description'       => '',
		),
	);

	/**
	 * Allowed HTML.
	 *
	 * @var array $allowed_html The allowed HTML for the element produced by this class.
	 */
	protected $allowed_html = array(
		'input' => array(
			'class'        => true,
			'data-*'       => true,
			'autocomplete' => true,
			'disabled'     => true,
			'id'           => true,
			'list'         => true,
			'maxlength'    => true,
			'minlength'    => true,
			'name'         => true,
			'pattern'      => true,
			'placeholder'  => true,
			'readonly'     => true,
			'required'     => true,
			'size'         => true,
			'spellcheck'   => true,
			'type'         => 'text',
			'value'        => true,
		),
	);

	/**
	 * Stored field value.
	 *
	 * @var array $field The registered field arguments.
	 */
	protected $field;

	/**
	 * Name the group of database options which the fields represent.
	 *
	 * @var string $option_group The database option group name. Lowercase letters and underscores only. If not configured it will default  to the menu_slug method argument with hyphens replaced with underscores.
	 */
	protected $option_group;

	/**
	 * Constructor for the Field class.
	 *
	 * @param array  $field {
	 *     The field registration arguments.
	 *
	 *     @type string $label       Formatted title of the field. Shown as the label for the field during output. Required.
	 *     @type string $id          Slug-name to identify the field. Used in the 'id' attribute of tags. Required.
	 *     @type string $type        The type attribute. Required.
	 *     @type string $description The description shown beneath the form field. Optional.
	 *     @type mixed  $placeholder The placeholder text, if applicable. Optional.
	 *     @type mixed  $label_for   When supplied, the setting title will be wrapped in a `<label>` element, its `for` attribute populated with this value. Optional.
	 *     @type mixed  $class       CSS Class to be added to the `<tr>` element when the field is output. Optional.
	 *     @type array  $data_args {
	 *         Data used to describe the setting when registered. Required.
	 *
	 *         @type string             $option_name       The option name. If not provided, will default to the ID attribute of the HTML element. Optional.
	 *         @type mixed              $default           Default value when calling `get_option()`. Optional.
	 *         @type boolean|callable   $sanitize_callback (Optional) A callback function that sanitizes the field's database option value. Hooked to the filter "sanitize_option_{$option_name}" with $option_name equal to this field's $id value. This hook is run by the "sanitize_option()" function which is executed within Settings API functions like "add_option", "update_option", etc.
	 *         @type bool|array         $show_in_rest      Whether data associated with this setting should be included in the REST API. When registering complex settings, this argument may optionally be an array with a 'schema' key.
	 *         @type string             $type              The type of data associated with this setting. Only used for the REST API. Valid values are 'string', 'boolean', 'integer', 'number', 'array', and 'object'.
	 *         @type string             $description       A description of the data attached to this setting for a REST API response.
	 *     }
	 * }
	 * @param string $menu_slug         The slug-name of the settings page on which to show the section (general, reading, writing, ...).
	 * @param string $section_id   The slug-name of the section of the settings page in which to show the box.
	 * @param string $option_group Name the group of database options which the fields represent.
	 */
	public function __construct( $field, $menu_slug, $section_id, $option_group ) {

		$this->option_group = $option_group;

		// Merge user-defined field values with default values.
		$field = $this->apply_defaults( $field );

		// A callback function that sanitizes the option's value.
		$callback = $field['data_args']['sanitize_callback'];
		if ( false !== boolval( $callback ) && ! is_callable( $callback ) ) {
			$field['data_args']['sanitize_callback'] = array( $this, 'sanitize' );
		}

		// Store the merged field.
		$this->field = $field;

		// Register the option.
		if ( false === get_option( $field['id'] ) ) {
			add_filter( "sanitize_option_{$field['id']}", array( $this, 'sanitize' ) );
			add_filter( "default_option_{$field['id']}", array( $this, 'default_option' ) );
			if ( array_key_exists( 'default', $field['data_args'] ) ) {
				add_option( $field['id'], $field['data_args']['default'] );
			} else {
				add_option( $field['id'] );
			}
		}

		/**
		 * Register the setting.
		 * Registers the following hooks, if applicable:
		 * 1. 'sanitize_option_{$option_name}'
		 * 2. 'default_option_{$option_name}'
		 * Runs the following hooks:
		 * 1. apply_filters( 'register_setting_args', ... );
		 * 2. do_action( 'register_setting', ... );
		 */
		register_setting( $option_group, $field['id'], $field['data_args'] );

		// Register the field.
		add_settings_field(
			$field['id'],
			$field['label'],
			array( $this, 'output' ),
			$menu_slug,
			$section_id,
			$field
		);

	}

	/**
	 * Merge user-defined field values with default values.
	 *
	 * @since 0.1.0
	 *
	 * @param array $field The field registration arguments.
	 *
	 * @return array
	 */
	private function apply_defaults( $field ) {

		foreach ( $this->default_field as $key => $default_value ) {
			if ( is_array( $default_value ) ) {
				foreach ( $default_value as $sub_key => $sub_value ) {
					if ( ! array_key_exists( $sub_key, $field[ $key ] ) ) {
						$field[ $key ][ $sub_key ] = $sub_value;
					}
				}
			} elseif ( ! array_key_exists( $key, $field ) ) {
				$field[ $key ] = $default_value;
			}
		}

		return $field;

	}

	/**
	 * Get the default value of the field.
	 *
	 * @param mixed $value Option value passed to the method.
	 * @return mixed
	 */
	public function default_option( $value ) {
		if ( array_key_exists( 'default', $this->field['data_args'] ) ) {
			$value = $this->field['data_args']['default'];
		}
		return $value;
	}

	/**
	 * A callback function that sanitizes the field's database option value. Hooked to the filter
	 * "sanitize_option_{$option_name}" with $option_name equal to this field's $id value. This
	 * hook is run by the "sanitize_option()" function which is executed within Settings API
	 * functions like "add_option", "update_option", etc.
	 *
	 * @param string $value The unsanitized option value.
	 *
	 * @return string
	 */
	public function sanitize( $value ) {

		$sanitizer = new Sanitize( $this->field );
		$value     = $sanitizer->sanitize( $value );
		return $value;

	}

	/**
	 * Get the settings option array and print one of its values.
	 *
	 * @link https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input/text
	 *
	 * @param array $args The arguments needed to render the setting field.
	 *
	 * @return void
	 */
	public function output( $args ) {

		// Assemble the variables necessary to output the form field from settings.
		$value = get_option( $args['id'] );
		if ( empty( $value ) && array_key_exists( 'default', $args['data_args'] ) ) {
			$value = $args['data_args']['default'];
		}
		$extra_attrs = $this->get_optional_attributes( $args );

		// Render the form field output.
		$output = sprintf(
			'<input type="%1$s" id="%2$s" name="%3$s" value="%4$s" %5$s/>',
			esc_attr( $args['type'] ),
			esc_attr( $args['id'] ),
			esc_attr( $args['data_args']['label_for'] ),
			esc_attr( $value ),
			$extra_attrs
		);
		echo wp_kses( $output, $this->allowed_html );

		// Render the description text.
		$this->output_description( $args );

	}

	/**
	 * Echo the Field description.
	 *
	 * @param array $args {
	 *     The arguments needed to render the setting field.
	 *
	 *     @key string $desc The field description.
	 * }
	 *
	 * @return void
	 */
	protected function output_description( $args ) {
		if ( array_key_exists( 'description', $args ) && $args['description'] ) {
			$desc = '<br />' . $args['description'];
			echo wp_kses_post( $desc );
		}
	}

	/**
	 * Get optional attributes of the output element.
	 *
	 * @since 0.1.0
	 *
	 * @param array $field The field parameters.
	 *
	 * @return string
	 */
	protected function get_optional_attributes( $field ) {

		// Determine additional HTML attributes to append to the element.
		$extra_attrs = array();
		// First choose those among the top-level array members.
		$disallowed_data_args_as_attrs = array(
			'type',
			'value',
			'name',
			'id',
		);
		if ( array_key_exists( 'placeholder', $field ) && ! empty( $field['placeholder'] ) ) {
			$extra_attrs['placeholder'] = 'placeholder="' . esc_attr( $field['placeholder'] ) . '"';
		}
		// Then choose those among the data_args array members.
		$field_allowed_html_key = array_keys( $this->allowed_html )[0];
		$field_allowed_html     = $this->allowed_html[ $field_allowed_html_key ];
		foreach ( $field['data_args'] as $attr => $attr_value ) {
			if ( array_key_exists( $attr, $field_allowed_html ) && ! in_array( $attr, $disallowed_data_args_as_attrs, true ) ) {
				$extra_attrs[ $attr ] = $attr . '="' . esc_attr( $attr_value ) . '"';
			}
		}
		// Then combine the results into a string.
		$extra_attrs = ! empty( $extra_attrs ) ? implode( ' ', $extra_attrs ) . ' ' : '';

		return $extra_attrs;

	}
}
