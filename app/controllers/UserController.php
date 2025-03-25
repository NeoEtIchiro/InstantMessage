<?php
// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclusion du modèle User et de la classe de connexion à la base de données
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../database/ConnexionDB.php';

class UserController {
    // Récupère toutes les informations de l'utilisateur connecté depuis la base de données
    public static function getCurrentUser() {
        // Vérifier si l'utilisateur est connecté et que son identifiant est défini dans la session
        if (isset($_SESSION['user']) && isset($_SESSION['user']['id'])) {
            // Création d'une nouvelle connexion à la base
            $connexionDB = new ConnexionDB();
            $conn = $connexionDB->getConnection();
            // Préparation de la requête pour sélectionner l'utilisateur par son identifiant
            $stmt = $conn->prepare("SELECT id, login, username FROM users WHERE id = :id");
            $stmt->bindParam(':id', $_SESSION['user']['id'], PDO::PARAM_INT);
            $stmt->execute();
            // Récupération du résultat sous forme de tableau associatif
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            // Retourne l'utilisateur s'il existe, sinon null
            return $user ? $user : null;
        }
        // Retourne null si aucune session utilisateur n'est trouvée
        return null;
    }

    // Recherche des utilisateurs par login
    public static function searchUsers($query) {
        // Création d'une nouvelle connexion à la base
        $connexionDB = new ConnexionDB();
        $conn = $connexionDB->getConnection();
        // Récupération de l'identifiant de l'utilisateur connecté afin de l'exclure de la recherche si besoin
        $currentUserId = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null;
        // Appel de la méthode searchUsers du modèle User
        $users = User::searchUsers($conn, $query, $currentUserId);
        $result = [];
        // Parcours des résultats et construction du tableau de réponses
        foreach ($users as $user) {
            $result[] = ['id' => $user->id, 'login' => $user->login, 'username' => $user->username];
        }
        // Retourne la liste des utilisateurs trouvés
        return $result;
    }

    // Met à jour le username de l'utilisateur dans la base de données
    public static function updateUsername($userId, $newUsername) {
        // Création d'une nouvelle connexion à la base
        $connexionDB = new ConnexionDB();
        $conn = $connexionDB->getConnection();
        // Préparation de la requête de mise à jour du username pour l'utilisateur identifié par son ID
        $stmt = $conn->prepare("UPDATE users SET username = :username WHERE id = :id");
        $stmt->bindParam(":username", $newUsername, PDO::PARAM_STR);
        $stmt->bindParam(":id", $userId, PDO::PARAM_INT);
        // Exécution de la requête et vérification du succès
        if ($stmt->execute()) {
            // Retourne le succès de la mise à jour
            return ['success' => true];
        }
        // En cas d'échec, retourne un message d'erreur
        return ['success' => false, 'message' => "Erreur lors de la mise à jour du username"];
    }
}
?>