<?php

use GuzzleHttp\Client;

require_once __DIR__ . '/vendor/autoload.php';

$city = $_REQUEST['city'] ?? '';

//校验参数
if (!$city) {
    echo json_encode(['code' => 1, 'msg' => '参数错误', 'data' => []]);
    die();
}

try {
    $res = (new Client())->get('http://jisutqybmf.market.alicloudapi.com/weather/query?city=' . $city, ['headers' => ['Authorization' => 'APPCODE 8529b3e2d15e4851a197229cfea62097']]);
    if ($res->getStatusCode() == 200) {
        $jsonData = json_decode($res->getBody()->getContents(), true);
        if (!empty($jsonData['result'])) {
            echo json_encode(['code' => 0, 'msg' => 'ok', 'data' => $jsonData['result']]);
	    die();
        }
    }
} catch (\Exception $e) {
    //
}

echo json_encode(['code' => 1, 'msg' => 'fail', 'data' => []]);
