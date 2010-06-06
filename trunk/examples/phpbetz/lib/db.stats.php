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
                username ASC
SQL;
        
        $db = new \SQLite3(database, \SQLITE3_OPEN_READONLY);
        $res = $db->query($sql);
        $i = 0;
        $j = 0;
        $total = -1;
        $position = 0;
        $rowspan = 0;
        $points = array();
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
            if ($total != $row['total_points']) {
                $total =  $row['total_points'];
                if ($i != 0) {
                    $points[$j]['rowspan'] = $rowspan;
                }
                $j = $i;
                $rowspan = 0;
                $position++;
            }
            $row['position'] = $position;
            $row['rowspan'] = $rowspan;
            $points[] = $row;
            $rowspan++;
            $i++;
        }
        $points[$j]['rowspan'] = $rowspan;
        $res->finalize();
        $db->close();
        
        return $points;
    }
}
