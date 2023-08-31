<?php
/**
 * @class: User
 * @description: Single User Model
 */

class User
{
    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getUserById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function register($username, $email, $password)
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
        $stmt->bindValue(':username', $username);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':password', $hashedPassword);
        $stmt->execute();

        // You might want to handle success/error scenarios appropriately here.

        return $this->db->lastInsertId();
        

    }

    public function authenticate($username, $password)
    {
        $stmt = $this->db->prepare("SELECT id, password FROM users WHERE username = :username");
        $stmt->bindValue(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            return false; // Authentication failed
        }

        $_SESSION['user_id'] = $user['id'];
        return true; // Authentication succeeded
    }
}
