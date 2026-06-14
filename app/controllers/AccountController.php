<?php
require_once 'app/config/database.php';
require_once 'app/models/AccountModel.php';
require_once 'app/helpers/SessionHelper.php';
require_once 'app/helpers/MailHelper.php';

class AccountController
{
    private $accountModel;
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->accountModel = new AccountModel($this->db);
        SessionHelper::start();
        SessionHelper::tryRememberLogin($this->db);
    }

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
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            if (strlen($username) < 3) $errors[] = 'Tên đăng nhập phải có ít nhất 3 ký tự.';
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) $errors[] = 'Tên đăng nhập chỉ gồm chữ, số và dấu gach dưới.';
            if (!$fullname) $errors[] = 'Vui lòng nhập họ tên.';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email không hợp lệ.';
            if (strlen($password) < 6) $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự.';
            if ($password !== $confirm) $errors[] = 'Mật khẩu xác nhận không khớp.';
            if ($this->accountModel->usernameExists($username)) $errors[] = 'Tên đăng nhập đã tồn tại.';
            if ($this->accountModel->emailExists($email)) $errors[] = 'Email đã được sử dụng.';

            if (empty($errors)) {
                if ($this->accountModel->register($username, $fullname, $password, $email)) {
                    $user = $this->accountModel->findByUsername($username);
                    $verifyToken = bin2hex(random_bytes(32));
                    $this->accountModel->saveEmailVerifyToken($user->id, hash('sha256', $verifyToken));
                    
                    MailHelper::send(
                        $email, 
                        'Xác nhận tài khoản ShopTech',
                        'Chào mừng ' . $fullname . '!',
                        'Cảm ơn bạn đã đăng ký tài khoản tại ShopTech. Vui lòng nhấn vào nút bên dưới để xác thực email và bắt đầu mua sắm.',
                        '/Account/verifyEmail?token=' . $verifyToken,
                        'Xác thực tài khoản'
                    );

                    SessionHelper::setFlash('success', 'Đăng ký thành công! Một email xác nhận đã được gửi đến ' . $email);
                    header('Location: /Account/login');
                    return;
                } else {
                    $errors[] = 'Có lỗi xảy ra khi tạo tài khoản.';
                }
            }
        }

        include 'app/views/account/register.php';
    }

    public function login()
    {
        if (SessionHelper::isLoggedIn()) {
            header('Location: /Product');
            return;
        }

        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $login = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $remember = !empty($_POST['remember_me']);

            if (!$login || !$password) {
                $errors[] = 'Vui lòng nhập đầy đủ thông tin.';
            } else {
                $user = $this->accountModel->findByLogin($login);
                if (!$user || !password_verify($password, $user->password)) {
                    $errors[] = 'Tên đăng nhập hoặc mật khẩu không đúng.';
                } elseif (($user->status ?? 'active') !== 'active') {
                    $errors[] = 'Tài khoản đang bị khóa. Vui lòng liên hệ quản trị viên.';
                } else {
                    SessionHelper::login($user);

                    if ($remember) {
                        $rawToken = bin2hex(random_bytes(32));
                        $tokenHash = hash('sha256', $rawToken);
                        $this->accountModel->saveRememberToken($user->id, $tokenHash, 30);
                        setcookie('remember_token', $rawToken, time() + (30 * 24 * 3600), '/', '', false, true);
                    }

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

    public function logout()
    {
        if (SessionHelper::isLoggedIn()) {
            $this->accountModel->clearRememberToken(SessionHelper::getUserId());
        }
        SessionHelper::logout();
        header('Location: /Account/login');
    }

    public function forgotPassword()
    {
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Email không hợp lệ.';
            } else {
                $user = $this->accountModel->findByEmail($email);
                if ($user) {
                    $rawToken = bin2hex(random_bytes(32));
                    $this->accountModel->saveResetToken($user->id, hash('sha256', $rawToken));
                    
                    MailHelper::send(
                        $email,
                        'Đặt lại mật khẩu ShopTech',
                        'Yêu cầu đặt lại mật khẩu',
                        'Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn. Nếu không phải bạn, hãy bỏ qua email này.',
                        '/Account/resetPassword?token=' . $rawToken,
                        'Đặt lại mật khẩu'
                    );

                    SessionHelper::setFlash('success', 'Hướng dẫn đặt lại mật khẩu đã được gửi đến email của bạn. Vui lòng kiểm tra hộp thư (nhớ kiểm tra cả mục Spam nhé).');
                    SessionHelper::setFlash('reset_link', '/Account/resetPassword?token=' . $rawToken);
                } else {
                    SessionHelper::setFlash('success', 'Nếu email ' . htmlspecialchars($email) . ' tồn tại trong hệ thống, chúng tôi sẽ gửi link đặt lại mật khẩu đến đó.');
                }
                header('Location: /Account/forgotPassword');
                return;
            }
        }

        include 'app/views/account/forgot_password.php';
    }

    public function resetPassword()
    {
        $errors = [];
        $token = trim($_GET['token'] ?? ($_POST['token'] ?? ''));
        if (!preg_match('/^[a-f0-9]{64}$/i', $token)) {
            $token = '';
        }
        $tokenHash = $token !== '' ? hash('sha256', $token) : '';
        $user = $tokenHash ? $this->accountModel->findByResetToken($tokenHash) : null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            if (!$user) {
                $errors[] = 'Link đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.';
            }
            if (strlen($password) < 6) $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự.';
            if ($password !== $confirm) $errors[] = 'Mật khẩu xác nhận không khớp.';

            if (empty($errors)) {
                $this->accountModel->updatePassword($user->id, $password);
                SessionHelper::setFlash('success', 'Đặt lại mật khẩu thành công. Vui lòng đăng nhập.');
                header('Location: /Account/login');
                return;
            }
        }

        include 'app/views/account/reset_password.php';
    }

    public function verifyEmail()
    {
        $token = $_GET['token'] ?? '';
        if (!$token) {
            SessionHelper::setFlash('error', 'Thiếu token xác thực email.');
            header('Location: /Account/login');
            return;
        }

        $user = $this->accountModel->findByEmailVerifyToken(hash('sha256', $token));
        if (!$user) {
            SessionHelper::setFlash('error', 'Link xác thực không hợp lệ, đã hết hạn hoặc đã được sử dụng.');
            header('Location: /Account/login');
            return;
        }

        $this->accountModel->markEmailVerified($user->id);
        SessionHelper::setFlash('success', 'Xác thực email thành công! Bây giờ bạn đã có thể thực hiện mua sắm.');
        header('Location: /Account/login');
    }

    public function resendVerifyEmail()
    {
        SessionHelper::requireLogin();

        $userId = SessionHelper::getUserId();
        $user = $this->accountModel->findById($userId);
        if (!$user) {
            SessionHelper::setFlash('error', 'Không tìm thấy tài khoản.');
            header('Location: /Account/profile?tab=info');
            return;
        }

        if (!empty($user->email_verified_at)) {
            SessionHelper::setFlash('success', 'Email của bạn đã được xác thực trước đó.');
            header('Location: /Account/profile?tab=info');
            return;
        }

        $verifyToken = bin2hex(random_bytes(32));
        $this->accountModel->saveEmailVerifyToken($userId, hash('sha256', $verifyToken));
        SessionHelper::setFlash('verify_link', '/Account/verifyEmail?token=' . $verifyToken);
        header('Location: /Account/profile?tab=info');
    }

    public function profile()
    {
        SessionHelper::requireLogin();
        $user = $this->accountModel->findById(SessionHelper::getUserId());
        $errors = [];
        $tab = $_GET['tab'] ?? 'info';
        include 'app/views/account/profile.php';
    }

    public function updateProfile()
    {
        SessionHelper::requireLogin();

        $id = SessionHelper::getUserId();
        $fullname = trim($_POST['fullname'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $errors = [];

        if (!$fullname) $errors[] = 'Vui long nhap ho ten.';
        if (strlen($username) < 3) $errors[] = 'Ten dang nhap phai co it nhat 3 ky tu.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email khong hop le.';
        if ($this->accountModel->usernameExists($username, $id)) $errors[] = 'Ten dang nhap da duoc dung boi tai khoan khac.';
        if ($this->accountModel->emailExists($email, $id)) $errors[] = 'Email da duoc dung boi tai khoan khac.';

        if (empty($errors)) {
            $old = $this->accountModel->findById($id);
            $this->accountModel->updateProfile($id, $fullname, $username, $email);
            $_SESSION['fullname'] = $fullname;
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;

            if (strcasecmp((string)$old->email, $email) !== 0) {
                $verifyToken = bin2hex(random_bytes(32));
                $this->accountModel->saveEmailVerifyToken($id, hash('sha256', $verifyToken));
                $this->db->prepare("UPDATE account SET email_verified_at = NULL WHERE id = ?")->execute([$id]);
                
                MailHelper::send(
                    $email,
                    'Xác thực email mới - ShopTech',
                    'Cập nhật email thành công!',
                    'Bạn vừa thay đổi địa chỉ email trên hệ thống ShopTech. Vui lòng nhấn nút bên dưới để xác thực địa chỉ email mới này.',
                    '/Account/verifyEmail?token=' . $verifyToken,
                    'Xác thực email ngay'
                );

                SessionHelper::setFlash('success', 'Cập nhật thành công. Một email xác nhận đã được gửi đến ' . $email);
                SessionHelper::setFlash('verify_link', '/Account/verifyEmail?token=' . $verifyToken);
            } else {
                SessionHelper::setFlash('success', 'Cập nhật thông tin thành công!');
            }

            header('Location: /Account/profile?tab=info');
            return;
        }

        $user = $this->accountModel->findById($id);
        $tab = 'info';
        include 'app/views/account/profile.php';
    }

    public function changePassword()
    {
        SessionHelper::requireLogin();

        $id = SessionHelper::getUserId();
        $old = $_POST['old_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $errors = [];
        $user = $this->accountModel->findById($id);

        if (!password_verify($old, $user->password)) $errors[] = 'Mat khau hien tai khong dung.';
        if (strlen($new) < 6) $errors[] = 'Mat khau moi phai co it nhat 6 ky tu.';
        if ($new !== $confirm) $errors[] = 'Mat khau xac nhan khong khop.';

        if (empty($errors)) {
            $this->accountModel->updatePassword($id, $new);
            SessionHelper::setFlash('success', 'Doi mat khau thanh cong!');
            header('Location: /Account/profile?tab=password');
            return;
        }

        $tab = 'password';
        include 'app/views/account/profile.php';
    }

    public function updateAvatar()
    {
        SessionHelper::requireLogin();
        $id = SessionHelper::getUserId();

        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK || empty($_FILES['avatar']['tmp_name'])) {
            SessionHelper::setFlash('error', 'Vui long chon anh hop le.');
            header('Location: /Account/profile');
            return;
        }

        $file = $_FILES['avatar'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $maxSize = 3 * 1024 * 1024;

        if (!in_array($ext, $allowed, true)) {
            SessionHelper::setFlash('error', 'Chi chap nhan JPG, PNG, GIF, WEBP.');
            header('Location: /Account/profile');
            return;
        }
        if ($file['size'] > $maxSize) {
            SessionHelper::setFlash('error', 'Anh khong duoc vuot qua 3MB.');
            header('Location: /Account/profile');
            return;
        }
        if (!getimagesize($file['tmp_name'])) {
            SessionHelper::setFlash('error', 'File khong phai anh hop le.');
            header('Location: /Account/profile');
            return;
        }

        $uploadDir = 'uploads/avatars/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $user = $this->accountModel->findById($id);
        if (!empty($user->avatar) && file_exists($user->avatar)) unlink($user->avatar);

        $filename = 'avatar_' . $id . '_' . time() . '.' . $ext;
        $destPath = $uploadDir . $filename;
        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            SessionHelper::setFlash('error', 'Loi khi tai anh len.');
            header('Location: /Account/profile');
            return;
        }

        $this->accountModel->updateAvatar($id, $destPath);
        SessionHelper::updateAvatar($destPath);

        SessionHelper::setFlash('success', 'Cap nhat anh dai dien thanh cong!');
        header('Location: /Account/profile');
    }

    public function users()
    {
        SessionHelper::requireAdmin();
        $users = $this->accountModel->getAllUsers();
        include 'app/views/account/users.php';
    }

    public function updateUserRole()
    {
        SessionHelper::requireAdmin();
        $id = (int)($_POST['id'] ?? 0);
        $role = $_POST['role'] ?? 'user';
        if (!$id || !in_array($role, ['admin', 'user'], true)) {
            SessionHelper::setFlash('error', 'Du lieu khong hop le.');
            header('Location: /Account/users');
            return;
        }

        $this->accountModel->updateRole($id, $role);
        SessionHelper::setFlash('success', 'Cap nhat quyen thanh cong.');
        header('Location: /Account/users');
    }

    public function toggleUserStatus()
    {
        SessionHelper::requireAdmin();
        $id = (int)($_POST['id'] ?? 0);
        if (!$id || $id === (int)SessionHelper::getUserId()) {
            SessionHelper::setFlash('error', 'Khong the khoa tai khoan hien tai.');
            header('Location: /Account/users');
            return;
        }

        $user = $this->accountModel->findById($id);
        if (!$user) {
            SessionHelper::setFlash('error', 'Khong tim thay tai khoan.');
            header('Location: /Account/users');
            return;
        }

        $next = ($user->status ?? 'active') === 'active' ? 'locked' : 'active';
        $this->accountModel->updateStatus($id, $next);
        if ($next === 'locked') $this->accountModel->clearRememberToken($id);

        SessionHelper::setFlash('success', $next === 'locked' ? 'Da khoa tai khoan.' : 'Da mo khoa tai khoan.');
        header('Location: /Account/users');
        return;
    }

    public function deleteUser($id)
    {
        SessionHelper::requireAdmin();
        if ($id == SessionHelper::getUserId()) {
            SessionHelper::setFlash('error', 'Không thể xóa tài khoản của chính mình.');
            header('Location: /Account/users');
            return;
        }

        $result = $this->accountModel->deleteUser($id);
        if ($result) {
            SessionHelper::setFlash('success', 'Xóa người dùng thành công.');
        } else {
            SessionHelper::setFlash('error', 'Có lỗi xảy ra khi xóa người dùng.');
        }
        header('Location: /Account/users');
    }

    public function editUser($id)
    {
        SessionHelper::requireAdmin();
        $user = $this->accountModel->findById($id);
        if (!$user) {
            SessionHelper::setFlash('error', 'Không tìm thấy người dùng.');
            header('Location: /Account/users');
            return;
        }
        include 'app/views/account/edit_user.php';
    }

    public function saveUserEdit()
    {
        SessionHelper::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            $fullname = trim($_POST['fullname'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $role = $_POST['role'] ?? 'user';
            $status = $_POST['status'] ?? 'active';

            if ($id) {
                // We use updateProfile for basic info and updateRole/updateStatus for admin fields
                $this->accountModel->updateProfile($id, $fullname, $username, $email);
                $this->accountModel->updateRole($id, $role);
                $this->accountModel->updateStatus($id, $status);
                
                SessionHelper::setFlash('success', 'Cập nhật tài khoản thành công.');
            }
        }
        header('Location: /Account/users');
    }
}
