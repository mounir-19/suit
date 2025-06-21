<?php
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['prenom'] ?? '');
    $last_name = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    // Validation
    if (empty($first_name)) $errors[] = "Le prénom est requis";
    if (empty($last_name)) $errors[] = "Le nom est requis";
    if (empty($email)) $errors[] = "L'email est requis";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Format d'email invalide";
    if (empty($password)) $errors[] = "Le mot de passe est requis";
    if (strlen($password) < 6) $errors[] = "Le mot de passe doit contenir au moins 6 caractères";
    if ($password !== $confirm_password) $errors[] = "Les mots de passe ne correspondent pas";
    
    if (empty($errors)) {
        if (register($first_name, $last_name, $email, $password)) {
            $success = "Compte créé avec succès! Vous pouvez maintenant vous connecter.";
        } else {
            $errors[] = "Cet email est déjà utilisé ou une erreur s'est produite";
        }
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
            <a href="homepage.php">Acceuil</a>
            <a href="store.php">Catalogue</a>
            <a href="homepage.php#contact">Contact</a>
        </div>
        
        <p class="name">MSH-ISTANBUL</p>

        <!-- User Actions -->
        <div class="user" id="user">
            <a href="#">Langue</a>
            <a href="cart.php">Cart</a>
            <a href="login.php">Account</a>
        </div>
    </div>
    <hr>
<div class="signup">
    <div class="form">
        <p class="p1">Créer un compte</p>
        <p class="p2">Créez un compte pour consulter l'historique <br>de vos commandes et mettre à jour vos coordonnées.</p>
        <?php if (isset($success)): ?>
            <div class="success-message" style="background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #c3e6cb;">
                <?php echo htmlspecialchars($success); ?>
            </div>
            <div style="text-align: center; margin-top: 20px;">
                <a href="login.php" style="color: #D4AE6A; text-decoration: none;">Se connecter maintenant</a>
            </div>
        <?php else: ?>
        <form action="" method="post">
            <?php if (!empty($errors)): ?>
                <div class="error-message" style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #f5c6cb;">
                    <?php foreach ($errors as $error): ?>
                        <div><?php echo htmlspecialchars($error); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <label for="nom">Nom</label><br>
            <input type="text" name="nom" id="nom" placeholder="votre nom" value="<?php echo htmlspecialchars($last_name ?? ''); ?>" required><br>

            <label for="prenom">Prénom</label><br>
            <input type="text" name="prenom" id="prenom" placeholder="votre prénom" value="<?php echo htmlspecialchars($first_name ?? ''); ?>" required><br>

            <label for="email">Email</label><br>
            <input type="email" name="email" id="email" placeholder="votre@email.com" value="<?php echo htmlspecialchars($email ?? ''); ?>" required><br>

            <label for="password">Mot de passe</label><br>
            <input type="password" name="password" id="password" placeholder="Au moins 6 caractères" required><br>

            <label for="confirm_password">Confirmer le mot de passe</label><br>
            <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirmer votre mot de passe" required><br>

            <button type="submit" style="width: 100%; padding: 12px; background: #D4AE6A; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; margin-top: 15px;">Créer le compte</button><br>
        </form>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="login.php" style="color: #D4AE6A; text-decoration: none;">Déjà un compte? Se connecter</a>
        </div>
        <?php endif; ?>
        </div>
    </div>
    
    <hr>
    <footer>
        <div class="service-client">
            <p>Service client</p>
            <a href="profile.php">Compte</a>
            <a href="#">Livraison & Retour</a>
            <a href="homepage.php#contact">Contactez-Nous</a>
        </div>
        <div class="service">
            <p>Services</p>
            <a href="homepage.php">Accueil</a>
            <a href="store.php">Boutique</a>
            <a href="cart.php">Panier</a>
        </div>
        <div class="Contact3">
            <a href="#">Alger, Algérie</a>
            <a href="tel:+213123456789">+213 123 456 789</a>
            <a href="mailto:contact@msh-istanbul.com">contact@msh-istanbul.com</a>
            <a href="#">Facebook</a>
        </div>
        <img src="logo.png" alt="Logo of MSH Istanbul">
        <div class="copyright">&copy; 2025 MSH Istanbul. Tous droits réservés.</div>
    </footer>
</body>
</html>

<!-- Remove the old wilaya dropdown and everything after -->
<!--
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