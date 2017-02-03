<?php
$db = new SQLite3('sc.db');
$db->exec('CREATE TABLE IF NOT EXISTS registers (name VARCHAR PRIMARY KEY, value VARCHAR)')
?>
