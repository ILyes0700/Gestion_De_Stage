<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
$ab= $_SESSION['id_service'];
$conn = mysqli_connect("localhost","root","","gestion_stagiaires");
$role = $_SESSION['role'];
// Recherche simple
$search = $_GET['search'] ?? '';

// Requête avec recherche (par nom ou prénom ou email)
if ($search) {
    $like_search = "%$search%" ;
    $etudiants = mysqli_query($conn, "
        SELECT e.* FROM etudiants e
        INNER JOIN stages s ON e.id_etudiant = s.id_etudiant
        WHERE e.nom LIKE '$like_search'
           OR e.prenom LIKE '$like_search'
           OR e.email LIKE '$like_search'
           OR e.telephone LIKE '$like_search'
           OR e.cin LIKE '$like_search'
           OR e.date_naissance LIKE '$like_search'
          
        GROUP BY e.id_etudiant
        ORDER BY e.prenom
    ");
} else {
    $etudiants = mysqli_query($conn, "
        SELECT e.* FROM etudiants e
        INNER JOIN stages s ON e.id_etudiant = s.id_etudiant
        GROUP BY e.id_etudiant
        ORDER BY e.prenom
    ");
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Liste des étudiants | Gestion Stagiaires</title>

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

  /* Contenu principal */
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

  /* Conteneur de contenu */
  .content-container {
    background: white;
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
    border: 1px solid #e0e8e0;
  }

  /* Barre de recherche */
  .search-container {
    display: flex;
    gap: 15px;
    margin-bottom: 25px;
    background: #f8faf8;
    padding: 20px;
    border-radius: 12px;
    border: 1px solid #e0e8e0;
  }

  .search-input {
    flex: 1;
    position: relative;
  }

  .search-input input {
    width: 100%;
    padding: 14px 20px 14px 45px;
    border: 1px solid #d0d8d0;
    border-radius: 10px;
    font-size: 15px;
    transition: all 0.3s;
    background-color: white;
    font-family: 'Poppins', sans-serif;
  }

  .search-input input:focus {
    border-color: var(--primary-color);
    outline: none;
    box-shadow: 0 0 0 3px rgba(4, 104, 24, 0.1);
  }

  .search-input i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #a0b0a0;
  }

  .btn {
    padding: 12px 20px;
    border-radius: 10px;
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
    font-family: 'Poppins', sans-serif;
  }

  .btn-primary {
    background-color: var(--primary-color);
    color: white;
    box-shadow: 0 2px 10px rgba(4, 104, 24, 0.3);
  }

  .btn-primary:hover {
    background-color: var(--secondary-color);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(4, 104, 24, 0.4);
  }

  .btn-icon {
    width: 42px;
    height: 42px;
    padding: 0;
    border-radius: 50%;
    background: white;
    color: var(--primary-color);
    border: 1px solid #d0d8d0;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
  }

  .btn-icon:hover {
    background: var(--primary-color);
    color: white;
    transform: translateY(-2px);
  }

  /* Nouveau style de liste */
  .students-list {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 12px;
    margin-top: 20px;
  }

  .students-list thead th {
    text-align: left;
    padding: 15px 20px;
    background-color: var(--primary-color);
    color: white;
    font-weight: 500;
    position: sticky;
    top: 0;
  }

  .students-list thead th:first-child {
    border-top-left-radius: 10px;
    border-bottom-left-radius: 10px;
  }

  .students-list thead th:last-child {
    border-top-right-radius: 10px;
    border-bottom-right-radius: 10px;
  }

  .student-row {
    background-color: white;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  }

  .student-row:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.1);
  }

  .student-row td {
    padding: 18px 20px;
    vertical-align: middle;
    border-bottom: 1px solid #f0f0f0;
  }

  .student-row:first-child td {
    border-top: 1px solid #f0f0f0;
  }

  .student-row td:first-child {
    border-left: 1px solid #f0f0f0;
    border-top-left-radius: 8px;
    border-bottom-left-radius: 8px;
  }

  .student-row td:last-child {
    border-right: 1px solid #f0f0f0;
    border-top-right-radius: 8px;
    border-bottom-right-radius: 8px;
  }

  .student-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-right: 15px;
  }

  .student-name {
    font-weight: 500;
    color: #333;
    display: flex;
    align-items: center;
  }

  .student-email {
    font-size: 13px;
    color: #666;
    margin-top: 4px;
  }

  .student-info {
    display: flex;
    align-items: center;
  }

  .action-buttons {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
  }

  .action-btn {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    color: var(--primary-color);
    border: 1px solid #d0d8d0;
    transition: all 0.3s;
    text-decoration: none;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
  }

  .action-btn:hover {
    background: var(--primary-color);
    color: white;
    transform: scale(1.1);
    box-shadow: 0 4px 8px rgba(4, 104, 24, 0.2);
  }

  .ac:hover {
    color: white;
    background: var(--danger-color);
    border-color: var(--danger-color);
  }

  /* État vide */
  .empty-state {
    text-align: center;
    padding: 60px 20px;
    background: #f8faf8;
    border-radius: 16px;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
    margin-top: 20px;
    border: 1px solid #e0e8e0;
  }

  .empty-state i {
    font-size: 70px;
    color: #d1d9d1;
    margin-bottom: 25px;
    opacity: 0.7;
  }

  .empty-state h3 {
    margin: 0 0 15px;
    color: #5c6b5c;
    font-size: 22px;
    font-weight: 600;
  }

  .empty-state p {
    margin: 0;
    color: #7f8c7f;
    font-size: 16px;
    max-width: 500px;
    margin: 0 auto;
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

    .search-container {
      flex-direction: column;
    }

    .students-list {
      display: block;
      overflow-x: auto;
    }

    .student-row td {
      white-space: nowrap;
    }
  }
  .delete-popup {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    display: none; /* caché par défaut */
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.delete-content {
    background-color: var(--light-color);
    padding: 20px 30px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    width: 320px;
    position: relative;
}

.warning-icon {
    font-size: 40px;
    color: var(--danger-color);
    margin-bottom: 15px;
}

.delete-actions {
    display: flex;
    justify-content: space-between;
    margin-top: 20px;
}

.delete-actions .btn {
    padding: 8px 15px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
    text-decoration: none;
    transition: 0.3s;
}

.btn-confirm {
    background-color: var(--danger-color);
    color: #fff;
}

.btn-confirm:hover {
    background-color: #d61770;
}

.btn-cancel {
    background-color: var(--primary-color)  ;
    color: #fff;
}

.btn-cancel:hover {
    background-color: ;
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
         <li><a href="etudiants.php" class="active"><i class="fas fa-users"></i> <span>Étudiants</span></a></li>
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
    <h1><i class="fas fa-users"></i> Liste des étudiants</h1>
    <div class="user-profile">
      <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['username'] ?? 'Admin') ?>&background=046818&color=fff" alt="User">
      <span><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></span>
    </div>
  </div>

  <div class="content-container">
    <div class="search-container">
      <div class="search-input">
        <i class="fas fa-search"></i>
        <form method="GET" style="display: flex;">
          <input type="text" name="search" placeholder="Rechercher un étudiant..." value="<?= htmlspecialchars($search) ?>">
        </form>
      </div>
      <div class="actions">
        <a href="etudiants.php" class="btn btn-icon" title="Réinitialiser la recherche">
          <i class="fas fa-sync-alt"></i>
        </a>
        <a href="etudiant_add.php" class="btn btn-primary">
          <i class="fas fa-plus"></i> Ajouter un étudiant
        </a>
      </div>
    </div>

    <?php if (mysqli_num_rows($etudiants) > 0): ?>
    <table class="students-list">
      <thead>
        <tr>
          <th>Étudiant</th>
          <th>CIN</th>
          <th>Téléphone</th>
          <th>Date de naissance</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while($e = mysqli_fetch_assoc($etudiants)): 
          $initials = strtoupper(substr($e['prenom'], 0, 1) . substr($e['nom'], 0, 1));
        ?>
        <tr class="student-row">
          <td>
            <div class="student-info">
              <div class="student-avatar"><?= $initials ?></div>
              <div>
                <div class="student-name"><?= htmlspecialchars($e['prenom'] . ' ' . $e['nom']) ?></div>
                <div class="student-email"><?= htmlspecialchars($e['email']) ?></div>
              </div>
            </div>
          </td>
          <td><?= htmlspecialchars($e['cin']) ?></td>
          <td><?= htmlspecialchars($e['telephone']) ?></td>
          <td><?= htmlspecialchars($e['date_naissance']) ?></td>
          <td>
            <div class="action-buttons">
              <a href="etudiant_profile.php?id=<?= $e['id_etudiant'] ?>" class="action-btn" title="Voir le profil">
                <i class="fas fa-eye"></i>
              </a>
              <a href="etudiant_edit.php?id=<?= $e['id_etudiant'] ?>" class="action-btn" title="Modifier">
                <i class="fas fa-edit"></i>
              </a>
             <a href="#" class="action-btn ac delete-btn" data-id="<?= $e['id_etudiant'] ?>" title="Supprimer"> 
  <i class="fas fa-trash-alt"></i> 
</a>
            </div>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
    <div class="delete-popup" id="delete-popup">
  <div class="delete-content">
    <i class="fas fa-exclamation-triangle warning-icon"></i>
    <p>Voulez-vous vraiment supprimer cet stagier ?</p>
    <div class="delete-actions">
      <a href="#" class="btn btn-confirm" id="confirm-delete">Supprimer</a>
      <button class="btn btn-cancel" id="cancel-delete">Annuler</button>
    </div>
  </div>
</div>
    <?php else: ?>
    <div class="empty-state">
      <i class="fas fa-user-graduate"></i>
      <h3>Aucun étudiant trouvé</h3>
      <p>Aucun étudiant ne correspond à votre recherche. Essayez d'autres termes ou ajoutez un nouvel étudiant.</p>
      <a href="etudiants.php" class="btn btn-primary" style="margin-top: 25px;">
        <i class="fas fa-sync-alt"></i> Réinitialiser la recherche
      </a>
      <a href="etudiant_add.php" class="btn btn-primary" style="margin-top: 15px;">
        <i class="fas fa-plus"></i> Ajouter un étudiant
      </a>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
  // Amélioration de la recherche avec délai
  const searchInput = document.querySelector('input[name="search"]');
  let searchTimeout;
  
  searchInput.addEventListener('input', function(e) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
      this.form.submit();
    }, 500);
  });

  // Confirmation pour la suppression

</script>
<script>
// Références
const deletePopup = document.getElementById('delete-popup');
const confirmBtn = document.getElementById('confirm-delete');
const cancelBtn = document.getElementById('cancel-delete');
let deleteUrl = '';

// Ouvrir popup au clic sur le bouton supprimer
document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', function(e){
        e.preventDefault();
        deleteUrl = `etudiant_delet.php?id=${this.dataset.id}`;
        deletePopup.style.display = 'flex';
    });
});

// Confirmer suppression
confirmBtn.addEventListener('click', function(){
    window.location.href = deleteUrl;
});

// Annuler suppression
cancelBtn.addEventListener('click', function(){
    deletePopup.style.display = 'none';
});

// Fermer popup si clic à l'extérieur
deletePopup.addEventListener('click', function(e){
    if(e.target === deletePopup){
        deletePopup.style.display = 'none';
    }
});
</script>
</body>
</html>