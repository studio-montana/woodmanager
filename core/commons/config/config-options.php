<?php
/**
 * @package WoodManager
 * @author Sébastien Chandonay www.seb-c.com / Cyril Tissot www.cyriltissot.com
 * License: GPL2
 * Text Domain: woodmanager
 *
 * Copyright 2016 Sébastien Chandonay (email : please contact me from my website)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
defined('ABSPATH') or die("Go Away!");

class WoodManagerOptions {
	/**
	 * Holds the values to be used in the fields callbacks
	 */
	private $options;

	/**
	 * Start up
	 */
	public function __construct(){
		// set options
		$this->options = woodmanager_get_options();

		// actions
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
	}

	/**
	 * Add options page
	 */
	public function add_plugin_page(){
		// This page will be under "Settings"
		add_options_page(
		'Settings Admin',
		'WoodManager',
		'manage_options',
		'woodmanager_options',
		array( $this, 'create_admin_page' )
		);
	}

	/**
	 * Options page callback
	 */
	public function create_admin_page(){
		?>
<div class="wrap">
	<h2>
		<i class="fa fa-gears"></i>&nbsp;
		<?php _e("WoodManager settings", WOODMANAGER_PLUGIN_TEXT_DOMAIN); ?>
	</h2>
	<form method="post" action="options.php">
		<?php
		// This prints out all hidden setting fields
		settings_fields( 'woodmanager_option_group' );
		do_settings_sections( 'woodmanager-admin' );
		submit_button();
		?>
	</form>
</div>
<?php
	}

	/**
	 * Register and add settings
	 */
	public function page_init(){
		register_setting(
		'woodmanager_option_group', // Option group
		WOODMANAGER_CONFIG_OPTIONS, // Option name
		array( $this, 'sanitize' ) // Sanitize
		);

		// --- Key activation

		add_settings_section(
		'woodmanager_settings_github', // ID
		__("Github"), // Title
		array( $this, 'print_section_github_info' ), // Callback
		'woodmanager-admin' // Page
		);

		add_settings_field(
		'github-api-url', // ID
		__("Github API url", WOODMANAGER_PLUGIN_TEXT_DOMAIN), // Title
		array( $this, 'print_setting_github_api_url' ), // Callback
		'woodmanager-admin', // Page
		'woodmanager_settings_github' // Section
		);

		add_settings_field(
		'github-user', // ID
		__("Github user", WOODMANAGER_PLUGIN_TEXT_DOMAIN), // Title
		array( $this, 'print_setting_github_user' ), // Callback
		'woodmanager-admin', // Page
		'woodmanager_settings_github' // Section
		);

		add_settings_field(
		'github-clientid', // ID
		__("Github client id", WOODMANAGER_PLUGIN_TEXT_DOMAIN), // Title
		array( $this, 'print_setting_github_clientid' ), // Callback
		'woodmanager-admin', // Page
		'woodmanager_settings_github' // Section
		);

		add_settings_field(
		'github-clientsecret', // ID
		__("Github client secret", WOODMANAGER_PLUGIN_TEXT_DOMAIN), // Title
		array( $this, 'print_setting_github_clientsecret' ), // Callback
		'woodmanager-admin', // Page
		'woodmanager_settings_github' // Section
		);

		add_settings_field(
		'github-update-interval', // ID
		__("Github update interval", WOODMANAGER_PLUGIN_TEXT_DOMAIN), // Title
		array( $this, 'print_setting_github_update_interval' ), // Callback
		'woodmanager-admin', // Page
		'woodmanager_settings_github' // Section
		);

	}


	/**
	 * Sanitize each setting field as needed
	 *
	 * @param array $input Contains all settings fields as array keys
	 */
	public function sanitize($input){

		if (isset($input['github-api-url']) && !empty($input['github-api-url'])){
			$input['github-api-url'] = rtrim($input['github-api-url'], '/') . '/';
		}

		$input = apply_filters("woodmanager_config_options_sanitize_fields", $input);

		return $input;
	}

	/**
	 * Print the Section text
	 */
	public function print_section_github_info(){
	}

	function print_setting_github_api_url(){
		$value = "";
		if (isset($this->options['github-api-url']))
			$value = $this->options['github-api-url'];
		echo '<input placeholder="'.__("https://api.github.com/", WOODMANAGER_PLUGIN_TEXT_DOMAIN).'" type="text" name="'.WOODMANAGER_CONFIG_OPTIONS.'[github-api-url]" value="'.$value.'" />';
	}

	function print_setting_github_user(){
		$value = "";
		if (isset($this->options['github-user']))
			$value = $this->options['github-user'];
		echo '<input type="text" name="'.WOODMANAGER_CONFIG_OPTIONS.'[github-user]" value="'.$value.'" />';
	}

	function print_setting_github_clientid(){
		$value = "";
		if (isset($this->options['github-clientid']))
			$value = $this->options['github-clientid'];
		echo '<input type="text" name="'.WOODMANAGER_CONFIG_OPTIONS.'[github-clientid]" value="'.$value.'" />';
	}

	function print_setting_github_clientsecret(){
		$value = "";
		if (isset($this->options['github-clientsecret']))
			$value = $this->options['github-clientsecret'];
		echo '<input type="text" name="'.WOODMANAGER_CONFIG_OPTIONS.'[github-clientsecret]" value="'.$value.'" />';
	}

	function print_setting_github_update_interval(){
		$value = "PT1H";
		if (isset($this->options['github-update-interval']))
			$value = $this->options['github-update-interval'];
		echo '<input type="text" name="'.WOODMANAGER_CONFIG_OPTIONS.'[github-update-interval]" value="'.$value.'" placeholder="DateInterval - PT1H = 1 hour - PT5S = 5 seconds" />';
	}
}

if( is_admin() )
	$woodmanager_options = new WoodManagerOptions();