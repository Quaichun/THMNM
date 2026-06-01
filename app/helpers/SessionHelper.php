<?php
class SessionHelper
{
    public static function start()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    public static function isLoggedIn()
    {
        self::start();
        return isset($_SESSION['user_id']);
    }

    public static function isAdmin()
    {
        self::start();
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    public static function getUserId() { self::start(); return $_SESSION['user_id'] ?? null; }
    public static function getUsername() { self::start(); return $_SESSION['username'] ?? null; }
    public static function getFullname() { self::start(); return $_SESSION['fullname'] ?? null; }
    public static function getRole() { self::start(); return $_SESSION['role'] ?? null; }
    public static function getAvatar() { self::start(); return $_SESSION['avatar'] ?? null; }
    public static function getEmail() { self::start(); return $_SESSION['email'] ?? null; }

    public static function login($user)
    {
        self::start();
        $_SESSION['user_id'] = $user->id;
        $_SESSION['username'] = $user->username;
        $_SESSION['fullname'] = $user->fullname;
        $_SESSION['role'] = $user->role;
        $_SESSION['avatar'] = $user->avatar ?? null;
        $_SESSION['email'] = $user->email ?? null;
    }

    public static function tryRememberLogin($db)
    {
        self::start();
        if (self::isLoggedIn() || empty($_COOKIE['remember_token'])) return;
        $tokenHash = hash('sha256', $_COOKIE['remember_token']);
        $stmt = $db->prepare("SELECT * FROM account WHERE remember_token = ? AND remember_expires_at > NOW() AND status = 'active' LIMIT 1");
        $stmt->execute([$tokenHash]);
        $user = $stmt->fetch(PDO::FETCH_OBJ);
        if ($user) {
            self::login($user);
        } else {
            setcookie('remember_token', '', time() - 3600, '/', '', false, true);
        }
    }

    public static function updateAvatar($path)
    {
        self::start();
        $_SESSION['avatar'] = $path;
    }

    public static function logout()
    {
        self::start();
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
        session_unset();
        session_destroy();
    }

    public static function requireLogin()
    {
        self::start();
        if (!self::isLoggedIn()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            self::setFlash('error', 'Vui long dang nhap de tiep tuc.');
            header('Location: /Account/login');
            exit;
        }
    }

    public static function requireAdmin()
    {
        self::start();
        if (!self::isLoggedIn()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            self::setFlash('error', 'Vui long dang nhap de tiep tuc.');
            header('Location: /Account/login');
            exit;
        }
        if (!self::isAdmin()) {
            self::setFlash('error', 'Ban khong co quyen thuc hien thao tac nay.');
            header('Location: /Product');
            exit;
        }
    }

    public static function setFlash($key, $message)
    {
        self::start();
        $_SESSION['flash'][$key] = $message;
    }

    public static function getFlash($key)
    {
        self::start();
        $msg = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
}
