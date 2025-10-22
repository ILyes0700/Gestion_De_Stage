<?php
error_reporting(E_ALL & ~E_DEPRECATED);
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$tcpdf_path = __DIR__ . '\TCPDF-6.10.0\TCPDF-6.10.0\tcpdf.php';
if (!file_exists($tcpdf_path)) {
    die("Erreur : Le fichier TCPDF n'a pas été trouvé");
}
require_once($tcpdf_path);

$conn = new PDO("mysql:host=localhost;dbname=gestion_stagiaires;charset=utf8", "root", "");

if (isset($_POST['add'])) {
    // Récupération des données du formulaire
    $nom = htmlspecialchars($_POST['nom'] ?? '');
    $cin = htmlspecialchars($_POST['cin'] ?? '');
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
    $dur_primary = htmlspecialchars($_POST['dur_primary'] ?? '');
    $dur_secondary = htmlspecialchars($_POST['dur_secondary'] ?? '');
    $diplome = htmlspecialchars($_POST['dip'] ?? '');
    $date_diplome = htmlspecialchars($_POST['dat_dip'] ?? '');

    // Universités
    $universites = [];
    if (isset($_POST['eta_unv'])) {
        foreach ($_POST['eta_unv'] as $key => $eta_unv) {
            $universites[] = [
                'etablissement' => htmlspecialchars($eta_unv),
                'specialite' => htmlspecialchars($_POST['spc'][$key] ?? ''),
                'duree' => htmlspecialchars($_POST['dur_unv'][$key] ?? '')
            ];
        }
    }

    $date = date("Y-m-d H:i:s");

    try {
        // Insertion de l'étudiant
        $stmt = $conn->prepare("INSERT INTO etudiants 
            (cin, delivre_cin, nom, prenom, email, telephone, telephone_pere, adress, 
             date_naissance, situation_familiale, nationalite, contact_urgence,
             etablissement_scolaire, niveau_scolaire, duree_scolaire, diplome, date_diplome) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $etablissement_scolaire = $eta_primary . " / " . $eta_secondary;
        $duree_scolaire = $dur_primary . " / " . $dur_secondary;
        $niveau_scolaire = "Primaire + Secondaire";
        
        $stmt->execute([
            $cin, $delivre_cin, $nom, $prenom, $email, $telephone, $telephone_p, $adresse,
            $date_naissance, $situation_familiale, $nationalite, $contact_urgence,
            $etablissement_scolaire, $niveau_scolaire, $duree_scolaire, $diplome, $date_diplome
        ]);
        
        $etudiant_id = $conn->lastInsertId();

        // Insertion des universités
        foreach ($universites as $universite) {
            $stmt = $conn->prepare("INSERT INTO universites 
                (id_etudiant, etablissement, specialite, duree) 
                VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $etudiant_id, 
                $universite['etablissement'], 
                $universite['specialite'], 
                $universite['duree']
            ]);
        }

        // Classe PDF personnalisée avec le nouveau style
        class ICF_PDF extends TCPDF {
    protected $header_logo = __DIR__.'/logo.png';
    
    public function Header() {
        // Logo ICF
        if (file_exists($this->header_logo)) {
            $this->Image($this->header_logo, 10, 10, 30, 35, 'png', '', 'T', false, 500, '', false, false, 0, false, false, false);
        }
        
        // Style du header
        $this->SetFont('helvetica', 'B', 16);
        $this->SetTextColor(4, 104, 24); // Couleur verte #046818
        $this->Cell(0, 10, 'FICHE INDIVIDUELLE RENSEIGNEMENT', 0, 1, 'C');
        
        // Ligne de séparation verte
        $this->SetLineStyle(array('width' => 0.5, 'color' => array(4, 104, 24)));
        $this->Line(15, 40, 195, 40);
        
        // Positionnement pour le contenu
        $this->SetY(45);
    }
    
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->SetTextColor(92, 107, 92); // Gris-vert #5c6b5c
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, 0, 'C');
    }
    
    public function createSection($title) {
        $this->SetFont('helvetica', 'B', 12);
        $this->SetTextColor(4, 104, 24); // Couleur verte #046818
        $this->Cell(0, 8, $title, 0, 1, 'L');
        $this->SetLineStyle(array('width' => 0.2, 'color' => array(4, 104, 24)));
        $this->Line(15, $this->GetY()-2, 195, $this->GetY()-2);
        $this->Ln(5);
        $this->SetTextColor(0, 0, 0); // Retour au noir pour le contenu
    }
}

