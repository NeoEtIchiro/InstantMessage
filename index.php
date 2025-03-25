<?php
// Démarrer la session pour gérer les variables de session
session_start();

// Vérifier si l'utilisateur est connecté
if (isset($_SESSION['user'])) {
    // Rediriger vers la page des messages si l'utilisateur est connecté
    header('Location: views/messages.php');
    exit;
} else {
    // Rediriger vers la page de connexion/inscription si l'utilisateur n'est pas connecté
    header('Location: views/login_register.html');
    exit;
}
?>
