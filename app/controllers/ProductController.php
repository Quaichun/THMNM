<?php

require_once('app/config/database.php');
require_once('app/models/ProductModel.php');
require_once('app/models/CategoryModel.php');
require_once('app/models/OrderModel.php');
require_once('app/models/AccountModel.php');
require_once('app/helpers/SessionHelper.php');

class ProductController
{
    private $productModel;
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->productModel = new ProductModel($this->db);
        $this->orderModel = new OrderModel($this->db);
        SessionHelper::start();
        SessionHelper::tryRememberLogin($this->db);
    }

    public function index()
    {
        $limit = 10;
        $products = $this->productModel->filterProducts([], 0, $limit);
        $totalProducts = $this->productModel->countFilteredProducts([]);
        $categories = (new CategoryModel($this->db))->getCategories();
        $specOptions = $this->productModel->getDistinctSpecValues(['RAM', 'CPU', 'Dung lượng']);
        include 'app/views/product/list.php';
    }

    public function show($id)
    {
        $product = $this->productModel->getProductById($id);
        $specs = $this->productModel->getSpecsByProductId($id);
        $reviews = $this->productModel->getReviewsByProductId($id);
        $ratingStats = $this->productModel->getRatingStats($id);

        if ($product) {
            include 'app/views/product/show.php';
        } else {
            SessionHelper::setFlash('error', 'Không tìm thấy sản phẩm.');
            header('Location: /Product');
        }
    }

    public function add()
    {
        SessionHelper::requireAdmin();
        $categories = (new CategoryModel($this->db))->getCategories();
        include_once 'app/views/product/add.php';
    }

    public function save()
    {
        SessionHelper::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $price = $_POST['price'] ?? '';
            $category_id = $_POST['category_id'] ?? null;

            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $image = $this->uploadImage($_FILES['image']);
            } else {
                $image = "";
            }

            $result = $this->productModel->addProduct(
                $name,
                $description,
                $price,
                $category_id,
                $image
            );

            if (is_array($result)) {
                $errors = $result;
                $categories = (new CategoryModel($this->db))->getCategories();
                include 'app/views/product/add.php';
            } else {
                // Save specs
                $specs = $_POST['specs'] ?? [];
                $this->productModel->saveSpecs($result, $specs);
                header('Location: /Product');
            }
        }
    }

    public function edit($id)
    {
        SessionHelper::requireAdmin();
        $product = $this->productModel->getProductById($id);
        $categories = (new CategoryModel($this->db))->getCategories();
        $specs = $this->productModel->getSpecsByProductId($id);

        if ($product) {
            include 'app/views/product/edit.php';
        } else {
            echo "Khong thay san pham.";
        }
    }

    public function update()
    {
        SessionHelper::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $name = $_POST['name'];
            $description = $_POST['description'];
            $price = $_POST['price'];
            $category_id = $_POST['category_id'];

            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $image = $this->uploadImage($_FILES['image']);
            } else {
                $image = $_POST['existing_image'];
            }

            $edit = $this->productModel->updateProduct(
                $id,
                $name,
                $description,
                $price,
                $category_id,
                $image
            );

            if (is_array($edit)) {
                $errors = $edit;
                $product = $this->productModel->getProductById($id);
                $categories = (new CategoryModel($this->db))->getCategories();
                $specs = $this->productModel->getSpecsByProductId($id);
                include 'app/views/product/edit.php';
            } elseif ($edit) {
                // Save specs
                $specs = $_POST['specs'] ?? [];
                $this->productModel->saveSpecs($id, $specs);
                header('Location: /Product');
            } else {
                echo "Da xay ra loi khi luu san pham.";
            }
        }
    }

    public function delete($id)
    {
        SessionHelper::requireAdmin();
        $deleted = $this->productModel->deleteProduct($id);
        if (is_array($deleted)) {
            SessionHelper::setFlash('error', reset($deleted));
            header('Location: /Product');
        } elseif ($deleted) {
            header('Location: /Product');
        } else {
            echo "Da xay ra loi khi xoa san pham.";
        }
    }

    private function uploadImage($file)
    {
        $target_dir = "uploads/";

        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $target_file = $target_dir . basename($file["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $check = getimagesize($file["tmp_name"]);

        if ($check === false) {
            throw new Exception("File khong phai la hinh anh.");
        }
        if ($file["size"] > 10 * 1024 * 1024) {
            throw new Exception("Hinh anh co kich thuoc qua lon.");
        }
        if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
            throw new Exception("Chi cho phep JPG, JPEG, PNG va GIF.");
        }
        if (!move_uploaded_file($file["tmp_name"], $target_file)) {
            throw new Exception("Co loi xay ra khi tai len hinh anh.");
        }

        return $target_file;
    }

    public function cart()
    {
        SessionHelper::requireLogin();

        $cart = $_SESSION['cart'] ?? [];
        include 'app/views/product/cart.php';
    }

    public function addToCart($id)
    {
        SessionHelper::requireLogin();
        $product = $this->productModel->getProductById($id);

        if (!$product) {
            header('Location: /Product');
            return;
        }

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['quantity']++;
        } else {
            $_SESSION['cart'][$id] = [
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 1,
                'image' => $product->image
            ];
        }

        $_SESSION['flash']['success'] = 'Da them vao gio hang!';
        $_SESSION['flash']['cart_added'] = [
            'id' => $id,
            'name' => $product->name,
            'price' => $product->price,
            'image' => $product->image,
            'quantity' => $_SESSION['cart'][$id]['quantity']
        ];

        $cartCount = 0;
        foreach ($_SESSION['cart'] as $item) {
            $cartCount += (int)$item['quantity'];
        }

        $isAjax = (
            (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_GET['ajax']) && $_GET['ajax'] == '1')
        );

        if ($isAjax) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode([
                'success' => true,
                'message' => 'Da them vao gio hang!',
                'cart_count' => $cartCount,
                'item' => $_SESSION['flash']['cart_added']
            ]);
            exit;
        }

        $redirectUrl = '/Product/list';
        if (!empty($_SERVER['HTTP_REFERER'])) {
            $refPath  = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);
            $refQuery = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY);

            if (is_string($refPath) && strpos($refPath, '/Product') === 0) {
                $redirectUrl = $refPath . ($refQuery ? ('?' . $refQuery) : '');
            }
        }

        header('Location: ' . $redirectUrl);
        exit;
    }

    public function increaseQuantity($id)
    {
        SessionHelper::requireLogin();
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['quantity']++;
        }
        header('Location: /Product/cart');
    }

    public function decreaseQuantity($id)
    {
        SessionHelper::requireLogin();
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['quantity']--;
            if ($_SESSION['cart'][$id]['quantity'] <= 0) {
                unset($_SESSION['cart'][$id]);
            }
        }
        header('Location: /Product/cart');
    }

    public function removeFromCart($id)
    {
        SessionHelper::requireLogin();
        unset($_SESSION['cart'][$id]);
        header('Location: /Product/cart');
    }

    public function clearCart()
    {
        SessionHelper::requireLogin();

        $_SESSION['cart'] = [];
        header('Location: /Product/cart');
    }

    public function list()
    {
        $products = $this->productModel->getProducts();
        $categories = (new CategoryModel($this->db))->getCategories();
        $specOptions = $this->productModel->getDistinctSpecValues(['RAM', 'CPU', 'Dung lượng']);
        require_once 'app/views/product/list.php';
    }

    public function ajaxFilter()
    {
        $filters = $_POST['filters'] ?? [];
        $offset = (int)($_POST['offset'] ?? 0);
        $limit = (int)($_POST['limit'] ?? 12);

        $products = $this->productModel->filterProducts($filters, $offset, $limit);
        
        // Get total count for the same filters
        $total = $this->productModel->countFilteredProducts($filters);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true, 
            'products' => $products,
            'total' => $total
        ]);
        exit;
    }

    public function liveSearch()
    {
        $query = $_GET['q'] ?? '';
        $products = [];
        if (strlen($query) >= 2) {
            $products = $this->productModel->filterProducts(['search' => $query], 0, 8);
        }
        
        header('Content-Type: application/json');
        echo json_encode($products);
        exit;
    }

    public function checkout()
    {
        SessionHelper::requireLogin();

        $cart = $_SESSION['cart'] ?? [];
        if (empty($cart)) {
            header('Location: /Product/cart');
            return;
        }

        $orderModel = new OrderModel($this->db);
        $nextOrderId = $orderModel->getNextOrderId();

        include 'app/views/product/checkout.php';
    }

    public function placeOrder()
    {
        SessionHelper::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Product/checkout');
            return;
        }

        $cart = $_SESSION['cart'] ?? [];
        if (empty($cart)) {
            header('Location: /Product/cart');
            return;
        }

        $accountModel = new AccountModel($this->db);
        $currentUser = $accountModel->findById(SessionHelper::getUserId());
        if (!$currentUser || empty($currentUser->email_verified_at)) {
            SessionHelper::setFlash('error', 'Ban can xac thuc email truoc khi dat hang.');
            $verifyToken = bin2hex(random_bytes(32));
            if ($currentUser) {
                $accountModel->saveEmailVerifyToken((int)$currentUser->id, hash('sha256', $verifyToken));
                SessionHelper::setFlash('verify_link', '/Account/verifyEmail?token=' . $verifyToken);
            }
            header('Location: /Account/profile?tab=info');
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $payment = trim($_POST['payment'] ?? 'cod');

        $errors = [];
        if (!$name) $errors[] = 'Vui lòng nhập họ tên.';
        if (!$phone) $errors[] = 'Vui lòng nhập số điện thoại.';
        if (!$email) $errors[] = 'Vui lòng nhập email.';
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email không đúng định dạng.';
        }
        if (!$address) $errors[] = 'Vui lòng nhập địa chỉ.';

        if ($payment === 'qr') {
            $payment = 'bank';
        }

        $allowedPayments = ['cod', 'bank'];
        if (!in_array($payment, $allowedPayments, true)) {
            $payment = 'cod';
        }

        if (!empty($errors)) {
            $orderModel = new OrderModel($this->db);
            $nextOrderId = $orderModel->getNextOrderId();
            include 'app/views/product/checkout.php';
            return;
        }

        $orderModel = new OrderModel($this->db);
        $order_id = $orderModel->createOrder($name, $phone, $email, $address, $payment, SessionHelper::getUserId());

        foreach ($cart as $product_id => $item) {
            $orderModel->addOrderDetail(
                $order_id,
                $product_id,
                $item['quantity'],
                $item['price']
            );
        }

        $_SESSION['cart'] = [];
        header('Location: /Product/orderSuccess/' . $order_id);
    }

    public function orderSuccess($id)
    {
        SessionHelper::requireLogin();

        $orderModel = new OrderModel($this->db);
        $order = $orderModel->getOrderById($id);
        $orderDetails = $orderModel->getOrderDetails($id);

        if (!$order) {
            header('Location: /Product');
            return;
        }

        include 'app/views/product/order_success.php';
    }

    public function myOrders()
    {
        SessionHelper::requireLogin();

        $orderModel = new OrderModel($this->db);

        if (SessionHelper::isAdmin()) {
            // Admin: load dashboard + tất cả đơn hàng
            $orders         = $orderModel->getAllOrdersWithTotal();
            $stats          = $orderModel->getRevenueStats();
            
            $range = $_GET['range'] ?? 'month';
            if ($range === 'day') {
                $revenueData = $orderModel->getRevenueByDay();
            } else {
                $revenueData = $orderModel->getRevenueByMonth();
            }

            $revenueBycat   = $orderModel->getRevenueByCategory();
            $topProducts    = $orderModel->getTopProducts(5);

            // Chuẩn bị data chart cho view
            $chartLabels  = [];
            $chartRevenue = [];
            $chartOrders  = [];
            foreach ($revenueData as $r) {
                $chartLabels[]  = $r->label;
                $chartRevenue[] = (float)$r->revenue;
                $chartOrders[]  = (int)$r->order_count;
            }

            include 'app/views/product/admin_orders.php';
        } else {
            // User thường: chỉ xem đơn của mình
            $orders = $orderModel->getOrdersByUserId(
                SessionHelper::getUserId()
            );
            $historyProducts = $orderModel->getPurchasedProductsByUserId(
                SessionHelper::getUserId()
            );
            $historyOrders = [];
            foreach ($orders as $orderItem) {
                $details = $orderModel->getOrderDetails($orderItem->id);
                $totalAmount = 0;
                $totalQty = 0;
                foreach ($details as $detail) {
                    $totalAmount += ((float)$detail->price * (int)$detail->quantity);
                    $totalQty += (int)$detail->quantity;
                }
                $historyOrders[] = (object)[
                    'order' => $orderItem,
                    'details' => $details,
                    'firstItem' => $details[0] ?? null,
                    'totalAmount' => $totalAmount,
                    'totalQty' => $totalQty
                ];
            }
            $deliveredOrders = array_values(array_filter($orders, function ($o) {
                return ($o->status ?? '') === 'delivered';
            }));
            include 'app/views/product/my_orders.php';
        }
    }

    public function orderDetail($id)
    {
        SessionHelper::requireLogin();

        $orderModel = new OrderModel($this->db);
        $order = $orderModel->getOrderById($id);
        $orderDetails = $orderModel->getOrderDetails($id);

        if (!$order) {
            header('Location: /Product/myOrders');
            return;
        }
        if (!SessionHelper::isAdmin() && (int)($order->user_id ?? 0) !== (int)SessionHelper::getUserId()) {
            SessionHelper::setFlash('error', 'Bạn không có quyền xem đơn hàng này.');
            header('Location: /Product/myOrders');
            return;
        }

        include 'app/views/product/order_detail.php';
    }

