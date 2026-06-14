<?php

require_once 'app/config/database.php';
require_once 'app/models/OrderModel.php';
require_once 'app/middlewares/JwtMiddleware.php';

class PaymentApiController
{
    private $db;
    private $orderModel;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->orderModel = new OrderModel($this->db);
    }

    public function index()
    {
        $this->methods();
    }

    public function methods()
    {
        $this->json([
            'success' => true,
            'methods' => [
                ['code' => 'cod', 'label' => 'Thanh toan khi nhan hang'],
                ['code' => 'bank', 'label' => 'Chuyen khoan / QR']
            ]
        ]);
    }

    public function qr()
    {
        $amount = isset($_GET['amount']) ? (int)$_GET['amount'] : 0;
        if ($amount < 0) $amount = 0;

        $code = trim((string)($_GET['code'] ?? 'mh0'));
        if ($code === '') $code = 'mh0';

        $imgData = $this->fetchQrImage($amount, $code);
        if ($imgData !== null && (($_GET['format'] ?? '') === 'image')) {
            header('Content-Type: image/png');
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            echo $imgData;
            exit;
        }

        $this->json([
            'success' => true,
            'amount' => $amount,
            'code' => $code,
            'qr_url' => '/api/payment/qr?amount=' . $amount . '&code=' . rawurlencode($code) . '&format=image'
        ]);
    }

    public function create()
    {
        $auth = (new JwtMiddleware())->requireAuth();
        $payload = $this->getRequestData();
        $fields = $payload['fields'];

        $orderId = (int)($fields['order_id'] ?? 0);
        $amount = (int)($fields['amount'] ?? 0);
        $method = trim((string)($fields['method'] ?? 'bank'));

        if (!in_array($method, ['cod', 'bank', 'qr'], true)) {
            $this->json(['success' => false, 'message' => 'Invalid method'], 422);
        }
        if ($method === 'qr') {
            $method = 'bank';
        }

        if ($orderId > 0) {
            $order = $this->orderModel->getOrderById($orderId);
            if (!$order) {
                $this->json(['success' => false, 'message' => 'Order not found'], 404);
            }
            if ((int)($order->user_id ?? 0) !== (int)$auth['user_id'] && ($auth['role'] ?? null) !== 'admin') {
                $this->json(['success' => false, 'message' => 'Forbidden'], 403);
            }
            $amount = (int)$this->orderTotal($orderId);
        }

        $code = 'mh' . ($orderId > 0 ? $orderId : time());
        $this->json([
            'success' => true,
            'payment' => [
                'method' => $method,
                'amount' => $amount,
                'code' => $code,
                'qr_url' => '/api/payment/qr?amount=' . $amount . '&code=' . rawurlencode($code) . '&format=image'
            ]
        ], 201);
    }

    public function confirm()
    {
        $auth = (new JwtMiddleware())->requireAuth();
        $payload = $this->getRequestData();
        $fields = $payload['fields'];

        $orderId = (int)($fields['order_id'] ?? 0);
        $status = trim((string)($fields['status'] ?? 'processing'));
        
        $order = $this->orderModel->getOrderById($orderId);
        if (!$order) {
            $this->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        // Check if already paid/processed
        if (!in_array($order->status, ['pending', 'processing'], true) && ($auth['role'] ?? '') !== 'admin') {
            $this->json(['success' => false, 'message' => 'Đơn hàng này không thể thanh toán lại hoặc đã được xử lý'], 400);
        }

        if ((int)($order->user_id ?? 0) !== (int)$auth['user_id'] && ($auth['role'] ?? null) !== 'admin') {
            $this->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $this->orderModel->updateStatus($orderId, $status);

        $this->json([
            'success' => true,
            'message' => 'Xác nhận thanh toán thành công!',
            'order_id' => $orderId,
            'status' => $status
        ]);
    }


    private function orderTotal($orderId): float
    {
        $total = 0.0;
        foreach ($this->orderModel->getOrderDetails($orderId) as $detail) {
            $total += ((float)$detail->price * (int)$detail->quantity);
        }
        return $total;
    }

    private function fetchQrImage(int $amount, string $code): ?string
    {
        $vietQrUrl = 'https://img.vietqr.io/image/mb-0775632430-compact2.png'
            . '?amount=' . $amount
            . '&addInfo=' . rawurlencode($code)
            . '&accountName=' . rawurlencode('Nguyen Hoai Trung');

        if (function_exists('curl_init')) {
            $ch = curl_init($vietQrUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            curl_setopt($ch, CURLOPT_USERAGENT, 'ShopTech-QR/1.0');
            $res = curl_exec($ch);
            $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($res !== false && $httpCode >= 200 && $httpCode < 300) {
                return $res;
            }
        }

        $fallbackUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data='
            . rawurlencode('MB|0775632430|Nguyen Hoai Trung|' . $code . '|amount:' . $amount);

        if (function_exists('curl_init')) {
            $ch = curl_init($fallbackUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            $res = curl_exec($ch);
            $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($res !== false && $httpCode >= 200 && $httpCode < 300) {
                return $res;
            }
        }

        if (ini_get('allow_url_fopen')) {
            $res = @file_get_contents($fallbackUrl);
            if ($res !== false) {
                return $res;
            }
        }

        return null;
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
