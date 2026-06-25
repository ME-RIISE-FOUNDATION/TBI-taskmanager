<?php
/**
 * Central configuration. All secrets are read from environment variables so
 * nothing sensitive needs to be committed. Works out-of-the-box (JSON-file
 * store) even with no env vars set; add SPREADSHEET_ID + credentials to switch
 * the same code to a shared Google Sheets backend.
 */

define('PROJECT_ROOT', dirname(__DIR__));
define('DATA_DIR', PROJECT_ROOT . '/data');

// Google Sheets spreadsheet id (empty => JSON-file store is used instead)
define('SPREADSHEET_ID', getenv('SPREADSHEET_ID') ?: '');

// Guards the one-time setup endpoint
define('SETUP_KEY', getenv('SETUP_KEY') ?: 'TBI_SETUP_2024');

/**
 * Resolve the Google service-account credentials file.
 * Priority: GOOGLE_CREDENTIALS_BASE64 env var -> config/credentials.json file.
 * Returns '' when no credentials are available.
 */
function tbi_credentials_path(): string {
    $b64 = getenv('GOOGLE_CREDENTIALS_BASE64');
    if ($b64) {
        $tmp = sys_get_temp_dir() . '/tbi_credentials.json';
        if (!file_exists($tmp)) {
            file_put_contents($tmp, base64_decode($b64));
        }
        return $tmp;
    }
    $file = __DIR__ . '/credentials.json';
    return file_exists($file) ? $file : '';
}

/** True when a real Google Sheets backend is configured and reachable. */
function tbi_use_sheets(): bool {
    return SPREADSHEET_ID !== '' && tbi_credentials_path() !== '';
}
