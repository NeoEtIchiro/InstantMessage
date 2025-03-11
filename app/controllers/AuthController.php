<?php
// filepath: /c:/xampp/htdocs/InstantMessage/app/controllers/AuthController.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../database/ConnexionDB.php';

class AuthController {
    // Traitement de la connexion
    public static function login($email, $password) {
        $connexionDB = new ConnexionDB();
        $conn = $connexionDB->getConnection();
        $user = User::getUserByEmail($conn, $email);
        if (!$user) {
            return ['success' => false, 'message' => "Aucun compte n'existe avec ce mail"];
        }
        if (!password_verify($password, $user->password)) {
            return ['success' => false, 'message' => "Mot de passe invalide"];
        }
        $_SESSION['user'] = ['id' => $user->id, 'login' => $user->login];
        return ['success' => true, 'user' => $_SESSION['user']];
    }

    // Traitement de l'inscription
    public static function register($email, $password) {
        $connexionDB = new ConnexionDB();
        $conn = $connexionDB->getConnection();
        if (User::getUserByEmail($conn, $email)) {
            return ['success' => false, 'message' => 'L\'utilisateur existe déjà'];
        }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $randomUser = 'user' . rand(10000, 99999);
        $stmt = $conn->prepare("INSERT INTO users (login, password, username) VALUES (:email, :password, :username)");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hash);
        $stmt->bindParam(':username', $randomUser);
        if ($stmt->execute()) {
            $user = User::getUserByEmail($conn, $email);
            $_SESSION['user'] = ['id' => $user->id, 'login' => $user->login];
            return ['success' => true, 'user' => $_SESSION['user']];
        }
        return ['success' => false, 'message' => 'Erreur lors de la création du compte'];
    }

    public static function getUsernameByLogin($login) {
        $connexionDB = new ConnexionDB();
        $conn = $connexionDB->getConnection();
        $stmt = $conn->prepare("SELECT username FROM users WHERE login = :login");
        $stmt->bindParam(':login', $login);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['username'] : null;
    }
}
?>