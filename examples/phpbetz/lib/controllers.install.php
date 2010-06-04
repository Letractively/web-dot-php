<?php

get('/install', function() {
    db\install\tables();
    db\install\views();
    db\install\teams();
    db\install\games();
});

get('/install/views', function() {
    db\install\views();
});

get('/install/admins', function() {
    db\install\admins();
});

get('/install/draw', function() {
    db\install\add_draw_to_games();
});

get('/install/paid', function() {
    db\install\add_paid_to_users();
});