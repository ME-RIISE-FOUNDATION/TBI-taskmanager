<?php
// ============================================================
//  Local JSON Database Service
//  Uses /data/*.json files. Write operations use flock() to
//  prevent corruption under concurrent requests.
// ============================================================

require_once __DIR__ . '/../config/config.php';

class LocalDataService
{
    private string $dataDir;

    public function __construct()
    {
        $this->dataDir = __DIR__ . '/../data/';
        if (!is_dir($this->dataDir)) mkdir($this->dataDir, 0755, true);
    }

    // ── File helpers ─────────────────────────────────────────
    private function sheetFile(string $sheet): string
    {
        $map = [
            SHEET_EMPLOYEES     => 'employees.json',
            SHEET_TASKS         => 'tasks.json',
            SHEET_APPROVALS     => 'approvals.json',
            SHEET_USERS         => 'users.json',
            SHEET_NOTIFICATIONS => 'notifications.json',
        ];
        return $this->dataDir . ($map[$sheet] ?? strtolower($sheet) . '.json');
    }

    private function read(string $sheet): array
    {
        $file = $this->sheetFile($sheet);
        if (!file_exists($file)) return [];
        $raw = file_get_contents($file);
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    private function write(string $sheet, array $data): void
    {
        $file = $this->sheetFile($sheet);
        $fp   = fopen($file, 'c+');
        if (!$fp) return;
        if (flock($fp, LOCK_EX)) {
            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            fflush($fp);
            flock($fp, LOCK_UN);
        }
        fclose($fp);
    }

    // ── Public API ───────────────────────────────────────────
    public function getAll(string $sheet): array
    {
        return $this->read($sheet);
    }

    public function findOne(string $sheet, string $field, string $value): ?array
    {
        foreach ($this->read($sheet) as $row) {
            if (($row[$field] ?? '') === $value) return $row;
        }
        return null;
    }

    public function findMany(string $sheet, string $field, string $value): array
    {
        return array_values(array_filter(
            $this->read($sheet),
            fn($r) => ($r[$field] ?? '') === $value
        ));
    }

    // appendRow: positional array, maps to header keys of first row
    public function appendRow(string $sheet, array $values): bool
    {
        $data = $this->read($sheet);
        if (!empty($data)) {
            $headers = array_keys($data[0]);
            $record  = [];
            foreach ($headers as $i => $h) {
                $record[$h] = $values[$i] ?? '';
            }
            $data[] = $record;
        } else {
            $data[] = $values;
        }
        $this->write($sheet, $data);
        return true;
    }

    // appendRecord: associative array, always preserves keys
    public function appendRecord(string $sheet, array $record): bool
    {
        $data   = $this->read($sheet);
        $data[] = $record;
        $this->write($sheet, $data);
        return true;
    }

    public function updateById(string $sheet, string $idField, string $id, array $changes): bool
    {
        $data    = $this->read($sheet);
        $updated = false;
        foreach ($data as &$row) {
            if (($row[$idField] ?? '') === $id) {
                foreach ($changes as $k => $v) $row[$k] = $v;
                $updated = true;
                break;
            }
        }
        unset($row);
        if ($updated) $this->write($sheet, $data);
        return $updated;
    }

    public function deleteById(string $sheet, string $idField, string $id): bool
    {
        $data     = $this->read($sheet);
        $filtered = array_values(array_filter($data, fn($r) => ($r[$idField] ?? '') !== $id));
        if (count($filtered) === count($data)) return false;
        $this->write($sheet, $filtered);
        return true;
    }

    public function refreshPendingDays(): void
    {
        $tasks   = $this->read(SHEET_TASKS);
        $today   = new DateTime();
        $changed = false;

        foreach ($tasks as &$t) {
            $status   = $t['Status']   ?? '';
            $deadline = $t['Deadline'] ?? '';
            if (in_array($status, ['Pending', 'In Progress']) && $deadline) {
                try {
                    $dl   = new DateTime($deadline);
                    $days = $today > $dl ? (int)$today->diff($dl)->days : 0;
                    if ((string)($t['Days_Pending'] ?? '') !== (string)$days) {
                        $t['Days_Pending'] = $days;
                        $changed = true;
                    }
                } catch (Exception) {}
            }
        }
        unset($t);
        if ($changed) $this->write(SHEET_TASKS, $tasks);
    }

    public static function newId(string $prefix): string
    {
        return strtoupper($prefix) . '_' . date('Ymd') . '_' . strtoupper(substr(uniqid('', true), -6));
    }
}
