<?php
function print_form($modifier) {//formulaire d'inscription ou de modification
  $mot = $modifier ? "Modifier mes informations" : "S'inscrire";
  $destination = $modifier ? "update" : "sauvegarder";

  $html =
    "<main><h2>$mot</h2>
    
    <form action='index.php?action=$destination' method='post'>
    <label for=\"pseudo\">Pseudo :</label>
    <input type=\"text\" id=\"pseudo\"  required=\"required\" name=\"Pseudo\" ";
    if($modifier) {
        $html .= "value= \"".$_SESSION['LOGGED_PSEUDO']."\">";
    }else{
        $html .= "placeholder=\"Votre pseudo\">";
    }

    $html.= "<br>
    <label for=\"nom\">Nom :</label>
    <input type=\"text\" id=\"nom\" required=\"required\" name=\"nom\"";
    if($modifier) {
        $html .= "value= \"".$_SESSION['LOGGED_NOM']."\">";
    }else{
        $html .= "placeholder=\"Votre nom\">";
    }

    $html.= "<br>
    <label for=\"prenom\">Prenom :</label>
    <input type=\"text\" id=\"prenom\"  required=\"required\" name=\"prenom\"";
    if($modifier) {
        $html .= "value= \"".$_SESSION['LOGGED_PRENOM']."\">";
    }else{
        $html .= "placeholder=\"Votre prénom\">";
    }

    $html.="<br>
    <label for=\"date\">Date de naissance :</label>
    <input type=\"date\" id=\"date\" required=\"required\" name=\"date\"";
    if($modifier) {
        $html .= "value= \"".$_SESSION['LOGGED_DATE']."\">";
    } else{
        $html .= ">";
    }

    $html .="<br>
    <label for=\"mail\">Adresse mail : </label>
    <input type=\"email\" id =\"mail\" name=\"mail\" required=\"required\" pattern=\"^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}\"";
    if($modifier) {
        $html .= "value= \"".$_SESSION['LOGGED_MAIL']."\">";
    }else{
        $html .= "placeholder=\"ex: toto@mail.com\">";
    }

    $html.="<br>
    <label for=\"mdp\">Mot de passe : </label>
    <input type=\"password\" name=\"mdp\" placeholder=\"mot de passe\">
    <br>
    <button type=\"submit\">".$mot."</button><br>
    </form></main>";
    if(!$modifier){$html.="<dic class=\"connexion\"><a href=\"index.php?action=connexion\"><i class=\"fa-solid fa-user\" style=\"color: #553d00;\"></i>J'ai déjà un compte</a></div>";}
    
    return $html;
}

function inscriptionValidee(){
        $pdo = new PDO('mysql:host=localhost;dbname=instapets', 'root', 'root');
    
        if(isset($_POST['Pseudo'])){
            $stmt = $pdo -> prepare('SELECT user_pseudo FROM Users');
            $stmt -> execute();
            $users = $stmt -> fetchAll();
            foreach($users as $pseudo){
                if($pseudo == $_POST['Pseudo']){
                    $message = "Ce nom d'utilisateur est déjà pris!";
                    echo "<script type=\"text/javascript\">window.alert(\"".$message."\");</script>";
                    return false;
                }
            }
            $userPseudo= htmlspecialchars($_POST['Pseudo']);
        } else return false;
        
        if(isset($_POST['nom'])){
            $usernom= htmlspecialchars($_POST['nom']);
        } else return false;
        
        if(isset($_POST['prenom'])){
            $userprenom= htmlspecialchars($_POST['prenom']);
        }else return false;
        
        if(isset($_POST['date'])){
            $userdate = $_POST['date'];
        } else return false;
        
        if(isset($_POST['mail'])){
            $stmt = $pdo->prepare('SELECT user_email FROM Users');
            $stmt->execute();
            $users = $stmt->fetchAll();
            foreach($users as $mail){
                if($mail == $_POST['mail']){
                    $message="Cette adresse mail est déjà associée à un compte !";
                    echo '<script type="text/javascript">window.alert("'.$message.'");</script>';
                    return false;
                }
            }
            $usermail= htmlspecialchars($_POST['mail']);
        } else return false;
    
        if(isset($_POST['mdp'])){
            $userpassword = sha1($_POST['mdp']); //crypter le mdp
        } else return false; 

        $sqlQuery = 'INSERT INTO Users(user_pseudo,user_prenom,user_nom,user_naissance,user_email,user_motdepasse) 
        VALUES (:user_pseudo,:user_prenom,:user_nom,:user_naissance,:user_email,:user_motdepasse);';
        $insertUsers = $pdo->prepare($sqlQuery);
        $insertUsers->execute([
            'user_pseudo' => $userPseudo,
            'user_prenom' => $userprenom,
            'user_nom' => $usernom,
            'user_naissance' => $userdate,
            'user_email' => $usermail,
            'user_motdepasse' => $userpassword
        ]);
        
        $recupID = $pdo->prepare('SELECT * FROM Users WHERE user_pseudo = ? AND user_motdepasse = ?');
        $recupID->execute(array($userPseudo, $userpassword));
        
        $_SESSION['LOGGED_ID']= $recupID->fetch()['user_id'];
        $_SESSION['LOGGED_PSEUDO']= $userPseudo;
        $_SESSION['LOGGED_MDP'] = $userpassword;
        $_SESSION['LOGGED_PRENOM'] =  $userprenom ;
        $_SESSION['LOGGED_NOM']= $usernom;
        $_SESSION['LOGGED_DATE'] = $userdate ;
        $_SESSION['LOGGED_MAIL'] = $usermail ;
        return true;
      
}

