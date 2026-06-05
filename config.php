<?php
// =============================================
// includes/config.php — Configuration globale
// =============================================

$script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
$parts  = explode('/', trim($script, '/'));
define('BASE', '/' . $parts[0]);

function url(string $path = ''): string {
    return BASE . '/' . ltrim($path, '/');
}
