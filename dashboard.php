<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$conn = new PDO("mysql:host=localhost;dbname=gestion_stagiaires;charset=utf8", "root", "");

// Récupérer le rôle et le service de l'utilisateur
$role = $_SESSION['role'];
$id_service = $_SESSION['id_service'];

// Statistiques générales (différentes selon le rôle)
$stats = [
    'etudiants' => 0,
    'stages_encours' => 0,
    'stages_termines' => 0,
    'documents' => 0,
    'presences' => 0,
    'evaluations' => 0
];
$date_actuelle = date('Y-m-d');

$update = $conn->prepare("
    UPDATE stages 
    SET etat = 'Terminé'
    WHERE date_fin < ? AND etat != 'Terminé'
");
$update->execute([$date_actuelle]);
if ($role === 'admin'or $role == 'admin_super') {
    // Admin voit tout
    $stats['etudiants'] = $conn->query("SELECT COUNT(*) FROM etudiants")->fetchColumn();
    $stats['stages_encours'] = $conn->query("SELECT COUNT(*) FROM stages WHERE etat = 'En cours'")->fetchColumn();
    $stats['stages_termines'] = $conn->query("SELECT COUNT(*) FROM stages WHERE etat = 'Terminé'")->fetchColumn();
    $stats['documents'] = $conn->query("SELECT COUNT(*) FROM documents")->fetchColumn();
    $stats['presences'] = $conn->query("SELECT COUNT(*) FROM presences")->fetchColumn();
    $stats['evaluations'] = $conn->query("SELECT COUNT(*) FROM evaluations")->fetchColumn();
} else {
    // Les autres voient seulement leur service
    $stats['etudiants'] = $conn->query("SELECT COUNT(*) FROM etudiants e JOIN stages s ON e.id_etudiant = s.id_etudiant WHERE s.id_service = $id_service")->fetchColumn();
    $stats['stages_encours'] = $conn->query("SELECT COUNT(*) FROM stages WHERE etat = 'En cours' AND id_service = $id_service")->fetchColumn();
    $stats['stages_termines'] = $conn->query("SELECT COUNT(*) FROM stages WHERE etat = 'Terminé' AND id_service = $id_service")->fetchColumn();
    $stats['documents'] = $conn->query("SELECT COUNT(*) FROM documents d JOIN stages s ON d.id_etudiant = s.id_etudiant WHERE s.id_service = $id_service")->fetchColumn();
    $stats['presences'] = $conn->query("SELECT COUNT(*) FROM presences p JOIN stages s ON p.id_stage = s.id_stage WHERE s.id_service = $id_service")->fetchColumn();
    $stats['evaluations'] = $conn->query("SELECT COUNT(*) FROM evaluations e JOIN stages s ON e.id_stage = s.id_stage WHERE s.id_service = $id_service")->fetchColumn();
}

$date = Date("Y-m-d");

// Statistiques par service (différentes selon le rôle)
if ($role == 'admin' or $role == 'admin_super') {
    $services_stats = $conn->query("
        SELECT 
            s.id_service, 
            s.nom_service,
            COUNT(st.id_stage) AS total_stagiaires,
            SUM(CASE WHEN st.etat = 'En cours' THEN 1 ELSE 0 END) AS stages_encours,
            SUM(CASE WHEN st.etat = 'Terminé' THEN 1 ELSE 0 END) AS stages_termines,
            (
                SELECT COUNT(*) 
                FROM presences p 
                JOIN stages st2 ON p.id_stage = st2.id_stage 
                WHERE st2.id_service = s.id_service 
                AND st2.etat = 'En cours' 
                AND p.etat = 'Présent'
                AND p.date = '$date'
            ) AS presences_present,
            (
                SELECT COUNT(*) 
                FROM presences p 
                JOIN stages st2 ON p.id_stage = st2.id_stage 
                WHERE st2.id_service = s.id_service 
                AND st2.etat = 'En cours' 
                AND p.etat = 'Absent'
                AND p.date = '$date'
            ) AS presences_absent,
            (
                SELECT COUNT(*) 
                FROM presences p 
                JOIN stages st2 ON p.id_stage = st2.id_stage 
                WHERE st2.id_service = s.id_service 
                AND st2.etat = 'En cours' 
                AND p.etat = 'Justifié'
                AND p.date = '$date'
            ) AS presences_justifie
        FROM services s
        LEFT JOIN stages st ON s.id_service = st.id_service
        GROUP BY s.id_service, s.nom_service
    ")->fetchAll(PDO::FETCH_ASSOC);
} else {
    $services_stats = $conn->query("
        SELECT 
            s.id_service, 
            s.nom_service,
            COUNT(st.id_stage) AS total_stagiaires,
            SUM(CASE WHEN st.etat = 'En cours' THEN 1 ELSE 0 END) AS stages_encours,
            SUM(CASE WHEN st.etat = 'Terminé' THEN 1 ELSE 0 END) AS stages_termines,
            (
                SELECT COUNT(*) 
                FROM presences p 
                JOIN stages st2 ON p.id_stage = st2.id_stage 
                WHERE st2.id_service = s.id_service 
                AND st2.etat = 'En cours' 
                AND p.etat = 'Présent'
                AND p.date = '$date'
            ) AS presences_present,
            (
                SELECT COUNT(*) 
                FROM presences p 
                JOIN stages st2 ON p.id_stage = st2.id_stage 
                WHERE st2.id_service = s.id_service 
                AND st2.etat = 'En cours' 
                AND p.etat = 'Absent'
                AND p.date = '$date'
            ) AS presences_absent,
            (
                SELECT COUNT(*) 
                FROM presences p 
                JOIN stages st2 ON p.id_stage = st2.id_stage 
                WHERE st2.id_service = s.id_service 
                AND st2.etat = 'En cours' 
                AND p.etat = 'Justifié'
                AND p.date = '$date'
            ) AS presences_justifie
        FROM services s
        LEFT JOIN stages st ON s.id_service = st.id_service
        WHERE s.id_service = $id_service
        GROUP BY s.id_service, s.nom_service
    ")->fetchAll(PDO::FETCH_ASSOC);
}

foreach ($services_stats as &$service) {
    $service['presence_percent'] = $service['stages_encours'] > 0 
        ? round(($service['presences_present'] / $service['stages_encours']) * 100) 
        : 0;
}
unset($service);

// Stages par année (différent selon le rôle)
if ($role == 'admin' or $role == 'admin_super') {
    $stages_par_annee = $conn->query("
        SELECT 
            YEAR(date_debut) AS annee,
            COUNT(*) AS total,
            SUM(CASE WHEN etat = 'En cours' THEN 1 ELSE 0 END) AS encours,
            SUM(CASE WHEN etat = 'Terminé' THEN 1 ELSE 0 END) AS termines
        FROM stages
        GROUP BY annee
        ORDER BY annee DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stages_par_annee = $conn->query("
        SELECT 
            YEAR(date_debut) AS annee,
            COUNT(*) AS total,
            SUM(CASE WHEN etat = 'En cours' THEN 1 ELSE 0 END) AS encours,
            SUM(CASE WHEN etat = 'Terminé' THEN 1 ELSE 0 END) AS termines
        FROM stages
        WHERE id_service = $id_service
        GROUP BY annee
        ORDER BY annee DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
}

// Derniers étudiants (différent selon le rôle)
if ($role == 'admin' or $role == 'admin_super') {
    $derniers_etudiants = $conn->query("SELECT * FROM etudiants ORDER BY id_etudiant DESC LIMIT 5")->fetchAll();
} else {
    $derniers_etudiants = $conn->query("
        SELECT e.* 
        FROM etudiants e
        JOIN stages s ON e.id_etudiant = s.id_etudiant
        WHERE s.id_service = $id_service
        ORDER BY e.id_etudiant DESC 
        LIMIT 5
    ")->fetchAll();
}

// Dernières modifications (commun à tous)
$derniers_Modifi = $conn->query("SELECT u.username,id_log,action,date_action from logs l,utilisateurs u where l.id_user=u.id_user ORDER BY id_log DESC LIMIT 6")->fetchAll();

// Stages récents (différent selon le rôle)
if ($role == 'admin' or $role == 'admin_super') {
    $stages_recents = $conn->query("
        SELECT s.*, e.nom, e.prenom, ser.nom_service 
        FROM stages s 
        JOIN etudiants e ON s.id_etudiant = e.id_etudiant 
        JOIN services ser ON s.id_service = ser.id_service 
        WHERE s.etat = 'En cours'
        ORDER BY s.id_stage DESC 
        LIMIT 5
    ")->fetchAll();
} else {
    $stages_recents = $conn->query("
        SELECT s.*, e.nom, e.prenom, ser.nom_service 
        FROM stages s 
        JOIN etudiants e ON s.id_etudiant = e.id_etudiant 
        JOIN services ser ON s.id_service = ser.id_service 
        WHERE s.etat = 'En cours'
        AND s.id_service = $id_service
        ORDER BY s.id_stage DESC 
        LIMIT 5
    ")->fetchAll();
}

// Documents récents (différent selon le rôle)
if ($role == 'admin' or $role == 'admin_super') {
    $documents_recents = $conn->query("
        SELECT d.*, e.nom, e.prenom 
        FROM documents d 
        JOIN etudiants e ON d.id_etudiant = e.id_etudiant 
        ORDER BY d.date_ajout DESC 
        LIMIT 5
    ")->fetchAll();
} else {
    $documents_recents = $conn->query("
        SELECT d.*, e.nom, e.prenom 
        FROM documents d 
        JOIN etudiants e ON d.id_etudiant = e.id_etudiant 
        JOIN stages s ON e.id_etudiant = s.id_etudiant
        WHERE s.id_service = $id_service
        ORDER BY d.date_ajout DESC 
        LIMIT 5
    ")->fetchAll();
}

// Présences récentes (différent selon le rôle)
if ($role == 'admin' or $role == 'admin_super') {
    $presences_recentes = $conn->query("
        SELECT p.*, e.nom, e.prenom, s.date_debut, s.date_fin 
        FROM presences p 
        JOIN stages s ON p.id_stage = s.id_stage 
        JOIN etudiants e ON s.id_etudiant = e.id_etudiant 
        WHERE s.etat = 'En cours'
        AND p.date='$date'
        ORDER BY p.date DESC 
        LIMIT 5
    ")->fetchAll();
} else {
    $presences_recentes = $conn->query("
        SELECT p.*, e.nom, e.prenom, s.date_debut, s.date_fin 
        FROM presences p 
        JOIN stages s ON p.id_stage = s.id_stage 
        JOIN etudiants e ON s.id_etudiant = e.id_etudiant 
        WHERE s.etat = 'En cours'
        AND p.date='$date'
        AND s.id_service = $id_service
        ORDER BY p.date DESC 
        LIMIT 5
    ")->fetchAll();
}

// Stages par mois (différent selon le rôle)
if ($role == 'admin' or $role == 'admin_super') {
    $stages_par_mois = $conn->query("
        SELECT 
            DATE_FORMAT(date_debut, '%Y-%m') AS mois, 
            COUNT(*) AS nombre
        FROM stages
        WHERE YEAR(date_debut) = YEAR(CURRENT_DATE)
        GROUP BY mois
        ORDER BY mois
    ")->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stages_par_mois = $conn->query("
        SELECT 
            DATE_FORMAT(date_debut, '%Y-%m') AS mois, 
            COUNT(*) AS nombre
        FROM stages
        WHERE YEAR(date_debut) = YEAR(CURRENT_DATE)
        AND id_service = $id_service
        GROUP BY mois
        ORDER BY mois
    ")->fetchAll(PDO::FETCH_ASSOC);
}

$mois_labels = [];
$mois_data_encours = [];
$mois_data_termines = [];

foreach ($stages_par_mois as $row) {
    $mois_labels[] = date("M", strtotime($row['mois'] . '-01'));
    if ($role === 'admin') {
        $encours = $conn->query("
            SELECT COUNT(*) 
            FROM stages 
            WHERE DATE_FORMAT(date_debut, '%Y-%m') = '{$row['mois']}' 
            AND etat = 'En cours'
        ")->fetchColumn();
        $termines = $conn->query("
            SELECT COUNT(*) 
            FROM stages 
            WHERE DATE_FORMAT(date_debut, '%Y-%m') = '{$row['mois']}' 
            AND etat = 'Terminé'
        ")->fetchColumn();
    } else {
        $encours = $conn->query("
            SELECT COUNT(*) 
            FROM stages 
            WHERE DATE_FORMAT(date_debut, '%Y-%m') = '{$row['mois']}' 
            AND etat = 'En cours'
            AND id_service = $id_service
        ")->fetchColumn();
        $termines = $conn->query("
            SELECT COUNT(*) 
            FROM stages 
            WHERE DATE_FORMAT(date_debut, '%Y-%m') = '{$row['mois']}' 
            AND etat = 'Terminé'
            AND id_service = $id_service
        ")->fetchColumn();
    }
    $mois_data_encours[] = $encours;
    $mois_data_termines[] = $termines;
}

// Fonction pour récupérer les étudiants selon le filtre
function getFilteredStudents($conn, $filterType, $serviceId, $date, $role) {
    $query = "";
    
    switch($filterType) {
        case 'total':
            if ($role == 'admin' or $role == 'admin_super' ) {
                $query = "SELECT e.*, s.date_debut, s.date_fin 
                         FROM etudiants e 
                         JOIN stages s ON e.id_etudiant = s.id_etudiant 
                         WHERE s.id_service = :serviceId";
            } else {
                $query = "SELECT e.*, s.date_debut, s.date_fin 
                         FROM etudiants e 
                         JOIN stages s ON e.id_etudiant = s.id_etudiant 
                         WHERE s.id_service = :serviceId";
            }
            break;
        case 'encours':
            $query = "SELECT e.*, s.date_debut, s.date_fin 
                     FROM etudiants e 
                     JOIN stages s ON e.id_etudiant = s.id_etudiant 
                     WHERE s.id_service = :serviceId AND s.etat = 'En cours'";
            break;
        case 'termines':
            $query = "SELECT e.*, s.date_debut, s.date_fin 
                     FROM etudiants e 
                     JOIN stages s ON e.id_etudiant = s.id_etudiant 
                     WHERE s.id_service = :serviceId AND s.etat = 'Terminé'";
            break;
        case 'present':
            $query = "SELECT e.*, s.date_debut, s.date_fin 
                     FROM etudiants e 
                     JOIN stages s ON e.id_etudiant = s.id_etudiant 
                     JOIN presences p ON s.id_stage = p.id_stage 
                     WHERE s.id_service = :serviceId AND s.etat = 'En cours' 
                     AND p.etat = 'Présent' AND p.date = :date";
            break;
        case 'absent':
            $query = "SELECT e.*, s.date_debut, s.date_fin 
                     FROM etudiants e 
                     JOIN stages s ON e.id_etudiant = s.id_etudiant 
                     JOIN presences p ON s.id_stage = p.id_stage 
                     WHERE s.id_service = :serviceId AND s.etat = 'En cours' 
                     AND p.etat = 'Absent' AND p.date = :date";
            break;
        case 'justifie':
            $query = "SELECT e.*, s.date_debut, s.date_fin 
                     FROM etudiants e 
                     JOIN stages s ON e.id_etudiant = s.id_etudiant 
                     JOIN presences p ON s.id_stage = p.id_stage 
                     WHERE s.id_service = :serviceId AND s.etat = 'En cours' 
                     AND p.etat = 'Justifié' AND p.date = :date";
            break;
    }
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':serviceId', $serviceId);
    if(in_array($filterType, ['present', 'absent', 'justifie'])) {
        $stmt->bindParam(':date', $date);
    }
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
    
}

// Vérifier si une requête AJAX a été envoyée pour obtenir les étudiants filtrés
if(isset($_GET['filter']) && isset($_GET['serviceId'])) {
    $students = getFilteredStudents($conn, $_GET['filter'], $_GET['serviceId'], $date, $role);
    
    // Retourner les résultats en JSON
    header('Content-Type: application/json');
    echo json_encode($students);
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tableau de Bord | Gestion Stagiaires</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/progressbar.js@1.1.0/dist/progressbar.min.js"></script>
<!-- Le reste du code HTML/CSS reste inchangé -->
 <style>
:root {
  --primary-color: #046818;
  --secondary-color: #034612;
  --accent-color: #049a2c;
  --dark-color: #02310b;
  --light-color: #f8f9fa;
  --success-color: #04c34d;
  --danger-color: #e63946;
  --warning-color: #ff9f1c;
  --info-color: #2ec4b6;
  --sidebar-collapsed: 80px;
  --sidebar-expanded: 250px;
}

* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

body, html {
  height: 100%;
  font-family: 'Poppins', sans-serif;
  background-color: #f5f7fa;
  color: #333;
}

/* Sidebar Styles */
.sidebar {
  position: fixed;
  top: 0;
  left: 0;
  height: 100%;
  width: var(--sidebar-collapsed);
  background: linear-gradient(180deg, var(--primary-color), var(--dark-color));
  color: white;
  box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
  z-index: 100;
  transition: all 0.3s ease;
  overflow: hidden;
}

.sidebar:hover {
  width: var(--sidebar-expanded);
}

.sidebar-header {
  padding: 20px 15px;
  text-align: center;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  height: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.sidebar-header .logo {
  font-size: 24px;
  font-weight: 700;
  color: white;
  display: flex;
  align-items: center;
  white-space: nowrap;
}

.sidebar-header .logo i {
  font-size: 28px;
  margin-right: 10px;
  flex-shrink: 0;
}

.sidebar-header .logo span {
  opacity: 0;
  transition: opacity 0.3s;
}

.sidebar:hover .sidebar-header .logo span {
  opacity: 1;
}

.sidebar-menu {
  padding: 20px 0;
  height: calc(100% - 80px);
  overflow-y: auto;
}

.sidebar-menu h3 {
  color: rgba(255, 255, 255, 0.7);
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 1px;
  padding: 0 20px;
  margin-bottom: 15px;
  white-space: nowrap;
  opacity: 0;
  transition: opacity 0.3s;
}

.sidebar:hover .sidebar-menu h3 {
  opacity: 1;
}

.sidebar-menu ul {
  list-style: none;
}

.sidebar-menu li a {
  display: flex;
  align-items: center;
  padding: 15px 20px;
  color: rgba(255, 255, 255, 0.8);
  text-decoration: none;
  transition: all 0.3s ease;
  font-size: 15px;
  white-space: nowrap;
}

.sidebar-menu li a:hover, 
.sidebar-menu li a.active {
  background: rgba(255, 255, 255, 0.1);
  color: white;
}

.sidebar-menu li a i {
  font-size: 20px;
  width: 40px;
  flex-shrink: 0;
  text-align: center;
}

.sidebar-menu li a span {
  opacity: 0;
  transition: opacity 0.3s;
}

.sidebar:hover .sidebar-menu li a span {
  opacity: 1;
}

.sidebar-menu .logout {
  margin-top: 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  padding-top: 20px;
}

/* Main Content Styles */
.main-content {
  margin-left: var(--sidebar-collapsed);
  padding: 30px;
  min-height: 100vh;
  transition: all 0.3s ease;
  background-color: #f0f2f5;
}

.sidebar:hover ~ .main-content {
  margin-left: var(--sidebar-expanded);
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e0e8e0;
  }

  .page-header h1 {
    font-size: 28px;
    font-weight: 600;
    color: #1a3a1a;
    display: flex;
    align-items: center;
    gap: 10px;
  }

.header-right {
    display: flex;
    align-items: center;
    gap: 20px;
  }
  
  .datetime-display {
    background: white;
    padding: 8px 15px;
    border-radius: 30px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    border: 1px solid #e0e8e0;
    font-size: 14px;
    color: #1a3a1a;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  
  .datetime-display i {
    color: #046818;
  }

  .user-profile {
    display: flex;
    align-items: center;
    background: white;
    padding: 8px 15px;
    border-radius: 30px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
    border: 1px solid #e0e8e0;
  }

  .user-profile:hover {
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transform: translateY(-2px);
  }

  .user-profile img {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    margin-right: 10px;
    object-fit: cover;
    border: 2px solid #e0e8e0;
  }
/* Dashboard Grid Layout */
.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(12, 1fr);
  gap: 25px;
  margin-bottom: 30px;
}

/* Stat Cards */
.stat-card {
  grid-column: span 3;
  background: white;
  border-radius: 16px;
  padding: 25px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
  
}

.stat-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  border:4px solid #046818;
}

.stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 5px;
  /*background: linear-gradient(90deg, var(--primary-color), var(--accent-color));*/
  opacity: 0;
  transition: opacity 0.3s;
}

.stat-card:hover::before {
  opacity: 1;
}



.stat-card.presences { border-left-color: var(--warning-color); }
.stat-card.evaluations { border-left-color: var(--info-color); }
.stat-card.finished { border-left-color: var(--dark-color); }

.stat-card .card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

.stat-card .icon {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: white;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

.stat-card.students .icon { background-color: var(--primary-color); }
.stat-card.stages .icon { background-color: var(--accent-color); }
.stat-card.documents .icon { background-color: var(--success-color); }
.stat-card.presences .icon { background-color: var(--warning-color); }
.stat-card.evaluations .icon { background-color: var(--info-color); }
.stat-card.finished .icon { background-color: var(--dark-color); }

.stat-card .value {
  font-size: 32px;
  font-weight: 700;
  color: #2c3e50;
  margin: 10px 0 5px;
}

.stat-card .label {
  font-size: 16px;
  color: #5c6b7a;
}

/* Service Cards */
.service-card {
  grid-column: span 4;
  background: white;
  border-radius: 16px;
  padding: 25px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.service-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
   border:4px solid #046818;
}

.service-card::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 5px;
  /*background: linear-gradient(90deg, var(--primary-color), var(--accent-color));*/
  opacity: 0;
  transition: opacity 0.3s;
}

.service-card:hover::after {
  opacity: 1;
  
}

.service-card .service-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.service-card .service-title {
  font-size: 18px;
  font-weight: 600;
  color: #2c3e50;
  margin: 0;
}

.service-card .service-icon {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: white;
  background-color: var(--primary-color);
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

.service-card .service-stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 15px;
  margin-bottom: 20px;
}

.stat-item {
  background: #f8f9fa;
  border-radius: 10px;
  padding: 15px;
  text-align: center;
  transition: all 0.3s;
  cursor: pointer;
}

.stat-item:hover {
  transform: translateY(-5px);
  box-shadow: 0 5px 15px rgba(0,0,0,0.05);
  background: white;
   border:1px solid #046818;
}

.service-card .stat-value {
  font-size: 24px;
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 5px;
}

.service-card .stat-label {
  font-size: 14px;
  color: #7f8c8d;
}

.service-card .presence-container {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin: 20px 0;
}

.service-card .presence-circle {
  width: 120px;
  height: 120px;
  position: relative;
}

.service-card .presence-percent {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 24px;
  font-weight: 700;
  color: #2c3e50;
}

.service-card .presence-details {
  flex: 1;
  margin-left: 20px;
  
}

.service-card .presence-detail {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
  padding: 10px;
  border-radius: 8px;
  background: #f8f9fa;
  transition: all 0.3s;
  cursor: pointer;
}

.service-card .presence-detail:hover {
  background: white;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
   border:1px solid #046818;
}

.service-card .presence-detail:last-child {
  margin-bottom: 0;
}

.service-card .presence-label {
  font-size: 14px;
  color: #7f8c8d;
}

.service-card .presence-count {
  font-weight: 600;
  color: #2c3e50;
}

/* Chart Containers */
.chart-container {
  grid-column: span 12;
  background: white;
  border-radius: 16px;
  padding: 25px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
  margin-bottom: 30px;
  transition: all 0.3s;
}

.chart-container:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.chart-header h2 {
  margin: 0;
  font-size: 20px;
  color: #2c3e50;
  display: flex;
  align-items: center;
  gap: 10px;
}

/* Recent Activity Cards */
.recent-container {
  display: grid;
  grid-template-columns: repeat(12, 1fr);
  gap: 25px;
  margin-bottom: 30px;
}

.recent-card {
  grid-column: span 4;
  background: white;
  border-radius: 16px;
  padding: 25px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
}

.recent-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.recent-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 15px;
  border-bottom: 1px solid #f0f2f5;
}

.recent-header h2 {
  margin: 0;
  font-size: 18px;
  color: #2c3e50;
  display: flex;
  align-items: center;
  gap: 10px;
}

.recent-list {
  list-style: none;
}

.recent-item {
  padding: 15px 0;
  border-bottom: 1px solid #f0f2f5;
  display: flex;
  align-items: center;
  transition: all 0.3s;
}

.recent-item:hover {
  background: #f8fafc;
  border-radius: 8px;
  padding: 15px;
   
}

.recent-item:last-child {
  border-bottom: none;
}

.recent-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: #f0f4ff;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 15px;
  color: var(--primary-color);
  font-size: 18px;
  flex-shrink: 0;
}

.recent-content {
  flex: 1;
  overflow-x: auto;
}

.recent-title {
  font-weight: 500;
  color: #2c3e50;
  margin-bottom: 5px;
}

.recent-meta {
  font-size: 13px;
  color: #7f8c8d;
  display: flex;
  gap: 15px;
  flex-wrap: wrap;
}

.recent-meta span {
  display: flex;
  align-items: center;
  gap: 5px;
}

.recent-actions {
  display: flex;
  gap: 10px;
  margin-left: 15px;
}

/* Action Buttons */
.action-btn {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f8fafc;
  color: var(--primary-color);
  border: 1px solid #e0e4e8;
  transition: all 0.2s;
  text-decoration: none;
  cursor: pointer;
}

.action-btn:hover {
  background: var(--primary-color);
  color: white;
  transform: scale(1.1);
}

.action-buttons {
  display: flex;
  gap: 10px;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 30px;
  color: #7f8c8d;
}

.empty-state i {
  font-size: 48px;
  color: #d1d9e0;
  margin-bottom: 15px;
}

/* Students List Modal */
.students-list-container {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s;
}

.students-list-container.show {
  opacity: 1;
  visibility: visible;
}

.students-list {
  background: white;
  border-radius: 16px;
  width: 80%;
  max-width: 800px;
  max-height: 80vh;
  overflow-y: auto;
  padding: 25px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
  transform: translateY(20px);
  transition: all 0.3s;
}

.students-list-container.show .students-list {
  transform: translateY(0);
}

.students-list-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 15px;
  border-bottom: 1px solid #f0f2f5;
}

.students-list-header h3 {
  margin: 0;
  font-size: 20px;
  color: #2c3e50;
}

.close-btn {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #7f8c8d;
  transition: all 0.2s;
}

.close-btn:hover {
  color: var(--danger-color);
  transform: rotate(90deg);
}

.student-item {
  display: flex;
  align-items: center;
  padding: 15px;
  border-bottom: 1px solid #f0f2f5;
  transition: all 0.3s;
}
 .page-header h1 {
    font-size: 28px;
    font-weight: 600;
    color: #1a3a1a;
    display: flex;
    align-items: center;
    gap: 10px;
  }
.student-item:last-child {
  border-bottom: none;
}

.student-item:hover {
  background: #f8fafc;
  border-radius: 8px;
}

.student-avatar {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: var(--primary-color);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 18px;
  margin-right: 15px;
  flex-shrink: 0;
}

.student-info {
  flex: 1;
}

.student-name {
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 5px;
}

.student-meta {
  font-size: 13px;
  color: #7f8c8d;
  display: flex;
  gap: 15px;
}

.student-meta span {
  display: flex;
  align-items: center;
  gap: 5px;
}

.student-actions {
  display: flex;
  gap: 10px;
}

/* Animation */
.ab {
  animation: aa 2s infinite;
}

@keyframes aa {
  0% { opacity: 0; }
  25% { opacity: 1; }
  50% { opacity: 0; }
  75% { opacity: 1; }
  100% { opacity: 0; }
}

/* Responsive Styles */
@media (max-width: 1200px) {
  .stat-card {
    grid-column: span 6;
  }
  
  .service-card {
    grid-column: span 6;
  }
  
  .recent-card {
    grid-column: span 6;
  }
}

@media (max-width: 768px) {
  .stat-card {
    grid-column: span 12;
  }
  
  .service-card {
    grid-column: span 12;
  }
  
  .recent-card {
    grid-column: span 12;
  }
  
  .main-content {
    padding: 20px;
  }
  
  .presence-container {
    flex-direction: column;
  }
  
  .presence-details {
    margin-left: 0;
    margin-top: 20px;
    width: 100%;
  }

  .students-list {
    width: 95%;
    padding: 15px;
  }
}
</style>
</head>

<body>
<!-- Sidebar -->
<div class="sidebar">
  <div class="sidebar-header">
    <div class="logo">
      <i class="fas fa-user-graduate"></i>
      <span>I.C.F</span>
    </div>
  </div>
  
  <div class="sidebar-menu">
    <h3>Menu Principal</h3>
    <ul>
      <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> <span>Tableau de bord</span></a></li>
      
      <?php if ($role == 'admin' or $role == 'admin_super'): ?>
      <li><a href="etudiants.php"><i class="fas fa-users"></i> <span>Étudiants</span></a></li>
      <li><a href="documents.php"><i class="fas fa-file-alt"></i> <span>Documents</span></a></li>
      <li><a href="utilisateurs.php"><i class="fas fa-user-cog"></i> <span>Utilisateurs</span></a></li>
      <?php endif; ?>
    </ul>
    
    <div class="logout">
      <ul>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Déconnexion</span></a></li>
      </ul>
    </div>
  </div>
</div>

<!-- Main Content -->
<div class="main-content">
 
  <div class="page-header">
  <h1><i class="fas fa-tachometer-alt"></i> Tableau de Bord</h1>
  <div class="header-right">
    <div class="datetime-display">
      <i class="far fa-clock"></i>
      <span id="live-datetime"><?= date('d/m/Y H:i:s') ?></span>
    </div>
    <div class="user-profile">
      <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['username'] ?? 'Admin') ?>&background=046818&color=fff" alt="User">
      <span><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?> (<?= strtoupper($role) ?>)</span>
    </div>
  </div>
</div>


  <!-- Statistiques Générales -->
<!-- Statistiques Générales -->
<div class="dashboard-grid">
    <div class="stat-card students" onclick="showStudentsList('etudiants')">
        <div class="card-header">
            <div class="icon">
                <i class="fas fa-user-graduate"></i>
            </div>
        </div>
        <div class="value"><?= $stats['etudiants'] ?></div>
        <div class="label">Étudiants inscrits</div>
    </div>
    
    <div class="stat-card stages" onclick="showStudentsList('stages_encours')">
        <div class="card-header">
            <div class="icon">
                <i class="fas fa-clipboard-list"></i>
            </div>
        </div>
        <div class="value"><?= $stats['stages_encours'] ?></div>
        <div class="label" style="font-family: 'Poppins';">Stages en cours</div>
    </div>
    
    <div class="stat-card finished" onclick="showStudentsList('stages_termines')">
        <div class="card-header">
            <div class="icon">
                <i class="fas fa-trophy"></i>
            </div>
        </div>
        <div class="value"><?= $stats['stages_termines'] ?></div>
        <div class="label" style="font-family: 'Poppins';">Stages terminés</div>
    </div>
    
    <div class="stat-card documents" onclick="showStudentsList('documents')">
        <div class="card-header">
            <div class="icon">
                <i class="fas fa-folder-open"></i>
            </div>
        </div>
        <div class="value"><?= $stats['documents'] ?></div>
        <div class="label">Documents déposés</div>
    </div>
</div>

<!-- Modal pour afficher la liste des étudiants -->
<div class="students-list-container" id="studentsListModal">
    <div class="students-list">
        <div class="students-list-header">
            <h3 id="studentsListTitle">Liste des étudiants</h3>
            <button class="close-btn" id="closeStudentsList">&times;</button>
        </div>
        <div id="studentsListContent">
            <!-- Contenu chargé dynamiquement -->
        </div>
    </div>
</div>

  <!-- Statistiques par Service -->
  <div class="dashboard-grid">
    <?php foreach ($services_stats as $service): ?>
    <div class="service-card">
      <div class="service-header">
        <h3 class="service-title"><?= htmlspecialchars($service['nom_service']) ?></h3>
        <div class="service-icon">
          <?php 
            $icons = [
              'Ressources Humaines' => 'fas fa-users',
              'Informatique' => 'fas fa-laptop-code',
              'Comptabilité' => 'fas fa-calculator',
              'Marketing' => 'fas fa-bullhorn',
              'Direction Générale' => 'fas fa-building'
            ];
            $icon = $icons[$service['nom_service']] ?? 'fas fa-briefcase';
          ?>
          <i class="<?= $icon ?>"></i>
        </div>
      </div>
      
      <div class="service-stats">
        <div class="stat-item" data-filter="total" data-service="<?= $service['id_service'] ?>">
          <div class="stat-value"><?= $service['total_stagiaires'] ?></div>
          <div class="stat-label">Total</div>
        </div>
        <div class="stat-item" data-filter="encours" data-service="<?= $service['id_service'] ?>">
          <div class="stat-value"><?= $service['stages_encours'] ?></div>
          <div class="stat-label">En cours</div>
        </div>
        <div class="stat-item" data-filter="termines" data-service="<?= $service['id_service'] ?>">
          <div class="stat-value"><?= $service['stages_termines'] ?></div>
          <div class="stat-label">Terminés</div>
        </div>
      </div>
      
      <div class="presence-container">
        <div class="presence-circle" id="circle-<?= $service['id_service'] ?>">
          <div class="presence-percent"><?= $service['presence_percent'] ?>%</div>
        </div>
        <div class="presence-details">
          <div class="presence-detail" data-filter="present" data-service="<?= $service['id_service'] ?>">
            <span class="presence-label">Présents</span>
            <span class="presence-count"><?= $service['presences_present'] ?></span>
          </div>
          <div class="presence-detail" data-filter="absent" data-service="<?= $service['id_service'] ?>">
            <span class="presence-label">Absents</span>
            <span class="presence-count"><?= $service['presences_absent'] ?></span>
          </div>
          <div class="presence-detail" data-filter="justifie" data-service="<?= $service['id_service'] ?>">
            <span class="presence-label">Justifiés</span>
            <span class="presence-count"><?= $service['presences_justifie'] ?></span>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Graphiques (uniquement pour admin ou si autorisé) -->
  <?php if ($role === 'admin'): ?>
  <div class="dashboard-grid">
    <div class="chart-container">
      <div class="chart-header">
        <h2><i class="fas fa-chart-line"></i> Stages par mois (<?= date('Y') ?>)</h2>
      </div>
      <canvas id="stagesChart" height="50"></canvas>
    </div>
    
    <div class="chart-container">
      <div class="chart-header">
        <h2><i class="fas fa-chart-bar"></i> Répartition des stages par année</h2>
      </div>
      <canvas id="stagesAnneeChart" height="50"></canvas>
    </div>
  </div>
  <?php endif; ?>

  <!-- Activité Récente -->
  <div class="recent-container">
    <?php if ($role == 'admin' ) {?>
    <div class="recent-card">
      <div class="recent-header">
        <h2><i class="fas fa-users"></i> Derniers Modification</h2>
      </div>
      
      <?php if (!empty($derniers_Modifi)): ?>
      <ul class="recent-list">
        <?php foreach ($derniers_Modifi as $a): ?>
        <li class="recent-item">
          <div class="recent-icon">
            <?= strtoupper(substr($a['username'], 0, 1)) . strtoupper(substr($a['username'], 1, 1)) ?>
          </div>
          <div class="recent-content">
            <div class="recent-title"><?= htmlspecialchars($a['username']) ?></div>
            <div class="recent-meta">
              <span><i class="fas fa-briefcase"></i> <?= htmlspecialchars($a['action']) ?></span>
              <span><i class="fas fa-calendar"></i> <?= htmlspecialchars($a['date_action']) ?></span>
            </div>
          </div>
        </li>
        <?php endforeach; ?>
      </ul>
      <?php else: ?>
      <div class="empty-state">
        <i class="fas fa-user-graduate"></i>
        <p>Aucun étudiant enregistré</p>
      </div>
      <?php endif; ?>
    </div>
    <?php } ?>
    
    
    <div class="recent-card">
      <div class="recent-header">
        <h2><i class="fas fa-briefcase"></i> Stages en cours</h2>
      </div>
      
      <?php if (!empty($stages_recents)): ?>
      <ul class="recent-list">
        <?php foreach ($stages_recents as $s): ?>
        <li class="recent-item">
          <div class="recent-icon" style="background: #e6f7ff; color: var(--accent-color);">
            <i class="fas fa-briefcase"></i>
          </div>
          <div class="recent-content">
            <div class="recent-title"><?= htmlspecialchars($s['prenom'] . ' ' . htmlspecialchars($s['nom'])) ?> (<?= $s['nom_service'] ?>)</div>
            <div class="recent-meta">
              <span><i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($s['date_debut'])) ?> - <?= date('d/m/Y', strtotime($s['date_fin'])) ?></span>
            </div>
          </div>
          
        </li>
        <?php endforeach; ?>
      </ul>
      <?php else: ?>
      <div class="empty-state">
        <i class="fas fa-briefcase"></i>
        <p>Aucun stage en cours</p>
      </div>
      <?php endif; ?>
    </div>
    
    <div class="recent-card">
      <div class="recent-header">
        <h2><i class="fas fa-calendar-check"></i> Présences récentes</h2>
      </div>
      
      <?php if (!empty($presences_recentes)): ?>
      <ul class="recent-list">
        <?php foreach ($presences_recentes as $p): ?>
        <li class="recent-item">
          <div class="recent-icon" style="background: #fff7e6; color: var(--warning-color);">
            <i class="fas fa-calendar"></i>
          </div>
          <div class="recent-content">
            <div class="recent-title"><?= htmlspecialchars($p['prenom'] . ' ' . htmlspecialchars($p['nom'])) ?></div>
            <div class="recent-meta">
              <span><i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($p['date'])) ?></span>
              <span style="color: <?= $p['etat'] == 'Présent' ? '#00a854' : ($p['etat'] == 'Absent' ? '#f5222d' : '#fa8c16') ?>">
                <i class="fas fa-circle ab"></i> <?= $p['etat'] ?>
              </span>
            </div>
          </div>
        </li>
        <?php endforeach; ?>
      </ul>
      <?php else: ?>
      <div class="empty-state">
        <i class="fas fa-calendar-times"></i>
        <p>Aucune présence enregistrée</p>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Modal pour afficher la liste des étudiants -->
<div class="students-list-container" id="studentsListModal">
  <div class="students-list">
    <div class="students-list-header">
      <h3 id="studentsListTitle">Liste des étudiants</h3>
      <button class="close-btn" id="closeStudentsList">&times;</button>
    </div>
    <div id="studentsListContent">
      <!-- Contenu chargé dynamiquement -->
    </div>
  </div>
</div>

<script>
  // Graphique des stages par mois (cette année)
  <?php if ($role == 'admin' or $role == 'admin_super'): ?>
  const ctx = document.getElementById('stagesChart').getContext('2d');
  const stagesChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: <?= json_encode($mois_labels) ?>,
      datasets: [
        {
          label: 'En cours',
          data: <?= json_encode($mois_data_encours) ?>,
          backgroundColor: '#046818',
          borderColor: '#034612',
          borderWidth: 1,
          borderRadius: 6
        },
        {
          label: 'Terminés',
          data: <?= json_encode($mois_data_termines) ?>,
          backgroundColor: '#04c34d',
          borderColor: '#039a3c',
          borderWidth: 1,
          borderRadius: 6
        }
      ]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'top',
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: {
            color: 'rgba(0, 0, 0, 0.05)'
          },
          ticks: {
            stepSize: 1
          }
        },
        x: {
          grid: {
            display: false
          }
        }
      }
    }
  });

  // Graphique des stages par année
  const ctxAnnee = document.getElementById('stagesAnneeChart').getContext('2d');
  const stagesAnneeChart = new Chart(ctxAnnee, {
    type: 'bar',
    data: {
      labels: <?= json_encode(array_column($stages_par_annee, 'annee')) ?>,
      datasets: [
        {
          label: 'En cours',
          data: <?= json_encode(array_column($stages_par_annee, 'encours')) ?>,
          backgroundColor: '#046818',
          borderColor: '#034612',
          borderWidth: 1,
          borderRadius: 6
        },
        {
          label: 'Terminés',
          data: <?= json_encode(array_column($stages_par_annee, 'termines')) ?>,
          backgroundColor: '#04c34d',
          borderColor: '#039a3c',
          borderWidth: 1,
          borderRadius: 6
        }
      ]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'top',
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: {
            color: 'rgba(0, 0, 0, 0.05)'
          },
          ticks: {
            stepSize: 1
          }
        },
        x: {
          grid: {
            display: false
          }
        }
      }
    }
  });
  <?php endif; ?>

  // Graphiques en cercle pour les services
  <?php foreach ($services_stats as $service): ?>
  var circle<?= $service['id_service'] ?> = new ProgressBar.Circle('#circle-<?= $service['id_service'] ?>', {
    color: '#046818',
    strokeWidth: 8,
    trailWidth: 8,
    trailColor: '#f0f2f5',
    easing: 'easeInOut',
    duration: 1400,
    text: {
      autoStyleContainer: false
    },
    from: { color: '#046818', width: 8 },
    to: { color: '#04c34d', width: 8 },
    step: function(state, circle) {
      circle.path.setAttribute('stroke', state.color);
      circle.path.setAttribute('stroke-width', state.width);

      var value = Math.round(circle.value() * 100);
      if (value === 0) {
        circle.setText('');
      } else {
        circle.setText('');
      }
    }
  });
  circle<?= $service['id_service'] ?>.text.style.fontSize = '2rem';
  circle<?= $service['id_service'] ?>.animate(<?= $service['presence_percent'] / 100 ?>);
  <?php endforeach; ?>

  // Gestion du clic sur les éléments de stat et de présence
  document.querySelectorAll('.stat-item, .presence-detail').forEach(element => {
    element.addEventListener('click', function() {
      const filter = this.getAttribute('data-filter');
      const serviceId = this.getAttribute('data-service');
      
      // Définir le titre en fonction du filtre
      let title = '';
      switch(filter) {
        case 'total': title = 'Tous les étudiants'; break;
        case 'encours': title = 'Étudiants en stage en cours'; break;
        case 'termines': title = 'Étudiants en stage terminé'; break;
        case 'present': title = 'Étudiants présents aujourd\'hui'; break;
        case 'absent': title = 'Étudiants absents aujourd\'hui'; break;
        case 'justifie': title = 'Étudiants absents justifiés aujourd\'hui'; break;
      }

      
      document.getElementById('studentsListTitle').textContent = title;
      
      // Charger les étudiants via AJAX
      fetch(`dashboard.php?filter=${filter}&serviceId=${serviceId}`)
        .then(response => response.json())
        .then(students => {
          const content = document.getElementById('studentsListContent');
          content.innerHTML = '';
          
          if(students.length === 0) {
            content.innerHTML = `
              <div class="empty-state">
                <i class="fas fa-user-graduate"></i>
                <p>Aucun étudiant trouvé</p>
              </div>
            `;
          } else {
            students.forEach(student => {
              const studentElement = document.createElement('div');
              studentElement.className = 'student-item';
              
              // Créer les initiales pour l'avatar
              const initials = (student.prenom ? student.prenom.charAt(0) : '') + (student.nom ? student.nom.charAt(0) : '');
              
              // Formater la date de stage
              const dateDebut = student.date_debut ? new Date(student.date_debut).toLocaleDateString('fr-FR') : 'N/A';
              const dateFin = student.date_fin ? new Date(student.date_fin).toLocaleDateString('fr-FR') : 'N/A';
              studentElement.innerHTML = `
                <div class="student-avatar">${initials}</div>
                <div class="student-info">
                  <div class="student-name">${student.prenom} ${student.nom}</div>
                  <div class="student-meta">
                    <span><i class="fas fa-envelope"></i> ${student.email || 'N/A'}</span>
                    <span><i class="fas fa-phone"></i> ${student.telephone || 'N/A'}</span>
                    <span><i class="fas fa-calendar"></i> ${dateDebut} - ${dateFin}</span>
                  </div>
                </div>
                <div class="student-actions">
                  <div class="action-buttons">
                  
                    <a href="etudiant_profile.php?id=${student.id_etudiant}" class="action-btn" title="Modifier">
                      <i class="fas fa-eye"></i>
                    </a>
                   
                  </div>
                </div>
              `;
              
              content.appendChild(studentElement);
            });
          }
          
          // Afficher la modal
          document.getElementById('studentsListModal').classList.add('show');
        })
        .catch(error => {
          console.error('Erreur:', error);
          document.getElementById('studentsListContent').innerHTML = `
            <div class="empty-state">
              <i class="fas fa-exclamation-triangle"></i>
              <p>Une erreur s'est produite lors du chargement des données</p>
            </div>
          `;
          document.getElementById('studentsListModal').classList.add('show');
        });
    });
  });

  // Fermer la modal
  document.getElementById('closeStudentsList').addEventListener('click', function() {
    document.getElementById('studentsListModal').classList.remove('show');
  });

  // Fermer la modal en cliquant à l'extérieur
  document.getElementById('studentsListModal').addEventListener('click', function(e) {
    if(e.target === this) {
      this.classList.remove('show');
    }
  });
  function updateDateTime() {
    const now = new Date();
    const options = { 
      weekday: 'long', 
      day: '2-digit', 
      month: '2-digit', 
      year: 'numeric',
      hour: '2-digit', 
      minute: '2-digit', 
      second: '2-digit',
      hour12: false
    };
    
    // Formatage de la date en français (vous pouvez adapter selon vos besoins)
    const dateStr = now.toLocaleDateString('fr-FR', {
      weekday: 'long',
      day: '2-digit',
      month: '2-digit',
      year: 'numeric'
    });
    
    const timeStr = now.toLocaleTimeString('fr-FR', {
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit',
      hour12: false
    });
    
    document.getElementById('live-datetime').textContent = `${dateStr} ${timeStr}`;
    
    // Mise à jour chaque seconde
    setTimeout(updateDateTime, 1000);
  }
  
  // Lancement de la fonction au chargement de la page
  document.addEventListener('DOMContentLoaded', updateDateTime);
  // Fonction pour afficher la liste des étudiants selon le type
function showStudentsList(type) {
    let title = '';
    let url = '';
    
    switch(type) {
        case 'etudiants':
            title = 'Tous les étudiants inscrits';
            url = 'get_students.php?type=all';
            break;
        case 'stages_encours':
            title = 'Étudiants en stage en cours';
            url = 'get_students.php?type=current_internships';
            break;
        case 'stages_termines':
            title = 'Étudiants avec stages terminés';
            url = 'get_students.php?type=finished_internships';
            break;
        case 'documents':
            title = 'Étudiants avec documents déposés';
            url = 'get_students.php?type=with_documents';
            break;
    }
    
    document.getElementById('studentsListTitle').textContent = title;
    
    // Afficher un indicateur de chargement
    document.getElementById('studentsListContent').innerHTML = `
        <div class="empty-state">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Chargement des données...</p>
        </div>
    `;
    
    // Charger les étudiants via AJAX
    fetch(url)
        .then(response => response.json())
        .then(students => {
            const content = document.getElementById('studentsListContent');
            content.innerHTML = '';
            
            if(students.length === 0) {
                content.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-user-graduate"></i>
                        <p>Aucun étudiant trouvé</p>
                    </div>
                `;
            } else {
                students.forEach(student => {
                    const studentElement = document.createElement('div');
                    studentElement.className = 'student-item';
                    
                    // Créer les initiales pour l'avatar
                    const initials = (student.prenom ? student.prenom.charAt(0) : '') + 
                                   (student.nom ? student.nom.charAt(0) : '');
                    
                    // Formater les dates
                    const dateDebut = student.date_debut ? new Date(student.date_debut).toLocaleDateString('fr-FR') : 'N/A';
                    const dateFin = student.date_fin ? new Date(student.date_fin).toLocaleDateString('fr-FR') : 'N/A';
                    
                    studentElement.innerHTML = `
                        <div class="student-avatar">${initials}</div>
                        <div class="student-info">
                            <div class="student-name">${student.prenom} ${student.nom}</div>
                            <div class="student-meta">
                                <span><i class="fas fa-envelope"></i> ${student.email || 'N/A'}</span>
                                <span><i class="fas fa-phone"></i> ${student.telephone || 'N/A'}</span>
                                ${student.date_debut ? `<span><i class="fas fa-calendar"></i> ${dateDebut} - ${dateFin}</span>` : ''}
                                ${student.nom_service ? `<span><i class="fas fa-building"></i> ${student.nom_service}</span>` : ''}
                            </div>
                        </div>
                        <div class="student-actions">
                            <div class="action-buttons">
                                <a href="etudiant_profile.php?id=${student.id_etudiant}" class="action-btn" title="Voir profil">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                    `;
                    
                    content.appendChild(studentElement);
                });
            }
            
            // Afficher la modal
            document.getElementById('studentsListModal').classList.add('show');
        })
        .catch(error => {
            console.error('Erreur:', error);
            document.getElementById('studentsListContent').innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Une erreur s'est produite lors du chargement des données</p>
                </div>
            `;
            document.getElementById('studentsListModal').classList.add('show');
        });
}

// Fermer la modal
document.getElementById('closeStudentsList').addEventListener('click', function() {
    document.getElementById('studentsListModal').classList.remove('show');
});

// Fermer la modal en cliquant à l'extérieur
document.getElementById('studentsListModal').addEventListener('click', function(e) {
    if(e.target === this) {
        this.classList.remove('show');
    }
});
</script>
</body>
</html>