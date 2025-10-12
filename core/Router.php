<?php

class Router {
    private $routes = [];
    private $apiRoutes = [];
    
    public function get($uri, $controller) {
        $this->addRoute('GET', $uri, $controller);
    }
    
    public function post($uri, $controller) {
        $this->addRoute('POST', $uri, $controller);
    }
    
    public function put($uri, $controller) {
        $this->addRoute('PUT', $uri, $controller);
    }
    
    public function delete($uri, $controller) {
        $this->addRoute('DELETE', $uri, $controller);
    }
    
    public function api($method, $uri, $controller) {
        $this->apiRoutes[] = [
            'method' => $method,
            'uri' => '/api' . $uri,
            'controller' => $controller
        ];
    }
    
    private function addRoute($method, $uri, $controller) {
        $this->routes[] = [
            'method' => $method,
            'uri' => $uri,
            'controller' => $controller
        ];
    }
    
    public function dispatch() {
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        
        // Debug: Show request info
        // echo "Request URI: " . $requestUri . "<br>";
        // echo "Request Method: " . $requestMethod . "<br>";
        
        // Handle different base paths for Laragon
        $possibleBasePaths = [
            '/Quan_ly_trung_tam/public',
            '/Quan_ly_trung_tam',
            ''
        ];
        
        $cleanUri = $requestUri;
        foreach ($possibleBasePaths as $basePath) {
            if (strpos($requestUri, $basePath) === 0) {
                $cleanUri = substr($requestUri, strlen($basePath));
                break;
            }
        }
        
        if (empty($cleanUri) || $cleanUri === '/') {
            $cleanUri = '/';
        }
        
        // Debug: Show clean URI
        // echo "Clean URI: " . $cleanUri . "<br>";
        
        // Check API routes first
        $allRoutes = array_merge($this->apiRoutes, $this->routes);
        
        foreach ($allRoutes as $route) {
            if ($this->matchRoute($route, $requestMethod, $cleanUri)) {
                return $this->executeRoute($route, $cleanUri);
            }
        }
        
        // 404 Not Found
        http_response_code(404);
        echo "<h1>404 - Page Not Found</h1>";
        echo "<p>Requested URI: " . htmlspecialchars($requestUri) . "</p>";
        echo "<p>Clean URI: " . htmlspecialchars($cleanUri) . "</p>";
        echo "<p>Method: " . htmlspecialchars($requestMethod) . "</p>";
        echo "<h3>Available Routes:</h3><ul>";
        foreach ($this->routes as $route) {
            echo "<li>" . $route['method'] . " " . $route['uri'] . " -> " . $route['controller'] . "</li>";
        }
        echo "</ul>";
    }
    
    private function matchRoute($route, $method, $uri) {
        if ($route['method'] !== $method) {
            return false;
        }
        
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $route['uri']);
        $pattern = '#^' . $pattern . '$#';
        
        return preg_match($pattern, $uri);
    }
    
    private function executeRoute($route, $uri) {
        // Extract parameters
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $route['uri']);
        $pattern = '#^' . $pattern . '$#';
        
        preg_match($pattern, $uri, $matches);
        $parameters = array_slice($matches, 1);
        
        // Parse controller and method
        list($controllerName, $method) = explode('@', $route['controller']);
        $controllerClass = $controllerName . 'Controller';
        
        try {
            $controller = new $controllerClass();
            
            // Check if it's an API route
            if (strpos($route['uri'], '/api') === 0) {
                header('Content-Type: application/json');
            }
            
            return call_user_func_array([$controller, $method], $parameters);
        } catch (Exception $e) {
            http_response_code(500);
            if (strpos($route['uri'], '/api') === 0) {
                echo json_encode(['error' => 'Internal Server Error', 'message' => $e->getMessage()]);
            } else {
                echo "<h1>500 - Internal Server Error</h1>";
                echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
    }
}