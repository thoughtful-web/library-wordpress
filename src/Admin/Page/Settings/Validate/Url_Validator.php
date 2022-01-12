<?php
/**
 * The file that creates a Settings page section.
 *
 * @package    ThoughtfulWeb\LibraryWP
 * @subpackage Settings
 * @author     Zachary Kendall Watkins <zachwatkins@tapfuel.io>
 * @copyright  2022 Zachary Kendall Watkins
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0-or-later
 * @link       https://github.com/thoughtful-web/library-wp/blob/master/admin/page/settings/validate.php
 * @since      0.1.0
 */

declare(strict_types=1);
namespace ThoughtfulWeb\LibraryWP\Admin\Page\Settings;

use \ThoughtfulWeb\LibraryWP\Admin\Page\Settings\Validate\Text_Validator;

class Url_Validator extends Text_Validator {

	/**
	 * Validate the input value and return any error messages.
	 *
	 * @param string $input The input value.
	 * @return void
	 */
	public function validate( $input ) {

		$valid = array(
			'status'   => true,
			'messages' => array(),
		);

		// If the input is empty but required.
		$is_empty = $this->is_empty( $input );
		if ( ! empty( $this->settings['data_args']['required'] ) ) {
			if ( true === $is_empty['status'] ) {
				$valid['status']    = false;
				$valid['message']['is_empty'] = 'The value cannot be empty.';
			}
		}

		// If the input must follow a pattern.
		if ( ! empty( $this->settings['data_args']['pattern'] ) ) {
			$is_pattern = $this->is_pattern( $input );
			if ( false === $is_pattern['status'] ) {
				$valid['status']    = false;
				$valid['message']['not_pattern'] = 'The value must follow the pattern "' . $this->settings['data_args']['pattern'] . '"';
			}
		}

		// If the input must follow length requirements.
		$is_length = $this->is_length( $input );
		if ( false === $is_length['status'] ) {
			$valid['status']               = false;
			$valid['message']['not_length'] = $is_length['message'];
		}

		return $valid;

	}

}