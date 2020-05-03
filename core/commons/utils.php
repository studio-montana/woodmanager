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

if (!function_exists("woodmanager_clean")):
/**
 * Sanitize $var
 * @param unknown $var
 * @return unknown
 */
function woodmanager_clean($var, $stripslashes = true) {
	if ( is_array( $var ) ) {
		return array_map( 'woodmanager_clean', $var );
	} else {
		$cleaned = is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
		return $stripslashes ? stripslashes($cleaned) : $cleaned;
	}
}
endif;

if (!function_exists("woodmanager_starts_with")):
/**
 * test if $haystack start with $needle
* @param string $haystack
* @param string $needle
* @return boolean
*/
function woodmanager_starts_with($haystack, $needle){
	return $needle === "" || strpos($haystack, $needle) === 0;
}
endif;

if (!function_exists("woodmanager_get_user_options")):
/**
 * retrieve user html options
* @param string $selected_id
*/
function woodmanager_get_user_options($selected_id = null, $groupby_role = true){
	$user_options = "";
	if ($groupby_role){
		$roles = get_editable_roles();
		if (!empty($roles)){
			foreach ($roles as $role_name => $role_info){
				$users = get_users(array('role' => $role_name));
				if (!empty($users)){
					$user_options .= '<optgroup label="'.$role_name.'">';
					foreach ($users as $user){
						$selected = '';
						if ($selected_id == $user->ID)
							$selected = ' selected="selected"';
						$user_options .= '<option value="'.$user->ID.'" '.$selected.'>'.$user->display_name.'</option>';
					}
					$user_options .= '</optgroup>';
				}
			}
		}
	}else{
		$users = get_users();
		foreach ($users as $user){
			$selected = '';
			if ($selected_id == $user->ID)
				$selected = ' selected="selected"';
			$user_options .= '<option value="'.$user->ID.'" '.$selected.'>'.$user->display_name.'</option>';
		}
	}
	return $user_options;
}
endif;

if (!function_exists("woodmanager_get_original_post")):
/**
 * retrieve original post id for specified post - particulary used when WPML is active to get original post translation
* @param <int, object> $post
* @return int original_id if exists
*/
function woodmanager_get_original_post($post){
	if (is_object($post))
		$id_post = $post->id;
	else
		$id_post = $post;

	$original_id = $id_post;

	if (function_exists('icl_object_id')) {
		global $sitepress;
		$trid = null;
		if (isset($_GET['trid']))
			$trid = $_GET['trid'];
		else if (!empty($id_post))
			$trid = $sitepress->get_element_trid($id_post, 'post_package');
		if (!empty($trid)){
			$original_id = $sitepress->get_original_element_id_by_trid($trid);
		}
	}

	return $original_id;
}
endif;

if (!function_exists("woodmanager_trace")):
/**
 * write log trace in log file's theme
* @param string $content
*/
function woodmanager_trace($log){
	if (true === WP_DEBUG) {
		if (is_array($log) || is_object($log)) {
			return error_log("PHP Info:\t".print_r($log, true));
		} else {
			return error_log("PHP Info:\t".$log);
		}
	}
	return false;
}
endif;

if (!function_exists("woodmanager_is_active_package")):
/**
 * test if package is activated
* @param unknown $ressource_name
* @return boolean
*/
function woodmanager_is_active_package($package_slug = '', $host = '', $key = '') {
	if (!empty($package_slug)){
		$bd_package = BD_Package::get_package_by_slug($package_slug);
		if (!empty($bd_package) && $bd_package->free == 'true'){
			return true;
		}else if (!empty($bd_package) && !empty($host) && !empty($key)){
			global $wpdb;
			$host = rtrim($host, '/'); // important : trim last slash
			// $bd_keys = BD_Package_Key::get_package_keys("id_package = ".$bd_package->id." AND host like '".$wpdb->esc_like($host)."' AND key_activation like '".$wpdb->esc_like($key)."'");
			
			// @since 13/02/2017 - patch to validate https for a saved http host
			$host = str_replace("http://", "", $host);
			$host = str_replace("https://", "", $host);
			$bd_keys = BD_Package_Key::get_package_keys("id_package = ".$bd_package->id." AND key_activation like '".$wpdb->esc_like($key)."' AND (host like '".$wpdb->esc_like("http://".$host)."' OR host like '".$wpdb->esc_like("https://".$host)."')");
			if (!empty($bd_keys) && count($bd_keys) > 0){
				return true;
			}
		}
	}
	return false;
}
endif;

