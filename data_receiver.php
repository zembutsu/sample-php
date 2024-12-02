<?php
// data_receiver.php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Access-Control-Allow-Origin: *');
header('Connection: keep-alive');

// ファイルからデータを読み取る
$filename = 'sensor_data.txt';

while (true) {
    if (file_exists($filename)) {
        $data = file_get_contents($filename);
        if ($data) {
            echo "data: " . $data . "\n\n";
            unlink($filename); // データを送信したらファイルを削除
        }
    }

    ob_flush();
    flush();
    sleep(1); // 1秒待機
}
?>
