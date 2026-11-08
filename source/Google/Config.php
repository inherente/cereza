<?php
/**
 * Google OAuth configuration loader.
 *
 * Returns the [google] section of config/google.ini (client_id, client_secret,
 * redirect_uri, calendar_id). The file is gitignored; a committable
 * google.ini.example is kept beside it.
 *
 * @param string|null $file
 * @return array The [google] section of the INI file.
 */
function getGoogleConfig($file = null) {
    if ($file === null) {
        $file = __DIR__ . '/../../config/google.ini';
    }
    if (!file_exists($file)) {
        http_response_code(500);
        die(json_encode(['error' => 'Google configuration file not found.']));
    }
    return parse_ini_file($file, true)['google'];
}
