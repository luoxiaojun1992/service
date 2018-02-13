<?php

use GuzzleHttp\Client;

require_once __DIR__ . '/vendor/autoload.php';

$date = $_REQUEST['date'] ?? date('Ymd');

//校验参数
if (!$date) {
    echo json_encode(['code' => 1, 'msg' => '参数错误', 'data' => []]);
    die();
}

try {
    $res = (new Client())->get('http://tool.bitefu.net/jiari/?d=' . $date, ['http_errors' => false]);
//    if ($res->getStatusCode() == 200) {
        $data = intval($res->getBody()->getContents());
        if (in_array($data, [0, 1, 2])) {
            echo json_encode(['code' => 0, 'msg' => 'ok', 'data' => $data]);
	    die();
        }
//    }
} catch (\Exception $e) {
    //
}

echo json_encode(['code' => 1, 'msg' => 'fail', 'data' => []]);
