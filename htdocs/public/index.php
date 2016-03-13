<?php

require_once('pm/Loader.php');
pm_Loader::registerAutoload();
pm_Bootstrap::init();

// Authentication
if (!isset($_GET['authToken']) || $_GET['authToken'] !== pm_Settings::get('authToken'))
{
    Modules_Harvard_Helper::error('Invalid auth token supplied.', 403);
}

// Check if the request has the right form.
if ($_SERVER['REQUEST_METHOD'] !== 'POST')
{
    Modules_Harvard_Helper::error('Invalid request.', 405);
}
if ($_SERVER['CONTENT_TYPE'] !== 'application/json')
{
    Modules_Harvard_Helper::error('Invalid request.', 400);
}

// Decode the posted JSON file.
$jsonData = json_decode(file_get_contents('php://input'), true);

$receiver = new Modules_Harvard_Receiver();
$receiver->processJson($jsonData);
