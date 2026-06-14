<?php

require_once 'app/controllers/CategoryApiController.php';
require_once 'app/controllers/CartApiController.php';
require_once 'app/controllers/OrderApiController.php';
require_once 'app/controllers/PaymentApiController.php';
require_once 'app/controllers/ProductApiController.php';
require_once 'app/controllers/AccountApiController.php';

class ApiController
{
    public function index()
    {
        $this->json([
            'success' => true,
            'message' => 'THMNM API Gateway',
            'endpoints' => [
                'account' => '/api/account',
                'category' => '/api/category',
                'product' => '/api/product',
                'cart' => '/api/cart',
                'order' => '/api/order',
                'payment' => '/api/payment'
            ]
        ]);
    }

    public function account($action = 'index', ...$args)
    {
        $this->dispatch(new AccountApiController(), $action, $args);
    }

    public function category($action = 'index', ...$args)
    {
        $this->dispatch(new CategoryApiController(), $action, $args);
    }

    public function product($action = 'index', ...$args)
    {
        $this->dispatch(new ProductApiController(), $action, $args);
    }

    public function cart($action = 'index', ...$args)
    {
        $this->dispatch(new CartApiController(), $action, $args);
    }

    public function order($action = 'index', ...$args)
    {
        $this->dispatch(new OrderApiController(), $action, $args);
    }

    public function payment($action = 'index', ...$args)
    {
        $this->dispatch(new PaymentApiController(), $action, $args);
    }

    private function dispatch($controller, $action, array $args)
    {
        if (!method_exists($controller, $action)) {
            $this->json(['success' => false, 'message' => "Action '$action' not found"], 404);
        }

        call_user_func_array([$controller, $action], $args);
    }

    private function json($data, $code = 200)
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}


