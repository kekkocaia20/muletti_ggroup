<?php
require 'auth_check.php';
require 'config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['msg_success'] = "ID Manutenzione non valido.";
    header("Location: view_muletti.php");
    exit;
}

$id = (int)$_GET['id'];

// Recupera i dati della manutenzione
$stmt = $pdo->prepare("SELECT * FROM manutenzione WHERE id = ?");
$stmt->execute([$id]);
$manutenzione = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$manutenzione) {
    $_SESSION['msg_success'] = "Manutenzione non trovata.";
    header("Location: view_manutenzione.php");
    exit;
}

// Recupero muletti per la select
$muletti = $pdo->query("SELECT id, matricola, marca, modello FROM anagrafica ORDER BY matricola")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_muletto = $_POST['id_muletto'];
    $data_manutenzione = $_POST['data_manutenzione'];
    $ore_attuali = $_POST['ore_attuali'];
    $tipo_intervento = $_POST['tipo_intervento'];
    $numero_rapportino = $_POST['numero_rapportino'];
    $descrizione = $_POST['descrizione'];
    $costo = $_POST['costo'];
    $fornitore = $_POST['fornitore'];

    if ($tipo_intervento === 'Manutenzione Ordinaria') {
        $prossima_manutenzione = date('Y-m-d', strtotime('+3 months', strtotime($data_manutenzione)));
    } else {
        $prossima_manutenzione = null;
    }

    $stmt = $pdo->prepare("UPDATE manutenzione SET
        id_muletto=:id_muletto,
        data_manutenzione=:data_manutenzione,
        ore_attuali=:ore_attuali,
        prossima_manutenzione=:prossima_manutenzione,
        numero_rapportino=:numero_rapportino,
        tipo_intervento=:tipo_intervento,
        descrizione=:descrizione,
        costo=:costo,
        fornitore=:fornitore
        WHERE id=:id");
    
    $stmt->execute([
        ':id_muletto'=>$id_muletto,
        ':data_manutenzione'=>$data_manutenzione,
        ':ore_attuali'=>$ore_attuali,
        ':prossima_manutenzione'=>$prossima_manutenzione,
        ':numero_rapportino'=>$numero_rapportino,
        ':tipo_intervento'=>$tipo_intervento,
        ':descrizione'=>$descrizione,
        ':costo'=>$costo,
        ':fornitore'=>$fornitore,
        ':id'=>$id
    ]);

    $_SESSION['msg_success'] = "Manutenzione aggiornata con successo!";
    header("Location: view_manutenzione.php?id_muletto=" . $id_muletto);
    exit;
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Modifica Manutenzione</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="css/insert.css">
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
            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'view_manutenzione.php' ? 'active' : '' ?>" 
               href="view_manutenzione.php?id_muletto=<?= $manutenzione['id_muletto'] ?>">
                🔧 Elenco Manutenzione
            </a>
        </li>
    </ul>
</div>
  </div>
</nav>

<div class="container mt-5 pt-5">
<h2>✏️ Modifica Manutenzione</h2>
<form method="POST">
    <div class="mb-3">
        <label class="form-label">Muletto</label>
        <select name="id_muletto" class="form-select" required>
            <option value="">-- Seleziona Muletto --</option>
            <?php foreach ($muletti as $m): ?>
                <option value="<?= $m['id'] ?>" <?= ($manutenzione['id_muletto']==$m['id'])?'selected':'' ?>>
                    <?= htmlspecialchars($m['matricola'] . " - " . $m['marca'] . " " . $m['modello']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Numero rapportino</label>
        <input type="text" name="numero_rapportino" class="form-control" value="<?= htmlspecialchars($manutenzione['numero_rapportino']) ?>">
    </div>

    <div class="mb-3">
        <label class="form-label">Tipo intervento</label>
        <select name="tipo_intervento" id="tipo_intervento" class="form-select">
            <option value="">-- Seleziona il tipo intervento --</option>
            <option value="Manutenzione Ordinaria" <?= ($manutenzione['tipo_intervento']=='Manutenzione Ordinaria')?'selected':'' ?>>Manutenzione Ordinaria</option>
            <option value="Manutenzione Straordinaria" <?= ($manutenzione['tipo_intervento']=='Manutenzione Straordinaria')?'selected':'' ?>>Manutenzione Straordinaria</option>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Data manutenzione</label>
        <input type="date" name="data_manutenzione" id="data_manutenzione" class="form-control" value="<?= $manutenzione['data_manutenzione'] ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Prossima manutenzione</label>
        <input type="date" name="prossima_manutenzione" id="prossima_manutenzione" class="form-control" value="<?= $manutenzione['prossima_manutenzione'] ?>" readonly>
    </div>

    <div class="mb-3">
        <label class="form-label">Ore attuali</label>
        <input type="number" name="ore_attuali" class="form-control" value="<?= $manutenzione['ore_attuali'] ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Descrizione</label>
        <textarea name="descrizione" class="form-control" rows="4"><?= htmlspecialchars($manutenzione['descrizione']) ?></textarea>
    </div>

    <div class="mb-3">
        <label class="form-label">Costo (€)</label>
        <input type="number" step="0.01" name="costo" class="form-control" value="<?= $manutenzione['costo'] ?>">
    </div>

    <div class="mb-3">
        <label class="form-label">Fornitore</label>
        <select name="fornitore" class="form-select">
            <option value="">-- Seleziona il fornitore --</option>
            <option value="SERVICAR" <?= ($manutenzione['fornitore']=='SERVICAR')?'selected':'' ?>>SERVICAR</option>
        </select>
    </div>

    <button type="submit" class="btn btn-primary">💾 Salva Modifiche</button>
</form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dataManut = document.getElementById('data_manutenzione');
    const prossimaManut = document.getElementById('prossima_manutenzione');
    const tipoIntervento = document.getElementById('tipo_intervento');

    function calcolaProssima() {
        if (dataManut.value && tipoIntervento.value === 'Manutenzione Ordinaria') {
            let data = new Date(dataManut.value);
            data.setMonth(data.getMonth() + 3);
            let month = (data.getMonth()+1).toString().padStart(2,'0');
            let day = data.getDate().toString().padStart(2,'0');
            prossimaManut.value = `${data.getFullYear()}-${month}-${day}`;
        } else {
            prossimaManut.value = '';
        }
    }

    dataManut.addEventListener('change', calcolaProssima);
    tipoIntervento.addEventListener('change', calcolaProssima);
});
</script>

</body>
</html>
