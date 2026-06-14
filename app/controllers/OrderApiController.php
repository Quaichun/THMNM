<?php

require_once 'app/config/database.php';
require_once 'app/models/OrderModel.php';
require_once 'app/models/ProductModel.php';
require_once 'app/models/AccountModel.php';
require_once 'app/middlewares/JwtMiddleware.php';
require_once 'app\helpers\SessionHelper.php';

class OrderApiController
{
    private $db;
    private $orderModel;
    private $accountModel;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->orderModel = new OrderModel($this->db);
        $this->accountModel = new AccountModel($this->db);
        SessionHelper::start();
    }

    public function index()
    {
        $auth = $this->requireAuth();
        if ($this->isAdmin($auth)) {
            $this->json([
                'success' => true,
                'orders' => $this->orderModel->getAllOrdersWithTotal()
            ]);
        }

        $orders = $this->orderModel->getOrdersByUserId((int)$auth['user_id']);
        $this->json([
            'success' => true,
            'orders' => $this->decorateOrders($orders, (int)$auth['user_id'])
        ]);
    }

    public function checkout()
    {
        $auth = $this->requireAuth();
        $cart = $_SESSION['cart'] ?? [];
        if (empty($cart)) {
            $this->json(['success' => false, 'message' => 'Cart is empty'], 422);
        }

        $this->json([
            'success' => true,
            'next_order_id' => $this->orderModel->getNextOrderId(),
            'cart' => $this->cartSnapshot($cart),
            'subtotal' => $this->cartSubtotal($cart),
            'payment_methods' => [
                ['code' => 'cod', 'label' => 'Thanh toan khi nhan hang'],
                ['code' => 'bank', 'label' => 'Chuyen khoan / QR']
            ],
            'user_id' => (int)$auth['user_id']
        ]);
    }

    public function store()
    {
        $auth = $this->requireAuth();
        $cart = $_SESSION['cart'] ?? [];
        if (empty($cart)) {
            $this->json(['success' => false, 'message' => 'Giỏ hàng đang trống'], 422);
        }

        $user = $this->accountModel->findById((int)$auth['user_id']);
        if (!$user) {
            $this->json(['success' => false, 'message' => 'User not found'], 404);
        }

        // Logic check: Email must be verified before ordering
        if (empty($user->email_verified_at)) {
            $this->json([
                'success' => false, 
                'message' => 'Bạn cần xác thực email trước khi đặt hàng. Vui lòng kiểm tra hồ sơ cá nhân.',
                'require_verification' => true
            ], 403);
        }

        $payload = $this->getRequestData();
        $fields = $payload['fields'];

        $name = trim((string)($fields['name'] ?? $user->fullname ?? ''));
        $phone = trim((string)($fields['phone'] ?? ''));
        $email = trim((string)($fields['email'] ?? $user->email ?? ''));
        $address = trim((string)($fields['address'] ?? ''));
        $payment = trim((string)($fields['payment_method'] ?? 'cod'));

        $errors = [];
        if ($name === '') $errors['name'] = 'Họ tên là bắt buộc';
        if ($phone === '') $errors['phone'] = 'Số điện thoại là bắt buộc';
        if ($address === '') $errors['address'] = 'Địa chỉ là bắt buộc';
        
        if ($errors) {
            $this->json(['success' => false, 'errors' => $errors], 422);
        }

        $orderId = $this->orderModel->createOrder(
            $name,
            $phone,
            $email,
            $address,
            $payment,
            (int)$auth['user_id']
        );

        if (!$orderId) {
            $this->json(['success' => false, 'message' => 'Tạo đơn hàng thất bại'], 400);
        }

        foreach ($cart as $productId => $item) {
            $this->orderModel->addOrderDetail(
                $orderId,
                (int)$productId,
                (int)($item['quantity'] ?? 0),
                (float)($item['price'] ?? 0)
            );
        }

        // Clear cart
        $_SESSION['cart'] = [];

        $this->json([
            'success' => true,
            'message' => 'Đã đặt hàng thành công!',
            'order_id' => $orderId
        ], 201);
    }


    public function show($id)
    {
        $auth = $this->requireAuth();
        $order = $this->orderModel->getOrderById((int)$id);
        if (!$order) {
            $this->json(['success' => false, 'message' => 'Order not found'], 404);
        }
        if (!$this->canViewOrder($order, $auth)) {
            $this->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $this->json([
            'success' => true,
            'order' => $order,
            'details' => $this->orderModel->getOrderDetails((int)$id)
        ]);
    }

    public function update($id)
    {
        $this->requireAdmin();
        $payload = $this->getRequestData();
        $status = trim((string)($payload['fields']['status'] ?? 'pending'));
        $allowed = ['pending', 'processing', 'shipping', 'delivered', 'cancelled'];
        if (!in_array($status, $allowed, true)) {
            $this->json(['success' => false, 'message' => 'Invalid status'], 422);
        }

        if (!$this->orderModel->updateStatus((int)$id, $status)) {
            $this->json(['success' => false, 'message' => 'Update failed'], 400);
        }

        $this->json([
            'success' => true,
            'message' => 'Order updated',
            'order' => $this->orderModel->getOrderById((int)$id)
        ]);
    }

    public function cancel($id)
    {
        $auth = $this->requireAuth();
        $order = $this->orderModel->getOrderById((int)$id);
        if (!$order) {
            $this->json(['success' => false, 'message' => 'Order not found'], 404);
        }
        if ((int)$order->user_id !== (int)$auth['user_id'] && $auth['role'] !== 'admin') {
            $this->json(['success' => false, 'message' => 'Forbidden'], 403);
        }
        if ($order->status !== 'pending') {
            $this->json(['success' => false, 'message' => 'Chỉ có thể hủy đơn hàng đang chờ xử lý'], 400);
        }

        if (!$this->orderModel->updateStatus((int)$id, 'cancelled')) {
            $this->json(['success' => false, 'message' => 'Hủy đơn thất bại'], 400);
        }

        $this->json(['success' => true, 'message' => 'Đã hủy đơn hàng!']);
    }


    public function history()
    {
        $this->index();
    }

    private function decorateOrders(array $orders, int $userId): array
    {
        $result = [];
        foreach ($orders as $order) {
            $details = $this->orderModel->getOrderDetails((int)$order->id);
            $totalAmount = 0;
            $totalQty = 0;
            foreach ($details as $detail) {
                $totalAmount += ((float)$detail->price * (int)$detail->quantity);
                $totalQty += (int)$detail->quantity;
            }
            $result[] = [
                'order' => $order,
                'details' => $details,
                'total_amount' => $totalAmount,
                'total_qty' => $totalQty,
                'can_view' => $this->canViewOrder($order, ['user_id' => $userId, 'role' => 'user'])
            ];
        }
        return $result;
    }

    private function cartSnapshot(array $cart): array
    {
        $items = [];
        foreach ($cart as $productId => $item) {
            $qty = (int)($item['quantity'] ?? 0);
            $price = (float)($item['price'] ?? 0);
            $items[] = [
                'product_id' => (int)$productId,
                'name' => $item['name'] ?? '',
                'price' => $price,
                'quantity' => $qty,
                'line_total' => $price * $qty,
                'image' => $item['image'] ?? null
            ];
        }
        return $items;
    }

    private function cartSubtotal(array $cart): float
    {
        $subtotal = 0.0;
        foreach ($cart as $item) {
            $subtotal += ((float)($item['price'] ?? 0) * (int)($item['quantity'] ?? 0));
        }
        return $subtotal;
    }

    private function canViewOrder($order, array $auth): bool
    {
        return $this->isAdmin($auth) || ((int)($order->user_id ?? 0) === (int)($auth['user_id'] ?? 0));
    }

    private function isAdmin(array $auth): bool
    {
        return ($auth['role'] ?? null) === 'admin';
    }

    private function requireAuth(): array
    {
        return (new JwtMiddleware())->requireAuth();
    }

    private function requireAdmin(): array
    {
        return (new JwtMiddleware())->requireAdmin();
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

