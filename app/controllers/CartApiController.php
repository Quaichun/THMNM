<?php

require_once 'app/config/database.php';
require_once 'app/models/ProductModel.php';
require_once 'app/middlewares/JwtMiddleware.php';
require_once 'app/helpers/SessionHelper.php';

class CartApiController
{
    private $db;
    private $productModel;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->productModel = new ProductModel($this->db);
        SessionHelper::start();
    }

    public function index()
    {
        $this->requireAuth();
        $this->json($this->cartResponse());
    }

    public function summary()
    {
        $this->requireAuth();
        $this->json($this->cartResponse());
    }

    public function add($productId)
    {
        $this->requireAuth();
        $product = $this->productModel->getProductById((int)$productId);
        if (!$product) {
            $this->json(['success' => false, 'message' => 'Sản phẩm không tồn tại'], 404);
        }

        $cart = $this->cart();
        $key = (int)$productId;
        if (!isset($cart[$key])) {
            $cart[$key] = [
                'name' => $product->name,
                'price' => (float)$product->price,
                'quantity' => 0,
                'image' => $product->image ?? null
            ];
        }
        
        $payload = $this->getRequestData();
        $qty = (int)($payload['fields']['quantity'] ?? 1);
        if ($qty < 1) $qty = 1;
        
        $cart[$key]['quantity'] += $qty;
        $this->setCart($cart);
        $this->json([
            'success' => true,
            'message' => 'Đã thêm sản phẩm vào giỏ hàng!',
            'cart_count' => $this->cartCount(),
            'cart' => $this->cartResponse()['cart']
        ]);
    }

    public function update($productId)
    {
        $this->requireAuth();
        $payload = $this->getRequestData();
        $qty = (int)($payload['fields']['quantity'] ?? 1);
        
        if ($qty <= 0) {
            $this->destroy($productId);
            return;
        }

        $cart = $this->cart();
        $key = (int)$productId;
        if (!isset($cart[$key])) {
            $this->json(['success' => false, 'message' => 'Sản phẩm không có trong giỏ hàng'], 404);
        }

        $cart[$key]['quantity'] = $qty;
        $this->setCart($cart);
        $this->json($this->cartResponse());
    }


    public function increase($productId)
    {
        $this->requireAuth();
        $cart = $this->cart();
        $key = (int)$productId;
        if (!isset($cart[$key])) {
            $this->json(['success' => false, 'message' => 'Cart item not found'], 404);
        }
        $cart[$key]['quantity']++;
        $this->setCart($cart);
        $this->json($this->cartResponse());
    }

    public function decrease($productId)
    {
        $this->requireAuth();
        $cart = $this->cart();
        $key = (int)$productId;
        if (!isset($cart[$key])) {
            $this->json(['success' => false, 'message' => 'Cart item not found'], 404);
        }
        $cart[$key]['quantity']--;
        if ($cart[$key]['quantity'] <= 0) {
            unset($cart[$key]);
        }
        $this->setCart($cart);
        $this->json($this->cartResponse());
    }

    public function destroy($productId)
    {
        $this->requireAuth();
        $cart = $this->cart();
        unset($cart[(int)$productId]);
        $this->setCart($cart);
        $this->json($this->cartResponse());
    }

    public function clear()
    {
        $this->requireAuth();
        $_SESSION['cart'] = [];
        $this->json($this->cartResponse());
    }

    private function cart(): array
    {
        return $_SESSION['cart'] ?? [];
    }

    private function setCart(array $cart): void
    {
        $_SESSION['cart'] = $cart;
    }

    private function cartCount(): int
    {
        $count = 0;
        foreach ($this->cart() as $item) {
            $count += (int)($item['quantity'] ?? 0);
        }
        return $count;
    }

    private function cartResponse(): array
    {
        $cart = [];
        $subtotal = 0.0;
        foreach ($this->cart() as $productId => $item) {
            $qty = (int)($item['quantity'] ?? 0);
            $price = (float)($item['price'] ?? 0);
            $lineTotal = $price * $qty;
            $subtotal += $lineTotal;
            $cart[] = [
                'product_id' => (int)$productId,
                'name' => $item['name'] ?? '',
                'price' => $price,
                'quantity' => $qty,
                'image' => $item['image'] ?? null,
                'line_total' => $lineTotal
            ];
        }

        return [
            'success' => true,
            'cart_count' => $this->cartCount(),
            'subtotal' => $subtotal,
            'cart' => $cart
        ];
    }

    private function requireAuth(): array
    {
        $auth = (new JwtMiddleware())->requireAuth();
        return $auth;
    }

    private function getRequestData(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($contentType, 'application/json') !== false) {
            $raw = file_get_contents('php://input');
            $decoded = json_decode($raw, true);
            return ['fields' => is_array($decoded) ? $decoded : []];
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            return ['fields' => $_POST];
        }

        $raw = file_get_contents('php://input');
        $fields = [];
        parse_str($raw, $fields);
        return ['fields' => $fields];
    }

    private function json($data, $code = 200)
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
