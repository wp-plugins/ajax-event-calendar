<div class="wrap">
<h2>Activity Report</h2>
<?php
$rows = $this->report_monthly_activity();
if ( count( $rows ) ) {
	foreach ( $rows as $row ) {
		echo '<strong>' . $row->cnt . '</strong>: ' . $row->category . $this->plural($row->cnt) . '<br>';
	}
} else {
	echo 'No events this month';
}
?>