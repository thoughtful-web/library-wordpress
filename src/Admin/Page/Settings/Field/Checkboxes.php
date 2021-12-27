<?php
/**
 * The file that wraps the WordPress Settings API in a file-configurable framework.
 *
 * @package    ThoughtfulWeb\LibraryWP
 * @subpackage Settings
 * @author     Zachary Kendall Watkins <zachwatkins@tapfuel.io>
 * @copyright  2021 Zachary Kendall Watkins
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0-or-later
 * @link       https://github.com/thoughtful-web/library-wp/blob/master/admin/page/settings/field/textareafield.php
 * @since      0.1.0
 */

declare(strict_types=1);
namespace ThoughtfulWeb\LibraryWP\Admin\Page\Settings\Field;

/**
 * The TextField class.
 *
 * @since 0.1.0
 */
class Checkboxes {

	/**
	 * The default values for required $field members.
	 *
	 * @var array $default The default field parameter member values.
	 */
	private $default_field = array(
		'type'        => 'checkbox',
		'desc'        => '',
		'placeholder' => '',
		'data_args'   => array(
			'default'      => array(),
			'show_in_rest' => false,
			'type'         => 'string',
			'description'  => '',
		)
	);

	/**
	 * Allowed HTML.
	 *
	 * @var array $allowed_html The allowed HTML for the element produced by this class.
	 */
	private $allowed_html = array(
		'input' => array(
			'checked'       => true,
			'class'         => true,
			'data-*'        => true,
			'disabled'      => true,
			'id'            => true,
			'name'          => true,
			'readonly'      => true,
			'required'      => true,
			'type'          => 'checkbox',
			'value'         => true,
		),
		'label' => array(
			'for' => true,
		),
		'br' => true,
	);

	/**
	 * Stored field value.
	 *
	 * @var array $field The registered field arguments.
	 */
	private $field;

	/**
	 * Constructor for the Field class.
	 *
	 * @param array $field {
	 *     The field registration arguments.
	 *
	 *     @type string $label       Formatted title of the field. Shown as the label for the field during output. Required.
	 *     @type string $id          Slug-name to identify the field. Used in the 'id' attribute of tags. Required.
	 *     @type string $type        The type attribute. Required.
	 *     @type string $desc        The description. Optional.
	 *     @type mixed  $placeholder The placeholder text, if applicable. Optional.
	 *     @type string $default     The default value. Optional.
	 *     @type mixed  $label_for   When supplied, the setting title will be wrapped in a `<label>` element, its `for` attribute populated with this value. Optional.
	 *     @type mixed  $class       CSS Class to be added to the `<tr>` element when the field is output. Optional.
	 *     @type array  $data_args {
	 *         Data used to describe the setting when registered. Required.
	 *
	 *         @type string     $option_name       The option name. If not provided, will default to the ID attribute of the HTML element. Optional.
	 *         @type mixed      $default           Default value when calling `get_option()`. Optional.
	 *         @type callable   $sanitize_callback A callback function that sanitizes the option's value. Optional.
	 *         @type bool|array $show_in_rest      Whether data associated with this setting should be included in the REST API. When registering complex settings, this argument may optionally be an array with a 'schema' key.
	 *         @type string     $type              The type of data associated with this setting. Only used for the REST API. Valid values are 'string', 'boolean', 'integer', 'number', 'array', and 'object'.
	 *         @type string     $description       A description of the data attached to this setting. Only used for the REST API.
	 *     }
	 * }
	 * @param string $menu_slug         The slug-name of the settings page on which to show the section (general, reading, writing, ...).
	 * @param string $section_id   The slug-name of the section of the settings page in which to show the box.
	 * @param string $option_group Name the group of database options which the fields represent.
	 */
	public function __construct( $field, $menu_slug, $section_id, $option_group ) {

		// Define the option value sanitization callback method.
		$this->default_field['data_args']['sanitize_callback'] = array( $this, 'sanitize' );

		// Merge user-defined field values with default values.
		foreach ( $this->default_field as $key => $default_value ) {
			if ( 'data_args' === $key ) {
				foreach( $default_value as $data_key => $default_data_value ) {
					if ( ! array_key_exists( $data_key, $field[ $key ] ) ) {
						$field[ $key ][ $data_key ] = $default_data_value;
					}
				}
			} elseif ( ! array_key_exists( $key, $field ) ) {
				$field[ $key ] = $default_value;
			}
		}

		// Store the merged field.
		$this->field = $field;

		// Register the setting.
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
	 * Sanitize the text field value.
	 *
	 * @param string $value          The unsanitized option value.
	 * @param string $option         The option name.
	 * @param string $original_value The original value passed to the function.
	 *
	 * @return string
	 */
	public static function sanitize( $value, $option, $original_value ) {

		// Get the predefined choices from the configuration variable.
		$config_choices = array_keys( $this->field['choices'] );
		foreach ( $value as $key => $choice ) {
			// If the choice value is present in the configuration, continue.
			if ( in_array( $choice, $config_choices, true ) ) {
				continue;
			} else {
				// Value is falsified.
				// Get the default choice values.
				$default = isset( $this->field['data_args']['default'] ) ? $this->field['data_args']['default'] : array();
				// Get the database choices and fall back to the default configured value.
				$value = get_site_option( $option, $default );
				break;
			}
		}

		return $value;

	}

	/**
	* Get the settings option array and print one of its values.
	* @link https://developer.mozilla.org/en-US/docs/Web/HTML/Element/textarea
	*
	* @param array $args The arguments needed to render the setting field.
	*
	* @return void
	*/
	public function output( $args ) {

		// Assemble the variables necessary to output the form field from settings.
		$value       = get_site_option( $args['id'], $args['data_args']['default'] );
		$extra_attrs = $this->get_optional_attributes( $args );

		// Render the form field output.
		$output = array();
		foreach ( $args['choices'] as $choice_value => $choice_label ) {
			$checked = '';
			if ( $value && in_array( $choice_value, $value, true ) ) {
				$checked = 'checked ';
			} elseif ( is_string( $value ) && $value === $choice_value ) {
				$checked = 'checked ';
			}
			$output[] = sprintf(
				'<input type="checkbox" id="%1$s__%2$s" name="%1$s[]" value="%2$s" %3$s%4$s/> <label for="%1$s__%2$s" />%5$s</label>',
				esc_attr( $args['id'] ),
				$choice_value,
				$checked,
				$extra_attrs,
				$choice_label
			);

		}
		$output = implode( '<br />', $output );
		echo wp_kses( $output, $this->allowed_html );

		// Render the description text.
		if ( isset( $args['desc'] ) && $args['desc'] ) {
			$desc = '<br />' . $args['desc'];
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
	private function get_optional_attributes( $field ) {

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
			$extra_attrs['placeholder']  = 'placeholder="' . esc_attr( $field['placeholder'] ) . '"';
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