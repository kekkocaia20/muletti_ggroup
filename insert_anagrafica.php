<?php
require 'auth_check.php';
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matricola = $_POST['matricola'];
    $marca = $_POST['marca'];
    $modello = $_POST['modello'];
    $tipo = $_POST['tipo'];
    $tipo_possesso = $_POST['tipo_possesso'];
    $stato = $_POST['stato'];
    $filiale = $_POST['filiale'];

    $sql = "INSERT INTO anagrafica 
            (matricola, marca, modello, tipo, tipo_possesso, stato, filiale)
            VALUES 
            (:matricola, :marca, :modello, :tipo, :tipo_possesso, :stato, :filiale)";
    
    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':matricola' => $matricola,
        ':marca' => $marca,
        ':modello' => $modello,
        ':tipo' => $tipo,
        ':tipo_possesso' => $tipo_possesso,
        ':stato' => $stato,
        ':filiale' => $filiale
    ]);

    // 🔹 REDIREZIONE alla pagina principale dei muletti
    $_SESSION['msg_success'] = "✅ Muletto inserito con successo!";
    header('Location: view_muletti.php'); // sostituisci con il nome corretto della tua pagina principale
    exit(); // sempre necessario dopo header
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Anagrafica</title>
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

  <h2>Inserisci un nuovo muletto</h2>

  <form action="" method="POST" class="needs-validation" novalidate>
    <div class="mb-3">
      <label for="matricola" class="form-label">Matricola</label>
      <input type="text" class="form-control" id="matricola" name="matricola" required>
    </div>
    <div class="mb-3">
      <label for="marca" class="form-label">Marca</label>
        <select class="form-select" id="marca" name="marca" required>
          <option value="">-- Seleziona una marca --</option>
          <option value="OM">OM</option>
          <option value="Still">Still</option>
          <option value="Fiorentini">Fiorentini</option>
        </select>
    </div>
    <div class="mb-3">
      <label for="modello" class="form-label">Modello</label>
        <select class="form-select" id="modello" name="modello" required>
          <option value="">-- Seleziona un modello --</option>
          <option value="XE15/3AC">XE15/3AC</option>
          <option value="XE18/3AC">XE18/3AC</option>
          <option value="CTX14">CTX14</option>
          <option value="TSX">TSX</option>
          <option value="EXV12">EXV12</option>
          <option value="SB28NEW">SB28NEW</option>
          <option value="138VE">138VE</option>
          <option value="RX2018">RX2018</option>
          <option value="EXVSF14">EXVSF14</option>
          <option value="FM-X14">FM-X14</option>
          <option value="RX20-18">RX20-18</option>
          <option value="EXH-SF20">EXH-SF20</option>
          <option value="RX20-16C">RX20-16C</option>
          <option value="RX20-16">RX20-16</option>
          <option value="RX20-20">RX20-20</option>
          <option value="ECU16">ECU16</option>
          <option value="ECOSMILE75">ECOSMILE75</option>
        </select>
    </div>
    <div class="mb-3">
      <label for="tipo" class="form-label">Tipo</label>
      <select class="form-select" id="tipo" name="tipo" required>
        <option value="">-- Seleziona il tipo --</option>
        <option value="Frontale">Frontale</option>
        <option value="Retrattile">Retrattile</option>
        <option value="Transpallet">Transpallet</option>
        <option value="Lavasciuga">Lavasciuga</option>
      </select>
    </div>
    <div class="mb-3">
      <label for="tipo_possesso" class="form-label">Tipo Possesso</label>
      <select class="form-select" id="tipo_possesso" name="tipo_possesso" required>
        <option value="">-- Seleziona il tipo possesso --</option>
        <option value="Proprietà">Proprietà</option>
        <option value="Noleggio">Noleggio</option>
      </select>
    </div>
    <div class="mb-3">
      <label for="stato" class="form-label">Stato</label>
      <select class="form-select" id="stato" name="stato" required>
        <option value="">-- Seleziona lo stato --</option>
        <option value="Attivo">Attivo</option>
        <option value="Rottamato">Rottamato</option>
        <option value="In Riparazione">In Riparazione</option>
      </select>
    </div>
    <div class="mb-3">
      <label for="filiale" class="form-label">Filiale</label>
      <select class="form-select" id="filiale" name="filiale" required>
        <option value="">-- Seleziona la filiale --</option>
        <option value="Casalnuovo">Casalnuovo</option>
        <option value="Volla">Volla</option>
        <option value="Roma">Roma</option>
        <option value="Misterbianco">Misterbianco</option>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">Inserisci Muletto</button>
  </form>
</div>

</body>
</html>
