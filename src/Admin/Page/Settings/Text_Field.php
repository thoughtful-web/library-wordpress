<?php
/**
 * The file that extends WP_Error notification capabilities.
 *
 * @package    Thoughtful_Web\Library_WP
 * @subpackage Field
 * @author     Zachary Kendall Watkins <zachwatkins@tapfuel.io>
 * @copyright  2021 Zachary Kendall Watkins
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0-or-later
 * @link       https://github.com/thoughtful-web/library-wp/blob/master/admin/page/settings/field/text_input.php
 * @since      0.1.0
 */

declare(strict_types=1);
namespace Thoughtful_Web\Library_WP\Admin\Page\Settings;

use \Thoughtful_Web\Library_WP\Admin\Page\Settings\Sanitize as TWPL_Sanitize;

class Text_Field {

	/**
	 * The default values for required $field members.
	 *
	 * @var array $default The default field parameter member values.
	 */
	private $default_field = array(
		'desc'        => '',
		'placeholder' => '',
		'data_args'   => array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => false,
			'type'              => 'string',
			'description'       => '',
		)
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
	 *         @type mixed      $default           Default value when calling `get_option()`. Optional.
	 *         @type callable   $sanitize_callback A callback function that sanitizes the option's value. Optional.
	 *         @type bool|array $show_in_rest      Whether data associated with this setting should be included in the REST API. When registering complex settings, this argument may optionally be an array with a 'schema' key.
	 *         @type string     $type              The type of data associated with this setting. Only used for the REST API. Valid values are 'string', 'boolean', 'integer', 'number', 'array', and 'object'.
	 *         @type string     $description       A description of the data attached to this setting. Only used for the REST API.
	 *     }
	 * }
	 * @param string   $page     The slug-name of the settings page on which to show the section (general, reading, writing, ...).
	 * @param string   $section  The slug-name of the section of the settings page in which to show the box.
	 */
	public function __construct( $field, $page, $section ) {

		// Apply default values for field registration parameters.
		$field       = array_merge_recursive( $this->default_field, $field );
		$this->field = $field;

		// Determine the database settings field.
		// Known blacklist of database option names.
		$option_group = str_replace( '-', '_', sanitize_key( $page ) );
		if ( in_array( $option_group, array( 'privacy', 'misc' ), true ) ) {
			$option_group .= '_option';
		}

		// Register the settings field output.
		add_settings_field( $field['id'], $field['label'], array( $this, 'output' ), $page, $section, $field );

		// Register the settings field database entry.
		register_setting( $option_group, $field['id'], $field['data_args'] );

	}

	/**
	 * Sanitizes a text field string.
	 *
	 * @param string $value          The unsanitized option value.
	 * @param string $option         The option name.
	 * @param string $original_value The original value passed to the function.
	 *
	 * @return string
	 */
	public function sanitize( $value, $option, $original_value ) {

		$value = sanitize_text_field( $value );
		if ( ! $value || $value !== $original_value ) {
			$value = get_site_option( $option );
			if ( ! $value ) {
				$value = $this->field['data_args']['default'];
			}
		}

		return $value;

	}

	/**
	* Get the settings option array and print one of its values.
	*
	* @param array $args The arguments needed to render the setting field.
	*
	* @return void
	*/
	public function text_field( $args ) {

		$option_name   = $args['option_name'];
		$field_name    = $args['field_name'];
		$default_value = $this->field['data_args']['default'];
		$placeholder   = isset( $args['placeholder'] ) ? $args['placeholder'] : '';
		$option        = get_site_option( $option_name );
		$value         = isset( $option[ $field_name ] ) ? $option[ $field_name ] : $default_value;
		$output        = sprintf(
			'<input type="text" name="%1$s[%2$s]" id="%3$s[%4$s]" class="settings-text" data-lpignore="true" size="40" placeholder="%6$s" value="%5$s" />',
			$option_name,
			$field_name,
			$option_name,
			$field_name,
			$value,
			$placeholder
		);
		echo $output;
		if ( isset( $args['after'] ) ) {
			echo wp_kses_post( $args['after'] );
		}

	}
}