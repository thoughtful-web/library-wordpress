<?php
/**
 * The file that extends WP_Error notification capabilities.
 *
 * @package    ThoughtfulWeb\LibraryWP
 * @subpackage Settings
 * @author     Zachary Kendall Watkins <zachwatkins@tapfuel.io>
 * @copyright  2021 Zachary Kendall Watkins
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0-or-later
 * @link       https://github.com/thoughtful-web/library-wp/blob/master/Admin/Page/Settings.php
 * @since      0.1.0
 */

declare(strict_types=1);
namespace ThoughtfulWeb\LibraryWP\Admin\Page;

use \ThoughtfulWeb\LibraryWP\Admin\Page\Settings\Section;
use \ThoughtfulWeb\LibraryWP\Admin\Page\Settings\Config;
/**
 * The Admin Settings Page Class.
 *
 * @since 0.1.0
 */
class Settings {

	/**
	 * Settings page and field Parameters.
	 *
	 * @var array $config The Settings page and fieldset parameters.
	 */
	private $config = array();

	/**
	 * User capability requirement for accessing the settings page.
	 *
	 * @var string $capability The user capability string.
	 */
	private $capability = 'manage_options';

	/**
	 * Name the group of database options which the fields represent.
	 *
	 * @var string $option_group The database option group name. Lowercase letters and underscores only. If not configured it will default  to the menu_slug method argument with hyphens replaced with underscores.
	 */
	private $option_group = 'options';

	/**
	 * The menu page slug.
	 *
	 * @var string $menu_slug The settings page slug for a URL.
	 */
	private $menu_slug;

	/**
	 * Admin settings class constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param array|string $settings The settings page parameters array or file path relative to the root directory.
	 */
	public function __construct( $settings = array() ) {

		// Store attributes from the compiled parameters.
		$config_obj = new \ThoughtfulWeb\LibraryWP\Admin\Page\Settings\Config( $settings );

		// Assign compiled values.
		$this->config       = $config_obj->get();
		$this->capability   = $this->config['method_args']['capability'];
		$this->menu_slug    = $this->config['method_args']['menu_slug'];
		$this->option_group = $this->config['option_group'];

		// Initialize.
		if ( ! isset( $this->config['network'] ) || ! $this->config['network'] ) {
			add_action( 'admin_menu', array( $this, 'add_settings' ) );
		} else {
			add_action( 'network_admin_menu', array( $this, 'add_settings' ) );
			add_action( 'network_admin_edit_' . $this->menu_slug, array( $this, 'save_network_option' ) );
		}
		add_action( 'admin_init', array( $this, 'settings_init' ) );

	}

	/**
	 * Register settings, add sections, and add fields to those sections.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function settings_init() {

		$this->register_settings();
		$this->add_sections();
		$this->add_fields();

	}

	/**
	 * Register all new database settings.
	 *
	 * @return void
	 */
	private function register_settings() {

		foreach ( $this->config['sections'] as $section ) {

			foreach ( $section['fields'] as $field ) {

				$data_args = isset( $field['data_args'] ) ? $field['data_args'] : array();
				// Register the settings field database entry.
				register_setting( $this->option_group, $field['id'], $data_args );

			}

		}

	}

	/**
	 * Add settings sections.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function add_sections() {

		foreach ( $this->config['sections'] as $id => $section ) {
			new \ThoughtfulWeb\LibraryWP\Admin\Page\Settings\Section( $id, $section['title'], $section['description'], $this->menu_slug, $this->capability );
		}

	}

	/**
	 * Add each settings field to the page.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function add_fields() {

		$network = $this->config['network'];

		foreach ( $this->config['sections'] as $section ) {

			$section_id = $section['section'];
			$fields     = $section['fields'];

			foreach ( $fields as $field ) {

				switch( $field['type'] ) {
					case 'text':
						new \ThoughtfulWeb\LibraryWP\Admin\Page\Settings\TextField( $field, $this->menu_slug, $section_id, $this->option_group, $network );
						break;
					default:
						break;
					}

			}

		}

	}

	/**
	 * Add the settings page to the Admin navigation menu.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function add_settings() {

		$options_form = $this->config['network'] ? 'network_options_form' : 'site_options_form';

		add_menu_page(
			$this->config['method_args']['page_title'],
			$this->config['method_args']['menu_title'],
			$this->config['method_args']['capability'],
			$this->config['method_args']['menu_slug'],
			array( $this, $options_form ),
			$this->config['method_args']['icon_url'],
			$this->config['method_args']['position']
		);

	}

	/**
	 * Add content to the Admin settings page.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function site_options_form() {

		if ( ! current_user_can( $this->capability ) ) {
			return;
		}

		?>
		<div class="wrap">
			<h1><?php $this->config['method_args']['page_title']; ?></h1>
			<?php settings_errors(); ?>
			<form method="POST" action="options.php">
				<?php
					settings_fields( $this->option_group );
					do_settings_sections( $this->menu_slug );
					submit_button( 'Save Settings' );
				?>
			</form>
		</div>
		<?php

	}

	/**
	 * Add content to the Admin settings page.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function network_options_form() {

		if ( ! current_user_can( $this->capability ) ) {
			return;
		}

		?>
		<div class="wrap">
			<h1><?php $this->config['method_args']['page_title']; ?></h1>
			<?php settings_errors(); ?>
			<form method="POST" action="edit.php?action=<?php echo $this->menu_slug ?>">
				<?php
					settings_fields( $this->option_group );
					do_settings_sections( $this->menu_slug );
					submit_button( 'Save Settings' );
				?>
			</form>
		</div>
		<?php

	}

	public function save_network_option() {

		// Verify nonce.
		wp_verify_nonce( $_POST['_wpnonce'], 'update' );

		// Save the option.
		// Todo: filtering?
		$option = $_POST[ $this->config['option_group'] ];
		update_site_option( $this->config['option_group'], $option );

		// Get the site or network admin URL.
		$admin_url = admin_url( 'admin.php' );
		if ( is_multisite() && is_super_admin() && $this->config['network'] ) {
			$admin_url = network_admin_url( 'admin.php' );
		}
		// Redirect to settings page.
		wp_safe_redirect(
			add_query_arg(
				array(
					'page'    => $this->config['method_args']['menu_slug'],
					'updated' => 'true',
				),
				$admin_url
			)
		);
		exit;

	}
}
