<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - ClicService</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8fafc;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%);
        }
        .card-shadow {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .input-focus:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <?php session_start(); ?>

    <div class="max-w-6xl w-full flex rounded-xl overflow-hidden card-shadow">
        <!-- Left side - Form -->
        <div class="w-full md:w-1/2 bg-white p-10 flex flex-col justify-center">

            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Créez votre compte</h1>
                <p class="text-gray-500">Rejoignez la communauté ClicService</p>
            </div>

            <!-- Messages de session -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
                    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
                    <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <form action="../controllers/signUpClient.php" method="POST" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label for="nom" class="block text-sm font-medium text-gray-700">Nom</label>
                        <input type="text" id="nom" name="nom" required class="w-full px-4 py-3 rounded-lg border border-gray-300 input-focus transition" placeholder="Votre nom">
                    </div>
                    <div class="space-y-1">
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="email" name="email" required class="w-full px-4 py-3 rounded-lg border border-gray-300 input-focus transition" placeholder="votre@email.com">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label for="password" class="block text-sm font-medium text-gray-700">Mot de passe</label>
                        <input type="password" id="password" name="password" required class="w-full px-4 py-3 rounded-lg border border-gray-300 input-focus transition" placeholder="••••••••">
                    </div>
                    <div class="space-y-1">
                        <label for="phone" class="block text-sm font-medium text-gray-700">Téléphone</label>
                        <input type="tel" id="phone" name="phone" required class="w-full px-4 py-3 rounded-lg border border-gray-300 input-focus transition" placeholder="+243 000000000">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label for="commune" class="block text-sm font-medium text-gray-700">Commune</label>
                        <input type="text" id="commune" name="commune" required class="w-full px-4 py-3 rounded-lg border border-gray-300 input-focus transition" placeholder="Votre commune">
                    </div>
                    <div class="space-y-1">
                        <label for="ville" class="block text-sm font-medium text-gray-700">Ville</label>
                        <input type="text" id="ville" name="ville" required class="w-full px-4 py-3 rounded-lg border border-gray-300 input-focus transition" placeholder="Votre ville">
                    </div>
                </div>

                <div class="flex items-center mt-2">
                    <input id="terms" name="terms" type="checkbox" class="h-4 w-4 text-blue-600 border-gray-300 rounded" required>
                    <label for="terms" class="ml-2 block text-sm text-gray-700">
                        J'accepte les <a href="#" class="text-blue-600 hover:text-blue-500">conditions d'utilisation</a>
                    </label>
                </div>

                <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition mt-6">
                    S'INSCRIRE
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Vous avez déjà un compte ? 
                    <a href="login.php" class="font-medium text-blue-600 hover:text-blue-500">Se connecter</a>
                </p>
            </div>
        </div>

        <!-- Right side - Welcome -->
        <div class="hidden md:flex md:w-1/2 gradient-bg p-10 flex-col justify-center text-white">
            <div class="text-center">
                <h2 class="text-4xl font-bold mb-4">Bienvenue !</h2>
                <p class="text-xl mb-8 opacity-90">Rejoignez notre communauté de professionnels et clients</p>
                <a href="login.php" class="inline-block">
                    <button class="px-8 py-3 bg-white text-blue-600 font-bold rounded-lg hover:bg-gray-100 transition transform hover:scale-105">
                        SE CONNECTER
                    </button>
                </a>
            </div>
        </div>
    </div>
</body>
</html>