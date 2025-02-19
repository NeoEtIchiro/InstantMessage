<?php
// filepath: /c:/xampp/htdocs/InstantMessage/app/controllers/AuthController.php
session_start();
require_once '../models/User.php';
require_once '../database/ConnexionDB.php';

class AuthController {
    // Traitement de la connexion
    public static function login($email, $password) {
        $connexionDB = new ConnexionDB();
        $conn = $connexionDB->getConnection();
        $user = User::getUserByEmail($conn, $email);
        if ($user && password_verify($password, $user->password)) {
            $_SESSION['user'] = ['id' => $user->id, 'login' => $user->login];
            return ['success' => true, 'user' => $_SESSION['user']];
        }
        return ['success' => false, 'message' => 'Login invalide'];
    }

    // Traitement de l'inscription
    public static function register($email, $password) {
        $connexionDB = new ConnexionDB();
        $conn = $connexionDB->getConnection();
        if (User::getUserByEmail($conn, $email)) {
            return ['success' => false, 'message' => 'L\'utilisateur existe déjà'];
        }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (login, password) VALUES (:email, :password)");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hash);
        if ($stmt->execute()) {
            $user = User::getUserByEmail($conn, $email);
            $_SESSION['user'] = ['id' => $user->id, 'login' => $user->login];
            return ['success' => true, 'user' => $_SESSION['user']];
        }
        return ['success' => false, 'message' => 'Erreur lors de la création du compte'];
    }
}
?>