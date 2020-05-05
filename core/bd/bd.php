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

	/**
	 * Construct the object
	 */
	public function __construct(){

	}

	/**
	 * Install WOODMANAGER BDD tables
	 */
	public static function install(){
		global $wpdb;
		
		self::create_table_package();
		self::create_table_package_installation();
		self::create_table_package_update();
		self::create_table_package_profile();
		self::create_table_package_key();
		
		
		// Upgrades
		$current_version = get_option ("woodmanager_db_version", "0.0");
		
		/**
		 * Version 1.1
		 */
		$version_upgrade = "1.1";
		if (version_compare ( $current_version, $version_upgrade ) < 0) {
			
			self::create_table_package_release();

			$wpdb->query("ALTER TABLE `".self::get_package_table_name($wpdb)."` CHANGE `package_release_date` `last_update` datetime;");
			$wpdb->query("ALTER TABLE `".self::get_package_table_name($wpdb)."` ADD COLUMN `separate_major_releases` varchar(20) NULL AFTER `last_update`;");
			$wpdb->query("ALTER TABLE `".self::get_package_table_name($wpdb)."` DROP `package_release`;");
			$wpdb->query("ALTER TABLE `".self::get_package_table_name($wpdb)."` DROP `package_release_github`;");
			$wpdb->query("ALTER TABLE `".self::get_package_table_name($wpdb)."` DROP `package_release_date`;");
			
			update_option("woodmanager_db_version", $version_upgrade);
		}
	}

	/**
	 * Uninstall WOODMANAGER BDD tables
	 */
	public static function uninstall(){}

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

	public static function get_package_release_table_name($wpdb){
		return $wpdb->prefix . WOODMANAGER_PLUGIN_NAME."_package_release";
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
		$sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
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
		$sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
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
		$sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
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
		$sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
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
		$sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
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
	 * create woodmanager_package_release sql data table
	 */
	private static function create_table_package_release(){
		global $wpdb;

		// name
		$table_name = self::get_package_release_table_name($wpdb);

		// charset collate
		$charset_collate = '';
		if (!empty($wpdb->charset))
			$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
		if (!empty($wpdb->collate))
			$charset_collate .= " COLLATE {$wpdb->collate}";
			
		// sql create
		$sql = "CREATE TABLE IF NOT EXISTS `{$table_name}` ( ";
		$sql .= "id bigint(20) NOT NULL AUTO_INCREMENT,";
		$sql .= "id_package bigint(20) NULL,"; // package (FOREIGN KEY)
		$sql .= "version varchar(255) NULL,"; // format x.x.x
		$sql .= "type varchar(255) NULL,"; // release | prerelease
		$sql .= "info text NULL,"; // public information for woodmanager API - json
		$sql .= "info_repository text NULL,"; // private information from Github repository - json
		$sql .= "date datetime NULL,";
		$sql .= "date_modif datetime NULL,";
		$sql .= "UNIQUE KEY id (id) ) {$charset_collate};";

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

	/**
	 * drop woodmanager_package_release sql data table
	 */
	private static function drop_table_package_release(){
		global $wpdb;
		$table_name = self::get_package_release_table_name($wpdb);
		$sql = "DROP TABLE ". $table_name;
		$wpdb->query($sql);
	}
}
