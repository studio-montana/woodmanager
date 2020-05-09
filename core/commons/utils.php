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

function woodmanager_package_release_version_compare($release_1, $release_2) {
	return version_compare($release_1->version, $release_2->version);
}

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

/**
 * write error log
* @param string $content
*/
function woodmanager_trace_error($log){
	if (is_array($log) || is_object($log)) {
		return error_log("PHP Error:\t".print_r($log, true));
	}
	return error_log("PHP Error:\t".$log);
}

/**
 * write log
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

function woodmanager_get_package_websites($package, $host, $key = null, $include_scope_depends_on = true, $processed_package_ids = array()) {
	global $wpdb;
	$res = array();
	if (!is_object($package)) {
		if (is_numeric($package)) {
			$package = BD_Package::get_package($package);
		} else {
			$package = BD_Package::get_package_by_slug($package);
		}
	}
	if (is_object($package) && !empty($host) && !in_array($package->id, $processed_package_ids)) {
		$host = rtrim($host, '/'); // important : trim last slash
		$host = str_replace("https://", "", str_replace("http://", "", $host)); // whatever protocole
		$sql_query = "id_package = ".$package->id." AND (host like '".$wpdb->esc_like("http://".$host)."' OR host like '".$wpdb->esc_like("https://".$host)."')";
		if ($key !== null) {
			$sql_query .= " AND key_activation like '".$wpdb->esc_like($key)."'";
		}
		$res = BD_Package_Website::get_package_websites($sql_query);
		$processed_package_ids[] = $package->id;
		// include websites from package dependencies
		if ($include_scope_depends_on && BD_Package::is_scope_dependency($package)) {
			$websites_dependency = woodmanager_get_package_websites($package->scope, $host, $key, $include_scope_depends_on, $processed_package_ids);
			$res = array_merge($res, $websites_dependency);
		}
	}
	woodmanager_trace("woodmanager_get_package_websites({$host}, {$key}) : " . var_export($res, true));
	return $res;
}

function woodmanager_is_package_prerelease_enabled($package, $host, $key = null) {
	if (!is_object($package)) {
		if (is_numeric($package)) {
			$package = BD_Package::get_package($package);
		} else {
			$package = BD_Package::get_package_by_slug($package);
		}
	}
	if (is_object($package) && !empty($host)){
		$websites = woodmanager_get_package_websites($package, $host, $key);
		if (!empty($websites)) {
			$is_prerelease_enabled = false;
			// check if at least one of websites has prerelease === 'true'
			foreach ($websites as $website) {
				if (!empty($website->prerelease) && $website->prerelease === 'true') {
					$is_prerelease_enabled = true;
					break;
				}
			}
			return $is_prerelease_enabled;
		}
	}
	return false;
}

if (!function_exists("woodmanager_is_active_package")):
/**
 * test if package is activated
* @return boolean
*/
function woodmanager_is_active_package($package, $host = null, $key = null) {
	if (!is_object($package)) {
		if (is_numeric($package)) {
			$package = BD_Package::get_package($package);
		} else {
			$package = BD_Package::get_package_by_slug($package);
		}
	}
	if (is_object($package)) {
		if (BD_Package::is_scope_public($package)){
			return true;
		}else if ($host !== null && $key !== null){
			$websites = woodmanager_get_package_websites($package, $host, $key);
			if (!empty($websites)) {
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

/**
 * Fetch Github repository for this package if necessary to update releases in our DB
 * @param string|BD_Package $package package object or package's slug
 * @param boolean $force_fetch force github fecthing, otherwise we keep calm with time interval
 */
function woodmanager_update_package_releases($package, $force_fetch = false) {
	if (!is_object($package)) {
		$package = BD_Package::get_package_by_slug($package);
	}
	if ($package){
		/** 
		 * IMPORTANT : les sites clients peuvent faire des appels à des moment simultanés/rapprochés et cette fonction
		 * peut prendre du temps (chargement des releases) => on doit donc se prémunir d'un update déjà en cours.
		 * ATTENTION : cette function ne doit pas faire de return (sauf après mise à jour de l'option 'woodmanager_updating_releases'
		 * en fin de process, sinon les update seront bloqués.
		 */
		$woodmanager_updating_releases = get_option('woodmanager_updating_releases', array());
		if (in_array($package->slug, $woodmanager_updating_releases)) {
			woodmanager_trace("##############################################################");
			woodmanager_trace("Github release update is already processing - please try later");
			woodmanager_trace("##############################################################");
			return;
		} else {
			$woodmanager_updating_releases[] = $package->slug;
			update_option('woodmanager_updating_releases', $woodmanager_updating_releases);
			/** pas d'update en cours, on y va */
			
			$fetch_github = true;
			$fetch_github_error = false;
			$last_repository_fetch = $package->last_repository_fetch;
			if (!empty($last_repository_fetch) && !$force_fetch){
				$last_repository_fetch = new DateTime($last_repository_fetch);
				$github_update_interval = woodmanager_get_option('github-update-interval', 'PT1H');
				if (!empty($github_update_interval)) {
					$last_repository_fetch->add(new DateInterval($github_update_interval));
				}
				$now = new DateTime(current_time('mysql'));
				if ($last_repository_fetch > $now){
					$fetch_github = false;
				}
			}
			if ($fetch_github || $force_fetch){
				/**
				 * Fetch Github API to retrieve all package releases
				 */
				$github_releases = woodmanager_fetch_github_releases($package);
				if (!empty($github_releases)) {
					foreach ($github_releases as $github_release) {
						woodmanager_trace("Fetch Github - release => " . $github_release['tag_name']);
						if (!isset($github_release['tag_name'])) {
							woodmanager_trace_error("La release Github n'a pas de 'tag_name' - elle ne peut être traitée - package {$package->slug}");
						} else {
							/** Woodmanager supports only version format like x.x.x - see more on woodmanager_get_package_latest_release() function */
							if (!BD_Package_Release::is_valid_version_format($github_release['tag_name'])){
								woodmanager_trace_error("La release Github a un 'tag_name' d'un mauvais format (format attendu x.x.x où x est un nombre) - elle ne peut être traitée - package {$package->slug} tag_name {$github_release['tag_name']}");
							} else {
								if (!BD_Package_Release::version_exists($package, $github_release['tag_name'])) {
										
									/** release public info (available via API) */
									$info = array(
											'tag_name' => $github_release['tag_name'],
											'published_at' => $github_release['published_at'],
											'body' => $github_release['body'],
											'prerelease' => $github_release['prerelease'],
											'zipball_url' => '',
											'tarball_url' => '',
									);
										
									/** donwload zip and tar.gz */
									if (isset($github_release['zipball_url'])){
										$ball = woodmanager_download_package($package->slug, $github_release['zipball_url'], '.zip', $info['tag_name'], $github_release['prerelease'] === true);
										$info['zipball_url'] = $ball['url'];
									}
									/* if (isset($github_release['tarball_url'])){ // not used
										$ball = woodmanager_download_package($package->slug, $github_release['tarball_url'], '.tar.gz', $info['tag_name'], $github_release['prerelease'] === true);
										$info['tarball_url'] = $ball['url'];
									} */
							
									/** create release in our DB */
									BD_Package_Release::create_package_release(array(
											"id_package" => $package->id,
											"version" => $github_release['tag_name'],
											"type" => isset($github_release['prerelease']) && $github_release['prerelease'] === true ? 'prerelease' : 'release',
											"info" => @json_encode($info),
											"info_repository" => @json_encode($github_release),
									));
								} else {
									// it's not necessary to update existing releases
								}
							}
						}
					}
				} else {
					woodmanager_trace("Fetch Github - no release");
				}
			}

			/**
			 * IMPORTANT : on redonne la main pour les prochains update...
			 */
			$woodmanager_updating_releases = get_option('woodmanager_updating_releases', array());
			if (in_array($package->slug, $woodmanager_updating_releases)) {
				unset($woodmanager_updating_releases[array_search($package->slug, $woodmanager_updating_releases)]);
				update_option('woodmanager_updating_releases', $woodmanager_updating_releases);
			}
		}
	}
}

function woodmanager_fetch_github_releases($package, $url = null) {
	$releases = array();
	if (!is_object($package)) {
		return false;
	}
	$github_url = woodmanager_get_option('github-api-url');
	$github_user = woodmanager_get_option('github-user');
	$github_clientid = woodmanager_get_option('github-clientid');
	$github_clientsecret = woodmanager_get_option('github-clientsecret');
	if (empty($github_url) || empty($github_user)){
		return false;
	}
	if (!$url) {
		$url = $github_url."repos/".$github_user."/".$package->slug."/releases";
	}
	if (empty($url)) {
		return false;
	}
	woodmanager_trace("Fetch Github on {$url}");
	$wp_remote = wp_remote_get($url, array(
			/**
			 * NOTE
			* - since 02/2020 github doesn't support authentication parameters as query_string
			* - we must use 'Authorization' header parameter with clientid:clientsecret
			* - more information : https://developer.github.com/changes/2020-02-10-deprecating-auth-through-query-param/
			*/
			'headers' => array(
					'Authorization' => 'Basic ' . base64_encode($github_clientid.':'.$github_clientsecret)
			),
			'sslverify' => false
	));
	if (is_wp_error($wp_remote)) {
		return false;
	} else {
		$wp_remote_body = wp_remote_retrieve_body($wp_remote);
		if (!empty($wp_remote_body)) {
			$fetched_releases = @json_decode($wp_remote_body, true);
			if (!empty($fetched_releases)) {
				$releases = array_merge($releases, $fetched_releases);
			}
		}
		// if github set header Link - we have to follow this pagination
		$header_links = woodmanager_parse_http2_headerLinks(wp_remote_retrieve_header($wp_remote, 'Link'));
		if (!empty($header_links)) {
			foreach ($header_links as $header_link) {
				if ($header_link['rel'] === 'next') {
					$link_releases = woodmanager_fetch_github_releases($package, $header_link['url']);
					if ($link_releases) {
						$releases = array_merge($releases, $link_releases);
					}
				}
			}
		}
	}
	return $releases;
}

function woodmanager_parse_http2_headerLinks ($header_links) {
	$res = array();
	if (!empty($header_links)) {
		$header_links = explode(",", $header_links);
		foreach ($header_links as $header_link) {
			preg_match('/<(.*?)>; rel="(.*?)"/', trim($header_link), $matches);
			if (count($matches) > 2) {
				$res[] = array('url' => $matches[1], 'rel' => $matches[2]);
			}
		}
	}
	return $res;
}

if (!function_exists("woodmanager_get_package_latest_release")):
/**
 * retrieve package's latest release
* @param string $package
* @param string $format : ARRAY_A | OBJECT
* @return Ambigous <array, stdClass>
*/

/**
 * Retrieve release if available
 * @param unknown $package_slug
 * @param unknown $package_version format 'x.x.x' where x is numeric
 * @param string $include_prerelease
 * @return string|unknown
 */
function woodmanager_get_package_latest_release($package_slug, $package_version, $include_prerelease = false){

	$package = BD_Package::get_package_by_slug($package_slug);
	if (!is_object($package)){
		// EXIT
		return array("error" => __("No package found for slug '{$package->slug}'"));
	}
	
	/**
	 * IMPORTANT : le numero de version des releases de packages doivent au format x.x.x où x est numérique
	 */
	if (!BD_Package_Release::is_valid_version_format($package_version)) {
		// EXIT
		return array("error" => __("Package version '{$package_version}' format is invalid - '{$package->slug}'"));
	}
	
	// Fetch Github API to retrieve releases and update our DB (if necessary)
	woodmanager_update_package_releases($package);
	
	// Check if package has 'separate-major-releases' option activated
	$separate_major_releases = $package->separate_major_releases === 'true';
	
	// Retrieve releases wich version is greater than client package version
	$releases = null;
	list($v_major, $v_minor, $v_corrective) = explode(".", $package_version);
	if ($separate_major_releases) {
		$sql_where = "id_package = {$package->id}";
		$sql_where .= " AND CONCAT(";
		$sql_where .= "LPAD(SUBSTRING_INDEX(SUBSTRING_INDEX(version,'.',2),'.',-1),10,'0'), ";
		$sql_where .= "LPAD(SUBSTRING_INDEX(SUBSTRING_INDEX(version,'.',3),'.',-1),10,'0')";
		$sql_where .= ") > CONCAT(LPAD({$v_minor},10,'0'), LPAD({$v_corrective},10,'0'))";
		$sql_where .= " AND LPAD(SUBSTRING_INDEX(SUBSTRING_INDEX(version,'.',1),'.',-1),10,'0') = LPAD({$v_major},10,'0')";
		$releases = BD_Package_Release::get_package_releases($sql_where);
	} else {
		$sql_where = "id_package = {$package->id} ";
		$sql_where .= "AND CONCAT(";
		$sql_where .= "LPAD(SUBSTRING_INDEX(SUBSTRING_INDEX(version,'.',1),'.',-1),10,'0'),";
		$sql_where .= "LPAD(SUBSTRING_INDEX(SUBSTRING_INDEX(version,'.',2),'.',-1),10,'0'),";
		$sql_where .= "LPAD(SUBSTRING_INDEX(SUBSTRING_INDEX(version,'.',3),'.',-1),10,'0')";
		$sql_where .= ") > CONCAT(LPAD({$v_major},10,'0'), LPAD({$v_minor},10,'0'), LPAD({$v_corrective},10,'0'))";
		$releases = BD_Package_Release::get_package_releases($sql_where);
	}
	
	// Retrieve latest release from available releases
	$latest_release = null;
	if (!empty($releases)) {
		// Maybe more than one release may be retrieved, it depends on client version
		// So we keep the latest
		foreach ($releases as $release) {
			// exclude prerelease
			if (!$include_prerelease && $release->type !== 'release') {
				continue;
			}
			if (!is_object($latest_release)) {
				$latest_release = $release;
				continue;
			}
			if (version_compare($release->version, $latest_release->version) > 0) {
				$latest_release = $release;
			}
		}
	}
	
	if (!is_object($latest_release)) {
		// EXIT - IMPORTANT : return empty array - it's not an error, just there is no release at this time
		return array();
	}
	
	return json_decode($latest_release->info, true);
}
endif;

if (!function_exists("woodmanager_download_package")):
function woodmanager_download_package($package_slug, $ball_url, $ext, $version, $is_prerelease = false){
	$final_path = '';
	$final_url = '';
	if (!empty($package_slug) && !empty($ball_url) && !empty($ext)){
		
		$final_filename = $is_prerelease ? "prerelease-{$version}{$ext}" : "release-{$version}{$ext}";
		$final_path = WOODMANAGER_PLUGIN_REPOSITORY_PATH.$package_slug."/".$final_filename;
		$final_url = WOODMANAGER_PLUGIN_REPOSITORY_URL.$package_slug."/".$final_filename;
		
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
		$options  = array('http' => array(
				'method' => 		'GET',
				'user_agent' => 	$_SERVER['HTTP_USER_AGENT'],
		));
		woodmanager_trace("download github releases - {$ext} at {$ball_url}...");
		$context  = stream_context_create($options);
		file_put_contents($final_path, file_get_contents($ball_url, false, $context));
		woodmanager_trace("...downloaded");
	}
	return array('path' => $final_path, 'url' => $final_url);
}
endif;