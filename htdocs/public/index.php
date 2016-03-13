<?php

require_once('pm/Loader.php');
pm_Loader::registerAutoload();
pm_Bootstrap::init();

// TODO: Handle authentication

// TODO: Check if valid request exists

// Decode the posted JSON file.
$jsonData = json_decode(file_get_contents('php://input'), true);

$receiver = new Modules_Harvard_Receiver();
$receiver->processJson($jsonData);
