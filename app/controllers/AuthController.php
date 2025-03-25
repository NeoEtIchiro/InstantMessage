<?php
// Ce contrôleur gère l'authentification : connexion, inscription et récupération du nom d'utilisateur.

if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Démarrage de la session si elle n'est pas déjà lancée
}

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../database/ConnexionDB.php';

class AuthController {
    // Méthode pour traiter la connexion de l'utilisateur
    public static function login($email, $password) {
        // Création d'une connexion à la base de données
        $connexionDB = new ConnexionDB();
        $conn = $connexionDB->getConnection();

        // Récupère l'utilisateur correspondant à l'email
        $user = User::getUserByEmail($conn, $email);
        if (!$user) {
            // Retourne une erreur si aucun utilisateur n'est trouvé
            return ['success' => false, 'message' => "Aucun compte n'existe avec ce mail"];
        }
        // Vérifie que le mot de passe correspond bien au hash stocké
        if (!password_verify($password, $user->password)) {
            // Retourne une erreur si le mot de passe est invalide
            return ['success' => false, 'message' => "Mot de passe invalide"];
        }
        // Stocke les informations de l'utilisateur dans la session
        $_SESSION['user'] = ['id' => $user->id, 'login' => $user->login];
        // Retourne une réponse de succès avec les informations de l'utilisateur
        return ['success' => true, 'user' => $_SESSION['user']];
    }

    // Méthode pour traiter l'inscription d'un nouvel utilisateur
    public static function register($email, $password) {
        // Création d'une connexion à la base de données
        $connexionDB = new ConnexionDB();
        $conn = $connexionDB->getConnection();

        // Vérifie si un utilisateur avec cet email existe déjà
        if (User::getUserByEmail($conn, $email)) {
            // Retourne une erreur si l'utilisateur existe déjà
            return ['success' => false, 'message' => 'L\'utilisateur existe déjà'];
        }
        // Hash du mot de passe pour sécuriser son stockage
        $hash = password_hash($password, PASSWORD_DEFAULT);
        // Génère un nom d'utilisateur aléatoire
        $randomUser = 'user' . rand(10000, 99999);
        // Prépare la requête d'insertion dans la base de données
        $stmt = $conn->prepare("INSERT INTO users (login, password, username) VALUES (:email, :password, :username)");
        $stmt->bindParam(':email', $email);       // Lie le paramètre email
        $stmt->bindParam(':password', $hash);       // Lie le paramètre du mot de passe hashé
        $stmt->bindParam(':username', $randomUser); // Lie le paramètre du nom d'utilisateur aléatoire
        
        // Exécute la requête d'insertion
        if ($stmt->execute()) {
            // Récupère l'utilisateur nouvellement créé
            $user = User::getUserByEmail($conn, $email);
            // Stocke les informations de l'utilisateur dans la session
            $_SESSION['user'] = ['id' => $user->id, 'login' => $user->login];
            // Retourne une réponse de succès avec les informations de l'utilisateur
            return ['success' => true, 'user' => $_SESSION['user']];
        }
        // Retourne une erreur en cas d'échec lors de la création du compte
        return ['success' => false, 'message' => 'Erreur lors de la création du compte'];
    }

    // Méthode pour récupérer le nom de l'utilisateur à partir de son login
    public static function getUsernameByLogin($login) {
        // Création d'une connexion à la base de données
        $connexionDB = new ConnexionDB();
        $conn = $connexionDB->getConnection();
        // Prépare la requête pour sélectionner le nom d'utilisateur
        $stmt = $conn->prepare("SELECT username FROM users WHERE login = :login");
        $stmt->bindParam(':login', $login); // Lie le paramètre login
        $stmt->execute();
        // Récupère le résultat de la requête en format associative array
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        // Retourne le nom d'utilisateur s'il existe, sinon null
        return $result ? $result['username'] : null;
    }
}
?>