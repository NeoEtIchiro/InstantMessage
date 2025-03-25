<?php
// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclusion des dépendances nécessaires
require_once(__DIR__ . '/../models/Message.php');
require_once(__DIR__ . '/../database/ConnexionDB.php');

class MessageController {
    // Retourne les messages d'une conversation
    public static function getMessages($conversationId) {
        // Création d'une connexion à la base de données
        $connexionDB = new ConnexionDB();
        $conn = $connexionDB->getConnection();
        
        // Récupération des messages via le modèle Message
        return Message::getMessagesByConversation($conn, $conversationId);
    }

    // Envoie (sauvegarde) un message
    public static function sendMessage($conversationId, $senderId, $content) {
        // Création d'une connexion à la base de données
        $connexionDB = new ConnexionDB();
        $conn = $connexionDB->getConnection();
        
        // Création d'une instance de Message
        $message = new Message(null, $conversationId, $senderId, $content);
        
        // Sauvegarde du message et vérification de la réussite de l'opération
        if ($message->save($conn)) {
            return $message;
        }
        return null;
    }
}
?>