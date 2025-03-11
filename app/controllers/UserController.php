<?php
// filepath: /c:/xampp/htdocs/InstantMessage/app/controllers/UserController.php
session_start();
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../database/ConnexionDB.php';

class UserController {
    // Retourne toutes les informations de l'utilisateur connecté depuis la BDD
    public static function getCurrentUser() {
        if (isset($_SESSION['user']) && isset($_SESSION['user']['id'])) {
            $connexionDB = new ConnexionDB();
            $conn = $connexionDB->getConnection();
            // Sélection de toutes les colonnes souhaitées (ajustez selon vos colonnes)
            $stmt = $conn->prepare("SELECT id, login, username FROM users WHERE id = :id");
            $stmt->bindParam(':id', $_SESSION['user']['id'], PDO::PARAM_INT);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user ? $user : null;
        }
        return null;
    }

    // Recherche des utilisateurs par login
    public static function searchUsers($query) {
        $connexionDB = new ConnexionDB();
        $conn = $connexionDB->getConnection();
        $currentUserId = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null;
        $users = User::searchUsers($conn, $query, $currentUserId);
        $result = [];
        foreach ($users as $user) {
            $result[] = ['id' => $user->id, 'login' => $user->login, 'username' => $user->username];
        }
        return $result;
    }

    public static function updateUsername($userId, $newUsername) {
        $connexionDB = new ConnexionDB();
        $conn = $connexionDB->getConnection();
        $stmt = $conn->prepare("UPDATE users SET username = :username WHERE id = :id");
        $stmt->bindParam(":username", $newUsername, PDO::PARAM_STR);
        $stmt->bindParam(":id", $userId, PDO::PARAM_INT);
        if ($stmt->execute()) {
            // Mettre à jour la session si besoin
            return ['success' => true];
        }
        return ['success' => false, 'message' => "Erreur lors de la mise à jour du username"];
    }
}
?>