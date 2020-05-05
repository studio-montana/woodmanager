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

<input type="hidden" name="<?php echo WOODMANAGER_PACKAGE_PROPERTIES_NONCE_ACTION; ?>" value="<?php echo wp_create_nonce(WOODMANAGER_PACKAGE_PROPERTIES_NONCE_ACTION);?>" />

<?php 
$original_id = woodmanager_get_original_post($post->ID);
$can_update = true;
if (!empty($original_id) && $original_id != $post->ID)
	$can_update = false;

if (!$can_update){
	?>
	<p class="">
		<?php _e("You can not modify package properties, please edit original item of this translation.", WOODMANAGER_PLUGIN_TEXT_DOMAIN); ?>
	</p>
	<p class="">
		<a href="<?php echo get_edit_post_link($original_id); ?>" class="button"><?php _e("Edit original item", WOODMANAGER_PLUGIN_TEXT_DOMAIN); ?></a>	
	</p>
	<?php
}else{ ?>
	<table>
		<tr>
			<?php $package_slug = @get_post_meta($original_id, 'meta_package_slug', true); ?>
			<td><label for="meta_package_slug"><?php _e("Package slug"); ?></label></td>
			<td>
				<input name="meta_package_slug" id="meta_package_slug" type="text" placeholder="my-package" value="<?php echo $package_slug; ?>" />
			</td>
		</tr>
		<?php $bd_package = null;
		if (!empty($package_slug)){
			$bd_package = BD_Package::get_package_by_slug($package_slug);
		} ?>
		<tr>
			<?php $free = is_object($bd_package) ? $bd_package->free : 'false'; ?>
			<td><label for="meta_package_free"><?php _e("Package free"); ?></label></td>
			<td>
				<input name="meta_package_free" id="meta_package_free" type="checkbox" <?php if ($free == 'true'){ ?>checked="checked" <?php } ?>/>
			</td>
		</tr>
		<tr>
			<?php $separate_major_releases = is_object($bd_package) ? $bd_package->separate_major_releases : 'false'; ?>
			<td><label for="meta_package_separate_major_releases"><?php _e("Separate major releases"); ?></label></td>
			<td>
				<input name="meta_package_separate_major_releases" id="meta_package_separate_major_releases" type="checkbox" <?php if ($separate_major_releases == 'true'){ ?>checked="checked" <?php } ?>/>
			</td>
		</tr>
	</table>
<?php } ?>
