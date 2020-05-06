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
 * BD_Package DEFINITION
*/
if(!class_exists('BD_Package')){

	class BD_Package{
		
		public static $scope_public = "__public";
		public static $scope_private = "__private";

		public function __construct(){

		}

		/**
		 * Check if package's scope is public (including dependencies)
		 * @param unknown $package
		 * @return boolean
		 */
		public static function is_scope_public($package, $processed_package_ids = array()) {
			if (!is_object($package)) {
				if (is_numeric($package)) {
					$package = self::get_package($package);
				} else {
					$package = self::get_package_by_slug($package);
				}
			}
			if (is_object($package) && !in_array($package->id, $processed_package_ids)) {
				$processed_package_ids[] = $package->id;
				if ($package->scope === self::$scope_public) {
					return true;
				} else if ($package->scope === self::$scope_private) {
					return false;
				} else if (!empty($package->scope)) {
					return self::is_scope_public($package->scope, $processed_package_ids);
				}
			}
			return false;
		}

		/**
		 * Check if package's scope is a denpendency
		 * @param unknown $package
		 * @return boolean
		 */
		public static function is_scope_dependency($package) {
			if (!is_object($package)) {
				if (is_numeric($package)) {
					$package = BD_Package::get_package($package);
				} else {
					$package = BD_Package::get_package_by_slug($package);
				}
			}
			return is_object($package) && !empty($package->scope) && $package->scope !== BD_Package::$scope_private && $package->scope !== BD_Package::$scope_public;
		}
		
		public static function get_package($id){
			global $wpdb;
			if ($id){
				$results = $wpdb->get_results('SELECT * FROM '.WoodManager_BD::get_package_table_name($wpdb).' WHERE id='.$id, OBJECT);
				if (!empty($results)){
					return $results[0];
				}
			}
			return null;
		}

		public static function get_packages($where_clause = '1=1'){
			global $wpdb;
			return $wpdb->get_results('SELECT * FROM '.WoodManager_BD::get_package_table_name($wpdb).' WHERE '.$where_clause, OBJECT);
		}

		public static function get_package_by_slug($slug){
			global $wpdb;
			if (!empty($slug)){
				$results = self::get_packages("slug like '".$wpdb->esc_like($slug)."'");
				if (count($results) > 1)
					woodmanager_trace("BD_Package - ERROR : more than one package for slug '".$slug."'");
				if (!empty($results)){
					return $results[0];
				}
			}
			return null;
		}

		public static function create_package($data = array()){
			global $wpdb;
			$res = array();

			$date = current_time('mysql');

			$data['id'] = NULL;
			$data['date'] = $date;
			$data['date_modif'] = $date;

			$rows_affected = $wpdb->insert(WoodManager_BD::get_package_table_name($wpdb), $data);
			if ($rows_affected == false){
				$res['error'] = __("Cannot create package.", WOODMANAGER_PLUGIN_TEXT_DOMAIN);
				woodmanager_trace("BD_Package - ERROR : Cannot create package - req : ".$wpdb->last_query." - ".$wpdb->last_error);
			}else{
				dbDelta($rows_affected);
				$res['id'] = $wpdb->insert_id;
				do_action('woodmanager_on_package_created', $wpdb->insert_id);
			}
			return $res;
		}

		public static function update_package($package, $data = array()){
			global $wpdb;
			$res = array();

			if (is_object($package))
				$id = $package->id;
			else
				$id = $package;

			$old_obj = self::get_package($id);

			$data['date_modif'] = current_time('mysql');

			if ($wpdb->update(WoodManager_BD::get_package_table_name($wpdb), $data, array("id" => $id)) == false){
				$res['error'] = __("Cannot update package [$id].", WOODMANAGER_PLUGIN_TEXT_DOMAIN);
				woodmanager_trace("BD_Package - ERROR : Cannot update package [$id] - req : ".$wpdb->last_query." - ".$wpdb->last_error);
			}else{
				do_action('woodmanager_on_package_updated', $id, $old_obj);
			}
			return $res;
		}

		public static function delete_package($package){
			global $wpdb;
			$res = array();

			if (is_object($package))
				$id = $package->id;
			else
				$id = $package;

			$deleted_package = self::get_package($id);

			if ($wpdb->delete(WoodManager_BD::get_package_table_name($wpdb), array("id" => $id)) == false){
				$res['error'] = __("Cannot delete package [$id].", WOODMANAGER_PLUGIN_TEXT_DOMAIN);
				woodmanager_trace("BD_Package - ERROR : Cannot delete package [$id] - req : ".$wpdb->last_query." - ".$wpdb->last_error);
			}else{
				do_action('woodmanager_on_package_deleted', $id, $deleted_package);
			}
			return $res;
		}

		public static function delete_packages($where = array()){
			global $wpdb;
			$res = array();

			if (!empty($where)){
				$packages = self::get_packages($where);
				if (!empty($packages)){
					foreach ($packages as $package){
						$res[] = self::delete_package($package);
					}
				}
			}
			return $res;
		}

		/**
		 * delete unlinked packages
		 * @return multitype:multitype:Ambigous <string, mixed>
		 */
		public static function clean_packages($exclude = array()){
			global $wpdb;
			$res = array();
			// DANGEROUS method... best solution is to delete bd-packages manualy (in phpMyAdmin for now)
			// otherwize, to clean perfectly we have to test all post-type package included translations OR just original items when WPML is active
			// the other solution is to manage bd-package from admin

			// 			$packages = self::get_packages();
			// 			if (!empty($packages)){
			// 				foreach ($packages as $package){
			// 					if (!empty($package->slug) && !in_array($package->slug, $exclude)){
			// 						$linked_packages = woodmanager_get_packages(array('meta_package_slug' => $package->slug), array('suppress_filters' => TRUE));
			// 						if (empty($linked_packages)){
			// 							woodmanager_trace("clean_packages - slug : ".$package->slug);
			// 							$res[] = self::delete_package($package);
			// 						}
			// 					}
			// 				}
			// 			}
			return $res;
		}
	}
}