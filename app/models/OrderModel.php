<?php
class OrderModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function createOrder($name, $phone, $address)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO orders (name, phone, address) VALUES (?, ?, ?)"
        );
        $stmt->execute([$name, $phone, $address]);
        return $this->db->lastInsertId();
    }

    public function addOrderDetail($order_id, $product_id, $quantity, $price)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO order_details (order_id, product_id, quantity, price)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$order_id, $product_id, $quantity, $price]);
    }

    public function getOrderById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function getOrderDetails($order_id)
    {
        $stmt = $this->db->prepare(
            "SELECT od.*, p.name as product_name, p.image
             FROM order_details od
             LEFT JOIN product p ON od.product_id = p.id
             WHERE od.order_id = ?"
        );
        $stmt->execute([$order_id]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getAllOrders()
    {
        $stmt = $this->db->query(
            "SELECT * FROM orders ORDER BY created_at DESC"
        );
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}