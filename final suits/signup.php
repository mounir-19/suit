<?php
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $firstName = $_POST['nom'] ?? '';
    $lastName = $_POST['prenom'] ?? '';
    
    if (register($email, $password, $firstName, $lastName)) {
        header('Location: login.php');
        exit;
    } else {
        $error = "Registration failed. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="signup.css">
    <script src="homepage.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kadwa:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Document</title>
</head>
<body>
    <div class="header">
        <img src="logo.png" alt="" class="logo">

        <!-- Hamburger Menu Toggle -->
        <div id="menuToggle" class="menu-toggle">
            <i class="fas fa-bars"></i>
        </div>
        
        <!-- Navigation Links -->
        <div class="nav" id="nav">
            <a href="#main">Acceuil</a>
            <a href="#main2">Catalogue</a>
            <a href="#contact">Contact</a>
        </div>
        
        <p class="name">MSH-ISTANBOUL</p>

        <!-- User Actions -->
        <div class="user" id="user">
            <a href="#">Langue</a>
            <a href="#">Cart</a>
            <a href="#">Account</a>
        </div>
    </div>
    <hr>
<div class="signup">
    <div class="form">
        <p class="p1">Créer un compte</p>
        <p class="p2">Créez un compte pour consulter l'historique <br>de vos commandes et mettre à jour vos coordonnées.</p>
        <form action="" method="post" id="signupForm" onsubmit="return validateSignupForm(event)">
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <label for="nom">Nom</label><br>
            <input type="text" name="nom" id="nom" placeholder="votre nom" required minlength="2"><br>
            <div class="error-message" id="nomError"></div>

            <label for="prenom">Prenom</label><br>
            <input type="text" name="prenom" id="prenom" placeholder="votre prenom" required minlength="2"><br>
            <div class="error-message" id="prenomError"></div>

            <label for="email">Email</label><br>
            <input type="email" name="email" id="email" placeholder="votre@email.com" required pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"><br>
            <div class="error-message" id="emailError"></div>

            <label for="password">Mot de passe</label><br>
            <input type="password" name="password" id="password" placeholder="Créer un mot de passe" required minlength="8"><br>
            <div class="error-message" id="passwordError"></div>

            <label for="confirm_password">Confirmer le mot de passe</label><br>
            <input type="password" id="confirm_password" placeholder="Confirmer votre mot de passe" required><br>
            <div class="error-message" id="confirmPasswordError"></div>

            <label for="wilaya">Wilaya</label><br>
            <select name="wilaya" id="wilaya" class="wilaya" required>
    <option value="">Sélectionnez votre wilaya</option>
    <option value="01">01 - Adrar</option>
    <option value="02">02 - Chlef</option>
    <option value="03">03 - Laghouat</option>
    <option value="04">04 - Oum El Bouaghi</option>
    <option value="05">05 - Batna</option>
    <option value="06">06 - Béjaïa</option>
    <option value="07">07 - Biskra</option>
    <option value="08">08 - Béchar</option>
    <option value="09">09 - Blida</option>
    <option value="10">10 - Bouira</option>
    <option value="11">11 - Tamanrasset</option>
    <option value="12">12 - Tébessa</option>
    <option value="13">13 - Tlemcen</option>
    <option value="14">14 - Tiaret</option>
    <option value="15">15 - Tizi Ouzou</option>
    <option value="16">16 - Alger</option>
    <option value="17">17 - Djelfa</option>
    <option value="18">18 - Jijel</option>
    <option value="19">19 - Sétif</option>
    <option value="20">20 - Saïda</option>
    <option value="21">21 - Skikda</option>
    <option value="22">22 - Sidi Bel Abbès</option>
    <option value="23">23 - Annaba</option>
    <option value="24">24 - Guelma</option>
    <option value="25">25 - Constantine</option>
    <option value="26">26 - Médéa</option>
    <option value="27">27 - Mostaganem</option>
    <option value="28">28 - M’Sila</option>
    <option value="29">29 - Mascara</option>
    <option value="30">30 - Ouargla</option>
    <option value="31">31 - Oran</option>
    <option value="32">32 - El Bayadh</option>
    <option value="33">33 - Illizi</option>
    <option value="34">34 - Bordj Bou Arréridj</option>
    <option value="35">35 - Boumerdès</option>
    <option value="36">36 - El Tarf</option>
    <option value="37">37 - Tindouf</option>
    <option value="38">38 - Tissemsilt</option>
    <option value="39">39 - El Oued</option>
    <option value="40">40 - Khenchela</option>
    <option value="41">41 - Souk Ahras</option>
    <option value="42">42 - Tipaza</option>
    <option value="43">43 - Mila</option>
    <option value="44">44 - Aïn Defla</option>
    <option value="45">45 - Naâma</option>
    <option value="46">46 - Aïn Témouchent</option>
    <option value="47">47 - Ghardaïa</option>
    <option value="48">48 - Relizane</option>
    <option value="49">49 - El M'Ghair</option>
    <option value="50">50 - El Menia</option>
    <option value="51">51 - Ouled Djellal</option>
    <option value="52">52 - Bordj Badji Mokhtar</option>
    <option value="53">53 - Béni Abbès</option>
    <option value="54">54 - Timimoun</option>
    <option value="55">55 - Touggourt</option>
    <option value="56">56 - Djanet</option>
    <option value="57">57 - In Salah</option>
    <option value="58">58 - In Guezzam</option>
  </select>
  <br>
            <label for="email">Email</label><br>
            <input type="email" id="email" placeholder="Exemple@gmail.com" required><br>
            <label for="password">Mot de passe</label><br>
            <input type="password" id="password" placeholder="Entrer votre mot de passe" required><br>
            <button type="submit">s'inscrire</button><br>
            <a href="login.html" class="login">vous avez déjà un compte?</a>
        </form>
        </div>
        
    </div>
<hr>









        <footer>
        <div class="service-client">
            <p>Service client</p>
            <a href="#">Compte</a>
            <a href="#">Livraison & Retour</a>
            <a href="#">Contactez-Nous</a>
        </div>
        <div class="service">
            <p>Services</p>
            <a href="#">page1</a>
            <a href="#">page2</a>
            <a href="#">page3</a>
        </div>
        <div class="Contact3">
            <a href="#">location</a>
            <a href="#">phone</a>
            <a href="#">email</a>
            <a href="#">facebook</a>
        </div>
        <img src="logo.png" alt="">
        <div class="copyright"><hr>&copy; 2025 MSH Istanboul. Tous droits réservés.</div>
    </footer>
    
</body>
</html>

<script>
function validateSignupForm(event) {
    event.preventDefault();
    const nom = document.getElementById('nom');
    const prenom = document.getElementById('prenom');
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    const wilaya = document.getElementById('wilaya');
    let isValid = true;

    // Reset all error messages
    document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
    document.getElementById('formMessage').textContent = '';

    // Validate nom
    if (nom.value.length < 2) {
        document.getElementById('nomError').textContent = 'Le nom doit contenir au moins 2 caractères';
        isValid = false;
    }

    // Validate prenom
    if (prenom.value.length < 2) {
        document.getElementById('prenomError').textContent = 'Le prénom doit contenir au moins 2 caractères';
        isValid = false;
    }

    // Validate email
    if (!email.validity.valid) {
        document.getElementById('emailError').textContent = 'Veuillez entrer une adresse email valide';
        isValid = false;
    }

    // Validate password
    if (password.value.length < 8) {
        document.getElementById('passwordError').textContent = 'Le mot de passe doit contenir au moins 8 caractères';
        isValid = false;
    }

    // Validate password confirmation
    if (password.value !== confirmPassword.value) {
        document.getElementById('confirmPasswordError').textContent = 'Les mots de passe ne correspondent pas';
        isValid = false;
    }

    // Validate wilaya selection
    if (!wilaya.value) {
        document.getElementById('wilayaError').textContent = 'Veuillez sélectionner votre wilaya';
        isValid = false;
    }

    if (isValid) {
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Création du compte...';

        // Simulate API call
        setTimeout(() => {
            document.getElementById('formMessage').textContent = 'Compte créé avec succès!';
            document.getElementById('formMessage').style.color = 'green';
            setTimeout(() => {
                window.location.href = 'login.html';
            }, 1000);
        }, 1500);
    }

    return false;
}
</script>