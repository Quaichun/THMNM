<?php

require_once 'app/config/jwt.php';
require_once 'app/helpers/SessionHelper.php';
require_once 'app/models/AccountModel.php';
require_once 'app/config/database.php';

class JwtMiddleware
{
    private function getBearerToken(): ?string
    {
        $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!$auth && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $auth = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }

        if (!$auth) return null;

        if (preg_match('/Bearer\s+(.*)$/i', $auth, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    public function requireAuth(): array
    {
        $token = $this->getBearerToken();
        if (!$token) {
            // Fallback to Session for internal web calls
            if (SessionHelper::isLoggedIn()) {
                return [
                    'user_id' => (int)SessionHelper::getUserId(),
                    'role' => SessionHelper::isAdmin() ? 'admin' : 'user'
                ];
            }
            $this->json(['success' => false, 'message' => 'Unauthorized: Token missing'], 401);
        }

        $secret = getenv('JWT_SECRET') ?: 'CHANGE_ME_SUPER_SECRET';
        $res = Jwt::verify($token, $secret);
        if (!$res['valid']) {
            // Even if token is invalid, if session exists, we can still trust it for web frontend
            if (SessionHelper::isLoggedIn()) {
                return [
                    'user_id' => (int)SessionHelper::getUserId(),
                    'role' => SessionHelper::isAdmin() ? 'admin' : 'user'
                ];
            }
            $msg = 'Unauthorized';
            if ($res['error'] === 'JWT_EXPIRED') $msg = 'Token expired';
            if ($res['error'] === 'JWT_SIGNATURE') $msg = 'Invalid signature';
            $this->json(['success' => false, 'message' => $msg, 'error' => $res['error']], 401);
        }

        $payload = $res['payload'];
        $userId = $payload['sub'] ?? null;
        $role = $payload['role'] ?? null;

        if (!$userId) {
            $this->json(['success' => false, 'message' => 'Unauthorized: Invalid payload'], 401);
        }

        return ['user_id' => (int)$userId, 'role' => $role];
    }

    public function requireAdmin(): array
    {
        $auth = $this->requireAuth();
        $role = $auth['role'] ?? null;
        if ($role !== 'admin') {
            $this->json(['success' => false, 'message' => 'Forbidden: Admin access required'], 403);
        }
        return $auth;
    }


    private function json($data, $code = 200)
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

