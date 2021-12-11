<?php
/**
 * The file that extends WP_Error notification capabilities.
 *
 * @package    Thoughtful_Web\Library_WP
 * @subpackage Admin\Page
 * @author     Zachary Kendall Watkins <zachwatkins@tapfuel.io>
 * @copyright  2021 Zachary Kendall Watkins
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0-or-later
 * @link       https://github.com/thoughtful-web/library-wp/blob/master/admin/class-settings.php
 * @since      0.1.0
 */

declare(strict_types=1);
namespace Thoughtful_Web\Library_WP\Admin\Page;

use \Thoughtful_Web\Library_WP\File\Auth_Include as TWL_File_Include;

/**
 * Undocumented class
 */
class Settings {

	/**
	 * Settings page and field Parameters.
	 *
	 * @var array $fieldset The Settings page and fieldset parameters.
	 */
	private $params = array(
	private $defaults = array(
		'method'      => 'add_menu_page',
		'method_args' => array(
			'page_title' => 'A Thoughtful Settings Page',
			'menu_title' => 'Thoughtful Settings',
			'capability' => 'manage_options',
			'menu_slug'  => 'thoughtful-settings',
			'function'   => null,
			'icon_url'   => 'dashicons-admin-settings',
			'position'   => 2,
		),
		'network'     => false,
		'description' => 'A thoughtful settings page description.',
		'heading'     => array(),
		'fieldsets'   => array(
			array(
				'section' => 'thoughtful-section-1',
				'title'   => '',
				'fields'  => array(
					array(
						'label'       => 'Text Field',
						'id'          => 'my_text_field',
						'type'        => 'text',
						'section'     => 'thoughtful-settings_section',
						'desc'        => 'Description',
						'placeholder' => 'placeholder',
						'label_for'   => null,
						'class'       => null,
					),
				),
			),
		),
	);

	/**
	 * Settings page and field Parameters.
	 *
	 * @var array $fieldset The Settings page and fieldset parameters.
	 */
	private $params = array();

	/**
	 * User capability requirement for accessing the settings page.
	 *
	 * @var string $capability The user capability string.
	 */
	private $capability = 'manage_options';

	/**
	 * Admin settings class constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param array|string $params The settings page parameters array or file path relative to the root directory.
	 */
	public function __construct( $params = array() ) {

		// Store attributes from the compiled parameters.
		$this->params     = new TWPL_Settings_Compile( $params, $this->defaults );
		$this->capability = $this->params['method_args']['capability'];

		// Initialize.
		$this->add_hooks();

	}

	/**
	 * Add action and filter hooks.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	private function add_hooks() {

		if ( isset( $this->params['network'] ) && $this->params['network'] ) {
			add_action( 'network_admin_menu', array( $this, 'add_settings' ) );
		} else {
			add_action( 'admin_menu', array( $this, 'add_settings' ) );
		}
		add_action( 'admin_init', array( $this, 'add_sections' ) );
		add_action( 'admin_init', array( $this, 'add_fields' ) );

		// add_action( 'admin_init', array( $this, 'settings_init' ) );
	}

	/**
	 * Add the settings page to the Admin navigation menu.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function add_settings() {

		add_menu_page(
			$this->params['method_args']['page_title'],
			$this->params['method_args']['menu_title'],
			$this->params['method_args']['capability'],
			$this->params['method_args']['menu_slug'],
			array( $this, 'add_settings_content' ),
			$this->params['method_args']['icon_url'],
			$this->params['method_args']['position']
		);

	}

	/**
	 * Add content to the Admin settings page.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function add_settings_content() {

		$method_args = $this->params['method_args'];
		if ( current_user_can( $method_args['capability'] ) ) {
			?>
			<div class="wrap">
				<h1><?php $method_args['page_title']; ?></h1>
				<?php settings_errors(); ?>
				<form method="POST" action="options.php">
					<?php
						foreach ( $this->params['fieldsets'] as $fieldset ) {
							settings_fields( $fieldset['section'] );
						}
						do_settings_sections( $method_args['menu_slug'] );
						submit_button();
					?>
				</form>
			</div>
			<?php
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

		$menu_slug = $this->params['method_args']['menu_slug'];
		foreach ( $this->params['fieldsets'] as $fieldset ) {
			add_settings_section(
				$fieldset['section'],
				$fieldset['title'],
				array( $this, 'add_section_description' ),
				$menu_slug
			);
		}

	}

	/**
	 * Add a section description.
	 *
	 * @since 0.1.0
	 *
	 * @param array $args {
	 *     The section arguments.
	 *
	 *     @key string $id       The section ID.
	 *     @key string $title    The section title.
	 *     @key string $callback This function's name.
	 * }
	 *
	 * @return void
	 */
	public function add_section_description( $args ) {

		if ( ! current_user_can( $this->capability ) ) {
			return;
		}

		$section_desc = $this->params['fieldsets'][ $id ]['description'];

		if ( empty( $section_desc ) ) {
			return;
		}

		$desc_html = "<p>$section_desc</p>";

		echo wp_kses_post( $desc_html );

	}

	/**
	 * Add each settings field to the page.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function add_fields() {

		$page      = $this->params['method_args']['menu_slug'];
		$callback  = array( $this, 'field_callback' );

		foreach( $this->params['fieldsets'] as $fieldset ){

			$section   = $fieldset['section'];
			$fields    = $fieldset['fields'];

			foreach ( $fields as $args ) {

				new TWL_Settings_Field( $args, $callback, $page, $section );

			}

		}

	}
}