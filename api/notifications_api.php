<?php
// ============================================================
//  Notifications API — JSON endpoint
//  GET  ?action=unread          → list unread notifications
//  POST action=mark_read, id=X  → mark one as read (CSRF required)
// ============================================================
require_once __DIR__ . '/../includes/functions.php';
startSession();

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/DataService.php';
$sheets  = getDataService();
$myEmpId = $_SESSION['employee_id'];
$action  = $_GET['action'] ?? $_POST['action'] ?? '';

// ── Read unread notifications (GET) ─────────────────────────
if ($action === 'unread' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $notifs = $sheets->findMany(SHEET_NOTIFICATIONS, 'User_ID', $myEmpId);
    $unread = array_values(array_filter($notifs, fn($n) => ($n['Read_Status'] ?? '') === 'unread'));
    $unread = array_slice(array_reverse($unread), 0, 10);

    echo json_encode([
        'count'         => count($unread),
        'notifications' => array_map(fn($n) => [
            'id'          => $n['Notif_ID'],
            'message'     => $n['Message'],
            'type'        => $n['Type'],
            'read_status' => $n['Read_Status'],
            'created_at'  => $n['Created_At'],
        ], $unread),
    ]);
    exit;
}

// ── Mark as read (POST with CSRF) ────────────────────────────
if ($action === 'mark_read' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $id = $_POST['id'] ?? '';
    if ($id) {
        $notif = $sheets->findOne(SHEET_NOTIFICATIONS, 'Notif_ID', $id);
        if ($notif && ($notif['User_ID'] ?? '') === $myEmpId) {
            $sheets->updateById(SHEET_NOTIFICATIONS, 'Notif_ID', $id, ['Read_Status' => 'read']);
        }
    }
    echo json_encode(['ok' => true]);
    exit;
}

echo json_encode(['error' => 'Unknown action']);
