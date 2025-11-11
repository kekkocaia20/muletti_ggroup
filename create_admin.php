<?php
require __DIR__ . '/config.php'; // $pdo

// Dati esempio: nella realtà prendi da form o CLI
$username = 'test';
$passwordPlain = 'test123';

// Validazioni minime
if (empty($username) || empty($passwordPlain)) {
    die('Username e password richiesti');
}

// Crea l'hash (PASSWORD_DEFAULT usa bcrypt/argon2 a seconda della versione PHP)
$hash = password_hash($passwordPlain, PASSWORD_DEFAULT);

// Opzionale: controlla che l'hash sia stato generato
if ($hash === false) {
    die('Errore generazione hash');
}

// Insert sicuro con prepared statement
$stmt = $pdo->prepare("INSERT INTO admins (username, password_hash) VALUES (:username, :hash)");
$stmt->execute([
    ':username' => $username,
    ':hash' => $hash
]);

echo "Utente creato con successo\n";
