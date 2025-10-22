<?php
session_start();
require 'vendor/autoload.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;                                  
}

// Connexion à la base de données
try {
    $conn = new PDO("mysql:host=localhost;dbname=gestion_stagiaires;charset=utf8", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
$abc=date('d/m/Y à H:i');
$da = new DateTime();
$da->modify('+1 hour');
$abc = $da->format('d/m/Y à H:i');

$role = $_SESSION['role'];
// Variables
$search_results = [];
$selected_student = null;
$student_documents = [];
$message = '';

// Recherche d'étudiants
if (isset($_POST['search'])) {
    $search_term = '%' . $_POST['search_term'] . '%';
    
    $stmt = $conn->prepare("SELECT * FROM etudiants 
                          WHERE cin LIKE ? OR telephone LIKE ? OR CONCAT(nom, ' ', prenom) LIKE ?
                          ORDER BY nom, prenom");
    $stmt->execute([$search_term, $search_term, $search_term]);
    $search_results = $stmt->fetchAll();
}

// Sélection d'un étudiant
if (isset($_GET['id_etudiant'])) {
    $id_etudiant = intval($_GET['id_etudiant']);
    
    // Récupérer les infos de l'étudia
    $stmt = $conn->prepare("SELECT * FROM etudiants WHERE id_etudiant = ?");
    $stmt->execute([$id_etudiant]);
    $selected_student = $stmt->fetch();
    
    // Récupérer les documents de l'étudiant
    $stmt = $conn->prepare("SELECT * FROM documents WHERE id_etudiant = ? ORDER BY date_ajout DESC");
    $stmt->execute([$id_etudiant]);
    $student_documents = $stmt->fetchAll();
}

// Ajout d'un document
if (isset($_POST['add_document'])) {
    $id_etudiant = intval($_POST['id_etudiant']);
    $type_document = $_POST['type_document'];
    $date_ajout = date('Y-m-d H:i:s');
    
    // Gestion du fichier uploadé
    $file_name = '';
    if (isset($_FILES['document_file']) && $_FILES['document_file']['error'] == UPLOAD_ERR_OK){
        $upload_dir = 'uploads/documents/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['document_file']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid() .'.'. $file_extension;
        $file_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['document_file']['tmp_name'], $file_path)){
            // Insertion dans la base de données
            
            $stmt = $conn->prepare("INSERT INTO documents(id_etudiant, type_document, nom_fichier, date_ajout) VALUES (?, ?, ?, ?)");
            $stmt->execute([$id_etudiant, $type_document, $file_name, $date_ajout]);
            $date=Date("Y-m-d H:i:s");
            $stmt = $conn->prepare("INSERT INTO logs (id_user,action,date_action) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], "Téléchargement du {$type_document} {$selected_student['prenom']} {$selected_student['nom']}",$date]);
            $message = "Document ajouté avec succès!";
            header("Location: documents.php?id_etudiant=" . $id_etudiant);
            exit;
        } else {
            $message = "Erreur lors de l'upload du fichier.";
        }
    } else {
        $message = "Veuillez sélectionner un fichier valide.";
    }
}

// Suppression d'un document
if (isset($_POST['delete_document'])) {
    $id_document = intval($_POST['id_document']);
    $filename = $_POST['filename'];
    $id_etudiant = intval($_POST['id_etudiant']);
    
    try {
        // Supprimer le fichier du serveur
        $file_path = 'uploads/documents/' . $filename;
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        
        // Supprimer l'entrée de la base de données
        $stmt = $conn->prepare("DELETE FROM documents WHERE id_document = ?");
        $stmt->execute([$id_document]);
        
        $message = "Document supprimé avec succès!";
        header("Location: documents.php?id_etudiant=" . $id_etudiant);
        exit;
    } catch (Exception $e) {
        $message = "Erreur lors de la suppression : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Documents | ICF</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #046818;
            --secondary-color: #034612;
            --accent-color: #049a2c;
            --dark-color: #02310b;
            --light-color: #f8f9fa;
            --sidebar-collapsed: 80px;
            --sidebar-expanded: 250px;
            --success-color: #04c34d;
            --warning-color: #ff9f1c;
            --danger-color: #e63946;
            --info-color: #2ec4b6;
            --card-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        
        body {
           font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            min-height: 100vh;
            color: #333;
        }
        
        /* Sidebar inchangée */
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
            transition: var(--transition);
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
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu li a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: var(--transition);
            font-size: 15px;
            white-space: nowrap;
        }
        
        .sidebar-menu li a:hover, 
        .sidebar-menu li a.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .sidebar-menu li a i {
            font-size: 18px;
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
            transition: var(--transition);
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
            border-bottom: 1px solid #e0e4e8;
        }
        
        .page-header h1 {
            font-size: 28px;
            font-weight: 600;
            color: #2c3e50;
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
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: var(--transition);
            cursor: pointer;
        }
        
        .user-profile:hover {
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
        
        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
            border: 2px solid #e0e4e8;
        }
        
        /* Cartes modernes */
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            overflow: hidden;
            margin-bottom: 25px;
            background: white;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.12);
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid #f0f2f5;
            padding: 20px 25px;
            font-weight: 600;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .card-body {
            padding: 25px;
        }
        

        .search-panel {
    background-color: white;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: var(--card-shadow);
    border: 1px solid rgba(4, 104, 24, 0.1);
    transition: var(--transition);
}

.search-panel:hover {
    box-shadow: 0 8px 20px rgba(4, 104, 24, 0.1);
}

.search-header {
    margin-bottom: 1.25rem;
}

.search-title {
    font-size: 1.25rem;
    color: var(--dark-color);
    margin: 0;
    display: flex;
    align-items: center;
    font-weight: 600;
}

.search-icon {
    color: var(--accent-color);
    margin-right: 0.75rem;
    font-size: 1.1rem;
}

.search-group {
    display: flex;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #e0e0e0;
    transition: var(--transition);
}

.search-group:focus-within {
    border-color: var(--accent-color);
    box-shadow: 0 0 0 3px rgba(4, 154, 44, 0.1);
}

.search-field {
    flex: 1;
    border: none;
    padding: 0.75rem 1rem;
    font-size: 0.95rem;
    color: #333;
    outline: none;
}

.search-field::placeholder {
    color: #9e9e9e;
}

.search-submit {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background-color: var(--primary-color);
    color: white;
    border: none;
    padding: 0 1.25rem;
    cursor: pointer;
    transition: var(--transition);
    font-weight: 500;
}

.search-submit:hover {
    background-color: var(--secondary-color);
}

.search-submit i {
    font-size: 0.9rem;
}

@media (max-width: 576px) {
    .search-submit span {
        display: none;
    }
    .search-submit i {
        margin-right: 0;
    }
}
        
        /* Profil étudiant */
        .student-profile {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 30px 20px;
            text-align: center;
            background: white;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
        }
        
        .student-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
            margin-bottom: 20px;
        }
        
        .student-info h4 {
            font-weight: 700;
            margin-bottom: 10px;
            color: #2c3e50;
        }
        
        .student-meta {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 12px;
            margin-top: 15px;
        }
        
        .student-meta .badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
            background: #f0f2f5;
            color: #5c6b7a;
        }
        
        /* Formulaire ajout document */
        .form-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: var(--card-shadow);
        }
        
        .form-card h5 {
            font-size: 18px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #2c3e50;
        }
        
        .form-card .form-label {
            font-weight: 500;
            margin-bottom: 8px;
            color: #2c3e50;
        }
        
        .form-card .form-control, 
        .form-card .form-select {
            padding: 12px 15px;
            border: 1px solid #e0e4e8;
            border-radius: 12px;
            transition: var(--transition);
        }
        
        .form-card .form-control:focus, 
        .form-card .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(4, 104, 24, 0.15);
        }
        
        .form-card .btn-primary {
            background: var(--primary-color);
            border: none;
            padding: 12px 20px;
            border-radius: 12px;
            font-weight: 500;
            transition: var(--transition);
            width: 100%;
        }
        
        .form-card .btn-primary:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(4, 104, 24, 0.3);
        }
        
        /* Liste des documents */
        .document-list {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
        }
        
        .document-item {
            padding: 20px;
            border-bottom: 1px solid #f0f2f5;
            transition: var(--transition);
            display: flex;
            align-items: center;
        }
        
        .document-item:last-child {
            border-bottom: none;
        }
        
        .document-item:hover {
            background: #f8fafc;
        }
        
        .document-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 20px;
            flex-shrink: 0;
        }
        
        .document-icon.pdf {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
        }
        
        .document-icon.word {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }
        
        .document-icon.image {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
        }
        
        .document-icon.default {
            background: rgba(139, 92, 246, 0.1);
            color: #8b5cf6;
        }
        
        .document-info {
            flex-grow: 1;
        }
        
        .document-info h6 {
            font-weight: 600;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        
        .document-info p {
            font-size: 13px;
            color: #6b7280;
            margin: 0;
        }
        
        .document-actions {
            display: flex;
            gap: 8px;
        }
        
        .document-actions .btn {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }
        
        .document-actions .btn-view {
            background: rgba(4, 104, 24, 0.1);
            color: var(--primary-color);
        }
        
        .document-actions .btn-view:hover {
            background: var(--primary-color);
            color: white;
        }
        
        .document-actions .btn-download {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
        }
        
        .document-actions .btn-download:hover {
            background: var(--success-color);
            color: white;
        }
        
        .document-actions .btn-delete {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
        }
        
        .document-actions .btn-delete:hover {
            background: var(--danger-color);
            color: white;
        }
        
        /* État vide */
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 50px 30px;
        }
        
        .empty-state i {
            font-size: 64px;
            color: #d1d9e0;
            margin-bottom: 20px;
        }
        
        .empty-state h4 {
            font-size: 22px;
            margin-bottom: 10px;
            color: #2c3e50;
        }
        
        .empty-state p {
            color: #6b7280;
            max-width: 500px;
            margin: 0 auto 20px;
        }
        
        /* Tableau de résultats */
        .results-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .results-table th {
            background: #f8fafc;
            padding: 15px 20px;
            text-align: left;
            font-weight: 600;
            color: #5c6b7a;
            border-bottom: 1px solid #e0e4e8;
        }
        
        .results-table td {
            padding: 15px 20px;
            border-bottom: 1px solid #f0f2f5;
        }
        
        .results-table tr:last-child td {
            border-bottom: none;
        }
        
        .results-table tr:hover {
            background: #f8fafc;
        }
        
        .student-avatar-sm {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .btn-open {
            padding: 8px 16px;
            border-radius: 12px;
            background: rgba(4, 104, 24, 0.1);
            color: var(--primary-color);
            font-weight: 500;
            transition: var(--transition);
            text-decoration: none;
        }
        
        .btn-open:hover {
            background: var(--primary-color);
            color: white;
        }
        
        /* Alertes */
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            border: none;
        }
        
        .alert-success {
            background: rgba(16, 185, 129, 0.15);
            color: var(--success-color);
        }
        
        .alert-danger {
            background: rgba(239, 68, 68, 0.15);
            color: var(--danger-color);
        }
        
        /* Responsive */
        @media (max-width: 992px) {
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
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .user-profile {
                width: 100%;
                justify-content: center;
            }
        }
        .pdf-modal {
        display: none;
        position: fixed;
        z-index: 1050;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.8);
        overflow: auto;
        animation: fadeIn 0.3s;
    }
     .page-header h1 {
    font-size: 28px;
    font-weight: 600;
    color: #1a3a1a;
    display: flex;
    align-items: center;
    gap: 10px;
  }
    @keyframes fadeIn {
        from {opacity: 0;}
        to {opacity: 1;}
    }
    
    .pdf-modal-content {
        position: relative;
        background-color: #f8f9fa;
        margin: 5% auto;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 5px 30px rgba(0,0,0,0.3);
        width: 90%;
        max-width: 900px;
        max-height: 85vh;
        display: flex;
        flex-direction: column;
    }
    
    .pdf-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 15px;
        border-bottom: 1px solid #e0e4e8;
        margin-bottom: 15px;
    }
    
    .pdf-modal-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #046818;
    }
    
    .pdf-modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        color: #6c757d;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .pdf-modal-close:hover {
        color: #046818;
        transform: rotate(90deg);
    }
    
    .pdf-modal-body {
        flex: 1;
        overflow: auto;
        background: white;
        border-radius: 8px;
        padding: 10px;
        box-shadow: inset 0 0 10px rgba(0,0,0,0.05);
    }
    
    .pdf-embed {
        width: 100%;
        height: 70vh;
        border: none;
        border-radius: 6px;
    }
    
    .pdf-modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding-top: 15px;
        border-top: 1px solid #e0e4e8;
        margin-top: 15px;
    }
    
    .pdf-download-btn {
        background-color: #046818;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }
    
    .pdf-download-btn:hover {
        background-color: #034512;
        transform: translateY(-2px);
    }
    </style>
</head>
<body>
    <div class="sidebar" >
        <div class="sidebar-header" >
            <div class="logo">
                <i class="fas fa-user-graduate"></i>
                <span>ICF</span>
            </div>
        </div>
        
        <div class="sidebar-menu">
            <h3 >Menu Principal</h3>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> <span>Tableau de bord</span></a></li>
              
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

    <div class="main-content">
        <div class="container-fluid">
            <div class="page-header">
                <h1><i class="fas fa-file-alt  me-2"></i>Gestion des Documents</h1>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['username'] ?? 'Admin') ?>&background=046818&color=fff" 
                         alt="User">
                    <span><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></span>
                </div>
            </div>

            <!-- Section de recherche -->
         <div class="search-panel">
    <div class="search-header">
        <h3 class="search-title">
            <span class="search-icon"><i class="fas fa-search"></i></span>
            Rechercher un étudiant
        </h3>
    </div>
    <form method="POST" class="search-form">
        <div class="search-group">
            <input type="text" name="search_term" class="search-field" 
                   placeholder="CIN, téléphone ou nom..." 
                   value="<?= isset($_POST['search_term']) ? htmlspecialchars($_POST['search_term']) : '' ?>">
            <button type="submit" name="search" class="search-submit">
                <i class="fas fa-search"></i>
                <span>Rechercher</span>
            </button>
        </div>
    </form>
