<?php
require 'auth_check.php';
require 'config.php';  // connessione PDO

// 1. Muletti per filiale
$stmt = $pdo->query("SELECT filiale, COUNT(*) as cnt FROM anagrafica GROUP BY filiale");
$mulettiFiliali = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Muletti per marca
$stmt = $pdo->query("SELECT marca, COUNT(*) as cnt FROM anagrafica GROUP BY marca");
$mulettiMarca = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Muletti per stato
$stmt = $pdo->query("SELECT stato, COUNT(*) as cnt FROM anagrafica GROUP BY stato");
$mulettiStato = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stati = array_column($mulettiStato, 'stato');
$conteggiStati = array_column($mulettiStato, 'cnt');

// 4. Muletti per tipo possesso
$stmt = $pdo->query("SELECT tipo_possesso, COUNT(*) as cnt FROM anagrafica GROUP BY tipo_possesso");
$mulettiTipoPossesso = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 5. Totale speso manutenzione per mese
$stmt = $pdo->query("
    SELECT DATE_FORMAT(data_manutenzione, '%Y-%m') as mese, 
           SUM(costo) as totale 
    FROM manutenzione 
    GROUP BY mese 
    ORDER BY mese
");
$spesaManutenzione = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Converte YYYY-MM in 'Gennaio 2025', ecc.
$mesiItaliani = [
    '01' => 'Gennaio', '02' => 'Febbraio', '03' => 'Marzo',
    '04' => 'Aprile', '05' => 'Maggio', '06' => 'Giugno',
    '07' => 'Luglio', '08' => 'Agosto', '09' => 'Settembre',
    '10' => 'Ottobre', '11' => 'Novembre', '12' => 'Dicembre'
];

foreach ($spesaManutenzione as &$riga) {
    [$anno, $meseNumero] = explode('-', $riga['mese']);
    $riga['mese'] = $mesiItaliani[$meseNumero] . ' ' . $anno;
}
unset($riga); // buona pratica
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
    <!-- Font personalizzato -->
     <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <title>Dashboard Muletti</title>
</head>
<body>

<nav class="navbar navbar-expand-lg custom-navbar">
  <div class="container-fluid">
    <!-- Logo + Titolo a sinistra -->
    <a class="navbar-brand d-flex align-items-center" href="view_dashboard.php">
      <img src="img/LOGO-GOLOGISTICS.png" alt="Logo" width="100" height="60" class="me-2 rounded">
    </a>

    <!-- Bottone responsive -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
      aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Link a destra -->
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


<div class="container mt-3 pt-3">
  <div class="row">
    <div class="col-md-6 grafico-card">
      <canvas id="graficoFiliali"></canvas>
    </div>
    <div class="col-md-6 grafico-card">
      <canvas id="graficoMarca"></canvas>
    </div>
    <div class="col-md-6 grafico-card">
      <canvas id="graficoStato"></canvas>
    </div>
    <div class="col-md-6 grafico-card">
      <canvas id="graficoTipoPossesso"></canvas>
    </div>
    <div class="col-md-6 grafico-card">
      <canvas id="graficoSpesaManutenzione"></canvas>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// --- Muletti per filiali ---
new Chart(document.getElementById('graficoFiliali'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($mulettiFiliali,'filiale')) ?>,
        datasets: [{
            label: 'Numero Muletti',
            data: <?= json_encode(array_column($mulettiFiliali,'cnt')) ?>,
            backgroundColor: '#00bcd4'
        }]
    },
    options: { responsive: true }
});

// --- Muletti per marca ---
new Chart(document.getElementById('graficoMarca'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($mulettiMarca,'marca')) ?>,
        datasets: [{
            label: 'Numero Muletti',
            data: <?= json_encode(array_column($mulettiMarca,'cnt')) ?>,
            backgroundColor: '#ffb300'
        }]
    },
    options: { responsive: true }
});

// --- Muletti per stato ---
new Chart(document.getElementById('graficoStato'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($stati) ?>,
        datasets: [{
            label: 'Numero Muletti',
            data: <?= json_encode($conteggiStati) ?>,
            backgroundColor: ['#00bfa5', '#ffb300', '#e53935'] // verde, giallo, rosso
        }]
    },
    options: { responsive: true }
});

// --- Muletti per tipo possesso ---
new Chart(document.getElementById('graficoTipoPossesso'), {
    type: 'pie',
    data: {
        labels: <?= json_encode(array_column($mulettiTipoPossesso,'tipo_possesso')) ?>,
        datasets: [{
            data: <?= json_encode(array_column($mulettiTipoPossesso,'cnt')) ?>,
            backgroundColor: ['#00bcd4','#ffb300']
        }]
    },
    options: { responsive: true }
});

// --- Totale speso manutenzione per mese ---
new Chart(document.getElementById('graficoSpesaManutenzione'), {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($spesaManutenzione,'mese')) ?>,
        datasets: [{
            label: 'Totale manutenzione (€)',
            data: <?= json_encode(array_column($spesaManutenzione,'totale')) ?>,
            borderColor: '#e53935',
            backgroundColor: 'rgba(229,57,53,0.2)',
            fill: true
        }]
    },
    options: { responsive: true }
});
</script>
</body>
</html>

