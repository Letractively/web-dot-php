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