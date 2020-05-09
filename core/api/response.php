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

class WoodAPIResponse {
	
	public static function get($action){
		// No cache headers.
		nocache_headers();
		if ($action) {
			$res = '';
			// Buffer, we won't want any output here.
			ob_start();
			$action = strtolower(woodmanager_clean($action));
			// Is there actually something hooked into this API request? If not trigger 400 - Bad request.
			if (method_exists('WoodAPIResponse','_' . $action)) {
				status_header(200);
				$res = call_user_func('WoodAPIResponse::_' . $action);
			}
			// Done, clear buffer and exit.
			ob_end_clean();
			return $res;
		}
		status_header(400);
	}
	
	private static function _active() {
		$host = '';
		if (isset($_GET['api-key-host']) && !empty($_GET['api-key-host'])){
			$host = urldecode($_GET['api-key-host']);
		}else if (isset($_GET['api-host']) && !empty($_GET['api-host'])){
			$host = urldecode($_GET['api-host']);
		}else if (isset($_GET['host']) && !empty($_GET['host'])){
			$host = urldecode($_GET['host']);
		}
		
		$package = '';
		if (isset($_GET['api-key-package']) && !empty($_GET['api-key-package'])){
			$package = urldecode($_GET['api-key-package']);
		}else if (isset($_GET['api-package']) && !empty($_GET['api-package'])){
			$package = urldecode($_GET['api-package']);
		}else if (isset($_GET['package']) && !empty($_GET['package'])){
			$package = urldecode($_GET['package']);
		}
		
		$key = '';
		if (isset($_GET['api-key']) && !empty($_GET['api-key'])) {
			$key = urldecode($_GET['api-key']);
		}else if (isset($_GET['key']) && !empty($_GET['key'])) {
			$key = urldecode($_GET['key']);
		}
		status_header(200);
		header('Content-Type: application/json;charset=utf-8');
		return json_encode(array("active" => woodmanager_is_active_package($package, $host, $key)));
	}
	
	private static function _install() {
		$data = array();
		$host = '';
		if (isset($_GET['api-host']) && !empty($_GET['api-host'])) {
			$host = urldecode($_GET['api-host']);
		}else if (isset($_GET['host']) && !empty($_GET['host'])){
			$host = urldecode($_GET['host']);
		}
		$package = '';
		if (isset($_GET['api-package']) && !empty($_GET['api-package'])) {
			$package = urldecode($_GET['api-package']);
		}else if (isset($_GET['package']) && !empty($_GET['package'])){
			$package = urldecode($_GET['package']);
		}
		$version = '';
		if (isset($_GET['api-version']) && !empty($_GET['api-version'])) {
			$version = urldecode($_GET['api-version']);
		}else if (isset($_GET['version']) && !empty($_GET['version'])){
			$version = urldecode($_GET['version']);
		}
		if(woodmanager_package_install($package, $host, $version)) {
			$data['install'] = true;
		} else {
			$data['install'] = false;
		}
		status_header(200);
		header('Content-Type: application/json;charset=utf-8');
		return json_encode($data);
	}
	
