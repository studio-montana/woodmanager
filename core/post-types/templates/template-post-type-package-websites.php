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

<input type="hidden" name="<?php echo WOODMANAGER_PACKAGE_WEBSITES_NONCE_ACTION; ?>" value="<?php echo wp_create_nonce(WOODMANAGER_PACKAGE_WEBSITES_NONCE_ACTION);?>" />

<?php 
$original_id = woodmanager_get_original_post($post->ID);
$can_update = true;
if (!empty($original_id) && $original_id != $post->ID)
	$can_update = false;

if (!$can_update){
	?>
	<p class="">
		<?php _e("You can not modify package websites, please edit original item of this translation.", WOODMANAGER_PLUGIN_TEXT_DOMAIN); ?>
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
					<div id="woodmanager-package-websites-manager">
						<?php
						$websites = BD_Package_Website::get_package_websites("id_package = ".$bd_package->id);
						$nb_websites = 0;
						if (!empty($websites)){
							foreach ($websites as $website){
								$nb_websites++;
								?>
								<div id="package-website-item-<?php echo $nb_websites; ?>" class="package-website-item">
									<input type="hidden" name="package-website-item-id-<?php echo $nb_websites; ?>" value="<?php echo $nb_websites; ?>" />
									<input type="hidden" name="package-website-item-bd-id-<?php echo $nb_websites; ?>" value="<?php echo $website->id; ?>" />
									<input size="30" type="text" id="package-website-host-<?php echo $nb_websites; ?>" name="package-website-host-<?php echo $nb_websites; ?>" class="package-website-host" value="<?php echo $website->host; ?>" placeholder="<?php echo esc_attr("http://www.new-site.com"); ?>" />
									<input type="text" id="package-website-activation-<?php echo $nb_websites; ?>" name="package-website-activation-<?php echo $nb_websites; ?>" class="package-website-activation" value="<?php echo $website->key_activation; ?>" placeholder="<?php echo esc_attr(__("key", WOODMANAGER_PLUGIN_TEXT_DOMAIN)); ?>" />
									<select id="package-website-user-<?php echo $nb_websites; ?>" name="package-website-user-<?php echo $nb_websites; ?>" class="package-website-user">
										<?php echo woodmanager_get_user_options($website->id_user); ?>
									</select>
									<input type="checkbox" id="package-website-prerelease-<?php echo $nb_websites; ?>" name="package-website-prerelease-<?php echo $nb_websites; ?>" class="package-website-prerelease"<?php if (!empty($website->prerelease) && $website->prerelease === 'true') { ?> checked="checked"<?php } ?> /><label for="package-website-prerelease-<?php echo $nb_websites; ?>"><?php _e("enable prerelease", WOODMANAGER_PLUGIN_TEXT_DOMAIN); ?></label>
									<span class="button delete-package-website-item" data-id="<?php echo $nb_websites; ?>">X</span>
								</div>
								<?php
							}
						}
						?>
					</div>
					<div id="add-package-website-item" class="button">+ <?php _e("Add website", WOODMANAGER_PLUGIN_TEXT_DOMAIN); ?></div>
					<script type="text/javascript">
						jQuery(document).ready(function($){
							var nb_websites = <?php echo $nb_websites; ?>;
							$(document).on("click", "#add-package-website-item", function(e){
								nb_websites ++;
								var new_website_item = '<div id="package-website-item-'+nb_websites+'" class="package-website-item">';
								new_website_item += '<input type="hidden" name="package-website-item-id-'+nb_websites+'" value="'+nb_websites+'" />';
								new_website_item += '<input size="30" type="text" id="package-website-host-'+nb_websites+'" name="package-website-host-'+nb_websites+'" class="package-website-host" value="" placeholder="<?php echo esc_attr("http://www.new-site.com"); ?>" />';
								new_website_item += '<input type="text" id="package-website-activation-'+nb_websites+'" name="package-website-activation-'+nb_websites+'" class="package-website-activation" value="" placeholder="<?php echo esc_attr(__("key", WOODMANAGER_PLUGIN_TEXT_DOMAIN)); ?>" />';
								new_website_item += '<select id="package-website-user-'+nb_websites+'" name="package-website-user-'+nb_websites+'" class="package-website-user">';
								new_website_item += '<?php echo woodmanager_get_user_options(get_current_user_id()); ?>';
								new_website_item += '</select>';
								new_website_item += '<input type="checkbox" id="package-website-prerelease-'+nb_websites+'" name="package-website-prerelease-'+nb_websites+'" class="package-website-prerelease" /><label for="package-website-prerelease-'+nb_websites+'"><?php _e("enable prerelease", WOODMANAGER_PLUGIN_TEXT_DOMAIN); ?></label>';
								new_website_item += '<span class="button delete-package-website-item" data-id="'+nb_websites+'">X</span>';
								new_website_item += '</div>';
								$("#woodmanager-package-websites-manager").append(new_website_item);
							});
							$(document).on("click", ".delete-package-website-item", function() {
								var id = $(this).data("id");
								$("#package-website-item-"+id).remove();
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