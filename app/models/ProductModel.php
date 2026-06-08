<?php

class ProductModel
{
    private $conn;
    private $table_name = "product";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getProducts()
    {
        $query = "SELECT p.id, p.name, p.description, p.price, p.image, p.category_id, c.name as category_name
                  FROM " . $this->table_name . " p
                  LEFT JOIN category c ON p.category_id = c.id";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_OBJ);

        return $result;
    }

    public function getProductById($id)
    {
        $query = "SELECT p.*, c.name as category_name
                  FROM " . $this->table_name . " p
                  LEFT JOIN category c ON p.category_id = c.id
                  WHERE p.id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_OBJ);

        return $result;
    }

    public function addProduct($name, $description, $price, $category_id, $image)
    {
        $errors = [];

        if (empty($name)) {
            $errors['name'] = 'Tên sản phẩm không được để trống';
        }

        if (empty($description)) {
            $errors['description'] = 'Mô tả không được để trống';
        }

        if (!is_numeric($price) || $price < 0) {
            $errors['price'] = 'Giá sản phẩm không hợp lệ';
        }

        if ($category_id !== null && $category_id !== '' && !$this->categoryExists($category_id)) {
            $errors['category_id'] = 'Danh muc khong hop le';
        }

        if (count($errors) > 0) {
            return $errors;
        }

        $query = "INSERT INTO " . $this->table_name . "
                  (name, description, price, category_id, image)
                  VALUES (:name, :description, :price, :category_id, :image)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindValue(':price', $price);
        $stmt->bindValue(':category_id', $category_id ?: null, $category_id ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':image', $image);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    public function updateProduct(
        $id,
        $name,
        $description,
        $price,
        $category_id,
        $image = null
    ) {
        $errors = [];

        if (trim((string)$name) === '') {
            $errors['name'] = 'Ten san pham khong duoc de trong';
        }

        if (trim((string)$description) === '') {
            $errors['description'] = 'Mo ta khong duoc de trong';
        }

        if (!is_numeric($price) || $price < 0) {
            $errors['price'] = 'Gia san pham khong hop le';
        }

        if ($category_id !== null && $category_id !== '' && !$this->categoryExists($category_id)) {
            $errors['category_id'] = 'Danh muc khong hop le';
        }

        if (count($errors) > 0) {
            return $errors;
        }

        $query = "UPDATE " . $this->table_name . "
                  SET name = :name, description = :description, price = :price, 
                      category_id = :category_id";

        if ($image) {
            $query .= ", image = :image";
        }

        $query .= " WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindValue(':price', $price);
        $stmt->bindValue(':category_id', $category_id ?: null, $category_id ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);

        if ($image) {
            $stmt->bindParam(':image', $image);
        }

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    public function deleteProduct($id)
    {
        if ($this->hasRelatedRecords($id)) {
            return [
                'constraint' => 'Khong the xoa san pham vi da co du lieu lien quan.'
            ];
        }

        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    private function categoryExists($category_id)
    {
        $sql = "SELECT COUNT(*) FROM category WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', (int)$category_id, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn() > 0;
    }

    private function hasRelatedRecords($id)
    {
        $relations = [
            ['table' => 'order_details', 'column' => 'product_id']
        ];

        foreach ($relations as $relation) {
            if (!$this->tableExists($relation['table'])) {
                continue;
            }

            $sql = "SELECT COUNT(*) FROM {$relation['table']} WHERE {$relation['column']} = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
            $stmt->execute();

            if ((int)$stmt->fetchColumn() > 0) {
                return true;
            }
        }

        return false;
    }

    private function tableExists($table)
    {
        $stmt = $this->conn->prepare("SHOW TABLES LIKE :table_name");
        $stmt->bindValue(':table_name', $table);
        $stmt->execute();
        return (bool)$stmt->fetchColumn();
    }

    public function getSpecsByProductId($id)
    {
        $sql = "SELECT * FROM product_specs WHERE product_id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getReviewsByProductId($id)
    {
        $sql = "SELECT r.*, a.fullname, a.avatar 
                FROM reviews r 
                JOIN account a ON r.user_id = a.id 
                WHERE r.product_id = :id 
                ORDER BY r.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getRatingStats($productId)
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    AVG(rating) as average,
                    COUNT(CASE WHEN rating = 5 THEN 1 END) as star5,
                    COUNT(CASE WHEN rating = 4 THEN 1 END) as star4,
                    COUNT(CASE WHEN rating = 3 THEN 1 END) as star3,
                    COUNT(CASE WHEN rating = 2 THEN 1 END) as star2,
                    COUNT(CASE WHEN rating = 1 THEN 1 END) as star1
                FROM reviews 
                WHERE product_id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $productId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function addReview($productId, $userId, $rating, $comment, $image = null)
    {
        $sql = "INSERT INTO reviews (product_id, user_id, rating, comment, image) 
                VALUES (:pid, :uid, :rating, :comment, :image)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':pid', $productId);
        $stmt->bindParam(':uid', $userId);
        $stmt->bindParam(':rating', $rating);
        $stmt->bindParam(':comment', $comment);
        $stmt->bindParam(':image', $image);

        return $stmt->execute();
    }

    public function saveSpecs($productId, $specs)
    {
        // Xóa thông số cũ
        $sql = "DELETE FROM product_specs WHERE product_id = :pid";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':pid', $productId);
        $stmt->execute();

        // Thêm thông số mới
        $sql = "INSERT INTO product_specs (product_id, spec_name, spec_value) VALUES (:pid, :name, :value)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':pid', $productId);

        foreach ($specs as $name => $value) {
            if (is_array($value)) {
                $name = $value['name'] ?? '';
                $value = $value['value'] ?? '';
            }

            if (trim((string)$name) === '' || trim((string)$value) === '') continue;
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':value', $value);
            $stmt->execute();
        }

        return true;
    }

    public function filterProducts($filters = [], $offset = 0, $limit = 12)
    {
        $sql = "SELECT p.*, c.name as category_name FROM product p 
                LEFT JOIN category c ON p.category_id = c.id 
                WHERE 1=1";
        
        $params = [];

        if (!empty($filters['category'])) {
            $sql .= " AND c.id = :category_id";
            $params[':category_id'] = $filters['category'];
        }

        if (!empty($filters['min_price'])) {
            $sql .= " AND p.price >= :min_price";
            $params[':min_price'] = $filters['min_price'];
        }

        if (!empty($filters['max_price'])) {
            $sql .= " AND p.price <= :max_price";
            $params[':max_price'] = $filters['max_price'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (p.name LIKE :search OR p.description LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['specs'])) {
            foreach ($filters['specs'] as $specName => $specValue) {
                if (empty($specValue)) continue;
                $sql .= " AND p.id IN (SELECT product_id FROM product_specs WHERE spec_name = :sname_$specName AND spec_value = :sval_$specName)";
                $params[":sname_$specName"] = $specName;
                $params[":sval_$specName"] = $specValue;
            }
        }

        $sql .= " ORDER BY p.id ASC LIMIT :offset, :limit";
        
        $stmt = $this->conn->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function countFilteredProducts($filters = [])
    {
        $sql = "SELECT COUNT(*) FROM product p
                LEFT JOIN category c ON p.category_id = c.id
                WHERE 1=1";
        
        $params = [];

        if (!empty($filters['category'])) {
            $sql .= " AND c.id = :category_id";
            $params[':category_id'] = $filters['category'];
        }

        if (!empty($filters['min_price'])) {
            $sql .= " AND p.price >= :min_price";
            $params[':min_price'] = $filters['min_price'];
        }

        if (!empty($filters['max_price'])) {
            $sql .= " AND p.price <= :max_price";
            $params[':max_price'] = $filters['max_price'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (p.name LIKE :search OR p.description LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['specs'])) {
            foreach ($filters['specs'] as $specName => $specValue) {
                if (empty($specValue)) continue;
                $sql .= " AND p.id IN (SELECT product_id FROM product_specs WHERE spec_name = :sname_$specName AND spec_value = :sval_$specName)";
                $params[":sname_$specName"] = $specName;
                $params[":sval_$specName"] = $specValue;
            }
        }
        
        $stmt = $this->conn->prepare($sql);
        foreach ($params as $key => $val) {
            if (is_numeric($val)) {
                $stmt->bindValue($key, $val, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $val, PDO::PARAM_STR);
            }
        }
        
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getDistinctSpecValues($specNames = ['RAM', 'CPU', 'Dung lượng'])
    {
        $results = [];
        foreach ($specNames as $name) {
            $sql = "SELECT DISTINCT spec_value FROM product_specs WHERE spec_name = :name ORDER BY spec_value ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':name', $name);
            $stmt->execute();
            $results[$name] = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }
        return $results;
    }
}
?>
