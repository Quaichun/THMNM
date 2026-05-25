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
        $stmt = $this->db->prepare(
            "SELECT * FROM account WHERE username = ? LIMIT 1"
        );
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM account WHERE id = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function register($username, $fullname, $password)
    {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare(
            "INSERT INTO account (username, fullname, password) VALUES (?, ?, ?)"
        );
        return $stmt->execute([$username, $fullname, $hash]);
    }

    public function updateProfile($id, $fullname, $username)
    {
        $stmt = $this->db->prepare(
            "UPDATE account SET fullname = ?, username = ? WHERE id = ?"
        );
        return $stmt->execute([$fullname, $username, $id]);
    }

    public function updatePassword($id, $newPassword)
    {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare(
            "UPDATE account SET password = ? WHERE id = ?"
        );
        return $stmt->execute([$hash, $id]);
    }

    public function usernameExists($username, $excludeId = null)
    {
        if ($excludeId) {
            $stmt = $this->db->prepare(
                "SELECT id FROM account WHERE username = ? AND id != ? LIMIT 1"
            );
            $stmt->execute([$username, $excludeId]);
        } else {
            $stmt = $this->db->prepare(
                "SELECT id FROM account WHERE username = ? LIMIT 1"
            );
            $stmt->execute([$username]);
        }
        return $stmt->fetch() !== false;
    }

    public function getAllUsers()
    {
        $stmt = $this->db->query(
            "SELECT id, username, fullname, role FROM account ORDER BY id DESC"
        );
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function updateAvatar($id, $avatarPath)
{
    $stmt = $this->db->prepare(
        "UPDATE account SET avatar = ? WHERE id = ?"
    );
    return $stmt->execute([$avatarPath, $id]);
}
}