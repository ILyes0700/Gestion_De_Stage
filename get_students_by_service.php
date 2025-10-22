<?php
session_start();
require_once 'config.php'; // Fichier avec votre connexion PDO

if(isset($_GET['service_id']) && isset($_GET['etat'])) {
    $serviceId = $_GET['service_id'];
    $etat = $_GET['etat'];
    
    $stmt = $conn->prepare("
        SELECT e.id_etudiant, e.nom, e.prenom, s.date_debut, s.date_fin
        FROM stages s
        JOIN etudiants e ON s.id_etudiant = e.id_etudiant
        WHERE s.id_service = ? AND s.etat = ?
    ");
    $stmt->execute([$serviceId, $etat]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if(count($students) > 0) {
        foreach($students as $student) {
            echo '<div class="student-item">';
            echo '<div>';
            echo '<strong>' . htmlspecialchars($student['prenom'] . ' ' . $student['nom']) . '</strong><br>';
            echo 'Du ' . date('d/m/Y', strtotime($student['date_debut'])) . ' au ' . date('d/m/Y', strtotime($student['date_fin']));
            echo '</div>';
            echo '<button class="view-btn" data-id="' . $student['id_etudiant'] . '">Voir</button>';
            echo '</div>';
        }
    } else {
        echo '<p>Aucun étudiant trouvé</p>';
    }
}
?>