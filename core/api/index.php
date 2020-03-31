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

require_once (WOODMANAGER_PLUGIN_PATH.'/'.WOODMANAGER_PLUGIN_API_FOLDER.'response.php');

class WoodAPI {
	
	public static $api_slug = 'woodapi';

	public function __construct() {
		// Add query vars.
		add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );
		
		// Register API endpoints.
		add_action( 'init', array( $this, 'add_endpoint' ), 0 );
		
		// Handle api endpoint requests.
		add_action( 'parse_request', array( $this, 'handle_api_requests' ), 0 );
	}
	
	/**
	 * Retrieve API endpoint URL - HTTPS only
	 * @return string
	 */
	public function get_endpoint($end_slash = true){
		return site_url(self::$api_slug) . ($end_slash ? '/' : '');
	}
	
	public function add_query_vars($vars){
		$vars[] = self::$api_slug;
		return $vars;
	}
	
	public function add_endpoint(){
		add_rewrite_endpoint(self::$api_slug, EP_ROOT);
		// flush_rewrite_rules();
	}
	
	public function handle_api_requests(){
		global $wp;
		if (!empty($_GET[self::$api_slug])) { // WPCS: input var okay, CSRF ok.
			$wp->query_vars[self::$api_slug] = sanitize_key(wp_unslash($_GET[self::$api_slug])); // WPCS: input var okay, CSRF ok.
		}
		
		// inside-api endpoint requests.
		if (isset($wp->query_vars[self::$api_slug])) {
			// get reponse & stop propagation
			die(WoodAPIResponse::get($wp->query_vars[self::$api_slug]));
		}
	}
}
