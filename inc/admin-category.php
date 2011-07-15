<?php	
	$categories = $this->query_categories();
?>
<div class='wrap'>
	<h2><?php _e('Categories', AEC_PLUGIN_NAME); ?></h2>
	<h5><?php _e('Add a new, or edit/delete an existing calendar category.  To change the category tile color, click the color swatch or edit the field containing the hex value, then click Update.  The foreground color (black or white) is automatically assigned for optimal readbility based on the selected background color.', AEC_PLUGIN_NAME); ?></h5>
	<form id="aec-category-form">
	<p>
	<input type="hidden" id="fgcolor" name="fgcolor" class="fg" value="#FFFFFF" />
	<input class="bg colors" type="text" id="bgcolor" name="bgcolor" value="#005294" size="7" maxlength="7" autocomplete="off">
	<input type="text" id="category" name="category" value="" /> 
	<button class="add button-primary"><?php _e('Add', AEC_PLUGIN_NAME); ?></button>
	</p>
	</form>
	<form id="aec-category-list">
<?php
	$out = '';
	foreach ($categories as $category) {
		$delete = ($category->id > 1) ? 
			'<button class="button-secondary delete">' . __('Delete', AEC_PLUGIN_NAME) . '</button>' . "\n" : 
			' <em>' . __('This category is required and can only be edited.', AEC_PLUGIN_NAME) . '</em>';
		$out .= '<p id="id_' . $category->id . '">' . "\n";
		$out .= '<input type="hidden" name="id" value="' . $category->id . '" />' . "\n";
		$out .= '<input type="hidden" name="fgcolor" value="#' . $category->fgcolor . '" class="fg" />' . "\n";
		$out .= '<input type="text" name="bgcolor" size="7" maxlength="7" autocomplete="off" value="#' . $category->bgcolor . '" class="bg colors" />' . "\n";
		$out .= '<input type="text" name="category" value="' . htmlentities(stripslashes($category->category)) . '" class="edit" />' . "\n";
		$out .= '<button id="category_update" class="update button-secondary">' . __('Update', AEC_PLUGIN_NAME) . '</button>' . "\n";	
		$out .= $delete;
		$out .= '</p>' . "\n";
	}

	echo $out;
?>
	</form>
</div>