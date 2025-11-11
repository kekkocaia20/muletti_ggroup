<?php
// Avvia la sessione con opzioni di sicurezza aggiuntive
session_start([
    'cookie_httponly' => true, // Impedisce accesso tramite JavaScript
    'use_strict_mode' => true, // Aiuta a prevenire session fixation
    'cookie_secure' => false // Imposta a true se usi HTTPS
]);

// ⚠️ NECESSARIO: Includi il file di configurazione con le credenziali corrette di InfinityFree
require __DIR__ . '/config.php';

$error = "";

// Se già loggato, reindirizza alla lista (Controllo di sicurezza anticipato)
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: view_dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';

    // 1. Prendi utente dal database (Preparazione dello statement)
    // Usa l'oggetto $pdo definito in config.php
    $stmt = $pdo->prepare("SELECT password_hash FROM admins WHERE username = :username");
    $stmt->execute([':username' => $user]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Se utente esiste e password corretta
    if ($admin && password_verify($pass, $admin['password_hash'])) {
        
        // 3. Login riuscito: Imposta la sessione
        $_SESSION['admin_logged_in'] = true;
        
        // Rigenera l'ID di sessione per prevenire session fixation
        session_regenerate_id(true); 
        
        header('Location: view_dashboard.php');
        exit;
    } else {
        $error = "Username o password errati";
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Login Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/login.css">
    <!-- Font personalizzato -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
<!-- 🌐 Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark shadow-sm fixed-top custom-navbar">
  <div class="container-fluid justify-content-center">
    <a class="navbar-brand d-flex align-items-center" href="">
      <img src="img/LOGO-GOLOGISTICS.png" alt="Logo" width="160" height="100" class="me-2 rounded">
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
      aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
  </div>
</nav>

<div class="container mt-3 pt-3">
    <h1 class="mb-4 text-center">Login Admin</h1>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" id="username" name="username" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
        <div class="input-group">
          <input type="password" id="password" name="password" class="form-control" required>
          <button type="button" id="togglePassword" class="btn btn-outline-secondary text-dark">
            Mostra
          </button>
        </div>
        </div>
        <button type="submit" class="btn btn-primary w-100">Accedi</button>
    </form>
</div>

<script>
const passwordInput = document.getElementById('password');
const toggleButton = document.getElementById('togglePassword');

toggleButton.addEventListener('click', () => {
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleButton.textContent = 'Nascondi';
    } else {
        passwordInput.type = 'password';
        toggleButton.textContent = 'Mostra';
    }
});
</script>
</body>
</html>
