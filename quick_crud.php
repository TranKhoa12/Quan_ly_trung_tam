#!/usr/bin/env php
<?php
/**
 * Quick CRUD Generator
 * Tạo nhiều CRUD cùng lúc cho hệ thống quản lý trung tâm
 */

// Define paths
define('ROOT_PATH', __DIR__);
define('BASE_PATH', __DIR__);
define('CONFIG_PATH', ROOT_PATH . '/config');

require_once __DIR__ . '/core/Migration.php';

class QuickCRUDGenerator {
    private $migration;
    
    public function __construct() {
        $this->migration = new Migration();
    }
    
    public function generateEducationSystem() {
        echo "🚀 Generating Complete Education Management System...\n\n";
        
        $tables = [
            'categories' => [
                'name' => 'Category Management',
                'fields' => ['name', 'code', 'description', 'status']
            ],
            'instructors' => [
                'name' => 'Instructor Management', 
                'fields' => ['name', 'email', 'phone', 'specialization', 'experience_years', 'status']
            ],
            'courses' => [
                'name' => 'Course Management',
                'fields' => ['name', 'code', 'category_id', 'instructor_id', 'duration_hours', 'fee', 'max_students', 'description', 'start_date', 'end_date', 'status']
            ],
            'students' => [
                'name' => 'Student Management',
                'fields' => ['student_code', 'full_name', 'email', 'phone', 'address', 'birth_date', 'gender', 'status']
            ],
            'enrollments' => [
                'name' => 'Enrollment Management',
                'fields' => ['student_id', 'course_id', 'enrollment_date', 'fee_paid', 'payment_status', 'completion_status', 'grade', 'certificate_issued']
            ],
            'payments' => [
                'name' => 'Payment Management', 
                'fields' => ['enrollment_id', 'amount', 'payment_method', 'payment_date', 'transaction_id', 'notes', 'status']
            ],
            'certificates' => [
                'name' => 'Certificate Management',
                'fields' => ['enrollment_id', 'certificate_number', 'issue_date', 'completion_date', 'grade', 'instructor_signature', 'director_signature', 'status']
            ],
            'attendance' => [
                'name' => 'Attendance Management',
                'fields' => ['enrollment_id', 'class_date', 'status', 'notes', 'checked_by']
            ]
        ];
        
        foreach ($tables as $table => $config) {
            echo "📋 Generating CRUD for: {$config['name']}\n";
            
            try {
                $this->migration->generateCRUD($table, $config['fields']);
                echo "✅ {$table} CRUD completed successfully!\n\n";
            } catch (Exception $e) {
                echo "❌ Error generating {$table}: " . $e->getMessage() . "\n\n";
            }
        }
        
        $this->generateCustomMigrations();
        $this->generateRoutes($tables);
        
        echo "🎉 Complete Education Management System generated!\n\n";
        $this->showNextSteps();
    }
    
    private function generateCustomMigrations() {
        echo "🔧 Generating custom migrations...\n";
        
        // Migration để thêm foreign keys
        $this->migration->createMigration('add_foreign_keys', 'custom');
        
        // Migration để thêm indexes
        $this->migration->createMigration('add_database_indexes', 'custom');
        
        echo "✅ Custom migrations created\n\n";
    }
    
    private function generateRoutes($tables) {
        echo "🛣️  Generating routes file...\n";
        
        $routeContent = "<?php\n";
        $routeContent .= "/**\n * Auto-generated Routes for Education Management System\n";
        $routeContent .= " * Add these to your Router.php file\n */\n\n";
        
        foreach ($tables as $table => $config) {
            $controller = ucfirst($table) . 'Controller';
            
            $routeContent .= "// {$config['name']} Routes\n";
            $routeContent .= "\$router->get('/{$table}', '{$controller}@index');\n";
            $routeContent .= "\$router->get('/{$table}/create', '{$controller}@create');\n";
            $routeContent .= "\$router->post('/{$table}/store', '{$controller}@store');\n";
            $routeContent .= "\$router->get('/{$table}/show/{id}', '{$controller}@show');\n";
            $routeContent .= "\$router->get('/{$table}/edit/{id}', '{$controller}@edit');\n";
            $routeContent .= "\$router->post('/{$table}/update/{id}', '{$controller}@update');\n";
            $routeContent .= "\$router->get('/{$table}/delete/{id}', '{$controller}@destroy');\n\n";
        }
        
        file_put_contents(__DIR__ . '/generated_routes.php', $routeContent);
        echo "✅ Routes generated: generated_routes.php\n\n";
    }
    
