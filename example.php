<?php

/**
 * @file
 * This file holds example commands for reading, creating, updating and deleting redmine components.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'lib/autoload.php';

// ----------------------------
// Instanciate a redmine client
// --> with ApiKey
$client = new RocketChat\Client('http://chat.mydomain.com/');

$token = $client->api('user')->login('','');

if ( $token)
{
    $client->setToken($token);

    $channel_result = $client->api('channel')->create('first-channel-from-api-3');
    if ($channel_result)
    {
        print_r($channel_result);
        print_r($client->api('channel')->getMessage());
    }
    else
    {
        print_r($client->api('channel')->getMessage());
    }
}
else {
    print_r($client->api('user')->getMessage());
}
