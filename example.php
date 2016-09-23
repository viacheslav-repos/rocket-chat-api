<?php

/**
 * @file
 * This file holds example commands for reading, creating, updating and deleting redmine components.
 */

// As this is only an example file, we make sure, this is not accidently executed and may destroy real
// life content.
return;

require_once 'vendor/autoload.php';

// ----------------------------
// Instanciate a redmine client
// --> with ApiKey
$client = new RocketChat\Client('http://chat.example.com');

print_r($client->api('user')->login('me','mypassword'));
