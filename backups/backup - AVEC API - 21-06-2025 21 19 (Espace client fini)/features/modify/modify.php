<?php
include '../../fonctions/db.php';
include '../../fonctions/fonctions.php';

$conn = getConnexion();

$table = $_GET['table'] ?? '';
$id = $_GET['id'] ?? 0;

if (!$table || !$id) {
    echo "<p>Paramètres manquants.</p>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $colonnes = [];
    $valeurs = [];
    foreach ($_POST as $col => $val) {
        $colonnes[] = "$col = :$col";
        $valeurs[":$col"] = $val;
    }
    $valeurs[':id'] = $id;
    $sql = "UPDATE $table SET " . implode(', ', $colonnes) . " WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute($valeurs);
    header("Location: ../backend.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM $table WHERE id = :id");
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();

$record = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$record) {
    echo "<p>Aucune donnée trouvée pour ID $id dans la table $table.</p>";
    exit;
}

function getDistinctValues($conn, $table, $column) {
    $columnsStmt = $conn->prepare("SHOW COLUMNS FROM $table");
    $columnsStmt->execute();
    $columns = array_column($columnsStmt->fetchAll(PDO::FETCH_ASSOC), 'Field');

    if (!in_array($column, $columns)) return [];

    $stmt = $conn->prepare("SELECT DISTINCT $column FROM $table WHERE $column IS NOT NULL");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

$statutOptions = getDistinctValues($conn, $table, 'statut');
$methodeOptions = getDistinctValues($conn, $table, 'methode');
?>

<!DOCTYPE html>
<html lang="fr" data-bs-theme="auto">
<head>
    <meta charset="UTF-8">
    <title>Modifier - <?php echo htmlspecialchars($table); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body class="d-flex flex-column min-vh-100">
<?php include '../../fonctions/header.php'; ?>
<div class="container py-5 flex-grow-1">
    <h1 class="mb-4">Modifier - Table <strong><?php echo htmlspecialchars($table); ?></strong></h1>

    <?php foreach ($record as $col => $val): ?>
        <?php if (in_array($col, ['id', 'id_client', 'id_livreur'])): ?>
            <p><strong><?php echo ucfirst($col); ?> :</strong> <?php echo htmlspecialchars($val); ?></p>
        <?php endif; ?>
    <?php endforeach; ?>

    <form action="" method="post">
        <?php foreach ($record as $col => $val): ?>
            <?php if (!in_array($col, ['id', 'id_client', 'id_livreur'])): ?>
                <div class="mb-3">
                    <label for="<?php echo $col; ?>" class="form-label"><?php echo ucfirst($col); ?></label>
                    <?php if ($col === 'statut' && count($statutOptions) > 0): ?>
                        <select class="form-select" id="<?php echo $col; ?>" name="<?php echo $col; ?>">
                            <?php foreach ($statutOptions as $option): ?>
                                <option value="<?php echo $option; ?>" <?php echo ($val === $option) ? 'selected' : ''; ?>><?php echo ucfirst($option); ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php elseif ($col === 'methode' && count($methodeOptions) > 0): ?>
                        <select class="form-select" id="<?php echo $col; ?>" name="<?php echo $col; ?>">
                            <?php foreach ($methodeOptions as $option): ?>
                                <option value="<?php echo $option; ?>" <?php echo ($val === $option) ? 'selected' : ''; ?>><?php echo ucfirst($option); ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <input type="text" class="form-control" id="<?php echo $col; ?>" name="<?php echo $col; ?>" value="<?php echo htmlspecialchars($val); ?>">
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
        <a href="javascript:history.back()" class="btn btn-secondary">Annuler</a>
    </form>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/darkmode.js"></script>
    <?php include '../../fonctions/footer.php'; ?>

</body>
</html>