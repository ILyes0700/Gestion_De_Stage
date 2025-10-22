<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: etudiants.php"); }
if (!isset($_GET['id'])) { header("Location: etudiants.php"); }

$conn = new PDO("mysql:host=localhost;dbname=gestion_stagiaires;charset=utf8", "root", "");

$id_etudiant = intval($_GET['id']);

// Infos étudiant
$stmt = $conn->prepare("SELECT * FROM etudiants WHERE id_etudiant = ?");
$stmt->execute([$id_etudiant]);
$etudiant = $stmt->fetch();
if (!$etudiant) { header("Location: etudiants.php"); }
$rol=$_SESSION['role'];
// Stages de cet étudiant
$stmt = $conn->prepare("SELECT s.*, ser.nom_service FROM stages s JOIN services ser ON s.id_service = ser.id_service WHERE s.id_etudiant = ? ORDER BY s.date_debut DESC");
$stmt->execute([$id_etudiant]);
$stages = $stmt->fetchAll();
$role = $_SESSION['role'];

?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profil étudiant | Gestion Stagiaires</title>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
  :root {
    --primary-color: #046818;
    --secondary-color: #034512;
    --accent-color: #05a326;
    --dark-color: #02320c;
    --light-color: #f8f9fa;
    --success-color: #4cc9f0;
    --danger-color: #f72585;
    --warning-color: #f8961e;
    --info-color: #43aa8b;
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

  /* Sidebar (inchangé) */
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

  .main-content {
    margin-left: var(--sidebar-collapsed);
    padding: 30px;
    min-height: 100vh;
    transition: all 0.3s ease;
    background-color: #f8faf8;
  }

  .sidebar:hover ~ .main-content {
    margin-left: var(--sidebar-expanded);
  }

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding: 15px 0;
    border-bottom: 1px solid #e0e8e0;
    position: relative;
  }

  .page-header h1 {
    font-size: 24px;
    font-weight: 600;
    color: #1a3a1a;
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 0;
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
  }
 .back-arrow {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #f5f9f5;
    color: #046818;
    font-size: 18px;
    transition: all 0.3s ease;
    border: 1px solid #e0e8e0;
    text-decoration: none;
  }

  .back-arrow:hover {
    background-color: #046818;
    color: white;
    transform: translateX(-3px);
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
    margin-left: auto;
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

  /* Nouveaux styles */
  .profile-container {
    display: flex;
    gap: 30px;
    margin-bottom: 30px;
  }

  .profile-sidebar {
    flex: 0 0 300px;
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    border: 1px solid #e0e8e0;
    height: fit-content;
  }

  .profile-avatar {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    background: linear-gradient(135deg, #046818, #1a3a1a);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 48px;
    font-weight: bold;
    margin: 0 auto 25px;
    border: 4px solid white;
    box-shadow: 0 8px 20px rgba(4, 104, 24, 0.2);
  }

  .profile-info {
    text-align: center;
    margin-bottom: 25px;
  }

  .profile-info h2 {
    margin: 0 0 10px;
    font-size: 22px;
    color: #1a3a1a;
  }

  .profile-info p {
    margin: 5px 0;
    color: #5c6b5c;
    font-size: 15px;
  }

  .profile-details {
    margin-top: 25px;
  }

  .detail-item {
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #f0f5f0;
  }

  .detail-item:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
  }

  .detail-item h3 {
    font-size: 16px;
    margin: 0 0 12px;
    color: #046818;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .detail-item p {
    margin: 8px 0;
    font-size: 14px;
    color: #333;
  }

  .documents-link {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    background: #f8fbf8;
    border-radius: 8px;
    color: #046818;
    text-decoration: none;
    transition: all 0.3s ease;
    margin-top: 20px;
    border: 1px dashed #c8e0c8;
  }

  .documents-link:hover {
    background: #046818;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(4, 104, 24, 0.2);
    border-color: transparent;
  }

  .documents-link i {
    font-size: 20px;
    margin-right: 10px;
  }

  .profile-content {
    flex: 1;
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    border: 1px solid #e0e8e0;
  }

  .section-title {
    font-size: 20px;
    margin: 0 0 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e0e6e0;
    color: #1a3a1a;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .btn {
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 500;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border: none;
    font-family: 'Poppins', sans-serif;
  }

  .btn-primary {
    background-color: var(--primary-color);
    color: white;
  }

  .btn-primary:hover {
    background-color: var(--secondary-color);
    transform: translateY(-2px);
  }

  /* Table styles */
  .stages-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
  }

  .stages-table th,
  .stages-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #f0f5f0;
  }

  .stages-table th {
    background-color: #f8faf8;
    font-weight: 600;
    color: #5c6b5c;
    font-size: 14px;
  }

  .stages-table tr:hover {
    background-color: #f8faf8;
  }

  .badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
  }

  .badge-success {
    background-color: #e6faf0;
    color: #00a854;
  }

  .badge-warning {
    background-color: #fff8e6;
    color: #fa8c16;
  }

  .action-buttons {
    display: flex;
    gap: 8px;
  }

  .action-btn {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f0f5f0;
    color: #5c6b5c;
    transition: all 0.3s;
    border: none;
    cursor: pointer;
  }

  .action-btn:hover {
    background: var(--primary-color);
    color: white;
  }

  .empty-state {
    text-align: center;
    padding: 40px 20px;
    background: #f8faf8;
    border-radius: 8px;
    margin-top: 20px;
  }

  .empty-state i {
    font-size: 50px;
    color: #d1e0d1;
    margin-bottom: 20px;
  }

  .empty-state h3 {
    margin: 0 0 10px;
    color: #5c6b5c;
    font-size: 18px;
  }

  /* Responsive */
  @media (max-width: 768px) {
    .sidebar {
      width: var(--sidebar-collapsed);
    }
    
    .sidebar:hover {
      width: var(--sidebar-expanded);
      z-index: 1000;
    }
    
    .main-content {
      margin-left: var(--sidebar-collapsed);
      padding: 20px;
    }
    
    .sidebar:hover ~ .main-content {
      margin-left: var(--sidebar-expanded);
    }

    .profile-container {
      flex-direction: column;
    }

    .profile-sidebar {
      flex: 1;
    }
  }
