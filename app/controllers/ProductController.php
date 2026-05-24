<?php

require_once('app/config/database.php');
require_once('app/models/ProductModel.php');
require_once('app/models/CategoryModel.php');
require_once('app/models/OrderModel.php');

class ProductController
{
    private $productModel;
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->productModel = new ProductModel($this->db);
        $this->orderModel = new OrderModel($this->db);
    }

    public function index()
    {
        $products = $this->productModel->getProducts();
        include 'app/views/product/list.php';
    }

    public function show($id)
    {
        $product = $this->productModel->getProductById($id);

        if ($product) {
            include 'app/views/product/show.php';
        } else {
            echo "Không thấy sản phẩm.";
        }
    }

    public function add()
    {
        $categories = (new CategoryModel($this->db))->getCategories();
        include_once 'app/views/product/add.php';
    }

    public function save()
    {
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

                header('Location: /Product');
            }
        }
    }

    public function edit($id)
    {
        $product = $this->productModel->getProductById($id);
        $categories = (new CategoryModel($this->db))->getCategories();

        if ($product) {
            include 'app/views/product/edit.php';
        } else {
            echo "Không thấy sản phẩm.";
        }
    }

    public function update()
    {
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

            if ($edit) {
                header('Location: /Product');
            } else {
                echo "Đã xảy ra lỗi khi lưu sản phẩm.";
            }
        }
    }

    public function delete($id)
    {
        if ($this->productModel->deleteProduct($id)) {
            header('Location: /Product');
        } else {
            echo "Đã xảy ra lỗi khi xóa sản phẩm.";
        }
    }

    private function uploadImage($file)
    {
        $target_dir = "uploads/";

        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $target_file = $target_dir . basename($file["name"]);

        $imageFileType = strtolower(
            pathinfo($target_file, PATHINFO_EXTENSION)
        );

        $check = getimagesize($file["tmp_name"]);

        if ($check === false) {
            throw new Exception("File không phải là hình ảnh.");
        }

        if ($file["size"] > 10 * 1024 * 1024) {
            throw new Exception("Hình ảnh có kích thước quá lớn.");
        }

        if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
            throw new Exception(
                "Chỉ cho phép các định dạng JPG, JPEG, PNG và GIF."
            );
        }

        if (!move_uploaded_file($file["tmp_name"], $target_file)) {
            throw new Exception(
                "Có lỗi xảy ra khi tải lên hình ảnh."
            );
        }

        return $target_file;
    }

    public function cart()
{
    session_start();
    $cart = $_SESSION['cart'] ?? [];
    include 'app/views/product/cart.php';
}

public function addToCart($id)
{
    session_start();
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
            'name'     => $product->name,
            'price'    => $product->price,
            'quantity' => 1,
            'image'    => $product->image
        ];
    }

    header('Location: /Product/cart');
}

public function increaseQuantity($id)
{
    session_start();
    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]['quantity']++;
    }
    header('Location: /Product/cart');
}

public function decreaseQuantity($id)
{
    session_start();
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
    session_start();
    unset($_SESSION['cart'][$id]);
    header('Location: /Product/cart');
}

public function clearCart()
{
    session_start();
    $_SESSION['cart'] = [];
    header('Location: /Product/cart');
}

    public function list()
    {
        $products = $this->productModel->getProducts();
        require_once 'app/views/product/list.php';
    }

public function checkout()
{
    if (session_status() === PHP_SESSION_NONE) session_start();

    $cart = $_SESSION['cart'] ?? [];
    if (empty($cart)) {
        header('Location: /Product/cart');
        return;
    }

    include 'app/views/product/checkout.php';
}

public function placeOrder()
{
    if (session_status() === PHP_SESSION_NONE) session_start();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: /Product/checkout');
        return;
    }

    $cart = $_SESSION['cart'] ?? [];
    if (empty($cart)) {
        header('Location: /Product/cart');
        return;
    }

    $name    = trim($_POST['name']    ?? '');
    $phone   = trim($_POST['phone']   ?? '');
    $address = trim($_POST['address'] ?? '');

    $errors = [];
    if (!$name)    $errors[] = 'Vui lòng nhập họ tên.';
    if (!$phone)   $errors[] = 'Vui lòng nhập số điện thoại.';
    if (!$address) $errors[] = 'Vui lòng nhập địa chỉ.';

    if (!empty($errors)) {
        include 'app/views/product/checkout.php';
        return;
    }

    $orderModel = new OrderModel($this->db);
    $order_id   = $orderModel->createOrder($name, $phone, $address);

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
    if (session_status() === PHP_SESSION_NONE) session_start();

    $orderModel   = new OrderModel($this->db);
    $order        = $orderModel->getOrderById($id);
    $orderDetails = $orderModel->getOrderDetails($id);

    if (!$order) {
        header('Location: /Product');
        return;
    }

    include 'app/views/product/order_success.php';
}

public function myOrders()
{
    if (session_status() === PHP_SESSION_NONE) session_start();

    $orderModel = new OrderModel($this->db);
    $orders     = $orderModel->getAllOrders();

    include 'app/views/product/my_orders.php';
}

public function orderDetail($id)
{
    if (session_status() === PHP_SESSION_NONE) session_start();

    $orderModel   = new OrderModel($this->db);
    $order        = $orderModel->getOrderById($id);
    $orderDetails = $orderModel->getOrderDetails($id);

    if (!$order) {
        header('Location: /Product/myOrders');
        return;
    }

    include 'app/views/product/order_detail.php';
}
}
?>