<?php

/**
 * Game Server Monitor
 *
 * Posts notifications when one of the monitored servers has players on it
 *
 * @version 1.0
 */

chdir(__DIR__);

require_once 'vendor/autoload.php';
require_once 'lib/autoload.php';

$db = new SQLite3('data/gasemon.db');
$ini = parse_ini_file('config.ini');

$tweetBot = new gasemon\AhlTweetBot(
    $ini['tweetbot.cooldown'],
    $ini['tweetbot.minimumPlayerCount'],
    $ini['tweetbot.consumerKey'],
    $ini['tweetbot.consumerSecret'],
    $ini['tweetbot.accessToken'],
    $ini['tweetbot.accessSecret']
);

$gm = new gasemon\ServerMonitor($db, $tweetBot, 'source');

$gm->runCheck();
