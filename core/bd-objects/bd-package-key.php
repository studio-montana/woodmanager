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
 * BD_Package_Key DEFINITION
*/
if(!class_exists('BD_Package_Key')){

	class BD_Package_Key{

		public function __construct(){

		}

		public static function get_package_key($id){
			global $wpdb;
			if ($id){
				$results = $wpdb->get_results('SELECT * FROM '.WoodManager_BD::get_package_key_table_name($wpdb).' WHERE id='.$id, OBJECT);
				if (!empty($results)){
					return $results[0];
				}
			}
			return null;
		}

		public static function get_package_keys($where_clause = '1=1'){
			global $wpdb;
			return $wpdb->get_results('SELECT * FROM '.WoodManager_BD::get_package_key_table_name($wpdb).' WHERE '.$where_clause, OBJECT);
		}

		public static function create_package_key($data = array()){
			global $wpdb;
			$res = array();

			$date = current_time('mysql');

			$data['id'] = NULL;
			$data['date'] = $date;
			$data['date_modif'] = $date;

			$rows_affected = $wpdb->insert(WoodManager_BD::get_package_key_table_name($wpdb), $data);
			if ($rows_affected == false){
				$res['error'] = __("Cannot create package account key.", WOODMANAGER_PLUGIN_TEXT_DOMAIN);
				woodmanager_trace("BD_Package - ERROR : Cannot create package account key - req : ".$wpdb->last_query." - ".$wpdb->last_error);
			}else{
				dbDelta($rows_affected);
				$res['id'] = $wpdb->insert_id;
				do_action('woodmanager_on_package_key_created', $wpdb->insert_id);
			}
			return $res;
		}

		public static function update_package_key($package_key, $data = array()){
			global $wpdb;
			$res = array();

			if (is_object($package_key))
				$id = $package_key->id;
			else
				$id = $package_key;

			$old_obj = self::get_package_key($id);

			$data['date_modif'] = current_time('mysql');

			if ($wpdb->update(WoodManager_BD::get_package_key_table_name($wpdb), $data, array("id" => $id)) == false){
				$res['error'] = __("Cannot update package account key [$id].", WOODMANAGER_PLUGIN_TEXT_DOMAIN);
				woodmanager_trace("BD_Package - ERROR : Cannot update package account key [$id] - req : ".$wpdb->last_query." - ".$wpdb->last_error);
			}else{
				do_action('woodmanager_on_package_key_updated', $id, $old_obj);
			}
			return $res;
		}

		public static function delete_package_key($package_key){
			global $wpdb;
			$res = array();

			if (is_object($package_key))
				$id = $package_key->id;
			else
				$id = $package_key;

			$deleted_package = self::get_package_key($id);

			if ($wpdb->delete(WoodManager_BD::get_package_key_table_name($wpdb), array("id" => $id)) == false){
				$res['error'] = __("Cannot delete package account key [$id].", WOODMANAGER_PLUGIN_TEXT_DOMAIN);
				woodmanager_trace("BD_Package - ERROR : Cannot delete package account key [$id] - req : ".$wpdb->last_query." - ".$wpdb->last_error);
			}else{
				do_action('woodmanager_on_package_key_deleted', $id, $deleted_package);
			}
			return $res;
		}

		public static function delete_package_keys($where = array()){
			global $wpdb;
			$res = array();

			if (!empty($where)){
				$package_keys = self::get_package_keys($where);
				if (!empty($package_keys)){
					foreach ($package_keys as $package_key){
						$res[] = self::delete_package($package_key);
					}
				}
			}
			return $res;
		}
	}
}