</div>

            <!-- Résultats de recherche -->
            <?php if (!empty($search_results)): ?>
            <div class="card">
                <div class="card-header">
                    <div><i class="fas fa-users me-2"></i>Résultats de la recherche</div>
                    <span class="badge bg-primary rounded-pill"><?= count($search_results) ?> résultat(s)</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="results-table">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Nom</th>
                                    <th>CIN</th>
                                    <th>Téléphone</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($search_results as $student): ?>
                                <tr>
                                    <td>
                                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($student['prenom'] . '+' . $student['nom']) ?>&background=random" 
                                             alt="Student" class="student-avatar-sm">
                                    </td>
                                    <td><?= htmlspecialchars($student['prenom'] . ' ' . $student['nom']) ?></td>
                                    <td><?= htmlspecialchars($student['cin']) ?></td>
                                    <td><?= htmlspecialchars($student['telephone']) ?></td>
                                    <td>
                                        <a href="documents.php?id_etudiant=<?= $student['id_etudiant'] ?>" 
                                           class="btn-open">
                                            <i class="fas fa-folder-open me-1"></i>Ouvrir
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Section étudiant sélectionné -->
            <?php if ($selected_student): ?>
            <div class="row">
                <div class="col-lg-4">
                    <!-- Profil étudiant -->
                    <div class="student-profile">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($selected_student['prenom'] . '+' . $selected_student['nom']) ?>&background=random&size=200" 
                             alt="Student" class="student-avatar">
                        <div class="student-info">
                            <h4><?= htmlspecialchars($selected_student['prenom'] . ' ' . $selected_student['nom']) ?></h4>
                            <p class="text-muted">
                                <i class="fas fa-envelope me-2"></i><?= htmlspecialchars($selected_student['email']) ?>
                            </p>
                            <div class="student-meta">
                                <span class="badge">
                                    <i class="fas fa-id-card me-1"></i><?= htmlspecialchars($selected_student['cin']) ?>
                                </span>
                                <span class="badge">
                                    <i class="fas fa-phone me-1"></i><?= htmlspecialchars($selected_student['telephone']) ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Formulaire ajout document -->
                    <div class="form-card mt-5">
                        <h5><i class="fas fa-plus-circle me-2"></i>Ajouter un document</h5>
                        <?php if ($message && isset($_POST['add_document'])): ?>
                        <div class="alert alert-<?= strpos($message, 'succès') ? 'success' : 'danger' ?>">
                            <i class="fas <?= strpos($message, 'succès') ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                            <?= htmlspecialchars($message) ?>
                        </div>
                        <?php endif; ?>
                        
                        <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                            <input type="hidden" name="id_etudiant" value="<?= $selected_student['id_etudiant'] ?>">
                            
                            <div class="mb-3">
                                <label class="form-label">Type de document *</label>
                                <select name="type_document" class="form-select" required>
                                    <option value="">Sélectionner un type...</option>
                                    <option value="Rapport de stage">Rapport de stage</option>
                                    <option value="Lettre d'affectation">Lettre d'affectation</option>
                                    <option value="Copie de CIN">Copie de CIN</option>
                                    <option value="Attestation de stage">Attestation de stage</option>
                                    <option value="Fiche Etudiant">Fiche Etudiant</option>
                                    <option value="Convention de stage">Convention de stage</option>
                                    <option value="Autre">Autre document</option>
                                </select>
                                <div class="invalid-feedback">
                                    Veuillez sélectionner un type de document
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Fichier du document *</label>
                                <input type="file" name="document_file" class="form-control" 
                                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                                <small class="text-muted">Formats acceptés: PDF, Word, JPG, PNG, DOCX (Max 5MB)</small>
                                <div class="invalid-feedback">
                                    Veuillez sélectionner un fichier valide
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">Date d'ajout</label>
                                <input type="text" class="form-control" value="<?= $abc ?>" readonly>
                            </div>
                            
                            <button type="submit" name="add_document" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Enregistrer
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Liste des documents -->
                <div class="col-lg-8">
                    <div class="document-list">
                        <div class="card-header">
                            <div><i class="fas fa-folder me-2"></i>Documents de l'étudiant</div>
                            <span class="badge bg-primary rounded-pill">
                                <?= count($student_documents) ?> document(s)
                            </span>
                        </div>
                        
                        <?php if (empty($student_documents)): ?>
                            <div class="empty-state">
                                <i class="fas fa-folder-open text-muted"></i>
                                <h4>Aucun document trouvé</h4>
                                <p>Commencez par ajouter des documents pour cet étudiant</p>
                            </div>
                        <?php else: ?>
                            <div class="card-body p-0">
                                <?php foreach ($student_documents as $doc): ?>
                                <div class="document-item">
                                    <?php
                                    $ext = pathinfo($doc['nom_fichier'], PATHINFO_EXTENSION);
                                    $iconClass = 'default';
                                    
                                    if (in_array($ext, ['pdf'])) {
                                        $iconClass = 'pdf';
                                    } elseif (in_array($ext, ['doc', 'docx'])) {
                                        $iconClass = 'word';
                                    } elseif (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                                        $iconClass = 'image';
                                    }
                                    ?>
                                    <div class="document-icon <?= $iconClass ?>">
                                        <i class="fas fa-file-<?= 
                                            ($iconClass == 'pdf') ? 'pdf' : 
                                            (($iconClass == 'word') ? 'word' : 
                                            (($iconClass == 'image') ? 'image' : 'alt')) 
                                        ?>"></i>
                                    </div>
                                    
                                    <div class="document-info">
                                        <h6><?= htmlspecialchars($doc['type_document']) ?></h6>
                                        <p>Ajouté le <?= date('d/m/Y à H:i', strtotime($doc['date_ajout'])) ?></p>
                                    </div>
                                    
                                    <div class="document-actions">
                                        <a href="#" class="btn btn-view" onclick="openPdfModal('uploads/documents/<?= htmlspecialchars($doc['nom_fichier']) ?>', '<?= htmlspecialchars($doc['type_document']) ?>')">
    <i class="fas fa-eye"></i>
