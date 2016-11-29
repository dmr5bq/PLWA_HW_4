<?php

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'PLWA_HW_4';

$data_filename = 'init.txt';

$_delim = ":\n";

$db_root = new mysqli($db_host, $db_user, $db_pass);

$db_root->query("
    DROP DATABASE $db_name;
") or die ($db_root->error);

$db_root->query("
    CREATE DATABASE $db_name;
") or die ($db_root->error);

$database = new mysqli($db_host, $db_user, $db_pass, $db_name);

$database->query("
    CREATE TABLE Words (id int NOT NULL PRIMARY KEY AUTO_INCREMENT, word varchar(40) NOT NULL);
")or die ($database->error);

$file_str = file_get_contents($data_filename);

$words = explode($_delim, $file_str);

foreach ($words as $word):

    $database->query("
        INSERT INTO Words (word) VALUES ('$word');
    ") or die($database->error);

endforeach;

