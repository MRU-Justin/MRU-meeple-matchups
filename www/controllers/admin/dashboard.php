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

$weekdays = [
    0 => ['name' => 'Sunday',    'count' => 0],
    1 => ['name' => 'Monday',    'count' => 0],
    2 => ['name' => 'Tuesday',   'count' => 0],
    3 => ['name' => 'Wednesday', 'count' => 0],
    4 => ['name' => 'Thursday',  'count' => 0],
    5 => ['name' => 'Friday',    'count' => 0],
    6 => ['name' => 'Saturday',  'count' => 0],
];

foreach ($queries->plays_by_weekday() as $row) {
    $day = (int) $row['day_num'];
    $weekdays[$day]['count'] = $row['play_count'];
}

$busiest = 0;
foreach ($weekdays as $weekday) {
    if ($weekday['count'] > $busiest) {
        $busiest = $weekday['count'];
    }
}
