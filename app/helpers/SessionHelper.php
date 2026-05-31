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
        $_SESSION['avatar']   = $user->avatar ?? null;
    }

    public static function updateAvatar($path)
    {
        self::start();
        $_SESSION['avatar'] = $path;
    }

    public static function logout()
    {
        self::start();
        session_unset();
        session_destroy();
    }

    /* ── Yêu cầu đăng nhập ── */
    public static function requireLogin()
    {
        self::start();
        if (!self::isLoggedIn()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            self::setFlash('error', 'Vui lòng đăng nhập để tiếp tục.');
            header('Location: /Account/login');
            exit;
        }
    }

    /* ── Yêu cầu quyền Admin ── */
    public static function requireAdmin()
    {
        self::start();
        if (!self::isLoggedIn()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            self::setFlash('error', 'Vui lòng đăng nhập để tiếp tục.');
            header('Location: /Account/login');
            exit;
        }
        if (!self::isAdmin()) {
            self::setFlash('error', 'Bạn không có quyền thực hiện thao tác này.');
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