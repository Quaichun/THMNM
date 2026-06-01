<?php
class AccountModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function findByUsername($username)
    {
        $stmt = $this->db->prepare("SELECT * FROM account WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function findByLogin($login)
    {
        $stmt = $this->db->prepare("SELECT * FROM account WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$login, $login]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function findByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM account WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM account WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function findByRememberToken($tokenHash)
    {
        $stmt = $this->db->prepare("SELECT * FROM account WHERE remember_token = ? AND remember_expires_at > NOW() LIMIT 1");
        $stmt->execute([$tokenHash]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function register($username, $fullname, $password, $email)
    {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("INSERT INTO account (username, fullname, password, email) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$username, $fullname, $hash, $email]);
    }

    public function updateProfile($id, $fullname, $username, $email)
    {
        $stmt = $this->db->prepare("UPDATE account SET fullname = ?, username = ?, email = ? WHERE id = ?");
        return $stmt->execute([$fullname, $username, $email, $id]);
    }

    public function updatePassword($id, $newPassword)
    {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("UPDATE account SET password = ?, reset_token = NULL, reset_expires_at = NULL WHERE id = ?");
        return $stmt->execute([$hash, $id]);
    }

    public function usernameExists($username, $excludeId = null)
    {
        if ($excludeId) {
            $stmt = $this->db->prepare("SELECT id FROM account WHERE username = ? AND id != ? LIMIT 1");
            $stmt->execute([$username, $excludeId]);
        } else {
            $stmt = $this->db->prepare("SELECT id FROM account WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
        }
        return $stmt->fetch() !== false;
    }

    public function emailExists($email, $excludeId = null)
    {
        if ($excludeId) {
            $stmt = $this->db->prepare("SELECT id FROM account WHERE email = ? AND id != ? LIMIT 1");
            $stmt->execute([$email, $excludeId]);
        } else {
            $stmt = $this->db->prepare("SELECT id FROM account WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
        }
        return $stmt->fetch() !== false;
    }

    public function saveRememberToken($id, $tokenHash, $days = 30)
    {
        $stmt = $this->db->prepare("UPDATE account SET remember_token = ?, remember_expires_at = DATE_ADD(NOW(), INTERVAL ? DAY) WHERE id = ?");
        return $stmt->execute([$tokenHash, (int)$days, $id]);
    }

    public function clearRememberToken($id)
    {
        $stmt = $this->db->prepare("UPDATE account SET remember_token = NULL, remember_expires_at = NULL WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function saveResetToken($id, $tokenHash, $minutes = 30)
    {
        $stmt = $this->db->prepare("UPDATE account SET reset_token = ?, reset_expires_at = DATE_ADD(NOW(), INTERVAL ? MINUTE) WHERE id = ?");
        return $stmt->execute([$tokenHash, (int)$minutes, $id]);
    }

    public function findByResetToken($tokenHash)
    {
        $stmt = $this->db->prepare("SELECT * FROM account WHERE reset_token = ? AND reset_expires_at > NOW() LIMIT 1");
        $stmt->execute([$tokenHash]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function saveEmailVerifyToken($id, $tokenHash)
    {
        $stmt = $this->db->prepare("UPDATE account SET email_verify_token = ? WHERE id = ?");
        return $stmt->execute([$tokenHash, $id]);
    }

    public function findByEmailVerifyToken($tokenHash)
    {
        $stmt = $this->db->prepare("SELECT * FROM account WHERE email_verify_token = ? LIMIT 1");
        $stmt->execute([$tokenHash]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function markEmailVerified($id)
    {
        $stmt = $this->db->prepare("UPDATE account SET email_verified_at = NOW(), email_verify_token = NULL WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getAllUsers()
    {
        $stmt = $this->db->query("SELECT id, username, fullname, email, avatar, role, status, email_verified_at, created_at FROM account ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function updateRole($id, $role)
    {
        $stmt = $this->db->prepare("UPDATE account SET role = ? WHERE id = ?");
        return $stmt->execute([$role, $id]);
    }

    public function updateStatus($id, $status)
    {
        $stmt = $this->db->prepare("UPDATE account SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    public function updateAvatar($id, $avatarPath)
    {
        $stmt = $this->db->prepare("UPDATE account SET avatar = ? WHERE id = ?");
        return $stmt->execute([$avatarPath, $id]);
    }
}
