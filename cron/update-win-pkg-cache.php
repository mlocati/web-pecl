#!/usr/local/bin/php -Cq
<?php

/*
  +----------------------------------------------------------------------+
  | The PECL website                                                     |
  +----------------------------------------------------------------------+
  | Copyright (c) 1999-2018 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.01 of the PHP license,      |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | https://php.net/license/3_01.txt                                     |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Authors: Anatol Belski <ab@php.net>                                  |
  +----------------------------------------------------------------------+
*/

require_once __DIR__ . '/../include/pear-prepend.php';

$dbh = DB::connect("mysql://pear:pear@localhost/pear");
if (DB::isError($dbh)) {
    die("could not connect to database");
}
$dbh->query('SET NAMES utf8');

$data = $dbh->getAll("SELECT packages.name, releases.version, releases.releasedate
                        FROM packages, releases
                        WHERE packages.id = releases.package",
                    NULL,
                    DB_FETCHMODE_ASSOC);

if (PackageDll::isResetOverdue()) {
    if (!PackageDll::resetDllDownloadCache()) {
        /* some reset running, just sleep and do our thing*/
        sleep(10);
    }
}

foreach ($data as $pkg) {
    if (!PackageDll::updateDllDownloadCache($pkg['name'], $pkg['version'])) {
        echo "Failed to update cache for $pkg[name]-$pkg[version]\n";
    }
}