</a>
                                        <a href="uploads/documents/<?= htmlspecialchars($doc['nom_fichier']) ?>" 
                                           download class="btn btn-download">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="id_document" value="<?= $doc['id_document'] ?>">
                                            <input type="hidden" name="filename" value="<?= htmlspecialchars($doc['nom_fichier']) ?>">
                                            <input type="hidden" name="id_etudiant" value="<?= $selected_student['id_etudiant'] ?>">
                                            <button type="submit" name="delete_document" class="btn btn-delete"
                                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce document ?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php elseif (empty($search_results) && !isset($_GET['id_etudiant'])): ?>
                <div class="card">
                    <div class="empty-state">
                        <i class="fas fa-search text-primary"></i>
                        <h4>Rechercher un étudiant</h4>
                        <p>Utilisez le formulaire ci-dessus pour trouver un étudiant et gérer ses documents</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<div id="pdfModal" class="pdf-modal">
    <div class="pdf-modal-content">
        <div class="pdf-modal-header">
            <h3 class="pdf-modal-title" id="pdfModalTitle"></h3>
            <button class="pdf-modal-close" onclick="closePdfModal()">&times;</button>
        </div>
        <div class="pdf-modal-body">
            <embed id="pdfEmbed" class="pdf-embed" src="" type="application/pdf">
        </div>
        <div class="pdf-modal-footer">
            <a id="pdfDownloadLink" href="#" class="pdf-download-btn" download>
                <i class="fas fa-download"></i> Télécharger
            </a>
        </div>
    </div>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validation du formulaire
        (function () {
            'use strict'
            
            var forms = document.querySelectorAll('.needs-validation')
            
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
        
        // Animation pour les documents
        document.addEventListener('DOMContentLoaded', function() {
            const docItems = document.querySelectorAll('.document-item');
            docItems.forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateY(20px)';
                item.style.transition = `all 0.5s ease ${index * 0.1}s`;
                
                setTimeout(() => {
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                }, 100);
            });
        });
        const searchInput = document.querySelector('input[name="search"]');
  let searchTimeout;
  
  searchInput.addEventListener('input', function(e) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
      this.form.submit();
    }, 500);
  });
