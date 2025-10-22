<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$conn = new PDO("mysql:host=localhost;dbname=gestion_stagiaires;charset=utf8", "root", "");
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_GET['id_etudiant'])) {
   header("Location: etudiants.php");
}

$id_etudiant = intval($_GET['id_etudiant']);
$date = date("Y-m-d H:i:s");
$role = $_SESSION['role'];

// Récupérer la liste des services
$stmt = $conn->prepare("SELECT * FROM services ORDER BY nom_service");
$stmt->execute();
$services = $stmt->fetchAll();

// Récupérer les infos de l'étudiant
$stmt = $conn->prepare("SELECT nom, prenom FROM etudiants WHERE id_etudiant = ?");
$stmt->execute([$id_etudiant]);
$etudiant = $stmt->fetch();

if (isset($_POST['add'])) {
    $id_service = intval($_POST['id_service']);
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];
    $etat = $_POST['etat'];

    // Insertion du stage
    $stmt = $conn->prepare("INSERT INTO stages (id_etudiant, id_service, date_debut, date_fin, etat) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$id_etudiant, $id_service, $date_debut, $date_fin, $etat]);

    // Log de l'action
    $stmt = $conn->prepare("INSERT INTO logs (id_user, action, date_action) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], "Ajout d'un nouveau stage", $date]);

    // Récupérer l'email du directeur du service
    $stmt = $conn->prepare("
        SELECT d.email, s.nom_service 
        FROM utilisateurs d 
        JOIN services s ON d.id_service = s.id_service 
        WHERE d.id_service = ?
    ");
    $stmt->execute([$id_service]);
    $directeur = $stmt->fetch();

    if ($directeur) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = '';
            $mail->Password = '';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ]
            ];

            $mail->setFrom('', 'ICF');
            $mail->addAddress($directeur['email']);
            $mail->isHTML(true);
            $mail->Subject = "📝 Nouveau stage ajouté - Service " . $directeur['nom_service'];

            $mail->Body = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; }
                    .container { background-color: #ffffff; padding: 20px; border-radius: 8px; max-width: 600px; margin: auto; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
                    .header { background-color: #046818; color: #ffffff; padding: 15px; border-radius: 8px 8px 0 0; text-align: center; }
                    .content { padding: 20px; }
                    .footer { font-size: 13px; text-align: center; color: #777; margin-top: 20px; }
                    .highlight { background-color: #e7f5e7; padding: 10px; border-left: 5px solid #05a326; margin: 15px 0; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>ICF - Notification de Stage</h2>
                    </div>
                    <div class='content'>
                        <p>Bonjour,</p>
                        <p>Un nouveau stage a été ajouté pour un étudiant dans votre service <strong>" . htmlspecialchars($directeur['nom_service']) . "</strong>.</p>
                        <div class='highlight'>
                            <p><strong>Étudiant :</strong> " . htmlspecialchars($etudiant['prenom']) . " " . htmlspecialchars($etudiant['nom']) . "<br>
                            <strong>Date de début :</strong> " . htmlspecialchars($date_debut) . "<br>
                            <strong>Date de fin :</strong> " . htmlspecialchars($date_fin) . "<br>
                            <strong>État :</strong> " . htmlspecialchars($etat) . "</p>
                        </div>
                        <p>Merci de prendre en compte cette affectation.</p>
                    </div>
                    <div class='footer'>
                        &copy; " . date('Y') . " ICF
                    </div>
                </div>
            </body>
            </html>
            ";

            $mail->send();
            $message = "Stage ajouté et email envoyé au directeur.";
        } catch (Exception $e) {
            $message = "Erreur lors de l'envoi de l'email : " . $mail->ErrorInfo;
        }
    }

    header("Location: etudiant_profile.php?id=$id_etudiant");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ajouter un stage | Gestion Stagiaires</title>

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
    background-color: #f8faf8;
    color: #333;
  }

  /* Sidebar */
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

  /* Form Container */
  .form-container {
    background: white;
    border-radius: 16px;
    padding: 40px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    max-width: 750px;
    margin: 0 auto;
    position: relative;
    overflow: hidden;
    border: 1px solid #e0e8e0;
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

  .form-header {
    text-align: center;
    margin-bottom: 35px;
  }

  .form-header h2 {
    margin: 0;
    font-size: 26px;
    color: #1a3a1a;
    position: relative;
    display: inline-block;
    font-weight: 600;
  }

  .form-header h2::after {
    content: '';
    position: absolute;
    bottom: -12px;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 4px;
    background: var(--primary-color);
    border-radius: 4px;
  }

  /* Student Card */
  .student-card {
    background: linear-gradient(135deg, #f0f8f0, #e0f0e0);
    border-radius: 16px;
    padding: 30px;
    margin-bottom: 40px;
    display: flex;
    align-items: center;
    gap: 25px;
    box-shadow: 0 6px 18px rgba(4, 104, 24, 0.1);
    position: relative;
    overflow: hidden;
  }

  .student-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(4, 104, 24, 0.05), transparent);
  }

  .student-icon {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    font-weight: bold;
    color: var(--primary-color);
    box-shadow: 0 8px 20px rgba(0,0,0,0.08);
    border: 3px solid rgba(4, 104, 24, 0.2);
    z-index: 1;
  }

  .student-info {
    z-index: 1;
  }

  .student-info h3 {
    margin: 0;
    font-size: 22px;
    color: var(--dark-color);
    font-weight: 600;
  }

  .student-info p {
    margin: 8px 0 0;
    color: #5c6b5c;
    font-size: 16px;
  }

  /* Form Grid */
  .form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-bottom: 30px;
  }

  @media (max-width: 600px) {
    .form-grid {
      grid-template-columns: 1fr;
    }
  }

  /* Form Groups */
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

  /* Select Container */
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

  /* Form Controls */
  .form-control {
    width: 100%;
    padding: 17px 20px;
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
    padding: 16px 20px;
  }

  /* Buttons */
  .btn {
    padding: 17px 30px;
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

  .btn-block {
    display: block;
    width: 100%;
  }

  .form-actions {
    display: flex;
    gap: 20px;
    margin-top: 20px;
  }

  .btn-secondary {
    background: #f8faf8;
    color: #5c6b5c;
    border: 1px solid #d0e0d0;
  }

  .btn-secondary:hover {
    background: #f0f5f0;
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
  }

  /* Confirmation Modal */
  .confirmation-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    z-index: 1000;
    align-items: center;
    justify-content: center;
    animation: fadeIn 0.3s ease;
  }

  .confirmation-modal.active {
    display: flex;
  }

  .confirmation-content {
    background: white;
    border-radius: 16px;
    padding: 40px;
    max-width: 500px;
    width: 90%;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
    text-align: center;
    position: relative;
    animation: slideIn 0.3s ease;
    border: 1px solid #e0e8e0;
  }

  .confirmation-content::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 5px;
    background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
    border-radius: 16px 16px 0 0;
  }

  .confirmation-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px;
    font-size: 32px;
    color: white;
    box-shadow: 0 8px 25px rgba(4, 104, 24, 0.3);
  }

  .confirmation-content h3 {
    font-size: 24px;
    color: var(--dark-color);
    margin-bottom: 15px;
    font-weight: 600;
  }

  .confirmation-content p {
    color: #5c6b5c;
    font-size: 16px;
    line-height: 1.6;
    margin-bottom: 30px;
  }

  .confirmation-details {
    background: #f8faf8;
    border-radius: 12px;
    padding: 20px;
    margin: 25px 0;
    text-align: left;
    border-left: 4px solid var(--primary-color);
  }

  .confirmation-details p {
    margin: 8px 0;
    font-size: 15px;
    color: #5c6b5c;
  }

  .confirmation-details strong {
    color: var(--dark-color);
  }

  .confirmation-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
  }

  .btn-cancel {
    background: #f8faf8;
    color: #5c6b5c;
    border: 1px solid #d0e0d0;
    padding: 15px 30px;
  }

  .btn-cancel:hover {
    background: #f0f5f0;
    transform: translateY(-2px);
  }

  .btn-confirm {
    background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
    color: white;
    padding: 15px 30px;
  }

  .btn-confirm:hover {
    background: linear-gradient(135deg, var(--secondary-color), var(--dark-color));
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(4, 104, 24, 0.3);
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

    .student-card {
      flex-direction: column;
      text-align: center;
      padding: 25px;
    }

    .form-actions {
      flex-direction: column;
      gap: 15px;
    }

    .confirmation-actions {
      flex-direction: column;
    }

    .confirmation-content {
      padding: 30px 20px;
    }
  }

  /* Animations */
  @keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
  }

  @keyframes slideIn {
    from { 
      opacity: 0;
      transform: translateY(-30px) scale(0.9);
    }
    to { 
      opacity: 1;
      transform: translateY(0) scale(1);
    }
  }

  .form-container {
    animation: fadeIn 0.6s ease forwards;
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
    <h1><i class="fas fa-briefcase"></i> Nouveau stage</h1>
    <div class="user-profile">
      <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['username'] ?? 'Admin') ?>&background=046818&color=fff" alt="User">
      <span><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></span>
    </div>
  </div>

  <div class="form-container">
    <div class="form-header">
      <h2>Ajouter un nouveau stage</h2>
    </div>
    
    <div class="student-card">
      <div class="student-icon">
        <?= strtoupper(substr($etudiant['prenom'], 0, 1) . strtoupper(substr($etudiant['nom'], 0, 1)) )?>
      </div>
      <div class="student-info">
        <h3><?= htmlspecialchars($etudiant['prenom'] . ' ' . $etudiant['nom']) ?></h3>
        <p>Création d'une nouvelle période de stage</p>
      </div>
    </div>

    <!-- AJOUT CRITIQUE : input hidden avec name="add" -->
    <form method="POST" id="stageForm">
      <input type="hidden" name="add" value="1">
      
      <div class="form-grid">
        <div class="form-group">
          <label for="id_service"><i class="fas fa-building"></i> Service</label>
          <div class="select-container">
            <select id="id_service" name="id_service" class="form-control" required>
              <option value="" disabled selected>-- Sélectionnez un service --</option>
              <?php foreach ($services as $service): ?>
                <option value="<?= $service['id_service'] ?>"><?= htmlspecialchars($service['nom_service']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label for="etat"><i class="fas fa-tasks"></i> État du stage</label>
          <select id="etat" name="etat" class="form-control" required>
            <option value="En cours" selected>En cours</option>
            <option value="Terminé">Terminé</option>
          </select>
        </div>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label for="date_debut"><i class="fas fa-calendar-alt"></i> Date de début</label>
          <input type="date" id="date_debut" name="date_debut" class="form-control" required>
        </div>

        <div class="form-group">
          <label for="date_fin"><i class="fas fa-calendar-alt"></i> Date de fin</label>
          <input type="date" id="date_fin" name="date_fin" class="form-control" required>
        </div>
      </div>

      <div class="form-actions">
        <a href="etudiant_profile.php?id=<?= $id_etudiant ?>" class="btn btn-secondary">
          <i class="fas fa-arrow-left"></i> Annuler
        </a>
        <!-- CHANGEMENT : type="button" au lieu de type="submit" -->
        <button type="button" id="saveBtn" class="btn btn-primary btn-block">
          <i class="fas fa-save"></i> Enregistrer le stage
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Modal de confirmation -->
<div class="confirmation-modal" id="confirmationModal">
  <div class="confirmation-content">
    <div class="confirmation-icon">
      <i class="fas fa-question"></i>
    </div>
    <h3>Confirmez-vous la création de ce nouveau stage ?</h3>
    <p>Veuillez vérifier les informations ci-dessous avant de confirmer :</p>
    
    <div class="confirmation-details" id="confirmationDetails">
      <!-- Les détails seront injectés ici par JavaScript -->
    </div>
    
    <div class="confirmation-actions">
      <button type="button" class="btn btn-cancel" id="cancelBtn">
        <i class="fas fa-times"></i> Annuler
      </button>
      <button type="button" class="btn btn-confirm" id="confirmBtn">
        <i class="fas fa-check"></i> Confirmer
      </button>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('stageForm');
  const saveBtn = document.getElementById('saveBtn');
  const confirmationModal = document.getElementById('confirmationModal');
  const confirmationDetails = document.getElementById('confirmationDetails');
  const cancelBtn = document.getElementById('cancelBtn');
  const confirmBtn = document.getElementById('confirmBtn');
  
  // Focus sur le premier champ
  document.getElementById('id_service').focus();
  
  // Validation des dates
  const dateDebut = document.getElementById('date_debut');
  const dateFin = document.getElementById('date_fin');
  
  // Définir la date minimale d'aujourd'hui
  const today = new Date().toISOString().split('T')[0];
  dateDebut.min = today;
  
  dateDebut.addEventListener('change', function() {
    dateFin.min = this.value;
  });
  
  dateFin.addEventListener('change', function() {
    if (dateDebut.value && this.value < dateDebut.value) {
      alert('La date de fin doit être postérieure à la date de début');
      this.value = '';
    }
  });
  
  // Gestion de la confirmation
  saveBtn.addEventListener('click', function() {
    // Validation basique
    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }
    
    // Récupérer les valeurs du formulaire
    const serviceSelect = document.getElementById('id_service');
    const selectedService = serviceSelect.options[serviceSelect.selectedIndex].text;
    const etat = document.getElementById('etat').value;
    const dateDebutValue = document.getElementById('date_debut').value;
    const dateFinValue = document.getElementById('date_fin').value;
    
    // Formater les dates
    const formatDate = (dateString) => {
      const date = new Date(dateString);
      return date.toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
      });
    };
    
    // Mettre à jour les détails de confirmation
    confirmationDetails.innerHTML = `
      <p><strong>Étudiant :</strong> <?= htmlspecialchars($etudiant['prenom'] . ' ' . $etudiant['nom']) ?></p>
      <p><strong>Service :</strong> ${selectedService}</p>
      <p><strong>État :</strong> ${etat}</p>
      <p><strong>Date de début :</strong> ${formatDate(dateDebutValue)}</p>
      <p><strong>Date de fin :</strong> ${formatDate(dateFinValue)}</p>
    `;
    
    // Afficher le modal
    confirmationModal.classList.add('active');
  });
  
  // Annuler la confirmation
  cancelBtn.addEventListener('click', function() {
    confirmationModal.classList.remove('active');
  });
  
  // CORRECTION CRITIQUE : Soumission réelle du formulaire
  confirmBtn.addEventListener('click', function() {
    confirmationModal.classList.remove('active');
    // Soumission réelle du formulaire
    form.submit();
  });
  
  // Fermer le modal en cliquant à l'extérieur
  confirmationModal.addEventListener('click', function(e) {
    if (e.target === confirmationModal) {
      confirmationModal.classList.remove('active');
    }
  });
});
</script>
</body>
</html>