public function updateOrderStatus($id)
{
SessionHelper::requireAdmin();


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Product/orderDetail/' . (int)$id);
    return;
}

$allowed = [
    'pending',
    'processing',
    'shipping',
    'delivered',
    'cancelled'
];

$status = $_POST['status'] ?? 'pending';

if (!in_array($status, $allowed, true)) {
    $status = 'pending';
}

$orderModel = new OrderModel($this->db);

$updated = $orderModel->updateStatus(
    (int)$id,
    $status
);

if ($updated) {
    $_SESSION['flash']['success']
        = 'Đã cập nhật trạng thái đơn hàng.';
} else {
    $_SESSION['flash']['error']
        = 'Không thể cập nhật trạng thái đơn hàng.';
}

header('Location: /Product/orderDetail/' . (int)$id);
exit;


}


    public function paymentQr()
    {
        $amount = isset($_GET['amount']) ? (int)$_GET['amount'] : 0;
        if ($amount < 0) $amount = 0;

        $code = trim($_GET['code'] ?? '');
        if ($code === '') {
            $code = 'mh0';
        }

        $vietQrUrl = 'https://img.vietqr.io/image/mb-0775632430-compact2.png'
            . '?amount=' . $amount
            . '&addInfo=' . rawurlencode($code)
            . '&accountName=' . rawurlencode('Nguyen Hoai Trung');

        $imgData = null;

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
                $imgData = $res;
            }
        }

        if ($imgData === null) {
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
                    $imgData = $res;
                }
            }

            if ($imgData === null && ini_get('allow_url_fopen')) {
                $res = @file_get_contents($fallbackUrl);
                if ($res !== false) {
                    $imgData = $res;
                }
            }
        }

        if ($imgData !== null) {
            header('Content-Type: image/png');
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            echo $imgData;
            exit;
        }

        http_response_code(500);
        header('Content-Type: text/plain; charset=UTF-8');
        echo 'QR currently unavailable';
        exit;
    }

    public function submitReview()
    {
        SessionHelper::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productId = $_POST['product_id'] ?? null;
            $rating = $_POST['rating'] ?? 5;
            $comment = $_POST['comment'] ?? '';
            $imagePath = null;

            if (isset($_FILES['review_image']) && $_FILES['review_image']['error'] == 0) {
                try {
                    $imagePath = $this->uploadImage($_FILES['review_image']);
                } catch (Exception $e) {
                    SessionHelper::setFlash('error', $e->getMessage());
                    header('Location: /Product/show/' . $productId . '#reviews');
                    exit;
                }
            }

            if ($productId) {
                $userId = SessionHelper::getUserId();
                $result = $this->productModel->addReview($productId, $userId, $rating, $comment, $imagePath);

                if ($result) {
                    SessionHelper::setFlash('success', 'Cảm ơn bạn đã đánh giá!');
                } else {
                    SessionHelper::setFlash('error', 'Có lỗi xảy ra khi gửi đánh giá.');
                }
                header('Location: /Product/show/' . $productId . '#reviews');
                exit;
            }
        }
        header('Location: /Product');
    }
}
?>
