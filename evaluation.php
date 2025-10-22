<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: etudiants.php"); }
if (!isset($_GET['id_stage'])) { header("Location: etudiants.php"); }
$conn = new PDO("mysql:host=localhost;dbname=gestion_stagiaires;charset=utf8", "root", "");

$id_stage = intval($_GET['id_stage']);
$abc = $conn->prepare("SELECT id_etudiant from stages WHERE id_stage = ? limit 1");
$abc->execute([$id_stage]);
$st = $abc->fetch();
$role = $_SESSION['role'];
$id_etud=$st["id_etudiant"];
if (isset($_POST['save'])) {
    $note = intval($_POST['note']);
    $commentaire = $_POST['commentaire'];
    $date_eval = $_POST['date_eval'];

    // Validation renforcée
    if ($note < 0 || $note > 20) {
        $error = "La note doit être comprise entre 0 et 20";
    } elseif (empty($date_eval)) {
        $error = "La date d'évaluation est requise";
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO evaluations (id_stage, date, note, commentaire) 
                                   VALUES (:id_stage, :date_eval, :note, :commentaire)");

            
            $stmt->bindParam(':id_stage', $id_stage, PDO::PARAM_INT);
            $stmt->bindParam(':date_eval', $date_eval);
            $stmt->bindParam(':note', $note, PDO::PARAM_INT);
            $stmt->bindParam(':commentaire', $commentaire);

            
            if ($stmt->execute()) {
                $message = "Évaluation enregistrée avec succès";
                header("Location: etudiant_profile.php?id=$id_etud");
            } else {
                $error = "Erreur lors de l'enregistrement";
                header("Location: etudiant_profile.php?id=$id_etud");
            }
        } catch (PDOException $e) {
            $error = "Erreur technique : " . $e->getMessage();
        }
        
    }
}

$stmt = $conn->prepare("SELECT * FROM evaluations WHERE id_stage = ? ORDER BY date DESC");
$stmt->execute([$id_stage]);
$evaluations = $stmt->fetchAll();

