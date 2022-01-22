# Thoughtful Web Settings Pages for WordPress

>Free open source software under the GNU GPL-2.0+ License.  
>Copyright Zachary Kendall Watkins 2022.  

This library generates both a Settings page and fully qualified Options for each of its fields from a single configuration file.

All HTML attributes for form fields are supported in the configuration and "pattern" attributes are validated for both the form and in the Option's sanitization filter hook. Each Field is a separate Option and all WordPress filters and actions which apply to Options can be used for them.

## Features

1. Settings page generation from a configuration file.
2. Wrapped around the Core WordPress Settings and Options APIs.
3. Each Field creates and updates an individual database Option, which has advantages when you use hooks and filters.
4. Each Field is validated in a manner similar to Core WordPress options and failed server-side validation emits a Settings Page error notice.
5. If a Field type supports it you can add the "pattern" attribute to further validate against a regular expression in both the page and the server. This works on the Settings page and also when a script calls `update_option( $option, $value )`.
6. Configure stylesheet and/or script file assets.
7. Configure default Field values to automatically load them into the database. If the field is ever emptied these values will load instead.
8. Zero production dependencies beyond PHP, WordPress, and WordPress included JavaScript Iris for the color picker field.
9. Configure and create multiple pages or subpages.

## Roadmap

Features, changes, and fixes which I plan on implementing:

1. Fix default value for Select fields to be a singular value unless it is a multiselect field.
2. Continue developing the documentation.
3. Create a form to generate a configuration file with.
4. Add a network admin settings page configuration.
5. Add a filter to the Select Field configuration to enable populating choices with data like:  
   a) Users  
   b) User Roles  
   c) Post Types  
   d) Taxonomies  
   e) Image Sizes  
6. Allow disabling or removing "choices" from Fields with this value in case we need to scope access to updating these values. Perhaps this should be a filter for the configuration preprocessor.
7. Consider having the Color field use Iris if the current browser doesn't support the color input type. This will not be implemented if default browser color inputs lack essential features that Iris provides.

## Requirements

1. WordPress 5.4 and above.
2. PHP 7.3.5 and above.

## Development Requirements

1. PHP Codesniffer composer module
2. WP Coding Standards composer module

## Installation

To install this module from Composer directly, use the command line. Then either use Composer's autoloader or require the class files directly in your PHP.

`$ composer require thoughtful-web/settings-page-wp`

To install this module from Github using Composer, add it as a repository to the composer.json file:

```
{
    "name": "thoughtful-web/settings-page-wp",
    "description": "WordPress Settings page generator and validator released as free open source software under GNU GPL-2.0+ License",
	"repositories": [
		{
			"type": "vcs",
			"url": "https://github.com/thoughtful-web/settings-page-wp"
		}
	],
	"require": {
		"thoughtful-web/settings-page-wp": "dev-main"
	}
}
```

## Simplest Implementation

The simplest implementation of this module is to include it with the autoloader and add a configuration file at `./config/thoughtful-web/settings/settings.php` or `./config/thoughtful-web/settings/settings.json`. Then declare the Settings from that configuration file by creating a new instance of the Settings page in your Plugin's main file like this:  

```
require __DIR__ . '/vendor/autoload.php;
new \ThoughtfulWeb\SettingsPageWP\Settings();
```

## Implementing The Class

To load the Settings class with (or without) a configuration parameter you should know the accepted values:

```
@param string|array $config (Optional) The Settings page configuration parameters.
                            Either a configuration file name, file path, or array.
```

This class will load a file using an `include` statement if it is a PHP file or using `file_read_contents` it is a JSON file. Here is an explanation of the possible values for this parameter:

1. **No parameter** assumes there is a configuration file located here: `./config/thoughtful-web/settings/settings.php`. Example:  
   a. `new \ThoughtfulWeb\SettingsPageWP\Settings();`  

2. **File name** accepts a PHP or JSON file name and requires the file to be in the directory `./config/thoughtful-web/settings/{file}`. Examples:  
   a. `new \ThoughtfulWeb\SettingsPageWP\Settings( 'filename.php' );`  
   b. `new \ThoughtfulWeb\SettingsPageWP\Settings( 'filename.json' );`  

3. **File path** can be any location on your server, as long as the `./src/Settings/Config.php` class file has read access to it. Examples:  
   a. `new \ThoughtfulWeb\SettingsPageWP\Settings( __DIR__ . '/config/settings.json' );`  
   b. `new \ThoughtfulWeb\SettingsPageWP\Settings( '/home/website/settings.php' );`  

4. **Array** The configuration values in their final state.

**Note:** Call the class without an action hook or within an action hook early enough in the execution order to not skip the WordPress actions, filters, and functions used in this feature's class files. It is yet to be determined which action hooks are compatible with this class's instantiation.

## Creating the Config File

Documentation for this framework is a work in progress. Some documentation for creating a configuration file can be found below. You should check out the example configuration file(s) at `./config/thoughtful-web/settings/settings.example.php`. Consider checking out the class variables of each **Field** class file to see which HTML attributes they support - these must be configured in a Field's `data_args` array member.


