<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Instant Message</title>
        <!-- Inclusion de Tailwind CSS -->
        <script src="https://cdn.tailwindcss.com"></script>
        <!-- Inclusion de jQuery -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <!-- Inclusion de FontAwesome pour l'icône -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    </head>
    <body class="min-h-screen flex flex-col">
        <main class="flex-grow p-4 flex justify-center">
            <!-- Affichage des informations du compte utilisateur -->
            <div class="flex flex-col justify-between">
                <div id="account-info"></div>
                <!-- Formulaire pour se déconnecter -->
                <form action="../public/api/logout.php" method="POST">
                    <button class="bg-red-600 rounded-lg text-white px-4 py-2 mt-4 w-full" type="submit">
                        Se déconnecter
                    </button>
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
                            // Affichage des informations de l'utilisateur et du formulaire de modification du username
                            $("#account-info").html(
                                '<div class="flex items-center w-[500px] text-left">' +
                                    '<div class="h-24 w-24 rounded-full bg-gray-400 mr-2"></div>' +
                                    '<div class="flex flex-col gap-2">' +
                                        // Affichage du username avec bouton d'édition
                                        '<div class="flex items-center">' +
                                            '<span id="username-display" class="text-2xl font-bold">' + response.user.username + '</span>' +
                                            '<button id="edit-username" class="ml-2 text-gray-600 hover:text-gray-800 focus:outline-none">' +
                                                '<i class="fas fa-pen"></i>' +
                                            '</button>' +
                                        '</div>' +
                                        // Affichage de l'email
                                        '<span>' + response.user.login + '</span>' +
                                    '</div>' +
                                '</div>'
                            );
                        } else {
                            // Message si aucun email n'est trouvé
                            $("#account-info").text("Aucun email trouvé.");
                        }
                    },
                    error: function() {
                        // Message d'erreur en cas d'échec de la requête AJAX
                        $("#account-info").text("Erreur lors de la récupération des informations.");
                    }
                });

                // Conversion du username en champ input lors du clic sur le bouton d'édition
                $("#account-info").on("click", "#edit-username", function(e) {
                    e.preventDefault();
                    var currentUsername = $("#username-display").text();
                    $("#username-display").replaceWith('<input type="text" id="username-input" class="text-2xl font-bold" value="'+ currentUsername +'" />');
                    $("#username-input").focus();
                });

                // Sauvegarde du nouveau username lorsque l'utilisateur appuie sur la touche Enter
                $("#account-info").on("keypress", "#username-input", function(e) {
                    if (e.which === 13) {
                        e.preventDefault();
                        var newUsername = $(this).val();
                        // Envoi de la mise à jour du username via AJAX
                        $.ajax({
                            url: "../public/api/user.php?action=updateUsername",
                            method: "POST",
                            data: { username: newUsername },
                            dataType: "json",
                            success: function(response) {
                                console.log(response); // Pour le débogage
                                if (response.success) {
                                    $("#username-input").replaceWith('<span id="username-display" class="text-2xl font-bold">' + newUsername + '</span>');
                                } else {
                                    alert(response.message);
                                }
                            }
                        });
                        $(this).blur(); // Retire le focus du champ input
                        return false;
                    }
                });
            });
        </script>
    </body>
</html>