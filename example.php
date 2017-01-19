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
$client = new RocketChat\Client('http://chat.somechat.eu/');

$token = $client->api('user')->login('','');

if ( $token)
{
    $client->setToken($token);

    $room_name = 'test-channel-from-api-'.mt_rand(10000000, 99999999);

    $channel_result = $client->api('channel')->create($room_name, ['Bubu']);
    if ($channel_result)
    {
        print_r($channel_result);
        print_r($client->api('channel')->getMessage());
    }
    else
    {
        print_r($client->api('channel')->getMessage());
    }

    $channel_result = $client->api('channel')->listRooms();
    if ($channel_result)
    {
        print_r($channel_result);
        print_r($client->api('channel')->getMessage());
    }
    else
    {
        print_r($client->api('channel')->getMessage());
    }

    $channel_result = $client->api('channel')->findByName($room_name);
    if ($channel_result)
    {
        print_r($channel_result);
        print_r($client->api('channel')->getMessage());
    }
    else
    {
        print_r($client->api('channel')->getMessage());
    }

    $roomId = $channel_result->_id;
    $channel_result = $client->api('channel')->setArchived($roomId,true);
    if ($channel_result)
    {
        print_r($channel_result);
        print_r($client->api('channel')->getMessage());
    }
    else
    {
        print_r($client->api('channel')->getMessage());
    }

    $channel_result = $client->api('channel')->sendMessage($roomId,'wake me up before you go');
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
