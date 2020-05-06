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
 * BD_Package_Release DEFINITION
*/
if(!class_exists('BD_Package_Release')){

	class BD_Package_Release{

		public function __construct(){

		}
		
		public static function version_exists($package, $version) {
			global $wpdb;
			if (is_object($package)){
				$package = $package->id;
			} else if (!is_numeric($package)){
				$package = BD_Package::get_package_by_slug($package);
				$package = $package ? $package->id : null;
			}
			if ($package) {
				$sql_query = 'SELECT * FROM '.WoodManager_BD::get_package_release_table_name($wpdb).' AS pr INNER JOIN '.WoodManager_BD::get_package_table_name($wpdb).' AS p ON pr.id_package = p.id WHERE p.id = '.$package.' AND pr.version LIKE \''.$version.'\'';
				// woodmanager_trace("BD_Package_Release - version_exists {$version} sql_query : {$sql_query}");
				$results = $wpdb->get_results($sql_query, OBJECT);
				return !empty($results);
			}
			return false;
		}

		public static function get_package_release($id){
			global $wpdb;
			if ($id){
				$results = $wpdb->get_results('SELECT * FROM '.WoodManager_BD::get_package_release_table_name($wpdb).' WHERE id='.$id, OBJECT);
				if (!empty($results)){
					return $results[0];
				}
			}
			return null;
		}

		public static function get_package_releases($where_clause = '1=1'){
			global $wpdb;
			$sql_query = 'SELECT * FROM '.WoodManager_BD::get_package_release_table_name($wpdb).' WHERE '.$where_clause;
			// woodmanager_trace("BD_Package_Release - get_package_releases sql_query : {$sql_query}");
			return $wpdb->get_results($sql_query, OBJECT);
		}

		public static function create_package_release($data = array()){
			global $wpdb;
			$res = array();

			$date = current_time('mysql');

			$data['id'] = NULL;
			$data['date'] = $date;
			$data['date_modif'] = $date;

			$rows_affected = $wpdb->insert(WoodManager_BD::get_package_release_table_name($wpdb), $data);
			if ($rows_affected == false){
				$res['error'] = __("Cannot create package release.", WOODMANAGER_PLUGIN_TEXT_DOMAIN);
				woodmanager_trace("BD_Package - ERROR : Cannot create package release - req : ".$wpdb->last_query." - ".$wpdb->last_error);
			}else{
				dbDelta($rows_affected);
				$res['id'] = $wpdb->insert_id;
				do_action('woodmanager_on_package_release_created', $wpdb->insert_id);
			}
			return $res;
		}

		public static function update_package_release($package_release, $data = array()){
			global $wpdb;
			$res = array();

			if (is_object($package_release))
				$id = $package_release->id;
			else
				$id = $package_release;

			$old_obj = self::get_package_release($id);

			$data['date_modif'] = current_time('mysql');

			if ($wpdb->update(WoodManager_BD::get_package_release_table_name($wpdb), $data, array("id" => $id)) == false){
				$res['error'] = __("Cannot update package release [$id].", WOODMANAGER_PLUGIN_TEXT_DOMAIN);
				woodmanager_trace("BD_Package - ERROR : Cannot update package release [$id] - req : ".$wpdb->last_query." - ".$wpdb->last_error);
			}else{
				do_action('woodmanager_on_package_release_updated', $id, $old_obj);
			}
			return $res;
		}

		public static function delete_package_release($package_release){
			global $wpdb;
			$res = array();

			if (is_object($package_release))
				$id = $package_release->id;
			else
				$id = $package_release;

			$deleted_package = self::get_package_release($id);

			if ($wpdb->delete(WoodManager_BD::get_package_release_table_name($wpdb), array("id" => $id)) == false){
				$res['error'] = __("Cannot delete package release [$id].", WOODMANAGER_PLUGIN_TEXT_DOMAIN);
				woodmanager_trace("BD_Package - ERROR : Cannot delete package release [$id] - req : ".$wpdb->last_query." - ".$wpdb->last_error);
			}else{
				do_action('woodmanager_on_package_release_deleted', $id, $deleted_package);
			}
			return $res;
		}

		public static function delete_package_releases($where = array()){
			global $wpdb;
			$res = array();

			if (!empty($where)){
				$package_releases = self::get_package_releases($where);
				if (!empty($package_releases)){
					foreach ($package_releases as $package_release){
						$res[] = self::delete_package($package_release);
					}
				}
			}
			return $res;
		}
	}
}