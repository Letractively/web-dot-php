<?php

get('/install', function() {
    $db = new db;
    $db->install->tables();
    $db->install->teams();
    $db->install->games();
    $db = null;
});
