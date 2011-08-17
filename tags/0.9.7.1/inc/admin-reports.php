<div class="wrap">
<h2><?php _e('Activity Report', AEC_PLUGIN_NAME); ?></h2>
<p><?php _e('Number of events scheduled for the current month, by type:', AEC_PLUGIN_NAME); ?></p>
<?php
$rows = $this->report_monthly_activity();
if ( count( $rows ) ) {
	foreach ( $rows as $row ) {
		echo '<p><strong>' . $row->cnt . '</strong> "' . $row->category . '" ';
		echo _n('Event', 'Events', $row->cnt, AEC_PLUGIN_NAME);
		echo '</p>';
	}
} else {
	echo '<p><em>' . __('No events this month.', AEC_PLUGIN_NAME) . '</em></p>';
}
?>
</div>