// Création du PDF
$pdf = new ICF_PDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('ICF Gestion');
$pdf->SetAuthor('ICF Administration');
$pdf->SetTitle('Fiche Étudiant - ' . $nom . ' ' . $prenom);
$pdf->SetMargins(15, 50, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);
$pdf->SetAutoPageBreak(TRUE, 25);
$pdf->SetFont('helvetica', '', 10);

$pdf->AddPage();

// Style pour les tableaux
$pdf->SetFillColor(232, 240, 232); // Fond vert très clair pour les en-têtes
$pdf->SetDrawColor(4, 104, 24); // Bordure verte
$pdf->SetLineWidth(0.2);

// Informations personnelles
$pdf->createSection('INFORMATIONS PERSONNELLES');

$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(60, 7, 'NOM (EN MAJUSCULE) :', 0, 0);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(60, 7, strtoupper($nom), 0, 0);
$pdf->Cell(60, 7, 'ADRESSE : ' . $adresse, 0, 1);

$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(60, 7, 'PRENOM :', 0, 0);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(60, 7, $prenom, 0, 0);
$pdf->Cell(60, 7, 'CNSS N° :', 0, 1);

$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(60, 7, 'DATE & LIEU DE NAISSANCE :', 0, 0);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(60, 7, $date_naissance, 0, 0);
$pdf->Cell(60, 7, 'PIÈCE D\'IDENTITE N°: ' . $cin, 0, 1);

$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(60, 7, 'NATIONALITE :', 0, 0);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(60, 7, $nationalite, 0, 0);
$pdf->Cell(60, 7, 'DELIVRER LE : ' . $delivre_cin, 0, 1);
$pdf->Cell(60, 7, 'TELEPHONE : ' . $telephone, 0, 1);

$pdf->Cell(60, 7, 'SITUATION FAMILIALE : ' . $situation_familiale, 0, 1);
$pdf->Ln(5);

// En cas d'accident
$pdf->createSection('EN CAS D\'ACCIDENT PREVENIR');
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(40, 7, 'Mr :', 0, 0);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 7, $contact_urgence, 0, 1);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(40, 7, 'ADRESSE:', 0, 0);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 7, $adresse, 0, 1);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(40, 7, 'TELEPHONE :', 0, 0);
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 7, $telephone_p, 0, 1);
$pdf->Ln(10);

// Formation scolaire & professionnelle
$pdf->createSection('FORMATION SCOLAIRE & PROFESSIONNELLE');

// En-tête du tableau
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetFillColor(232, 240, 232);
$pdf->Cell(60, 7, 'ETABLISSEMENT', 1, 0, 'C', 1);
$pdf->Cell(50, 7, 'NIVEAU & SPECIALITE', 1, 0, 'C', 1);
$pdf->Cell(30, 7, 'DUREE', 1, 0, 'C', 1);
$pdf->Cell(30, 7, 'DIPLÔME', 1, 0, 'C', 1);
$pdf->Cell(20, 7, 'DATE', 1, 1, 'C', 1);

$pdf->SetFont('helvetica', '', 10);
$pdf->SetFillColor(255, 255, 255);

// Primaire
$pdf->Cell(60, 7, $eta_primary, 1);
$pdf->Cell(50, 7, '6ème Année Primaire', 1);
$pdf->Cell(30, 7, $dur_primary, 1);
$pdf->Cell(30, 7, '', 1);
$pdf->Cell(20, 7, '', 1, 1);

// Secondaire
$pdf->Cell(60, 7, $eta_secondary, 1);
$pdf->Cell(50, 7, '9ème Année de Base', 1);
$pdf->Cell(30, 7, $dur_secondary, 1);
$pdf->Cell(30, 7, '', 1);
$pdf->Cell(20, 7, '', 1, 1);

