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

define('WOODMANAGER_PACKAGE_PROPERTIES_NONCE_ACTION', 'WOODMANAGER_PACKAGE_PROPERTIES_NONCE_ACTION');
define('WOODMANAGER_PACKAGE_RELEASES_NONCE_ACTION', 'WOODMANAGER_PACKAGE_RELEASES_NONCE_ACTION');
define('WOODMANAGER_PACKAGE_INSTALLATIONS_NONCE_ACTION', 'WOODMANAGER_PACKAGE_INSTALLATIONS_NONCE_ACTION');
define('WOODMANAGER_PACKAGE_UPDATES_NONCE_ACTION', 'WOODMANAGER_PACKAGE_UPDATES_NONCE_ACTION');
define('WOODMANAGER_PACKAGE_WEBSITES_NONCE_ACTION', 'WOODMANAGER_PACKAGE_WEBSITES_NONCE_ACTION');

function woodmanager_add_package_post_type(){

	$labels = array(
			'name'               => __('Packages', WOODMANAGER_PLUGIN_TEXT_DOMAIN),
			'singular_name'      => __('Package', WOODMANAGER_PLUGIN_TEXT_DOMAIN),
			'add_new_item'       => __('Add package', WOODMANAGER_PLUGIN_TEXT_DOMAIN),
			'edit_item'          => __('Edit package', WOODMANAGER_PLUGIN_TEXT_DOMAIN),
			'new_item'           => __('New package', WOODMANAGER_PLUGIN_TEXT_DOMAIN),
			'all_items'          => __('Packages', WOODMANAGER_PLUGIN_TEXT_DOMAIN),
			'view_item'          => __('View packages', WOODMANAGER_PLUGIN_TEXT_DOMAIN),
			'search_items'       => __('Find packages', WOODMANAGER_PLUGIN_TEXT_DOMAIN),
			'not_found'          => __('No package found', WOODMANAGER_PLUGIN_TEXT_DOMAIN),
			'not_found_in_trash' => __('no package found in trash', WOODMANAGER_PLUGIN_TEXT_DOMAIN)
	);
	$args = array(
			'labels' => $labels,
			'exclude_from_search' => false,
			'public' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'capability_type' => 'post',
			'hierarchical' => false,
			'menu_icon' => 'dashicons-archive',
			'supports' => array('title', 'editor', 'thumbnail'),
			'rewrite' => array('slug' => _x('package', 'URL slug', WOODMANAGER_PLUGIN_TEXT_DOMAIN))
	);
	register_post_type('package', $args);

}
add_action( 'init', 'woodmanager_add_package_post_type' );

function woodmanager_package_admin_init() {
	add_meta_box('woodmanager-package-properties', __( 'Package properties', WOODMANAGER_PLUGIN_TEXT_DOMAIN), 'woodmanager_package_boxe_properties', 'package', 'normal', 'high');
	add_meta_box('woodmanager-package-websites', __( 'Package websites', WOODMANAGER_PLUGIN_TEXT_DOMAIN), 'woodmanager_package_boxe_websites', 'package', 'normal', 'high');
	add_meta_box('woodmanager-package-installations', __( 'Package installations', WOODMANAGER_PLUGIN_TEXT_DOMAIN), 'woodmanager_package_boxe_installations', 'package', 'normal', 'high');
	add_meta_box('woodmanager-package-updates', __( 'Package updates', WOODMANAGER_PLUGIN_TEXT_DOMAIN), 'woodmanager_package_boxe_updates', 'package', 'normal', 'high');
	add_meta_box('woodmanager-package-releases', __( 'Package releases', WOODMANAGER_PLUGIN_TEXT_DOMAIN), 'woodmanager_package_boxe_releases', 'package', 'normal', 'high');
}
add_action('admin_init', 'woodmanager_package_admin_init');

function woodmanager_package_boxe_properties($post) {
	include (WOODMANAGER_PLUGIN_PATH.'/'.WOODMANAGER_PLUGIN_POST_TYPES.'templates/template-post-type-package-properties.php');
}

