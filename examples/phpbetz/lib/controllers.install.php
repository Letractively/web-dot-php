<?php
get('/install', function() {
    db\install\tables();
    db\install\triggers();
    db\install\views();
    db\install\teams();
    db\install\games();
});

get('/install/admins', function() {
    db\install\admins();
});