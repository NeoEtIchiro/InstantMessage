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
        <div id="account-email"></div>
        <form action="../public/api/logout.php" method="POST">
            <button type="submit">Se déconnecter</button>
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
                    $("#account-email").text("Email : " + response.user.login);
                } else {
                    $("#account-email").text("Aucun email trouvé.");
                }
            },
            error: function() {
                $("#account-email").text("Erreur lors de la récupération des informations.");
            }
        });
    });
    </script>
</body>
</html>