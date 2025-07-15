<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - ClicService</title>
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
        .error-message {
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    
    <header class="bg-white shadow-md fixed w-full top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-xl font-bold text-blue-600">ClicService</h1>
            <nav class="space-x-6 text-sm font-medium text-gray-600">
                <a href="/index.php" class="hover:text-blue-500">Accueil</a>
                <a href="#" class="hover:text-blue-500">Connexion</a>
                <a href="#" class="hover:text-blue-500">Inscription</a>
            </nav>
        </div>
    </header>

    <div class="max-w-6xl w-full flex rounded-xl overflow-hidden card-shadow mt-16">
        <!-- Left side - Form -->
        <div class="w-full md:w-1/2 bg-white p-10 flex flex-col justify-center">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Connectez-vous à ClicService</h1>
                <p class="text-gray-500">ou utilisez votre email pour vous connecter</p>
            </div>

            <div id="error-message" class="mb-4 p-3 bg-red-100 text-red-700 rounded hidden"></div>
            <div id="success-message" class="mb-4 p-3 bg-green-100 text-green-700 rounded hidden"></div>

            <form id="loginForm" method="POST" class="space-y-6">
                <div class="space-y-1">
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="email" name="email" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:border-blue-500 input-focus transition" placeholder="votre@email.com">
                    <div id="email-error" class="error-message"></div>
                </div>

                <div class="space-y-1">
                    <label for="password" class="block text-sm font-medium text-gray-700">Mot de passe</label>
                    <input type="password" id="password" name="password" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:border-blue-500 input-focus transition" placeholder="••••••••">
                    <div id="password-error" class="error-message"></div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="remember-me" class="ml-2 block text-sm text-gray-700">Se souvenir de moi</label>
                    </div>
                    <div class="text-sm">
                        <a href="#" class="font-medium text-blue-600 hover:text-blue-500">Mot de passe oublié?</a>
                    </div>
                </div>

                <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                    SE CONNECTER
                </button>
            </form>

        
        </div>

        <!-- Right side - Welcome -->
        <div class="hidden md:flex md:w-1/2 gradient-bg p-10 flex-col justify-center text-white">
            <div class="text-center">
                <h2 class="text-4xl font-bold mb-4">Bonjour, Ami !</h2>
                <p class="text-xl mb-8 opacity-90">Commencez une incroyable aventure<br>et amusez-vous avec nous</p>
                <a href="/views/signUpPresta.php" class="px-8 py-3 bg-white text-blue-600 font-bold rounded-lg hover:bg-gray-100 transition transform hover:scale-105 block">
                    S'INSCRIRE
                </a>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Reset error messages
            document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
            document.getElementById('error-message').classList.add('hidden');
            document.getElementById('success-message').classList.add('hidden');
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('../controllers/signInClient.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.status === 'error') {
                    if (result.errors) {
                        // Display field-specific errors
                        for (const field in result.errors) {
                            const errorElement = document.getElementById(`${field}-error`);
                            if (errorElement) {
                                errorElement.textContent = result.errors[field];
                            }
                        }
                    }
                    
                    if (result.message) {
                        // Display general error message
                        const errorElement = document.getElementById('error-message');
                        errorElement.textContent = result.message;
                        errorElement.classList.remove('hidden');
                    }
                } else if (result.status === 'success') {
                    // Display success message and redirect to client profile
                    const successElement = document.getElementById('success-message');
                    successElement.textContent = result.message || 'Connexion réussie! Redirection en cours...';
                    successElement.classList.remove('hidden');
                    
                    // Redirect to profile page after a short delay
                    setTimeout(() => {
                        window.location.href = result.redirect || 'profilClient.php';
                    }, 1500);
                }
            } catch (error) {
                console.error('Error:', error);
                const errorElement = document.getElementById('error-message');
                errorElement.textContent = "Une erreur s'est produite. Veuillez réessayer.";
                errorElement.classList.remove('hidden');
            }
        });
    </script>
</body>
</html>