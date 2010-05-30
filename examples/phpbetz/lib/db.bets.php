<?php
class bets extends dbo {
    function games($user) {
        $sql =<<< 'EOT'
    SELECT
        g.id AS id,
        g.time AS time,
        g.home AS home,
        g.road AS road,
        b.score as score,
        b.user
    FROM
        games AS g
    LEFT OUTER JOIN
        gamebets AS b
    ON
        g.id = b.game AND b.user = :user
    ORDER BY
        time;
EOT;
        $stm = $this->db->prepare($sql);
        $stm->bindValue(':user', $user, SQLITE3_TEXT);
        $res = $stm->execute();
        $games = array();
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) $games[] = $row;
        $res->finalize();
        $stm->close();
        return $games;
    }
}