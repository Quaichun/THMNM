<?php

require_once('app/config/database.php');
require_once('app/models/OrderModel.php');

class OrderController
{
    private $orderModel;
    private $db;

    public function __construct()
    {
        $this->db         = (new Database())->getConnection();
        $this->orderModel = new OrderModel($this->db);
    }

    /* ─────────────────────────────────────────
       Xác nhận đơn hàng sau khi đặt
       Route: /Order/orderConfirmation?id=xxx
    ───────────────────────────────────────── */
    public function orderConfirmation()
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if (!$id) {
            header('Location: /Product/');
            return;
        }

        $order        = $this->orderModel->getOrderById($id);
        $orderDetails = $this->orderModel->getOrderDetails($id);

        if (!$order) {
            echo "Không tìm thấy đơn hàng.";
            return;
        }

        $total = 0;
        foreach ($orderDetails as $detail) {
            $total += $detail->price * $detail->quantity;
        }

        include 'app/views/order/orderConfirmation.php';
    }

    /* ─────────────────────────────────────────
       Chi tiết đơn hàng + cập nhật trạng thái
       Route: /Order/orderDetail/1  hoặc  ?id=1
    ───────────────────────────────────────── */
    public function orderDetail($id = null)
    {
        // Hỗ trợ cả /orderDetail/1 và ?id=1
        if (!$id) $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $id = (int)$id;

        if (!$id) {
            header('Location: /Product/myOrders');
            return;
        }

        $order        = $this->orderModel->getOrderById($id);
        $orderDetails = $this->orderModel->getOrderDetails($id);

        if (!$order) {
            echo "Không tìm thấy đơn hàng.";
            return;
        }

        include 'app/views/product/order_detail.php';
    }

    /* ─────────────────────────────────────────
       Cập nhật trạng thái đơn hàng
       Route: POST /Order/updateStatus
    ───────────────────────────────────────── */
    public function updateStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Order/list');
            return;
        }

        $orderId = (int)($_POST['order_id'] ?? 0);
        $status  = $_POST['status'] ?? '';

        $allowedStatuses = ['pending', 'confirmed', 'processing', 'shipping', 'delivered', 'cancelled'];

        if (!$orderId || !in_array($status, $allowedStatuses)) {
            header('Location: /Order/list');
            return;
        }

        $updated = $this->orderModel->updateStatus($orderId, $status);

        // Redirect về trang chi tiết
        header('Location: /Order/orderDetail/' . $orderId);
        exit;
    }

    /* ─────────────────────────────────────────
       Danh sách tất cả đơn hàng
       Route: /Order/list
    ───────────────────────────────────────── */
    public function list()
    {
        $orders = $this->orderModel->getAllOrders();
        include 'app/views/order/list.php';
    }

    /* ─────────────────────────────────────────
       Đơn hàng của tôi (user)
       Route: /Product/myOrders
    ───────────────────────────────────────── */
    public function myOrders()
    {
        $orders = $this->orderModel->getAllOrders();
        include 'app/views/order/list.php';
    }
}
?>