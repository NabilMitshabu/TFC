<?php 

// Vérifie si la session n'est pas déjà démarrée avant de l'initialiser
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header class="bg-white shadow-md fixed w-full top-0 z-50">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        <a href="index.php" class="text-xl font-bold text-blue-600">ClicService</a>
        <nav class="flex items-center space-x-6 text-sm font-medium text-gray-600">
            <a href="/../index.php" class="hover:text-blue-500">Accueil</a>
            
            <?php if (!empty($_SESSION['user'])): ?>
                <div class="relative group">
                    <button class="flex items-center space-x-2 focus:outline-none">
                        <span class="hidden md:inline">
                            <?= htmlspecialchars($_SESSION['user']['prenom'] ?? $_SESSION['user']['nom']) ?>
                        </span>
                        <img src="https://ui-avatars.com/api/?name=<?= 
                            urlencode(
                                ($_SESSION['user']['prenom'] ?? '') . '+' . $_SESSION['user']['nom']
                            ) ?>&background=3b82f6&color=fff" 
                             alt="Profil" class="w-8 h-8 rounded-full">
                    </button>
                    <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 hidden group-hover:block">
                        <a href="<?= ($_SESSION['user']['role'] ?? 'client') === 'prestataire' ? 'dashboard-prestataire.php' : 'profilClient.php' ?>" 
                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            Mon profil
                        </a>
                        <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Déconnexion</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="/../views/signInClient.php" class="hover:text-blue-500">Connexion</a>
                <a href="/../views/signUpClient.php" class="hover:text-blue-500">Inscription</a>
            <?php endif; ?>
        </nav>
    </div>
</header>