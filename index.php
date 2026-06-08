<?php

require_once 'app/models/ProductModel.php';

$url = $_GET['url'] ?? 'product';
$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

if (($url[0] ?? '') === 'api' && strtolower($url[1] ?? '') === 'product') {
    require_once 'app/controllers/ProductApiController.php';

    $controller = new ProductApiController();
    $method = $_SERVER['REQUEST_METHOD'];
    $id = $url[2] ?? null;

    switch ($method) {
        case 'GET':
            $id === null ? $controller->index() : $controller->show($id);
            break;
        case 'POST':
            $controller->store();
            break;
        case 'PUT':
            $id === null ? $controller->methodNotAllowed() : $controller->update($id);
            break;
        case 'DELETE':
            $id === null ? $controller->methodNotAllowed() : $controller->destroy($id);
            break;
        default:
            $controller->methodNotAllowed();
            break;
    }
    exit;
}

if (($url[0] ?? '') === 'api' && strtolower($url[1] ?? '') === 'category') {
    require_once 'app/controllers/CategoryApiController.php';

    $controller = new CategoryApiController();
    $method = $_SERVER['REQUEST_METHOD'];
    $id = $url[2] ?? null;

    switch ($method) {
        case 'GET':
            $id === null ? $controller->index() : $controller->show($id);
            break;
        case 'POST':
            $controller->store();
            break;
        case 'PUT':
            $id === null ? $controller->methodNotAllowed() : $controller->update($id);
            break;
        case 'DELETE':
            $id === null ? $controller->methodNotAllowed() : $controller->destroy($id);
            break;
        default:
            $controller->methodNotAllowed();
            break;
    }
    exit;
}

// Kiá»ƒm tra pháº§n Ä‘áº§u tiÃªn cá»§a URL Ä‘á»ƒ xÃ¡c Ä‘á»‹nh controller
$controllerName = isset($url[0]) && $url[0] != ''
    ? ucfirst($url[0]) . 'Controller'
    : 'DefaultController';

// Kiá»ƒm tra pháº§n thá»© hai cá»§a URL Ä‘á»ƒ xÃ¡c Ä‘á»‹nh action
$action = isset($url[1]) && $url[1] != ''
    ? $url[1]
    : 'index';

// die ("controller=$controllerName - action=$action");

// Kiá»ƒm tra xem controller vÃ  action cÃ³ tá»“n táº¡i khÃ´ng
if (!file_exists('app/controllers/' . $controllerName . '.php')) {

    // Xá»­ lÃ½ khÃ´ng tÃ¬m tháº¥y controller
    die('Controller not found');
}

require_once 'app/controllers/' . $controllerName . '.php';

$controller = new $controllerName();

if (!method_exists($controller, $action)) {

    // Xá»­ lÃ½ khÃ´ng tÃ¬m tháº¥y action
    die('Action not found');
}

// Gá»i action vá»›i cÃ¡c tham sá»‘ cÃ²n láº¡i (náº¿u cÃ³)
call_user_func_array([$controller, $action], array_slice($url, 2));
?>
