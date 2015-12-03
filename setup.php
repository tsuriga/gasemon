<?php

if (!is_dir('data')) {
    mkdir('data');
}

if (!file_exists('data/gasemon.db')) {
    $db = new SQLite3('data/gasemon.db');

    $db->query(<<<'EOQ'
CREATE TABLE server(
    addr TEXT
)
EOQ
    );
}
