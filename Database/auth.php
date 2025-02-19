<?php
session_start();
require_once 'Database/ConnexionDB.php';

$connexionDB = new ConnexionDB();
$conn = $connexionDB->getConnection();

function loginUser($conn, $email, $password) {
    $stmt = $conn->prepare("SELECT id, email, password FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        return true;
    }
    return false;
}

function registerUser($conn, $email, $password) {
    // Vérifier si un compte avec cet email existe déjà.
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        return false; // L'utilisateur existe déjà
    }
    // Hachage du mot de passe
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (:email, :password)");
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $hash);
    return $stmt->execute();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'login') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            if (loginUser($conn, $email, $password)) {
                header("Location: Views/messages.html");
                exit;
            } else {
                echo "Login invalide.";
            }
        } elseif ($_POST['action'] === 'register') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            if (registerUser($conn, $email, $password)) {
                echo "Compte créé avec succès. Vous pouvez maintenant vous connecter.";
            } else {
                echo "Erreur lors de la création du compte ou le compte existe déjà.";
            }
        }
    }
}
?>