	private static function _latestrelease() {
		$data = array();
		$package = '';
		if (isset($_GET['api-package']) && !empty($_GET['api-package'])){
			$package = urldecode($_GET['api-package']);
		}else if (isset($_GET['package']) && !empty($_GET['package'])){
			$package = urldecode($_GET['package']);
		}
		$host = '';
		if (isset($_GET['api-key-host']) && !empty($_GET['api-key-host'])){
			$host = urldecode($_GET['api-key-host']);
		}else if (isset($_GET['api-host']) && !empty($_GET['api-host'])){
			$host = urldecode($_GET['api-host']);
		}else if (isset($_GET['host']) && !empty($_GET['host'])){
			$host = urldecode($_GET['host']);
		}
		$key = '';
		if (isset($_GET['api-key']) && !empty($_GET['api-key'])){
			$key = urldecode($_GET['api-key']);
		}else if (isset($_GET['key']) && !empty($_GET['key'])){
			$key = urldecode($_GET['key']);
		}
		
		/**
		 * Depuis WoodManager v.1.2 (05/05/2020)
		 * Le paramètre 'api-package-version' est souhaité
		 * Ce paramètre permet de garder (si l'option 'separate-major-releases' du package en question est activé) les packages dans leur version majeur.
		 * Ainsi, lors d'un changement de version majeur d'un package, les anciens sites n'y passent pas automatiquement, cela nécessite une action manuelle
		 * Cela est parfois nécessaire pour maintenir des anciennes version de WP avec une version de package adéquate.
		 * Exemple : admettons qu'il existe deux releases, une en version 1.0.15 et l'autre en version 2.0.20 et que cette option soit activé sur le package
		 * 		- un site utilisant le package en version 1.0.3 passera en 1.0.15 (et pas en 2.0.20 car sa version majeur est 1)
		 * 		- un site utilisant le package en version 2.0.7 passera en 2.0.20
		 */
		$package_version = '';
		if (isset($_GET['api-package-version']) && !empty($_GET['api-package-version'])){
			$package_version = urldecode($_GET['api-package-version']);
		} else if (isset($_GET['package-version']) && !empty($_GET['package-version'])){
			$package_version = urldecode($_GET['package-version']);
		} else if (isset($_GET['version']) && !empty($_GET['version'])){
			$package_version = urldecode($_GET['version']);
		}

		if (empty($package_version)) {
			if ($package === 'woodkit') {
				/**
				 * Par default, pour Woodkit
				 * On admet que les packages Woodkit qui n'envoient pas le paramètre 'api-package-version' sont en version majeur 1 (il n'existe pas de Woodkit v.0 et la v.2 envoi ce paramètre)
				 * En fixant ce paramètre par défault, on n'a pas à intervenir manuellement sur tous les Woodkit installés pour ajouter le paramètre 'api-package-version' à l'appel de l'API et c'est tant mieux !
				 * NOTE : ce package sépare les versions majeurs, c'est pourquoi on ne peut pas mettre $package_version = '0.0.0' - ainsi les woodkit en v.1 restent en v.1, ils ne passent pas en v.2 sans action manuelle
				 */
				$package_version = '1.0.0';
			} else {
				/**
				 * Par défault
				 */
				$package_version = '0.0.0';
			}
		}
		
		if (woodmanager_is_active_package($package, $host, $key)){
			$data = woodmanager_get_package_latest_release($package, $package_version, woodmanager_is_package_prerelease_enabled($package, $host, $key));
		}else{
			$data = json_encode(array("error" => "'".$package."' ".__("package isn't active or doesn't exist", WOODMANAGER_PLUGIN_TEXT_DOMAIN)));
		}
		status_header(200);
		header('Content-Type: application/json;charset=utf-8');
		return $data;
	}
	
	private static function _update() {
		$data = array();
		$host = '';
		if (isset($_GET['api-host']) && !empty($_GET['api-host'])) {
			$host = urldecode($_GET['api-host']);
		}else if (isset($_GET['host']) && !empty($_GET['host'])) {
			$host = urldecode($_GET['host']);
		}
		$package = '';
		if (isset($_GET['api-package']) && !empty($_GET['api-package'])) {
			$package = urldecode($_GET['api-package']);
		}else if (isset($_GET['package']) && !empty($_GET['package'])) {
			$package = urldecode($_GET['package']);
		}
		$version = '';
		if (isset($_GET['api-version']) && !empty($_GET['api-version'])) {
			$version = urldecode($_GET['api-version']);
		}else if (isset($_GET['version']) && !empty($_GET['version'])) {
			$version = urldecode($_GET['version']);
		}
		if(woodmanager_package_update($package, $host, $version)) {
			$data['install'] = true;
		} else {
			$data['install'] = false;
		}
		status_header(200);
		header('Content-Type: application/json;charset=utf-8');
		return json_encode($data);
	}
	
}
