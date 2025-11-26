#!/usr/bin/env php
<?php
/**
 * Export Migrations to SQL
 * Tạo file SQL thuần từ migration files để import vào phpMyAdmin
 */

define('ROOT_PATH', __DIR__);
define('BASE_PATH', __DIR__);
define('CONFIG_PATH', ROOT_PATH . '/config');

class SQLExporter {
    private $outputFile = 'exported_migrations.sql';
    
    public function export() {
        echo "📤 Exporting migrations to SQL file...\n\n";
        
        $migrationFiles = $this->getMigrationFiles();
        
        if (empty($migrationFiles)) {
            echo "❌ No migration files found!\n";
            return;
        }
        
        $sqlContent = $this->generateSQLHeader();
        
        foreach ($migrationFiles as $migrationFile) {
            echo "📝 Processing: {$migrationFile}\n";
            $sqlContent .= $this->processMigrationFile($migrationFile);
        }
        
        $sqlContent .= $this->generateSQLFooter();
        
        file_put_contents($this->outputFile, $sqlContent);
        
        echo "\n✅ SQL exported successfully!\n";
        echo "📁 File: {$this->outputFile}\n";
        echo "📋 Instructions:\n";
        echo "   1. Download the exported_migrations.sql file\n";
        echo "   2. Open phpMyAdmin on your hosting\n";
        echo "   3. Select your database\n";
        echo "   4. Go to Import tab\n";
        echo "   5. Upload and execute the SQL file\n\n";
    }
    
    private function getMigrationFiles() {
        $path = __DIR__ . '/database/migrations';
        if (!is_dir($path)) {
            return [];
        }
        
        $files = scandir($path);
        $migrations = [];
        
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $migrations[] = $file;
            }
        }
        
        sort($migrations);
        return $migrations;
    }
    
    private function processMigrationFile($filename) {
        $filepath = __DIR__ . '/database/migrations/' . $filename;
        
        if (!file_exists($filepath)) {
            return "-- Migration file not found: {$filename}\n\n";
        }
        
        $content = file_get_contents($filepath);
        
        // Extract SQL from up() method
        $sql = $this->extractSQLFromMigration($content, $filename);
        
        $header = "\n-- =====================================================\n";
        $header .= "-- Migration: {$filename}\n";
        $header .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $header .= "-- =====================================================\n\n";
        
        return $header . $sql . "\n";
    }
    
    private function extractSQLFromMigration($content, $filename) {
        // Try to extract SQL from CREATE TABLE statements
        if (preg_match('/CREATE TABLE\s+(\w+)/i', $content, $matches)) {
            $tableName = $matches[1];
            
            // Look for the full CREATE TABLE statement
            if (preg_match('/\$sql\s*=\s*"([^"]+CREATE TABLE[^"]+)"/s', $content, $sqlMatches)) {
                $sql = $sqlMatches[1];
                // Clean up the SQL
                $sql = str_replace('\\"', '"', $sql);
                $sql = str_replace('\\n', "\n", $sql);
                return $sql . ";\n";
            }
        }
        
        // Fallback: try to extract any SQL pattern
        if (preg_match_all('/\$sql\s*=\s*"([^"]+)"/s', $content, $matches)) {
            $sqlStatements = [];
            foreach ($matches[1] as $sql) {
                $sql = str_replace('\\"', '"', $sql);
                $sql = str_replace('\\n', "\n", $sql);
                if (trim($sql)) {
                    $sqlStatements[] = $sql . ";\n";
                }
            }
            return implode("\n", $sqlStatements);
        }
        
        return "-- Could not extract SQL from {$filename}\n";
    }
    
    private function generateSQLHeader() {
        $header = "-- =====================================================\n";
        $header .= "-- Auto-generated SQL from Migration Files\n";
        $header .= "-- Project: Quan Ly Trung Tam\n";
        $header .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $header .= "-- =====================================================\n\n";
        $header .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $header .= "START TRANSACTION;\n";
        $header .= "SET time_zone = \"+00:00\";\n\n";
        
        // Create migrations tracking table
        $header .= "-- Create migrations tracking table\n";
        $header .= "CREATE TABLE IF NOT EXISTS `migrations` (\n";
        $header .= "  `id` int(11) NOT NULL AUTO_INCREMENT,\n";
        $header .= "  `migration_name` varchar(255) NOT NULL,\n";
        $header .= "  `batch` int(11) NOT NULL DEFAULT 1,\n";
        $header .= "  `executed_at` timestamp NOT NULL DEFAULT current_timestamp(),\n";
        $header .= "  PRIMARY KEY (`id`),\n";
        $header .= "  UNIQUE KEY `unique_migration` (`migration_name`)\n";
        $header .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;\n\n";
        
        return $header;
    }
    
    private function generateSQLFooter() {
        $footer = "\n-- =====================================================\n";
        $footer .= "-- Insert migration records\n";
        $footer .= "-- =====================================================\n\n";
        
        $migrationFiles = $this->getMigrationFiles();
        $batch = 1;
        
        foreach ($migrationFiles as $file) {
            $migrationName = pathinfo($file, PATHINFO_FILENAME);
            $footer .= "INSERT IGNORE INTO `migrations` (`migration_name`, `batch`) VALUES ('{$migrationName}', {$batch});\n";
        }
        
        $footer .= "\nCOMMIT;\n";
        $footer .= "\n-- Migration export completed successfully!\n";
        
        return $footer;
    }
}

// CLI Interface
if (php_sapi_name() === 'cli') {
    $exporter = new SQLExporter();
    $exporter->export();
} else {
    echo "This script should be run from command line.\n";
    echo "Usage: php export_sql.php\n";
}
?>