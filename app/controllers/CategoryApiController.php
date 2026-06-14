<?php

require_once('app/config/database.php');
require_once('app/models/CategoryModel.php');
require_once('app/middlewares/JwtMiddleware.php');

class CategoryApiController
{
    private $db;
    private $categoryModel;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->categoryModel = new CategoryModel($this->db);
    }

    public function index()
    {
        $this->json($this->categoryModel->getCategories());
    }

    public function show($id)
    {
        $category = $this->categoryModel->getCategoryById((int)$id);
        if (!$category) {
            $this->json(['success' => false, 'message' => 'Category not found'], 404);
        }

        $this->json($category);
    }

    public function store()
    {
        $mw = new JwtMiddleware();
        $mw->requireAdmin();

        try {
            $payload = $this->getRequestData();
            $name = trim((string)($payload['fields']['name'] ?? ''));
            $description = trim((string)($payload['fields']['description'] ?? ''));

            if ($name === '') {
                $this->json(['success' => false, 'message' => 'Tên danh mục không được để trống'], 422);
            }

            $result = $this->categoryModel->addCategory($name, $description);
            if (!$result) {
                $this->json(['success' => false, 'message' => 'Create failed'], 400);
            }

            $category = $this->categoryModel->getCategoryById((int)$this->db->lastInsertId());
            $this->json(['success' => true, 'message' => 'Category created', 'category' => $category], 201);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function update($id)
    {
        $mw = new JwtMiddleware();
        $mw->requireAdmin();

        try {
            $category = $this->categoryModel->getCategoryById((int)$id);
            if (!$category) {
                $this->json(['success' => false, 'message' => 'Category not found'], 404);
            }

            $payload = $this->getRequestData('PUT');
            $name = trim((string)($payload['fields']['name'] ?? $category->name));
            $description = trim((string)($payload['fields']['description'] ?? $category->description));

            if ($name === '') {
                $this->json(['success' => false, 'message' => 'Tên danh mục không được để trống'], 422);
            }

            $result = $this->categoryModel->updateCategory((int)$id, $name, $description);
            if (!$result) {
                $this->json(['success' => false, 'message' => 'Update failed'], 400);
            }

            $category = $this->categoryModel->getCategoryById((int)$id);
            $this->json(['success' => true, 'message' => 'Category updated', 'category' => $category]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function destroy($id)
    {
        $mw = new JwtMiddleware();
        $mw->requireAdmin();

        $category = $this->categoryModel->getCategoryById((int)$id);
        if (!$category) {
            $this->json(['success' => false, 'message' => 'Category not found'], 404);
        }

        if ($this->hasRelatedProducts((int)$id)) {
            $this->json(['success' => false, 'message' => 'Không thể xóa danh mục vì vẫn còn sản phẩm thuộc danh mục này'], 409);
        }

        $result = $this->categoryModel->deleteCategory((int)$id);
        if (!$result) {
            $this->json(['success' => false, 'message' => 'Delete failed'], 400);
        }

        $this->json(['success' => true, 'message' => 'Category deleted']);
    }


    public function methodNotAllowed()
    {
        $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
    }

    private function getRequestData($overrideMethod = null)
    {
        $method = $overrideMethod ?: ($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (stripos($contentType, 'application/json') !== false) {
            $raw = file_get_contents('php://input');
            $decoded = json_decode($raw, true);
            return ['fields' => is_array($decoded) ? $decoded : [], 'files' => []];
        }

        if ($method === 'POST') {
            return ['fields' => $_POST, 'files' => $_FILES];
        }

        $raw = file_get_contents('php://input');
        $fields = [];
        parse_str($raw, $fields);
        return ['fields' => $fields, 'files' => []];
    }

    private function hasRelatedProducts($categoryId)
    {
        $sql = "SELECT COUNT(*) FROM product WHERE category_id = :category_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':category_id', (int)$categoryId, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn() > 0;
    }

    private function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

?>
