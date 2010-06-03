<?php

get('/install', function() {
    db\install\tables();
    db\install\teams();
    db\install\games();
});
