<?php

namespace CtCloudBackend\Common;


class C
{
    const DATABASE_TYPE_MYSQL = 'mysql';
    const DATABASE_TYPE_SQLITE = 'sqlite';
    const SELECT_DATABASE_TYPE = self::DATABASE_TYPE_SQLITE;
    const GET_REAL_LINK_AUTH_CODE = 'ct-cloud';
    const CTCLOUD_ROOT_DIR_ID = '7138134155848552';
    const BROWSER_USER_AGENT = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.119 Safari/537.36';
    const SQLITE_FILE_PATH = __DIR__ . '/../../sqlite-data/';
    const CTCLOUD_SQLITE_DATA_FILE = 'ctyun_api.sqlite';
}