```
array(
	'method_args'  => array(
		'page_title'  => __( 'My Plugin Settings', 'thoughtful-web' ),
		'menu_title'  => __( 'My Settings', 'thoughtful-web' ),
		'capability'  => 'manage_options',
		'menu_slug'   => 'my-plugin-settings',
		'icon_url'    => 'dashicons-admin-settings',
		'position'    => 1,
	),
	'option_group' => 'my_plugin_settings',
	'description'  => 'Settings for my awesome plugin.',
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
			'section' => 'unique_section_id',
			'title'   => __( 'My Section', 'thoughtful-web' ),
			'fields'  => array(
				array(
					'label' => 'My text field',
					'id'    => 'unique_text_field',
					'type'  => 'text',
				),
			),
		),
		array(
			'section'     => 'unique_section_two_id',
			'title'       => __( 'Included File', 'thoughtful-web' ),
			'description' => __( 'Displaying a helpful file.', 'thoughtful-web' ),
			'include'     => __DIR__ . '/views/file.php',
		),
	),
);
```

The topmost configuration array accepts six parameters: method_args, description, option_group, stylesheet, script, and sections.

### method_args

The "method_args" key is an array and applies its values to the add_menu_page function, or the add_submenu_page function if instead of an "icon_url" parameter you provide a "parent_slug" parameter.

Documentation:
1. https://developer.wordpress.org/reference/functions/add_menu_page/
2. https://developer.wordpress.org/reference/functions/add_submenu_page/

### description

The "description" key is a text description of the menu page and appears just below the title.

### option_group

The "option_group" key is the slug name of the option group which settings are registered to.

### stylesheet

The "stylesheet" key allows you to register and enqueue your stylesheet file for the Settings page.

### script

The "script" key allows you to register and enqueue your javascript file for the Settings page.

### sections

The "sections" key accepts an array of Section configurations, each with either an "include" or "fields" key to determine their main content.

## Sections

A Section requires "section" and "title" values and either a "fields" or "include" value. Example:

You may include a file by path reference in the Section configuration using the "include" value, which accepts an absolute file path string. Example:

```
array(
	'section'     => 'section_error_logs',
	'title'       => __( 'Error Logs', 'thoughtful-web' ),
	'description' => __( 'Displaying error logs.', 'thoughtful-web' ),
	'include'     => __DIR__ . '/views/file.php',
),
```

## Fields

Here is the most basic field configuration:

```
array(
	'label' => 'My Text Field',
	'id'    => 'unique_text_field_option',
	'type'  => 'text',
)
```

Here is an example field configuration using optional values:

```
array(
	'label'       => 'My Text Field',
	'id'          => 'unique_text_field_option',
	'type'        => 'text',
	'description' => 'My text field description',
	'data_args'   => array( // Meaning you don't have to declare the data_args at all.
		'placeholder'   => 'my placeholder',
		'default'       => 'A default value',
		'data-lpignore' => 'true', // Accepts any data attribute. LastPass ignores fields with this data attribute.
		'size'          => '40', // HTML "size" attribute.
	),
),
```

The following Field types are supported. Notes on each Field type's configuration and behavior follow. Refer to their class files to see supported HTML attributes which, if declared, must be in the "data_args" value of the field's configuration.

1. Checkbox
2. Checkboxes
3. Color
4. Email
5. Number
6. Phone
7. Radio
8. Select
9. Text
10. Textarea
11. URL
12. WP Editor (WYSIWYG editor)

## Field Configuration

Here is a guide for implementing each Field type. You may also wish to refer to the source code for each Field which has its own documentation in the files.

### Checkbox

The Checkbox field uses the "choice" value to configure a single checkbox field whose value is input into the database as a string. Multiple checkboxes may be configured using "choices" instead of "choice". Each choice follows a "value => label" format. The "default" data_args value of a singular Checkbox configuration accepts a string and the multiple Checkbox configuration accepts an array of choice values. Required values are: label, id, type, choice.

```
array(
	'label'       => 'My Checkbox Field', // Required.
	'id'          => 'unique_checkbox_field', // Required.
	'type'        => 'checkbox', // Required.
	'description' => 'My checkbox field description',
	'choice'      => array( // Required.
		'1' => 'My Choice',
	),
	'data_args'   => array(
		'default' => '1',
	),
),
```

Multiple checkboxes are configured as shown below:

```
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
```

### Text

The Text field is the simplest field to implement. Required values are: label, id, type.

```
array(
	'label'       => 'My Text Field', // Required.
	'id'          => 'unique_text_field',
	'type'        => 'text',
	'description' => 'My text field description',
	'data_args'   => array(
		'placeholder'   => 'my placeholder',
		'default'       => 'A thoughtful, optional, default value',
		'data-lpignore' => 'true',
		'size'          => '40',
	),
),
```

### Color

The Color field uses Iris from WordPress's script files to render a color picker. Required values are: label, id, type.

```
array(
	'label'       => 'My Color Field',
	'id'          => 'unique_color_field',
	'type'        => 'color',
	'description' => 'My color field description',
	'data_args'   => array(
		'default' => '#000000',
	),
),
```

### Select

The Select field supports a "prompt" configuration value for customizing the first `<option>` element's label to describe what a user should do. The default value is "Please choose an option".

**Multiselect**

If you configure the field as a multiselect field, and choose to configure a default value, then you must declare the default value as an array of values.

```
array(
	'label'       => 'My Select Field',
	'id'          => 'unique_select_field',
	'type'        => 'select',
	'prompt'      => 'Select an option',
	'description' => 'My select field description',
	'choices'     => array(
		'option_one'   => 'Option 1',
		'option_two'   => 'Option 2',
		'option_three' => 'Option 3',
	),
	'data_args'   => array(
		'default' => 'option_one',
	)
),
```