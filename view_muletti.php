<?php
require 'auth_check.php';
require 'config.php';

// --- Filtro a cascata ---
$campo = $_GET['campoFiltro'] ?? '';
$valore = $_GET['valoreFiltro'] ?? '';

// --- Ricerca libera ---
$ricercaLibera = $_GET['ricercaLibera'] ?? '';

$conditions = [];
$params = [];

// Filtro a cascata
if ($campo && $valore) {
    $conditions[] = "a.$campo = ?";
    $params[] = $valore;
}

// Ricerca libera (LIKE su matricola, modello, marca, tipo)
if (!empty($ricercaLibera)) {
    $conditions[] = "(a.matricola LIKE ? OR a.modello LIKE ? OR a.marca LIKE ? OR a.tipo LIKE ?)";
    $ricercaParam = '%' . $ricercaLibera . '%';
    $params[] = $ricercaParam;
    $params[] = $ricercaParam;
    $params[] = $ricercaParam;
    $params[] = $ricercaParam;
}

// --- Query SQL ---
$sql = "SELECT 
    a.id,
    a.matricola,
    a.marca,
    a.modello,
    a.tipo,
    a.tipo_possesso,
    a.stato,
    a.filiale,
    MAX(m.ore_attuali) AS ore_attuali,
    MAX(m.prossima_manutenzione) AS prossima_manutenzione,
    SUM(m.costo) AS costo_totale,
    COUNT(m.id) AS numero_interventi
FROM anagrafica a
LEFT JOIN manutenzione m ON a.id = m.id_muletto";

// Aggiungi condizioni se presenti
if ($conditions) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " GROUP BY a.id, a.matricola, a.marca, a.modello, a.tipo, a.tipo_possesso, a.stato, a.filiale
ORDER BY a.matricola";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$muletti = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Elenco Muletti</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Font personalizzato -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/view.css">
</head>
<body>

<!-- 🌐 Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark shadow-sm fixed-top custom-navbar">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center" href="view_dashboard.php">
      <img src="img/LOGO-GOLOGISTICS.png" alt="Logo" width="100" height="60" class="me-2 rounded">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'view_dashboard.php' ? 'active' : '' ?>" href="view_dashboard.php">📊 Dashboard</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'insert_anagrafica.php' ? 'active' : '' ?>" href="insert_anagrafica.php">➕ Inserisci Muletto</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'view_muletti.php' ? 'active' : '' ?>" href="view_muletti.php">🚜 Elenco Muletti</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-3 pt-3">

<?php
if (!empty($_SESSION['msg_success'])):
    $msg = $_SESSION['msg_success'];
    unset($_SESSION['msg_success']);
?>
<div id="flash-msg" class="flash-msg">
    <?= htmlspecialchars($msg) ?>
</div>
<script>
window.addEventListener('DOMContentLoaded', () => {
    const flash = document.getElementById('flash-msg');
    if (flash) {
        setTimeout(() => {
            flash.style.opacity = '0';
            setTimeout(() => flash.remove(), 500);
        }, 10000);
    }
});
</script>
<?php endif; ?>

<!-- ===== Filtro a cascata ===== -->
<form method="get" class="filtro-cascata mb-4 text-start">
  
  <!-- 🔍 Ricerca libera -->
  <div class="campo-libero">
    <label for="ricercaLibera" class="form-label mb-1">Ricerca libera</label>
    <input type="text" id="ricercaLibera" name="ricercaLibera" 
           class="form-control" placeholder="" 
           value="<?= htmlspecialchars($_GET['ricercaLibera'] ?? '') ?>">
  </div>

  <!-- 🧩 Campo filtro -->
  <div class="campo-filtro">
    <label for="campoFiltro" class="form-label mb-1">Campo</label>
    <select id="campoFiltro" name="campoFiltro" class="form-select">
      <option value="">-- Scegli un campo --</option>
      <option value="stato" <?= $campo === 'stato' ? 'selected' : '' ?>>Stato</option>
      <option value="filiale" <?= $campo === 'filiale' ? 'selected' : '' ?>>Filiale</option>
      <option value="marca" <?= $campo === 'marca' ? 'selected' : '' ?>>Marca</option>
      <option value="tipo" <?= $campo === 'tipo' ? 'selected' : '' ?>>Tipo</option>
      <option value="tipo_possesso" <?= $campo === 'tipo_possesso' ? 'selected' : '' ?>>Tipo Possesso</option>
    </select>
  </div>

  <!-- 🎯 Valore filtro -->
  <div class="valore-filtro" id="valoreFiltroDiv" style="display: <?= $campo ? 'block' : 'none' ?>;">
    <label for="valoreFiltro" class="form-label mb-1">Valore</label>
    <select name="valoreFiltro" id="valoreFiltro" class="form-select">
      <option value="">-- Seleziona un valore --</option>
    </select>
  </div>

  <!-- 🧠 Bottoni -->
  <div class="bottoni-filtro">
    <button type="submit" class="btn btn-primary me-2">🔍 Filtra</button>
  </div>
