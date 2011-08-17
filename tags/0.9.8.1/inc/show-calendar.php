<div id="aec-container">
	<div id="aec-loading"><?php _e('Loading...', AEC_PLUGIN_NAME); ?></div>
	<div id="aec-modal">
		<div class="aec-title"></div>
		<div class="aec-content"></div>
	</div>
	<div id="aec-header">
		<?php
		$options = get_option('aec_options');
		if ($options['menu']) {
			$out = '<div id="aec-menu">';
			$out .= '<a href="' . admin_url() . 'admin.php?page=ajax-event-calendar.php">' . __('Add Events', AEC_PLUGIN_NAME) . '</a>';
			$out .= '</div>';
			echo $out;
		}
		?>
		<ul id="aec-filter">
		<?php
			$categories = $this->get_categories();
			if (sizeof($categories) > 1) {
				$out = '<li>' . __('Show Types', AEC_PLUGIN_NAME) . '</li>' . "\n";
				$out .= '<li class="active"><a class="round5 all">' . __('All', AEC_PLUGIN_NAME) . '</a></li>' . "\n";
				foreach ($categories as $category) {
					 $out .= '<li><a class="round5 cat' . $category->id . '">' . $category->category . '</a></li>' . "\n";
				}
				echo $out;
			}
		?>
		</ul>
	</div>
	<div id="aec-calendar"></div>
	<?php echo '<a href="http://eranmiller.com/" id="aec-credit">' . AEC_PLUGIN_NAME . ' v' . AEC_PLUGIN_VERSION . ' ' . __('Created By', AEC_PLUGIN_NAME) . ' Eran Miller</a>'; ?>
</div>