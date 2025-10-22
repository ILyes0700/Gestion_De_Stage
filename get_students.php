<?php
session_start();


// Vérifier l'authentification
if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    exit;
}

$type = $_GET['type'] ?? 'all';
$role = $_SESSION['role'];
$id_service = $_SESSION['id_service'] ?? null;

try {
    $conn = new PDO("mysql:host=localhost;dbname=gestion_stagiaires;charset=utf8", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $query = "";
    $params = [];
    
    switch($type) {
        case 'all':
            if ($role === 'admin' || $role == 'admin_super') {
                $query = "SELECT e.*, s.date_debut, s.date_fin, ser.nom_service 
                         FROM etudiants e 
                         LEFT JOIN stages s ON e.id_etudiant = s.id_etudiant 
                         LEFT JOIN services ser ON s.id_service = ser.id_service";
            } else {
                $query = "SELECT e.*, s.date_debut, s.date_fin, ser.nom_service 
                         FROM etudiants e 
                         JOIN stages s ON e.id_etudiant = s.id_etudiant 
                         JOIN services ser ON s.id_service = ser.id_service 
                         WHERE s.id_service = ?";
                $params[] = $id_service;
            }
            break;
            
        case 'current_internships':
            if ($role === 'admin' || $role == 'admin_super') {
                $query = "SELECT e.*, s.date_debut, s.date_fin, ser.nom_service 
                         FROM etudiants e 
                         JOIN stages s ON e.id_etudiant = s.id_etudiant 
                         JOIN services ser ON s.id_service = ser.id_service 
                         WHERE s.etat = 'En cours'";
            } else {
                $query = "SELECT e.*, s.date_debut, s.date_fin, ser.nom_service 
                         FROM etudiants e 
                         JOIN stages s ON e.id_etudiant = s.id_etudiant 
                         JOIN services ser ON s.id_service = ser.id_service 
                         WHERE s.etat = 'En cours' AND s.id_service = ?";
                $params[] = $id_service;
            }
            break;
            
        case 'finished_internships':
            if ($role === 'admin' || $role == 'admin_super') {
                $query = "SELECT e.*, s.date_debut, s.date_fin, ser.nom_service 
                         FROM etudiants e 
                         JOIN stages s ON e.id_etudiant = s.id_etudiant 
                         JOIN services ser ON s.id_service = ser.id_service 
                         WHERE s.etat = 'Terminé'";
            } else {
                $query = "SELECT e.*, s.date_debut, s.date_fin, ser.nom_service 
                         FROM etudiants e 
                         JOIN stages s ON e.id_etudiant = s.id_etudiant 
                         JOIN services ser ON s.id_service = ser.id_service 
                         WHERE s.etat = 'Terminé' AND s.id_service = ?";
                $params[] = $id_service;
            }
            break;
            
        case 'with_documents':
            if ($role === 'admin' || $role == 'admin_super') {
                $query = "SELECT DISTINCT e.*, s.date_debut, s.date_fin, ser.nom_service 
                         FROM etudiants e 
                         JOIN documents d ON e.id_etudiant = d.id_etudiant 
                         LEFT JOIN stages s ON e.id_etudiant = s.id_etudiant 
                         LEFT JOIN services ser ON s.id_service = ser.id_service";
            } else {
                $query = "SELECT DISTINCT e.*, s.date_debut, s.date_fin, ser.nom_service 
                         FROM etudiants e 
                         JOIN documents d ON e.id_etudiant = d.id_etudiant 
                         JOIN stages s ON e.id_etudiant = s.id_etudiant 
                         JOIN services ser ON s.id_service = ser.id_service 
                         WHERE s.id_service = ?";
                $params[] = $id_service;
            }
            break;
    }
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($students);
    
} catch(PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Erreur de base de données: ' . $e->getMessage()]);
}
?>