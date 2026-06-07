<?php
// src/Router.php

class Router
{
    private $routes = [];
    private $systemRoutes = [];
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        session_start();
        $this->loadRoutes();
    }

    private function loadRoutes()
    {
        $xml = simplexml_load_file(__DIR__ . '/routes.xml');

        if (isset($xml->system)) {
            foreach ($xml->system->route as $route) {
                $this->systemRoutes[(string)$route['match']] = [
                    'controller' => (string)$route['controller'],
                    'action' => (string)$route['action'],
                ];
            }
        }

        if (isset($xml->routes)) {
            $this->parseRoutes($xml->routes);
        }
    }

    private function parseRoutes($routesNode, $parentPath = '')
    {
        foreach ($routesNode->route as $route) {
            $match = (string)$route['match'];
            $fullPath = $parentPath . $match;

            $routeData = [
                'controller' => (string)$route['controller'],
                'action' => (string)$route['action'],
                'method' => isset($route['method']) ? (string)$route['method'] : 'GET'
            ];

            $key = $fullPath . ':' . $routeData['method'];

            if (preg_match('/\{([a-zA-Z0-9_]+)(?:\|(num|str))?\}/', $match)) {
                $pattern = $this->convertToRegex($fullPath);
                $this->routes['dynamic'][$pattern . ':' . $routeData['method']] = $routeData;
            } else {
                $this->routes['static'][$key] = $routeData;
            }

            if (count($route->route) > 0) {
                $this->parseRoutes($route, $fullPath . '/');
            }
        }
    }

    private function convertToRegex($pattern)
    {
        $pattern = preg_quote($pattern, '#');
        $pattern = preg_replace_callback('/\\\{([a-zA-Z0-9_]+)(?:\|(num|str))?\\\}/', function($matches) {
            $type = $matches[2] ?? 'str';
            switch ($type) {
                case 'num':
                    return '(?P<' . $matches[1] . '>\d+)';
                default:
                    return '(?P<' . $matches[1] . '>[a-zA-Z0-9]+)';
            }
        }, $pattern);

        return '#^' . $pattern . '$#u';
    }

    public function dispatch()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        $scriptName = $_SERVER['SCRIPT_NAME'];
        if (strpos($uri, $scriptName) === 0) {
            $uri = substr($uri, strlen($scriptName));
        }

        $uri = trim($uri, '/');

        if ($uri === '') {
            $controllerName = $this->systemRoutes['']['controller'];
            $action = $this->systemRoutes['']['action'];
            $this->callController($controllerName, $action, []);
            return;
        }

        $method = $_SERVER['REQUEST_METHOD'];
        $found = $this->findRoute($uri, $method);

        if (!$found) {
            $controllerName = $this->systemRoutes['not_found']['controller'];
            $action = $this->systemRoutes['not_found']['action'];
            $this->callController($controllerName, $action, []);
            return;
        }

        $this->callController($found['controller'], $found['action'], $found['params'] ?? []);
    }

    private function findRoute($uri, $method)
    {
        $key = $uri . ':' . $method;

        if (isset($this->routes['static'][$key])) {
            return [
                'controller' => $this->routes['static'][$key]['controller'],
                'action' => $this->routes['static'][$key]['action'],
                'params' => []
            ];
        }

        if (isset($this->routes['dynamic'])) {
            foreach ($this->routes['dynamic'] as $pattern => $routeData) {
                if ($routeData['method'] !== $method && $routeData['method'] !== 'ANY') {
                    continue;
                }

                if (preg_match($pattern, $uri, $matches)) {
                    $params = [];
                    foreach ($matches as $key => $value) {
                        if (!is_numeric($key)) {
                            $params[$key] = $value;
                        }
                    }
                    return [
                        'controller' => $routeData['controller'],
                        'action' => $routeData['action'],
                        'params' => $params
                    ];
                }
            }
        }

        return null;
    }

    private function callController($controllerName, $action, $params)
    {
        // Правильный путь к контроллерам
        $controllerFile = __DIR__ . '/controllers/' . $controllerName . '.php';

        if (!file_exists($controllerFile)) {
            http_response_code(500);
            echo "Controller not found: " . $controllerName . " at " . $controllerFile;
            return;
        }

        require_once $controllerFile;

        if (!class_exists($controllerName)) {
            http_response_code(500);
            echo "Class not found: " . $controllerName;
            return;
        }

        $controller = new $controllerName($this->pdo);

        if (!method_exists($controller, $action)) {
            http_response_code(500);
            echo "Method not found: " . $action . " in " . $controllerName;
            return;
        }

        call_user_func_array([$controller, $action], $params);
    }
}