// Bac
$pdf->Cell(60, 7, $eta_secondary, 1);
$pdf->Cell(50, 7, $diplome, 1);
$pdf->Cell(30, 7, $dur_secondary, 1);
$pdf->Cell(30, 7, 'BAC', 1);
$pdf->Cell(20, 7, $date_diplome, 1, 1);

// Universités
foreach ($universites as $univ) {
    $pdf->Cell(60, 7, $univ['etablissement'], 1);
    $pdf->Cell(50, 7, $univ['specialite'], 1);
    $pdf->Cell(30, 7, $univ['duree'], 1);
    $pdf->Cell(30, 7, '', 1);
    $pdf->Cell(20, 7, '', 1, 1);
}

$pdf->Ln(10);

// Signature
$pdf->createSection('SIGNATURE');
$html = '
<style>
    .signature {
        margin-top: 20px;
        text-align: center;
        color: #046818;
        font-family: helvetica;
    }
    .signature-line {
        width: 200px;
        border-top: 1px solid #046818;
        margin: 5px auto;
    }
    .signature-date {
        display: inline-block;
        width: 100px;
        border-bottom: 1px solid #046818;
    }
</style>
<div class="signature">
    Fait à <span class="signature-date"></span>, le '.date('d/m/Y').'
    <div class="signature-line"></div>
    <div style="font-style:italic;margin-top:15px;">Signature de l\'étudiant</div>
</div>';

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Ln(10);

