<?php
session_start();
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) { 
    header("Location: dashboard.php"); 
    exit; 
}

if (!isset($_GET['id_stage'])) { 
    header("Location: dashboard.php"); 
    exit;
}

$conn = new PDO("mysql:host=localhost;dbname=gestion_stagiaires;charset=utf8", "root", "");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$id_stage = intval($_GET['id_stage']);
$date = $_GET['date'] ?? date('Y-m-d');
$message = '';
$role = $_SESSION['role'];

// Récupération ID étudiant
$abc = $conn->prepare("SELECT id_etudiant FROM stages WHERE id_stage = ? LIMIT 1");
$abc->execute([$id_stage]);
$st = $abc->fetch();
$id_etud = $st["id_etudiant"] ?? null;

// Récupération infos étudiant
$stmt = $conn->prepare("SELECT e.id_etudiant, e.nom, e.prenom, e.email
                        FROM stages s 
                        JOIN etudiants e ON s.id_etudiant = e.id_etudiant 
                        WHERE s.id_stage = ?");
$stmt->execute([$id_stage]);
$student_info = $stmt->fetch();

$pre = $student_info['prenom'] ?? '';
$no = $student_info['nom'] ?? '';
$email = $student_info['email'] ?? '';

if (isset($_POST['save'])) {
    $etat = $_POST['etat'] ?? '';

    try {
        $stmt = $conn->prepare("SELECT id_presence FROM presences WHERE id_stage = ? AND date = ?");
        $stmt->execute([$id_stage, $date]);
        $exists = $stmt->fetch();

        $date_action = date("Y-m-d H:i:s");

        if ($exists) {
            // Modifier présence
            $stmt = $conn->prepare("UPDATE presences SET etat = ? WHERE id_presence = ?");
            $stmt->execute([$etat, $exists['id_presence']]);
            $stmt = $conn->prepare("INSERT INTO logs (id_user, action, date_action) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], "Modifie la présence de Stagiaire $pre $no", $date_action]);
        } else {
            // Ajouter présence
            $stmt = $conn->prepare("INSERT INTO presences (id_stage, date, etat) VALUES (?, ?, ?)");
            $stmt->execute([$id_stage, $date, $etat]);
            $stmt = $conn->prepare("INSERT INTO logs (id_user, action, date_action) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], "Ajoute la présence de Stagiaire $pre $no", $date_action]);
            header("Location: etudiant_profile.php?id=$id_etud");
            exit;
        }

        // Envoi d’email seulement si absent
        if ($etat === 'Absent') {
            $stmt = $conn->prepare("SELECT e.nom, e.prenom, e.email, s.date_debut, s.date_fin 
                                    FROM stages s 
                                    JOIN etudiants e ON s.id_etudiant = e.id_etudiant 
                                    WHERE s.id_stage = ?");
            $stmt->execute([$id_stage]);
            $info_etudiant = $stmt->fetch();

            if ($info_etudiant && filter_var($info_etudiant['email'], FILTER_VALIDATE_EMAIL)) {
                $mail = new PHPMailer(true);

                try {
                    // 🔧 Log SMTP pour debug
                    $mail->SMTPDebug = 2;
                    $mail->Debugoutput = function($str, $level) {
                        file_put_contents('email_debug.log', $str . PHP_EOL, FILE_APPEND);
                    };

                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    
                    $mail->Username = 'pharfind@gmail.com';
                    $mail->Password = 'stag hgcx gvxm irwd';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;
                    $mail->CharSet = 'UTF-8';

                    // Options SSL (évite erreurs sur serveurs non sécurisés)
                    $mail->SMTPOptions = [
                        'ssl' => [
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true,
                        ]
                    ];

                    $mail->setFrom('pharfind@gmail.com', 'ICF - Suivi des Stagiaires');
                    $mail->addAddress($info_etudiant['email']);
                    $mail->isHTML(true);
                    $mail->Subject = "Notification d'absence - ICF";

                    $mail->Body = "
                    <html>
                    <head>
                        <style>
                            body { font-family: Arial, sans-serif; background-color: #f5f6f8; margin: 0; padding: 0; }
                            .container { max-width: 600px; background: #fff; margin: 30px auto; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
                            .header { background: #046818; color: #fff; text-align: center; padding: 20px; font-size: 20px; }
                            .body { padding: 25px; color: #333; font-size: 16px; }
                            .notice { background: #f8d7da; color: #721c24; padding: 15px; border-left: 5px solid #f44336; border-radius: 4px; margin: 20px 0; }
                            .footer { background: #f1f1f1; text-align: center; padding: 10px; font-size: 14px; color: #555; }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <div class='header'>ICF - Notification de Présence</div>
                            <div class='body'>
                                <p>Bonjour <strong>" . htmlspecialchars($info_etudiant['prenom']) . " " . htmlspecialchars($info_etudiant['nom']) . "</strong>,</p>
                                <div class='notice'>
                                    Vous avez été marqué(e) comme <strong>absent(e)</strong> le <strong>" . htmlspecialchars($date) . "</strong>.
                                </div>
                                <p>Votre période de stage : du <strong>" . htmlspecialchars($info_etudiant['date_debut']) . "</strong> au <strong>" . htmlspecialchars($info_etudiant['date_fin']) . "</strong>.</p>
                                <p>Merci de justifier cette absence dès que possible.</p>
                            </div>
                            <div class='footer'>
                                Cordialement,<br>L'équipe ICF
                            </div>
                        </div>
                    </body>
                    </html>
                    ";

                    $mail->send();
                    $message = "✅ Présence enregistrée et email envoyé.";
                } catch (Exception $e) {
                    $message = "⚠️ Erreur d'envoi de l'email : {$mail->ErrorInfo}";
                }
            } else {
                $message = "⚠️ Présence enregistrée mais email invalide.";
            }
        } else {
            $message = "✅ Présence enregistrée.";
        }

    } catch (PDOException $e) {
        $message = "Erreur lors de l'enregistrement : " . $e->getMessage();
    }
}

// Récupération présence + infos stage
try {
    $stmt = $conn->prepare("SELECT etat FROM presences WHERE id_stage = ? AND date = ?");
    $stmt->execute([$id_stage, $date]);
    $presence = $stmt->fetchColumn();

    $stmt = $conn->prepare("SELECT e.nom, e.prenom, s.date_debut, s.date_fin 
                            FROM stages s 
                            JOIN etudiants e ON s.id_etudiant = e.id_etudiant 
                            WHERE s.id_stage = ?");
    $stmt->execute([$id_stage]);
    $info = $stmt->fetch();
} catch (PDOException $e) {
    die("Erreur base de données : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Présence | Gestion Stagiaires</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
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

  /* Student Card */
  .student-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    border-left: 4px solid var(--primary-color);
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

  .student-info h2 {
    margin: 0 0 5px;
    font-size: 22px;
    color: #1a3a1a;
  }

  .student-info p {
    margin: 5px 0;
    color: #5c6b5c;
  }

  .student-details {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
  }

  .detail-item {
    margin-bottom: 10px;
  }

  .detail-label {
    font-weight: 500;
    color: #5c6b5c;
    font-size: 14px;
    margin-bottom: 5px;
  }

  .detail-value {
    color: #2c3e50;
    font-size: 15px;
  }

  .status-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 500;
  }

  .status-present {
    background-color: rgba(4, 200, 100, 0.1);
    color: #04c864;
  }

  .status-absent {
    background-color: rgba(230, 57, 70, 0.1);
    color: #e63946;
  }

  .status-justified {
    background-color: rgba(255, 159, 28, 0.1);
    color: #ff9f1c;
  }

  /* Form Card */
  .form-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    border-left: 4px solid var(--primary-color);
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

  .select-wrapper {
    position: relative;
  }

  .select-wrapper::after {
    content: '\f078';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    color: var(--primary-color);
  }

  .form-select {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    padding-right: 35px;
    cursor: pointer;
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

  /* Message */
  .alert {
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .alert-success {
    background-color: rgba(4, 200, 100, 0.1);
    color: #04c864;
    border-left: 4px solid #04c864;
  }

  .alert-warning {
    background-color: rgba(255, 159, 28, 0.1);
    color: #ff9f1c;
    border-left: 4px solid #ff9f1c;
  }

  .alert-danger {
    background-color: rgba(230, 57, 70, 0.1);
    color: #e63946;
    border-left: 4px solid #e63946;
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

    .student-details {
      grid-template-columns: 1fr;
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
      <li><a href="presence.php" class="active"><i class="fas fa-calendar-check"></i> <span>Présences</span></a></li>
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
    <h1><i class="fas fa-calendar-check"></i> Gestion des présences</h1>
    <div class="user-profile">
      <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['username'] ?? 'Admin') ?>&background=046818&color=fff" alt="User">
      <span><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></span>
    </div>
  </div>

  <div class="content-container">
    <?php if (!empty($message)): ?>
      <div class="alert <?= strpos($message, 'enregistrée') !== false ? 'alert-success' : (strpos($message, 'invalide') !== false ? 'alert-warning' : 'alert-danger') ?>">
        <i class="fas <?= strpos($message, 'enregistrée') !== false ? 'fa-check-circle' : (strpos($message, 'invalide') !== false ? 'fa-exclamation-triangle' : 'fa-exclamation-circle') ?>"></i>
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <div class="student-card">
      <div class="student-header">
        <div class="student-avatar">
          <?= strtoupper(substr($info['prenom'], 0, 1) . substr($info['nom'], 0, 1)) ?>
        </div>
        <div class="student-info">
          <h2><?= htmlspecialchars($info['prenom'] . ' ' . $info['nom']) ?></h2>
          <p><i class="fas fa-calendar-alt"></i> <?= htmlspecialchars($date) ?></p>
        </div>
      </div>
      
      <div class="student-details">
        <div class="detail-item">
          <div class="detail-label">Période de stage</div>
          <div class="detail-value"><?= htmlspecialchars($info['date_debut']) ?> - <?= htmlspecialchars($info['date_fin']) ?></div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Statut actuel</div>
          <div class="detail-value">
            <?php if ($presence): ?>
              <span class="status-badge <?= $presence == 'Présent' ? 'status-present' : ($presence == 'Absent' ? 'status-absent' : 'status-justified') ?>">
                <i class="fas <?= $presence == 'Présent' ? 'fa-check' : ($presence == 'Absent' ? 'fa-times' : 'fa-info-circle') ?>"></i>
                <?= htmlspecialchars($presence) ?>
              </span>
            <?php else: ?>
              <span style="color: #5c6b5c;">Non enregistré</span>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <div class="form-card">
      <h2 class="form-title"><i class="fas fa-edit"></i> Modifier la présence</h2>
      <form method="POST">
        <div class="form-group">
          <label for="date" class="form-label">Date</label>
          <input type="date" style="font-family: 'Poppins', sans-serif;" id="date" name="date" class="form-control" value="<?= htmlspecialchars($date) ?>" readonly>
        </div>
        
        <div class="form-group select-wrapper">
          <label for="etat" class="form-label">État de présence</label>
          <select style="font-family: 'Poppins', sans-serif;" id="etat" name="etat" class="form-control form-select" required>
            <option value="Présent" <?= $presence == 'Présent' ? 'selected' : '' ?>>Présent</option>
            <option value="Absent" <?= $presence == 'Absent' ? 'selected' : '' ?>>Absent</option>
            <option value="Justifié" <?= $presence == 'Justifié' ? 'selected' : '' ?>>Justifié</option>
          </select>
        </div>
        
        <button type="submit" name="save" style="font-family: 'Poppins', sans-serif;" class="btn btn-primary btn-block">
          <i class="fas fa-save"></i> Enregistrer
        </button>
      </form>
    </div>
  </div>
</div>
</body>
</html>