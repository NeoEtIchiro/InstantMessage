<?php
// filepath: /c:/xampp/htdocs/InstantMessage/app/controllers/ConversationController.php
session_start();
require_once __DIR__ . '/../models/Conversation.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../database/ConnexionDB.php';

class ConversationController {
    public static function getGlobalConversation() {
        $connexionDB = new ConnexionDB();
        $conn = $connexionDB->getConnection();
        // Recherche d'une conversation de type 'global'
        $stmt = $conn->prepare("SELECT * FROM conversations WHERE type = 'global' LIMIT 1");
        $stmt->execute();
        $global = $stmt->fetch(PDO::FETCH_OBJ);
        
        return $global;
    }

    // Retourne la liste des conversations de l'utilisateur avec le login de l'autre participant pour une conversation directe
    public static function getConversationsForUser($userId) {
        $connexionDB = new ConnexionDB();
        $conn = $connexionDB->getConnection();
        $conversations = Conversation::getConversationsForUser($conn, $userId);
        $result = [];

        $global = self::getGlobalConversation();
        if ($global) {
            $result[] = [
                'conversation_id' => $global->id,
                'type'            => 'global',
                'other_login'     => 'Chat with everyone',
                'other_username'  => 'Global'
            ];
        }

        foreach ($conversations as $conversation) {
            // Récupérer l'autre participant
            $stmt = $conn->prepare("SELECT u.id, u.login 
                FROM conversation_users cu
                JOIN users u ON cu.user_id = u.id 
                WHERE cu.conversation_id = :convId AND u.id != :userId");
            $stmt->bindParam(':convId', $conversation->id, PDO::PARAM_INT);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $other = $stmt->fetch(PDO::FETCH_ASSOC);

            $other_username = $other ? User::getUsernameByLogin($conn, $other['login']) : null;

            $result[] = [
                'conversation_id' => $conversation->id,
                'type' => $conversation->type,
                'other_login' => $other ? $other['login'] : 'Groupe',
                'other_username' => $other_username
            ];
        }
        return $result;
    }

    // Récupère ou crée une conversation directe entre l'utilisateur connecté et un autre utilisateur
    public static function getOrCreateDirectConversation($currentUserId, $otherUserId) {
        $connexionDB = new ConnexionDB();
        $conn = $connexionDB->getConnection();
        
        $query = "SELECT c.id FROM conversations c 
                  JOIN conversation_users cu1 ON c.id = cu1.conversation_id 
                  JOIN conversation_users cu2 ON c.id = cu2.conversation_id 
                  WHERE c.type = 'direct' AND cu1.user_id = :user1 AND cu2.user_id = :user2";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user1', $currentUserId, PDO::PARAM_INT);
        $stmt->bindParam(':user2', $otherUserId, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return $row['id'];
        }
        // Crée la conversation s'il n'existe pas
        $conversation = Conversation::createDirectConversation($conn, $currentUserId, $otherUserId);
        return $conversation->id;
    }

    public static function getOrCreateGroupConversation($currentUserId){
        $connexionDB = new ConnexionDB();
        $conn = $connexionDB->getConnection();

        $query = "SELECT c.id FROM conversations c 
                  JOIN conversation_users cu1 ON c.id = cu1.conversation_id 
                  WHERE c.type = 'group' AND cu1.user_id = :user1";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user1', $currentUserId, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return $row['id'];
        }
        // Crée la conversation s'il n'existe pas
        $conversation = Conversation::createGroupConversation($conn, $currentUserId, "Nouveau groupe");
        return $conversation->id;
    }
}
?>