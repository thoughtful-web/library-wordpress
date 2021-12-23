<?php
/**
 * The file that provides sanitization functions to other classes.
 *
 * @package    ThoughtfulWeb\LibraryWP
 * @subpackage Field
 * @author     Zachary Kendall Watkins <zachwatkins@tapfuel.io>
 * @copyright  2021 Zachary Kendall Watkins
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0-or-later
 * @link       https://github.com/thoughtful-web/library-wp/blob/master/admin/page/settings/sanitize.php
 * @since      0.1.0
 */

declare(strict_types=1);
namespace ThoughtfulWeb\LibraryWP\Admin\Page\Settings;

class Sanitize {


	/**
	 * Sanitizes a boolean option value.
	 *
	 * @param string $values         The unsanitized option value.
	 * @param string $option         The option name.
	 * @param string $original_value The original value passed to the function.
	 *
	 * @return string
	 */
	public static function sanitize_booleanish( $values, $option, $original_value ) {

		// Truthy values in a format we interpret later.
		// Setting array keys to non-string values can have unintended effects.
		$results  = array( false );
		$bool_map = array(
			'^true' => false,
			'true'  => 'false',
			'TRUE'  => 'FALSE',
			'yes'   => 'no',
			'YES'   => 'NO',
			'^1'    => 0,
			'1'     => '0'
		);

		// Assume values might be an array, sometimes.
		// If the value was not an array remember to restore it to a non-array value at the end.
		$was_array = true;
		if ( ! is_array( $values ) ) {
			$was_array = false;
			$values = array( $values );
		}

		// Check each value for presence in the truthy array.
		foreach ( $values as $value ) {
			// Convert the value to a string but remember if it was not a string.
			$ovalue = $value;
			$value  = is_string( $value ) ? $value : '^' . strval( $value );
			if ( array_key_exists( $value, $bool_map ) ) {
				$results[] = $ovalue;
			} else {
				$results[] = 'no';
			}
		}

		// Restore non-array state if necessary.
		if ( ! $was_array ) {
			$results = $results[0];
		}

		return $results;

	}

	/**
	 * Sanitize title array.
	 *
	 * @since 0.1.0
	 *
	 * @param string $value          The unsanitized option value.
	 * @param string $option         The option name.
	 * @param string $original_value The original value passed to the function.
	 *
	 * @return string
	 */
	public static function sanitize_choices( $value ) {

		// The valid choices.
		$choices = $this->params['field']['choices'];
		$value   = sanitize_title( $value );
		if ( ! array_key_exists( $value, $choices ) ) {
			return '';
		}

		return $value;

	}

	/**
	 * Sanitize and validate media upload's file name.
	 *
	 * @since 0.1.0
	 *
	 * @param string $value The media file's URL.
	 *
	 * @return void
	 */
	public static function sanitize_file_name( $value ) {

		$value = sanitize_file_name( $value );
		if ( ! $value ) {
			return;
		}

		$parsed = parse_url( $value );
		$valid  = parse_url( get_admin_url() );
		if (
			$parsed['scheme'] === $valid['scheme']
			&& $parsed['host'] === $valid['host']
		) {
			return $value;
		}
	}
}
