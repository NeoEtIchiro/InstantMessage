<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Instant Message</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="min-h-screen flex flex-col">
    <!-- Conteneur principal pour conversations et messages -->
    <div class="flex flex-grow" style="max-height: calc(100vh - 64px);">
        <!-- Liste des conversations à gauche -->
        <aside class="w-1/4 border-r overflow-auto">
            <h2 class="text-xl font-bold p-4 border-b">Conversations</h2>
            <ul id="conversation-list">
                <!-- La liste sera chargée dynamiquement -->
                <li class="p-4 text-center text-gray-500">Chargement…</li>
            </ul>
        </aside>

        <!-- Zone de messages pour la conversation sélectionnée -->
        <section class="flex-grow p-4">
            <div class="flex flex-col h-full">
                <div id="messages-container" class="w-full flex-grow overflow-auto">
                    <h1 class="text-2xl font-bold mb-4">Bienvenue sur Instant Message</h1>
                    <p class="max-w-[600px] flex justify-center">Sélectionnez une conversation sur la gauche ou recherchez un utilisateur pour en commencer une nouvelle</p>  
                </div>
                <form id="message-form" class="w-full h-fit flex" style="display: none;">
                    <textarea id="message-text" placeholder="Envoyer un message" class="w-full resize-none p-2 min-h-11 h-11 border rounded-lg mr-2"></textarea>
                    <button type="submit">
                        <img class="h-6 w-6" src="/InstantMessage/public/assets/images/envoyer-le-message.png" alt="Envoyer">
                    </button>
                </form>
            </div>
        </section>
    </div>

    <?php include __DIR__ . '/partials/footer.php'; ?>

    <script>
        $(document).ready(function() {
            var currentConversationId = null;
            var refreshInterval = 500; // 500ms pour éviter les appels trop fréquents
            var scrollThreshold = 50; // Seuil en pixels pour considérer que l'utilisateur est en bas

            // Fonction de chargement des messages pour une conversation donnée.
            function loadConversation(conversationId) {
                currentConversationId = conversationId;
                $.ajax({
                    url: "../public/api/message.php?action=getMessages",
                    method: "GET",
                    data: { id: conversationId },
                    dataType: "html",
                    success: function(data) {
                        var $container = $("#messages-container");
                        var isScrolledToBottom = ($container[0].scrollHeight - $container.scrollTop()) <= ($container.outerHeight() + scrollThreshold);
                        $container.html(data);
                        if(isScrolledToBottom) {
                            $container.scrollTop($container[0].scrollHeight);
                        }
                        // Affiche le formulaire dès qu'une conversation est sélectionnée
                        $("#message-form").show();
                    },
                    error: function() {
                        $("#messages-container").html("<p>Erreur lors du chargement des messages.</p>");
                    }
                });
            }

            // Actualise les messages à intervalles réguliers.
            setInterval(function() {
                if (currentConversationId !== null) {
                    loadConversation(currentConversationId);
                }
            }, refreshInterval);

            setInterval(loadConversations, 5000);

            // Charge la liste des conversations.
            function loadConversations() {
                $.ajax({
                    url: "../public/api/conversation.php?action=getConversations",
                    method: "GET",
                    dataType: "json",
                    success: function(response) {
                        $("#conversation-list").empty();
                        if(response.conversations && response.conversations.length > 0) {
                            response.conversations.forEach(function(conv) {
                                var li = $('<li>', {
                                    'data-id': conv.conversation_id,
                                    class: "p-4 border-b cursor-pointer hover:bg-gray-100 flex items-center gap-2"
                                });
                                var circle = $('<div>', {
                                    class: "min-w-8 h-8 w-8 rounded-full bg-gray-400"
                                });
                                var name = $('<span>', {
                                    text: conv.other_login
                                });
                                li.append(circle, name);
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

            // Lorsqu'une conversation est cliquée.
            $("#conversation-list").on('click', 'li[data-id]', function() {
                var conversationId = $(this).data('id');
                loadConversation(conversationId);
            });

            // Création ou récupération d'une conversation via un paramètre URL.
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

            loadConversations();

            // Envoi du message via le formulaire.
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

            // Envoi du message sur "Enter" (et saut de ligne avec Shift+Enter)
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