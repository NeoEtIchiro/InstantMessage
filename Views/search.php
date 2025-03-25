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
    <main class="flex-grow p-4">
        <!-- Placeholder pour la recherche par username ou login -->
        <input type="text" id="search-bar" placeholder="Rechercher par username ou login" class="w-full p-2 border mb-4">
        <ul id="user-list" class="list-disc pl-5"></ul>
    </main>

    <?php include __DIR__ . '/partials/footer.php'; ?>
    
    <script>
        // Exécution après le chargement complet du DOM
        $(document).ready(function() {

            // Fonction pour récupérer les utilisateurs en fonction de la requête
            function fetchUsers(query = '') {
                $.ajax({
                    url: "../public/api/user.php?action=searchUsers",
                    method: "GET",
                    data: { query: query },
                    dataType: "json",
                    success: function(response) {
                        $('#user-list').empty();
                        if (response.users && response.users.length > 0) {
                            // Parcours de chaque utilisateur et ajout dans la liste
                            response.users.forEach(function(user) {
                                var li = $('<li>', {
                                    'data-id': user.id,
                                    // Ajout de classes pour le style
                                    class: "flex items-center cursor-pointer hover:underline border-b border-gray-200 py-2"
                                });
                                var circle = $('<div>', {
                                    class: "h-12 w-12 rounded-full bg-gray-400 mr-2"
                                });
                                var text = 
                                    '<div class="flex flex-col">' +
                                        '<span class="text-lg font-bold">' + user.username + '</span>' +
                                        '<span>' + user.login + '</span>' +
                                    '</div>';
                                li.append(circle, text);
                                $('#user-list').append(li);
                            });
                        } else {
                            $('#user-list').append('<li>Aucun utilisateur trouvé.</li>');
                        }
                    },
                    error: function() {
                        $('#user-list').append('<li>Erreur lors de la récupération des utilisateurs.</li>');
                    }
                });
            }

            // Redirige vers messages.php en passant l'ID de l'utilisateur cliqué
            $('#user-list').on('click', 'li[data-id]', function() {
                var userId = $(this).data('id');
                console.log("Clique sur un utilisateur, id =", userId);
                window.location.href = "messages.php?userId=" + userId;
            });

            // Recherche en direct lors de la saisie dans la barre de recherche
            $('#search-bar').on('input', function() {
                const query = $(this).val();
                // Récupération des utilisateurs en fonction de la valeur saisie
                fetchUsers(query);
            });

            // Chargement initial de tous les utilisateurs
            fetchUsers();
        });
    </script>
</body>
</html>