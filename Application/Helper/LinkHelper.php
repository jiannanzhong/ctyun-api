<?php

namespace CtCloudBackend\Helper;

use CtCloudBackend\Common\C;
use CtCloudBackend\Common\D;

class LinkHelper
{
    public static function getRealLinkByFileRecord($fileRecord)
    {
        $realLink = '';
        $originLink = self::getOriginLinkByFileRecord($fileRecord);
        if (!empty($originLink)) {
            $realLink = self::getRealLinkByOriginLink($originLink);
        }

        return $realLink;
    }

    private static function mathRandom()
    {
        return (mt_rand() / mt_getrandmax() * 1) . rand(10, 99);
    }

    private static function getCtCloudDirListJson($parentDirId)
    {
        $d = D::getInstance();
        $cookie = $d->getConfig('cookie');
        if (empty($cookie)) {
            return [];
        }

        $url = 'https://cloud.189.cn/v2/listFiles.action?fileId=' . $parentDirId . '&mediaType=&keyword=&inGroupSpace=false&orderBy=1&order=ASC&pageNum=1&pageSize=60&noCache=' . self::mathRandom();
        $headerArr = [
            'Accept' => '*/*',
            'Accept-Encoding' => 'gzip',
            'Accept-Language' => 'zh-CN,zh;q=0.9',
            'Connection' => 'close',
            'Cookie' => $cookie,
            'Host' => 'cloud.189.cn',
            'Referer' => 'https://cloud.189.cn/main.action',
            'User-Agent' => C::BROWSER_USER_AGENT,
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

    private static function getDirInfo($parentDirId, $targetName)
    {
        $result = [];
        $listJson = self::getCtCloudDirListJson($parentDirId);
        if (!empty($listJson)) {
            $fileList = $listJson['data'];
            foreach ($fileList as $file) {
                if (preg_match_all('/^' . $targetName . '.*?/', $file['fileName'])) {
                    $result['fileId'] = $file['fileId'];
                    $result['downloadUrl'] = isset($file['downloadUrl']) ? $file['downloadUrl'] : '';
                    break;
                }
            }
        }

        return $result;
    }

    private static function getOriginLinkByFileRecord($fileRecord)
    {
        $originLink = '';
        $rootFileId = C::CTCLOUD_ROOT_DIR_ID;
        $result = ['fileId' => $rootFileId];
        for ($i = 0; $i <= 3; $i++) {
            if (empty($result)) {
                break;
            }
            if ($i >= 3) {
                $result = self::getDirInfo($result['fileId'], $fileRecord);
            } else {
                $result = self::getDirInfo($result['fileId'], $fileRecord[$i]);
            }
        }

        if ($result) {
            $originLink = 'https:' . $result['downloadUrl'];
        }

        return $originLink;
    }

    private static function getRealLinkByOriginLink($originLink)
    {
        $realLink = '';
        $d = D::getInstance();
        $cookie = $d->getConfig('cookie');
        $cookie2 = $d->getConfig('cookie2');

        if (empty($cookie) || empty($cookie2)) {
            return $realLink;
        }

        $redirecting = true;
        $redirectCount = 0;
        while ($redirecting && $redirectCount++ < 5) {
            $headerArr['User-Agent'] = C::BROWSER_USER_AGENT;
            if (preg_match_all('/^https:\/\/cloud.189.cn\/.*/', $originLink)) {
                $headerArr = [
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
                    'Accept-Encoding' => 'gzip',
                    'Accept-Language' => 'zh-CN,zh;q=0.9',
                    'Connection' => 'keep-alive',
                    'Cache-Control' => 'max-age=0',
                    'Cookie' => $cookie,
                    'Host' => 'cloud.189.cn',
                    'Upgrade-Insecure-Requests' => '1',
                ];
            } else {
                $headerArr = [
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
                    'Accept-Encoding' => 'gzip',
                    'Accept-Language' => 'zh-CN,zh;q=0.9',
                    'Connection' => 'keep-alive',
                    'Cookie' => $cookie2,
                    'Host' => 'download.cloud.189.cn',
                    'Upgrade-Insecure-Requests' => '1',
                ];
            }


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
            $rsp_headers = get_headers($originLink, null, $context);
            $newLoc = '';
            foreach ($rsp_headers as $header) {
                if (preg_match_all('/^Location: /', $header)) {
                    $newLoc = preg_replace('/Location: /', '', $header);
                    break;
                }
            }
            if ($newLoc !== '') {
                $realLink = $newLoc;
                if (!preg_match_all('/^https:\/\/(download\.)?cloud.189.cn\/.*/', $realLink)) {
                    break;
                }
                $originLink = $realLink;
            } else {
                break;
            }
        }
        return $realLink;
    }
}