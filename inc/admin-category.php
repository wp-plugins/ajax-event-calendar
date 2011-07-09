<?php	
	$categories = $this->get_categories();
?>
<div class='wrap'>
	<h2><?php _e('Categories', AEC_PLUGIN_NAME); ?></h2>
	<p><?php _e('Add new, or edit existing category type (and associated calendar tile color).', AEC_PLUGIN_NAME); ?></p>
	<form id="aec-category-form">
	<ul>
		<li>
			<input type="hidden" id="bgcolor" name="bgcolor" class="bg colors" value="#ABCABC" /> 
			<input type="hidden" id="fgcolor" name="fgcolor" class="fg" value="#FFFFFF" />
			<input type="text" id="category" name="category" value="" /> 
			<button class="add button-primary"><?php _e('Add', AEC_PLUGIN_NAME); ?></button>
		</li>
	</ul>
	</form>

	<ol id="aec-category-table">
<?php
		$out = '';
		foreach ($categories as $category) {
			$delete = ($category->id > 1) ? 
				'<a class="delete">' . __('Delete', AEC_PLUGIN_NAME) . '</a>' . "\n" : 
				' <em>' . __('This category is required and can only be edited.', AEC_PLUGIN_NAME) . '</em>';
			$out .= '<li id="id_' . $category->id . '">' . "\n";
			$out .= '<input type="hidden" name="bgcolor" value="#' . $category->bgcolor . '" class="bg colors" />' . "\n";
			$out .= '<input type="hidden" name="fgcolor" value="#' . $category->fgcolor . '" class="fg" />' . "\n";
			$out .= '<input type="text" name="category" value="' . $category->category . '" class="edit" />' . "\n";
			$out .= '<span><button class="update button-secondary">' . __('Update', AEC_PLUGIN_NAME) . '</button> ' . $delete . '</span>' . "\n";
			$out .= '</li>' . "\n";
		}
		echo $out;
?>
	</ol>
</div>