<?php
namespace db\stats {
    function points() {
        $sql =<<< 'SQL'
        SELECT
            u.username               AS username,
            u.paid                   AS paid,
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
                LOWER(username) ASC
SQL;
        
        $db = new \SQLite3(database, \SQLITE3_OPEN_READONLY);
        if (method_exists($db, 'busyTimeout')) $db->busyTimeout(10000);
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
                    $points[$j]['keyrow'] = true;
                    $points[$j]['rowspan'] = $rowspan;
                }
                $j = $i;
                $rowspan = 0;
                $position++;
            }
            $row['position'] = $position;
            $row['rowspan'] = $rowspan;
            $row['keyrow'] = false;
            $points[] = $row;
            $rowspan++;
            $i++;
        }
        $points[$j]['rowspan'] = $rowspan;
        $points[$j]['keyrow'] = true;
        $res->finalize();
        $db->close();
        return $points;
    }
    function games($user) {
        $sql =<<< 'SQL'
        SELECT
            g.id AS id,
            g.time AS time,
            g.home AS home,
            g.home_abbr AS home_abbr,
            g.home_goals AS home_goals,
            g.home_percent AS home_percent,
            g.road AS road,
            g.road_abbr AS road_abbr,
            g.road_goals AS road_goals,
            g.road_percent AS road_percent,
            g.draw AS draw,
            g.draw_percent AS draw_percent,
            g.score AS score,
            g.points AS points,
            b.score AS bet_score,
            b.points AS bet_points
        FROM
            view_games g
        LEFT OUTER JOIN
            gamebets b
        ON
            g.id = b.game AND b.user = :user
        WHERE
            g.time < :time
        ORDER BY
            time
SQL;

        $db = new \SQLite3(database, \SQLITE3_OPEN_READONLY);
        if (method_exists($db, 'busyTimeout')) $db->busyTimeout(10000);
        $stm = $db->prepare($sql);
        $stm->bindValue(':user', $user, SQLITE3_TEXT);
        $stm->bindValue(':time', date_format(date_create(), DATE_SQLITE), SQLITE3_TEXT);
        $res = $stm->execute();
        $games = array();
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) $games[] = $row;
        $res->finalize();
        $stm->close();
        $db->close();
        return $games;
    }
}