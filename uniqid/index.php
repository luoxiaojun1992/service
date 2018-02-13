<?php

use GuzzleHttp\Client;

require_once __DIR__ . '/vendor/autoload.php';

try {
    $res = (new Client())->get('http://127.0.0.1:9049/?type=1');
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
