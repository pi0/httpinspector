<?php
$file = __FILE__;
$last_modified_time = filemtime($file);
$etag = md5_file($file);

header("Last-Modified: " . gmdate("D, d M Y H:i:s", $last_modified_time) . " GMT");
header("Etag: $etag");

$currentCookieParams = session_get_cookie_params();

$rootDomain = '.pi0.ir';

session_start();
session_set_cookie_params(
    $currentCookieParams["lifetime"],
    $currentCookieParams["path"],
    $rootDomain,
    $currentCookieParams["secure"],
    $currentCookieParams["httponly"]
);



setcookie("Storm", "Havij", time() + 3600, "/test/", $rootDomain);
setcookie("Light", "Moz", time() + 3610, "/test/", $rootDomain);
setcookie("Moraba", "Kivi", time() - 1000, "/test/", $rootDomain);

if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="My Realm"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Text to send if user hits Cancel button';
    exit;
} else {
    echo "<p>Hello {$_SERVER['PHP_AUTH_USER']}.</p>";
    echo "<p>You entered {$_SERVER['PHP_AUTH_PW']} as your password.</p>";
}