function woodmanager_package_boxe_websites($post) {
	include (WOODMANAGER_PLUGIN_PATH.'/'.WOODMANAGER_PLUGIN_POST_TYPES.'templates/template-post-type-package-websites.php');
}

function woodmanager_package_boxe_installations($post) {
	include (WOODMANAGER_PLUGIN_PATH.'/'.WOODMANAGER_PLUGIN_POST_TYPES.'templates/template-post-type-package-installations.php');
}

function woodmanager_package_boxe_updates($post) {
	include (WOODMANAGER_PLUGIN_PATH.'/'.WOODMANAGER_PLUGIN_POST_TYPES.'templates/template-post-type-package-updates.php');
}

function woodmanager_package_boxe_releases($post) {
	include (WOODMANAGER_PLUGIN_PATH.'/'.WOODMANAGER_PLUGIN_POST_TYPES.'templates/template-post-type-package-releases.php');
}

function woodmanager_package_save_post($post_id){
	// verify if this is an auto save routine.
	// If it is our form has not been submitted, so we dont want to do anything
	if (defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return;
	// verify if this post-type is available and editable.
	if (isset($_POST['post_type']) && !empty($_POST['post_type']) && $_POST['post_type'] == 'package'){
		$post_type = $_POST['post_type'];
	}
	if (empty($post_type))
		return;
	if (!current_user_can('edit_post', $post_id ))
		return;
	if (!isset($_POST[WOODMANAGER_PACKAGE_PROPERTIES_NONCE_ACTION]) || !wp_verify_nonce($_POST[WOODMANAGER_PACKAGE_PROPERTIES_NONCE_ACTION], WOODMANAGER_PACKAGE_PROPERTIES_NONCE_ACTION))
		return;

	// manage bd_package
	if (function_exists('icl_object_id')) {
		global $sitepress;
		$trid = $sitepress->get_element_trid($post_id, 'post_package');
		$original_id = $sitepress->get_original_element_id_by_trid($trid);
	}
	if (!isset($original_id) || empty($original_id))
		$original_id = $post_id;

	// update bd_package propertie only for original post (if WPML activated)
	if ($original_id == $post_id){

		// slug - IMPORTANT : slug is the link between package post-type and bd-package - it's saved in post-type meta-data
		$meta_package_slug = (empty($_POST['meta_package_slug'])) ? '' : sanitize_text_field($_POST['meta_package_slug']);

		// update / create bd_package
		global $wpdb;
		if(!empty($meta_package_slug)){

			update_post_meta($original_id, 'meta_package_slug', $meta_package_slug);

			$id_bd_package = null;
			$data = array();
			$data['scope'] = BD_Package::$scope_public;
			if (isset($_POST['meta_package_scope']) && !empty($_POST['meta_package_scope'])) {
				$data['scope'] = $_POST['meta_package_scope'];
			}
			$data['separate_major_releases'] = 'false';
			if (isset($_POST['meta_package_separate_major_releases']) && !empty($_POST['meta_package_separate_major_releases']) && $_POST['meta_package_separate_major_releases'] == 'on') {
				$data['separate_major_releases'] = 'true';
			}

			$bd_package = BD_Package::get_package_by_slug($meta_package_slug);
			if (!empty($bd_package)){
				// existing package : update
				$res = BD_Package::update_package($bd_package, $data);
				$id_bd_package = $bd_package->id;
			}else{
				// no package found : create
				$data['slug'] = $meta_package_slug;
				$res = BD_Package::create_package($data);
				if (isset($res['id'])) {
					$id_bd_package = $res['id'];
				}
			}

			// KEYS
			if (!empty($id_bd_package)){
				if (isset($_POST[WOODMANAGER_PACKAGE_WEBSITES_NONCE_ACTION]) && wp_verify_nonce($_POST[WOODMANAGER_PACKAGE_WEBSITES_NONCE_ACTION], WOODMANAGER_PACKAGE_WEBSITES_NONCE_ACTION)){
					$existing_websites = BD_Package_Website::get_package_websites("id_package = ".$id_bd_package);
					$updated_website_ids = array();
					foreach ($_POST as $k => $v){
						if (woodmanager_starts_with($k, "package-website-item-id-")){
							$website_item_id = $v;
							$website = array( // defaults
									'key_activation' => '',
									'user' => null,
									'prerelease' => 'false'
							);
							if (isset($_POST['package-website-item-bd-id-'.$website_item_id]) && !empty($_POST['package-website-item-bd-id-'.$website_item_id]))
								$website["id"] = $_POST['package-website-item-bd-id-'.$website_item_id];
							if (isset($_POST['package-website-host-'.$website_item_id]) && !empty($_POST['package-website-host-'.$website_item_id]))
								$website["host"] = $_POST['package-website-host-'.$website_item_id];
							if (isset($_POST['package-website-activation-'.$website_item_id]) && !empty($_POST['package-website-activation-'.$website_item_id]))
								$website["key_activation"] = $_POST['package-website-activation-'.$website_item_id];
							if (isset($_POST['package-website-user-'.$website_item_id]) && !empty($_POST['package-website-user-'.$website_item_id]))
								$website["user"] = $_POST['package-website-user-'.$website_item_id];
							if (isset($_POST['package-website-prerelease-'.$website_item_id]) && !empty($_POST['package-website-prerelease-'.$website_item_id]) && $_POST['package-website-prerelease-'.$website_item_id] == 'on')
								$website["prerelease"] = 'true';

							if (isset($website["host"]) && !empty($website["host"])){
								
								$website['host'] = rtrim($website['host'], '/');// important : trim last slash
								
								if (isset($website["id"]) && !empty($website["id"])){
									// update
									$res = BD_Package_Website::update_package_website($website["id"], array("id_package" => $id_bd_package, "id_user" => $website["user"], "host" => $website["host"], "key_activation" => $website["key_activation"], "prerelease" => $website['prerelease']));
									if (!isset($res['error']))
										$updated_website_ids[] = intval($website["id"]);
								}else{
									// create
									$res = BD_Package_Website::create_package_website(array("id_package" => $id_bd_package, "id_user" => $website["user"], "host" => $website["host"], "key_activation" => $website["key_activation"], "prerelease" => $website['prerelease']));
								}
							}
						}
					}
					// clean deleted website
					foreach ($existing_websites as $existing_website){
						if (!in_array($existing_website->id, $updated_website_ids)) {
							BD_Package_Website::delete_package_website($existing_website->id);
						}
					}
				}
			}else{
				woodmanager_trace_error("post-type-package - ERROR : no id retrieve");
			}
		}else{
			delete_post_meta($original_id, 'meta_package_slug');
		}
	}
}
add_action('save_post', 'woodmanager_package_save_post');

function woodmanager_get_packages($meta_args = array(), $args = array()){
	$posts = array();
	// parse args array and set default values
	$args['post_type'] = 'package';
	if (empty($args['orderby']))
		$args['orderby'] = "title";
	if (empty($args['order']))
		$args['order'] = 'ASC';
	if (empty($args['numberposts']))
		$args['numberposts'] = -1;
	if (empty($args['suppress_filters']))
		$args['suppress_filters'] = FALSE; // pour n'avoir que les items de la langue courante (compatibilité WPML)

	$posts = array_merge($posts, get_posts($args));
	$posts_fin = array();

	// meta filters
	if ($meta_args && count($meta_args)>0){
		foreach ($posts as $post){
			$add = true;
			foreach ($meta_args as $meta_key => $meta_value){
				if (get_post_meta($post->ID, $meta_key, true) != $meta_value){
					$add = false;
				}
			}
			if ($add == true){
				array_push($posts_fin, $post);
			}
		}
	}else{
		$posts_fin = $posts;
	}

	return $posts_fin;
}
