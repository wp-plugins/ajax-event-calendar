<div class="wrap">
	<div class="icon32" id="icon-options-general"><br></div>
	<h2><?php _e('Ajax Event Calendar Options', AEC_PLUGIN_NAME); ?></h2>
	<?php
		$general = array(
			'show_weekends' => __('Display calendar weekends.', AEC_PLUGIN_NAME),
			'show_map_link' => __('Display View Map link on event details, if the address fields are populated.', AEC_PLUGIN_NAME),
			'menu' 			=> __('Display Add Events link on the front-end calendar.', AEC_PLUGIN_NAME),
			'limit' 		=> __('Prevent users from adding events that have transpired.', AEC_PLUGIN_NAME)
		);
		$checkboxes = array(
			'venue' 		=> __('Venue', AEC_PLUGIN_NAME),
			'address' 		=> __('Neighborhood or Street Address', AEC_PLUGIN_NAME),
			'city' 			=> __('City', AEC_PLUGIN_NAME),
			'state' 		=> __('State', AEC_PLUGIN_NAME),
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
		<?php settings_fields(AEC_DOMAIN . 'plugin_options'); ?>
		<?php $options = get_option(AEC_DOMAIN . 'options'); ?>
		<input type="hidden" name="aec_options[title]" value="2" />
		<table class="form-table">
			<tr>
				<th scope="row"><?php _e('General Options', AEC_PLUGIN_NAME); ?></th>
				<td>
					<?php
					foreach ($general as $field => $value) {
						$checked = ($options[$field]) ? ' checked="checked" ' : ' ';
						echo '<input type="hidden" name="aec_options[' . $field . ']" value="0" />';
						echo '<label>';
						echo '<input' . $checked . 'id="' . $field . '" value="1" name="aec_options[' . $field . ']" type="checkbox" /> ';
						echo $value . '</label><br />';
					}
					?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Checked fields are required', AEC_PLUGIN_NAME); ?></th>
				<td>
					<?php 
					foreach ($checkboxes as $checkbox => $value) {
						$checked = ($options[$checkbox] == 2) ? ' checked="checked" ' : ' ';
						echo '<input type="hidden" name="aec_options[' . $checkbox . ']" value="1" />';
						echo '<label>';
						echo '<input' . $checked . 'id="' . $checkbox . '" value="2" name="aec_options[' . $checkbox . ']" type="checkbox" /> ';
						echo $value . '</label><br />';
					}
					?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Checked fields are displayed', AEC_PLUGIN_NAME); ?></th>
				<td>
					<?php
					foreach ($optional as $field => $value) {
						$checked = ($options[$field]) ? ' checked="checked" ' : ' ';
						echo '<input type="hidden" name="aec_options[' . $field . ']" value="0" />';
						echo '<label>';
						echo '<input' . $checked . 'id="' . $field . '" value="1" name="aec_options[' . $field . ']" type="checkbox" /> ';
						echo $value . '</label><br />';
					}
					?>
				</td>
			</tr>
			<tr style="border-top:1px solid #ccccccc">
				<th scope="row"><?php _e('Restore Original Settings', AEC_PLUGIN_NAME); ?></th>
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