<?php
session_start();

// Vérifier si l'utilisateur est connecté et est un admin
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Seuls les admins et admin_superviseur peuvent accéder à cette page
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'admin_superviseur') {
    header("Location: dashboard.php");
    exit;
}

$conn = new PDO("mysql:host=localhost;dbname=gestion_stagiaires;charset=utf8", "root", "");

// Récupérer la liste des services pour le formulaire
$services = $conn->query("SELECT id_service, nom_service FROM services")->fetchAll();

// Traitement du formulaire d'ajout
if (isset($_POST['ajouter'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $id_service = $_POST['id_service'] ?? null;

    // Validation simple
    if (empty($username) || empty($password) || empty($email) || empty($role)) {
        $erreur = "Tous les champs obligatoires doivent être remplis";
    } else {
        // Vérifier si l'utilisateur existe déjà
        $stmt = $conn->prepare("SELECT id_user FROM utilisateurs WHERE username = ? OR email = ? ORDER BY (id_user) ASC");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $erreur = "Un utilisateur avec ce nom ou cet email existe déjà";
        } else {
            // Insertion du nouvel utilisateur
            $stmt = $conn->prepare("INSERT INTO utilisateurs (username, password, email, role, id_service) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$username, $password, $email, $role, $id_service])) {
                $succes = "Utilisateur ajouté avec succès";
                // Journalisation
                $date = Date("Y-m-d H:i:s");
                $stmt = $conn->prepare("INSERT INTO logs (id_user, action, date_action) VALUES (?, ?, ?)");
                $stmt->execute([$_SESSION['user_id'], "Ajout de l'utilisateur $username", $date]);
                
                // Recharger la page pour afficher le nouvel utilisateur
                header("Location: utilisateurs.php");
                exit;
            } else {
                $erreur = "Une erreur s'est produite lors de l'ajout";
            }
        }
    }
}

// Traitement de la suppression
if (isset($_GET['supprimer'])) {
    $id = $_GET['supprimer'];
    
    // Ne pas permettre de supprimer son propre compte
    if ($id == $_SESSION['user_id']) {
        $erreur = "Vous ne pouvez pas supprimer votre propre compte";
    } else {
        $stmt = $conn->prepare("DELETE FROM utilisateurs WHERE id_user = ?");
        if ($stmt->execute([$id])) {
            $succes = "Utilisateur supprimé avec succès";
            // Journalisation
            $date = Date("Y-m-d H:i:s");
            $stmt = $conn->prepare("INSERT INTO logs (id_user, action, date_action) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], "Suppression de l'utilisateur ID $id", $date]);
            
            // Recharger la page pour actualiser la liste
            header("Location: utilisateurs.php");
            exit;
        } else {
            $erreur = "Erreur lors de la suppression";
        }
    }
}

// Récupérer la liste des utilisateurs avec leurs services
$utilisateurs = $conn->query("
    SELECT u.*, s.nom_service 
    FROM utilisateurs u 
    LEFT JOIN services s ON u.id_service = s.id_service
    ORDER BY 
        CASE 
            WHEN u.role = 'admin' THEN 1
            WHEN u.role = 'admin_super' THEN 2
            ELSE 3
        END,
        u.username
    
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs | ICF Gabès</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
            font-family: 'Poppins';
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
        .delete-confirmation-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .delete-confirmation-modal {
            background: white;
            border-radius: 16px;
            padding: 0;
            width: 90%;
            max-width: 450px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            animation: modalAppear 0.3s ease-out;
        }

        @keyframes modalAppear {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(-20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .delete-confirmation-header {
            background: linear-gradient(135deg, var(--danger-color), #c1121f);
            color: white;
            padding: 20px 25px;
            border-radius: 16px 16px 0 0;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .delete-confirmation-header i {
            font-size: 24px;
        }

        .delete-confirmation-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
        }

        .delete-confirmation-body {
            padding: 25px;
            border-bottom: 1px solid #f0f2f5;
        }

        .delete-confirmation-body p {
            margin: 0 0 15px 0;
            color: #495057;
            line-height: 1.5;
        }

        .warning-text {
            color: var(--danger-color) !important;
            font-weight: 500;
            font-size: 14px;
        }

        .delete-confirmation-actions {
            padding: 20px 25px;
            display: flex;
            gap: 15px;
            justify-content: flex-end;
        }

        .btn-cancel {
            background-color: #6c757d;
            color: white;
        }

        .btn-cancel:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }

        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }

        .btn-danger:hover {
            background-color: #d90429;
            transform: translateY(-2px);
        }

        /* Animation pour le bouton de suppression */
        .btn-icon-danger {
            transition: all 0.3s ease;
        }

        .btn-icon-danger:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(230, 57, 70, 0.3);
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
            letter-spacing: 0.5px;
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
            padding: 8px 15px;
            border-radius: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
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
            border: 2px solid #e0e4e8;
        }

        /* Alert Messages */
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background-color: #f6ffed;
            color: var(--success-color);
            border-left: 4px solid var(--success-color);
        }

        .alert-error {
            background-color: #fff1f0;
            color: var(--danger-color);
            border-left: 4px solid var(--danger-color);
        }

        .alert i {
            font-size: 18px;
        }

        /* Card Styles */
        .card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            transition: all 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f2f5;
        }

        .card-header h2 {
            margin: 0;
            font-size: 20px;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #495057;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s;
            background-color: #f8f9fa;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(4, 104, 24, 0.2);
            background-color: white;
        }

        .form-row {
            display: flex;
            gap: 20px;
        }

        .form-row .form-group {
            flex: 1;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--dark-color);
            transform: translateY(-2px);
        }

        /* Table Styles */
        .table-responsive {
            overflow-x: auto;
            margin-bottom: 20px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th, .table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #f0f2f5;
            vertical-align: middle;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
            text-transform: uppercase;
            font-size: 13px;
            letter-spacing: 0.5px;
        }

        .table tr:hover {
            background-color: #f8fafc;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-primary {
            background-color: #e6f7ff;
            color: var(--primary-color);
        }

        .badge-success {
            background-color: #f6ffed;
            color: var(--success-color);
        }

        .badge-info {
            background-color: #e6f7ff;
            color: #1890ff;
        }

        .badge-warning {
            background-color: #fff7e6;
            color: var(--warning-color);
        }

        .badge-danger {
            background-color: #fff1f0;
            color: var(--danger-color);
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #f8fafc;
            color: var(--primary-color);
            border: 1px solid #e0e4e8;
            transition: all 0.2s;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-icon:hover {
            background: var(--primary-color);
            color: white;
            transform: scale(1.1);
        }

        .btn-icon-danger {
            color: var(--danger-color);
            border-color: #ffccc7;
        }

        .btn-icon-danger:hover {
            background: var(--danger-color);
            color: white;
        }

        .btn-icon-success {
            color: var(--success-color);
            border-color: #b7eb8f;
        }

        .btn-icon-success:hover {
            background: var(--success-color);
            color: white;
        }

        /* Loading spinner */
        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Toast notification */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            background-color: var(--primary-color);
            color: white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 10px;
            transform: translateX(150%);
            transition: transform 0.3s ease;
        }

        .toast.show {
            transform: translateX(0);
        }

        .toast-success {
            background-color: var(--success-color);
        }

        .toast-error {
            background-color: var(--danger-color);
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .main-content {
                padding: 20px;
            }

            .form-row {
                flex-direction: column;
                gap: 0;
            }

            .table td, .table th {
                padding: 8px 10px;
                font-size: 14px;
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
                <span> ICF</span>
            </div>
        </div>
        
        <div class="sidebar-menu">
            <h3>Menu Principal</h3>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> <span>Tableau de bord</span></a></li>
                <li><a href="etudiants.php"><i class="fas fa-users"></i> <span>Étudiants</span></a></li>
                <li><a href="documents.php"><i class="fas fa-file-alt"></i> <span>Documents</span></a></li>
                <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'admin_super'): ?>
                <li><a href="utilisateurs.php" class="active"><i class="fas fa-user-cog"></i> <span>Utilisateurs</span></a></li>
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
            <h1><i class="fas fa-user-cog"></i> Gestion des Utilisateurs</h1>
            <div class="user-profile">
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['username']) ?>&background=046818&color=fff" alt="User">
                <span>
                    <?= htmlspecialchars($_SESSION['username']) ?> 
                    (<?= $_SESSION['role'] === 'admin' ? 'Admin' : ($_SESSION['role'] === 'admin_super' ? 'Admin Superviseur' : 'Responsable') ?>)
                </span>
            </div>
        </div>

        <?php if (isset($erreur)): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <span><?= $erreur ?></span>
        </div>
        <?php endif; ?>

        <?php if (isset($succes)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <span><?= $succes ?></span>
        </div>
        <?php endif; ?>
<div id="deleteConfirmation" class="delete-confirmation-overlay" style="display: none;">
            <div class="delete-confirmation-modal">
                <div class="delete-confirmation-header">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Confirmer la suppression</h3>
                </div>
                <div class="delete-confirmation-body">
                    <p>Êtes-vous sûr de vouloir supprimer l'utilisateur "<span id="userToDeleteName"></span>" ?</p>
                    <p class="warning-text">Cette action est irréversible.</p>
                </div>
                <div class="delete-confirmation-actions">
                    <button id="cancelDelete" class="btn btn-cancel">
                        <i class="fas fa-times"></i> Annuler
                    </button>
                    <button id="confirmDelete" class="btn btn-danger">
                        <i class="fas fa-trash-alt"></i> Supprimer
                    </button>
                </div>
            </div>
        </div>
        <!-- Formulaire d'ajout d'utilisateur -->
        <?php if ($_SESSION['role'] === 'admin'): ?>
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-user-plus"></i> Ajouter un nouvel utilisateur</h2>
            </div>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="username">Nom d'utilisateur *</label>
                        <input type="text" style="font-family: 'Poppins';" id="username" name="username" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Mot de passe *</label>
                        <input type="password" style="font-family: 'Poppins';"  id="password" name="password" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" style="font-family: 'Poppins';"  id="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="role">Rôle *</label>
                        <select style="font-family: 'Poppins';"  id="role" name="role" class="form-control" required>
                            <option value="" style="font-family: 'Poppins';" >Sélectionner un rôle</option>
                            <option value="admin" style="font-family: 'Poppins';" >Administrateur</option>
                            <option value="admin_super" style="font-family: 'Poppins';" >Admin Superviseur</option>
                            <option value="superviseur" style="font-family: 'Poppins';" >Responsable de service</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="id_service">Service (si responsable)</label>
                    <select style="font-family: 'Poppins';"  id="id_service"  name="id_service" class="form-control">
                        <option style="font-family: 'Poppins';"  value="">Aucun service</option>
                        <?php foreach ($services as $service): ?>
                        <option style="font-family: 'Poppins';"  value="<?= $service['id_service'] ?>"><?= htmlspecialchars($service['nom_service']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" style="font-family: 'Poppins';"  name="ajouter" class="btn btn-primary">
                    <i class="fas fa-save"></i> Ajouter l'utilisateur
                </button>
            </form>
        </div>
        <?php endif; ?>

        <!-- Liste des utilisateurs -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-users"></i> Liste des utilisateurs</h2>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                           
                            <th>Nom d'utilisateur</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Service</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($utilisateurs as $user): ?>
                        <tr data-user-id="<?= $user['id_user'] ?>">
                           
                            <td class="editable" data-field="username">
                                <span class="view-mode"><?= htmlspecialchars($user['username']) ?></span>
                                <input  type="text" class="edit-mode form-control" value="<?= htmlspecialchars($user['username']) ?>" style="display:none;">
                            </td>
                            <td class="editable" data-field="email">
                                <span class="view-mode"><?= htmlspecialchars($user['email']) ?></span>
                                <input type="email" class="edit-mode form-control" value="<?= htmlspecialchars($user['email']) ?>" style="display:none;">
                            </td>
                            <td class="editable" data-field="role">
                                <span class="view-mode">
                                    <?php if ($user['role'] === 'admin'): ?>
                                        <span class="badge badge-primary">Administrateur</span>
                                    <?php elseif ($user['role'] === 'admin_super'): ?>
                                        <span class="badge badge-info">Admin Superviseur</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">Responsable</span>
                                    <?php endif; ?>
                                </span>
                                <select class="edit-mode form-control" style="display:none;">
                                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Administrateur</option>
                                    <option value="admin_super" <?= $user['role'] === 'admin_super' ? 'selected' : '' ?>>Admin Superviseur</option>
                                    <option value="superviseur" <?= $user['role'] === 'superviseur' ? 'selected' : '' ?>>Responsable</option>
                                </select>
                            </td>
                            <td class="editable" data-field="id_service">
                                <span class="view-mode"><?= $user['nom_service'] ? htmlspecialchars($user['nom_service']) : '-' ?></span>
                                <select class="edit-mode form-control" style="display:none;">
                                    <option value="">Aucun service</option>
                                    <?php foreach ($services as $service): ?>
                                    <option value="<?= $service['id_service'] ?>" <?= $user['id_service'] == $service['id_service'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($service['nom_service']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <?php if ($_SESSION['role'] === 'admin' || ($_SESSION['role'] === 'admin_super' && $user['role'] !== 'admin')): ?>
                                    <button class="btn-icon btn-edit" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-icon btn-save btn-icon-success" title="Enregistrer" style="display:none;">
                                        <i class="fas fa-save"></i>
                                        <div class="spinner"></div>
                                    </button>
                                    <button class="btn-icon btn-cancel" title="Annuler" style="display:none;">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <?php if ($_SESSION['role'] === 'admin' && $user['id_user'] != $_SESSION['user_id']): ?>
                                    <a href="utilisateurs.php?supprimer=<?= $user['id_user'] ?>" class="btn-icon btn-icon-danger" title="Supprimer" 
                                    >
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
      <div id="toast" class="toast">
        <i class="fas fa-check-circle"></i>
        <span id="toast-message">Opération réussie</span>
    </div>
     <script>
        document.addEventListener('DOMContentLoaded', function() {
            let userToDelete = null;

            // Gestion des clics sur les boutons de suppression
            document.querySelectorAll('.btn-icon-danger').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const row = this.closest('tr');
                    const userId = row.dataset.userId;
                    const userName = row.querySelector('[data-field="username"] .view-mode').textContent;
                    
                    // Stocker les informations de l'utilisateur à supprimer
                    userToDelete = {
                        id: userId,
                        name: userName,
                        url: this.getAttribute('href')
                    };
                    
                    // Afficher la confirmation
                    showDeleteConfirmation(userName);
                });
            });

            // Afficher la modal de confirmation
            function showDeleteConfirmation(userName) {
                document.getElementById('userToDeleteName').textContent = userName;
                document.getElementById('deleteConfirmation').style.display = 'flex';
            }

            // Cacher la modal de confirmation
            function hideDeleteConfirmation() {
                document.getElementById('deleteConfirmation').style.display = 'none';
                userToDelete = null;
            }

            // Gestion du bouton Annuler
            document.getElementById('cancelDelete').addEventListener('click', hideDeleteConfirmation);

            // Gestion du bouton Confirmer
            document.getElementById('confirmDelete').addEventListener('click', function() {
                if (userToDelete) {
                    // Rediriger vers l'URL de suppression
                    window.location.href = userToDelete.url;
                }
            });

            // Fermer la modal en cliquant en dehors
            document.getElementById('deleteConfirmation').addEventListener('click', function(e) {
                if (e.target === this) {
                    hideDeleteConfirmation();
                }
            });

            // Le reste du code JavaScript existant reste inchangé
            document.querySelectorAll('.btn-edit').forEach(btn => {
                btn.addEventListener('click', function() {
                    const row = this.closest('tr');
                    enableEditMode(row);
                });
            });

            document.querySelectorAll('.btn-cancel').forEach(btn => {
                btn.addEventListener('click', function() {
                    const row = this.closest('tr');
                    disableEditMode(row);
                });
            });

            document.querySelectorAll('.btn-save').forEach(btn => {
                btn.addEventListener('click', function() {
                    const row = this.closest('tr');
                    saveUserData(row);
                });
            });

            function enableEditMode(row) {
                row.querySelectorAll('.view-mode').forEach(el => el.style.display = 'none');
                row.querySelectorAll('.edit-mode').forEach(el => el.style.display = 'block');
                row.querySelector('.btn-edit').style.display = 'none';
                row.querySelector('.btn-save').style.display = 'inline-flex';
                row.querySelector('.btn-cancel').style.display = 'inline-flex';
            }

            function disableEditMode(row) {
                row.querySelectorAll('.view-mode').forEach(el => el.style.display = '');
                row.querySelectorAll('.edit-mode').forEach(el => el.style.display = 'none');
                row.querySelector('.btn-edit').style.display = 'inline-flex';
                row.querySelector('.btn-save').style.display = 'none';
                row.querySelector('.btn-cancel').style.display = 'none';
            }

            function saveUserData(row) {
                const userId = row.dataset.userId;
                const data = {
                    id: userId,
                    username: row.querySelector('[data-field="username"] .edit-mode').value,
                    email: row.querySelector('[data-field="email"] .edit-mode').value,
                    role: row.querySelector('[data-field="role"] .edit-mode').value,
                    id_service: row.querySelector('[data-field="id_service"] .edit-mode').value || null
                };

                const saveBtn = row.querySelector('.btn-save');
                saveBtn.disabled = true;
                const spinner = saveBtn.querySelector('.spinner');
                spinner.style.display = 'block';

                fetch('update_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        row.querySelector('[data-field="username"] .view-mode').textContent = data.username;
                        row.querySelector('[data-field="email"] .view-mode').textContent = data.email;
                        
                        const roleBadge = row.querySelector('[data-field="role"] .view-mode');
                        if (data.role === 'admin') {
                            roleBadge.innerHTML = '<span class="badge badge-primary">Administrateur</span>';
                        } else if (data.role === 'admin_super') {
                            roleBadge.innerHTML = '<span class="badge badge-info">Admin Superviseur</span>';
                        } else {
                            roleBadge.innerHTML = '<span class="badge badge-success">Responsable</span>';
                        }
                        
                        if (data.id_service) {
                            const serviceName = row.querySelector(`[data-field="id_service"] .edit-mode option[value="${data.id_service}"]`).textContent;
                            row.querySelector('[data-field="id_service"] .view-mode').textContent = serviceName;
                        } else {
                            row.querySelector('[data-field="id_service"] .view-mode').textContent = '-';
                        }

                        disableEditMode(row);
                        showToast('Modifications enregistrées avec succès', 'success');
                    } else {
                        showToast(result.message || 'Une erreur est survenue', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Une erreur est survenue lors de la mise à jour', 'error');
                })
                .finally(() => {
                    saveBtn.disabled = false;
                    spinner.style.display = 'none';
                });
            }

            function showToast(message, type = 'success') {
                const toast = document.getElementById('toast');
                const toastMessage = document.getElementById('toast-message');
                
                toast.className = `toast toast-${type}`;
                toastMessage.textContent = message;
                
                const icon = toast.querySelector('i');
                icon.className = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
                
                toast.classList.add('show');
                
                setTimeout(() => {
                    toast.classList.remove('show');
                }, 3000);
            }

            document.getElementById('role')?.addEventListener('change', function() {
                const serviceField = document.getElementById('id_service');
                if (this.value === 'superviseur') {
                    serviceField.required = true;
                } else {
                    serviceField.required = false;
                    serviceField.value = '';
                }
            });
        });
    </script>
</body>
</html>