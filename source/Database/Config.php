<?php
/**
 * Database configuration loader.
 *
 * Returns the [database] section of config/db.ini. The file is gitignored
 * (it contains DB credentials); a committable db.ini.example is kept beside it.
 *
 * @param string|null $file
 * @return array The [database] section of the INI file.
 */
function getDatabaseConfig($file = null) {
    if ($file === null) {
        $file = __DIR__ . '/../../config/db.ini';
    }
    if (!file_exists($file)) {
        http_response_code(500);
        die(json_encode(['error' => 'Configuration file not found.']));
    }
    return parse_ini_file($file, true)['database'];
}
