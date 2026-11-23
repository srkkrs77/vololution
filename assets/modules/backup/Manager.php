<?php
class Manager
{
    private $modx;

    public function __construct($modx)
    {
        $this->modx = $modx;
    }

    public function createBackup(bool $includeDb = true, bool $includeFiles = true): string
    {
        $backupDir = MODX_BASE_PATH . 'assets/backups/';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0777, true);
        }

        $filename = 'backup_' . date('Ymd_His') . '.zip';
        $zipPath = $backupDir . $filename;
        
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception('Не удалось создать ZIP-архив.');
        }

        if ($includeDb) {
            $sql = $this->backupDatabase();
            $zip->addFromString('database.sql', $sql);
        }

        if ($includeFiles) {
            // Отключаем базу данных на время долгой упаковки файлов, чтобы она не оборвалась
            if (isset($this->modx->db->conn)) {
                @$this->modx->db->conn->close();
            }
            $this->addFolderToZip(MODX_BASE_PATH, $zip);
        }

        $zip->close();

        // ПРИНУДИТЕЛЬНО ПЕРЕПОДКЛЮЧАЕМСЯ К БАЗЕ в конце, чтобы Evo смогла записать лог
        $this->modx->db->connect();

        return $filename;
    }

    private function addFolderToZip(string $folder, ZipArchive $zip, string $basePath = ''): void
    {
        $realPath = rtrim($folder, '/\\') . DIRECTORY_SEPARATOR;
        $files = scandir($realPath);

        // Исключаем только системный мусор, vendor оставляем!
        // Exclude only system junk, keep vendor!
        $exclude = ['.', '..', '.git'];

        foreach ($files as $file) {
            if (in_array($file, $exclude, true)) {
                continue;
            }

            $filePath = $realPath . $file;
            $localPath = $basePath . $file;

            // Нормализуем путь для кроссплатформенной проверки
            // Normalize path for cross-platform check
            $normalizedPath = str_replace('\\', '/', $filePath);

            // Пропускаем кэш и старые бэкапы (без слэша на конце, чтобы пропустить саму папку)
            // Skip cache and old backups (no trailing slash to skip the folder itself)
            if (strpos($normalizedPath, '/assets/cache') !== false || 
                strpos($normalizedPath, '/assets/backups') !== false) {
                continue;
            }

            if (is_dir($filePath)) {
                $zip->addEmptyDir($localPath);
                $this->addFolderToZip($filePath, $zip, $localPath . '/');
            } elseif (is_file($filePath)) {
                $zip->addFile($filePath, $localPath);
            }
        }
    }

    public function backupDatabase(): string
    {
        $sql = "-- VoloLution CMS Database Backup\n";
        $sql .= "-- Date: " . date('Y-m-d H:i:s') . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n";
        $sql .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n\n";

        $result = $this->modx->db->query("SHOW TABLES");
        while ($row = $this->modx->db->getRow($result)) {
            $table = array_values($row)[0];
            
            $createResult = $this->modx->db->query("SHOW CREATE TABLE `{$table}`");
            $createRow = $this->modx->db->getRow($createResult);
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= array_values($createRow)[1] . ";\n\n";

            $dataResult = $this->modx->db->query("SELECT * FROM `{$table}`");
            $rowCount = $this->modx->db->getRecordCount($dataResult);
            
            if ($rowCount > 0) {
                $sql .= "INSERT INTO `{$table}` VALUES \n";
                $i = 0;
                while ($row = $this->modx->db->getRow($dataResult)) {
                    $i++;
                    $values = [];
                    foreach ($row as $value) {
                        if ($value === null) {
                            $values[] = 'NULL';
                        } else {
                            $escaped = $this->modx->db->escape((string)$value);
                            $values[] = "'" . $escaped . "'";
                        }
                    }
                    $sql .= "(" . implode(', ', $values) . ")";
                    
                    if ($i < $rowCount) {
                        $sql .= ",\n";
                    } else {
                        $sql .= ";\n";
                    }
                }
                $sql .= "\n\n";
            }
        }
        
        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        return $sql;
    }
}