<?php

require path_to('core/DatabaseHelper.php');

// Where your database queries live. Add a method per question you want to
// ask the database, and call it from a controller:
//
//     public function all_albums()
//     {
//         return $this->db_helper
//             ->run("SELECT * FROM albums ORDER BY released")
//             ->fetchAll();
//     }
//
// Values always go in as parameters, never glued into the SQL string:
//
//     public function albums_by_artist($artist_id)
//     {
//         return $this->db_helper
//             ->run("SELECT * FROM albums WHERE artist_id = ?", [$artist_id])
//             ->fetchAll();
//     }
class DatabaseQueries
{
    private $db_helper;

    public function __construct()
    {
        $config = require path_to('config/database.php');
        $this->db_helper = new DatabaseHelper($config);
    }

    public function all_members()
    {
        return $this->db_helper
            ->run("SELECT m.id,
                          m.first_name,
                          m.last_name,
                          m.email,
                          m.cell_phone,
                          m.plan_type,
                          m.enrolled_on,
                          COUNT(pp.play_id) AS total_plays
                   FROM members m
                   LEFT JOIN play_players pp ON pp.member_id = m.id
                   GROUP BY m.id
                   ORDER BY m.last_name, m.first_name")
            ->fetchAll();
    }
}
