/**
 * Handle: init_admin_category
 * Version: 1.0
 * Deps: jQuery
 * Enqueue: true
 */

jQuery(document).ready(function ($) {
	var validateForm = function (err) {
		var	required	= ['category'];
		err			= false;

		// process required fields
		$.each(required, function (index, value) {
			var value = $.trim($('#' + this).val());
			if (value.length) {
				$('#' + this).removeClass('aec-error');
				err = false;
			} else {
				$('#' + this).addClass('aec-error');
				err = true;
			}
		});
			
		if (err) {
			$('.add').attr('disabled', 'disabled');
			return false;
		} else {
			$('.add').removeAttr('disabled');
			return true;
		}
	},
		getFG = function (rgb) {
			// output legible foreground color based on background luminance
			var lums = rgb.r * 0.299 + rgb.g * 0.587 + rgb.b * 0.114,
				fg	 = (lums > 186) ? '#000000' : '#FFFFFF';
			return fg;
		},
		eventHandlers = function () {
			$('.colors', 'form').miniColors({
				change: function (hex, rgb) {
					$('.fg', $(this).parent()[0]).val(getFG(rgb));
				}
			});

			$('#aec-category-form').keyup(function () {
				validateForm();
			});
			
			$('.add').click(function (e) {
				e.preventDefault();
				$.post(ajaxurl, { action: 'add_category', 'category_data': $('#aec-category-form').serialize() }, function (data) {
					if (data) {
						var row =  '<p id="id_' + data.id + '">\n';
						row += '<input type="hidden" name="fgcolor" value="#' + data.fgcolor + '" class="fg" />\n';
						row += '<span class="round5" style="color:#' + data.fgcolor + ';background-color:#' + data.bgcolor + '">' + data.id + '</span>\n';
						row += '<input type="text" name="category" value="" class="edit" />\n';
						row += '<input type="text" name="bgcolor" size="7" maxlength="7" autocomplete="off" value="#' + data.bgcolor + '" class="bg colors" />\n';
						row += '<button class="button-secondary update">' + custom.update_btn + '</button>\n';
						row += '<button class="button-secondary delete">' + custom.delete_btn + '</button>\n';
						row += '</p> \n';

						$('#aec-category-list').append(row);
						$('#id_' + data.id).find('.edit').val(data.category);
						$('.colors', $('#id_' + data.id)).miniColors({
							change: function (hex, rgb) {
								$('.fg', $(this).parent()[0]).val(getFG(rgb));
							}
						});
						$.jGrowl(custom.category_type + ' <strong>' + data.category + '</strong> ' + custom.has_been_created, { header: custom.success });
						$('#category').val('');	// clear field after submission
					}
				}, 'json');
			});
			
			$('#filter_update').click(function (e) {
				e.preventDefault();
				var label = $.trim($('#filter_label').val());
				$.post(ajaxurl, { action: 'update_filter_label', 'label': label }, function (data) {
					if (data) {
						$.jGrowl(custom.category_filter_label + ' <strong>' + label + '</strong> ' + custom.has_been_modified, { header: custom.success });
					}
				});
				return false;
			});
			
			$('#aec-category-list').delegate('.update', 'click', function (e) {
				e.preventDefault();
				var row = $(this).parent()[0],
					html_id = row.id,
					id		= html_id.replace('id_', ''),
					cat		= $.trim($('.edit', row).val()),
					fg		= $('.fg', row).val(),
					bg		= $('.bg', row).val(),
					json	= { 'id': id, 'bgcolor': bg, 'fgcolor': fg, 'category': cat };
				if (cat.length > 1) {
					$.post(ajaxurl, { action: 'update_category', 'category_data': json }, function (data) {
						if (data) {
							$("span", row).removeClass('cat' + id).css({'background-color': bg, 'color': fg });
							
							$.jGrowl(custom.category_type + ' <strong>' + cat + '</strong> ' + custom.has_been_modified, { header: custom.success });
						}
					});
				} else {
					$.jGrowl(custom.error_blank_category, { header: custom.whoops });
				}
			});

			$('#aec-category-list').delegate('.delete', 'click', function (e) {
				e.preventDefault();
				var row		= $(this).parent()[0],
					html_id = row.id,
					id		= html_id.replace('id_', ''),
					cat		= $('.edit', row).val();
				if (confirm(custom.confirm_category_delete)) {
					$.post(ajaxurl, { action: 'delete_category', 'id': id }, function (data) {
						if (data) {
							if (data === 'false') {
								if (confirm(custom.confirm_category_reassign)) {
									$.post(ajaxurl, { action: 'reassign_category', 'id': id }, function (data) {
										$.jGrowl(custom.events_reassigned, { header: custom.success });
										$(row).remove();
									});
								}
							} else {
								$(row).remove();
								$.jGrowl(custom.category_type + ' <strong>' + cat + '</strong> ' + custom.has_been_deleted, { header: custom.success });
							}
						}
					});
				}
			});
		},
		aec_init = function () {
			$.jGrowl.defaults.closerTemplate = '<div>' + custom.hide_all_notifications + '</div>';
			$.jGrowl.defaults.position = (custom.is_rtl === '1') ? 'bottom-left' : 'bottom-right';
			isFilter = ($('#aec-filter li a').length > 0);
			eventHandlers();
			validateForm();
		};

	aec_init();
});