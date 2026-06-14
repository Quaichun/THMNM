<?php

require_once 'app/config/database.php';
require_once 'app/models/AccountModel.php';
require_once 'app/config/jwt.php';
require_once 'app/middlewares/JwtMiddleware.php';
require_once 'app/helpers/SessionHelper.php';
require_once 'app/helpers/MailHelper.php';

class AccountApiController
{
    private $db;
    private $accountModel;
    private $jwtSecret;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->accountModel = new AccountModel($this->db);
        $this->jwtSecret = getenv('JWT_SECRET') ?: 'CHANGE_ME_SUPER_SECRET';
    }

    public function register()
    {
        $payload = $this->getRequestData();
        $fields = $payload['fields'];

        $username = trim((string)($fields['username'] ?? ''));
        $fullname = trim((string)($fields['fullname'] ?? ''));
        $email = trim((string)($fields['email'] ?? ''));
        $password = (string)($fields['password'] ?? '');

        $errors = [];
        if (strlen($username) < 3) $errors['username'] = 'username>=3';
        if ($username && !preg_match('/^[a-zA-Z0-9_]+$/', $username)) $errors['username'] = 'invalid username';
        if ($fullname === '') $errors['fullname'] = 'required';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'invalid email';
        if (strlen($password) < 6) $errors['password'] = 'password>=6';

        if ($this->accountModel->usernameExists($username)) $errors['username'] = 'username exists';
        if ($this->accountModel->emailExists($email)) $errors['email'] = 'email exists';

        if ($errors) return $this->json(['success' => false, 'errors' => $errors], 422);

        $ok = $this->accountModel->register($username, $fullname, $password, $email);
        if (!$ok) return $this->json(['success' => false, 'message' => 'register failed'], 400);

        $user = $this->accountModel->findByUsername($username);
        SessionHelper::login($user);
        $token = $this->createToken($user);

        return $this->json([
            'success' => true,
            'token' => $token,
            'user' => $this->userPayload($user)
        ], 201);
    }

    public function login()
    {
        $payload = $this->getRequestData();
        $fields = $payload['fields'];

        $login = trim((string)($fields['username'] ?? $fields['login'] ?? ''));
        $password = (string)($fields['password'] ?? '');

        if ($login === '' || $password === '') {
            return $this->json(['success' => false, 'message' => 'username/password required'], 422);
        }

        $user = $this->accountModel->findByLogin($login);
        if (!$user || !password_verify($password, $user->password)) {
            return $this->json(['success' => false, 'message' => 'invalid credentials'], 401);
        }
        if (($user->status ?? 'active') !== 'active') {
            return $this->json(['success' => false, 'message' => 'account locked'], 403);
        }

        SessionHelper::login($user);
        $token = $this->createToken($user);
        return $this->json([
            'success' => true,
            'token' => $token,
            'user' => $this->userPayload($user)
        ]);
    }

    public function profile()
    {
        $mw = new JwtMiddleware();
        $auth = $mw->requireAuth();

        $user = $this->accountModel->findById((int)$auth['user_id']);
        if (!$user) return $this->json(['success' => false, 'message' => 'User not found'], 404);

        $data = [
            'id' => (int)$user->id,
            'username' => $user->username,
            'fullname' => $user->fullname,
            'email' => $user->email,
            'avatar' => $user->avatar ?? null,
            'role' => $user->role ?? 'user',
            'status' => $user->status ?? 'active'
        ];

        return $this->json(['success' => true, 'profile' => $data]);
    }

    public function updateProfile()
    {
        $mw = new JwtMiddleware();
        $auth = $mw->requireAuth();

        $payload = $this->getRequestData();
        $fields = $payload['fields'];

        $id = (int)$auth['user_id'];
        $fullname = trim((string)($fields['fullname'] ?? ''));
        $username = trim((string)($fields['username'] ?? ''));
        $email = trim((string)($fields['email'] ?? ''));

        $errors = [];
        if ($fullname === '') $errors['fullname'] = 'required';
        if (strlen($username) < 3) $errors['username'] = 'username>=3';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'invalid email';
        if ($this->accountModel->usernameExists($username, $id)) $errors['username'] = 'username exists';
        if ($this->accountModel->emailExists($email, $id)) $errors['email'] = 'email exists';

        if ($errors) return $this->json(['success' => false, 'errors' => $errors], 422);

        $old = $this->accountModel->findById($id);
        $this->accountModel->updateProfile($id, $fullname, $username, $email);
        $user = $this->accountModel->findById($id);

        $emailChanged = strcasecmp((string)($old->email ?? ''), $email) !== 0;
        if ($emailChanged) {
            $verifyToken = bin2hex(random_bytes(32));
            $this->accountModel->saveEmailVerifyToken($id, hash('sha256', $verifyToken));
            
            // Sync status
            $db = (new Database())->getConnection();
            $db->prepare("UPDATE account SET email_verified_at = NULL WHERE id = ?")->execute([$id]);

            MailHelper::send(
                $email,
                'Xác thực email mới - ShopTech',
                'Cập nhật email thành công!',
                'Bạn vừa thay đổi địa chỉ email trên hệ thống ShopTech. Vui lòng nhấn nút bên dưới để xác thực địa chỉ email mới này.',
                '/Account/verifyEmail?token=' . $verifyToken,
                'Xác thực email ngay'
            );
            
            if (SessionHelper::isLoggedIn() && SessionHelper::getUserId() == $id) {
                $_SESSION['email'] = $email;
                $_SESSION['fullname'] = $fullname;
                $_SESSION['username'] = $username;
                SessionHelper::setFlash('success', 'Email đã được cập nhật. Vui lòng xác thực email mới.');
                SessionHelper::setFlash('verify_link', '/Account/verifyEmail?token=' . $verifyToken);
            }
        } elseif (SessionHelper::isLoggedIn() && SessionHelper::getUserId() == $id) {
            $_SESSION['fullname'] = $fullname;
            $_SESSION['username'] = $username;
        }

        return $this->json(['success' => true, 'profile' => $user, 'email_changed' => $emailChanged]);
    }

    public function changePassword()
    {
        $mw = new JwtMiddleware();
        $auth = $mw->requireAuth();

        $payload = $this->getRequestData();
        $fields = $payload['fields'];

        $id = (int)$auth['user_id'];
        $old = (string)($fields['old_password'] ?? '');
        $new = (string)($fields['new_password'] ?? '');

        if ($old === '' || $new === '') return $this->json(['success' => false, 'message' => 'required'], 422);
        if (strlen($new) < 6) return $this->json(['success' => false, 'message' => 'new_password>=6'], 422);

        $user = $this->accountModel->findById($id);
        if (!$user || !password_verify($old, $user->password)) {
            return $this->json(['success' => false, 'message' => 'invalid old password'], 403);
        }

        $this->accountModel->updatePassword($id, $new);
        return $this->json(['success' => true]);
    }

    public function forgotPassword()
    {
        // mock: accepts email and returns ok (no email sending required for API spec)
        $payload = $this->getRequestData();
        $email = trim((string)($payload['fields']['email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json(['success' => false, 'message' => 'invalid email'], 422);
        }
        return $this->json(['success' => true, 'message' => 'mock_otp_sent']);
    }

    public function logout()
    {
        if (SessionHelper::isLoggedIn()) {
            $this->accountModel->clearRememberToken((int)SessionHelper::getUserId());
        }
        SessionHelper::logout();
        return $this->json(['success' => true, 'message' => 'logged_out']);
    }

    private function createToken($user): string
    {
        $now = time();
        $exp = $now + (24 * 3600); // 24 hours
        $payload = [
            'iss' => 'ShopTech',
            'sub' => (int)$user->id,
            'role' => $user->role ?? 'user',
            'iat' => $now,
            'exp' => $exp
        ];
        return Jwt::sign($payload, $this->jwtSecret);
    }

    private function userPayload($user): array
    {
        return [
            'id' => (int)$user->id,
            'username' => $user->username,
            'fullname' => $user->fullname,
            'email' => $user->email,
            'avatar' => $user->avatar ?? null,
            'role' => $user->role ?? 'user',
            'status' => $user->status ?? 'active'
        ];
    }

    private function getRequestData(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($contentType, 'application/json') !== false) {
            $raw = file_get_contents('php://input');
            $decoded = json_decode($raw, true);
            return ['fields' => is_array($decoded) ? $decoded : []];
        }
        return ['fields' => $_POST];
    }

    private function json($data, $code = 200)
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
