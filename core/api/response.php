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
		header('Content-Type: application/json;charset=utf-8');
		$key_host = '';
		if (isset($_GET['api-key-host']) && !empty($_GET['api-key-host'])){
			$key_host = urldecode($_GET['api-key-host']);
		}else if (isset($_GET['api-host']) && !empty($_GET['api-host'])){
			$key_host = urldecode($_GET['api-host']);
		}
		
		$key_package = '';
		if (isset($_GET['api-key-package']) && !empty($_GET['api-key-package'])){
			$key_package = urldecode($_GET['api-key-package']);
		}else if (isset($_GET['api-package']) && !empty($_GET['api-package'])){
			$key_package = urldecode($_GET['api-package']);
		}
		
		$key = '';
		if (isset($_GET['api-key']) && !empty($_GET['api-key'])) {
			$key = urldecode($_GET['api-key']);
		}
		
		$data = array("active" => woodmanager_is_active_package($key_package, $key_host, $key));
		return json_encode($data);
	}
	
	private static function _install() {
		header('Content-Type: application/json;charset=utf-8');
		$data = array();
		$host = '';
		if (isset($_GET['api-host']) && !empty($_GET['api-host'])) {
			$host = urldecode($_GET['api-host']);
		}
		$package = '';
		if (isset($_GET['api-package']) && !empty($_GET['api-package'])) {
			$package = urldecode($_GET['api-package']);
		}
		$version = '';
		if (isset($_GET['api-version']) && !empty($_GET['api-version'])) {
			$version = urldecode($_GET['api-version']);
		}
		if(woodmanager_package_install($package, $host, $version)) {
			$data['install'] = true;
		} else {
			$data['install'] = false;
		}
		return json_encode($data);
	}
	
	private static function _latestrelease() {
		header('Content-Type: application/json;charset=utf-8');
		$data = array();
		$package = '';
		if (isset($_GET['api-package']) && !empty($_GET['api-package'])){
			$package = urldecode($_GET['api-package']);
		}
		$key_host = '';
		if (isset($_GET['api-key-host']) && !empty($_GET['api-key-host'])){
			$key_host = urldecode($_GET['api-key-host']);
		}else if (isset($_GET['api-host']) && !empty($_GET['api-host'])){
			$key_host = urldecode($_GET['api-host']);
		}
		$key_package = '';
		if (isset($_GET['api-key-package']) && !empty($_GET['api-key-package'])){
			$key_package = urldecode($_GET['api-key-package']);
		}else if (isset($_GET['api-package']) && !empty($_GET['api-package'])){
			$key_package = urldecode($_GET['api-package']);
		}
		$key = '';
		if (isset($_GET['api-key']) && !empty($_GET['api-key'])){
			$key = urldecode($_GET['api-key']);
		}
		$prerelease_hosts = null;
		// uncomment following declaration to active pre-release only for their websites
		/* $prerelease_hosts = array(
		 "http://localhost/woodtry/site",
		 "http://www.van-venture.com",
		 "https://www.seb-c.com",
		 ); */
		if (empty($prerelease_hosts) || in_array($key_host, $prerelease_hosts)){
			if (woodmanager_is_active_package($key_package, $key_host, $key)){
				$data = woodmanager_get_package_latest_release($package);
			}else{
				$data = json_encode(array("error" => "'".$package."' ".__("is not active for your host", WOODMANAGER_PLUGIN_TEXT_DOMAIN)));
			}
		}else{
			$data = json_encode(array("error" => "'".$package."' ".__("updates are only available for prerelease - please wait to get release...", WOODMANAGER_PLUGIN_TEXT_DOMAIN)));
		}
		return $data;
	}
	
	private static function _update() {
		header('Content-Type: application/json;charset=utf-8');
		$data = array();
		$host = '';
		if (isset($_GET['api-host']) && !empty($_GET['api-host'])) {
			$host = urldecode($_GET['api-host']);
		}
		$package = '';
		if (isset($_GET['api-package']) && !empty($_GET['api-package'])) {
			$package = urldecode($_GET['api-package']);
		}
		$version = '';
		if (isset($_GET['api-version']) && !empty($_GET['api-version'])) {
			$version = urldecode($_GET['api-version']);
		}
		if(woodmanager_package_update($package, $host, $version)) {
			$data['install'] = true;
		} else {
			$data['install'] = false;
		}
		return json_encode($data);
	}
	
}