</form>


<script>
const campoFiltro = document.getElementById('campoFiltro');
const valoreDiv = document.getElementById('valoreFiltroDiv');
const valoreSelect = document.getElementById('valoreFiltro');

const opzioni = {
    stato: ['Attivo','In Riparazione','Rottamato'],
    filiale: ['Casalnuovo','Volla','Roma','Misterbianco'],
    marca: ['OM','Still','Fiorentini'],
    tipo: ['Frontale','Retrattile','Transpallet','Lavasciuga'],
    tipo_possesso: ['Proprietà','Noleggio']
};

// Popola select se già selezionato
const campoSelezionato = "<?= $campo ?>";
const valoreSelezionato = "<?= $valore ?>";
if(campoSelezionato) {
    valoreSelect.innerHTML = '<option value="">-- Seleziona un valore --</option>';
    opzioni[campoSelezionato].forEach(v => {
        const option = document.createElement('option');
        option.value = v;
        option.textContent = v;
        if(v === valoreSelezionato) option.selected = true;
        valoreSelect.appendChild(option);
    });
}

campoFiltro.addEventListener('change', () => {
    const campo = campoFiltro.value;
    valoreSelect.innerHTML = '';
    if(campo) {
        valoreDiv.style.display = 'block';
        valoreSelect.innerHTML = '<option value="">-- Seleziona un valore --</option>';
        opzioni[campo].forEach(v => {
            const option = document.createElement('option');
            option.value = v;
            option.textContent = v;
            valoreSelect.appendChild(option);
        });
    } else {
        valoreDiv.style.display = 'none';
    }
});
</script>

<!-- ===== Tabella Muletti ===== -->
<table class="table table-dark align-middle text-center table-bordered">
  <thead>
    <tr>
      <th>Matricola</th>
      <th>Marca</th>
      <th>Modello</th>
      <th>Ore</th>
      <th>Tipo</th>
      <th>Possesso</th>
      <th>Stato</th>
      <th>Filiale</th>
      <th>Prox.Manutenzione</th>
      <th>Totale</th>
      <th>Interventi</th>
      <th>Manutenzione</th>
      <th>Modifica</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach($muletti as $muletto): ?>
    <?php
      $oggi = new DateTime();
      $classeRiga = '';
      if ($muletto['stato'] === 'Rottamato') {
          $classeRiga = 'table-secondary';
      } elseif(!empty($muletto['prossima_manutenzione'])) {
          $diff = (int)$oggi->diff(new DateTime($muletto['prossima_manutenzione']))->format('%r%a');
          if($diff < 0) $classeRiga='table-danger';
          elseif($diff <=15) $classeRiga='table-warning';
      }
    ?>
    <tr class="<?= $classeRiga ?>">
      <td><?= htmlspecialchars($muletto['matricola']) ?></td>
      <td><?= htmlspecialchars($muletto['marca']) ?></td>
      <td><?= htmlspecialchars($muletto['modello']) ?></td>
      <td><?= $muletto['ore_attuali'] ?: '-' ?></td>
      <td><?= htmlspecialchars($muletto['tipo']) ?></td>
      <td><?= htmlspecialchars($muletto['tipo_possesso']) ?></td>
      <td><?= htmlspecialchars($muletto['stato']) ?></td>
      <td><?= htmlspecialchars($muletto['filiale']) ?></td>
      <td><?= $muletto['stato'] !== 'Rottamato' ? ($muletto['prossima_manutenzione'] ?: '-') : '-' ?></td>
      <td><?= $muletto['costo_totale'] ? number_format($muletto['costo_totale'],2) : '0.00' ?></td>
      <td><?= $muletto['numero_interventi'] ?></td>
      <td><a href="view_manutenzione.php?id_muletto=<?= $muletto['id'] ?>" class="btn btn-warning btn-sm">🔧🔧</a></td>
      <td><a href="edit_anagrafica.php?id=<?= $muletto['id'] ?>" class="btn btn-primary btn-sm">✏️</a></td>
  <?php endforeach; ?>
  </tbody>
</table>

</div>
</body>
</html>


