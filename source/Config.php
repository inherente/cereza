<?php
/**
 * Shared configuration loaders.
 *
 * - getDatabaseConfig(): the [database] section of config/db.ini
 * - getGoogleConfig():   the [google] section of config/google.ini
 *
 * Both files are gitignored and contain secrets; committable *.example
 * templates are kept beside them.
 */

function getDatabaseConfig($file = null) {
    if ($file === null) {
        $file = __DIR__ . '/../config/db.ini';
    }
    if (!file_exists($file)) {
        http_response_code(500);
        die(json_encode(['error' => 'Configuration file not found.']));
    }
    return parse_ini_file($file, true)['database'];
}

function getGoogleConfig($file = null) {
    if ($file === null) {
        $file = __DIR__ . '/../config/google.ini';
    }
    if (!file_exists($file)) {
        http_response_code(500);
        die(json_encode(['error' => 'Google configuration file not found.']));
    }
    return parse_ini_file($file, true)['google'];
}