</style>
</head>

<body>
<!-- Sidebar (inchangé) -->
<div class="sidebar">
  <div class="sidebar-header">
    <div class="logo">
      <i class="fas fa-user-graduate"></i>
      <span>ICF</span>
    </div>
  </div>
  
  <div class="sidebar-menu">
    <h3>Menu Principal</h3>
    <ul>
      <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> <span>Tableau de bord</span></a></li>
      
      <?php if ($role == 'admin' or $role == 'admin_super'): ?>
        <li><a href="etudiants.php"><i class="fas fa-users"></i> <span>Étudiants</span></a></li>
      <li><a href="documents.php"><i class="fas fa-file-alt"></i> <span>Documents</span></a></li>
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
  <a href="dashboard.php" class="back-arrow">
    <i class="fas fa-arrow-left"></i>
  </a>
  <h1><i class="fas fa-user-graduate"></i> Profil étudiant</h1>
  <div class="user-profile">
    <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['username'] ?? 'Admin') ?>&background=046818&color=fff" alt="User">
    <span><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></span>
  </div>
</div>

<div class="profile-container">
  <div class="profile-sidebar">
    <div class="profile-avatar">
      <?= strtoupper(substr($etudiant['prenom'], 0, 1) ). strtoupper(substr($etudiant['nom'], 0, 1)) ?>
    </div>
    <div class="profile-info">
      <h2><?= htmlspecialchars($etudiant['prenom'] . ' ' . $etudiant['nom']) ?></h2>
      <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($etudiant['email']) ?></p>
      <p><i class="fas fa-phone"></i> <?= htmlspecialchars($etudiant['telephone']) ?></p>
    </div>

    <div class="profile-details">
      <div class="detail-item">
        <h3><i class="fas fa-id-card"></i> CIN</h3>
        <p><?= htmlspecialchars($etudiant['cin']) ?></p>
      </div>
      <div class="detail-item">
        <h3><i class="fas fa-calendar"></i> Date de naissance</h3>
        <p><?= htmlspecialchars($etudiant['date_naissance']) ?></p>
      </div>
      <div class="detail-item">
        <h3><i class="fas fa-graduation-cap"></i> Niveau scolaire</h3>
        <p><?= htmlspecialchars($etudiant['niveau_scolaire']) ?></p>
      </div>
    </div>
    <?php if($rol=="admin" or $rol == 'admin_super'){ ?>
    <!-- Nouvelle section Documents -->
    <a href="documents.php?id_etudiant=<?= $etudiant['id_etudiant'] ?>" class="documents-link">
      <i class="fas fa-folder-open"></i>
      <span>Documents de l'étudiant</span>
    </a>
    <?php }?>
  </div>

    <div class="profile-content">
      <h2 class="section-title"><i class="fas fa-briefcase"></i> Historique des stages</h2>
      <?php if ($role == 'admin' or $role == 'admin_super'): ?>
      <a href="stage_add.php?id_etudiant=<?= $id_etudiant ?>" class="btn btn-primary">
        <i class="fas fa-plus"></i> Ajouter un stage
      </a>
      <?php endif; ?>

      <?php if (count($stages) > 0): ?>
      <table class="stages-table">
        <thead>
          <tr>
            <th>Service</th>
            <th>Période</th>
            <th>État</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($stages as $s): 
            $dateDebut = new DateTime($s['date_debut']);
            $dateFin = new DateTime($s['date_fin']);
          ?>
          <tr>
  <td><strong><?= htmlspecialchars($s['nom_service']) ?></strong></td>
  <td>
    <?= $dateDebut->format('d/m/Y') ?> - <?= $dateFin->format('d/m/Y') ?>
  </td>
  <td>
 <?php 
