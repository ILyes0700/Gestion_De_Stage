<?php
session_start();

$conn = new PDO("mysql:host=localhost;dbname=gestion_stagiaires;charset=utf8", "root", "");

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $date = Date("Y-m-d H:i:s");
    $stmt = $conn->prepare("SELECT * FROM utilisateurs WHERE username = ? AND password=? limit 1");
    $stmt->execute([$username, $password]);
    $user = $stmt->fetch();

    if ($user && $password) {
        $_SESSION['user_id'] = $user['id_user'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['id_service'] = $user['id_service'];
        $_SESSION['nom_service'] = $user['nom_service'] ?? ''; // Ajout du nom du service
        
        header("Location: dashboard.php");
        $stmt = $conn->prepare("INSERT INTO logs (id_user, action, date_action) VALUES (?, ?, ?)");
        $stmt->execute([$user['id_user'], "Connexion au système", $date]);
        exit;
    } else {
        $erreur = "Nom d'utilisateur ou mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Connexion - Gestion des stagiaires ICF Gabès</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary-color: #026c17ff;
      --primary-dark: #027a1aff;
      --primary-light: #e2ffd7;
      --accent-color: #3a86ff;
      --error-color: #ef233c;
      --light-gray: #f8f9fa;
      --medium-gray: #e9ecef;
      --dark-gray: #6c757d;
      --white: #ffffff;
      --black: #212529;
      --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.32);
      --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Poppins';
      background-color: var(--light-gray);
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 20px;
      background-image: linear-gradient(135deg, #f5f7fa 0%, #dfe7f1 100%);
      color: var(--black);
    }
    
    .login-container {
      display: flex;
      max-width: 1000px;
      width: 100%;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: var(--box-shadow);
      background-color: var(--white);
      transition: var(--transition);
    }
    
    .login-container:hover {
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
    }
    
    .login-illustration {
      flex: 1;
      background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 50px;
      color: var(--white);
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    
    .login-illustration::before {
      content: '';
      position: absolute;
      top: -50px;
      right: -50px;
      width: 200px;
      height: 200px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.1);
    }
    
    .login-illustration::after {
      content: '';
      position: absolute;
      bottom: -80px;
      left: -80px;
      width: 300px;
      height: 300px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.05);
    }
    
    .login-illustration img {
      width: 180px;
      margin-bottom: 30px;
      z-index: 1;
      filter: drop-shadow(0 5px 15px rgba(0, 0, 0, 0.2));
    }
    
    .login-illustration h2 {
      margin-bottom: 15px;
      font-weight: 600;
      font-size: 28px;
      z-index: 1;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .login-illustration p {
      opacity: 0.9;
      line-height: 1.6;
      font-size: 16px;
      max-width: 350px;
      z-index: 1;
    }
    
    .login-form {
      flex: 1;
      padding: 70px 60px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      position: relative;
    }
    
    .logo-mobile {
      display: none;
      text-align: center;
      margin-bottom: 30px;
    }
    
    .logo-mobile img {
      height: 80px;
    }
    
    .login-form h1 {
      color: var(--primary-color);
      margin-bottom: 10px;
      font-size: 32px;
      font-weight: 700;
    }
    
    .login-form p.subtitle {
      color: var(--dark-gray);
      margin-bottom: 30px;
      font-size: 15px;
    }
    
    .form-group {
      margin-bottom: 25px;
      position: relative;
    }
    
    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
      color: var(--black);
      font-size: 14px;
    }
    
    .form-group .input-with-icon {
      position: relative;
    }
    
    .form-group .input-with-icon i {
      position: absolute;
      left: 18px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--dark-gray);
      font-size: 16px;
      transition: var(--transition);
    }
    
    .form-group input {
      width: 100%;
      padding: 15px 20px 15px 50px;
      border: 2px solid var(--medium-gray);
      border-radius: 10px;
      font-size: 15px;
      transition: var(--transition);
      background-color: var(--white);
      color: var(--black);
    }
    
    .form-group input:focus {
      outline: none;
      border-color: var(--primary-color);
      box-shadow: 0 0 0 4px rgba(4, 104, 24, 0.2);
    }
    
    .form-group input:focus + i {
      color: var(--primary-color);
    }
    
    .remember-forgot {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
      font-size: 14px;
    }
    
    .remember-me {
      display: flex;
      align-items: center;
    }
    
    .remember-me input {
      margin-right: 10px;
      accent-color: var(--primary-color);
      width: 16px;
      height: 16px;
    }
    
    .forgot-password a {
      color: var(--accent-color);
      text-decoration: none;
      transition: var(--transition);
      font-weight: 500;
    }
    
    .forgot-password a:hover {
      color: var(--primary-dark);
      text-decoration: underline;
    }
    
    .login-button {
      width: 100%;
      padding: 16px;
      background-color: var(--primary-color);
      color: var(--white);
      border: none;
      border-radius: 10px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: var(--transition);
      margin-top: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
    }
    
    .login-button:hover {
      background-color: var(--primary-dark);
      transform: translateY(-3px);
      box-shadow: 0 5px 15px rgba(4, 104, 24, 0.3);
    }
    
    .login-button:active {
      transform: translateY(0);
    }
    
    .alert {
      padding: 16px;
      border-radius: 10px;
      margin-bottom: 30px;
      font-size: 14px;
      display: flex;
      align-items: center;
      gap: 12px;
      background-color: #fff1f0;
      color: var(--error-color);
      border-left: 4px solid var(--error-color);
    }
    
    .alert i {
      font-size: 20px;
    }
    
    .footer-text {
      text-align: center;
      margin-top: 40px;
      color: var(--dark-gray);
      font-size: 13px;
      line-height: 1.6;
    }
    
    /* Animation */
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .login-form {
      animation: fadeIn 0.6s ease-out;
    }
    
    /* Responsive */
    @media (max-width: 992px) {
      .login-container {
        max-width: 700px;
      }
      
      .login-illustration {
        padding: 40px 30px;
      }
      
      .login-form {
        padding: 50px 40px;
      }
    }
    
    @media (max-width: 768px) {
      .login-container {
        flex-direction: column;
        max-width: 500px;
      }
      
      .login-illustration {
        display: none;
      }
      
      .logo-mobile {
        display: block;
      }
      
      .login-form {
        padding: 40px 30px;
      }
    }
    
    @media (max-width: 480px) {
      .login-form {
        padding: 30px 20px;
      }
      
      .login-form h1 {
        font-size: 28px;
      }
      
      .remember-forgot {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
      }
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="login-illustration">
      <img src="logo.png" alt="Logo ICF Gabès">
      <h2>Bienvenue sur ICF Gabès</h2>
      <p>Système de gestion intégrée des stagiaires de l'usine ICF Gabès - Direction des Ressources Humaines</p>
    </div>
    
    <div class="login-form">
      <div class="logo-mobile">
        <img src="logo.png" alt="Logo ICF Gabès">
      </div>
      
      <h1>Connexion</h1>
      <p class="subtitle">Accédez à votre espace de gestion des stagiaires</p>
      
      <?php if (!empty($erreur)): ?>
        <div class="alert">
          <i class="fas fa-exclamation-circle"></i>
          <span><?= $erreur ?></span>
        </div>
      <?php endif; ?>
      
      <form method="POST">
        <div class="form-group">
          <label for="username">Identifiant</label>
          <div class="input-with-icon">
            <i class="fas fa-user"></i>
            <input type="text" style="font-family: 'Poppins';" id="username" name="username" placeholder="Saisissez votre identifiant" required autofocus>
          </div>
        </div>
        
        <div class="form-group">
          <label for="password">Mot de passe</label>
          <div class="input-with-icon">
            <i class="fas fa-lock"></i>
            <input type="password" style="font-family: 'Poppins';" id="password" name="password" placeholder="Entrez votre mot de passe" required>
          </div>
        </div>
        
        <div class="remember-forgot">
          <div class="remember-me">
            <input type="checkbox" id="remember" name="remember">
            <label for="remember" style="font-family: 'Poppins';">Maintenir la connexion</label>
          </div>
          
        </div>
        
        <button type="submit" name="login" class="login-button">
          <i class="fas fa-sign-in-alt"></i> Se connecter
        </button>
      </form>
      <p class="footer-text">
        &copy; <?= date('Y') ?> ICF Gabès - Direction des Ressources Humaines<br>
         &copy; Rejeb_Ilyes

      </p>
    </div>
  </div>
</body>
</html>