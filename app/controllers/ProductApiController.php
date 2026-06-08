<?php

require_once('app/config/database.php');
require_once('app/models/ProductModel.php');
require_once('app/helpers/SessionHelper.php');

class ProductApiController
{
    private $db;
    private $productModel;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->productModel = new ProductModel($this->db);
        SessionHelper::start();
        SessionHelper::tryRememberLogin($this->db);
    }

    public function index()
    {
        $this->json($this->productModel->getProducts());
    }

    public function show($id)
    {
        $product = $this->productModel->getProductById((int)$id);
        if (!$product) {
            $this->json(['message' => 'Product not found'], 404);
        }

        $this->json($product);
    }

    public function store()
    {
        try {
            $payload = $this->getRequestData();
            $image = $this->uploadImageFromRequest($payload['files']['image'] ?? ($_FILES['image'] ?? null), '');
            if ($image === '' && !empty($payload['fields']['image'])) {
                $image = $payload['fields']['image'];
            }
            $result = $this->productModel->addProduct(
                $payload['fields']['name'] ?? '',
                $payload['fields']['description'] ?? '',
                $payload['fields']['price'] ?? '',
                $payload['fields']['category_id'] ?? null,
                $image
            );

            if (is_array($result)) {
                $this->json(['success' => false, 'errors' => $result], 422);
            }

            $this->productModel->saveSpecs((int)$result, $payload['fields']['specs'] ?? []);
            $product = $this->productModel->getProductById((int)$result);
            $this->json(['success' => true, 'message' => 'Product created', 'product' => $product], 201);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function update($id)
    {
        try {
            $currentProduct = $this->productModel->getProductById((int)$id);
            if (!$currentProduct) {
                $this->json(['success' => false, 'message' => 'Product not found'], 404);
            }

            $request = $this->getRequestData('PUT');
            $data = $request['fields'];
            $files = $request['files'];
            $image = $this->uploadImageFromRequest($files['image'] ?? null, $currentProduct->image ?? '');
            if (($files['image'] ?? null) === null && !empty($data['image'])) {
                $image = $data['image'];
            }

            $result = $this->productModel->updateProduct(
                (int)$id,
                $data['name'] ?? '',
                $data['description'] ?? '',
                $data['price'] ?? '',
                $data['category_id'] ?? null,
                $image
            );

            if (is_array($result)) {
                $this->json(['success' => false, 'errors' => $result], 422);
            }

            $this->productModel->saveSpecs((int)$id, $data['specs'] ?? []);
            $product = $this->productModel->getProductById((int)$id);
            $this->json(['success' => true, 'message' => 'Product updated', 'product' => $product]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function destroy($id)
    {
        $product = $this->productModel->getProductById((int)$id);
        if (!$product) {
            $this->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        $result = $this->productModel->deleteProduct((int)$id);
        if (is_array($result)) {
            $this->json(['success' => false, 'errors' => $result], 409);
        }

        $this->json(['success' => (bool)$result, 'message' => $result ? 'Product deleted' : 'Delete failed']);
    }

    public function methodNotAllowed()
    {
        $this->json(['message' => 'Method not allowed'], 405);
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
            return [
                'fields' => $_POST,
                'files' => $_FILES
            ];
        }

        if ($method !== 'POST' && stripos($contentType, 'multipart/form-data') !== false) {
            $raw = file_get_contents('php://input');
            return $this->parseMultipartPut($raw, $contentType);
        }

        if ($method === 'PUT') {
            $raw = file_get_contents('php://input');
            $fields = [];
            parse_str($raw, $fields);
            return ['fields' => $fields, 'files' => []];
        }

        $fields = [];
        parse_str(file_get_contents('php://input'), $fields);
        return ['fields' => $fields, 'files' => []];
    }

    private function parseMultipartPut($raw, $contentType)
    {
        if (!preg_match('/boundary=(.*)$/', $contentType, $matches)) {
            return ['fields' => [], 'files' => []];
        }

        $boundary = '--' . trim($matches[1], '"');
        $parts = array_slice(explode($boundary, $raw), 1, -1);
        $fields = [];
        $files = [];

        foreach ($parts as $part) {
            $part = ltrim($part, "\r\n");
            [$rawHeaders, $body] = array_pad(explode("\r\n\r\n", $part, 2), 2, '');
            $body = preg_replace("/\r\n$/", '', $body);

            if (!preg_match('/name="([^"]+)"/', $rawHeaders, $nameMatch)) {
                continue;
            }

            $name = $nameMatch[1];
            if (preg_match('/filename="([^"]*)"/', $rawHeaders, $fileMatch)) {
                if ($fileMatch[1] === '') {
                    continue;
                }

                $tmpName = tempnam(sys_get_temp_dir(), 'put_upload_');
                file_put_contents($tmpName, $body);
                $files[$name] = [
                    'name' => $fileMatch[1],
                    'type' => $this->getMultipartHeaderValue($rawHeaders, 'Content-Type') ?: 'application/octet-stream',
                    'tmp_name' => $tmpName,
                    'error' => UPLOAD_ERR_OK,
                    'size' => filesize($tmpName),
                    'from_put' => true
                ];
            } else {
                $this->assignNestedField($fields, $name, $body);
            }
        }

        return ['fields' => $fields, 'files' => $files];
    }

    private function assignNestedField(&$fields, $name, $value)
    {
        if (strpos($name, '[') === false) {
            $fields[$name] = $value;
            return;
        }

        preg_match_all('/([^\[\]]+)/', $name, $matches);
        $keys = $matches[1] ?? [];
        if (empty($keys)) {
            return;
        }

        $target =& $fields;
        foreach ($keys as $index => $key) {
            if ($index === count($keys) - 1) {
                $target[$key] = $value;
                return;
            }

            if (!isset($target[$key]) || !is_array($target[$key])) {
                $target[$key] = [];
            }
            $target =& $target[$key];
        }
    }

    private function getMultipartHeaderValue($headers, $name)
    {
        if (preg_match('/' . preg_quote($name, '/') . ':\s*([^\r\n]+)/i', $headers, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    private function uploadImageFromRequest($file, $existingImage = '')
    {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return $existingImage;
        }

        $targetDir = 'uploads/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif'], true)) {
            throw new Exception('Chi cho phep JPG, JPEG, PNG va GIF.');
        }

        if (($file['size'] ?? 0) > 10 * 1024 * 1024) {
            throw new Exception('Hinh anh co kich thuoc qua lon.');
        }

        if (getimagesize($file['tmp_name']) === false) {
            throw new Exception('File khong phai la hinh anh.');
        }

        $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
        $targetFile = $targetDir . $safeName . '_' . time() . '.' . $extension;
        $moved = !empty($file['from_put'])
            ? rename($file['tmp_name'], $targetFile)
            : move_uploaded_file($file['tmp_name'], $targetFile);

        if (!$moved) {
            throw new Exception('Co loi xay ra khi tai len hinh anh.');
        }

        return $targetFile;
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
