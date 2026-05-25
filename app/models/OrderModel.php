<?php

class OrderModel
{
    private $conn;
    private $columnExistsCache = [];

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function createOrder($name, $phone, $email, $address, $paymentMethod = 'cod')
    {
        $hasStatus  = $this->ordersHasColumn('status');
        $hasEmail   = $this->ordersHasColumn('email');
        $hasPayment = $this->ordersHasColumn('payment_method');

        $columns = ['name', 'phone', 'address'];
        $values  = [':name', ':phone', ':address'];

        if ($hasEmail) {
            $columns[] = 'email';
            $values[]  = ':email';
        }
        if ($hasPayment) {
            $columns[] = 'payment_method';
            $values[]  = ':payment_method';
        }
        if ($hasStatus) {
            $columns[] = 'status';
            $values[]  = "'pending'";
        }

        $sql = "INSERT INTO orders (" . implode(', ', $columns) . ")
                VALUES (" . implode(', ', $values) . ")";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);
        if ($hasEmail) {
            $stmt->bindParam(':email', $email);
        }
        if ($hasPayment) {
            $stmt->bindParam(':payment_method', $paymentMethod);
        }

        if ($stmt->execute()) {
            return (int)$this->conn->lastInsertId();
        }

        return false;
    }

    public function addOrderDetail($order_id, $product_id, $quantity, $price)
    {
        $sql = "INSERT INTO order_details (order_id, product_id, quantity, price)
                VALUES (:order_id, :product_id, :quantity, :price)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindParam(':price', $price);

        return $stmt->execute();
    }

    public function getOrderById($id)
    {
        $sql = "SELECT * FROM orders WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function getOrderDetails($order_id)
    {
        $sql = "SELECT od.*,
                       p.name  AS product_name,
                       p.image AS product_image
                FROM order_details od
                LEFT JOIN product p ON od.product_id = p.id
                WHERE od.order_id = :order_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function updateStatus($order_id, $status)
    {
        if (!$this->ordersHasColumn('status')) {
            return false;
        }

        $sql = "UPDATE orders SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $order_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getAllOrders()
    {
        $sql = "SELECT * FROM orders ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getNextOrderId()
    {
        $stmt = $this->conn->query("SHOW TABLE STATUS LIKE 'orders'");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row || empty($row['Auto_increment'])) {
            return 1;
        }
        return (int)$row['Auto_increment'];
    }

    private function ordersHasColumn($columnName)
    {
        if (array_key_exists($columnName, $this->columnExistsCache)) {
            return $this->columnExistsCache[$columnName];
        }

        $columnNameSafe = str_replace("'", "''", $columnName);
        $stmt = $this->conn->query("SHOW COLUMNS FROM orders LIKE '" . $columnNameSafe . "'");
        $this->columnExistsCache[$columnName] = (bool)$stmt->fetch(PDO::FETCH_ASSOC);
        return $this->columnExistsCache[$columnName];
    }
}
?>
