<?php

use Swoole\Http\Server;

$config = require_once __DIR__ . '/config.php';

define('REDIS_PREFIX', 'countService_');

$server = new Server("127.0.0.1", 9501);

/** @var \Redis $cli */
$cli = null;

$server->on('WorkerStart', function ($serv, $worker_id) use(&$cli, $config) {
    $cli = extension_loaded('connect_pool') ? new \redisProxy() : new \Redis();
    $cli->connect($config['host'], $config['port']);
    $cli->auth($config['password']);
});

$server->on("request", function ($request, $response) use ($config, &$cli) {
    $key = $request->post['key'] ?? null;
    if (!$key) {
        $response->end(json_encode(['code' => 1, 'msg' => '参数错误', 'data' => []], JSON_UNESCAPED_UNICODE));
    }

    //利用redis的hash数据结构减少相同前缀计数器内存消耗
    $secondKeys = $request->post['sec_key'] ?? 'default';
    if (!is_array($secondKeys)) {
        $secondKeys = [$secondKeys];
    }

    $step = $request->post['step'] ?? 1;

    $op = $request->post['op'] ?? 'incrBy';
    if (!in_array($op, ['incrBy', 'decrBy', 'del', 'get'])) {
        $response->end(json_encode(['code' => 1, 'msg' => '参数错误', 'data' => []], JSON_UNESCAPED_UNICODE));
    }
    $op = 'h' . ucfirst($op);

    //批量原子操作
    $cli->multi(\Redis::PIPELINE);
    foreach ($secondKeys as $secondKey) {
        if (in_array($op, ['hIncrBy', 'hDecrBy'])) {
            $cli->{$op}(REDIS_PREFIX . $key, $secondKey, $step);
        } else {
            $cli->{$op}(REDIS_PREFIX . $key, $secondKey);
        }
    }
    $counts = $cli->exec();

    //格式化
    foreach ($counts as $k => $count) {
        $counts[$k] = intval($count);
    }

    //释放连接到连接池
    if (extension_loaded('connect_pool')) {
        $cli->release();
    }

    $response->end(json_encode(['code' => 0, 'msg' => 'ok', 'data' => ['count' => count($counts) > 1 ? $counts : $counts[0]]]));
});

$server->on('WorkerStop', function ($serv, $worker_id) use(&$cli) {
    $cli->close();
});

$server->start();
