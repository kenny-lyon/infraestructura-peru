<?php
require_once __DIR__ . '/vendor/autoload.php';
session_start();

$clientID = getenv('GOOGLE_CLIENT_ID') ?: 'tu-client-id-aqui';
$clientSecret = getenv('GOOGLE_CLIENT_SECRET') ?: 'tu-client-secret-aqui';
$redirectUri = getenv('GOOGLE_REDIRECT_URI') ?: 'http://localhost:3000/proyecto_bd/google_login.php';


$adminEmails = [
    '73313424@est.unap.edu.pe',
    'jefryerickq@gmail.com',
    '73146012@est.unap.edu.pe',
    // ...otros
];

$client = new Google_Client();
$client->setClientId($clientID);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectUri);
$client->addScope('email');
$client->addScope('profile');

if (!isset($_GET['code'])) {
    // Redirigir a Google para login
    $authUrl = $client->createAuthUrl();
    header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
    exit;
} else {
    // Intercambiar el code por un token
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    if (isset($token['error'])) {
        die('Error de autenticación');
    }
    $client->setAccessToken($token['access_token']);
    $oauth2 = new Google_Service_Oauth2($client);
    $userinfo = $oauth2->userinfo->get();
    $email = $userinfo->email;

    if (in_array($email, $adminEmails)) {
        $_SESSION['admin_email'] = $email;
        header('Location: admin.php');
        exit;
    } else {
        echo 'Acceso denegado. Solo cuentas autorizadas pueden ingresar.';
        exit;
    }
}
?>