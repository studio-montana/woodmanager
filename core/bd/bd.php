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

require_once(ABSPATH.'wp-admin/includes/upgrade.php');

class WoodManager_BD{

	public static $woodmanager_db_version = '1.0';

	/**
	 * Construct the object
	 */
	public function __construct(){

	}

	/**
	 * Install WOODMANAGER BDD tables
	 */
	public static function install(){
		self::create_table_package();
		self::create_table_package_installation();
		self::create_table_package_update();
		self::create_table_package_profile();
		self::create_table_package_key();
		update_option("woodmanager_db_version", self::$woodmanager_db_version);
	}

	/**
	 * Uninstall WOODMANAGER BDD tables
	 */
	public static function uninstall(){
		if (defined('WOODMANAGER_UNINSTALL_DROPING_DATA') && WOODMANAGER_UNINSTALL_DROPING_DATA == true){
			self::drop_table_package();
			self::drop_table_package_installation();
			self::drop_table_package_update();
			self::drop_table_package_profile();
			self::drop_table_package_key();
			update_option("woodmanager_db_version", "");
		}
	}

	public static function get_package_table_name($wpdb){
		return $wpdb->prefix . WOODMANAGER_PLUGIN_NAME."_package";
	}

	public static function get_package_installation_table_name($wpdb){
		return $wpdb->prefix . WOODMANAGER_PLUGIN_NAME."_package_installation";
	}

	public static function get_package_update_table_name($wpdb){
		return $wpdb->prefix . WOODMANAGER_PLUGIN_NAME."_package_update";
	}

	public static function get_package_profile_table_name($wpdb){
		return $wpdb->prefix . WOODMANAGER_PLUGIN_NAME."_package_profile";
	}

	public static function get_package_key_table_name($wpdb){
		return $wpdb->prefix . WOODMANAGER_PLUGIN_NAME."_package_key";
	}

	/**
	 * create woodmanager_package sql data table
	 */
	private static function create_table_package(){
		global $wpdb;

		// name
		$table_name = self::get_package_table_name($wpdb);

		// charset collate
		$charset_collate = '';
		if (!empty($wpdb->charset))
			$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
		if (!empty($wpdb->collate))
			$charset_collate .= " COLLATE {$wpdb->collate}";

		// sql create
		$sql = "CREATE TABLE $table_name (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		slug varchar(255) NULL,
		free varchar(20) NULL,
		package_release text NULL,
		package_release_github text NULL,
		package_release_date datetime NULL,
		date datetime NULL,
		date_modif datetime NULL,
		UNIQUE KEY id (id)
		) $charset_collate;";

		// table creation
		dbDelta($sql);
	}

	/**
	 * create woodmanager_package_installation sql data table
	 */
	private static function create_table_package_installation(){
		global $wpdb;

		// name
		$table_name = self::get_package_installation_table_name($wpdb);

		// charset collate
		$charset_collate = '';
		if (!empty($wpdb->charset))
			$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
		if (!empty($wpdb->collate))
			$charset_collate .= " COLLATE {$wpdb->collate}";

		// sql create
		$sql = "CREATE TABLE $table_name (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		id_package bigint(20) NULL,
		host varchar(255) NULL,
		version varchar(25) NULL,
		date datetime NULL,
		date_modif datetime NULL,
		UNIQUE KEY id (id)
		) $charset_collate;";

		// table creation
		dbDelta($sql);
	}

	/**
	 * create woodmanager_package_update sql data table
	 */
	private static function create_table_package_update(){
		global $wpdb;

		// name
		$table_name = self::get_package_update_table_name($wpdb);

		// charset collate
		$charset_collate = '';
		if (!empty($wpdb->charset))
			$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
		if (!empty($wpdb->collate))
			$charset_collate .= " COLLATE {$wpdb->collate}";

		// sql create
		$sql = "CREATE TABLE $table_name (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		id_package bigint(20) NULL,
		host varchar(255) NULL,
		version varchar(25) NULL,
		date datetime NULL,
		date_modif datetime NULL,
		UNIQUE KEY id (id)
		) $charset_collate;";

		// table creation
		dbDelta($sql);
	}

	/**
	 * create woodmanager_package_profile sql data table
	 */
	private static function create_table_package_profile(){
		global $wpdb;

		// name
		$table_name = self::get_package_profile_table_name($wpdb);

		// charset collate
		$charset_collate = '';
		if (!empty($wpdb->charset))
			$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
		if (!empty($wpdb->collate))
			$charset_collate .= " COLLATE {$wpdb->collate}";

		// sql create
		$sql = "CREATE TABLE $table_name (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		id_package bigint(20) NULL,
		id_user bigint(20) NULL,
		profile varchar(255) NULL,
		date datetime NULL,
		date_modif datetime NULL,
		UNIQUE KEY id (id)
		) $charset_collate;";

		// table creation
		dbDelta($sql);
	}

	/**
	 * create woodmanager_package_key sql data table
	 */
	private static function create_table_package_key(){
		global $wpdb;

		// name
		$table_name = self::get_package_key_table_name($wpdb);

		// charset collate
		$charset_collate = '';
		if (!empty($wpdb->charset))
			$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
		if (!empty($wpdb->collate))
			$charset_collate .= " COLLATE {$wpdb->collate}";

		// sql create
		$sql = "CREATE TABLE $table_name (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		id_package bigint(20) NULL,
		id_user bigint(20) NULL,
		key_activation varchar(255) NULL,
		host varchar(255) NULL,
		date datetime NULL,
		date_modif datetime NULL,
		UNIQUE KEY id (id)
		) $charset_collate;";

		// table creation
		dbDelta($sql);
	}

	/**
	 * drop woodmanager_package sql data table
	 */
	private static function drop_table_package(){
		global $wpdb;
		$table_name = self::get_package_table_name($wpdb);
		$sql = "DROP TABLE ". $table_name;
		$wpdb->query($sql);
	}

	/**
	 * drop woodmanager_package_installation sql data table
	 */
	private static function drop_table_package_installation(){
		global $wpdb;
		$table_name = self::get_package_installation_table_name($wpdb);
		$sql = "DROP TABLE ". $table_name;
		$wpdb->query($sql);
	}

	/**
	 * drop woodmanager_package_update sql data table
	 */
	private static function drop_table_package_update(){
		global $wpdb;
		$table_name = self::get_package_update_table_name($wpdb);
		$sql = "DROP TABLE ". $table_name;
		$wpdb->query($sql);
	}

	/**
	 * drop woodmanager_package_profile sql data table
	 */
	private static function drop_table_package_profile(){
		global $wpdb;
		$table_name = self::get_package_profile_table_name($wpdb);
		$sql = "DROP TABLE ". $table_name;
		$wpdb->query($sql);
	}

	/**
	 * drop woodmanager_package_key sql data table
	 */
	private static function drop_table_package_key(){
		global $wpdb;
		$table_name = self::get_package_key_table_name($wpdb);
		$sql = "DROP TABLE ". $table_name;
		$wpdb->query($sql);
	}
}
