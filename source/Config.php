<?php
/**
 * Shared database configuration loader.
 *
 * Returns the [database] section of config/db.ini. Every DB-using entry point
 * should call getDatabaseConfig() instead of re-defining its own loader, so the
 * connection config lives in exactly one place.
 *
 * @param string|null $file INI path; defaults to <repo>/config/db.ini
 * @return array The [database] section of the INI file
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
