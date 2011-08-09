<div class="wrap">
	<div class="icon32" id="icon-options-general"><br></div>
	<h2><?php _e('Ajax Event Calendar Options', AEC_PLUGIN_NAME); ?></h2>
	<?php
		$general = array(
			'show_weekends' => __('Display calendar weekends.', AEC_PLUGIN_NAME),
			'show_map_link' => __('Display "View Map" link on event details (uses populated address fields).', AEC_PLUGIN_NAME),
			'menu' 			=> __('Display "Add Events" link on the front-end calendar.', AEC_PLUGIN_NAME),
			'limit' 		=> __('Prevent users from adding or editing expired events.', AEC_PLUGIN_NAME)
		);
		$fields = array(
			'venue' 		=> __('Venue', AEC_PLUGIN_NAME),
			'address' 		=> __('Neighborhood or Street Address', AEC_PLUGIN_NAME),
			'city' 			=> __('City', AEC_PLUGIN_NAME),
			'state' 		=> array(__('State', AEC_PLUGIN_NAME), __('if State is hidden, the address format displayed is: {Postal Code} {City}', AEC_PLUGIN_NAME)),
			'zip' 			=> __('Postal Code', AEC_PLUGIN_NAME),
			'link' 			=> __('Event Link', AEC_PLUGIN_NAME),
			'description' 	=> __('Description', AEC_PLUGIN_NAME),
			'contact' 		=> __('Contact Name', AEC_PLUGIN_NAME),
			'contact_info'	=> __('Contact Information', AEC_PLUGIN_NAME)
		);
		$optional = array(
			'accessible'	=> __('This event is accessible to people with disabilities.', AEC_PLUGIN_NAME),
			'rsvp' 			=> __('Please register with the contact person for this event.', AEC_PLUGIN_NAME)
		);
	?>
	<form method="post" action="options.php">
		<?php settings_fields('aec_plugin_options'); ?>
		<?php $options = get_option('aec_options'); ?>
		<input type="hidden" name="aec_options[title]" value="2" />
		<table class="form-table">
			<tr>
				<th><label for="filter_label"><?php _e('Category filter label', AEC_PLUGIN_NAME); ?></label></th>
				<td><input type='text' name='aec_options[filter_label]' id='filter_label' value="<?php esc_attr_e($options['filter_label']); ?>" /></td>
			</tr>
			<tr>
				<th><?php _e('General Options', AEC_PLUGIN_NAME); ?></th>
				<td>
					<?php
					foreach ($general as $field => $value) {
						$checked = ($options[$field]) ? ' checked="checked" ' : ' ';
						echo '<p><input type="hidden" name="aec_options[' . $field . ']" value="0" />';
						echo '<input' . $checked . 'id="' . $field . '" value="1" name="aec_options[' . $field . ']" type="checkbox" class="box" /> ';
						echo '<label for="' . $field . '">' . $value . '</label></p>';
					}
					?>
				</td>
			</tr>
			<tr>
				<th colspan="2"><?php _e('Hide, display or require form fields.  Hidden fields will not appear in the event form.', AEC_PLUGIN_NAME); ?></th>
			</tr>
			<?php
				foreach ($fields as $field => $value) {
					$desc = (is_array($value)) ? $value[1] : false; 
					$value = (is_array($value)) ? $value[0] : $value; 
					$out = '<tr>';
					$out .= "<th>{$value}</th>\n";
					$out .= '<td><select name="aec_options[' . $field . ']" >';
					$out .= "<option value=0 name=aec_options[{$field}] " . selected($options[$field], 0, false) . ">" . __('Hide', AEC_PLUGIN_NAME) . '</option>';
					$out .= "<option value=1 name=aec_options[{$field}] " . selected($options[$field], 1, false) . ">" . __('Display', AEC_PLUGIN_NAME) . '</option>';
					$out .= "<option value=2 name=aec_options[{$field}] " . selected($options[$field], 2, false) . ">" . __('Require', AEC_PLUGIN_NAME) . '</option>';
					$out .= "</select>\n";
					if ($desc) $out .= '<span class="description">' . $desc . '</span>';
					$out .= '</td></tr>';
					echo $out;
				}
			?>
			<tr>
				<th><?php _e('Checked fields are displayed', AEC_PLUGIN_NAME); ?></th>
				<td>
					<?php
					foreach ($optional as $field => $value) {
						$checked = ($options[$field]) ? ' checked="checked" ' : ' ';
						echo '<p><input type="hidden" name="aec_options[' . $field . ']" value="0" />';
						echo '<label>';
						echo '<input' . $checked . 'id="' . $field . '" value="1" name="aec_options[' . $field . ']" type="checkbox" /> ';
						echo $value . '</label></p>';
					}
					?>
				</td>
			</tr>
			<tr>
				<th><?php _e('Restore Original Settings', AEC_PLUGIN_NAME); ?></th>
				<td>
					<label>
					<input type="hidden" name="aec_options[reset]" value="0" />
					<input name="aec_options[reset]" type="checkbox" value="1" <?php if (isset($options['reset'])) { checked('1', $options['reset']); } ?> /> <?php _e('Resets plugin settings on Save', AEC_PLUGIN_NAME); ?></label>
				</td>
			</tr>
		</table>
		<p class="submit">
			<input name="Submit" type="submit" class="button-primary" value="<?php _e('Save Changes', AEC_PLUGIN_NAME); ?>" />
		</p>
	</form>
</div>