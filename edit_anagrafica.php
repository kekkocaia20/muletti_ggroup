<?php
require 'auth_check.php';
require 'config.php';

// Controlla che ci sia l'id passato
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['msg_success'] = "ID Muletto non valido.";
    header("Location: elenco_muletti.php");
    exit;
}

$id = (int)$_GET['id'];

// Recupera i dati del muletto
$stmt = $pdo->prepare("SELECT * FROM anagrafica WHERE id = ?");
$stmt->execute([$id]);
$muletto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$muletto) {
    $_SESSION['msg_success'] = "Muletto non trovato.";
    header("Location: elenco_muletti.php");
    exit;
}

// Se il form viene inviato
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matricola = $_POST['matricola'] ?? '';
    $marca = $_POST['marca'] ?? '';
    $modello = $_POST['modello'] ?? '';
    $tipo = $_POST['tipo'] ?? '';
    $tipo_possesso = $_POST['tipo_possesso'] ?? '';
    $stato = $_POST['stato'] ?? '';
    $filiale = $_POST['filiale'] ?? '';

    // Aggiorna il DB
    $update = $pdo->prepare("UPDATE anagrafica SET 
        matricola = ?, 
        marca = ?, 
        modello = ?, 
        tipo = ?, 
        tipo_possesso = ?, 
        stato = ?, 
        filiale = ? 
        WHERE id = ?");
    $update->execute([$matricola, $marca, $modello, $tipo, $tipo_possesso, $stato, $filiale, $id]);

    $_SESSION['msg_success'] = "Muletto aggiornato con successo!";
    header("Location: view_muletti.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Anagrafica</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- CSS personalizzato -->
    <link rel="stylesheet" href="css/insert.css">
    <!-- Font personalizzato -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
<!-- 🌐 Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark shadow-sm fixed-top custom-navbar">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center" href="view_dashboard.php">
      <img src="img/LOGO-GOLOGISTICS.png" alt="Logo" width="100" height="60" class="me-2 rounded">
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
      aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'view_dashboard.php' ? 'active' : '' ?>" href="view_dashboard.php">
            📊 Dashboard
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'view_muletti.php' ? 'active' : '' ?>" href="view_muletti.php">
            🚜 Elenco Muletti
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-5 pt-5">
  <h2>✏️ Modifica Muletto</h2>
  <form method="POST" class="mt-4">
    
    <div class="mb-3">
      <label class="form-label">Matricola</label>
      <input type="text" name="matricola" class="form-control" value="<?= htmlspecialchars($muletto['matricola']) ?>" required>
    </div>

    <div class="mb-3">
      <label for="marca" class="form-label">Marca</label>
      <select class="form-select" id="marca" name="marca" required>
        <option value="">-- Seleziona una marca --</option>
        <?php foreach (['OM','Still','Fiorentini'] as $m): ?>
          <option value="<?= $m ?>" <?= ($muletto['marca']===$m)?'selected':'' ?>><?= $m ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="mb-3">
      <label for="modello" class="form-label">Modello</label>
      <select class="form-select" id="modello" name="modello" required>
        <option value="">-- Seleziona un modello --</option>
        <?php 
        $modelli = ["XE15/3AC","XE18/3AC","CTX14","TSX","EXV12","SB28NEW","138VE","RX2018","EXVSF14","FM-X14","RX20-18","EXH-SF20","RX20-16C","RX20-16","RX20-20","ECU16","ECOSMILE75"];
        foreach ($modelli as $mod): ?>
          <option value="<?= $mod ?>" <?= ($muletto['modello']===$mod)?'selected':'' ?>><?= $mod ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="mb-3">
      <label for="tipo" class="form-label">Tipo</label>
      <select class="form-select" id="tipo" name="tipo" required>
        <option value="">-- Seleziona il tipo --</option>
        <?php foreach (['Frontale','Retrattile','Transpallet','Lavasciuga'] as $t): ?>
          <option value="<?= $t ?>" <?= ($muletto['tipo']===$t)?'selected':'' ?>><?= $t ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="mb-3">
      <label for="tipo_possesso" class="form-label">Tipo Possesso</label>
      <select class="form-select" id="tipo_possesso" name="tipo_possesso" required>
        <option value="">-- Seleziona il tipo possesso --</option>
        <?php foreach (['Proprietà','Noleggio'] as $tp): ?>
          <option value="<?= $tp ?>" <?= ($muletto['tipo_possesso']===$tp)?'selected':'' ?>><?= $tp ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="mb-3">
      <label for="stato" class="form-label">Stato</label>
      <select class="form-select" id="stato" name="stato" required>
        <option value="">-- Seleziona lo stato --</option>
        <?php foreach (['Attivo','Rottamato','In Riparazione'] as $s): ?>
          <option value="<?= $s ?>" <?= ($muletto['stato']===$s)?'selected':'' ?>><?= $s ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="mb-3">
      <label for="filiale" class="form-label">Filiale</label>
      <select class="form-select" id="filiale" name="filiale" required>
        <option value="">-- Seleziona la filiale --</option>
        <?php foreach (['Casalnuovo','Volla','Roma','Misterbianco'] as $f): ?>
          <option value="<?= $f ?>" <?= ($muletto['filiale']===$f)?'selected':'' ?>><?= $f ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">💾 Salva Modifiche</button>
    <a href="elimina_muletto.php?id=<?= $id ?>" 
     class="btn btn-danger"
     onclick="return confirm('⚠️ Sei sicuro di voler eliminare questo muletto e tutte le sue manutenzioni collegate?');">
     🗑️ Elimina Muletto
    </a>
  </form>
</div>
</body>
</html>
