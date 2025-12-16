<?php
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

session_start();
$client = unserialize($_SESSION['google_client']);

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token);
    
 // Save token (e.g., database or session)
    $_SESSION['google_token'] = $token;
    
 // Redirect to app
    header('Location: /default.php');
    exit;
} else {
    die("Authorization failed.");
}
?>