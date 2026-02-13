<?php

/**
 * Router pour le serveur PHP intégré (php -S).
 * Redirige toutes les requêtes vers index.php pour que Symfony gère le routing.
 * 
 * Utilisation: php -S localhost:8000 -t public public/router.php
 * Ou avec symfony CLI: symfony serve (gère le routing automatiquement)
 */
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Si le fichier existe physiquement (css, js, images...), le servir
if ($path !== '/' && $path !== '' && file_exists(__DIR__ . $path)) {
    $realPath = realpath(__DIR__ . $path);
    if ($realPath !== false && str_starts_with($realPath, __DIR__) && is_file($realPath)) {
        return false; // Serve le fichier tel quel
    }
}

// Sinon, transmettre à Symfony
$_SERVER['SCRIPT_NAME'] = '/index.php';
require __DIR__ . '/index.php';
