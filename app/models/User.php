<?php
class User {
    public $id;
    public $login;
    public $password; // Mot de passe hashé

    public function __construct($id = null, $login = '', $password = '') {
        $this->id = $id;
        $this->login = $login;
        $this->password = $password;
    }

    public static function getUserById(PDO $conn, $id) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return new User($row['id'], $row['login'], $row['password']);
        }
        return null;
    }

    public static function getUserByEmail(PDO $conn, $email) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE login = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return new User($row['id'], $row['login'], $row['password']);
        }
        return null;
    }

    public static function searchUsers(PDO $conn, $query) {
        $searchQuery = '%' . $query . '%';
        $stmt = $conn->prepare("SELECT id, login FROM users WHERE login LIKE :query");
        $stmt->bindParam(':query', $searchQuery, PDO::PARAM_STR);
        $stmt->execute();
        $users = [];
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = new User($row['id'], $row['login']);
        }
        return $users;
    }
}