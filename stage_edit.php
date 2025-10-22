<?php
session_start();
if (!isset($_SESSION['user_id'])) { 
    header("Location: index.php"); 
    exit; 
}

if (!isset($_GET['id'])) { 
    header("Location: etudiants.php");
}

$conn = new PDO("mysql:host=localhost;dbname=gestion_stagiaires;charset=utf8", "root", "");

$id_stage = intval($_GET['id']);

// Récupérer les informations du stage
$stmt = $conn->prepare("SELECT s.*, e.nom, e.prenom, ser.nom_service 
                        FROM stages s 
                        JOIN etudiants e ON s.id_etudiant = e.id_etudiant 
                        JOIN services ser ON s.id_service = ser.id_service 
                        WHERE s.id_stage = ?");
$stmt->execute([$id_stage]);
$stage = $stmt->fetch();

if (!$stage) {
    header("Location: etudiants.php");
}

// Récupérer la liste des services
$stmt = $conn->prepare("SELECT * FROM services ORDER BY nom_service");
$stmt->execute();
$services = $stmt->fetchAll();
$role = $_SESSION['role'];
// Traitement du formulaire
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validation des données
    $id_service = intval($_POST['id_service']);
    $date_debut = trim($_POST['date_debut']);
    $date_fin = trim($_POST['date_fin']);
    $etat = trim($_POST['etat']);
    
    // Validation
    if (empty($id_service)) {
        $errors[] = "Le service est requis.";
    }
    
    if (empty($date_debut)) {
        $errors[] = "La date de début est requise.";
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_debut)) {
        $errors[] = "Format de date de début invalide.";
    }
    
    if (empty($date_fin)) {
        $errors[] = "La date de fin est requise.";
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_fin)) {
        $errors[] = "Format de date de fin invalide.";
    } elseif (strtotime($date_fin) < strtotime($date_debut)) {
        $errors[] = "La date de fin doit être postérieure à la date de début.";
    }
    
    if (empty($etat)) {
        $errors[] = "L'état du stage est requis.";
    }
    $abc = $conn->prepare("SELECT id_etudiant from stages WHERE id_stage = ? limit 1");
    $abc->execute([$id_stage]);
    $st = $abc->fetch();
    $id_etud=$st["id_etudiant"];
    // Si pas d'erreurs, mise à jour
    if (empty($errors)) {
      
        $stmt = $conn->prepare("UPDATE stages 
                                SET id_service = ?, date_debut = ?, date_fin = ?, etat = ?
                                WHERE id_stage = ?");
        $stmt->execute([$id_service, $date_debut, $date_fin, $etat, $id_stage]);
        $date=Date("Y-m-d H:i:s");
        $stmt = $conn->prepare("INSERT INTO logs (	id_user, action,date_action) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], "Modfie le stage", $date]);
        header("Location: etudiant_profile.php?id=$id_etud");
        if ($stmt->rowCount() > 0) {
            $success = true;
            // Actualiser les données du stage
            $stmt = $conn->prepare("SELECT s.*, e.nom, e.prenom, ser.nom_service 
                                    FROM stages s 
                                    JOIN etudiants e ON s.id_etudiant = e.id_etudiant 
                                    JOIN services ser ON s.id_service = ser.id_service 
                                    WHERE s.id_stage = ?");
            $stmt->execute([$id_stage]);
            $stage = $stmt->fetch();
        } else {
            $errors[] = "Erreur lors de la mise à jour du stage.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Modifier stage | Gestion Stagiaires</title>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
  :root {
    --primary-color: #046818; /* Nouvelle couleur principale */
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
    background-color: #f8faf8; 
    color: #333;
  }
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



  /* Nouveau design moderne pour le contenu principal */
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

  .user-profile {
    display: flex;
    align-items: center;
    background: white;
    padding: 10px 20px;
    border-radius: 30px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    border: 1px solid #e0e8e0;
  }

  .user-profile:hover {
    box-shadow: 0 6px 18px rgba(0,0,0,0.12);
    transform: translateY(-2px);
  }

  .user-profile img {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    margin-right: 12px;
    object-fit: cover;
    border: 2px solid #e0e8e0;
  }

  /* Nouveau conteneur de contenu */
  .content-container {
    background: white;
    border-radius: 16px;
    padding: 40px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    margin-bottom: 30px;
    border: 1px solid #e0e8e0;
    position: relative;
    overflow: hidden;
  }

  .content-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 5px;
    background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
  }

  /* En-tête du formulaire modernisé */
  .form-header {
    display: flex;
    align-items: center;
    margin-bottom: 35px;
    padding-bottom: 25px;
    border-bottom: 1px solid #f0f5f0;
    position: relative;
  }

  .form-header::after {
    content: '';
    position: absolute;
    bottom: -1px;
    left: 0;
    width: 100px;
    height: 3px;
    background: var(--primary-color);
    border-radius: 3px;
  }

  .form-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 30px;
    color: white;
    font-size: 32px;
    box-shadow: 0 8px 20px rgba(4, 104, 24, 0.3);
    border: 3px solid rgba(255,255,255,0.3);
    position: relative;
    overflow: hidden;
  }

  .form-icon::before {
    content: '';
    position: absolute;
    top: -10px;
    left: -10px;
    right: -10px;
    bottom: -10px;
    background: linear-gradient(135deg, rgba(255,255,255,0.1), transparent);
    transform: rotate(45deg);
  }

  .form-info h2 {
    margin: 0;
    font-size: 26px;
    color: #1a3a1a;
    font-weight: 600;
  }

  .form-info p {
    margin: 10px 0 0;
    color: #5c6b5c;
    font-size: 16px;
  }

  /* Messages modernisés */
  .alert {
    padding: 18px 25px;
    margin-bottom: 30px;
    border-radius: 12px;
    font-size: 16px;
    display: flex;
    align-items: center;
    gap: 15px;
    border: 1px solid transparent;
  }

  .alert-success {
    background-color: #e6faf0;
    color: #00a854;
    border-color: #b7eb8f;
  }

  .alert-danger {
    background-color: #fff1f0;
    color: #f5222d;
    border-color: #ffa39e;
  }

  .alert i {
    font-size: 22px;
  }

  /* Formulaire modernisé */
  .form-container {
    max-width: 800px;
    margin: 0 auto;
  }

  .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-bottom: 30px;
  }

  @media (max-width: 768px) {
    .form-row {
      grid-template-columns: 1fr;
    }
  }

  .form-group {
    margin-bottom: 30px;
    position: relative;
  }

  .form-group label {
    display: block;
    margin-bottom: 12px;
    font-weight: 500;
    color: #5c6b5c;
    font-size: 16px;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  /* Sélecteur modernisé */
  .select-container {
    position: relative;
  }

  .select-container::after {
    content: '\f107';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    position: absolute;
    top: 50%;
    right: 20px;
    transform: translateY(-50%);
    color: var(--primary-color);
    font-size: 18px;
    pointer-events: none;
  }

  .form-control {
    width: 100%;
    padding: 16px 20px;
    border: 1px solid #d0e0d0;
    border-radius: 12px;
    font-size: 16px;
    transition: all 0.3s;
    background-color: #f8faf8;
    box-shadow: 0 3px 8px rgba(0,0,0,0.05);
    font-family: 'Poppins', sans-serif;
  }

  select.form-control {
    padding-right: 50px;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    cursor: pointer;
  }

  .form-control:focus {
    border-color: var(--primary-color);
    outline: none;
    box-shadow: 0 0 0 3px rgba(4, 104, 24, 0.1);
    background-color: white;
  }

  /* Date Input */
  input[type="date"].form-control {
    padding: 15px 20px;
  }

  /* Actions du formulaire */
  .form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 20px;
    margin-top: 30px;
    padding-top: 30px;
    border-top: 1px solid #f0f5f0;
  }

  .btn {
    padding: 16px 30px;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    border: none;
    font-size: 16px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
  }

  .btn-primary {
    background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
    color: white;
  }

  .btn-primary:hover {
    background: linear-gradient(135deg, var(--secondary-color), var(--dark-color));
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(4, 104, 24, 0.3);
  }

  .btn-outline {
    background: #f8faf8;
    color: #5c6b5c;
    border: 1px solid #d0e0d0;
  }

  .btn-outline:hover {
    background: #f0f5f0;
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
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

    .form-header {
      flex-direction: column;
      text-align: center;
    }

    .form-icon {
      margin-right: 0;
      margin-bottom: 25px;
    }

    .form-info {
      text-align: center;
    margin-bottom: 20px;
    }

    .form-actions {
      flex-direction: column;
      gap: 15px;
    }

    .btn {
      width: 100%;
    }
  }

  /* Animation */
  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .content-container {
    animation: fadeIn 0.6s ease forwards;
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
    <h1><i class="fas fa-edit"></i> Modifier un stage</h1>
    <div class="user-profile">
      <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['username'] ?? 'Admin') ?>&background=046818&color=fff" alt="User">
      <span><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></span>
    </div>
  </div>

  <div class="content-container">
    <div class="form-header">
      <div class="form-icon">
        <i class="fas fa-briefcase"></i>
      </div>
      <div class="form-info">
        <h2>Modifier les informations du stage</h2>
        <p>Étudiant: <?= htmlspecialchars($stage['prenom'] . ' ' . $stage['nom']) ?></p>
      </div>
    </div>
    
    <?php if ($success): ?>
      <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> Le stage a été mis à jour avec succès.
      </div>
    <?php endif; ?>
    
    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> 
        <div>
          <strong>Erreurs:</strong>
          <ul style="margin-top: 10px; padding-left: 20px;">
            <?php foreach ($errors as $error): ?>
              <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    <?php endif; ?>
    
    <form method="post" class="form-container">
      <div class="form-row">
        <div class="form-group">
          <label for="service"><i class="fas fa-building"></i> Service *</label>
          <div class="select-container">
            <select id="service" name="id_service" class="form-control" required>
              <option value="">Sélectionnez un service</option>
              <?php foreach ($services as $service): ?>
                <option value="<?= $service['id_service'] ?>" 
                  <?= ($service['id_service'] == $stage['id_service']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($service['nom_service']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        
        <div class="form-group">
          <label for="etat"><i class="fas fa-tasks"></i> État du stage *</label>
          <select id="etat" name="etat" class="form-control" required>
            <option value="">Sélectionnez un état</option>
            <option value="En cours" <?= ($stage['etat'] == 'En cours') ? 'selected' : '' ?>>En cours</option>
            <option value="Terminé" <?= ($stage['etat'] == 'Terminé') ? 'selected' : '' ?>>Terminé</option>
          </select>
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-group">
          <label for="date_debut"><i class="fas fa-calendar-alt"></i> Date de début *</label>
          <input type="date" id="date_debut" name="date_debut" 
                 class="form-control" 
                 value="<?= htmlspecialchars($stage['date_debut']) ?>" 
                 required>
        </div>
        
        <div class="form-group">
          <label for="date_fin"><i class="fas fa-calendar-alt"></i> Date de fin *</label>
          <input type="date" id="date_fin" name="date_fin" 
                 class="form-control"
                 value="<?= htmlspecialchars($stage['date_fin']) ?>" 
                 required>
        </div>
      </div>
      
      <div class="form-actions">
        <a href="etudiant_profile.php?id=<?= $stage['id_etudiant'] ?>" class="btn btn-outline">
          <i class="fas fa-arrow-left"></i> Retour
        </a>
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save"></i> Enregistrer les modifications
        </button>
      </div>
    </form>
  </div>
</div>

<script>
// Amélioration de l'expérience utilisateur
document.addEventListener('DOMContentLoaded', function() {
  // Validation client pour les dates
  const dateDebut = document.getElementById('date_debut');
  const dateFin = document.getElementById('date_fin');
  
  dateDebut.addEventListener('change', function() {
    dateFin.min = this.value;
  });
  
  dateFin.addEventListener('change', function() {
    if (dateDebut.value && this.value < dateDebut.value) {
      alert("La date de fin doit être postérieure à la date de début");
      this.value = '';
    }
  });
  
  // Animation des champs de formulaire
  const inputs = document.querySelectorAll('.form-control');
  inputs.forEach(input => {
    input.addEventListener('focus', function() {
      this.parentElement.style.transform = 'translateY(-3px)';
      this.parentElement.style.boxShadow = '0 8px 20px rgba(0,0,0,0.1)';
    });
    
    input.addEventListener('blur', function() {
      this.parentElement.style.transform = 'none';
      this.parentElement.style.boxShadow = '0 3px 8px rgba(0,0,0,0.05)';
    });
  });
  
  // Animation des boutons
  const buttons = document.querySelectorAll('.btn');
  buttons.forEach(btn => {
    btn.addEventListener('mouseenter', function() {
      this.style.transform = 'translateY(-3px)';
      if (this.classList.contains('btn-primary')) {
        this.style.boxShadow = '0 10px 25px rgba(4, 104, 24, 0.4)';
      } else {
        this.style.boxShadow = '0 8px 20px rgba(0,0,0,0.15)';
      }
    });
    
    btn.addEventListener('mouseleave', function() {
      this.style.transform = 'none';
      this.style.boxShadow = '0 5px 15px rgba(0,0,0,0.1)';
    });
  });
  
  // Confirmation avant soumission
  
});
</script>
</body>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const form = document.querySelector('form');
  const confirmationBox = document.getElementById('confirmationBox');
  const confirmYes = document.getElementById('confirmYes');
  const confirmNo = document.getElementById('confirmNo');

  // Quand on clique sur "Enregistrer les modifications"
  form.addEventListener('submit', function(e) {
    e.preventDefault(); // empêcher la soumission immédiate
    confirmationBox.classList.add('show'); // afficher la boîte de confirmation
  });

  // Si l’utilisateur confirme
  confirmYes.addEventListener('click', function() {
    confirmationBox.classList.remove('show');
    form.submit(); // soumettre réellement le formulaire ici
  });

  // Si l’utilisateur annule
  confirmNo.addEventListener('click', function() {
    confirmationBox.classList.remove('show');
  });
});
</script>

<!-- Boîte de confirmation -->
<div class="confirmation-box" id="confirmationBox">
  <div class="confirmation-content">
    <h3><i class="fas fa-exclamation-triangle"></i> Confirmer la modification</h3>
    <p>Êtes-vous sûr de vouloir enregistrer les changements pour ce stage ?</p>
    <div class="confirmation-actions">
      <button id="confirmYes" class="btn btn-primary">Oui, modifier</button>
      <button id="confirmNo" class="btn btn-outline">Annuler</button>
    </div>
  </div>
</div>

<!-- Animation de chargement -->


<style>
.confirmation-box {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.5);
  display: none;
  align-items: center;
  justify-content: center;
  z-index: 2000;
  animation: fadeIn 0.3s ease;
}
.confirmation-box.show { display: flex; }

.confirmation-content {
  background: white;
  padding: 40px;
  border-radius: 20px;
  text-align: center;
  box-shadow: 0 10px 40px rgba(0,0,0,0.2);
  max-width: 400px;
  width: 90%;
  animation: scaleIn 0.3s ease;
}
.confirmation-content h3 {
  color: #046818;
  margin-bottom: 10px;
}
.confirmation-content p {
  color: #333;
  margin-bottom: 30px;
}
.confirmation-actions {
  display: flex;
  justify-content: center;
  gap: 15px;
}
@keyframes scaleIn {
  from { transform: scale(0.8); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}

/* Overlay de chargement */
.loading-overlay {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(255,255,255,0.8);
  display: none;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  z-index: 3000;
  font-family: 'Poppins', sans-serif;
}
.loading-overlay.show { display: flex; }

.spinner {
  width: 60px;
  height: 60px;
  border: 5px solid #cceccc;
  border-top: 5px solid #046818;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 15px;
}
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>





</html>