<?php

/**
 * Plugin Name: WoodManager
 * Description: Manage your wooden packages easily
 * Version: 1.0.4
 * Author: Studio Montana
 * Author URI: http://www.studio-montana.com/
 * License: GPL2
 * Text Domain: woodmanager
 */

/**
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

/**
 * WoodManager PLUGIN CONSTANTS
*/
define('WOODMANAGER_PLUGIN_NAME', "woodmanager");
define('WOODMANAGER_PLUGIN_FILE', __FILE__);
define('WOODMANAGER_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WOODMANAGER_PLUGIN_URI', plugin_dir_url(__FILE__));

define('WOODMANAGER_PLUGIN_TEXT_DOMAIN', 'woodmanager');
define('WOODMANAGER_PLUGIN_CORE_FOLDER', 'core/');
define('WOODMANAGER_PLUGIN_BD_FOLDER', 'core/bd/');
define('WOODMANAGER_PLUGIN_BD_OBJECTS_FOLDER', 'core/bd-objects/');
define('WOODMANAGER_PLUGIN_API_FOLDER', 'core/api/');
define('WOODMANAGER_PLUGIN_COMMONS_FOLDER', 'core/commons/');
define('WOODMANAGER_PLUGIN_CONFIG_FOLDER', 'core/commons/config/');
define('WOODMANAGER_PLUGIN_POST_TYPES', 'core/post-types/');
define('WOODMANAGER_PLUGIN_TEMPLATES_FOLDER', 'templates/');

/**
 * WoodManager REPOSITORY PATHS & URLS
 */
$updir = wp_upload_dir();
$uppath = $updir['basedir'];
$upurl = $updir['baseurl'];
define('WOODMANAGER_PLUGIN_REPOSITORY_PATH', $uppath.'/'.WOODMANAGER_PLUGIN_NAME.'/');
define('WOODMANAGER_PLUGIN_REPOSITORY_URL', $upurl.'/'.WOODMANAGER_PLUGIN_NAME.'/');

/**
 * WoodManager PLUGIN DEFINITION
*/
if(!class_exists('WoodManager')){

	class WoodManager{

		/**
		 * Construct the plugin object
		 */
		public function __construct(){

			load_plugin_textdomain('woodmanager', false, dirname(plugin_basename(__FILE__)).'/lang/');
				
			do_action("woodmanager_before_requires");
				
			/** utils */
			require_once (WOODMANAGER_PLUGIN_PATH.'/'.WOODMANAGER_PLUGIN_COMMONS_FOLDER.'utils.php');
				
			/** api */
			require_once (WOODMANAGER_PLUGIN_PATH.'/'.WOODMANAGER_PLUGIN_API_FOLDER.'index.php');
				
			/** config */
			require_once (WOODMANAGER_PLUGIN_PATH.'/'.WOODMANAGER_PLUGIN_CONFIG_FOLDER.'config.php');
			
			/** bd */
			require_once (WOODMANAGER_PLUGIN_PATH.'/'.WOODMANAGER_PLUGIN_BD_FOLDER.'bd.php');
			require_once (WOODMANAGER_PLUGIN_PATH.'/'.WOODMANAGER_PLUGIN_BD_OBJECTS_FOLDER.'bd-package.php');
			require_once (WOODMANAGER_PLUGIN_PATH.'/'.WOODMANAGER_PLUGIN_BD_OBJECTS_FOLDER.'bd-package-installation.php');
			require_once (WOODMANAGER_PLUGIN_PATH.'/'.WOODMANAGER_PLUGIN_BD_OBJECTS_FOLDER.'bd-package-profile.php');
			require_once (WOODMANAGER_PLUGIN_PATH.'/'.WOODMANAGER_PLUGIN_BD_OBJECTS_FOLDER.'bd-package-key.php');
			require_once (WOODMANAGER_PLUGIN_PATH.'/'.WOODMANAGER_PLUGIN_BD_OBJECTS_FOLDER.'bd-package-update.php');
			require_once (WOODMANAGER_PLUGIN_PATH.'/'.WOODMANAGER_PLUGIN_BD_OBJECTS_FOLDER.'bd-package-release.php');
			
			/** post types */
			require_once (WOODMANAGER_PLUGIN_PATH.'/'.WOODMANAGER_PLUGIN_POST_TYPES.'post-type-package.php');
				
			do_action("woodmanager_after_requires");
			
			/** Install API */
			new WoodAPI();
				
			add_action("init", array('WoodManager', 'init'));

		}

		/**
		 * Activate the plugin
		 */
		public static function activate(){
			// BDD installation
			$bd = new WoodManager_BD();
			$bd::install();
		}

		/**
		 * Deactivate the plugin
		 */
		public static function deactivate(){
			// BDD uninstallation
			$bd = new WoodManager_BD();
			$bd::uninstall();
		}
		
		public static function get_info($name){
			$plugin_data = get_plugin_data(__FILE__);
			return $plugin_data[$name];
		}

		/**
		 * Load plugin textdomain.
		 */
		public static function init() {
				
			do_action("woodmanager_before_init");
				
			require_once (WOODMANAGER_PLUGIN_PATH.WOODMANAGER_PLUGIN_CORE_FOLDER.'init.php');
				
			do_action("woodmanager_after_init");
		}
	}
}

if(class_exists('WoodManager')){

	// Installation and uninstallation hooks
	register_activation_hook(__FILE__, array('WoodManager', 'activate'));
	register_deactivation_hook(__FILE__, array('WoodManager', 'deactivate'));

	// instantiate the plugin class
	$woodmanager_plugin = new WoodManager();
}