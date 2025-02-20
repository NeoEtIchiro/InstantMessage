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
        <!-- Affichage de l'email de l'utilisateur -->
        <div id="account-info"></div>
        <form action="../public/api/logout.php" method="POST">
            <button class="bg-red-600 rounded-lg text-white px-4 py-2 mt-4" type="submit">Se déconnecter</button>
        </form>
    </main>

    <?php include __DIR__ . '/partials/footer.php'; ?>
    
    <script>
    $(document).ready(function() {
        // Appel à l'API pour récupérer l'utilisateur courant
        $.ajax({
            url: "../public/api/user.php?action=getCurrentUser",
            method: "GET",
            dataType: "json",
            success: function(response) {
                if (response.user && response.user.login) {
                    $("#account-info").html(
                        '<div class="flex items-center">' +
                            '<div class="h-12 w-12 rounded-full bg-gray-400 mr-2"></div>' +
                            '<span>' +  response.user.login + '</span>' +
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
    });
    </script>
</body>
</html>