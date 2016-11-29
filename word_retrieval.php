<?php


$completed_words_str = file_get_contents('php://input'); // read in the JSON data sent by the index script via AJAX

$completed_words = json_decode($completed_words_str); // decode the JSON input

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'PLWA_HW_4';

$database = new mysqli($db_host, $db_user, $db_pass, $db_name); // set up the database

$result = $database->query("
    SELECT * FROM Words;
");

// set up data arrays
$all_options = array();
$all_ids = array();
$remaining_options = array();

while ($row = mysqli_fetch_row($result)) { // convert the MySQL result to an array
    $all_options[] = $row;
    $all_ids[] = $row[0];
}

foreach ($all_ids as $id) {

    $position = $id - 1; // position and ID differ

    $id_is_completed = in_array($id, $completed_words);

    if (!$id_is_completed) {
        $remaining_options[] = $all_options[$position];
    }
}

if (count($remaining_options) != 0) {
    $rand_position = rand(0, count($remaining_options) - 1);

    echo json_encode($remaining_options[$rand_position]);
}