// Sauvegarde du PDF
$upload_dir = __DIR__ . '/uploads/documents/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}
$filename = 'fiche_'.$etudiant_id.'_'.date('Ymd_His').'.pdf';
$pdf->Output($upload_dir . $filename, 'F');

        // Enregistrement dans la base de données
        $stmt = $conn->prepare("INSERT INTO documents 
            (id_etudiant, type_document, nom_fichier, date_ajout) 
            VALUES (?, ?, ?, ?)");
        $stmt->execute([$etudiant_id, 'Fiche Etudiant', $filename, $date]);

        // Journalisation
        $stmt = $conn->prepare("INSERT INTO logs (id_user, action, date_action) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], "Ajout étudiant", $date]);
        

        $_SESSION['success_message'] = "Étudiant ajouté avec succès. Fiche PDF générée.";
        header("Location: stage_add.php?id_etudiant=$etudiant_id");
       
        exit;

    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Erreur base de données: " . $e->getMessage();
        header("Location: etudiant_add.php");
        exit;
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Erreur génération PDF: " . $e->getMessage();
        header("Location: etudiant_add.php");
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
<title>Ajouter un étudiant | Gestion Stagiaires</title>

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
    --card-radius: 12px;
    --transition: all 0.3s ease;
    --shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    --shadow-hover: 0 8px 20px rgba(0, 0, 0, 0.1);
  }

  * {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
  }

  body, html {
    height: 100%;
    font-family: 'Poppins';
    background-color: #f8faf8;
    color: #333;
    line-height: 1.6;
  }
  label{
    font-family: 'Poppins';
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

  .user-profile:hover {
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  }

  .user-profile img {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    margin-right: 10px;
    object-fit: cover;
    border: 2px solid #e0e8e0;
  }

  /* Form Container */
  .form-container {
    background: white;
    border-radius: var(--card-radius);
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: var(--shadow);
    
    max-width: 1000px;
    margin: 0 auto;
  }

  .form-header {
    text-align: center;
    margin-bottom: 30px;
  }

  .form-header h2 {
    color: #1a3a1a;
    margin-bottom: 10px;
    font-size: 22px;
  }

  .form-header p {
    color: #5c6b5c;
    font-size: 14px;
  }

  .form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 20px;
  }

  @media (max-width: 768px) {
    .form-grid {
      grid-template-columns: 1fr;
    }
  }

  .section-title {
    grid-column: 1 / -1;
    font-size: 18px;
    color: var(--primary-color);
    margin: 20px 0 10px;
    padding-bottom: 8px;
    border-bottom: 2px solid var(--accent-color);
  }

  .form-group {
    margin-bottom: 20px;
  }

  .form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #5c6b5c;
    font-size: 14px;
  }

  .form-control {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #e0e8e0;
    border-radius: 8px;
    font-size: 14px;
    background-color: #f8faf8;
    transition: all 0.3s;
  }

  .form-control:focus {
    border-color: var(--primary-color);
    outline: none;
    box-shadow: 0 0 0 3px rgba(4, 104, 24, 0.1);
  }

  /* Input with icon */
  .input-icon {
    position: relative;
  }

  .input-icon i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #7f8c8d;
    font-size: 16px;
  }

  .input-icon .form-control {
    padding-left: 40px;
  }

  /* Buttons */
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

  .btn-secondary {
    background: #6c757d;
    color: white;
  }

  .btn-secondary:hover {
    background: #5a6268;
  }

  .btn-block {
    display: block;
    width: 100%;
  }

  /* University section */
  .university-section {
    grid-column: 1 / -1;
    margin-top: 20px;
    border: 1px dashed #ddd;
    padding: 20px;
    border-radius: 8px;
    background-color: #f9f9f9;
  }

  .university-item {
    position: relative;
    margin-bottom: 20px;
    padding: 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    
  }

  .remove-university {
    position: absolute;
    top: 15px;
    right: 15px;
    background: var(--danger-color);
    color: white;
    border: none;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s;
    font-size: 14px;
  }

  .remove-university:hover {
    background: #d91a63;
    transform: scale(1.1);
  }

  /* School levels section */
  .school-levels {
    grid-column: 1 / -1;
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 20px;
  }

  .school-level {
    background: #ffffffff;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid var(--primary-color);
   
  }

  .school-level h4 {
    margin-bottom: 15px;
    color: var(--dark-color);
    font-size: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .university-item{
    border: 1px solid var(--primary-color);
  }
  .school-level h4 i {
    color: var(--info-color);
  }

  /* Duration field */
  .duration-field {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .duration-field input {
    flex: 1;
  }

  .duration-field span {
    white-space: nowrap;
    color: #6c757d;
    font-size: 14px;
  }
  input{
    style="font-family: 'Poppins';
  }

  /* Responsive */
  @media (max-width: 768px) {
    .main-content {
      padding: 20px;
    }
    
    .page-header {
      flex-direction: column;
      align-items: flex-start;
      gap: 20px;
    }
    
    .form-container {
      padding: 20px;
    }

    .school-levels {
      grid-template-columns: 1fr;
    }
  }
</style>
</head>

<body>

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
        <li><a href="etudiants.php"><i class="fas fa-users"></i><span>Étudiants</span></a></li>
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
    <h1><i class="fas fa-user-plus"></i> Ajouter un étudiant</h1>
    <div class="user-profile">
      <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['username'] ?? 'Admin') ?>&background=046818&color=fff" alt="User">
      <span><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></span>
    </div>
  </div>

  <div class="form-container">
    <div class="form-header">
      <h2>Nouvel étudiant</h2>
      <p>Remplissez le formulaire pour ajouter un nouvel étudiant</p>
    </div>
    
    <form method="POST" id="studentForm">
      <div class="form-grid">
        <h3 class="section-title">Informations Personnelles</h3>
        
        <div class="form-group">
          <label for="nom">Nom</label>
          <div class="input-icon">
            <i class="fas fa-user"></i>
            <input style="font-family: 'Poppins';" type="text" id="nom" name="nom" class="form-control" placeholder="Entrez le nom" required>
          </div>
        </div>

        <div class="form-group">
          <label for="prenom">Prénom</label>
          <div class="input-icon">
            <i class="fas fa-user"></i>
            <input style="font-family: 'Poppins';" type="text" id="prenom" name="prenom" class="form-control" placeholder="Entrez le prénom" required>
          </div>
        </div>

        <div class="form-group">
          <label for="sf">Situation Familiale</label>
          <div class="input-icon">
            <i class="fas fa-heart"></i>
            <select name="sf" style="font-family: 'Poppins';" id="sf" class="form-control">
              <option value="Célibataire" style="font-family: 'Poppins';">Célibataire</option>
              <option value="Marié(e)" style="font-family: 'Poppins';">Marié(e)</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label for="cin">CIN</label>
          <div class="input-icon" style="font-family: 'Poppins';">
            <i class="fas fa-id-card"></i>
            <input  style="font-family: 'Poppins';" type="text" id="cin" name="cin" class="form-control" placeholder="Entrez le CIN">
          </div>
        </div>

        <div class="form-group">
          <label for="deli_cin"> (Cin) Délivré le</label>
          <div class="input-icon" style="font-family: 'Poppins';">
            <i class="fas fa-calendar-day"></i>
            <input style="font-family: 'Poppins';" type="date" id="deli_cin" name="deli_cin" class="form-control">
          </div>
        </div>
        
        <div class="form-group">
          <label for="nas">Nationalité</label>
          <div class="input-icon" style="font-family: 'Poppins';">
            <i class="fas fa-globe"></i>
            <input style="font-family: 'Poppins';" type="text" id="nas" name="nas" class="form-control" placeholder="Entrez la nationalité">
          </div>
        </div>

        <div class="form-group">
          <label for="email">Email</label>
          <div class="input-icon" style="font-family: 'Poppins';">
            <i class="fas fa-envelope"></i>
            <input style="font-family: 'Poppins';" type="email" id="email" name="email" class="form-control" placeholder="Entrez l'email">
          </div>
        </div>

        <div class="form-group">
          <label for="acc">En cas d'accident prévenir</label>
          <div class="input-icon" style="font-family: 'Poppins';">
            <i class="fas fa-exclamation-triangle"></i>
            <input style="font-family: 'Poppins';" type="text" id="acc" name="acc" class="form-control" placeholder="Nom du contact d'urgence">
          </div>
        </div>

        <div class="form-group">
          <label for="telephone">Téléphone</label>
          <div class="input-icon">
            <i class="fas fa-phone"></i>
            <input style="font-family: 'Poppins';" type="text" id="telephone" name="telephone" class="form-control" placeholder="Téléphone de l'étudiant">
          </div>
        </div>

        <div class="form-group">
          <label for="telephonep">Téléphone du père</label>
          <div class="input-icon" style="font-family: 'Poppins';">
            <i class="fas fa-phone-alt"></i>
            <input style="font-family: 'Poppins';" type="text" id="telephonep" name="telephonep" class="form-control" placeholder="Téléphone du père">
          </div>
        </div>

        <div class="form-group">
          <label for="Adr">Adresse</label>
          <div class="input-icon">
            <i class="fas fa-map-marker-alt"></i>
            <input style="font-family: 'Poppins';" type="text" id="Adr" name="Adr" class="form-control" placeholder="Entrez l'adresse">
          </div>
        </div>

        <div class="form-group">
          <label for="date_naissance">Date de naissance</label>
          <div class="input-icon">
            <i class="fas fa-calendar"></i>
            <input type="date" id="date_naissance" name="date_naissance" class="form-control">
          </div>
        </div>

        <h3 class="section-title" style="font-family: 'Poppins';">Formation Scolaire & Professionnelle</h3>

        <div class="school-levels">
          <div class="school-level">
            <h4><i class="fas fa-school"></i> Niveau Primaire</h4>
            <div class="form-group">
              <label for="eta_sta_primary">Établissement</label>
              <input style="font-family: 'Poppins';" type="text" id="eta_sta_primary" name="eta_sta_primary" class="form-control" placeholder="Nom de l'établissement">
            </div>
            <div class="form-group">
              <label for="dur_primary">Durée</label>
              <div class="duration-field">
                <input style="font-family: 'Poppins';" type="text" id="dur_primary" name="dur_primary" class="form-control" placeholder="Durée en années">
                <span>Ans</span>
              </div>
            </div>
          </div>

          <div class="school-level">
            <h4><i class="fas fa-graduation-cap"></i> Niveau Secondaire</h4>
            <div class="form-group">
              <label for="eta_sta_secondary">Établissement</label>
              <input type="text" style="font-family: 'Poppins';" id="eta_sta_secondary" name="eta_sta_secondary" class="form-control" placeholder="Nom de l'établissement">
            </div>
            <div class="form-group">
              <label for="dur_secondary">Durée</label>
              <div class="duration-field">
                <input type="text" style="font-family: 'Poppins';" id="dur_secondary" name="dur_secondary" class="form-control" placeholder="Durée en années">
                <span style="font-family: 'Poppins';">Ans</span>
              </div>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label for="dip">Diplôme</label>
          <select style="font-family: 'Poppins';" name="dip" id="dip" class="form-control">
            <option  style="font-family: 'Poppins' ;" value="Baccalaureat Informatique">Baccalauréat Informatique</option>
            <option style="font-family: 'Poppins';" value="Baccalaureat Economie">Baccalauréat Economie</option>
            <option style="font-family: 'Poppins';" value="Baccalaureat science">Baccalauréat science</option>
            <option style="font-family: 'Poppins';" value="Baccalaureat mathématique">Baccalauréat mathématique</option>
            <option style="font-family: 'Poppins';" value="Baccalaureat Lettre">Baccalauréat Lettre</option>
          </select>
        </div>

        <div class="form-group">
          <label style="font-family: 'Poppins';" for="dat_dip">Date de diplôme</label>
          <input style="font-family: 'Poppins';" type="date" id="dat_dip" name="dat_dip" class="form-control">
        </div>

        <h3 style="font-family: 'Poppins';" class="section-title">Formation Universitaire</h3>
        
        <div id="universities-container">
          <!-- Universities will be added here dynamically -->
        </div>

        <div class="form-group full-width">
          <button type="button" id="add-university" style="font-family: 'Poppins';" class="btn btn-secondary">
            <i class="fas fa-plus"></i> Ajouter une université
          </button>
        </div>
      </div>

      <button type="submit" style="font-family: 'Poppins';" name="add" class="btn btn-primary btn-block">
        <i class="fas fa-save"></i> Enregistrer l'étudiant
      </button>
    </form>
  </div>
</div>
<script>
// University counter
let universityCounter = 0;
// Add university button
document.getElementById('add-university').addEventListener('click', function() {
  universityCounter++;
  
  const universityHtml = `
    <div class="university-item" id="university-${universityCounter}">
      <button type="button" class="remove-university" data-id="${universityCounter}">
        <i class="fas fa-times"></i>
      </button>
      
      <div class="form-grid">
        <div class="form-group">
          <label style="font-family: 'Poppins';" for="eta_unv_${universityCounter}">Établissement universitaire</label>
          <input type="text" style="font-family: 'Poppins';" id="eta_unv_${universityCounter}" name="eta_unv[]" class="form-control" placeholder="Nom de l'université">
        </div>
        
        <div class="form-group">
          <label for="spc_${universityCounter}">Spécialité</label>
          <input type="text" style="font-family: 'Poppins';" id="spc_${universityCounter}" name="spc[]" class="form-control" placeholder="Spécialité étudiée">
        </div>
        
        <div class="form-group">
          <label for="dur_unv_${universityCounter}">Durée</label>
          <div class="duration-field">
            <input type="text" style="font-family: 'Poppins';" id="dur_unv_${universityCounter}" name="dur_unv[]" class="form-control" placeholder="Durée en années">
            <span>Ans</span>
          </div>
        </div>
      </div>
    </div>
  `;
  
  document.getElementById('universities-container').insertAdjacentHTML('beforeend', universityHtml);
  
  // Add event listener to the new remove button
  document.querySelector(`#university-${universityCounter} .remove-university`).addEventListener('click', function() {
    this.closest('.university-item').remove();
  });
});

// Initialize any existing remove buttons (for page reloads)
document.querySelectorAll('.remove-university').forEach(button => {
  button.addEventListener('click', function() {
    this.closest('.university-item').remove();
  });
});
</script>
</body>
</html>