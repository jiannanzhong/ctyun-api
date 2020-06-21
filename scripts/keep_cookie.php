<?php

require __DIR__ . '/../vendor/autoload.php';

use Medoo\Medoo;
use CtCloudBackend\Common\C;

$db = new Medoo([
    'database_type' => 'sqlite',
    'database_file' => C::SQLITE_FILE_PATH . C::CTCLOUD_SQLITE_DATA_FILE,
    'option' => []
]);

$parentDirId = '2138134109615622';
$json = getCtCloudDirListJson($db, $parentDirId);
print_r($json);

function getConfig(Medoo $db, $key)
{
    try {
        $config = $db->get(
            'config', 'value', ['name' => $key]
        );
        if (empty($config)) {
            $config = '';
        }
        return $config;
    } catch (\Exception $e) {
        return '';
    }
}

function getCtCloudDirListJson($db, $parentDirId)
{
    $cookie = getConfig($db, 'cookie');
    if (empty($cookie)) {
        return [];
    }

    $url = 'https://cloud.189.cn/v2/listFiles.action?fileId=' . $parentDirId . '&mediaType=&keyword=&inGroupSpace=false&orderBy=1&order=ASC&pageNum=1&pageSize=60&noCache=' . mathRandom();
    $headerArr = [
        'Accept' => '*/*',
        'Accept-Encoding' => 'gzip',
        'Accept-Language' => 'zh-CN,zh;q=0.9',
        'Connection' => 'close',
        'Cookie' => $cookie,
        'Host' => 'cloud.189.cn',
        'Referer' => 'https://cloud.189.cn/main.action',
        'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.119 Safari/537.36',
        'X-Requested-With' => 'XMLHttpRequest'
    ];
    $headerStr = '';
    foreach ($headerArr as $headerKey => $headerValue) {
        $headerStr .= $headerKey . ': ' . $headerValue . "\r\n";
    }
    $opts = array('http' =>
        array(
            'method' => 'GET',
            'header' => $headerStr,
        )
    );
    $context = stream_context_create($opts);
    $jsonRsp = file_get_contents($url, true, $context);
    $json = json_decode($jsonRsp, true);
    if (empty($json)) {
        $json = [];
    }
    return $json;
}

function mathRandom()
{
    return (mt_rand() / mt_getrandmax() * 1) . rand(10, 99);
}