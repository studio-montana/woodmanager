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
 * BD_Package_Website DEFINITION
*/
if(!class_exists('BD_Package_Website')){

	class BD_Package_Website{

		public function __construct(){

		}

		public static function get_package_website($id){
			global $wpdb;
			if ($id){
				$results = $wpdb->get_results('SELECT * FROM '.WoodManager_BD::get_package_website_table_name($wpdb).' WHERE id='.$id, OBJECT);
				if (!empty($results)){
					return $results[0];
				}
			}
			return null;
		}

		public static function get_package_websites($where_clause = '1=1'){
			global $wpdb;
			return $wpdb->get_results('SELECT * FROM '.WoodManager_BD::get_package_website_table_name($wpdb).' WHERE '.$where_clause, OBJECT);
		}

		public static function create_package_website($data = array()){
			global $wpdb;
			$res = array();

			$date = current_time('mysql');

			$data['id'] = NULL;
			$data['date'] = $date;
			$data['date_modif'] = $date;

			$rows_affected = $wpdb->insert(WoodManager_BD::get_package_website_table_name($wpdb), $data);
			if ($rows_affected == false){
				$res['error'] = __("Cannot create package website.", WOODMANAGER_PLUGIN_TEXT_DOMAIN);
				woodmanager_trace("BD_Package - ERROR : Cannot create package website - req : ".$wpdb->last_query." - ".$wpdb->last_error);
			}else{
				dbDelta($rows_affected);
				$res['id'] = $wpdb->insert_id;
				do_action('woodmanager_on_package_website_created', $wpdb->insert_id);
			}
			return $res;
		}

		public static function update_package_website($package_website, $data = array()){
			global $wpdb;
			$res = array();

			if (is_object($package_website))
				$id = $package_website->id;
			else
				$id = $package_website;

			$old_obj = self::get_package_website($id);

			$data['date_modif'] = current_time('mysql');

			if ($wpdb->update(WoodManager_BD::get_package_website_table_name($wpdb), $data, array("id" => $id)) == false){
				$res['error'] = __("Cannot update package website [$id].", WOODMANAGER_PLUGIN_TEXT_DOMAIN);
				woodmanager_trace("BD_Package - ERROR : Cannot update package website [$id] - req : ".$wpdb->last_query." - ".$wpdb->last_error);
			}else{
				do_action('woodmanager_on_package_website_updated', $id, $old_obj);
			}
			return $res;
		}

		public static function delete_package_website($package_website){
			global $wpdb;
			$res = array();

			if (is_object($package_website))
				$id = $package_website->id;
			else
				$id = $package_website;

			$deleted_package = self::get_package_website($id);

			if ($wpdb->delete(WoodManager_BD::get_package_website_table_name($wpdb), array("id" => $id)) == false){
				$res['error'] = __("Cannot delete package website [$id].", WOODMANAGER_PLUGIN_TEXT_DOMAIN);
				woodmanager_trace("BD_Package - ERROR : Cannot delete package website [$id] - req : ".$wpdb->last_query." - ".$wpdb->last_error);
			}else{
				do_action('woodmanager_on_package_website_deleted', $id, $deleted_package);
			}
			return $res;
		}

		public static function delete_package_websites($where = array()){
			global $wpdb;
			$res = array();

			if (!empty($where)){
				$package_websites = self::get_package_websites($where);
				if (!empty($package_websites)){
					foreach ($package_websites as $package_website){
						$res[] = self::delete_package($package_website);
					}
				}
			}
			return $res;
		}
	}
}