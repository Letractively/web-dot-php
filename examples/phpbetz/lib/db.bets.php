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
        g.id = b.game AND b.user = ?
    ORDER BY
        time;
EOT;
        $sql = $this->db->prepare($sql);
        $sql->execute(array($user));
        $sql->fetchAll(PDO::FETCH_ASSOC);
    }
}