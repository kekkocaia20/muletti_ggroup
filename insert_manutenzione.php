<?php
require 'auth_check.php';
require 'config.php';

// Se il form è stato inviato
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_muletto = $_POST['id_muletto'];
    $data_manutenzione = $_POST['data_manutenzione'];
    $ore_attuali = $_POST['ore_attuali'];


    // Calcola la prossima manutenzione solo se è ordinaria
    if ($_POST['tipo_intervento'] === 'Manutenzione Ordinaria') {
    $prossima_manutenzione = date('Y-m-d', strtotime('+3 months', strtotime($data_manutenzione)));
    } else {
    $prossima_manutenzione = null; // nessuna prossima manutenzione per le straordinarie
    }

    $numero_rapportino = $_POST['numero_rapportino'];
    $tipo_intervento = $_POST['tipo_intervento'];
    $descrizione = $_POST['descrizione'];
    $costo = $_POST['costo'];
    $fornitore = $_POST['fornitore'];

    $stmt = $pdo->prepare("INSERT INTO manutenzione 
        (id_muletto, data_manutenzione, ore_attuali, prossima_manutenzione, numero_rapportino, tipo_intervento, descrizione, costo, fornitore)
        VALUES (:id_muletto, :data_manutenzione, :ore_attuali, :prossima_manutenzione, :numero_rapportino, :tipo_intervento, :descrizione, :costo, :fornitore)");

    $stmt->execute([
        ':id_muletto' => $id_muletto,
        ':data_manutenzione' => $data_manutenzione,
        ':ore_attuali' => $ore_attuali,
        ':prossima_manutenzione' => $prossima_manutenzione,
        ':numero_rapportino' => $numero_rapportino,
        ':tipo_intervento' => $tipo_intervento,
        ':descrizione' => $descrizione,
        ':costo' => $costo,
        ':fornitore' => $fornitore
    ]);

    // 🔹 REDIREZIONE alla pagina principale dei muletti
    $_SESSION['msg_success'] = "✅ Manutenzione effettuata con successo!";
    header('Location: view_muletti.php'); // sostituisci con il nome corretto della tua pagina principale
    exit(); // sempre necessario dopo header
}

// Recupero muletti per la select
$muletti = $pdo->query("SELECT id, matricola, marca, modello FROM anagrafica ORDER BY matricola")->fetchAll(PDO::FETCH_ASSOC);
$id_preselezionato = $_GET['id_muletto'] ?? '';
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inserisci Manutenzione</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
    <h2>Inserisci Manutenzione</h2>

    <form action="" method="POST">
        <div class="mb-3">
            <label for="id_muletto" class="form-label">Muletto</label>
            <select name="id_muletto" class="form-select" required>
                <option value="">-- Seleziona Muletto --</option>
                 <?php foreach ($muletti as $m): ?>
                   <option value="<?= htmlspecialchars($m['id']) ?>"
                   <?= $id_preselezionato == $m['id'] ? 'selected' : '' ?>>
                   <?= htmlspecialchars($m['matricola'] . " - " . $m['marca'] . " " . $m['modello']) ?>
                </option>
               <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="numero_rapportino" class="form-label">Numero rapportino</label>
            <input type="text" class="form-control" name="numero_rapportino" id="numero_rapportino">
        </div>

        <div class="mb-3">
            <label for="tipo_intervento" class="form-label">Tipo intervento</label>
            <select class="form-select" name="tipo_intervento" id="tipo_intervento">
                <option value="">-- Seleziona il tipo intervento --</option>
                <option value="Manutenzione Ordinaria">Manutenzione Ordinaria</option>
                <option value="Manutenzione Straordinaria">Manutenzione Straordinaria</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="data_manutenzione" class="form-label">Data manutenzione</label>
            <input type="date" class="form-control" name="data_manutenzione" id="data_manutenzione" required>
        </div>

        <div class="mb-3">
            <label for="prossima_manutenzione" class="form-label">Prossima manutenzione</label>
            <input type="date" class="form-control" name="prossima_manutenzione" id="prossima_manutenzione" readonly>
        </div>

        <div class="mb-3">
            <label for="ore_attuali" class="form-label">Ore attuali</label>
            <input type="number" class="form-control" name="ore_attuali" id="ore_attuali" placeholder="Inserisci le ore" required>
        </div>

        <div class="mb-3">
            <label for="descrizione" class="form-label">Descrizione</label>
            <textarea class="form-control" name="descrizione" id="descrizione" rows="4"></textarea>
        </div>

        <div class="mb-3">
            <label for="costo" class="form-label">Costo (€)</label>
            <input type="number" step="0.01" class="form-control" name="costo" id="costo">
        </div>

        <div class="mb-3">
            <label for="fornitore" class="form-label">Fornitore</label>
            <select class="form-select" name="fornitore" id="fornitore">
                <option value="">-- Seleziona il fornitore --</option>
                <option value="SERVICAR">SERVICAR</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Inserisci Manutenzione</button>
    </form>
</div>

<!-- JS: aggiorna automaticamente la prossima manutenzione -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dataManut = document.getElementById('data_manutenzione');
    const prossimaManut = document.getElementById('prossima_manutenzione');
    const tipoIntervento = document.getElementById('tipo_intervento');

    // Calcolo automatico per le manutenzioni ordinarie
    dataManut.addEventListener('change', function() {
        if (this.value && tipoIntervento.value === 'Manutenzione Ordinaria') {
            let data = new Date(this.value);
            data.setMonth(data.getMonth() + 3);
            let month = (data.getMonth() + 1).toString().padStart(2, '0');
            let day = data.getDate().toString().padStart(2, '0');
            prossimaManut.value = `${data.getFullYear()}-${month}-${day}`;
        } else {
            prossimaManut.value = '';
        }
    });

    // Se cambia il tipo intervento
    tipoIntervento.addEventListener('change', function() {
        if (this.value === 'Manutenzione Straordinaria') {
            prossimaManut.value = '';
            prossimaManut.readOnly = true;
        } else {
            prossimaManut.readOnly = false;
            // Ricalcola se c'è già una data manutenzione
            if (dataManut.value) dataManut.dispatchEvent(new Event('change'));
        }
    });
});
</script>


</body>
</html>
