<?php
require 'auth_check.php';
require 'config.php';

$id_muletto = isset($_GET['id_muletto']) ? (int)$_GET['id_muletto'] : 0;

if ($id_muletto) {
    $sql = "SELECT
          m.id, 
          a.matricola, 
          m.data_manutenzione, 
          m.prossima_manutenzione, 
          m.ore_attuali, 
          m.numero_rapportino, 
          m.tipo_intervento, 
          m.costo, 
          m.fornitore
    FROM manutenzione m
    INNER JOIN anagrafica a ON a.id = m.id_muletto
    WHERE a.id = :id_muletto
    ORDER BY m.data_manutenzione DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_muletto' => $id_muletto]);

} else {
    // Nessun filtro, prende tutte le manutenzioni
    $sql = "SELECT
          m.id, 
          a.matricola, 
          m.data_manutenzione, 
          m.prossima_manutenzione, 
          m.ore_attuali, 
          m.numero_rapportino, 
          m.tipo_intervento, 
          m.costo, 
          m.fornitore
    FROM manutenzione m
    INNER JOIN anagrafica a ON a.id = m.id_muletto
    ORDER BY m.data_manutenzione DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(); // nessun parametro
}

$muletti = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Elenco Manutenzione</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font personalizzato -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/view.css">
</head>
<body>
<!-- 🌐 Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark shadow-sm fixed-top custom-navbar">
  <div class="container-fluid">
    <!-- Logo -->
    <a class="navbar-brand d-flex align-items-center" href="view_dashboard.php">
      <img src="img/LOGO-GOLOGISTICS.png" alt="Logo" width="100" height="60" class="me-2 rounded">
    </a>

    <!-- Bottone responsive -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
      aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Link menu -->
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'view_dashboard.php' ? 'active' : '' ?>" href="view_dashboard.php">
        📊 Dashboard
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'insert_manutenzione.php?id_muletto=<?= $id_muletto ?>' ? 'active' : '' ?>" href="insert_manutenzione.php?id_muletto=<?= $id_muletto ?>">
       ➕ Effettua Manutenzione
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'view_muletti.php' ? 'active' : '' ?>" href="view_muletti.php">
        🚜 Elenco Muletti
      </a>
    </li>
  </ul>
</div>
</ul>
</div>
</div>
</nav>

<div class="container mt-3 pt-3">
<?php
if (!empty($_SESSION['msg_success'])):
    $msg = $_SESSION['msg_success'];
    unset($_SESSION['msg_success']); // rimuove subito
?>
<div id="flash-msg" class="flash-msg">
    <?= htmlspecialchars($msg) ?>
</div>
<script>
window.addEventListener('DOMContentLoaded', (event) => {
    const flash = document.getElementById('flash-msg');
    if (flash) {
        setTimeout(() => {
            flash.style.opacity = '0';
            setTimeout(() => flash.remove(), 500);
        }, 10000); // 10 secondi
    }
});
</script>
<?php endif; ?>

  <table class="table table-dark align-middle text-center table-bordered">
    <thead>
      <tr>
        <th>Matricola</th>
        <th>DataManutenzione</th>
        <th>Prox.Manutenzione</th>
        <th>Ore</th>
        <th>Rapportino</th>
        <th>Intervento</th>
        <th>Costo</th>
        <th>Fornitore</th>
        <th>Modifica</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($muletti as $muletto): ?>
        <?php
          $oggi = new DateTime();
          $classeRiga = '';

          if (!empty($muletto['prossima_manutenzione'])) {
              $dataManut = new DateTime($muletto['prossima_manutenzione']);
              $diff = (int)$oggi->diff($dataManut)->format('%r%a'); // giorni di differenza (+/-)
              
              if ($diff < 0) {
                  // manutenzione scaduta
                  $classeRiga = 'table-danger';
              } elseif ($diff <= 15) {
                  // entro 15 giorni
                  $classeRiga = 'table-warning';
              }
          }
        ?>
        <tr class="<?= $classeRiga ?>">
        <td><?= htmlspecialchars($muletto['matricola']) ?></td>
        <td><?= $muletto['data_manutenzione'] ?: '-' ?></td>
        <td><?= $muletto['prossima_manutenzione'] ?: '-' ?></td>
        <td><?= $muletto['ore_attuali'] ?: '-' ?></td>
        <td><?= $muletto['numero_rapportino'] ?: '-' ?></td>
        <td><?= htmlspecialchars($muletto['tipo_intervento']) ?></td>
        <td><?= $muletto['costo'] ? number_format($muletto['costo'], 2) : '0.00' ?></td>
        <td><?= htmlspecialchars($muletto['fornitore']) ?></td>
        <td>
        <a href="edit_manutenzione.php?id=<?= $muletto['id'] ?>" class="btn btn-warning btn-sm">
        ✏️
        </a>
        </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php if (empty($muletti)): ?>
    <tr><td colspan="8">Nessuna manutenzione trovata</td></tr>
<?php endif; ?>
</div>
</body>
</html>