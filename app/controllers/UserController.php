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
            $stmt = $conn->prepare("SELECT id, login FROM users WHERE id = :id");
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
        $users = User::searchUsers($conn, $query);
        $result = [];
        foreach ($users as $user) {
            $result[] = ['id' => $user->id, 'login' => $user->login];
        }
        return $result;
    }
}
?>