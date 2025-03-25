<?php

// Classe représentant une conversation
class Conversation {
    public $id;
    public $type;      

    // Constructeur de la classe Conversation
    public function __construct($id = null, $type = 'direct') {
        $this->id = $id;
        $this->type = $type;
    }

    // Récupère toutes les conversations d'un utilisateur
    public static function getConversationsForUser(PDO $conn, $userId) {
        // Requête pour sélectionner les conversations liées à l'utilisateur
        $query = "SELECT c.id, c.type 
                  FROM conversations c
                  JOIN conversation_users cu ON c.id = cu.conversation_id
                  WHERE cu.user_id = :userId
                  AND c.type IN ('direct')";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();

        // Création d'un tableau d'instances Conversation
        $conversations = [];
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $conversations[] = new Conversation($row['id'], $row['type']);
        }
        return $conversations;
    }

    // Crée une conversation directe entre deux utilisateurs
    public static function createDirectConversation(PDO $conn, $userId1, $userId2) {
        // Insertion d'une nouvelle conversation de type 'direct'
        $stmt = $conn->prepare("INSERT INTO conversations (type) VALUES ('direct')");
        $stmt->execute();
        $conversationId = $conn->lastInsertId();

        // Ajoute le premier participant à la conversation
        $stmt1 = $conn->prepare("INSERT INTO conversation_users (conversation_id, user_id) VALUES (:conversationId, :userId)");
        $stmt1->bindParam(':conversationId', $conversationId, PDO::PARAM_INT);
        $stmt1->bindParam(':userId', $userId1, PDO::PARAM_INT);
        $stmt1->execute();

        // Ajoute le deuxième participant à la conversation
        $stmt2 = $conn->prepare("INSERT INTO conversation_users (conversation_id, user_id) VALUES (:conversationId, :userId)");
        $stmt2->bindParam(':conversationId', $conversationId, PDO::PARAM_INT);
        $stmt2->bindParam(':userId', $userId2, PDO::PARAM_INT);
        $stmt2->execute();

        // Retourne l'objet Conversation créé
        return new Conversation($conversationId, 'direct');
    }

    // Crée une conversation de groupe
    public static function createGroupConversation(PDO $conn, $userId, $conversationName) {
        // Insertion d'une nouvelle conversation de type 'group'
        $stmt = $conn->prepare("INSERT INTO conversations (type) VALUES ('group')");
        $stmt->execute();
        $conversationId = $conn->lastInsertId();

        // Ajoute le créateur comme participant
        $stmt1 = $conn->prepare("INSERT INTO conversation_users (conversation_id, user_id) VALUES (:conversationId, :userId)");
        $stmt1->bindParam(':conversationId', $conversationId, PDO::PARAM_INT);
        $stmt1->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt1->execute();

        // Ajoute le nom de la conversation (attention : cette requête semble insérer dans une table 'conversations' déjà utilisée)
        $stmt2 = $conn->prepare("INSERT INTO conversations (conversation_id, name) VALUES (:conversationId, :name)");
        $stmt2->bindParam(':conversationId', $conversationId, PDO::PARAM_INT);
        $stmt2->bindParam(':name', $conversationName, PDO::PARAM_STR);
        $stmt2->execute();

        // Retourne l'objet Conversation créé
        return new Conversation($conversationId, 'group');
    }
}