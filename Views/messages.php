<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Instant Message</title>
    <!-- Importation de Tailwind CSS et jQuery -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="min-h-screen flex flex-col">
    <!-- Conteneur principal pour les conversations et les messages -->
    <div class="flex flex-grow" style="max-height: calc(100vh - 64px);">
        <!-- Liste des conversations à gauche -->
        <aside class="w-1/4 border-r overflow-auto">
            <h2 class="text-xl font-bold p-4 border-b">Conversations</h2>
            <ul id="conversation-list">
                <!-- Chargement en cours de la liste des conversations -->
                <li class="p-4 text-center text-gray-500">Chargement…</li>
            </ul>
        </aside>

        <!-- Zone de messages de la conversation sélectionnée -->
        <section class="flex-grow p-4">
            <div class="flex flex-col h-full">
                <!-- Container qui affiche les messages -->
                <div id="messages-container" class="w-full flex-grow overflow-auto">
                    <h1 class="text-2xl font-bold mb-4">Bienvenue sur Instant Message</h1>
                    <p class="max-w-[600px] flex justify-center">
                        Sélectionnez une conversation sur la gauche ou recherchez un utilisateur pour en commencer une nouvelle
                    </p>
                </div>
                <!-- Formulaire pour envoyer un message (initialement caché) -->
                <form id="message-form" class="w-full h-fit flex" style="display: none;">
                    <textarea id="message-text" placeholder="Envoyer un message" class="w-full resize-none p-2 min-h-11 h-11 border rounded-lg mr-2"></textarea>
                    <button type="submit">
                        <img class="h-6 w-6" src="/InstantMessage/public/assets/images/envoyer-le-message.png" alt="Envoyer">
                    </button>
                </form>
            </div>
        </section>
    </div>

    <!-- Inclusion du pied de page -->
    <?php include __DIR__ . '/partials/footer.php'; ?>

    <script>
        $(document).ready(function() {
            // Initialisation des variables de conversation et d'intervalle de rafraîchissement
            var currentConversationId = null;
            var refreshInterval = 500; // Intervalle de rafraîchissement des messages (en ms)
            var scrollThreshold = 50; // Seuil pour considérer que l'utilisateur est en bas

            // Charge les messages d'une conversation
            function loadConversation(conversationId) {
                currentConversationId = conversationId;
                $.ajax({
                    url: "../public/api/message.php?action=getMessages",
                    method: "GET",
                    data: { id: conversationId },
                    dataType: "html",
                    success: function(data) {
                        var $container = $("#messages-container");
                        // Vérifie si le conteneur est scrolled vers le bas
                        var isScrolledToBottom = ($container[0].scrollHeight - $container.scrollTop()) <= ($container.outerHeight() + scrollThreshold);
                        $container.html(data);
                        if(isScrolledToBottom) {
                            // Fait défiler vers le bas si nécessaire
                            $container.scrollTop($container[0].scrollHeight);
                        }
                        // Affiche le formulaire de message lorsque la conversation est sélectionnée
                        $("#message-form").show();
                    },
                    error: function() {
                        $("#messages-container").html("<p>Erreur lors du chargement des messages.</p>");
                    }
                });
            }

            // Actualisation des messages à intervalle régulier
            setInterval(function() {
                if (currentConversationId !== null) {
                    loadConversation(currentConversationId);
                }
            }, refreshInterval);

            // Rafraîchissement de la liste des conversations toutes les 5 secondes
            setInterval(loadConversations, 5000);

            // Charge la liste des conversations
            function loadConversations() {
                $.ajax({
                    url: "../public/api/conversation.php?action=getConversations",
                    method: "GET",
                    dataType: "json",
                    success: function(response) {
                        $("#conversation-list").empty();
                        if(response.conversations && response.conversations.length > 0) {
                            response.conversations.forEach(function(conv) {
                                // Création de l'élément de liste pour chaque conversation
                                var li = $('<li>', {
                                    'data-id': conv.conversation_id,
                                    class: "p-4 border-b cursor-pointer hover:bg-gray-100 flex items-center gap-2"
                                });
                                // Création de l'avatar
                                var circle = $('<div>', {
                                    class: "min-w-12 h-12 w-12 rounded-full bg-gray-400"
                                });
                                // Informations sur l'autre utilisateur
                                var text = 
                                '<div class="flex flex-col">' +
                                    '<span class="text-lg font-bold">' + conv.other_username + '</span>' +
                                    '<span>' + conv.other_login + '</span>' +
                                '</div>';
                                li.append(circle, text);
                                $("#conversation-list").append(li);
                            });
                        } else {
                            $("#conversation-list").append("<li class='p-4 text-center text-gray-500'>Aucune conversation trouvée.</li>");
                        }
                    },
                    error: function() {
                        $("#conversation-list").html("<li class='p-4 text-center text-gray-500'>Erreur lors du chargement des conversations.</li>");
                    }
                });
            }

            // Gère la sélection d'une conversation lors d'un clic sur un élément de la liste
            $("#conversation-list").on('click', 'li[data-id]', function() {
                var conversationId = $(this).data('id');
                loadConversation(conversationId);
            });

            // Création ou récupération d'une conversation à partir d'un paramètre URL
            let params = new URLSearchParams(window.location.search);
            if (params.has('userId') && currentConversationId === null) {
                let userId = params.get('userId');
                $.ajax({
                    url: "../public/api/conversation.php?action=getOrCreateConversation",
                    method: "GET",
                    data: { userId: userId },
                    dataType: "json",
                    success: function(response) {
                        if (response.conversationId) {
                            loadConversation(response.conversationId);
                        } else {
                            $("#messages-container").html("<p>Erreur lors de la création de la conversation.</p>");
                        }
                    },
                    error: function() {
                        $("#messages-container").html("<p>Erreur lors de la création de la conversation.</p>");
                    }
                });
            }

            // Chargement initial des conversations
            loadConversations();

            // Envoi d'un message via le formulaire
            $("form").on("submit", function(e) {
                e.preventDefault();
                var messageText = $("#message-text").val().trim();
                if (!currentConversationId) {
                    alert("Sélectionnez une conversation.");
                    return;
                }
                if (messageText === "") {
                    return;
                }
                $.ajax({
                    url: "../public/api/message.php?action=sendMessage",
                    method: "POST",
                    data: {
                        conversationId: currentConversationId,
                        content: messageText
                    },
                    dataType: "json",
                    success: function(response) {
                        if(response.success){
                            // Efface le champ de texte et recharge la conversation
                            $("#message-text").val("");
                            loadConversation(currentConversationId);
                        } else {
                            alert(response.message || "Erreur lors de l'envoi du message.");
                        }
                    },
                    error: function() {
                        alert("Erreur lors de l'envoi du message.");
                    }
                });
            });

            // Envoi du message en appuyant sur la touche "Enter" (saut de ligne avec Shift+Enter)
            $("#message-text").on("keydown", function(e) {
                if (e.key === "Enter" && !e.shiftKey) {
                    e.preventDefault();
                    $(this).closest("form").trigger("submit");
                }
            });
        });
    </script>
</body>
</html>