<?php
/**
 * Database Migration System
 * Auto-update database tables and generate corresponding Models/Controllers
 */

require_once __DIR__ . '/Database.php';

class MigrationDatabase {
    private $pdo;
    private $stmt;
    
    public function __construct() {
        $db = Database::getInstance();
        $this->pdo = $db->getConnection();
    }
    
    public function query($sql) {
        $this->stmt = $this->pdo->prepare($sql);
    }
    
    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }
    
    public function execute() {
        return $this->stmt->execute();
    }
    
    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    public function single() {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_OBJ);
    }
}

class Migration {
    private $db;
    private $migrationTable = 'migrations';
    
    public function __construct() {
        // Delay database connection until needed
    }
    
    private function getDb() {
        if (!$this->db) {
            $this->db = new MigrationDatabase();
            $this->createMigrationTable();
        }
        return $this->db;
    }
    
    /**
     * Create migrations tracking table
     */
    private function createMigrationTable() {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->migrationTable} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration_name VARCHAR(255) NOT NULL,
            batch INT NOT NULL DEFAULT 1,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_migration (migration_name)
        )";
        
        $this->getDb()->query($sql);
        $this->getDb()->execute();
    }
    
    /**
     * Run all pending migrations
     */
    public function migrate() {
        echo "🚀 Starting database migration...\n";
        
        $migrationFiles = $this->getMigrationFiles();
        $executedMigrations = $this->getExecutedMigrations();
        
        $pendingMigrations = array_diff($migrationFiles, $executedMigrations);
        
        if (empty($pendingMigrations)) {
            echo "✅ No pending migrations found.\n";
            return;
        }
        
        $batch = $this->getNextBatch();
        
        foreach ($pendingMigrations as $migration) {
            echo "⏳ Running migration: {$migration}\n";
            
            try {
                $this->executeMigration($migration);
                $this->recordMigration($migration, $batch);
                echo "✅ Migration {$migration} completed successfully.\n";
            } catch (Exception $e) {
                echo "❌ Migration {$migration} failed: " . $e->getMessage() . "\n";
                break;
            }
        }
        
        echo "🎉 Migration process completed!\n";
    }
    
    /**
     * Create a new migration file
     */
    public function createMigration($name, $type = 'create_table', $tableName = null) {
        $timestamp = date('Y_m_d_His');
        $className = $this->toCamelCase($name);
        $fileName = "{$timestamp}_{$name}.php";
        $filePath = $this->getMigrationsPath() . '/' . $fileName;
        
        // Ensure migrations directory exists
        $migrationsDir = $this->getMigrationsPath();
        if (!is_dir($migrationsDir)) {
            mkdir($migrationsDir, 0755, true);
        }
        
        $template = $this->getMigrationTemplate($className, $type, $tableName);
        
        file_put_contents($filePath, $template);
        
        echo "✅ Migration created: {$fileName}\n";
        echo "📝 Edit the file: {$filePath}\n";
        
        return $fileName;
    }
    
    /**
     * Auto-generate table with Model and Controller
     */
    public function generateCRUD($tableName, $columns = []) {
        echo "🔧 Generating CRUD for table: {$tableName}\n";
        
        // 1. Create migration
        $migrationName = "create_{$tableName}_table";
        $this->createMigration($migrationName, 'create_table', $tableName);
        
        // 2. Generate Model
        $this->generateModel($tableName, $columns);
        
        // 3. Generate Controller
        $this->generateController($tableName);
        
        // 4. Generate Views
        $this->generateViews($tableName, $columns);
        
        echo "🎉 CRUD generated successfully for {$tableName}!\n";
        echo "📋 Next steps:\n";
        echo "   1. Edit migration file to define columns\n";
        echo "   2. Run: php migrate.php\n";
        echo "   3. Add routes to Router.php\n";
    }
    
    /**
     * Generate Model class
     */
    private function generateModel($tableName, $columns = []) {
        $className = $this->toPascalCase($tableName);
        $fileName = $className . '.php';
        $filePath = __DIR__ . '/../app/models/' . $fileName;
        
        $template = $this->getModelTemplate($className, $tableName, $columns);
        
        file_put_contents($filePath, $template);
        echo "✅ Model created: {$fileName}\n";
    }
    
    /**
     * Generate Controller class
     */
    private function generateController($tableName) {
        $className = $this->toPascalCase($tableName) . 'Controller';
        $modelName = $this->toPascalCase($tableName);
        $fileName = $className . '.php';
        $filePath = __DIR__ . '/../app/controllers/' . $fileName;
        
        $template = $this->getControllerTemplate($className, $modelName, $tableName);
        
        file_put_contents($filePath, $template);
        echo "✅ Controller created: {$fileName}\n";
    }
    
    /**
     * Generate View files
     */
    private function generateViews($tableName, $columns = []) {
        $viewsDir = __DIR__ . '/../app/views/' . $tableName;
        
        if (!is_dir($viewsDir)) {
            mkdir($viewsDir, 0755, true);
        }
        
        // Generate index.php
        $indexTemplate = $this->getViewTemplate($tableName, 'index', $columns);
        file_put_contents($viewsDir . '/index.php', $indexTemplate);
        
        // Generate create.php
        $createTemplate = $this->getViewTemplate($tableName, 'create', $columns);
        file_put_contents($viewsDir . '/create.php', $createTemplate);
        
        // Generate edit.php
        $editTemplate = $this->getViewTemplate($tableName, 'edit', $columns);
        file_put_contents($viewsDir . '/edit.php', $editTemplate);
        
        echo "✅ Views created: index.php, create.php, edit.php\n";
    }
    
    /**
     * Rollback last migration batch
     */
    public function rollback() {
        echo "⏪ Rolling back last migration batch...\n";
        
        $lastBatch = $this->getLastBatch();
        if (!$lastBatch) {
            echo "❌ No migrations to rollback.\n";
            return;
        }
        
        $migrations = $this->getBatchMigrations($lastBatch);
        
        foreach (array_reverse($migrations) as $migration) {
            echo "⏳ Rolling back: {$migration}\n";
            
            try {
                $this->rollbackMigration($migration);
                $this->removeMigrationRecord($migration);
                echo "✅ Rollback {$migration} completed.\n";
            } catch (Exception $e) {
                echo "❌ Rollback {$migration} failed: " . $e->getMessage() . "\n";
            }
        }
        
        echo "🎉 Rollback completed!\n";
    }
    
    // Helper methods
    private function getMigrationsPath() {
        return __DIR__ . '/../database/migrations';
    }
    
    private function getMigrationFiles() {
        $path = $this->getMigrationsPath();
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
        $this->db->query("SELECT migration_name FROM {$this->migrationTable} ORDER BY id");
        $results = $this->db->resultSet();
        
        return array_column($results, 'migration_name');
    }
    
    private function getNextBatch() {
        $this->db->query("SELECT MAX(batch) as max_batch FROM {$this->migrationTable}");
        $result = $this->db->single();
        
        return ($result->max_batch ?? 0) + 1;
    }
    
    private function getLastBatch() {
        $this->db->query("SELECT MAX(batch) as max_batch FROM {$this->migrationTable}");
        $result = $this->db->single();
        
        return $result->max_batch ?? null;
    }
    
    private function getBatchMigrations($batch) {
        $this->db->query("SELECT migration_name FROM {$this->migrationTable} WHERE batch = :batch ORDER BY id");
        $this->db->bind(':batch', $batch);
        $results = $this->db->resultSet();
        
        return array_column($results, 'migration_name');
    }
    
    private function executeMigration($migration) {
        $filePath = $this->getMigrationsPath() . '/' . $migration . '.php';
        
        if (!file_exists($filePath)) {
            throw new Exception("Migration file not found: {$filePath}");
        }
        
        require_once $filePath;
        
        $className = $this->toCamelCase($migration);
        
        if (!class_exists($className)) {
            throw new Exception("Migration class not found: {$className}");
        }
        
        $migrationInstance = new $className();
        $migrationInstance->up($this->db);
    }
    
    private function rollbackMigration($migration) {
        $filePath = $this->getMigrationsPath() . '/' . $migration . '.php';
        
        if (!file_exists($filePath)) {
            throw new Exception("Migration file not found: {$filePath}");
        }
        
        require_once $filePath;
        
        $className = $this->toCamelCase($migration);
        $migrationInstance = new $className();
        $migrationInstance->down($this->db);
    }
    
    private function recordMigration($migration, $batch) {
        $this->db->query("INSERT INTO {$this->migrationTable} (migration_name, batch) VALUES (:migration, :batch)");
        $this->db->bind(':migration', $migration);
        $this->db->bind(':batch', $batch);
        $this->db->execute();
    }
    
    private function removeMigrationRecord($migration) {
        $this->db->query("DELETE FROM {$this->migrationTable} WHERE migration_name = :migration");
        $this->db->bind(':migration', $migration);
        $this->db->execute();
    }
    
    private function toCamelCase($string) {
        $parts = explode('_', $string);
        $parts = array_map('ucfirst', $parts);
        return implode('', $parts);
    }
    
    private function toPascalCase($string) {
        return ucfirst($this->toCamelCase($string));
    }
    
    private function toSnakeCase($string) {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $string));
    }
    
    /**
     * Get migration template
     */
    private function getMigrationTemplate($className, $type, $tableName) {
        $upMethod = '';
        $downMethod = '';
        
        switch ($type) {
            case 'create_table':
                $upMethod = "        // Create {$tableName} table
        \$sql = \"CREATE TABLE {$tableName} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )\";
        \$db->query(\$sql);
        \$db->execute();";
                
                $downMethod = "        // Drop {$tableName} table
        \$sql = \"DROP TABLE IF EXISTS {$tableName}\";
        \$db->query(\$sql);
        \$db->execute();";
                break;
                
            case 'add_column':
                $upMethod = "        // Add column to {$tableName}
        \$sql = \"ALTER TABLE {$tableName} ADD COLUMN column_name VARCHAR(255)\";
        \$db->query(\$sql);
        \$db->execute();";
                
                $downMethod = "        // Remove column from {$tableName}
        \$sql = \"ALTER TABLE {$tableName} DROP COLUMN column_name\";
        \$db->query(\$sql);
        \$db->execute();";
                break;
        }
        
        return "<?php

class {$className} {
    public function up(\$db) {
{$upMethod}
    }
    
    public function down(\$db) {
{$downMethod}
    }
}
";
    }
    
    /**
     * Get model template
     */
    private function getModelTemplate($className, $tableName, $columns) {
        return "<?php

class {$className} extends BaseModel {
    protected \$table = '{$tableName}';
    protected \$primaryKey = 'id';
    protected \$fillable = [
        // Add fillable columns here
    ];
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Get all records
     */
    public function getAll() {
        \$sql = \"SELECT * FROM {\$this->table} ORDER BY id DESC\";
        return \$this->db->fetchAll(\$sql);
    }
    
    /**
     * Get record by ID
     */
    public function getById(\$id) {
        \$sql = \"SELECT * FROM {\$this->table} WHERE id = ?\";
        return \$this->db->fetch(\$sql, [\$id]);
    }
    
    /**
     * Create new record
     */
    public function create(\$data) {
        \$columns = implode(',', array_keys(\$data));
        \$placeholders = ':' . implode(', :', array_keys(\$data));
        
        \$sql = \"INSERT INTO {\$this->table} (\$columns) VALUES (\$placeholders)\";
        \$stmt = \$this->db->query(\$sql, \$data);
        
        return \$this->db->lastInsertId();
    }
    
    /**
     * Update record
     */
    public function update(\$id, \$data) {
        \$setParts = [];
        foreach (array_keys(\$data) as \$column) {
            \$setParts[] = \"\$column = :\$column\";
        }
        \$setClause = implode(', ', \$setParts);
        
        \$data['id'] = \$id;
        \$sql = \"UPDATE {\$this->table} SET \$setClause WHERE id = :id\";
        
        return \$this->db->query(\$sql, \$data);
    }
    
    /**
     * Delete record
     */
    public function delete(\$id) {
        \$sql = \"DELETE FROM {\$this->table} WHERE id = ?\";
        return \$this->db->query(\$sql, [\$id]);
    }
    
    /**
     * Get active records
     */
    public function getActive() {
        \$sql = \"SELECT * FROM {\$this->table} WHERE status = 'active' ORDER BY id DESC\";
        return \$this->db->fetchAll(\$sql);
    }
}
";
    }
    
    /**
     * Get controller template
     */
    private function getControllerTemplate($className, $modelName, $tableName) {
        return "<?php

require_once '../app/models/{$modelName}.php';

class {$className} extends BaseController {
    private \${$this->toSnakeCase($modelName)};
    
    public function __construct() {
        parent::__construct();
        \$this->{$this->toSnakeCase($modelName)} = new {$modelName}();
    }
    
    /**
     * Display list of records
     */
    public function index() {
        \$data = [
            '{$tableName}' => \$this->{$this->toSnakeCase($modelName)}->getAll(),
            'title' => '" . ucfirst($tableName) . " Management'
        ];
        
        \$this->view('{$tableName}/index', \$data);
    }
    
    /**
     * Show create form
     */
    public function create() {
        \$data = [
            'title' => 'Create New " . ucfirst(rtrim($tableName, 's')) . "'
        ];
        
        \$this->view('{$tableName}/create', \$data);
    }
    
    /**
     * Store new record
     */
    public function store() {
        if (\$_SERVER['REQUEST_METHOD'] === 'POST') {
            \$data = [
                // Add your form fields here
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            if (\$this->{$this->toSnakeCase($modelName)}->create(\$data)) {
                \$this->redirect('/{$tableName}?success=created');
            } else {
                \$this->redirect('/{$tableName}/create?error=failed');
            }
        }
    }
    
    /**
     * Show single record
     */
    public function show(\$id) {
        \$record = \$this->{$this->toSnakeCase($modelName)}->getById(\$id);
        
        if (!\$record) {
            \$this->redirect('/{$tableName}?error=not_found');
        }
        
        \$data = [
            '" . rtrim($tableName, 's') . "' => \$record,
            'title' => 'View " . ucfirst(rtrim($tableName, 's')) . "'
        ];
        
        \$this->view('{$tableName}/show', \$data);
    }
    
    /**
     * Show edit form
     */
    public function edit(\$id) {
        \$record = \$this->{$this->toSnakeCase($modelName)}->getById(\$id);
        
        if (!\$record) {
            \$this->redirect('/{$tableName}?error=not_found');
        }
        
        \$data = [
            '" . rtrim($tableName, 's') . "' => \$record,
            'title' => 'Edit " . ucfirst(rtrim($tableName, 's')) . "'
        ];
        
        \$this->view('{$tableName}/edit', \$data);
    }
    
    /**
     * Update record
     */
    public function update(\$id) {
        if (\$_SERVER['REQUEST_METHOD'] === 'POST') {
            \$data = [
                // Add your form fields here
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            if (\$this->{$this->toSnakeCase($modelName)}->update(\$id, \$data)) {
                \$this->redirect('/{$tableName}?success=updated');
            } else {
                \$this->redirect('/{$tableName}/edit/' . \$id . '?error=failed');
            }
        }
    }
    
    /**
     * Delete record
     */
    public function destroy(\$id) {
        if (\$this->{$this->toSnakeCase($modelName)}->delete(\$id)) {
            \$this->redirect('/{$tableName}?success=deleted');
        } else {
            \$this->redirect('/{$tableName}?error=delete_failed');
        }
    }
}
";
    }
    
    /**
     * Get view template
     */
    private function getViewTemplate($tableName, $viewType, $columns) {
        $title = ucfirst($tableName);
        $singular = rtrim($tableName, 's');
        
        switch ($viewType) {
            case 'index':
                return $this->getIndexViewTemplate($tableName, $title);
            case 'create':
                return $this->getCreateViewTemplate($tableName, $title, $singular);
            case 'edit':
                return $this->getEditViewTemplate($tableName, $title, $singular);
            default:
                return '';
        }
    }
    
    private function getIndexViewTemplate($tableName, $title) {
        return "<?php require_once '../app/views/layouts/app.php'; ?>

<div class=\"container mt-4\">
    <div class=\"row\">
        <div class=\"col-12\">
            <div class=\"card\">
                <div class=\"card-header d-flex justify-content-between align-items-center\">
                    <h5 class=\"mb-0\">
                        <i class=\"fas fa-list\"></i> {$title} Management
                    </h5>
                    <a href=\"/{$tableName}/create\" class=\"btn btn-primary\">
                        <i class=\"fas fa-plus\"></i> Add New
                    </a>
                </div>
                <div class=\"card-body\">
                    <?php if (isset(\$_GET['success'])): ?>
                        <div class=\"alert alert-success alert-dismissible fade show\" role=\"alert\">
                            Operation completed successfully!
                            <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset(\$_GET['error'])): ?>
                        <div class=\"alert alert-danger alert-dismissible fade show\" role=\"alert\">
                            An error occurred. Please try again.
                            <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button>
                        </div>
                    <?php endif; ?>
                    
                    <div class=\"table-responsive\">
                        <table class=\"table table-striped table-hover\">
                            <thead class=\"table-dark\">
                                <tr>
                                    <th>ID</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty(\${$tableName})): ?>
                                    <?php foreach (\${$tableName} as \$item): ?>
                                        <tr>
                                            <td><?= \$item['id'] ?></td>
                                            <td><?= date('d/m/Y H:i', strtotime(\$item['created_at'])) ?></td>
                                            <td>
                                                <div class=\"btn-group\" role=\"group\">
                                                    <a href=\"/{$tableName}/show/<?= \$item['id'] ?>\" class=\"btn btn-sm btn-info\">
                                                        <i class=\"fas fa-eye\"></i>
                                                    </a>
                                                    <a href=\"/{$tableName}/edit/<?= \$item['id'] ?>\" class=\"btn btn-sm btn-warning\">
                                                        <i class=\"fas fa-edit\"></i>
                                                    </a>
                                                    <button type=\"button\" class=\"btn btn-sm btn-danger\" 
                                                            onclick=\"confirmDelete(<?= \$item['id'] ?>)\">
                                                        <i class=\"fas fa-trash\"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan=\"3\" class=\"text-center\">No records found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this record?')) {
        window.location.href = '/{$tableName}/delete/' + id;
    }
}
</script>
";
    }
    
    private function getCreateViewTemplate($tableName, $title, $singular) {
        return "<?php require_once '../app/views/layouts/app.php'; ?>

<div class=\"container mt-4\">
    <div class=\"row\">
        <div class=\"col-md-8 mx-auto\">
            <div class=\"card\">
                <div class=\"card-header\">
                    <h5 class=\"mb-0\">
                        <i class=\"fas fa-plus\"></i> Create New {$singular}
                    </h5>
                </div>
                <div class=\"card-body\">
                    <?php if (isset(\$_GET['error'])): ?>
                        <div class=\"alert alert-danger alert-dismissible fade show\" role=\"alert\">
                            An error occurred. Please check your input and try again.
                            <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method=\"POST\" action=\"/{$tableName}/store\">
                        <!-- Add your form fields here -->
                        
                        <div class=\"mb-3\">
                            <label for=\"name\" class=\"form-label\">Name <span class=\"text-danger\">*</span></label>
                            <input type=\"text\" class=\"form-control\" id=\"name\" name=\"name\" required>
                        </div>
                        
                        <div class=\"mb-3\">
                            <label for=\"description\" class=\"form-label\">Description</label>
                            <textarea class=\"form-control\" id=\"description\" name=\"description\" rows=\"3\"></textarea>
                        </div>
                        
                        <div class=\"mb-3\">
                            <label for=\"status\" class=\"form-label\">Status</label>
                            <select class=\"form-select\" id=\"status\" name=\"status\">
                                <option value=\"active\">Active</option>
                                <option value=\"inactive\">Inactive</option>
                            </select>
                        </div>
                        
                        <div class=\"d-flex justify-content-between\">
                            <a href=\"/{$tableName}\" class=\"btn btn-secondary\">
                                <i class=\"fas fa-arrow-left\"></i> Back
                            </a>
                            <button type=\"submit\" class=\"btn btn-primary\">
                                <i class=\"fas fa-save\"></i> Create {$singular}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
";
    }
    
    private function getEditViewTemplate($tableName, $title, $singular) {
        return "<?php require_once '../app/views/layouts/app.php'; ?>

<div class=\"container mt-4\">
    <div class=\"row\">
        <div class=\"col-md-8 mx-auto\">
            <div class=\"card\">
                <div class=\"card-header\">
                    <h5 class=\"mb-0\">
                        <i class=\"fas fa-edit\"></i> Edit {$singular}
                    </h5>
                </div>
                <div class=\"card-body\">
                    <?php if (isset(\$_GET['error'])): ?>
                        <div class=\"alert alert-danger alert-dismissible fade show\" role=\"alert\">
                            An error occurred. Please check your input and try again.
                            <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method=\"POST\" action=\"/{$tableName}/update/<?= \${$singular}['id'] ?>\">
                        <!-- Add your form fields here -->
                        
                        <div class=\"mb-3\">
                            <label for=\"name\" class=\"form-label\">Name <span class=\"text-danger\">*</span></label>
                            <input type=\"text\" class=\"form-control\" id=\"name\" name=\"name\" 
                                   value=\"<?= htmlspecialchars(\${$singular}['name'] ?? '') ?>\" required>
                        </div>
                        
                        <div class=\"mb-3\">
                            <label for=\"description\" class=\"form-label\">Description</label>
                            <textarea class=\"form-control\" id=\"description\" name=\"description\" rows=\"3\"><?= htmlspecialchars(\${$singular}['description'] ?? '') ?></textarea>
                        </div>
                        
                        <div class=\"mb-3\">
                            <label for=\"status\" class=\"form-label\">Status</label>
                            <select class=\"form-select\" id=\"status\" name=\"status\">
                                <option value=\"active\" <?= (\${$singular}['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value=\"inactive\" <?= (\${$singular}['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                        
                        <div class=\"d-flex justify-content-between\">
                            <a href=\"/{$tableName}\" class=\"btn btn-secondary\">
                                <i class=\"fas fa-arrow-left\"></i> Back
                            </a>
                            <button type=\"submit\" class=\"btn btn-primary\">
                                <i class=\"fas fa-save\"></i> Update {$singular}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
";
    }
}
?>