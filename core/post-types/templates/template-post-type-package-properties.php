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
			<td><label for="meta_package_slug"><?php _e("Package slug", WOODMANAGER_PLUGIN_TEXT_DOMAIN); ?></label></td>
			<td>
				<input name="meta_package_slug" id="meta_package_slug" type="text" placeholder="my-package" value="<?php echo $package_slug; ?>" />
			</td>
			<td></td>
		</tr>
		<?php $bd_package = null;
		if (!empty($package_slug)){
			$bd_package = BD_Package::get_package_by_slug($package_slug);
		} ?>
		<tr>
			<?php $scope = is_object($bd_package) ? $bd_package->scope : BD_Package::$scope_public; ?>
			<td><label for="meta_package_scope"><?php _e("Package scope", WOODMANAGER_PLUGIN_TEXT_DOMAIN); ?></label></td>
			<td>
				<select name="meta_package_scope" id="meta_package_scope">
					<option value="<?php echo BD_Package::$scope_public; ?>"<?php if (empty($scope) || $scope == BD_Package::$scope_public) { ?> selected="selected"<?php }?>><?php _e("Public", WOODMANAGER_PLUGIN_TEXT_DOMAIN); ?></option>
					<option value="<?php echo BD_Package::$scope_private; ?>"<?php if (!empty($scope) && $scope == BD_Package::$scope_private) { ?> selected="selected"<?php }?>><?php _e("Private", WOODMANAGER_PLUGIN_TEXT_DOMAIN); ?></option>
					<?php $option_packages = woodmanager_get_packages(); // NOTE : this function retrieves WP_Post packages (not DB_Package)
					if (!empty($option_packages)) {
						foreach ($option_packages as $option_package) {
							$option_package_slug = get_post_meta($option_package->ID, "meta_package_slug", true);
							if (!empty($option_package_slug) && $option_package_slug != $package_slug) { // do not purpose current package ?>
								<option value="<?php echo $option_package_slug; ?>"<?php if (!empty($scope) && $scope == $option_package_slug) { ?> selected="selected"<?php }?>><?php echo __("Depends on :", WOODMANAGER_PLUGIN_TEXT_DOMAIN) . ' ' . get_the_title($option_package->ID); ?></option>
							<?php } ?>
						<?php }
					} ?>
				</select>
			</td>
			<td>
				- Public : pas besoin de clé pour les mises à jour<br />
				- Private : une clé est nécessaire pour les mises à jour<br />
				- Depends on ... : le scope dépend d'un autre package<br />
			</td>
		</tr>
		<tr valign="top">
			<?php $separate_major_releases = is_object($bd_package) ? $bd_package->separate_major_releases : 'false'; ?>
			<td><label for="meta_package_separate_major_releases"><?php _e("Separate major releases", WOODMANAGER_PLUGIN_TEXT_DOMAIN); ?></label></td>
			<td>
				<input name="meta_package_separate_major_releases" id="meta_package_separate_major_releases" type="checkbox" <?php if ($separate_major_releases == 'true'){ ?>checked="checked" <?php } ?>/>
			</td>
			<td>Cette option permet de séparer les versions majeures lors des mises à jour du package.<br />
				Exemple : admettons qu'il existe deux releases, une en version 1.0.15 et l'autre en version 2.0.20
				<br /> - un site utilisant le package en version 1.0.3 passera en 1.0.15 (et pas en 2.0.20 car sa version majeur est 1)
				<br /> - un site utilisant le package en version 2.0.7 passera en 2.0.20
				<br />Ainsi, les passages en versions majeures doivent se faire manuellement.
			</td>
		</tr>
	</table>
<?php } ?>
