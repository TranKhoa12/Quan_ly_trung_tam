#!/usr/bin/env php
<?php
/**
 * Migration CLI Tool
 * Usage: php migrate.php [command] [options]
 */

// Define paths
define('ROOT_PATH', __DIR__);
define('BASE_PATH', __DIR__);
define('CONFIG_PATH', ROOT_PATH . '/config');

require_once __DIR__ . '/core/Migration.php';

class MigrationCLI {
    private $migration;
    
    public function __construct() {
        // Don't initialize migration here to avoid database connection
    }
    
    public function run($args) {
        if (empty($args) || count($args) < 2) {
            $this->showHelp();
            return;
        }
        
        $command = $args[1];
        
        switch ($command) {
            case 'migrate':
                $this->getMigration()->migrate();
                break;
                
            case 'rollback':
                $this->getMigration()->rollback();
                break;
                
            case 'make':
                if (count($args) < 3) {
                    echo "❌ Migration name is required.\n";
                    echo "Usage: php migrate.php make migration_name [type] [table]\n";
                    return;
                }
                
                $name = $args[2];
                $type = $args[3] ?? 'create_table';
                $table = $args[4] ?? null;
                
                // This doesn't need database connection
                $migration = new Migration();
                $migration->createMigration($name, $type, $table);
                break;
                
            case 'crud':
                if (count($args) < 3) {
                    echo "❌ Table name is required.\n";
                    echo "Usage: php migrate.php crud table_name\n";
                    return;
                }
                
                $tableName = $args[2];
                $columns = array_slice($args, 3);
                
                // This doesn't need database connection
                $migration = new Migration();
                $migration->generateCRUD($tableName, $columns);
                break;
                
            case 'status':
                $this->showStatus();
                break;
                
            case 'fresh':
                $this->freshMigrate();
                break;
                
            default:
                echo "❌ Unknown command: {$command}\n";
                $this->showHelp();
        }
    }
    
    private function getMigration() {
        if (!$this->migration) {
            $this->migration = new Migration();
        }
        return $this->migration;
    }
    
    private function showStatus() {
        echo "📊 Migration Status:\n";
        echo "==================\n\n";
        
        // Get migration files
        $migrationFiles = $this->getMigrationFiles();
        $executedMigrations = $this->getExecutedMigrations();
        
        if (empty($migrationFiles)) {
            echo "No migration files found.\n";
            return;
        }
        
        foreach ($migrationFiles as $migration) {
            $status = in_array($migration, $executedMigrations) ? '✅ Executed' : '⏳ Pending';
            echo "{$status} - {$migration}\n";
        }
        
        echo "\n";
        echo "Total migrations: " . count($migrationFiles) . "\n";
        echo "Executed: " . count($executedMigrations) . "\n";
        echo "Pending: " . (count($migrationFiles) - count($executedMigrations)) . "\n";
    }
    
    private function freshMigrate() {
        echo "⚠️  Fresh migration will DROP ALL TABLES and re-run migrations.\n";
        echo "Are you sure? (yes/no): ";
        
        $handle = fopen("php://stdin", "r");
        $confirmation = trim(fgets($handle));
        fclose($handle);
        
        if (strtolower($confirmation) !== 'yes') {
            echo "❌ Fresh migration cancelled.\n";
            return;
        }
        
        echo "🔥 Starting fresh migration...\n";
        
        // Drop all tables except migrations
        $this->dropAllTables();
        
        // Recreate migrations table
        $this->migration = new Migration();
        
        // Run all migrations
        $this->migration->migrate();
        
        echo "🎉 Fresh migration completed!\n";
    }
    
    private function dropAllTables() {
        try {
            $db = Database::getInstance();
            $pdo = $db->getConnection();
            
            // Get all tables
            $stmt = $pdo->prepare("SHOW TABLES");
            $stmt->execute();
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Disable foreign key checks
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
            
            // Drop all tables except migrations
            foreach ($tables as $table) {
                if ($table !== 'migrations') {
                    echo "🗑️  Dropping table: {$table}\n";
                    $pdo->exec("DROP TABLE IF EXISTS `{$table}`");
                }
            }
            
            // Clear migrations table
            $pdo->exec("DELETE FROM migrations");
            
            // Re-enable foreign key checks
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
            
        } catch (Exception $e) {
            echo "❌ Error dropping tables: " . $e->getMessage() . "\n";
        }
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
                $migrations[] = pathinfo($file, PATHINFO_FILENAME);
            }
        }
        
        sort($migrations);
        return $migrations;
    }
    
    private function getExecutedMigrations() {
        try {
            $db = new MigrationDatabase();
            $db->query("SELECT migration_name FROM migrations ORDER BY id");
            $results = $db->resultSet();
            
            return array_column($results, 'migration_name');
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function showHelp() {
        echo "🚀 Laravel-style Migration Tool\n";
        echo "===============================\n\n";
        echo "Usage: php migrate.php [command] [options]\n\n";
        echo "Commands:\n";
        echo "  migrate                 Run pending migrations\n";
        echo "  rollback               Rollback last migration batch\n";
        echo "  make [name] [type]     Create new migration file\n";
        echo "  crud [table]           Generate complete CRUD (Model/Controller/Views)\n";
        echo "  status                 Show migration status\n";
        echo "  fresh                  Drop all tables and re-run migrations\n\n";
        echo "Examples:\n";
        echo "  php migrate.php migrate\n";
        echo "  php migrate.php make create_users_table\n";
        echo "  php migrate.php make add_email_to_users add_column users\n";
        echo "  php migrate.php crud products\n";
        echo "  php migrate.php status\n";
        echo "  php migrate.php fresh\n\n";
        echo "Migration Types:\n";
        echo "  create_table           Create new table\n";
        echo "  add_column            Add column to existing table\n";
        echo "  drop_column           Remove column from table\n";
        echo "  modify_column         Modify existing column\n\n";
    }
}

// Run CLI
if (php_sapi_name() === 'cli') {
    $cli = new MigrationCLI();
    $cli->run($argv);
} else {
    echo "This script can only be run from the command line.\n";
}