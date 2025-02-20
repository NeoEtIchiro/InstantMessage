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
        <input type="text" id="search-bar" placeholder="Rechercher par email" class="w-full p-2 border mb-4">
        <ul id="user-list" class="list-disc pl-5"></ul>
    </main>

    <?php include __DIR__ . '/partials/footer.php'; ?>
    
    <script>
        $(document).ready(function() {
            function fetchUsers(query = '') {
                $.ajax({
                    url: "../public/api/user.php?action=searchUsers",
                    method: "GET",
                    data: { query: query },
                    dataType: "json",
                    success: function(response) {
                        $('#user-list').empty();
                        if (response.users && response.users.length > 0) {
                            response.users.forEach(function(user) {
                                var li = $('<li>', {
                                    'data-id': user.id,
                                    // Added py-2 for vertical padding, border-b for 1px bottom border and border-gray-200 for light border color.
                                    class: "flex items-center cursor-pointer hover:underline border-b border-gray-200 py-2"
                                });
                                var circle = $('<div>', {
                                    class: "h-8 w-8 rounded-full bg-gray-400 mr-2"
                                });
                                li.append(circle, user.login);
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

            // Au clic sur un utilisateur, rediriger vers messages.html avec l'ID utilisateur en paramètre
            $('#user-list').on('click', 'li[data-id]', function() {
                var userId = $(this).data('id');
                console.log("Clique sur un utilisateur, id =", userId);
                window.location.href = "messages.php?userId=" + userId;
            });

            $('#search-bar').on('input', function() {
                const query = $(this).val();
                fetchUsers(query);
            });

            // Chargement initial de tous les utilisateurs
            fetchUsers();
        });
    </script>
</body>
</html>