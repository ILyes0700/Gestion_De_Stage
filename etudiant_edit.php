<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$conn = new PDO("mysql:host=localhost;dbname=gestion_stagiaires;charset=utf8", "root", "");

if (!isset($_GET['id'])) {
    header("Location: etudiants.php");
    exit;
}
$id_etudiant = intval($_GET['id']);

// Récupérer les données de l'étudiant
$stmt = $conn->prepare("SELECT * FROM etudiants WHERE id_etudiant = ?");
$stmt->execute([$id_etudiant]);
$etudiant = $stmt->fetch();

if (!$etudiant) {
    header("Location: etudiants.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupération des données du formulaire
    $cin = htmlspecialchars($_POST['cin'] ?? '');
    $nom = htmlspecialchars($_POST['nom'] ?? '');
    $prenom = htmlspecialchars($_POST['prenom'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $telephone = htmlspecialchars($_POST['telephone'] ?? '');
    $telephone_p = htmlspecialchars($_POST['telephonep'] ?? '');
    $date_naissance = htmlspecialchars($_POST['date_naissance'] ?? '');
    $adresse = htmlspecialchars($_POST['Adr'] ?? '');
    $situation_familiale = htmlspecialchars($_POST['sf'] ?? '');
    $nationalite = htmlspecialchars($_POST['nas'] ?? '');
    $contact_urgence = htmlspecialchars($_POST['acc'] ?? '');
    $delivre_cin = htmlspecialchars($_POST['deli_cin'] ?? '');
    
    // Formation scolaire
    $eta_primary = htmlspecialchars($_POST['eta_sta_primary'] ?? '');
    $eta_secondary = htmlspecialchars($_POST['eta_sta_secondary'] ?? '');
    $etablissement_scolaire = $eta_primary . " / " . $eta_secondary;
    $niveau_scolaire = "Primaire + Secondaire";
    $dur_primary = htmlspecialchars($_POST['dur_primary'] ?? '');
    $dur_secondary = htmlspecialchars($_POST['dur_secondary'] ?? '');
    $duree_scolaire = $dur_primary . " / " . $dur_secondary;
    $diplome = htmlspecialchars($_POST['dip'] ?? '');
    $date_diplome = htmlspecialchars($_POST['dat_dip'] ?? '');
    try {
        // Mise à jour de l'étudiant
        $stmt = $conn->prepare("UPDATE etudiants SET 
            cin = ?, delivre_cin = ?, nom = ?, prenom = ?, email = ?, telephone = ?, telephone_pere = ?, adress = ?, 
            date_naissance = ?, situation_familiale = ?, nationalite = ?, contact_urgence = ?,
            etablissement_scolaire = ?, niveau_scolaire = ?, duree_scolaire = ?, diplome = ?, date_diplome = ?
            WHERE id_etudiant = ?");
        $stmt->execute([
            $cin, $delivre_cin, $nom, $prenom, $email, $telephone, $telephone_p, $adresse,
            $date_naissance, $situation_familiale, $nationalite, $contact_urgence,
            $etablissement_scolaire, $niveau_scolaire, $duree_scolaire, $diplome, $date_diplome,
            $id_etudiant
           ]);

        // Journalisation
        $date = date("Y-m-d H:i:s");
        $stmt = $conn->prepare("INSERT INTO logs (id_user, action, date_action) VALUES (?, ?, ?)");


        $stmt->execute([$_SESSION['user_id'], "Modification des données de l'étudiant $nom $prenom", $date]);
        
        $_SESSION['success_message'] = "Les informations de l'étudiant ont été mises à jour avec succès.";
        header("Location: etudiant_edit.php?id=$id_etudiant");
        exit;

    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Erreur base de données: " . $e->getMessage();
        header("Location: etudiant_edit.php?id=$id_etudiant");
        exit;
    }
}
$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Modifier Étudiant | Gestion Stagiaires</title>

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
    background-color: #f8faf8; /* Fond légèrement verdâtre */
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


  /* Nouveau design pour le contenu principal */
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

  /* Conteneur de formulaire modernisé */
  .form-container {
    background: white;
    border-radius: 16px;
    padding: 40px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    margin: 0 auto;
    border: 1px solid #e0e8e0;
    position: relative;
    overflow: hidden;
  }

  .form-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 5px;
    background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
  }

  /* En-tête étudiant modernisé */
  .student-header {
    display: flex;
    align-items: center;
    gap: 25px;
    margin-bottom: 35px;
    padding-bottom: 25px;
    border-bottom: 1px solid #f0f5f0;
    position: relative;
  }

  .student-header::after {
    content: '';
    position: absolute;
    bottom: -1px;
    left: 0;
    width: 100px;
    height: 3px;
    background: var(--primary-color);
    border-radius: 3px;
  }

  .student-avatar {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 36px;
    font-weight: bold;
    box-shadow: 0 8px 20px rgba(4, 104, 24, 0.3);
    border: 3px solid rgba(255,255,255,0.3);
    position: relative;
    overflow: hidden;
  }

  .student-avatar::before {
    content: '';
    position: absolute;
    top: -10px;
    left: -10px;
    right: -10px;
    bottom: -10px;
    background: linear-gradient(135deg, rgba(255,255,255,0.1), transparent);
    transform: rotate(45deg);
  }

  .student-title h2 {
    margin: 0;
    font-size: 28px;
    color: #1a3a1a;
    font-weight: 600;
  }

  .student-title p {
    margin: 8px 0 0;
    color: #5c6b5c;
    font-size: 16px;
  }

  /* Sections de formulaire modernisées */
  .form-section {
    background: #f8faf8;
    border-radius: 14px;
    padding: 25px;
    margin-bottom: 30px;
    border-left: 5px solid var(--primary-color);
    box-shadow: 0 6px 18px rgba(0,0,0,0.06);
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    position: relative;
    overflow: hidden;
  }

  .form-section:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.1);
  }

  .form-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(4, 104, 24, 0.03), transparent);
    pointer-events: none;
  }

  .form-section-title {
    font-size: 18px;
    font-weight: 600;
    color: var(--primary-color);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    position: relative;
  }

  .form-section-title::after {
    content: '';
    position: absolute;
    bottom: -8px;
    left: 0;
    width: 40px;
    height: 3px;
    background: var(--primary-color);
    border-radius: 3px;
  }

  .form-section-title i {
    font-size: 22px;
  }

  /* Grille de formulaire améliorée */
  .form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 25px;
    margin-bottom: 15px;
  }

  /* Groupes de formulaire */
  .form-group {
    margin-bottom: 20px;
    position: relative;
  }

  .form-group label {
    display: block;
    margin-bottom: 10px;
    font-weight: 500;
    color: #5c6b5c;
    font-size: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .form-group label i {
    color: var(--primary-color);
    font-size: 16px;
    width: 20px;
  }

  /* Contrôles de formulaire */
  .form-control {
    width: 100%;
    padding: 15px 18px;
    border: 1px solid #d0e0d0;
    border-radius: 12px;
    font-size: 15px;
    transition: all 0.3s;
    background-color: white;
    box-shadow: 0 3px 8px rgba(0,0,0,0.05);
    font-family: 'Poppins', sans-serif;
  }
.confirmation-box {
  position: fixed;
  top: 530px;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(255, 255, 255, 0.51);
  display: flex;
  justify-content: center;
  align-items: center;
  font-family: 'Poppins', sans-serif;
  z-index: 999;
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.3s ease;
}

.confirmation-box.show {
  opacity: 1;
  pointer-events: all;
}

.confirmation-box.hidden {
  display: none;
}

.confirmation-content {
  background-color: var(--light-color);
  padding: 30px 40px;
  border-radius: 15px;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
  text-align: center;
  max-width: 400px;
  width: 90%;
  animation: fadeIn 0.3s ease-in-out;
}

.confirmation-content h3 {
  color: var(--primary-color);
  margin-bottom: 10px;
}

.confirmation-content p {
  color: var(--dark-color);
  margin-bottom: 20px;
  font-size: 15px;
}

.confirmation-buttons {
  display: flex;
  justify-content: space-around;
}

.btn-confirm,
.btn-cancel {
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 14px;
}

.btn-confirm {
  background-color: var(--primary-color);
  color: white;
}

.btn-confirm:hover {
  background-color: var(--accent-color);
}

.btn-cancel {
  background-color: var(--danger-color);
  color: white;
}

.btn-cancel:hover {
  background-color: #c51b6d;
}

@keyframes fadeIn {
  from {
    transform: scale(0.9);
    opacity: 0;
  }
  to {
    transform: scale(1);
    opacity: 1;
  }
}
  .form-control:focus {
    border-color: var(--primary-color);
    outline: none;
    box-shadow: 0 0 0 3px rgba(4, 104, 24, 0.1);
  }

  select.form-control {
    appearance: none;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%235c6b5c' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 15px center;
    background-size: 16px 12px;
    padding-right: 40px;
  }

  textarea.form-control {
    min-height: 100px;
    resize: vertical;
  }

  /* Actions du formulaire */
  .form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 20px;
    margin-top: 40px;
    padding-top: 30px;
    border-top: 1px solid #f0f5f0;
  }

  /* Boutons modernisés */
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

  /* Champs dynamiques */
  .dynamic-field {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
    align-items: flex-end;
  }

  .dynamic-field .form-group {
    flex: 1;
    margin-bottom: 0;
  }

  .btn-remove {
    background-color: var(--danger-color);
    color: white;
    border: none;
    border-radius: 8px;
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s;
  }

  .btn-remove:hover {
    transform: scale(1.1);
  }

  .btn-add {
    background-color: var(--success-color);
    color: white;
    border: none;
    border-radius: 8px;
    padding: 12px 20px;
    font-size: 15px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-top: 10px;
    transition: all 0.3s;
  }

  .btn-add:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(76, 201, 240, 0.3);
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

    .form-container {
      padding: 25px;
    }

    .student-header {
      flex-direction: column;
      text-align: center;
      gap: 20px;
    }

    .student-avatar {
      margin: 0 auto;
    }

    .form-grid {
      grid-template-columns: 1fr;
    }

    .dynamic-field {
      flex-direction: column;
      gap: 10px;
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

  .form-container {
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
    <h1><i class="fas fa-user-edit"></i> Modifier Étudiant</h1>
    <div class="user-profile">
      <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['username'] ?? 'Admin') ?>&background=046818&color=fff" alt="User">
      <span><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></span>
    </div>
  </div>

  <div class="form-container">
    <div class="student-header">
      <div class="student-avatar">
        <?= strtoupper(substr($etudiant['prenom'], 0, 1) . substr($etudiant['nom'], 0, 1)) ?>
      </div>
      <div class="student-title">
        <h2><?= htmlspecialchars($etudiant['prenom'] . ' ' . $etudiant['nom']) ?></h2>
        <p>Modification des informations</p>
      </div>
    </div>

    <form method="POST">
      <!-- Section Informations personnelles -->
      <div class="form-section">
        <div class="form-section-title">
          <i class="fas fa-user"></i> Informations personnelles
        </div>
        <div class="form-grid">
          <div class="form-group">
            <label for="cin"><i class="fas fa-id-card"></i> CIN</label>
            <input type="text" id="cin" name="cin" class="form-control" value="<?= htmlspecialchars($etudiant['cin']) ?>" required maxlength="8">
          </div>

          <div class="form-group">
            <label for="deli_cin"><i class="fas fa-map-marker-alt"></i> Délivré à</label>
            <input type="text" id="deli_cin" name="deli_cin" class="form-control" value="<?= htmlspecialchars($etudiant['delivre_cin']) ?>">
          </div>

          <div class="form-group">
            <label for="nom"><i class="fas fa-signature"></i> Nom</label>
            <input type="text" id="nom" name="nom" class="form-control" value="<?= htmlspecialchars($etudiant['nom']) ?>" required>
          </div>

          <div class="form-group">
            <label for="prenom"><i class="fas fa-signature"></i> Prénom</label>
            <input type="text" id="prenom" name="prenom" class="form-control" value="<?= htmlspecialchars($etudiant['prenom']) ?>" required>
          </div>

          <div class="form-group">
            <label for="date_naissance"><i class="fas fa-birthday-cake"></i> Date de naissance</label>
            <input type="date" id="date_naissance" name="date_naissance" class="form-control" value="<?= htmlspecialchars($etudiant['date_naissance']) ?>">
          </div>

          <div class="form-group">
            <label for="nas"><i class="fas fa-globe"></i> Nationalité</label>
            <input type="text" id="nas" name="nas" class="form-control" value="<?= htmlspecialchars($etudiant['nationalite']) ?>">
          </div>

          <div class="form-group">
            <label for="sf"><i class="fas fa-heart"></i> Situation familiale</label>
            <select id="sf" name="sf" class="form-control">
              <option value="Célibataire" <?= $etudiant['situation_familiale'] == 'Célibataire' ? 'selected' : '' ?>>Célibataire</option>
              <option value="Marié(e)" <?= $etudiant['situation_familiale'] == 'Marié(e)' ? 'selected' : '' ?>>Marié(e)</option>
              <option value="Divorcé(e)" <?= $etudiant['situation_familiale'] == 'Divorcé(e)' ? 'selected' : '' ?>>Divorcé(e)</option>
              <option value="Veuf/Veuve" <?= $etudiant['situation_familiale'] == 'Veuf/Veuve' ? 'selected' : '' ?>>Veuf/Veuve</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Section Coordonnées -->
      <div class="form-section">
        <div class="form-section-title">
          <i class="fas fa-address-card"></i> Coordonnées
        </div>
        <div class="form-grid">
          <div class="form-group">
            <label for="email"><i class="fas fa-envelope"></i> Email</label>
            <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($etudiant['email']) ?>">
          </div>

          <div class="form-group">
            <label for="telephone"><i class="fas fa-phone"></i> Téléphone</label>
            <input type="text" id="telephone" name="telephone" class="form-control" value="<?= htmlspecialchars($etudiant['telephone']) ?>">
          </div>

          <div class="form-group">
            <label for="telephonep"><i class="fas fa-phone-alt"></i> Téléphone parent</label>
            <input type="text" id="telephonep" name="telephonep" class="form-control" value="<?= htmlspecialchars($etudiant['telephone_pere']) ?>">
          </div>

          <div class="form-group">
            <label for="Adr"><i class="fas fa-home"></i> Adresse</label>
            <textarea id="Adr" name="Adr" class="form-control" rows="3"><?= htmlspecialchars($etudiant['adress']) ?></textarea>
          </div>

          <div class="form-group">
            <label for="acc"><i class="fas fa-exclamation-triangle"></i> Contact d'urgence</label>
            <input type="text" id="acc" name="acc" class="form-control" value="<?= htmlspecialchars($etudiant['contact_urgence']) ?>">
          </div>
        </div>
      </div>

      <!-- Section Formation scolaire -->
      <div class="form-section">
        <div class="form-section-title">
          <i class="fas fa-graduation-cap"></i> Formation scolaire
        </div>
        <div class="form-grid">
          <div class="form-group">
            <label for="eta_sta_primary"><i class="fas fa-school"></i> Établissement primaire</label>
            <input type="text" id="eta_sta_primary" name="eta_sta_primary" class="form-control" 
              value="<?= isset(explode(' / ', $etudiant['etablissement_scolaire'])[0]) ? htmlspecialchars(explode(' / ', $etudiant['etablissement_scolaire'])[0]) : '' ?>">
          </div>

          <div class="form-group">
            <label for="dur_primary"><i class="fas fa-calendar-alt"></i> Durée primaire</label>
            <input type="text" id="dur_primary" name="dur_primary" class="form-control" 
              value="<?= isset(explode(' / ', $etudiant['duree_scolaire'])[0]) ? htmlspecialchars(explode(' / ', $etudiant['duree_scolaire'])[0]) : '' ?>">
          </div>

          <div class="form-group">
            <label for="eta_sta_secondary"><i class="fas fa-school"></i> Établissement secondaire</label>
            <input type="text" id="eta_sta_secondary" name="eta_sta_secondary" class="form-control" 
              value="<?= isset(explode(' / ', $etudiant['etablissement_scolaire'])[1]) ? htmlspecialchars(explode(' / ', $etudiant['etablissement_scolaire'])[1]) : '' ?>">
          </div>

          <div class="form-group">
            <label for="dur_secondary"><i class="fas fa-calendar-alt"></i> Durée secondaire</label>
            <input type="text" id="dur_secondary" name="dur_secondary" class="form-control" 
              value="<?= isset(explode(' / ', $etudiant['duree_scolaire'])[1]) ? htmlspecialchars(explode(' / ', $etudiant['duree_scolaire'])[1]) : '' ?>">
          </div>

          <div class="form-group">
            <label for="dip"><i class="fas fa-certificate"></i> Diplôme obtenu</label>
            <input type="text" id="dip" name="dip" class="form-control" value="<?= htmlspecialchars($etudiant['diplome']) ?>">
          </div>

          <div class="form-group">
            <label for="dat_dip"><i class="fas fa-calendar-check"></i> Date diplôme</label>
            <input type="date" id="dat_dip" name="dat_dip" class="form-control" value="<?= htmlspecialchars($etudiant['date_diplome']) ?>">
          </div>
        </div>
      </div>

      <!-- Section Formation universitaire -->
      <div class="form-section">
        <div class="form-section-title">
          <i class="fas fa-university"></i> Formation universitaire
        </div>
        <div id="university-fields">
          <!-- Les champs universitaires seront ajoutés dynamiquement ici -->
        </div>
        <button type="button" style="font-family: 'Poppins', sans-serif;" id="add-university" class="btn-add">
          <i class="fas fa-plus"></i> Ajouter une formation universitaire
        </button>
      </div>

      <div class="form-actions">
        <a href="etudiant_profile.php?id=<?= $id_etudiant ?>" class="btn btn-outline">
          <i class="fas fa-times"></i> Annuler
        </a>
        <button type="submit"  name="update" class="btn btn-primary">
          <i class="fas fa-save"></i> Enregistrer les modifications
        </button>
      </div>
      <!-- Fenêtre de confirmation stylée -->
<div id="confirmation-box" class="confirmation-box hidden">
  <div class="confirmation-content">
    <h3><i class="fas fa-question-circle"></i> Confirmation de modification</h3>
    <p>Voulez-vous vraiment enregistrer les modifications apportées à cet étudiant ?</p>
    <div class="confirmation-buttons">
      <button type="button" style="font-family: 'Poppins', sans-serif;" id="confirm-update" class="btn-confirm">
        <i class="fas fa-check"></i> Oui, modifier
      </button>
      <button type="button" style="font-family: 'Poppins', sans-serif;" id="cancel-update" class="btn-cancel">
        <i class="fas fa-times"></i> Annuler
      </button>
    </div>
  </div>
</div>

    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Focus sur le premier champ
  document.getElementById('cin').focus();
  
  // Validation du CIN (8 caractères)
  const cinInput = document.getElementById('cin');
  cinInput.addEventListener('input', function() {
    if (this.value.length > 8) {
      this.value = this.value.slice(0, 8);
    }
  });
  
  // Animation au focus
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

  // Gestion des champs universitaires dynamiques
  const universityFields = document.getElementById('university-fields');
  const addUniversityBtn = document.getElementById('add-university');
  
  // Fonction pour ajouter un nouveau champ universitaire
  function addUniversityField(data = { etablissement: '', specialite: '', duree: '' }) {
    const fieldId = Date.now();
    const fieldHtml = `
      <div class="dynamic-field" id="university-${fieldId}">
        <div class="form-group">
          <label><i class="fas fa-university"></i> Établissement</label>
          <input type="text" name="eta_unv[]" class="form-control" value="${data.etablissement}" placeholder="Nom de l'université">
        </div>
        <div class="form-group">
          <label><i class="fas fa-book"></i> Spécialité</label>
          <input type="text" name="spc[]" class="form-control" value="${data.specialite}" placeholder="Spécialité étudiée">
        </div>
        <div class="form-group">
          <label><i class="fas fa-clock"></i> Durée</label>
          <input type="text" name="dur_unv[]" class="form-control" value="${data.duree}" placeholder="Durée des études">
        </div>
        <button type="button" class="btn-remove" onclick="document.getElementById('university-${fieldId}').remove()">
          <i class="fas fa-trash"></i>
        </button>
      </div>
    `;
    universityFields.insertAdjacentHTML('beforeend', fieldHtml);
  }
  
  // Charger les formations universitaires existantes
  <?php
  // Récupérer les universités de l'étudiant
  $stmt = $conn->prepare("SELECT * FROM universites WHERE id_etudiant = ?");
  $stmt->execute([$id_etudiant]);
  $universites = $stmt->fetchAll();
  
  if (!empty($universites)) {
    foreach ($universites as $univ) {
      echo "addUniversityField({ 
        etablissement: '" . addslashes($univ['etablissement']) . "', 
        specialite: '" . addslashes($univ['specialite']) . "', 
        duree: '" . addslashes($univ['duree']) . "' 
      });";
    }
  }
  ?>
  
  addUniversityBtn.addEventListener('click', function() {
    addUniversityField();
  });
  if (universityFields.children.length === 0) {
    addUniversityField();
  }
  const form = document.querySelector('form');
const confirmationBox = document.getElementById('confirmation-box');
const confirmBtn = document.getElementById('confirm-update');
const cancelBtn = document.getElementById('cancel-update');

form.addEventListener('submit', function(e) {
  e.preventDefault(); // Empêche l’envoi direct
  confirmationBox.classList.remove('hidden');
  setTimeout(() => {
    confirmationBox.classList.add('show');
  }, 10);
});

// Si l’utilisateur confirme
confirmBtn.addEventListener('click', function() {
  confirmationBox.classList.remove('show');
  setTimeout(() => {
    form.submit(); // Envoie le formulaire
  }, 200);
});

// Si l’utilisateur annule
cancelBtn.addEventListener('click', function() {
  confirmationBox.classList.remove('show');
  setTimeout(() => {
    confirmationBox.classList.add('hidden');
  }, 200);
});

});
</script>
</body>
</html>