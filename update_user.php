<?php
session_start();
header('Content-Type: application/json');

// Vérifier les permissions
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
    exit;
}

// Seuls les admins et admin_superviseur peuvent modifier des utilisateurs
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'admin_super') {
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
    exit;
}

$conn = new PDO("mysql:host=localhost;dbname=gestion_stagiaires;charset=utf8", "root", "");

$data = json_decode(file_get_contents('php://input'), true);

try {
    // Vérifier que l'utilisateur existe
    $stmt = $conn->prepare("SELECT id_user, role FROM utilisateurs WHERE id_user = ?");
    $stmt->execute([$data['id']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Utilisateur introuvable']);
        exit;
    }
    
    // Un admin_superviseur ne peut pas modifier un admin
    if ($_SESSION['role'] === 'admin_super' && $user['role'] === 'admin') {
        echo json_encode(['success' => false, 'message' => 'Permission denied - Vous ne pouvez pas modifier un administrateur']);
        exit;
    }
    
    // Un admin_superviseur ne peut pas donner le rôle admin
    if ($_SESSION['role'] === 'admin_super' && $data['role'] === 'admin') {
        echo json_encode(['success' => false, 'message' => 'Permission denied - Vous ne pouvez pas attribuer le rôle administrateur']);
        exit;
    }

    // Mettre à jour l'utilisateur
    $stmt = $conn->prepare("UPDATE utilisateurs SET 
        username = :username, 
        email = :email, 
        role = :role, 
        id_service = :id_service 
        WHERE id_user = :id");
    
    $id_service = !empty($data['id_service']) ? $data['id_service'] : null;
    
    $stmt->execute([
        ':username' => $data['username'],
        ':email' => $data['email'],
        ':role' => $data['role'],
        ':id_service' => $id_service,
        ':id' => $data['id']
    ]);
    
    // Journalisation
    $date = Date("Y-m-d H:i:s");
    $stmt = $conn->prepare("INSERT INTO logs (id_user, action, date_action) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], "Modification de l'utilisateur ID " . $data['id'], $date]);
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>