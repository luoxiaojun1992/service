<?php

$config = require_once __DIR__ . '/config.php';

$host = $_REQUEST['host'] ?? '';
$uri = $_REQUEST['uri'] ?? '';
$protocol = $_REQUEST['protocol'] ?? 'http';
$port = intval($_REQUEST['port'] ?? 80);
$method = $_REQUEST['method'] ?? 'GET';
$data = $_REQUEST['data'] ?? '';
$time = doubleval($_REQUEST['time'] ?? microtime(true)) * 10000;
$headers = $_REQUEST['headers'] ?? '';

//校验参数
if (!$host) {
    echo json_encode(['code' => 1, 'msg' => '参数错误', 'data' => []]);
    die();
}
if (!$uri) {
    echo json_encode(['code' => 1, 'msg' => '参数错误', 'data' => []]);
    die();
}
if (!$protocol) {
    echo json_encode(['code' => 1, 'msg' => '参数错误', 'data' => []]);
    die();
}
if (!$port) {
    echo json_encode(['code' => 1, 'msg' => '参数错误', 'data' => []]);
    die();
}
if (!$method) {
    echo json_encode(['code' => 1, 'msg' => '参数错误', 'data' => []]);
    die();
}
if (!$time) {
    echo json_encode(['code' => 1, 'msg' => '参数错误', 'data' => []]);
    die();
}

try {
    if (extension_loaded('connect_pool')) {
        $pdo = new \pdoProxy($config['dsn'], $config['username'], $config['password'], [
            'charset' => 'utf8',
        ]);
    } else {
        $pdo = new \PDO($config['dsn'], $config['username'], $config['password'], [
            'charset' => 'utf8',
        ]);
    }
} catch (\Exception $e) {
    echo json_encode(['code' => 1, 'msg' => 'fail', 'data' => []]);
    die();
}

try {
    $sql = <<<EOF
INSERT INTO `request_log` (`request_log_host`, `request_log_uri`, `request_log_protocol`, `request_log_port`, `request_log_method`, `request_log_data`, `request_log_headers`, `request_log_time`) VALUES (
  :host,
  :uri,
  :protocol,
  :port,
  :method,
  :data,
  :headers,
  :time
)
EOF;

    $statement = $pdo->prepare($sql);

    $statement->bindValue(':host', $host, \PDO::PARAM_STR);
    $statement->bindValue(':uri', $uri, \PDO::PARAM_STR);
    $statement->bindValue(':protocol', $protocol, \PDO::PARAM_STR);
    $statement->bindValue(':port', $port, \PDO::PARAM_INT);
    $statement->bindValue(':method', $method, \PDO::PARAM_STR);
    $statement->bindValue(':data', $data, \PDO::PARAM_STR);
    $statement->bindValue(':headers', $headers, \PDO::PARAM_STR);
    $statement->bindValue(':time', $time, \PDO::PARAM_INT);

    if (!$statement->execute()) {
        throw new \Exception('数据库插入失败');
    }

    echo json_encode(['code' => 0, 'msg' => 'ok', 'data' => []]);
} catch (\Exception $e) {
    echo json_encode(['code' => 1, 'msg' => 'fail', 'data' => []]);
}

if (extension_loaded('connect_pool')) {
    $pdo->release();
}
