<?php
class SessionHelper
{
    public static function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
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

    public static function getUserId()
    {
        self::start();
        return $_SESSION['user_id'] ?? null;
    }

    public static function getUsername()
    {
        self::start();
        return $_SESSION['username'] ?? null;
    }

    public static function getFullname()
    {
        self::start();
        return $_SESSION['fullname'] ?? null;
    }

    public static function getRole()
    {
        self::start();
        return $_SESSION['role'] ?? null;
    }

    // Avatar: lấy từ session, tự đồng bộ từ DB nếu session chưa có
    public static function getAvatar()
    {
        self::start();
        return $_SESSION['avatar'] ?? null;
    }

    public static function login($user)
    {
        self::start();
        $_SESSION['user_id']  = $user->id;
        $_SESSION['username'] = $user->username;
        $_SESSION['fullname'] = $user->fullname;
        $_SESSION['role']     = $user->role;
        $_SESSION['avatar']   = $user->avatar ?? null; // lưu avatar khi login
    }

    public static function updateAvatar($path)
    {
        self::start();
        $_SESSION['avatar'] = $path; // cập nhật session ngay lập tức
    }

    public static function logout()
    {
        self::start();
        session_unset();
        session_destroy();
    }

    public static function requireLogin()
    {
        if (!self::isLoggedIn()) {
            header('Location: /Account/login');
            exit;
        }
    }

    public static function requireAdmin()
    {
        if (!self::isLoggedIn() || !self::isAdmin()) {
            header('Location: /Account/login');
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