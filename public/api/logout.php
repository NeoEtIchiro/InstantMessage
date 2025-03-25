<?php
// Démarrer la session pour pouvoir la détruire
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Supprimer toutes les variables de session
session_unset();

// Détruire la session
session_destroy();

// Rediriger vers la page de connexion
header("Location: ../../views/login_register.html");
exit();

?>