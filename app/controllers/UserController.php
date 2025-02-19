<?php
// filepath: /c:/xampp/htdocs/InstantMessage/app/controllers/UserController.php
session_start();
require_once '../models/User.php';
require_once '../database/ConnexionDB.php';

class UserController {
    // Retourne l'utilisateur actuellement connecté
    public static function getCurrentUser() {
        return isset($_SESSION['user']) ? $_SESSION['user'] : null;
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