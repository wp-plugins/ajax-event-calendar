<div class="wrap">
<h2>Activity Report</h2>
<span>Number of events scheduled for the current month, by type</span>
<?php
$rows = $this->report_monthly_activity();
if ( count( $rows ) ) {
	foreach ( $rows as $row ) {
		echo '<p><strong>' . $row->cnt . '</strong> ' . $row->category . $this->plural($row->cnt) . '</p>';
	}
} else {
	echo 'No events this month';
}
?>
</div>