<?php

require path_to('queries/DatabaseQueries.php');


//Query for which month using Calgary time.
$current_time = new DateTime('now', new DateTimeZone('America/Edmonton'));
$this_month = $current_time->format('Y-m');
$last_month = (clone $current_time)->modify('first day of last month')->format('Y-m');



$queries = new DatabaseQueries();
$plays_this_month = $queries->plays_in_month($this_month);
$plays_last_month = $queries->plays_in_month($last_month);

view('admin/dashboard', [
    'plays_this_month' => $plays_this_month,
    'plays_last_month' => $plays_last_month,
]);
