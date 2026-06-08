<?php
require_once 'app/config/database.php';
require_once 'app/models/CategoryModel.php';
require_once 'app/helpers/SessionHelper.php';

class CategoryController
{
    private $categoryModel;
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->categoryModel = new CategoryModel($this->db);
        SessionHelper::start();
        SessionHelper::tryRememberLogin($this->db);
    }

    public function index()
    {
        $categories = $this->categoryModel->getCategories();
        include 'app/views/category/list.php';
    }

    public function list()
    {
        $categories = $this->categoryModel->getCategories();
        include 'app/views/category/list.php';
    }

    public function add()
    {
        SessionHelper::requireAdmin();
        include 'app/views/category/add.php';
    }

    public function save()
    {
        SessionHelper::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');

            if (!$name) {
                $errors = ['Ten danh muc khong duoc de trong.'];
                include 'app/views/category/add.php';
                return;
            }

            $this->categoryModel->addCategory($name, $description);
            SessionHelper::setFlash('success', 'Them danh muc thanh cong!');
            header('Location: /Category/list');
        }
    }

    public function edit($id)
    {
        SessionHelper::requireAdmin();
        $category = $this->categoryModel->getCategoryById($id);
        if (!$category) {
            SessionHelper::setFlash('error', 'Khong tim thay danh muc.');
            header('Location: /Category/list');
            return;
        }
        include 'app/views/category/edit.php';
    }

    public function update()
    {
        SessionHelper::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');

            if (!$name) {
                $errors = ['Ten danh muc khong duoc de trong.'];
                $category = $this->categoryModel->getCategoryById($id);
                include 'app/views/category/edit.php';
                return;
            }

            $this->categoryModel->updateCategory($id, $name, $description);
            SessionHelper::setFlash('success', 'Cap nhat danh muc thanh cong!');
            header('Location: /Category/list');
        }
    }

    public function delete($id)
    {
        SessionHelper::requireAdmin();
        $this->categoryModel->deleteCategory($id);
        SessionHelper::setFlash('success', 'Da xoa danh muc.');
        header('Location: /Category/list');
    }
}
