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
?>

<input type="hidden" name="<?php echo WOODMANAGER_PACKAGE_RELEASES_NONCE_ACTION; ?>" value="<?php echo wp_create_nonce(WOODMANAGER_PACKAGE_RELEASES_NONCE_ACTION);?>" />

<?php 
$original_id = woodmanager_get_original_post($post->ID);
$can_update = true;
if (!empty($original_id) && $original_id != $post->ID)
	$can_update = false;

$package_slug = @get_post_meta($original_id, 'meta_package_slug', true);

if (!$can_update){
	?>
	<p class="">
		<?php _e("You can not view package releases, please edit original item of this translation.", WOODMANAGER_PLUGIN_TEXT_DOMAIN); ?>
	</p>
	<p class="">
		<a href="<?php echo get_edit_post_link($original_id); ?>" class="button"><?php _e("Edit original item", WOODMANAGER_PLUGIN_TEXT_DOMAIN); ?></a>	
	</p>
	<?php
}else{ 
	$has_package = false;
	$bd_package = null;
	$package_slug = @get_post_meta($original_id, 'meta_package_slug', true);
	$installations = array();
	if (!empty($package_slug)){
		$bd_package = BD_Package::get_package_by_slug($package_slug);
		if(!empty($bd_package)){
			$has_package = true;
		}
	}
	if ($has_package){
		$date = $bd_package->last_update;
		$date_s = '';
		if (!empty($date)){
			$date = new DateTime($date);
			$date_s = $date->format("Y-m-d H:i:s");
		}
		?>
		<h4><?php _e("Last Github fetch", WOODMANAGER_PLUGIN_TEXT_DOMAIN); ?> : <em><?php
		if (!empty($date_s)){
			echo $date_s;
		}else{
			_e("no fetch", WOODMANAGER_PLUGIN_TEXT_DOMAIN);
		} ?>
		</em></h4>
		<table>
			<tr>
				<th><?php _e("version", WOODMANAGER_PLUGIN_TEXT_DOMAIN); ?></th>
				<th><?php _e("type", WOODMANAGER_PLUGIN_TEXT_DOMAIN); ?></th>
			</tr>
			<?php	
			$releases = BD_Package_Release::get_package_releases("id_package = {$bd_package->id}");
			
			if (!empty($releases)) {
				usort($releases, 'woodmanager_package_release_version_compare');
				foreach ($releases as $release) {
					?>
					<tr>
						<td><?php echo $release->version; ?></td>
						<td><?php echo $release->type; ?></td>
					</tr>
					<?php
				}
			} else {
				?><tr><td colspan="3"><?php _e("no release for this package", WOODMANAGER_PLUGIN_TEXT_DOMAIN); ?></td></tr><?php
			} ?>
		</table>
	<?php }
} ?>
