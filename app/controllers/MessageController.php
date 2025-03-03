<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../models/Message.php');
require_once(__DIR__ . '/../database/ConnexionDB.php');

class MessageController {
    // Retourne les messages d'une conversation
    public static function getMessages($conversationId) {
        $connexionDB = new ConnexionDB();
        $conn = $connexionDB->getConnection();
        return Message::getMessagesByConversation($conn, $conversationId);
    }

    // Envoie (sauvegarde) un message
    public static function sendMessage($conversationId, $senderId, $content) {
        $connexionDB = new ConnexionDB();
        $conn = $connexionDB->getConnection();
        $message = new Message(null, $conversationId, $senderId, $content);
        if ($message->save($conn)) {
            return $message;
        }
        return null;
    }
}
?>