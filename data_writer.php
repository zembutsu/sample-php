<?php
// data_writer.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// POSTデータを受け取る
$input = file_get_contents('php://input');
if ($input) {
    // データをファイルに書き込む
    file_put_contents('sensor_data.txt', $input);
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No data received']);
}
?>
