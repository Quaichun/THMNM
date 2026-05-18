<?php

require_once('app/config/database.php');
require_once('app/models/CategoryModel.php');

class CategoryController
{
    private $categoryModel;
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->categoryModel = new CategoryModel($this->db);
    }

    public function list()
    {
        $categories = $this->categoryModel->getCategories();

        include 'app/views/category/list.php';
    }

    public function add()
    {
        include 'app/views/category/add.php';
    }

    public function save()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $name = $_POST['name'];
            $description = $_POST['description'];

            $this->categoryModel->addCategory($name, $description);

            header('Location: /Category/list');
        }
    }

    public function edit($id)
    {
        $category = $this->categoryModel->getCategoryById($id);

        include 'app/views/category/edit.php';
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $id = $_POST['id'];
            $name = $_POST['name'];
            $description = $_POST['description'];

            $this->categoryModel->updateCategory(
                $id,
                $name,
                $description
            );

            header('Location: /Category/list');
        }
    }

    public function delete($id)
    {
        $this->categoryModel->deleteCategory($id);

        header('Location: /Category/list');
    }
}

?>