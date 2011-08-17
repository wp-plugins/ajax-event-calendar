/**
 * Handle: init_admin_category
 * Version: 0.9.8.5
 * Deps: $jq
 * Enqueue: true
 */

$jq = jQuery.noConflict();
$jq().ready(function(){
	$jq.jGrowl.defaults.closerTemplate = '<div>' + custom.hide_all_notifications + '</div>';
	$jq.jGrowl.defaults.position = 'bottom-right';

	$jq('.colors').miniColors({
		change: function(hex, rgb) {
			$jq('.fg', $jq(this).parent()[0]).val(getFG(rgb));
		}
	});

	validateForm();

	$jq('#aec-category-form').keyup(function() {
		validateForm();
	});

	function validateForm() {
		var required = ['category'];

		// check required fields
		$jq.each(required, function(index, value) {
			var value = $jq.trim($jq('#' + this).val());
			if (value.length) {
				$jq('#' + this).removeClass('error');
				err= false;
			} else {
				$jq('#' + this).addClass('error');
				err = true;
			}
		});
		if (err) {
			$jq('.add').attr('disabled', 'disabled');
			return false;
		} else {
			$jq('.add').removeAttr('disabled');
			return true;
		}
	}

	$jq('.add').click(function(e) {
		e.preventDefault();
		$jq.post(ajaxurl, { action: 'add_category', 'category_data': $jq('#aec-category-form').serialize() }, function(data){
			if (data) {
				var row =  '<p id="id_' + data.id + '"> \n';
					row += '<input type="hidden" name="fgcolor" value="#' + data.fgcolor + '" class="fg" /> \n';
					row += '<input type="text" name="bgcolor" size="7" maxlength="7" autocomplete="off" value="#' + data.bgcolor + '" class="bg colors" /> \n';
					row += '<input type="text" name="category" value="" class="edit" /> \n';
					row += '<button class="button-secondary update">' + custom.update_btn + '</button> \n';
					row += '<button class="button-secondary delete">' + custom.delete_btn + '</button> \n';
					row += '</p> \n';

				$jq('#aec-category-list').append(row);
				$jq('#id_' + data.id).find('.edit').val(data.category);
				$jq('.colors', $jq('#id_' + data.id)).miniColors({
					change: function(hex, rgb) {
						$jq('.fg', $jq(this).parent()[0]).val(getFG(rgb));
					}
				});
				$jq.jGrowl(custom.category_type + ' <strong>' + data.category + '</strong> ' + custom.has_been_created, { header: custom.success });
				$jq('#category').val('');	// clear field after submission
			}
		}, 'json');
	});

	$jq('#aec-category-list').delegate('.update', 'click', function(e) {
		e.preventDefault();
		var row = $jq(this).parent()[0],
		html_id = row.id,
		id 		= html_id.replace('id_', ''),
		cat 	= $jq.trim($jq('.edit', row).val()),
		fg 		= $jq('.fg', row).val(),
		bg 		= $jq('.bg', row).val(),
		json 	= { 'id': id, 'bgcolor': bg, 'fgcolor': fg, 'category': cat };
		if (cat.length > 1) {
			 $jq.post(ajaxurl, { action: 'update_category', 'category_data': json }, function(data){
				if (data) {
					$jq.jGrowl(custom.category_type + ' <strong>' + cat + '</strong> ' + custom.has_been_modified, { header: custom.success });
				}
			});
		} else {
			$jq.jGrowl(custom.error_blank_category, { header: custom.whoops });
		}
	});

	$jq('#aec-category-list').delegate('.delete', 'click', function(e) {
		e.preventDefault();
		var row 	= $jq(this).parent()[0],
			html_id = row.id,
			id		= html_id.replace('id_', ''),
			cat 	= $jq('.edit' , row).val();
		if (confirm(custom.confirm_category_delete)) {
			$jq.post(ajaxurl, { action: 'delete_category', 'id': id }, function(data) {
				if (data) {
					if (data == 'false') {
						if (confirm(custom.confirm_category_reassign)) {
							$jq.post(ajaxurl, { action: 'reassign_category', 'id': id }, function(data) {
								$jq.jGrowl(custom.events_reassigned, { header: custom.success });
								$jq(row).remove();
							});
						}
					} else {
						$jq(row).remove();
						$jq.jGrowl(custom.category_type + ' <strong>' + cat + '</strong> ' + custom.has_been_deleted, { header: custom.success });
					}
				}
			});
		}
	});

	// output legible foreground color based on background luminance
	function getFG(rgb) {
		var lums = rgb.r * 0.299 + rgb.g * 0.587 + rgb.b * 0.114,
			fg	 = (lums > 186) ? '#000000' : '#FFFFFF';
		return fg;
	}
});