<?php
session_start();
if (isset($_SESSION['user'])) {
    header('Location: views/messages.php');
    exit;
} else {
    header('Location: views/login_register.html');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Titre de la</title>
</head>
<body class="min-h-screen flex flex-col">
    
</body>
</html>
