<?php
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

$client = new Google\Client();
$client->setAuthConfig($_SERVER['DOCUMENT_ROOT'] . '/credentials.json');
$client->addScope(Google\Service\Calendar::CALENDAR_EVENTS);
$client->setRedirectUri('https://yoursite.com/auth/oauth2callback.php'); // Match GCP settings
$client->setAccessType('offline');
$client->setPrompt('consent');

// Save client to session for reuse
session_start();
$_SESSION['google_client'] = serialize($client);

// Generate auth URL
$authUrl = $client->createAuthUrl();
header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
exit;
?>