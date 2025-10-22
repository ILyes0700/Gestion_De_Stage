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
$stmt = $conn->prepare("SELECT * FROM etudiants WHERE id_etudiant = ? limit 1");
$stmt->execute([$id_etudiant]);
$etudiant = $stmt->fetch();
$nom=$etudiant["nom"];
$pre=$etudiant["prenom"];
$date=Date("Y-m-d H:i:s");
if (!$etudiant) {
    header("Location: etudiants.php");
    exit;
}

    $stmt = $conn->prepare("DELETE FROM `etudiants` WHERE  id_etudiant = ?");
    $stmt->execute([$id_etudiant]);
    $date=Date("Y-m-d H:i:s");
    $stmt = $conn->prepare("INSERT INTO logs (id_user, action ,date_action) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], "Supprime de Stagiér $nom $pre" , $date]);
    header("Location: etudiants.php");
    exit;
?>
