<?php

class OrderModel
{
    private $conn;
    private $columnExistsCache = [];

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function createOrder(
    $name,
    $phone,
    $email,
    $address,
    $paymentMethod = 'cod',
    $user_id = null
)
{
    $hasStatus  = $this->ordersHasColumn('status');
    $hasEmail   = $this->ordersHasColumn('email');
    $hasPayment = $this->ordersHasColumn('payment_method');
    $hasUserId  = $this->ordersHasColumn('user_id');

    $columns = ['name', 'phone', 'address'];
    $values  = [':name', ':phone', ':address'];

    if ($hasEmail) {
        $columns[] = 'email';
        $values[] = ':email';
    }

    if ($hasPayment) {
        $columns[] = 'payment_method';
        $values[] = ':payment_method';
    }

    if ($hasUserId) {
        $columns[] = 'user_id';
        $values[] = ':user_id';
    }

    if ($hasStatus) {
        $columns[] = 'status';
        $values[] = "'pending'";
    }

    $sql = "INSERT INTO orders (" . implode(',', $columns) . ")
            VALUES (" . implode(',', $values) . ")";

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

    if ($hasUserId) {
        $stmt->bindParam(':user_id', $user_id);
    }

    if ($stmt->execute()) {
        return (int)$this->conn->lastInsertId();
    }

    return false;
}

    public function getOrdersByUserId($user_id)
{
    $sql = "SELECT * 
            FROM orders
            WHERE user_id = :user_id
            ORDER BY created_at DESC";

    $stmt = $this->conn->prepare($sql);

    $stmt->bindParam(
        ':user_id',
        $user_id,
        PDO::PARAM_INT
    );

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_OBJ);
}

    public function getPurchasedProductsByUserId($user_id)
    {
        $sql = "SELECT
                    p.id,
                    p.name,
                    p.image,
                    od.price,
                    MAX(o.created_at) AS last_order_at
                FROM orders o
                INNER JOIN order_details od ON od.order_id = o.id
                INNER JOIN product p ON p.id = od.product_id
                WHERE o.user_id = :user_id
                GROUP BY p.id, p.name, p.image, od.price
                ORDER BY last_order_at DESC
                LIMIT 30";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
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

    public function getRevenueStats()
    {
        // Dùng LEFT JOIN để không bị NULL khi chưa có đơn
        $sql = "SELECT
                    COUNT(DISTINCT o.id) AS total_orders,
                    COALESCE(SUM(od.price * od.quantity), 0) AS total_revenue,
                    COALESCE(SUM(
                        CASE WHEN DATE(o.created_at) = CURDATE()
                        THEN od.price * od.quantity END
                    ), 0) AS today_revenue,
                    COUNT(DISTINCT CASE
                        WHEN DATE(o.created_at) = CURDATE()
                        THEN o.id END
                    ) AS today_orders,
                    COALESCE(SUM(
                        CASE WHEN MONTH(o.created_at) = MONTH(NOW())
                          AND YEAR(o.created_at) = YEAR(NOW())
                        THEN od.price * od.quantity END
                    ), 0) AS month_revenue,
                    COUNT(DISTINCT CASE
                        WHEN MONTH(o.created_at) = MONTH(NOW())
                          AND YEAR(o.created_at) = YEAR(NOW())
                        THEN o.id END
                    ) AS month_orders
                FROM orders o
                LEFT JOIN order_details od ON od.order_id = o.id";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function getRevenueByMonth()
    {
        $sql = "SELECT
                    DATE_FORMAT(o.created_at, '%Y-%m')   AS month,
                    DATE_FORMAT(o.created_at, '%m/%Y')   AS label,
                    COALESCE(SUM(od.price * od.quantity), 0) AS revenue,
                    COUNT(DISTINCT o.id)                 AS order_count
                FROM orders o
                LEFT JOIN order_details od ON od.order_id = o.id
                WHERE o.created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY
                    DATE_FORMAT(o.created_at, '%Y-%m'),
                    DATE_FORMAT(o.created_at, '%m/%Y')
                ORDER BY month ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getRevenueByDay()
    {
        $sql = "SELECT
                    DATE(o.created_at)                   AS day,
                    DATE_FORMAT(o.created_at, '%d/%m')    AS label,
                    COALESCE(SUM(od.price * od.quantity), 0) AS revenue,
                    COUNT(DISTINCT o.id)                 AS order_count
                FROM orders o
                LEFT JOIN order_details od ON od.order_id = o.id
                WHERE o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY 
                    DATE(o.created_at),
                    DATE_FORMAT(o.created_at, '%d/%m')
                ORDER BY day ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

public function getRevenueByCategory()
{
    $sql = "SELECT
                COALESCE(c.name, 'Chưa phân loại')      AS category,
                COALESCE(SUM(od.price * od.quantity), 0) AS revenue,
                COALESCE(SUM(od.quantity), 0)            AS quantity
            FROM order_details od
            JOIN product p       ON p.id = od.product_id
            LEFT JOIN category c ON c.id = p.category_id
            GROUP BY c.id, COALESCE(c.name, 'Chưa phân loại')
            ORDER BY revenue DESC";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_OBJ);
}

public function getTopProducts($limit = 5)
{
    $sql = "SELECT
                p.name,
                COALESCE(SUM(od.quantity), 0)            AS total_qty,
                COALESCE(SUM(od.price * od.quantity), 0) AS total_revenue
            FROM order_details od
            JOIN product p ON p.id = od.product_id
            GROUP BY od.product_id, p.name
            ORDER BY total_qty DESC
            LIMIT :lim";

    $stmt = $this->conn->prepare($sql);
    $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_OBJ);
}

public function getAllOrdersWithTotal()
{
    $sql = "SELECT
                o.id,
                o.name,
                o.phone,
                o.address,
                o.created_at,
                o.user_id,
                COALESCE(o.status, 'pending')            AS status,
                COALESCE(SUM(od.price * od.quantity), 0) AS total_amount,
                COUNT(od.id)                             AS item_count
            FROM orders o
            LEFT JOIN order_details od ON od.order_id = o.id
            GROUP BY
                o.id,
                o.name,
                o.phone,
                o.address,
                o.created_at,
                o.user_id,
                o.status
            ORDER BY o.created_at DESC";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_OBJ);
}

    
}
?>
