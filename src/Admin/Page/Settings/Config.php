<?php
/**
 * The file that extends WP_Error notification capabilities.
 *
 * @package    Thoughtful_Web\Library_WP
 * @subpackage Settings
 * @author     Zachary Kendall Watkins <zachwatkins@tapfuel.io>
 * @copyright  2021 Zachary Kendall Watkins
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0-or-later
 * @link       https://github.com/thoughtful-web/library-wp/blob/main/Admin/Page/Settings/Config.php
 * @since      0.1.0
 */

declare(strict_types=1);
namespace Thoughtful_Web\Library_WP\Admin\Page\Settings;

/**
 * The Settings Page Parameter Compiler Class.
 *
 * @since 0.1.0
 */
class Config {

	/**
	 * Default parameters.
	 *
	 * @var array defaults The default values for the settings page registration parameters.
	 */
	private $defaults = array(
		'method'       => 'add_menu_page',
		'method_args'  => array(
			'page_title' => 'A Thoughtful Settings Page',
			'menu_title' => 'Thoughtful Settings',
			'capability' => 'manage_options',
			'menu_slug'  => 'thoughtful-settings',
			'function'   => null,
			'icon_url'   => 'dashicons-admin-settings',
			'position'   => 2,
		),
		'description'  => 'A thoughtful settings page description.',
		'option_group' => 'options',
		'network'      => false,
	);

	/**
	 * The configuration associative array.
	 *
	 * @var array $config The associative array storing the final configuration state.
	 */
	private $config;

	/**
	 * Constructor for the Compile class.
	 *
	 * @param mixed $config The Settings page configuration parameters. Either a configuration file or an array.
	 *
	 * @return array
	 */
	public function construct( $config ) {

		// Maybe retrieve in file format.
		if ( is_string( $config ) ) {
			$config_path = $this->validate_file_path( $config );
			if ( $config_path ) {
				$config = include $config_path;
			}
		}

		$this->preprocess( $config );

	}

	/**
	 * Get the compiler results.
	 *
	 * @param array $config   The Settings page configuration parameters.
	 *
	 * @return array
	 */
	private function preprocess( $config ) {

		// Apply default values to the parameters.
		$config = $this->merge_defaults( $config );

		// Configure sections.
		$config = $this->associate_sections( $config );

		$this->config = $config;

	}

	/**
	 * Return the configuration array.
	 *
	 * @return array
	 */
	public function get() {

		return $this->config;

	}

	/**
	 * Merge the default parameters with the user defined parameters.
	 * Only 2 levels deep.
	 *
	 * @param array $params User defined parameters.
	 *
	 * @return array
	 */
	private function merge_defaults( $params ) {

		foreach ( $this->defaults as $key => $default ) {
			if ( is_array( $default ) ) {
				if ( array_key_exists( $key, $params ) ) {
					foreach ( $default as $key2 => $default2 ) {
						if ( ! array_key_exists( $key2, $params[ $key ] ) ) {
							$params[ $key ][ $key2 ] = $default2;
						}
					}
				} else {
					$params[ $key ] = $default;
				}
			} else {
				if ( ! array_key_exists( $key, $params ) ) {
					$params[ $key ] = $default;
				}
			}
		}

		return $params;

	}

	/**
	 * Configure each section to be associated with a section ID key.
	 *
	 * @since 0.1.0
	 *
	 * @param array $config The settings configuration.
	 *
	 * @return array
	 */
	private function associate_sections( $config ) {

		foreach ( $config['sections'] as $key => $section ) {

			// Get this section's ID.
			$section_id = $section['section'];

			// Create an associative key assignment for the section.
			// This is done for succinct code elsewhere.
			$config['sections'][ $section_id ] = $section;

			// Remove the old numeric-indexed key assignment.
			unset( $config['sections'][ $key ] );

		}

		return $config;

	}
}