    private function showNextSteps() {
        echo "📋 Next Steps:\n";
        echo "=============\n\n";
        echo "1. 📝 Edit migration files to customize table structures\n";
        echo "2. 🔧 Update Model fillable arrays with proper fields\n";  
        echo "3. 📋 Customize Controller logic for business rules\n";
        echo "4. 🎨 Update View forms with proper field types\n";
        echo "5. 🛣️  Copy routes from generated_routes.php to Router.php\n";
        echo "6. 🗄️  Start database and run: php migrate.php migrate\n\n";
        
        echo "🚀 Your complete Education Management System is ready!\n";
        echo "📊 Generated: 8 Models + 8 Controllers + 24 Views + Migrations\n";
    }
}

class SingleCRUDGenerator {
    private $migration;
    
    public function __construct() {
        $this->migration = new Migration();
    }
    
    public function generate($tableName, $fields = []) {
        echo "🔧 Generating CRUD for: {$tableName}\n";
        
        try {
            $this->migration->generateCRUD($tableName, $fields);
            echo "✅ {$tableName} CRUD completed successfully!\n";
            
            // Show sample routes
            $controller = ucfirst($tableName) . 'Controller';
            echo "\n📋 Add these routes to Router.php:\n";
            echo "// {$tableName} Routes\n";
            echo "\$router->get('/{$tableName}', '{$controller}@index');\n";
            echo "\$router->get('/{$tableName}/create', '{$controller}@create');\n";
            echo "\$router->post('/{$tableName}/store', '{$controller}@store');\n";
            echo "\$router->get('/{$tableName}/edit/{id}', '{$controller}@edit');\n";
            echo "\$router->post('/{$tableName}/update/{id}', '{$controller}@update');\n";
            echo "\$router->get('/{$tableName}/delete/{id}', '{$controller}@destroy');\n\n";
            
        } catch (Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
        }
    }
}

// CLI Handler
if (php_sapi_name() === 'cli') {
    if (empty($argv) || count($argv) < 2) {
        echo "🚀 Quick CRUD Generator\n";
        echo "======================\n\n";
        echo "Usage:\n";
        echo "  php quick_crud.php full        - Generate complete education system\n";
        echo "  php quick_crud.php single [table] [fields...] - Generate single CRUD\n\n";
        echo "Examples:\n";
        echo "  php quick_crud.php full\n";
        echo "  php quick_crud.php single products name code price\n";
        echo "  php quick_crud.php single employees name email position salary\n\n";
        exit;
    }
    
    $command = $argv[1];
    
    switch ($command) {
        case 'full':
        case 'education':
        case 'complete':
            $generator = new QuickCRUDGenerator();
            $generator->generateEducationSystem();
            break;
            
        case 'single':
            if (count($argv) < 3) {
                echo "❌ Table name is required for single CRUD\n";
                echo "Usage: php quick_crud.php single table_name [fields...]\n";
                exit;
            }
            
            $tableName = $argv[2];
            $fields = array_slice($argv, 3);
            
            $generator = new SingleCRUDGenerator();
            $generator->generate($tableName, $fields);
            break;
            
        default:
            echo "❌ Unknown command: {$command}\n";
            echo "Use 'full' or 'single'\n";
    }
} else {
    echo "This script can only be run from command line.\n";
}
?>