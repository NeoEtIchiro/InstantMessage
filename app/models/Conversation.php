<?php

class Conversation {
    public $id;
    public $type;      

    public function __construct($id = null, $type = 'direct') {
        $this->id = $id;
        $this->type = $type;
    }

    // Récupérer toutes les conversations d'un utilisateur
    public static function getConversationsForUser(PDO $conn, $userId) {
        $query = "SELECT c.id, c.type 
                  FROM conversations c
                  JOIN conversation_users cu ON c.id = cu.conversation_id
                  WHERE cu.user_id = :userId";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $conversations = [];
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $conversations[] = new Conversation($row['id'], $row['type']);
        }
        return $conversations;
    }

    // Créer une conversation directe entre deux utilisateurs
    public static function createDirectConversation(PDO $conn, $userId1, $userId2) {
        $stmt = $conn->prepare("INSERT INTO conversations (type) VALUES ('direct')");
        $stmt->execute();
        $conversationId = $conn->lastInsertId();

        // Ajouter les deux participants
        $stmt1 = $conn->prepare("INSERT INTO conversation_users (conversation_id, user_id) VALUES (:conversationId, :userId)");
        $stmt1->bindParam(':conversationId', $conversationId, PDO::PARAM_INT);
        $stmt1->bindParam(':userId', $userId1, PDO::PARAM_INT);
        $stmt1->execute();

        $stmt2 = $conn->prepare("INSERT INTO conversation_users (conversation_id, user_id) VALUES (:conversationId, :userId)");
        $stmt2->bindParam(':conversationId', $conversationId, PDO::PARAM_INT);
        $stmt2->bindParam(':userId', $userId2, PDO::PARAM_INT);
        $stmt2->execute();

        return new Conversation($conversationId, 'direct');
    }
}