$stmt = $conn->prepare("SELECT e.nom, e.prenom, s.date_debut, s.date_fin, ser.nom_service 
                       FROM stages s 
                       JOIN etudiants e ON s.id_etudiant = e.id_etudiant 
                       JOIN services ser ON s.id_service = ser.id_service 
                       WHERE s.id_stage = ?");
$stmt->execute([$id_stage]);
$info = $stmt->fetch();
$date = $_GET['date'] ?? date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Évaluation | Gestion Stagiaires</title>

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

  /* Main Content */
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

  /* Header */
  .page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e0e6e0;
  }

  .page-header h1 {
    font-size: 24px;
    font-weight: 600;
    color: #1a3a1a;
    display: flex;
    align-items: center;
    gap: 10px;
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

  .user-profile img {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    margin-right: 10px;
    object-fit: cover;
    border: 2px solid #e0e8e0;
  }

  /* Content */
  .content-container {
    max-width: 800px;
    margin: 0 auto;
  }

  /* Student Info */
  .student-info-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    
  }

  .student-header {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
  }

  .student-avatar {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    font-weight: bold;
    margin-right: 20px;
  }

  .student-details {
    flex: 1;
  }

  .student-name {
    margin: 0 0 5px;
    font-size: 22px;
    color: #1a3a1a;
  }

  .student-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
  }

  .meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: #5c6b5c;
  }

  /* Form */
  .evaluation-form {
    background: white;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    
  }

  .form-title {
    font-size: 20px;
    margin: 0 0 20px;
    color: #1a3a1a;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .form-group {
    margin-bottom: 20px;
  }

  .form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #5c6b5c;
  }

  .form-control {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #e0e8e0;
    border-radius: 8px;
    font-size: 15px;
    background-color: #f8faf8;
    transition: all 0.3s;
  }

  .form-control:focus {
    border-color: var(--primary-color);
    outline: none;
    box-shadow: 0 0 0 3px rgba(4, 104, 24, 0.1);
  }

  textarea.form-control {
    min-height: 120px;
    resize: vertical;
  }

  .note-input {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .note-input input {
    width: 80px;
    text-align: center;
    font-weight: 600;
  }

  .note-input span {
    color: #5c6b5c;
  }

  .btn {
    padding: 12px 20px;
    border-radius: 8px;
    font-weight: 500;
    font-size: 15px;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    border: none;
  }

  .btn-primary {
    background-color: var(--primary-color);
    color: white;
  }

  .btn-primary:hover {
    background-color: var(--secondary-color);
    transform: translateY(-2px);
  }

  .btn-block {
    display: block;
    width: 100%;
  }

  /* Alerts */
  .alert {
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .alert-danger {
    background-color: #fff1f0;
    color: var(--danger-color);
    border-left: 4px solid var(--danger-color);
  }

  .alert-success {
    background-color: #e6faf0;
    color: var(--success-color);
    border-left: 4px solid var(--success-color);
  }

  /* Evaluations List */
  .evaluations-list {
    margin-top: 30px;
  }

  .section-title {
    font-size: 20px;
    margin: 0 0 20px;
    color: #1a3a1a;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .evaluation-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    
  }

  .evaluation-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
  }

  .evaluation-date {
    color: #5c6b5c;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .evaluation-note {
    font-size: 24px;
    font-weight: 600;
  }

  .note-excellent {
    color: #04c864;
  }

  .note-good {
    color: var(--primary-color);
  }

  .note-average {
    color: var(--warning-color);
  }

  .note-poor {
    color: var(--danger-color);
  }

  .evaluation-comment {
    color: #5c6b5c;
    line-height: 1.6;
  }

  .empty-state {
    text-align: center;
    padding: 40px 20px;
    background: #f8faf8;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  }

  .empty-state i {
    font-size: 50px;
    color: #d1e0d1;
    margin-bottom: 15px;
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

    .student-header {
      flex-direction: column;
      text-align: center;
    }

    .student-avatar {
      margin-right: 0;
      margin-bottom: 15px;
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
    <h1><i class="fas fa-star"></i> Évaluation du stagiaire</h1>
    <div class="user-profile">
      <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['username'] ?? 'Admin') ?>&background=046818&color=fff" alt="User">
      <span style="font-family: 'Poppins';"><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></span>
    </div>
  </div>

  <div class="content-container" style="font-family: 'Poppins';">
    <div class="student-info-card">
      <div class="student-header">
        <div class="student-avatar" style="font-family: 'Poppins';">
          <?= strtoupper(substr($info['prenom'], 0, 1)) . strtoupper(substr($info['nom'], 0, 1)) ?>
        </div>
        <div class="student-details">
          <h2 class="student-name" style="font-family: 'Poppins';"><?= htmlspecialchars($info['prenom'] . ' ' . $info['nom']) ?></h2>
          <div class="student-meta">
            <div class="meta-item" style="font-family: 'Poppins';">
              <i class="fas fa-building"></i> <?= htmlspecialchars($info['nom_service']) ?>
            </div>
            <div class="meta-item" style="font-family: 'Poppins';">
              <i class="fas fa-calendar-alt"></i> 
              <?= date('d/m/Y', strtotime($info['date_debut'])) ?> - <?= date('d/m/Y', strtotime($info['date_fin'])) ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger" style="font-family: 'Poppins';">
        <i class="fas fa-exclamation-circle"></i> <?= $error ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($message)): ?>
      <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?= $message ?>
      </div>
    <?php endif; ?>

    <div class="evaluation-form">
      <h2 class="form-title"><i class="fas fa-edit"></i> Nouvelle évaluation</h2>
      <form method="POST">
        <div class="form-group">
          <label for="date_eval" class="form-label"><i class="fas fa-calendar"></i> Date d'évaluation</label>
          <input type="date" style="font-family: 'Poppins';" id="date_eval" name="date_eval" class="form-control" value="<?= htmlspecialchars($date) ?>" readonly>
        </div>

        <div class="form-group">
          <label for="note" class="form-label"><i class="fas fa-star"></i> Note</label>
          <div class="note-input">
            <input type="number" id="note" name="note" class="form-control" min="0" max="20" step="0.5" required>
            <span>/ 20</span>
          </div>
        </div>

        <div class="form-group">
          <label for="commentaire" class="form-label"><i class="fas fa-comment"></i> Commentaire</label>
          <textarea id="commentaire" style="font-family: 'Poppins';" name="commentaire" class="form-control" placeholder="Décrivez les points forts et axes d'amélioration..."></textarea>
        </div>

        <button type="submit" style="font-family: 'Poppins';" name="save" class="btn btn-primary btn-block">
          <i class="fas fa-save"></i> Enregistrer l'évaluation
        </button>
      </form>
    </div>

    <div class="evaluations-list">
      <h3 class="section-title"><i class="fas fa-history"></i> Historique des évaluations</h3>
      
      <?php if (count($evaluations) > 0): ?>
        <?php foreach ($evaluations as $eval): 
          $noteClass = $eval['note'] >= 16 ? 'note-excellent' : 
                     ($eval['note'] >= 12 ? 'note-good' : 
                     ($eval['note'] >= 8 ? 'note-average' : 'note-poor'));
        ?>
        <div class="evaluation-card">
          <div class="evaluation-header">
            <div class="evaluation-date">
              <i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($eval['date'])) ?>
            </div>
            <div class="evaluation-note <?= $noteClass ?>">
              <?= $eval['note'] ?>
            </div>
          </div>
          <div class="evaluation-comment">
            <?= nl2br(htmlspecialchars($eval['commentaire'])) ?>
          </div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-state">
          <i class="fas fa-star"></i>
          <h3>Aucune évaluation enregistrée</h3>
          <p>Ce stagiaire n'a pas encore été évalué.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Validation de la note
  const noteInput = document.getElementById('note');
  noteInput.addEventListener('change', function() {
    if (this.value < 0) this.value = 0;
    if (this.value > 20) this.value = 20;
  });

  // Focus sur le champ note
  noteInput.focus();
});
</script>
</body>
</html>