<?php
//Afficher les données des tables avec les pastilles de statut et les boutons d'action
function afficherAvecLimite($conn, $table, $colonnes, $limite, $ordre = 'ASC') {
    $stmt = $conn->prepare("SELECT $colonnes FROM $table ORDER BY id $ordre LIMIT :limite");
    $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
    $stmt->execute();

    $columns = array_filter(explode(',', str_replace(' ', '', $colonnes)), fn($col) => strtolower($col) !== 'id');

    echo "<table class='table table-striped'><thead><tr>";
    foreach ($columns as $col) echo "<th>" . ucfirst($col) . "</th>";
    echo "<th>Actions</th></tr></thead><tbody>";

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        foreach ($columns as $col) {
            if ($col === 'statut') {
                $status = strtolower($row[$col]);
                $icon = in_array($status, ['terminé', 'effectué', 'livrée', 'disponible']) ? '🟢' :
                        (in_array($status, ['en cours', 'en attente', 'en livraison']) ? '🗾' : '🔴');
                echo "<td>$icon {$row[$col]}</td>";
            } else {
                echo "<td>{$row[$col]}</td>";
            }
        }
        echo "<td>
            <a href='../features/modify/modify.php?table=$table&id={$row['id']}' class='btn btn-primary btn-sm'>Détails</a>
            <a href='../features/modify/delete.php?table=$table&id={$row['id']}' class='btn btn-danger btn-sm ms-2' onclick=\"return confirm('Voulez-vous vraiment supprimer cet élément ?');\">Supprimer</a>
        </td></tr>";
    }
    echo "</tbody></table>";
}
