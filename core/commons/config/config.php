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

/**
 * CONSTANTS
*/
define('WOODMANAGER_CONFIG_OPTIONS', 'woodmanager_config_options');

/**
 * GLOBALS
*/
global $woodmanager_config_default_values;
global $woodmanager_config_values;

if (!function_exists("woodmanager_get_options")):
/**
 * retrieve woodmanager options values
* @return multiple : option value - null if doesn't exists
*/
function woodmanager_get_options($reload = false){
	global $woodmanager_config_values;
	if ($reload || !isset($woodmanager_config_values)){
		$options = get_option(WOODMANAGER_CONFIG_OPTIONS);
		if (!isset($options))
			$options = array();
		$default_values = woodmanager_get_option_default_values();
		foreach ($default_values as $id => $value){
			if (!isset($options[$id])){
				$options[$id] = $value;
			}
		}
	}
	return $options;
}
endif;

if (!function_exists("woodmanager_get_option")):
/**
 * retrieve woodmanager option value
* @param string $id : option id
* @return multiple : option value - null if doesn't exists
*/
function woodmanager_get_option($slug){
	$res = null;
	$options = woodmanager_get_options();
	if (!empty($options)){
		foreach ($options as $value) {
			if (isset($options[$slug])) {
				$res = $options[$slug];
			}
		}
	}
	return $res;
}
endif;

if (!function_exists("woodmanager_get_option_default_values")):
/**
 * retrieve woodmanager option default values
* @return multiple : option value - null if doesn't exists
*/
function woodmanager_get_option_default_values(){
	global $woodmanager_config_default_values;
	if (!isset($woodmanager_config_default_values)){
		$woodmanager_config_default_values = array();
		$woodmanager_config_default_values = apply_filters("woodmanager_config_default_values", $woodmanager_config_default_values);
	}
	return $woodmanager_config_default_values;
}
endif;

/**
 * Plugin options page
 */
if (is_admin()){

	require_once (WOODMANAGER_PLUGIN_PATH.'/'.WOODMANAGER_PLUGIN_CONFIG_FOLDER.'config-options.php');

	if (!function_exists("woodmanager_plugin_action_links")):
	/**
	 * Plugin admin links
	* @param unknown $links
	* @return string
	*/
	function woodmanager_plugin_action_links( $links ) {
		global $pagenow;
		if($pagenow == 'plugins.php'){
			$links[] = '<a href="'.esc_url(get_admin_url(null, 'options-general.php?page=woodmanager_options')).'">'.__("Setup", WOODMANAGER_PLUGIN_TEXT_DOMAIN).'</a>';
		}
		return $links;
	}
	add_filter('plugin_action_links_woodmanager/woodmanager.php', 'woodmanager_plugin_action_links');
	endif;
}