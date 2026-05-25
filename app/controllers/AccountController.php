<?php
require_once 'app/config/database.php';
require_once 'app/models/AccountModel.php';
require_once 'app/helpers/SessionHelper.php';

class AccountController
{
    private $accountModel;
    private $db;

    public function __construct()
    {
        $this->db           = (new Database())->getConnection();
        $this->accountModel = new AccountModel($this->db);
        SessionHelper::start();
    }

    /* ════════════════════════════════════════════════════
       ĐĂNG KÝ
    ════════════════════════════════════════════════════ */
    public function register()
    {
        if (SessionHelper::isLoggedIn()) {
            header('Location: /Product');
            return;
        }

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $fullname = trim($_POST['fullname'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm  = $_POST['confirm_password'] ?? '';

            if (strlen($username) < 3)
                $errors[] = 'Tên đăng nhập phải có ít nhất 3 ký tự.';
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $username))
                $errors[] = 'Tên đăng nhập chỉ gồm chữ, số và dấu gạch dưới.';
            if (!$fullname)
                $errors[] = 'Vui lòng nhập họ tên.';
            if (strlen($password) < 6)
                $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự.';
            if ($password !== $confirm)
                $errors[] = 'Mật khẩu xác nhận không khớp.';
            if ($this->accountModel->usernameExists($username))
                $errors[] = 'Tên đăng nhập đã tồn tại.';

            if (empty($errors)) {
                $this->accountModel->register($username, $fullname, $password);
                SessionHelper::setFlash('success', 'Đăng ký thành công! Hãy đăng nhập.');
                header('Location: /Account/login');
                return;
            }
        }

        include 'app/views/account/register.php';
    }

    /* ════════════════════════════════════════════════════
       ĐĂNG NHẬP
    ════════════════════════════════════════════════════ */
    public function login()
    {
        if (SessionHelper::isLoggedIn()) {
            header('Location: /Product');
            return;
        }

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if (!$username || !$password) {
                $errors[] = 'Vui lòng nhập đầy đủ thông tin.';
            } else {
                $user = $this->accountModel->findByUsername($username);
                if (!$user || !password_verify($password, $user->password)) {
                    $errors[] = 'Tên đăng nhập hoặc mật khẩu không đúng.';
                } else {
                    SessionHelper::login($user);
                    SessionHelper::setFlash('success', 'Chào mừng ' . $user->fullname . '!');
                    $redirect = $_SESSION['redirect_after_login'] ?? '/Product';
                    unset($_SESSION['redirect_after_login']);
                    header('Location: ' . $redirect);
                    return;
                }
            }
        }

        include 'app/views/account/login.php';
    }

    /* ════════════════════════════════════════════════════
       ĐĂNG XUẤT
    ════════════════════════════════════════════════════ */
    public function logout()
    {
        SessionHelper::logout();
        header('Location: /Account/login');
    }

    /* ════════════════════════════════════════════════════
       HỒ SƠ
    ════════════════════════════════════════════════════ */
    public function profile()
    {
        SessionHelper::requireLogin();

        $user   = $this->accountModel->findById(SessionHelper::getUserId());
        $errors = [];
        $tab    = $_GET['tab'] ?? 'info';

        include 'app/views/account/profile.php';
    }

    /* ════════════════════════════════════════════════════
       CẬP NHẬT THÔNG TIN
    ════════════════════════════════════════════════════ */
    public function updateProfile()
    {
        SessionHelper::requireLogin();

        $id       = SessionHelper::getUserId();
        $fullname = trim($_POST['fullname'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $errors   = [];

        if (!$fullname)
            $errors[] = 'Vui lòng nhập họ tên.';
        if (strlen($username) < 3)
            $errors[] = 'Tên đăng nhập phải có ít nhất 3 ký tự.';
        if ($this->accountModel->usernameExists($username, $id))
            $errors[] = 'Tên đăng nhập đã được dùng bởi tài khoản khác.';

        if (empty($errors)) {
            $this->accountModel->updateProfile($id, $fullname, $username);
            $_SESSION['fullname'] = $fullname;
            $_SESSION['username'] = $username;
            SessionHelper::setFlash('success', 'Cập nhật thông tin thành công!');
            header('Location: /Account/profile?tab=info');
            return;
        }

        $user = $this->accountModel->findById($id);
        $tab  = 'info';
        include 'app/views/account/profile.php';
    }

    /* ════════════════════════════════════════════════════
       ĐỔI MẬT KHẨU
    ════════════════════════════════════════════════════ */
    public function changePassword()
    {
        SessionHelper::requireLogin();

        $id      = SessionHelper::getUserId();
        $old     = $_POST['old_password']     ?? '';
        $new     = $_POST['new_password']     ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $errors  = [];
        $user    = $this->accountModel->findById($id);

        if (!password_verify($old, $user->password))
            $errors[] = 'Mật khẩu hiện tại không đúng.';
        if (strlen($new) < 6)
            $errors[] = 'Mật khẩu mới phải có ít nhất 6 ký tự.';
        if ($new !== $confirm)
            $errors[] = 'Mật khẩu xác nhận không khớp.';

        if (empty($errors)) {
            $this->accountModel->updatePassword($id, $new);
            SessionHelper::setFlash('success', 'Đổi mật khẩu thành công!');
            header('Location: /Account/profile?tab=password');
            return;
        }

        $tab = 'password';
        include 'app/views/account/profile.php';
    }

    /* ════════════════════════════════════════════════════
       CẬP NHẬT ẢNH ĐẠI DIỆN
    ════════════════════════════════════════════════════ */
    public function updateAvatar()
{
    SessionHelper::requireLogin();

    $id = SessionHelper::getUserId();

    if (
        !isset($_FILES['avatar']) ||
        $_FILES['avatar']['error'] !== UPLOAD_ERR_OK ||
        empty($_FILES['avatar']['tmp_name'])
    ) {
        SessionHelper::setFlash('error', 'Vui lòng chọn ảnh hợp lệ.');
        header('Location: /Account/profile');
        return;
    }

    $file    = $_FILES['avatar'];
    $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $maxSize = 3 * 1024 * 1024;

    if (!in_array($ext, $allowed)) {
        SessionHelper::setFlash('error', 'Chỉ chấp nhận JPG, PNG, GIF, WEBP.');
        header('Location: /Account/profile');
        return;
    }

    if ($file['size'] > $maxSize) {
        SessionHelper::setFlash('error', 'Ảnh không được vượt quá 3MB.');
        header('Location: /Account/profile');
        return;
    }

    if (!getimagesize($file['tmp_name'])) {
        SessionHelper::setFlash('error', 'File không phải ảnh hợp lệ.');
        header('Location: /Account/profile');
        return;
    }

    $uploadDir = 'uploads/avatars/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Xóa ảnh cũ
    $user = $this->accountModel->findById($id);
    if (!empty($user->avatar) && file_exists($user->avatar)) {
        unlink($user->avatar);
    }

    $filename = 'avatar_' . $id . '_' . time() . '.' . $ext;
    $destPath = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        SessionHelper::setFlash('error', 'Lỗi khi tải ảnh lên.');
        header('Location: /Account/profile');
        return;
    }

    // Lưu DB
    $this->accountModel->updateAvatar($id, $destPath);

    // Đồng bộ session ngay lập tức — dùng helper chuyên dụng
    SessionHelper::updateAvatar($destPath);

    SessionHelper::setFlash('success', 'Cập nhật ảnh đại diện thành công!');
    header('Location: /Account/profile');
}
}