if (!function_exists("woodmanager_package_install")):
/**
 * package install notification
* @param string $package
* @param string $host
*/
function woodmanager_package_install($package_slug = '', $host = '', $version = ''){
	$package = BD_Package::get_package_by_slug($package_slug);
	if (!empty($package)){
		$package_installations = BD_Package_Installation::get_package_installations("id_package = ".$package->id);
		$installations = array();
		$already_exists = false;
		$old_version = '';
		if (!empty($package_installations)){
			foreach ($package_installations as $package_installation){
				if (!empty($package_installation->host) && $package_installation->host == $host){
					$old_version = $package_installation->version;
					$res = BD_Package_Installation::update_package_installation($package_installation, array('version' => $version)); // update date_modif & version
					$already_exists = true;
				}
			}
		}
		if (!$already_exists){
			$res = BD_Package_Installation::create_package_installation(array('id_package' => $package->id, 'host' => $host, 'version' => $version));
			// -- Mail notification
			$from = get_bloginfo( 'name' )." <" . get_bloginfo ( 'admin_email' ) . ">";
			$header = 'From: ' . $from . "\r\n";
			$header .= 'Reply-To: ' . $from . "\r\n";
			$header .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
			$template = 'New installation - package : '.$package_slug.' - host : '.$host.' - version : '.$version;
			$wp_mail = wp_mail ( get_bloginfo ( 'admin_email' ), "[Lab Montana] package installation", $template, $header );			
			// -----------
		}else if($old_version != $version){
			// -- Mail notification
			$from = get_bloginfo( 'name' )." <" . get_bloginfo ( 'admin_email' ) . ">";
			$header = 'From: ' . $from . "\r\n";
			$header .= 'Reply-To: ' . $from . "\r\n";
			$header .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
			$template = 'New update - package : '.$package_slug.' - host : '.$host.' - version : '.$old_version.' to '.$version;
			$wp_mail = wp_mail ( get_bloginfo ( 'admin_email' ), "[Lab Montana] package update", $template, $header );
			// -----------
		}
	}else{
		return false;
	}
	return true;
}
endif;

if (!function_exists("woodmanager_package_update")):
/**
 * package update notification
* @param string $package
* @param string $host
*/
function woodmanager_package_update($package_slug = '', $host = '', $version = ''){
	$package = BD_Package::get_package_by_slug($package_slug);
	if (!empty($package)){
		$package_updates = BD_Package_Update::get_package_updates("id_package = ".$package->id);
		$updates = array();
		$already_exists = false;
		if (!empty($package_updates)){
			foreach ($package_updates as $package_update){
				if (!empty($package_update->host) && $package_update->host == $host){
					$res = BD_Package_Update::update_package_update($package_update, array('version' => $version)); // update date_modif & version
					$already_exists = true;
				}
			}
		}
		if (!$already_exists)
			$res = BD_Package_Update::create_package_update(array('id_package' => $package->id, 'host' => $host, 'version' => $version));
	}else{
		return false;
	}
	return true;
}
endif;

