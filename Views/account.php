<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Instant Message</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Si vous utilisez FontAwesome pour l'icône, incluez son CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="min-h-screen flex flex-col">
    <main class="flex-grow p-4 flex justify-center">
        <!-- Affichage de l'email de l'utilisateur -->
        <div class="flex flex-col justify-between">
            <div id="account-info"></div>
            <form action="../public/api/logout.php" method="POST">
                <button class="bg-red-600 rounded-lg text-white px-4 py-2 mt-4 w-full" type="submit">Se déconnecter</button>
            </form>
        </div>
    </main>

    <?php include __DIR__ . '/partials/footer.php'; ?>
    
    <script>
    $(document).ready(function() {
        // Récupération de l'utilisateur courant
        $.ajax({
            url: "../public/api/user.php?action=getCurrentUser",
            method: "GET",
            dataType: "json",
            success: function(response) {
                if (response.user && response.user.login) {
                    $("#account-info").html(
                        '<div class="flex items-center w-[500px] text-left">' +
                            '<div class="h-24 w-24 rounded-full bg-gray-400 mr-2"></div>' +
                            '<div class="flex flex-col gap-2">' +
                                // Conteneur pour le username avec icône crayon
                                '<div class="flex items-center">' +
                                    '<span id="username-display" class="text-2xl font-bold">' + response.user.username + '</span>' +
                                    '<button id="edit-username" class="ml-2 text-gray-600 hover:text-gray-800 focus:outline-none">' +
                                        '<i class="fas fa-pen"></i>' +
                                    '</button>' +
                                '</div>' +
                                '<span>' + response.user.login + '</span>' +
                            '</div>' +
                        '</div>'
                    );
                } else {
                    $("#account-info").text("Aucun email trouvé.");
                }
            },
            error: function() {
                $("#account-info").text("Erreur lors de la récupération des informations.");
            }
        });
        
        // Au clic sur le bouton crayon : convertir le username en champ input
        $("#account-info").on("click", "#edit-username", function(e) {
            e.preventDefault();
            var currentUsername = $("#username-display").text();
            $("#username-display").replaceWith('<input type="text" id="username-input" class="text-2xl font-bold" value="'+ currentUsername +'" />');
            $("#username-input").focus();
        });
        
        // Lors de la validation avec la touche Enter
        $("#account-info").on("keypress", "#username-input", function(e) {
            if (e.which === 13) { // Enter key
                e.preventDefault();  // Prevent any default action
                var newUsername = $(this).val();
                $.ajax({
                    url: "../public/api/user.php?action=updateUsername",
                    method: "POST",
                    data: { username: newUsername },
                    dataType: "json",
                    success: function(response) {
                        console.log(response); // For debugging
                        if (response.success) {
                            $("#username-input").replaceWith('<span id="username-display" class="text-2xl font-bold">' + newUsername + '</span>');
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function() {
                        alert("Erreur lors de la mise à jour du username.");
                    }
                });
                return false;
            }
        });
    });
    </script>
</body>
</html>