<?php

require_once 'app/config/database.php';
require_once 'app/models/ProductModel.php';
require_once 'app/models/CategoryModel.php';
require_once 'app/middlewares/JwtMiddleware.php';

class ProductApiController
{
    private $db;
    private $productModel;
    private $categoryModel;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->productModel = new ProductModel($this->db);
        $this->categoryModel = new CategoryModel($this->db);
    }

    public function index()
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = (int)($_GET['limit'] ?? 20);
        $limit = $limit > 0 ? min($limit, 100) : 12;
        $offset = ($page - 1) * $limit;

        $filters = $this->buildFilters($_GET);
        $products = $this->productModel->filterProducts($filters, $offset, $limit);
        $total = (int)$this->productModel->countFilteredProducts($filters);

        $this->json([
            'success' => true,
            'products' => $products,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => (int)ceil($total / $limit)
            ]
        ]);
    }

    public function search()
    {
        $_GET['search'] = $_GET['q'] ?? '';
        $this->index();
    }

    public function show($id)
    {
        $product = $this->productModel->getProductById((int)$id);
        if (!$product) {
            $this->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        $this->json([
            'success' => true,
            'product' => $product,
            'specs' => $this->productModel->getSpecsByProductId((int)$id),
            'reviews' => $this->productModel->getReviewsByProductId((int)$id),
            'rating' => $this->productModel->getRatingStats((int)$id)
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        $payload = $this->getRequestData();
        $fields = $payload['fields'];

        $name = trim((string)($fields['name'] ?? ''));
        $description = trim((string)($fields['description'] ?? ''));
        $price = $fields['price'] ?? null;
        $categoryId = (int)($fields['category_id'] ?? 0);

        $errors = [];
        if ($name === '') $errors['name'] = 'name is required';
        if (!is_numeric($price) || (float)$price <= 0) $errors['price'] = 'price must be > 0';
        if ($categoryId <= 0 || !$this->categoryModel->getCategoryById($categoryId)) {
            $errors['category_id'] = 'invalid category';
        }

        // Image validation
        $image = $this->extractImagePath($payload);
        if ($image === null && empty($fields['image'])) {
            $errors['image'] = 'image is required';
        }

        if ($errors) {
            $this->json(['success' => false, 'errors' => $errors], 422);
        }

        $result = $this->productModel->addProduct($name, $description, $price, $categoryId, $image);
        if (!$result) {
            $this->json(['success' => false, 'message' => 'Create failed'], 400);
        }

        // Save specs if any
        if (!empty($fields['specs']) && is_array($fields['specs'])) {
            foreach ($fields['specs'] as $spec) {
                $sName = trim((string)($spec['name'] ?? ''));
                $sValue = trim((string)($spec['value'] ?? ''));
                if ($sName !== '' && $sValue !== '') {
                    $this->productModel->addSpec((int)$result, $sName, $sValue);
                }
            }
        }

        $this->json([
            'success' => true,
            'message' => 'Product created',
            'product' => $this->productModel->getProductById((int)$result)
        ], 201);
    }


    public function update($id)
    {
        $this->requireAdmin();
        $product = $this->productModel->getProductById((int)$id);
        if (!$product) {
            $this->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        $payload = $this->getRequestData();
        $fields = $payload['fields'];

        $name = trim((string)($fields['name'] ?? $product->name));
        $description = trim((string)($fields['description'] ?? $product->description));
        $price = $fields['price'] ?? $product->price;
        $categoryId = (int)($fields['category_id'] ?? $product->category_id);

        $errors = [];
        if ($name === '') $errors['name'] = 'name is required';
        if (!is_numeric($price) || (float)$price <= 0) $errors['price'] = 'price must be > 0';

        if ($errors) {
            $this->json(['success' => false, 'errors' => $errors], 422);
        }

        $image = $this->extractImagePath($payload);
        if ($image === null) {
            $image = $product->image ?? null;
        }

        if (!$this->productModel->updateProduct((int)$id, $name, $description, $price, $categoryId, $image)) {
            $this->json(['success' => false, 'message' => 'Update failed'], 400);
        }

        $this->json([
            'success' => true,
            'message' => 'Product updated',
            'product' => $this->productModel->getProductById((int)$id)
        ]);
    }

    public function destroy($id)
    {
        $this->requireAdmin();
        $product = $this->productModel->getProductById((int)$id);
        if (!$product) {
            $this->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        if (!$this->productModel->deleteProduct((int)$id)) {
            $this->json(['success' => false, 'message' => 'Delete failed'], 400);
        }

        $this->json(['success' => true, 'message' => 'Product deleted']);
    }

    private function buildFilters(array $query): array
    {
        $filters = [];
        // q or search for name-based search
        $search = $query['q'] ?? $query['search'] ?? '';
        if ($search !== '') $filters['search'] = trim((string)$search);

        // category_id or category
        $catId = $query['category_id'] ?? $query['category'] ?? 0;
        if ((int)$catId > 0) $filters['category'] = (int)$catId;

        // price range
        if (($query['min_price'] ?? '') !== '') $filters['min_price'] = (float)$query['min_price'];
        if (($query['max_price'] ?? '') !== '') $filters['max_price'] = (float)$query['max_price'];

        // sorting
        if (!empty($query['sort'])) $filters['sort'] = $this->normalizeSort((string)$query['sort']);

        // product specs (RAM, CPU, etc.)
        foreach ($query as $key => $val) {
            if (strpos($key, 'spec_') === 0 && !empty($val)) {
                $specName = substr($key, 5);
                $filters['specs'][$specName] = $val;
            }
        }

        return $filters;
    }

    private function normalizeSort(string $sort): string
    {
        $allowed = ['newest', 'oldest', 'price_asc', 'price_desc', 'name_asc', 'name_desc'];
        return in_array($sort, $allowed, true) ? $sort : 'newest';
    }

    private function extractImagePath(array $payload): ?string
    {
        if (!empty($payload['fields']['image']) && is_string($payload['fields']['image'])) {
            return trim($payload['fields']['image']);
        }

        if (!empty($payload['files']['image']) && is_array($payload['files']['image']) && ($payload['files']['image']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $file = $payload['files']['image'];
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $targetFile = $uploadDir . basename($file['name']);
            if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                return $targetFile;
            }
        }

        return null;
    }

    private function requireAdmin(): array
    {
        return (new JwtMiddleware())->requireAdmin();
    }

    private function getRequestData($overrideMethod = null): array
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

    private function json($data, $code = 200)
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

