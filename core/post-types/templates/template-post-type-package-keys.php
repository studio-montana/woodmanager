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

<input type="hidden" name="<?php echo WOODMANAGER_PACKAGE_KEYS_NONCE_ACTION; ?>" value="<?php echo wp_create_nonce(WOODMANAGER_PACKAGE_KEYS_NONCE_ACTION);?>" />

<?php 
$original_id = woodmanager_get_original_post($post->ID);
$can_update = true;
if (!empty($original_id) && $original_id != $post->ID)
	$can_update = false;

if (!$can_update){
	?>
	<p class="">
		<?php _e("You can not modify package keys, please edit original item of this translation.", WOODMANAGER_PLUGIN_TEXT_DOMAIN); ?>
	</p>
	<p class="">
		<a href="<?php echo get_edit_post_link($original_id); ?>" class="button"><?php _e("Edit original item", WOODMANAGER_PLUGIN_TEXT_DOMAIN); ?></a>	
	</p>
	<?php
}else{ ?>
	<table>
		<?php 
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
			?>
			<tr>
				<td>
					<div id="woodmanager-package-keys-manager">
						<?php
						$keys = BD_package_key::get_package_keys("id_package = ".$bd_package->id);
						$nb_keys = 0;
						if (!empty($keys)){
							foreach ($keys as $key){
								$nb_keys++;
								?>
								<div id="package-key-item-<?php echo $nb_keys; ?>" class="package-key-item">
									<input type="hidden" name="package-key-item-id-<?php echo $nb_keys; ?>" value="<?php echo $nb_keys; ?>" />
									<input type="hidden" name="package-key-item-bd-id-<?php echo $nb_keys; ?>" value="<?php echo $key->id; ?>" />
									<input size="30" type="text" id="package-key-host-<?php echo $nb_keys; ?>" name="package-key-host-<?php echo $nb_keys; ?>" class="package-key-host" value="<?php echo $key->host; ?>" placeholder="<?php echo esc_attr("http://www.new-site.com"); ?>" />
									<input type="text" id="package-key-activation-<?php echo $nb_keys; ?>" name="package-key-activation-<?php echo $nb_keys; ?>" class="package-key-activation" value="<?php echo $key->key_activation; ?>" placeholder="<?php echo esc_attr(__("new key", WOODMANAGER_PLUGIN_TEXT_DOMAIN)); ?>" />
									<select id="package-key-user-<?php echo $nb_keys; ?>" name="package-key-user-<?php echo $nb_keys; ?>" class="package-key-user">
										<?php echo woodmanager_get_user_options($key->id_user); ?>
									</select>
									<span class="button delete-package-key-item" data-id="<?php echo $nb_keys; ?>"><i class="fa fa-times"></i></span>
								</div>
								<?php
							}
						}
						// new empty item
						$nb_keys++;
						?>
						<div id="package-key-item-<?php echo $nb_keys; ?>" class="package-key-item">
							<input type="hidden" name="package-key-item-id-<?php echo $nb_keys; ?>" value="<?php echo $nb_keys; ?>" />
							<input size="30" type="text" id="package-key-host-<?php echo $nb_keys; ?>" name="package-key-host-<?php echo $nb_keys; ?>" class="package-key-host" value="" placeholder="<?php echo esc_attr("http://www.new-site.com"); ?>" />
							<input type="text" id="package-key-activation-<?php echo $nb_keys; ?>" name="package-key-activation-<?php echo $nb_keys; ?>" class="package-key-activation" value="" placeholder="<?php echo esc_attr("key"); ?>" />
							<select id="package-key-user-<?php echo $nb_keys; ?>" name="package-key-user-<?php echo $nb_keys; ?>" class="package-key-user">
								<?php echo woodmanager_get_user_options(get_current_user_id()); ?>
							</select>
							<span class="button delete-package-key-item" data-id="<?php echo $nb_keys; ?>"><i class="fa fa-times"></i></span>
						</div>
					</div>
					<div id="add-package-key-item" class="button"><i class="fa fa-plus" style="margin-right: 6px;"></i><?php _e("Add key", WOODMANAGER_PLUGIN_TEXT_DOMAIN); ?></div>
					<script type="text/javascript">
						jQuery(document).ready(function($){
							var nb_keys = <?php echo $nb_keys; ?>;
							$(document).on("click", "#add-package-key-item", function(e){
								nb_keys ++;
								var new_key_item = '<div id="package-key-item-'+nb_keys+'" class="package-key-item">';
								new_key_item += '<input type="hidden" name="package-key-item-id-'+nb_keys+'" value="'+nb_keys+'" />';
								new_key_item += '<input size="30" type="text" id="package-key-host-'+nb_keys+'" name="package-key-host-'+nb_keys+'" class="package-key-host" value="" placeholder="<?php echo esc_attr("http://www.new-site.com"); ?>" />';
								new_key_item += '<input type="text" id="package-key-activation-'+nb_keys+'" name="package-key-activation-'+nb_keys+'" class="package-key-activation" value="" placeholder="<?php echo esc_attr("key"); ?>" />';
								new_key_item += '<select id="package-key-user-'+nb_keys+'" name="package-key-user-'+nb_keys+'" class="package-key-user">';
								new_key_item += '<?php echo woodmanager_get_user_options(get_current_user_id()); ?>';
								new_key_item += '</select>';
								new_key_item += '<span class="button delete-package-key-item" data-id="'+nb_keys+'"><i class="fa fa-times"></i></span>';
								new_key_item += '</div>';
								$("#woodmanager-package-keys-manager").append(new_key_item);
							});
							$(document).on("click", ".delete-package-key-item", function() {
								var id = $(this).data("id");
								$("#package-key-item-"+id).remove();
							});
						});
					</script>
				</td>
			</tr>
			<?php
		}else{
			?><tr><td><span><?php _e("no package", WOODMANAGER_PLUGIN_TEXT_DOMAIN); ?></span></td></tr><?php
		}
		?>
	</table>
<?php } ?>
