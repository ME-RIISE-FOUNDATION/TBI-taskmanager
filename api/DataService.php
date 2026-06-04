<?php
// ============================================================
//  DataService — singleton factory; auto-selects Local or Sheets
// ============================================================

require_once __DIR__ . '/../config/config.php';

function isGoogleSheetsConfigured(): bool
{
    return file_exists(CREDENTIALS_PATH)
        && SPREADSHEET_ID !== 'YOUR_SPREADSHEET_ID_HERE';
}

function getDataService(): LocalDataService|GoogleSheetsService
{
    static $instance = null;
    if ($instance !== null) return $instance;

    if (isGoogleSheetsConfigured()) {
        require_once __DIR__ . '/GoogleSheetsService.php';
        $instance = new GoogleSheetsService();
    } else {
        require_once __DIR__ . '/LocalDataService.php';
        $instance = new LocalDataService();
    }
    return $instance;
}
