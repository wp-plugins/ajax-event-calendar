<?php	
	// Form post trigger
	if (isset($_POST['action'])) {
		require_once("../../../../wp-blog-header.php");
		header("HTTP/1.1 200 OK");
		$action = $_POST['action'];
		if ($action == 'delete')
			$aec->delete_category($_POST['id']);
		if ($action == 'change')
			$aec->change_category($_POST['id']);
		if ($action == 'update')
			$aec->update_category($_POST['category_data']);
		if ($action == 'add')
			$aec->add_category($_POST['category_data']);
	} else {
		$categories = $this->get_categories();
?>
<div class='wrap'>
	<h2><?php _e('Categories', AEC_PLUGIN_NAME); ?></h2>
	<p><?php _e('Add new, or edit existing category type (and associated calendar tile color).', AEC_PLUGIN_NAME); ?></p>
	<form id="aec-category-form">
	<ul>
		<li>
			<input type="hidden" id="bgcolor" name="bgcolor" class="bg colors" value="#ABCABC" /> 
			<input type="hidden" id="fgcolor" name="fgcolor" class="fg" />
			<input type="text" id="category" name="category" value="" /> 
			<button class="add button-primary"><?php _e('Add', AEC_PLUGIN_NAME); ?></button>
		</li>
	</ul>
	</form>

	<ol id="aec-category-table">
<?php
		$out = '';
		foreach ($categories as $category) {
			$delete = ($category->id > 1) ? '<a class="delete">' . htmlentities(__('Delete', AEC_PLUGIN_NAME)) . '</a>' . "\n" : ' <em>' . htmlentities(__('This category is required and can only be edited.', AEC_PLUGIN_NAME)) . '</em>';
			$out .= '<li id="id_' . $category->id . '">' . "\n";
			$out .= '<input type="hidden" name="bgcolor" value="#' . $category->bgcolor . '" class="bg colors" />' . "\n";
			$out .= '<input type="hidden" name="fgcolor" value="#' . $category->fgcolor . '" class="fg" />' . "\n";
			$out .= '<input type="text" name="category" value="' . $category->category . '" class="edit" />' . "\n";
			$out .= '<span><button class="update button-secondary">' . htmlentities(__('Update', AEC_PLUGIN_NAME)) . '</button> ' . $delete . '</span>' . "\n";
			$out .= '</li>' . "\n";
		}
		echo $out;
?>
	</ol>
</div>
<script>
	jQuery(document).ready(function() {
		jQuery.jGrowl.defaults.closerTemplate = '<div><?php htmlentities(_e('hide all notifications', AEC_PLUGIN_NAME)); ?></div>';
		jQuery.jGrowl.defaults.position = 'bottom-right';
		jQuery('.colors').miniColors({
			change: function(hex, rgb) {
				jQuery('.fg', jQuery(this).parent()[0]).val(getFG(rgb));
			}
		});

		validateForm();

		jQuery('#aec-category-form').keyup(function() {
			validateForm();
		});
		
		function validateForm() {
			var required = ['category'];
			
			// check required fields
			jQuery.each(required, function(index, value) {
				var value = jQuery.trim(jQuery('#' + this).val());
				 if (value.length > 1) {
					jQuery('#' + this).removeClass('error');
					err= false;
				 } else {
					jQuery('#' + this).addClass('error');
					err = true;
				 }
			});
			if (err) {
				jQuery('.add').attr('disabled', 'disabled');
				return false;
			} else {
				jQuery('.add').removeAttr('disabled');
				return true;
			}
		}

		jQuery('.add').click(function(e) {
			e.preventDefault();
			jQuery.post('<?php echo AEC_PLUGIN_URL; ?>inc/admin-category.php', { 'category_data': jQuery('#aec-category-form').serialize(), 'action': 'add' }, function(data){
				if (data) {
					var row =  '<li id="id_' + data.id + '"> \n';
						row += '<input type="hidden" name="bgcolor" value="#' + data.bgcolor + '"  class="bg colors" /> \n';
						row += '<input type="hidden" name="fgcolor" value="#' + data.fgcolor + '" class="fb" /> \n';
						row += '<input type="text" name="category" value="' + data.category + '" class="edit" /> \n';
						row += '<span><button class="update button-secondary"><?php htmlentities(_e('Update', AEC_PLUGIN_NAME)); ?></button> <a class="delete"><?php htmlentities(_e('Delete', AEC_PLUGIN_NAME)); ?></a></span> \n';
						row += '</li> \n';

					jQuery('#aec-category-table').append(row);
					jQuery('.colors', jQuery('#id_' + data.id)).miniColors({
						change: function(hex, rgb) {
							jQuery('.fg', jQuery(this).parent()[0]).val(getFG(rgb));
						}
					});
					jQuery.jGrowl('<?php htmlentities(_e('Category type', AEC_PLUGIN_NAME)); ?> <strong>' + data.category + '</strong> <?php htmlentities(_e('has been created.', AEC_PLUGIN_NAME)); ?>', { header: '<?php htmlentities(_e('Success!', AEC_PLUGIN_NAME)); ?>' });
					jQuery('#category').val('');	// clear field after submission
				}
			}, 'json');
		});
		
		jQuery('body').delegate('.update', 'click', function(e) {
			e.preventDefault();
			var row = jQuery(this).parent().parent()[0],
				html_id = row.id,
				id = html_id.replace('id_', ''),
				cat = jQuery.trim(jQuery('.edit' , row).val()),
				fg = jQuery('.fg' , row).val(),
				bg = jQuery('.bg', row).val(),
				json = { 'id': id, 'bgcolor': bg, 'fgcolor': fg, 'category': cat };
				if (cat.length > 1) {
					 jQuery.post('<?php echo AEC_PLUGIN_URL; ?>inc/admin-category.php', { 'category_data': json, 'action': 'update' }, function(data, textStatus, jqXHR){
					if (data) {					
							jQuery.jGrowl('<?php htmlentities(_e('Category type', AEC_PLUGIN_NAME)); ?> <strong>' + cat + '</strong> <?php htmlentities(_e('has been created.', AEC_PLUGIN_NAME)); ?>', { header: '<?php htmlentities(_e('Success!', AEC_PLUGIN_NAME)); ?>' });
						}
					});
				} else {
					jQuery.jGrowl('<?php htmlentities(_e('Category type cannot be a blank value.', AEC_PLUGIN_NAME)); ?>', { header: '<?php htmlentities(_e('Whoops!', AEC_PLUGIN_NAME)); ?>' });
				}
		});
		
		jQuery('body').delegate('.delete', 'click', function(e) {
			e.preventDefault();
			var row = jQuery(this).parent().parent()[0],
				html_id = row.id,
				id = html_id.replace('id_', ''),
				cat = jQuery('.edit' , row).val();

			if (confirm('<?php htmlentities(_e('Are you sure you want to delete this category type?', AEC_PLUGIN_NAME)); ?>')) {
				jQuery.post('<?php echo AEC_PLUGIN_URL; ?>inc/admin-category.php', { 'id': id, 'action': 'delete' }, function(data) {
					if (data) {
						if (data == 'false') {
							if (confirm('<?php htmlentities(_e('Several events are associated with this category. Click OK to reassign these events to the default category.', AEC_PLUGIN_NAME)); ?>')) {
								jQuery.post('<?php echo AEC_PLUGIN_URL; ?>inc/admin-category.php', { 'id': id, 'action': 'change' }, function(data) {
									jQuery.jGrowl('<?php htmlentities(_e('Events have been reassigned to the default category.', AEC_PLUGIN_NAME)); ?>', { header: '<?php htmlentities(_e('Success!', AEC_PLUGIN_NAME)); ?>!' });
									jQuery(row).remove();
								});
							}
						} else {
							jQuery(row).remove();
							jQuery.jGrowl('<?php htmlentities(_e('Category type', AEC_PLUGIN_NAME)); ?> <strong>' + cat + '</strong> <?php htmlentities(_e('has been deleted.', AEC_PLUGIN_NAME)); ?>', { header: '<?php htmlentities(_e('Success!', AEC_PLUGIN_NAME)); ?>' });
						}
					}
				});
			}
		});
		
		function getFG(rgb) {
			var lums = rgb.r * 0.299 + rgb.g * 0.587 + rgb.b * 0.114,
				fg = (lums > 186) ? '#000000' : '#FFFFFF';
			return fg;
		}
	});
</script>
<?php
}
?>