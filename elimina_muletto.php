<?php
require 'config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // prima elimino le manutenzioni collegate
    $pdo->prepare("DELETE FROM manutenzione WHERE id_muletto = :id")->execute([':id' => $id]);

    // poi elimino il muletto
    $pdo->prepare("DELETE FROM anagrafica WHERE id = :id")->execute([':id' => $id]);
}

header("Location: view_muletti.php");
exit;
?>