function openPdfModal(pdfUrl, title) {
    const modal = document.getElementById('pdfModal');
    const embed = document.getElementById('pdfEmbed');
    const titleElement = document.getElementById('pdfModalTitle');
    const downloadLink = document.getElementById('pdfDownloadLink');
    
    // Configure le contenu de la modale
    embed.src = pdfUrl;
    titleElement.textContent = title;
    downloadLink.href = pdfUrl;
    downloadLink.download = pdfUrl.split('/').pop();
    
    // Affiche la modale
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
    
    // Ajoute un effet de fondu
    setTimeout(() => {
        modal.style.opacity = '1';
    }, 10);
}

function closePdfModal() {
    const modal = document.getElementById('pdfModal');
    
    // Effet de fondu
    modal.style.opacity = '0';
    
    // Cache la modale après l'animation
    setTimeout(() => {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }, 300);
}

// Ferme la modale si on clique en dehors du contenu
window.onclick = function(event) {
    const modal = document.getElementById('pdfModal');
    if (event.target === modal) {
        closePdfModal();
    }
}

// Ferme la modale avec la touche ESC
document.addEventListener('keydown', function(event) {
    const modal = document.getElementById('pdfModal');
    if (event.key === 'Escape' && modal.style.display === 'block') {
        closePdfModal();
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
    </script>
</body>
</html>