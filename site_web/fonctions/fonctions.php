<?php
function afficherAvecLimite($conn, $table, $champs = '*', $limite = 5, $ordre = 'DESC', $where = '') {
    $sql = "SELECT $champs FROM $table";
    if (!empty($where)) {
        $sql .= " WHERE $where";
    }
    $sql .= " ORDER BY id $ordre LIMIT $limite";

    $stmt = $conn->query($sql);
    $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($resultats)) {
        echo "<p class='text-muted'>Aucune donnée trouvée.</p>";
        return;
    }

    echo "<table class='table table-striped'>";
    echo "<thead><tr>";
    foreach (array_keys($resultats[0]) as $col) {
        echo "<th>" . htmlspecialchars((string) $col) . "</th>";
    }
    echo "<th>Actions</th>"; // Colonne pour les boutons
    echo "</tr></thead><tbody>";

    foreach ($resultats as $row) {
        echo "<tr>";
        foreach ($row as $cell) {
            echo "<td>" . htmlspecialchars((string) $cell) . "</td>";
        }

        // ID utilisé pour les liens
        $id = $row['id'] ?? null;

        echo "<td>";
        if ($id !== null) {
            echo "<a href='modify/modify.php?table=$table&id=$id' class='btn btn-sm btn-info me-2'>Détails</a>";
            echo "<a href='modify/delete.php?table=$table&id=$id' class='btn btn-sm btn-danger' onclick=\"return confirm('Confirmer la suppression ?')\">Supprimer</a>";
        } else {
            echo "<span class='text-muted'>Aucune action</span>";
        }
        echo "</td>";

        echo "</tr>";
    }

    echo "</tbody></table>";
}
