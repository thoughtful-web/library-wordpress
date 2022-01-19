<?php
/**
 * The file that extends WP_Error notification capabilities.
 *
 * @package    ThoughtfulWeb\LibraryWP
 * @subpackage Settings
 * @author     Zachary Kendall Watkins <watkinza@gmail.com>
 * @copyright  Zachary Kendall Watkins 2022
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0-or-later
 * @link       https://github.com/thoughtful-web/settings-page-wp/blob/main/src/Admin/Page/Settings.php
 * @since      0.1.0
 */

declare(strict_types=1);
namespace ThoughtfulWeb\LibraryWP\Admin\Page;

use \ThoughtfulWeb\LibraryWP\Admin\Page\Settings\Config;
use \ThoughtfulWeb\LibraryWP\Admin\Page\Settings\Section;
use \ThoughtfulWeb\LibraryWP\Admin\Page\Settings\TextField;
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
	 * @param array|string $config (Optional) The settings page configuration parameters.
	 *                                        Either a configuration file name, file path, or array.
	 */
	public function __construct( $config = array() ) {

		// Store attributes from the compiled parameters.
		$config_obj = new \ThoughtfulWeb\LibraryWP\Admin\Page\Settings\Config( $config );

		// Assign compiled values.
		$this->config       = $config_obj->get();
		$this->capability   = $this->config['method_args']['capability'];
		$this->menu_slug    = $this->config['method_args']['menu_slug'];
		$this->option_group = $this->config['option_group'];

		// Initialize.
		if ( ! isset( $this->config['network'] ) || ! $this->config['network'] ) {
			add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		} else {
			// add_action( 'network_admin_menu', array( $this, 'add_settings' ) );
			// add_action( 'network_admin_edit_' . $this->menu_slug, array( $this, 'save_network_option' ) );
		}
		add_action( 'admin_init', array( $this, 'settings_init' ) );

		// Register the stylesheet if present.
		if ( $this->has_stylesheet() ) {
			add_action( 'admin_init', array( $this, 'register_stylesheet' ) );
		}

		// Register the script if present.
		if ( $this->has_script() ) {
			add_action( 'admin_init', array( $this, 'register_script' ) );
		}

	}

	/**
	 * Detect if the config has valid stylesheet parameters.
	 *
	 * @return boolean
	 */
	private function has_stylesheet() {

		$has_stylesheet = false;

		if (
			array_key_exists( 'stylesheet', $this->config )
			&& ! empty( $this->config['stylesheet'] )
			&& array_key_exists( 'file', $this->config['stylesheet'] )
		) {
			$has_stylesheet = true;
		}

		return $has_stylesheet;

	}

	/**
	 * Detect if the config has valid script parameters.
	 *
	 * @return boolean
	 */
	private function has_script() {

		$has_script = false;

		if (
			array_key_exists( 'script', $this->config )
			&& ! empty( $this->config['script'] )
			&& array_key_exists( 'file', $this->config['script'] )
		) {
			$has_script = true;
		}

		return $has_script;

	}

	/**
	 * Enqueue the Settings page's stylesheet file.
	 *
	 * @return void
	 */
	public function register_stylesheet() {

		$slug        = $this->config['method_args']['menu_slug'];
		$plugin_root = dirname( __FILE__, 7 );
		$config_path = '/config/thoughtful-web/settings/';
		$deps        = array_key_exists( 'deps', $this->config['stylesheet'] ) ? $this->config['stylesheet']['deps'] : array();
		$file_url    = plugins_url( basename( $plugin_root ) . $config_path . $this->config['stylesheet']['file'] );
		$file_path   = $plugin_root . $config_path . $this->config['stylesheet']['file'];
		$version     = filemtime( $file_path );
		// Register the stylesheet.
		wp_register_style( 'settings-' . $slug, $file_url, $deps, $version );

	}

	/**
	 * Enqueue the Settings page stylesheet.
	 *
	 * @param string $hook_suffix The current admin page.
	 *
	 * @return void
	 */
	public function enqueue_stylesheet( $hook_suffix ) {

		if ( false !== strpos( $hook_suffix, 'page-' . $this->config['method_args']['menu_slug'] ) ) {
			wp_enqueue_style( 'settings-' . $this->config['method_args']['menu_slug'] );
		}

	}

	/**
	 * Enqueue the Settings page's script file.
	 *
	 * @return void
	 */
	public function register_script() {

		$slug        = $this->config['method_args']['menu_slug'];
		$plugin_root = dirname( __FILE__, 7 );
		$config_path = '/config/thoughtful-web/settings/';
		$deps        = array_key_exists( 'deps', $this->config['script'] ) ? $this->config['script']['deps'] : array();
		$file_url    = plugins_url( basename( $plugin_root ) . $config_path . $this->config['script']['file'] );
		$file_path   = $plugin_root . $config_path . $this->config['script']['file'];
		$version     = filemtime( $file_path );
		$in_footer   = array_key_exists( 'position', $this->config['script'] ) ? boolval( $this->config['script'] ) : false;
		// Register the stylesheet.
		wp_register_script( 'settings-' . $slug, $file_url, $deps, $version, $in_footer );

	}

	/**
	 * Enqueue the Settings page script.
	 *
	 * @param string $hook_suffix The current admin page.
	 *
	 * @return void
	 */
	public function enqueue_script( $hook_suffix ) {

		if ( false !== strpos( $hook_suffix, 'page-' . $this->config['method_args']['menu_slug'] ) ) {
			wp_enqueue_style( 'settings-' . $this->config['method_args']['menu_slug'] );
		}

	}

	/**
	 * Register settings, add sections, and add fields to those sections.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function settings_init() {

		$this->add_sections();
		$this->add_fields();

	}

	/**
	 * Add settings sections.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	private function add_sections() {

		foreach ( $this->config['sections'] as $id => $section ) {
			new \ThoughtfulWeb\LibraryWP\Admin\Page\Settings\Section(
				$id,
				$section['title'],
				$section['description'],
				$this->menu_slug,
				$this->capability,
				$section,
			);
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

		foreach ( $this->config['sections'] as $section ) {

			// Skip this section if it is missing fields.
			if ( ! array_key_exists( 'fields', $section ) ) {
				continue;
			}

			$section_id = $section['section'];
			$fields     = $section['fields'];

			foreach ( $fields as $field ) {

				switch( $field['type'] ) {
					case 'text':
						new \ThoughtfulWeb\LibraryWP\Admin\Page\Settings\Field\Text(
							$field,
							$this->menu_slug,
							$section_id,
							$this->option_group
						);
						break;
					case 'textarea':
						new \ThoughtfulWeb\LibraryWP\Admin\Page\Settings\Field\Textarea(
							$field,
							$this->menu_slug,
							$section_id,
							$this->option_group
						);
						break;
					case 'number':
						new \ThoughtfulWeb\LibraryWP\Admin\Page\Settings\Field\Number(
							$field,
							$this->menu_slug,
							$section_id,
							$this->option_group
						);
						break;
					case 'checkbox':
						if ( array_key_exists( 'choice', $field ) ) {
							new \ThoughtfulWeb\LibraryWP\Admin\Page\Settings\Field\Checkbox(
								$field,
								$this->menu_slug,
								$section_id,
								$this->option_group
							);
						} elseif ( array_key_exists( 'choices', $field ) ) {
							new \ThoughtfulWeb\LibraryWP\Admin\Page\Settings\Field\Checkboxes(
								$field,
								$this->menu_slug,
								$section_id,
								$this->option_group
							);
						}
						break;
					case 'wp_editor':
						new \ThoughtfulWeb\LibraryWP\Admin\Page\Settings\Field\WP_Editor(
							$field,
							$this->menu_slug,
							$section_id,
							$this->option_group
						);
						break;
					case 'color':
						new \ThoughtfulWeb\LibraryWP\Admin\Page\Settings\Field\Color(
							$field,
							$this->menu_slug,
							$section_id,
							$this->option_group
						);
						break;
					case 'email':
						new \ThoughtfulWeb\LibraryWP\Admin\Page\Settings\Field\Email(
							$field,
							$this->menu_slug,
							$section_id,
							$this->option_group
						);
						break;
					case 'select':
						new \ThoughtfulWeb\LibraryWP\Admin\Page\Settings\Field\Select(
							$field,
							$this->menu_slug,
							$section_id,
							$this->option_group
						);
						break;
					case 'tel':
						new \ThoughtfulWeb\LibraryWP\Admin\Page\Settings\Field\Phone(
							$field,
							$this->menu_slug,
							$section_id,
							$this->option_group
						);
						break;
					case 'url':
						new \ThoughtfulWeb\LibraryWP\Admin\Page\Settings\Field\Url(
							$field,
							$this->menu_slug,
							$section_id,
							$this->option_group
						);
						break;
					case 'password':
						new \ThoughtfulWeb\LibraryWP\Admin\Page\Settings\Field\Password(
							$field,
							$this->menu_slug,
							$section_id,
							$this->option_group
						);
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
	public function add_settings_page() {

		// $options_form = $this->config['network'] ? 'network_options_form' : 'site_options_form';
		$options_form = 'site_options_form';

		if (
			! isset( $this->config['method_args']['parent_slug'] )
		) {
			$page = add_menu_page(
				$this->config['method_args']['page_title'],
				$this->config['method_args']['menu_title'],
				$this->config['method_args']['capability'],
				$this->config['method_args']['menu_slug'],
				array( $this, $options_form ),
				$this->config['method_args']['icon_url'],
				$this->config['method_args']['position']
			);
		} else {
			$page = add_submenu_page(
				$this->config['method_args']['parent_slug'],
				$this->config['method_args']['page_title'],
				$this->config['method_args']['menu_title'],
				$this->config['method_args']['capability'],
				$this->config['method_args']['menu_slug'],
				array( $this, $options_form ),
				$this->config['method_args']['position']
			);
		}


		// Enqueue the stylesheet, if present.
		if ( $this->has_stylesheet() ) {
			add_action( "admin_enqueue_scripts", array( $this, 'enqueue_stylesheet' ) );
		}
		// Enqueue the stylesheet, if present.
		if ( $this->has_script() ) {
			add_action( "admin_enqueue_scripts", array( $this, 'enqueue_script' ) );
		}

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
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<?php settings_errors(); ?>
			<form action="options.php" method="post">
				<?php
					// Output security fields for the registered setting.
					settings_fields( $this->option_group );
					// Output setting sections and their fields.
					// (Sections are registered for "$this->menu_slug", each field is registered to a specific section).
					do_settings_sections( $this->menu_slug );
					// Output save settings button.
					submit_button( 'Save Settings' );
				?>
			</form>
			<?php
			if ( wp_script_is( 'wp-color-picker', 'queue' ) ) {
				?>
				<script type="text/javascript">

				</script>
				<?php
			}
			?>
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
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<?php settings_errors(); ?>
			<form method="POST" action="edit.php?action=<?php echo $this->menu_slug ?>">
				<?php
					settings_fields( $this->option_group );
					do_settings_sections( $this->menu_slug );
					submit_button( 'Save Settings' );
				?>
			</form>
			<?php
			if ( wp_script_is( 'wp-color-picker', 'queue' ) ) {
				?>
				<script type="text/javascript">
					jQuery(document).ready(
						function($){
							$('input[data-wp-color-picker]').wpColorPicker();
						}
					);
				</script>
				<?php
			}
			?>
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
