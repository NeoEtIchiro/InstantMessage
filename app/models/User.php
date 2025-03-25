<?php
// Classe représentant un utilisateur
class User {
    public $id;           // Identifiant de l'utilisateur
    public $login;        // Adresse email de connexion
    public $username;     // Nom d'utilisateur
    public $password;     // Mot de passe haché

    // Constructeur de la classe User
    public function __construct($id = null, $login = '', $password = '') {
        $this->id = $id;
        $this->login = $login;
        $this->password = $password;
        $this->username = 'user'; // Valeur par défaut pour username
    }

    // Récupère un utilisateur par son ID depuis la base de données
    public static function getUserById(PDO $conn, $id) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return new User($row['id'], $row['login'], $row['password']);
        }
        return null;
    }

    // Récupère un utilisateur par son email depuis la base de données
    public static function getUserByEmail(PDO $conn, $email) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE login = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return new User($row['id'], $row['login'], $row['password']);
        }
        return null;
    }

    // Recherche des utilisateurs par une requête et éventuellement exclut un utilisateur via son ID
    public static function searchUsers(PDO $conn, $query, $excludeId = null) {
        $searchQuery = "%" . $query . "%";
        if ($excludeId) {
            $stmt = $conn->prepare("SELECT id, login, username FROM users WHERE (username LIKE :query OR login LIKE :query) AND id <> :excludeId");
            $stmt->bindParam(':query', $searchQuery, PDO::PARAM_STR);
            $stmt->bindParam(':excludeId', $excludeId, PDO::PARAM_INT);
        } else {
            $stmt = $conn->prepare("SELECT id, login, username FROM users WHERE username LIKE :query OR login LIKE :query");
            $stmt->bindParam(':query', $searchQuery, PDO::PARAM_STR);
        }
        $stmt->execute();
        // Utilisation de FETCH_PROPS_LATE pour appeler le constructeur après l'affectation des propriétés
        return $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'User');
    }

    // Récupère le nom d'utilisateur (username) à partir du login dans la base de données
    public static function getUsernameByLogin(PDO $conn, $login) {
        $stmt = $conn->prepare("SELECT username FROM users WHERE login = :login");
        $stmt->bindParam(':login', $login);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['username'] : null;
    }
}