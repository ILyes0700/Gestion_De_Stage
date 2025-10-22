<?php
// Connexion à la base de données
try {
    $conn = new PDO("mysql:host=localhost;dbname=gestion_stagiaires;charset=utf8", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Récupération des paramètres
$type = $_GET['type'] ?? '';
$service = intval($_GET['service'] ?? 0);

// Sécurité : Vérifier le type
$type_allowed = ['Présent', 'Absent', 'Justifié'];
if (!in_array($type, $type_allowed)) {
    echo "Type invalide.";
    exit;
}

// Requête SQL
$sql = "SELECT e.nom, e.prenom, s.date_debut, s.date_fin, p.date
FROM presences p
JOIN stages s ON p.id_stage = s.id_stage
JOIN etudiants e ON s.id_etudiant = e.id_etudiant
WHERE s.id_service = :service AND p.etat = :etat
ORDER BY p.date DESC
";

// Préparation et exécution
$stmt = $conn->prepare($sql);
$stmt->execute([
    ':service' => $service,
    ':etat' => $type
]);

$stagiaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Affichage HTML (renvoyé via AJAX)
if (count($stagiaires) === 0) {
    echo "<p>Aucun stagiaire trouvé pour l'état <strong>$type</strong>.</p>";
} else {
    echo "<ul>";
    foreach ($stagiaires as $stagiaire) {
        echo "<li><strong>{$stagiaire['nom']} {$stagiaire['prenom']}</strong> – Présence le <em>{$stagiaire['date']}</em> (Stage: {$stagiaire['date_debut']} → {$stagiaire['date_fin']})</li>";
    }
    echo "</ul>";
}
?>
