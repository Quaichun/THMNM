<?php

// ... (models required already)

$url = $_GET['url'] ?? 'product';
$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

// Determine Controller and Action
if (isset($url[0]) && strtolower($url[0]) === 'api') {
    // API Route: api/resource/action/...
    $controllerName = 'ApiController';
    $action = isset($url[1]) ? $url[1] : 'index';
    $params = array_slice($url, 2);
} else {
    // Web Route: resource/action/...
    $controllerName = (isset($url[0]) && $url[0] != '') ? ucfirst($url[0]) . 'Controller' : 'DefaultController';
    $action = (isset($url[1]) && $url[1] != '') ? $url[1] : 'index';
    $params = array_slice($url, 2);
}

// Fallback for DefaultController if not found
if (!file_exists('app/controllers/' . $controllerName . '.php')) {
    if ($controllerName === 'DefaultController') {
        require_once 'app/controllers/ProductController.php';
        $controller = new ProductController();
        $controller->index();
        exit;
    }
    http_response_code(404);
    die('Controller not found: ' . $controllerName);
}

require_once 'app/controllers/' . $controllerName . '.php';
$controller = new $controllerName();

if (!method_exists($controller, $action)) {
    http_response_code(404);
    die('Action not found: ' . $action);
}

call_user_func_array([$controller, $action], $params);

?>