if (!function_exists("woodmanager_get_package_latest_release")):
/**
 * retrieve package's latest release
* @param string $package
* @param string $format : ARRAY_A | OBJECT
* @return Ambigous <array, stdClass>
*/
function woodmanager_get_package_latest_release($package_slug){

	$data = '';

	$package = BD_Package::get_package_by_slug($package_slug);
	if (!empty($package)){
		
		$relead = true;
		$now = new DateTime(current_time('mysql'));

		$last_release = $package->package_release;
		$last_release_date = $package->package_release_date;
		if (!empty($last_release_date)){
			$last_release_datetime = new DateTime($last_release_date);
			$github_update_interval = woodmanager_get_option('github-update-interval');
			if (!empty($github_update_interval))
				$last_release_datetime->add(new DateInterval($github_update_interval)); // PT1H
			if ($last_release_datetime > $now){
				$relead = false;
			}
		}
		
		$wp_remote = null;
		$github_release = '';
		if ($relead){
			/** 
			 * NOTE
			 * - since 02/2020 github doesn't support authentication parameters as query_string
			 * - we must use 'user' header parameter with clientid:clientsecret
			 * - more information : https://developer.github.com/changes/2020-02-10-deprecating-auth-through-query-param/
			 */
			$github_url = woodmanager_get_option('github-api-url');
			$github_user = woodmanager_get_option('github-user');
			$github_clientid = woodmanager_get_option('github-clientid');
			$github_clientsecret = woodmanager_get_option('github-clientsecret');
			if (!empty($github_url) && !empty($github_user)){
				$url = $github_url."repos/".$github_user."/".$package->slug."/releases/latest";
				$wp_remote = wp_remote_get($url, array(
						'headers' => array(
								'Authorization' => 'Basic ' . base64_encode($github_clientid.':'.$github_clientsecret)
						),
						'sslverify' => false
				));
				$github_release = wp_remote_retrieve_body($wp_remote);
			}
		}
		
		if (is_wp_error($wp_remote)){
			if (!empty($last_release)){
				$data = $last_release;
			}else{
				$data = json_encode(array("error" => __("An error occurs during this process")));
			}
			woodmanager_trace("Error during Github connection : ".var_export($wp_remote, true));
		}else{
	
			// compare versions
			$last_release_arr = array();
			$github_release_arr = array();
			if (!empty($last_release))
				$last_release_arr = @json_decode($last_release, true);
			if (!empty($github_release))
				$github_release_arr = @json_decode($github_release, true);

			woodmanager_trace("last_release_arr : " . var_export($last_release_arr, true));
			woodmanager_trace("github_release_arr : " . var_export($github_release_arr, true));
			
			if (isset($github_release_arr['tag_name']) && isset($last_release_arr['tag_name'])) {
	
				if (version_compare($github_release_arr['tag_name'], $last_release_arr['tag_name'])){
					$new_release = array();
					// release version
					$new_release['tag_name'] = '';
					if (isset($github_release_arr['tag_name']))
						$new_release['tag_name'] = $github_release_arr['tag_name'];
					// release date
					$new_release['published_at'] ='';
					if (isset($github_release_arr['published_at']))
						$new_release['published_at'] = $github_release_arr['published_at'];
					// release comment
					$new_release['body'] ='';
					if (isset($github_release_arr['body']))
						$new_release['body'] = $github_release_arr['body'];
					// release zip url
					$new_release['zipball_url'] = '';
					if (isset($github_release_arr['zipball_url'])){
						$ball = woodmanager_download_package($package->slug, $github_release_arr['zipball_url'], '.zip', $new_release['tag_name']);
						$new_release['zipball_url'] = $ball['url'];
					}
					// release tar url
					$new_release['tarball_url'] = '';
					if (isset($github_release_arr['tarball_url'])){
						$ball = woodmanager_download_package($package->slug, $github_release_arr['tarball_url'], '.tar.gz', $new_release['tag_name']);
						$new_release['tarball_url'] = $ball['url'];
					}
					// update release
					$res = BD_Package::update_package($package, array('package_release' => json_encode($new_release), 'package_release_github' => $github_release, 'package_release_date' => current_time('mysql')));
		
					$data = json_encode($new_release);
						
				}else{
					if (!empty($last_release)){
						$data = $last_release;
					}
				}
			}else{
				if (!empty($last_release)) {
					$data = $last_release;
				}
			}
		}
	}
	return $data;
}
endif;

if (!function_exists("woodmanager_download_package")):
function woodmanager_download_package($package, $ball_url, $ext, $version = ''){
	$final_path = '';
	$final_url = '';
	if (!empty($package) && !empty($ball_url) && !empty($ext)){
		$date = current_time('mysql');
		$hash_1 = md5($date);
		$hash_2 = md5($package.$date);
		$final_path = WOODMANAGER_PLUGIN_REPOSITORY_PATH.$package.'-'.$hash_1.'/'.$hash_2.$ext;
		$final_url = WOODMANAGER_PLUGIN_REPOSITORY_URL.$package.'-'.$hash_1.'/'.$hash_2.$ext;

		if (file_exists($final_path)){
			unlink($final_path);
		}else{
			$dirname = dirname($final_path);
			if (!is_dir($dirname)){
				mkdir($dirname, 0755, true);
			}
		}

		$github_clientid = woodmanager_get_option('github-clientid');
		$github_clientsecret = woodmanager_get_option('github-clientsecret');
		$ball_url = add_query_arg(array("client_id" => $github_clientid), $ball_url);
		$ball_url = add_query_arg(array("client_secret" => $github_clientsecret), $ball_url);

		$options  = array('http' => array('method' => 'GET', 'user_agent' => $_SERVER['HTTP_USER_AGENT']));
		$context  = stream_context_create($options);
		file_put_contents($final_path, file_get_contents($ball_url, false, $context));
	}
	return array('path' => $final_path, 'url' => $final_url);
}
endif;