$date_actuelle = date('Y-m-d');

// Conversion de la date de fin pour la comparaison
$date_fin = date('Y-m-d', strtotime($s['date_fin']));

// Vérifier si la date de fin est inférieure à la date actuelle et l'état est encore "En cours"
if ($date_fin < $date_actuelle && $s['etat'] != 'Terminé') {
    // Mise à jour automatique dans la base de données
    $id_stage = $s['id_stage'];
    $update = $conn->prepare("UPDATE stages SET etat = 'Terminé' WHERE id_stage = ?");
    $update->execute([$id_stage]);

    // Mettre aussi à jour la variable locale pour l'affichage
    $s['etat'] = 'Terminé';
}

// --- AFFICHAGE ---
$date_affiche = date('d/m/Y');

if ($s['etat'] == 'En cours'): ?>
  <span class="badge badge-success">En cours </span>

<?php elseif ($s['etat'] == "Terminé"): ?>
  <span class="badge badge-warning">Terminé</span>

<?php endif; ?>

  </td>
  <?php if ($s['etat'] != 'Terminé' ): ?>
    <td>
      <div class="action-buttons">
        <a href="presence.php?id_stage=<?= $s['id_stage'] ?>" class="action-btn" title="Présences">
          <i class="fas fa-calendar-check"></i>
        </a>
        <a href="evaluation.php?id_stage=<?= $s['id_stage'] ?>" class="action-btn" title="Évaluations">
          <i class="fas fa-star"></i>
        </a>
        <?php if ($role == 'admin' or $role == 'admin_super'): ?>
        <a href="stage_edit.php?id=<?= $s['id_stage'] ?>" class="action-btn" title="Modifier">
          <i class="fas fa-edit"></i>
        </a>
        <?php endif; ?>
      </div>
    </td>
  <?php else: ?>
    <td></td> <!-- Cellule vide pour maintenir l'alignement du tableau -->
  <?php endif; ?>
</tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
      <div class="empty-state">
        <i class="fas fa-briefcase"></i>
        <h3>Aucun stage enregistré</h3>
        <p>Cet étudiant n'a pas encore effectué de stage.</p>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
  // Animation au chargement
  document.addEventListener('DOMContentLoaded', () => {
    // Confirmation pour les actions importantes
    const deleteButtons = document.querySelectorAll('.action-btn[title="Supprimer"]');
    deleteButtons.forEach(btn => {
      btn.addEventListener('click', (e) => {
        if (!confirm('Êtes-vous sûr de vouloir effectuer cette action ?')) {
          e.preventDefault();
        }
      });
    });
  });
</script>
</body>
</html>