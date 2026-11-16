<?php

declare(strict_types=1);

use PDO;
use PDOException;
use Exception;
use ZipArchive;

/**
 * Менеджер резервного копирования.
 * Backup Manager.
 */
class Manager
{
    private string $dbHost;
    private string $dbUser;
    private string $dbPass;
    private string $dbName;
    private string $dbPort;

    /**
     * Конструктор принимает настройки БД.
     * Constructor accepts DB settings.
     *
     * @param array $config Массив с настройками (из $modx->config)
     */
    public function __construct(array $config)
    {
        $this->dbName = trim($config['dbase'] ?? '', '`');
        
        $hostParts = explode(':', $config['database_server'] ?? 'localhost');
        $this->dbHost = $hostParts[0];
        $this->dbPort = $hostParts[1] ?? '3306';

        $this->dbUser = $config['database_user'] ?? 'root';
        $this->dbPass = $config['database_password'] ?? '';
    }

    /**
     * Создает полный бэкап (БД + Файлы или по отдельности) и возвращает путь к ZIP.
     * Creates a full backup (DB + Files or separately) and returns path to ZIP.
     *
     * @param bool $includeDb Включить ли базу данных
     * @param bool $includeFiles Включить ли файлы
     * @return string Путь к созданному ZIP-файлу
     * @throws Exception
     */
    public function createBackup(bool $includeDb = true, bool $includeFiles = true): string
    {
        $zipPath = MODX_BASE_PATH . 'assets/cache/backup_' . date('Ymd_His') . '.zip';
        
        if (!is_dir(dirname($zipPath))) {
            mkdir(dirname($zipPath), 0777, true);
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception('Не удалось создать ZIP-архив / Cannot create ZIP archive.');
        }

        if ($includeDb) {
            $sql = $this->backupDatabase();
            $zip->addFromString('database.sql', $sql);
        }

        if ($includeFiles) {
            $this->addFolderToZip(MODX_BASE_PATH, $zip);
        }

        $zip->close();

        return $zipPath;
    }

    /**
     * Рекурсивно добавляет папку в ZIP, игнорируя кэш и системные папки.
     * Recursively adds folder to ZIP, ignoring cache and system folders.
     */
    private function addFolderToZip(string $folder, ZipArchive $zip, string $basePath = ''): void
    {
        $realPath = rtrim($folder, '/\\') . DIRECTORY_SEPARATOR;
        $files = scandir($realPath);

        $exclude = ['.', '..', '.git', 'vendor', 'assets/cache', 'assets/backups'];

        foreach ($files as $file) {
            if (in_array($file, $exclude, true)) {
                continue;
            }

            $filePath = $realPath . $file;
            $localPath = $basePath . $file;

            if (is_dir($filePath)) {
                $zip->addEmptyDir($localPath);
                $this->addFolderToZip($filePath, $zip, $localPath . '/');
            } elseif (is_file($filePath)) {
                if (strpos($filePath, 'assets/cache/backup_') !== false) {
                    continue;
                }
                $zip->addFile($filePath, $localPath);
            }
        }
    }

    /**
     * Создает SQL-дамп базы данных.
     * Creates a database SQL dump.
     *
     * @return string SQL-код дампа / SQL dump string
     * @throws Exception
     */
    public function backupDatabase(): string
    {
        $dsn = "mysql:host={$this->dbHost};port={$this->dbPort};dbname={$this->dbName};charset=utf8mb4";
        
        try {
            $pdo = new PDO($dsn, $this->dbUser, $this->dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            throw new Exception('Ошибка подключения к БД: ' . $e->getMessage());
        }

        $tables = [];
        $stmt = $pdo->query('SHOW TABLES');
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }

        $sql = "-- VoloLution CMS Database Backup\n";
        $sql .= "-- Date: " . date('Y-m-d H:i:s') . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n";
        $sql .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n\n";

        foreach ($tables as $table) {
            $stmt = $pdo->query("SHOW CREATE TABLE `{$table}`");
            $row = $stmt->fetch(PDO::FETCH_NUM);
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= $row[1] . ";\n\n";

            $stmt = $pdo->query("SELECT * FROM `{$table}`");
            $rowCount = $stmt->rowCount();
            
            if ($rowCount > 0) {
                $sql .= "INSERT INTO `{$table}` VALUES \n";
                $i = 0;
                while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                    $i++;
                    $values = [];
                    foreach ($row as $value) {
                        if ($value === null) {
                            $values[] = 'NULL';
                        } else {
                            $values[] = $pdo->quote((string)$value);
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