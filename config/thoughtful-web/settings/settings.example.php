<?php

if ( ! defined( 'ABSPATH' ) ) {

	http_response_code( 404 );
	?><html><head><title>HTTP 404 Not Found</title></head><body><p>The requested page does not exist.</p></body></html>
	<?php
	die();

}

return array(
	'method_args'  => array(
		'page_title' => 'A Thoughtful Settings Page',
		'menu_title' => 'Thoughtful Settings',
		'capability' => 'manage_options',
		'menu_slug'  => 'thoughtful-settings',
		'icon_url'   => 'dashicons-admin-settings',
		'position'   => 2,
	),
	'description'  => 'A thoughtful settings page description.',
	'option_group' => 'thoughtful_settings',
	'stylesheet'   => array(
		'file' => 'settings.css',
		'deps' => array(),
	),
	'script'       => array(
		'file'      => 'settings.js',
		'deps'      => array(),
		'in_footer' => true,
	),
	'sections'     => array(
		array(
			'section'     => 'unique-section-id-1',
			'title'       => 'Section One',
			'description' => 'A description for Section One',
			'fields'      => array(
				array(
					'label'       => 'My Text Field',
					'id'          => 'unique_text_field',
					'type'        => 'text',
					'description' => 'My text field description',
					'placeholder' => 'my placeholder',
					'data_args'   => array(
						'default'       => 'A thoughtful, optional, default value',
						'data-lpignore' => 'true',
						'size'          => '40',
					),
				),
				array(
					'label'       => 'My Color Field',
					'id'          => 'unique_color_field',
					'type'        => 'color',
					'description' => 'My color field description',
					'data_args'   => array(
						'default' => '#000000',
					),
				),
				array(
					'label'       => 'My Textarea Field',
					'id'          => 'unique_textarea_field',
					'type'        => 'textarea',
					'description' => 'My textarea field',
					'placeholder' => 'my placeholder',
				),
				array(
					'label'       => 'My Checkbox Field',
					'id'          => 'unique_checkbox_field',
					'type'        => 'checkbox',
					'description' => 'My checkbox field description',
					'choice'      => array(
						'1' => 'My Choice',
					),
					'data_args'   => array(
						'default' => array(
							'1' => 'My Choice',
						),
					),
				),
				array(
					'label'       => 'My Checkbox Fields',
					'id'          => 'unique_checkbox_fields',
					'type'        => 'checkbox',
					'description' => 'My checkbox fields description',
					'choices'     => array(
						'option_one'   => 'Option 1',
						'option_two'   => 'Option 2',
						'option_three' => 'Option 3',
					),
					'data_args' => array(
						'default' => array(
							'option_one',
							'option_two',
						),
					),
				),
			),
		),
		array(
			'section'     => 'unique-section-id-2',
			'title'       => 'Section Two',
			'description' => 'Section Two description text',
			'fields'      => array(
				array(
					'label'       => 'My WP Editor Field',
					'id'          => 'unique_wp_editor_field',
					'type'        => 'wp_editor',
					'description' => 'My WP Editor field description',
					'data_args'   => array(
						'default' => 'my placeholder',
					),
				),
				array(
					'label'       => 'My Decimal Number Field',
					'id'          => 'unique_decimal_number_field',
					'type'        => 'number',
					'description' => 'My number field description',
					'placeholder' => 'Multiple of 0.1',
					'data_args'   => array(
						'step' => '0.1',
						'min'  => '0',
						'max'  => '10',
					),
				),
				array(
					'label'       => 'My Negative Number Field',
					'id'          => 'unique_negative_number_field',
					'type'        => 'number',
					'description' => 'My negative number field description',
					'placeholder' => 'Multiple of -1',
					'data_args'   => array(
						'step' => '1',
						'min'  => '-10',
						'max'  => '0',
					),
				),
				array(
					'label'       => 'My Radio Field',
					'id'          => 'unique_radio_field',
					'type'        => 'radio',
					'description' => 'My radio field description',
					'choices'     => array(
						'option_one'   => 'Option 1',
						'option_two'   => 'Option 2',
						'option_three' => 'Option 3',
					),
				),
				array(
					'label'       => 'My Select Field',
					'id'          => 'unique_select_field',
					'type'        => 'select',
					'description' => 'My select field description',
					'choices'     => array(
						'option_one'   => 'Option 1',
						'option_two'   => 'Option 2',
						'option_three' => 'Option 3',
					),
				),
				array(
					'label'       => 'My Multi-select Field',
					'id'          => 'unique_multiselect_field',
					'type'        => 'multiselect',
					'description' => 'My multi-select field description',
					'choices'     => array(
						'option_one'   => 'Option 1',
						'option_two'   => 'Option 2',
						'option_three' => 'Option 3',
					),
				),
				array(
					'label'       => 'My Email Field',
					'id'          => 'unique_email_field',
					'type'        => 'email',
					'description' => 'My email field description',
					'placeholder' => 'my placeholder',
				),
				array(
					'label'       => 'My Phone Field',
					'id'          => 'unique_phone_field',
					'type'        => 'tel',
					'description' => 'Example: 555-555-5555',
					'placeholder' => '555-555-5555',
					'data_args'   => array(
						'pattern' => '[0-9]{3}-[0-9]{3}-[0-9]{4}',
					),
				),
				array(
					'label'       => 'My URL Field',
					'id'          => 'unique_url_field',
					'type'        => 'url',
					'description' => 'Must have the "https" protocol. Example: https://example.com/',
					'placeholder' => 'https://example.com/',
					'data_args'   => array(
						'pattern' => 'https://.*',
					),
				),
			),
		),
	),
);