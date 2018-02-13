<?php

use GuzzleHttp\Client;

require_once __DIR__ . '/vendor/autoload.php';

$ip = $_REQUEST['ip'] ?? '';

//校验参数
if (!$ip) {
    echo json_encode(['code' => 1, 'msg' => '参数错误', 'data' => []]);
    die();
}

try {
    $res = (new Client())->get('http://freeapi.ipip.net/' . $ip);
    if ($res->getStatusCode() == 200) {
        $jsonData = json_decode($res->getBody()->getContents(), true);
        if (!empty($jsonData)) {
            echo json_encode(['code' => 0, 'msg' => 'ok', 'data' => $jsonData]);
	    die();
        }
    }
} catch (\Exception $e) {
    //
}

echo json_encode(['code' => 1, 'msg' => 'fail', 'data' => []]);
