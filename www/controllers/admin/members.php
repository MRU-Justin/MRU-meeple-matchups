<?php

require path_to('queries/DatabaseQueries.php');

$queries = new DatabaseQueries();
$members = $queries->all_members();

view('admin/members', ['members' => $members]);
