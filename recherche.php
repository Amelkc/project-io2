<?php
    function search($user){
    

        $pdo = new PDO('mysql:host=localhost;dbname=instapets', 'root', 'root');
		if(isset($user)&&!empty($user)){
			$stmt = $pdo->prepare('SELECT user_pseudo, user_id 
						FROM Users 
						WHERE user_pseudo 
						LIKE ? 
						ORDER BY user_id DESC'); 
			$stmt->execute(array('%' . $user . '%'));//pour ne pas se limiter si on ne connait pas le pseud entier
			$utilisateurs = $stmt->fetchAll();
		}
           
	    $html = "<main><h2>Résultat(s) pour '".$user."'</h2>";
       
        if(isset($utilisateurs) && count($utilisateurs) > 0){
            foreach($utilisateurs as $ut){
            	$html.= "<li><i class=\"fa-solid fa-user\" style=\"color: #ada368;\"></i><a href=\"index.php?action=profil&amp;id=" .$ut['user_id']."\" >".$ut['user_pseudo']."</a></li>";
	   		}
			$html.= "</main><br>
			<aside><form action=\"index.php?action=search\" method=\"post\"><label for=\"recherche\">Modifier ma recherche :</label><input type=\"search\" name=\"q\" placeholder=\"Rechercher\">
			<div class=\"valider\"><button type=\"submit\" >Ok !</button></div>
			</form>
			<div class=\"retour\"><a href = \"index.php\">Retour à l'accueil</a>
			</div></aside>";
        } else{
           $html.= "<h3> Aucun utilisateur trouvé</h3></main>
			<aside><form action=\"index.php?action=search\" method=\"post\"><label for=\"recherche\">Modifier ma recherche :</label><input type=\"search\" name=\"q\" placeholder=\"Rechercher\">
			<div class=\"valider\"><button type=\"submit\">Ok !</button></div>
			</form>
			<div class=\"retour\"><a href = \"index.php\">Retour à l'accueil</a>
			</div></aside>";
        }
       return $html;
    }
?>