/*cas de la modification des infos: recuperer l'utilisateur, valider ses nouvelles infos*/

function modificationValidee(){

    //verifier pr vrmt etre sures mais en principe dans le html les champs sont marqués 'required'
    if(!isset($_POST['Pseudo'])|| !isset($_POST['mail'])|| !isset($_POST['mdp'])|| !isset($_POST['prenom'])|| !isset($_POST['nom'])|| !isset($_POST['date'])){
        $message = "Merci de remplir tous les champs";
        echo "<script type=\"text/javascript\">window.alert(\"".$message."\");</script>";
        return false;
    }

    $pdo = new PDO('mysql:host=localhost;dbname=instapets', 'root', 'root');

    //d'abord extraire les informations de l'utilisateur connecté
    
    $oldInfo = array(
        "oldPseudo" =>  $_SESSION['LOGGED_PSEUDO'],
        "oldMail" =>  $_SESSION['LOGGED_MAIL'] ,
        "oldMdp" =>   $_SESSION['LOGGED_MDP'] ,
        "oldPrenom" =>  $_SESSION['LOGGED_PRENOM'], 
        "oldNom" => $_SESSION['LOGGED_NOM'],
        "oldDate" => $_SESSION['LOGGED_DATE']
    );

    // recuperer les données du formulaire ->htmlspecialchars ; sha1(); 

    $newInfo = array(
        "newPseudo"=> htmlspecialchars($_POST['Pseudo']),
        "newMail"=> htmlspecialchars($_POST['mail']),
        "newMdp"=> sha1($_POST['mdp']),
        "newPrenom"=> htmlspecialchars($_POST['prenom']),
        "newNom"=> htmlspecialchars($_POST['nom']),
        "newDate" => $_POST['date']
    );

 
    // Modifier les infos dans la base (si differentes nouvelles);

    // si nouveau pseudo/mail verifier qu'il n'est pas dans la base de données avant de modifer

    if($oldInfo['oldMail'] != $newInfo['newMail']){
        $stmt = $pdo->prepare('SELECT user_email FROM Users WHERE user_id != ?');
        $stmt->execute(array($_SESSION['LOGGED_ID']));
        $usersMail = $stmt->fetchAll();
        foreach($usersMail as $mail){
            if($mail == $newInfo['newMail']){
                $message="Cette adresse mail est déjà associée à un compte !";
                echo '<script type="text/javascript">window.alert("'.$message.'");</script>';
                return false;
            }
        }

        
        $modif = $pdo->prepare('UPDATE Users SET user_email = ? WHERE user_id = ? ');
        $modif->execute(array($newInfo['newMail'], $_SESSION['LOGGED_ID']));

    }

    if($oldInfo['oldPseudo'] != $newInfo['newPseudo']){
        $stmt = $pdo -> prepare('SELECT user_pseudo FROM Users WHERE user_id != ?');
        $stmt->execute(array($_SESSION['LOGGED_ID']));
        $usersPseudo = $stmt -> fetchAll();
        foreach($usersPseudo as $pseudo){
            if($pseudo == $newInfo['newPseudo']){
                $message = "Ce nom d'utilisateur est déjà pris!";
                echo "<script type=\"text/javascript\">window.alert(\"".$message."\");</script>";
                return false;
            }
        }
        // si on arrive ici, pseudo valide donc on modifie dans la base

        $modif = $pdo->prepare('UPDATE Users SET user_pseudo = ? WHERE user_id = ? ');
        $modif->execute(array($newInfo['newPseudo'], $_SESSION['LOGGED_ID']));
        $_SESSION['LOGGED_PSEUDO'] = $newInfo['newPseudo'];
        
    }

    if($oldInfo['oldMdp'] != $newInfo['newMdp']){
        $modif = $pdo->prepare('UPDATE Users SET user_motdepasse = ? WHERE user_id = ? ');
        $modif->execute(array($newInfo['newMdp'], $_SESSION['LOGGED_ID']));
        $_SESSION['LOGGED_MDP'] = $newInfo['newMdp'];
    }

    if($oldInfo['oldPrenom'] != $newInfo['newPrenom']){
        $modif = $pdo->prepare('UPDATE Users SET user_prenom = ? WHERE user_id = ? ');
        $modif->execute(array($newInfo['newPrenom'], $_SESSION['LOGGED_ID']));
    }

    if($oldInfo['oldNom'] != $newInfo['newNom']){
        $modif = $pdo->prepare('UPDATE Users SET user_nom = ? WHERE user_id = ? ');
        $modif->execute(array($newInfo['newNom'], $_SESSION['LOGGED_ID']));
    }


    if($oldInfo['oldDate'] != $newInfo['newDate']){
        $modif = $pdo->prepare('UPDATE Users SET user_naissance = ? WHERE user_id = ? ');
        $modif->execute(array($newInfo['newDate'], $_SESSION['LOGGED_ID']));
    }

    return true;
}
?>
