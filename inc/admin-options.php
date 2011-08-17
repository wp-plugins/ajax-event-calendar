<div class="wrap">
	<div class="icon32" id="icon-options-general"><br></div>
	<h2><?php _e('Ajax Event Calendar Options', AEC_PLUGIN_NAME); ?></h2>
	<?php
		$city 					= __('City', AEC_PLUGIN_NAME);
		$state 					= __('State', AEC_PLUGIN_NAME);
		$postal					= __('Postal Code', AEC_PLUGIN_NAME);
		$format_options 		= array("{{$city}}, {{$state}} {{$postal}}", "{{$postal}} {{$city}}");
		$field_options			= array( __('Hide', AEC_PLUGIN_NAME), __('Display', AEC_PLUGIN_NAME), __('Require', AEC_PLUGIN_NAME));
		$fields 				= array(
			'title'				=> array('hidden', 2),	// preserves event title as a required field.
			'filter_label'		=> array('textfield', __('Category filter label', AEC_PLUGIN_NAME)),
			'limit'				=> array('checkbox', __('Prevent users from adding or editing expired events.', AEC_PLUGIN_NAME)),
			'show_weekends'		=> array('checkbox', __('Display calendar weekends.', AEC_PLUGIN_NAME)),
			'show_map_link' 	=> array('checkbox', __('Display {View Map} link on event details (uses populated address fields).', AEC_PLUGIN_NAME)),
			'menu' 				=> array('checkbox', __('Display {Add Events} link on the front-end calendar.', AEC_PLUGIN_NAME)),
			'popup_links'		=> array('checkbox', __('{Event Detail} links open in a new window (when unchecked, links open in the same window).', AEC_PLUGIN_NAME)),
			'make_links'		=> array('checkbox', __('URLs entered in the description field are converted into clickable links.', AEC_PLUGIN_NAME)),
			'addy_format'		=> array('select', __('Address format', AEC_PLUGIN_NAME), $format_options),
			'fields'			=> array('heading', __('Form Options', AEC_PLUGIN_NAME), __('Hide, display or require form fields.  Hidden fields do not appear in the event form.', AEC_PLUGIN_NAME)),
			'venue' 			=> array('select', __('Venue', AEC_PLUGIN_NAME), $field_options),
			'address' 			=> array('select', __('Neighborhood or Street Address', AEC_PLUGIN_NAME), $field_options),
			'city' 				=> array('select', __('City', AEC_PLUGIN_NAME), $field_options),
			'state' 			=> array('select', __('State', AEC_PLUGIN_NAME), $field_options),
			'zip' 				=> array('select', __('Postal Code', AEC_PLUGIN_NAME), $field_options),
			'link' 				=> array('select', __('Event Link', AEC_PLUGIN_NAME), $field_options),
			'description' 		=> array('select', __('Description', AEC_PLUGIN_NAME), $field_options),
			'contact' 			=> array('select', __('Contact Name', AEC_PLUGIN_NAME), $field_options),
			'contact_info'		=> array('select', __('Contact Information', AEC_PLUGIN_NAME), $field_options),
			'accessible'		=> array('checkbox', __('This event is accessible to people with disabilities.', AEC_PLUGIN_NAME)),
			'rsvp' 				=> array('checkbox', __('Please register with the contact person for this event.', AEC_PLUGIN_NAME)),
			'reset'				=> array('checkbox', __('Resets plugin settings on Save.', AEC_PLUGIN_NAME))
		);
	?>
	<form method="post" action="options.php">
		<?php settings_fields('aec_plugin_options'); ?>
		<?php $options = get_option('aec_options'); ?>
		<table class="form-table">
			<?php
				foreach ($fields as $field => $values) {
					$type = $values[0];
					$value = $values[1];
					$description = isset($values[2]) ? $values[2] : false;
					switch ($type) {
						case "hidden":
							echo "<input type='hidden' name='aec_options[{$field}]' value='{$value}' />\n";
						break;
						case "heading":
							echo "<tr>\n";
							echo "<th>{$value}</th>\n";
							if ($description) echo "<td><span class='description'>{$description}</span></td>\n";
							echo "</tr>\n";
						break;
						case "textfield":
							echo "<tr>\n";
							echo "<th><label for='{$field}'>{$value}</label></th>\n";
							echo "<td><input type='text' name='aec_options[{$field}]' id='{$field}' value='" . esc_attr($options[$field]) . "' />\n";
							if ($description) echo "<span class='description'>{$description}</span>\n";
							echo "</td></tr>\n";
						break;
						case "checkbox":
							$checked = ($options[$field]) ? ' checked="checked" ' : ' ';					
							echo "<td><input type='hidden' name='aec_options[{$field}]' value='0' /></td>\n";
							echo "<td><input type='checkbox' name='aec_options[{$field}]' id='{$field}' value='1' class='box' {$checked} />\n";
							echo "<label for='{$field}'>{$value}</label>\n";
							if ($description) echo "<span class='description'>{$description}</span>\n";
							echo "</td></tr>\n";
						break;
						case "select":
							$select_opts = $values[2];
							$description = isset($values[3]) ? $values[3] : false;
							echo "<th>{$value}</th>\n";
							echo "<td><select name='aec_options[{$field}]' >\n";
							foreach ($select_opts as $option => $value) {
								echo "<option value='{$option}' name='aec_options[{$field}]' " . selected($options[$field], $option, false) . ">{$value}</option>\n";								
							}
							echo "</select>\n";
							if ($description) echo "<span class='description'>{$description}</span>\n";
							echo "</td></tr>\n";
						break;
					}
				}
			?>
		</table>
		<p class="submit">
			<input name="Submit" type="submit" class="button-primary" value="<?php _e('Save Changes', AEC_PLUGIN_NAME); ?>" />
		</p>
	</form>
</div>