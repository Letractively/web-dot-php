<?php
namespace db\stats {
    function points() {
        $sql =<<< 'SQL'
        SELECT
            u.username               AS username,
            COALESCE(SUM(points), 0) AS game_points,
            u.winner                 AS winner,
            u.winner_abbr            AS winner_abbr,
            u.winner_points          AS winner_points,
            u.second                 AS second,
            u.second_abbr            AS second_abbr,
            u.second_points          AS second_points,
            u.third                  AS third,
            u.third_abbr             AS third_abbr,
            u.third_points           AS third_points,
            u.winner_points + u.second_points + u.third_points AS team_points,
            u.scorer                 AS scorer,
            u.scorer_betted          AS scorer_betted,
            u.scorer_points          AS scorer_points,
            COALESCE(SUM(points), 0) + u.winner_points + u.second_points + u.third_points + u.scorer_points AS total_points
            FROM
                view_users u
            LEFT OUTER JOIN
                gamebets g
            ON
                u.username = g.user
            GROUP BY
                u.username,
                u.winner,
                u.winner_abbr,
                u.winner_points,
                u.second,
                u.second_abbr,
                u.second_points,
                u.third,
                u.third_abbr,
                u.third_points,
                u.scorer,
                u.scorer_betted,
                u.scorer_points
            ORDER BY
                total_points DESC,
                username DESC
SQL;
        
        $db = new \SQLite3(database, \SQLITE3_OPEN_READONLY);
        $res = $db->query($sql);
        $points = array();
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) $points[] = $row;
        $res->finalize();
        $db->close();
        return $points;
    }
}
