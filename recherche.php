<?php
    function search($user){
    
        $pdo = new PDO('mysql:host=localhost;dbname=instapets', 'root', 'root');
           
	    $html = "<h2>Résultat pour '".$user."'</h2>";
        $stmt = $pdo->prepare('SELECT user_pseudo, user_id FROM `Users` WHERE user_pseudo = ? ORDER BY user_id DESC'); 
		$stmt->execute(array($user));
		$utilisateurs = $stmt->fetchAll();
       
        if(count($utilisateurs) > 0){
            	foreach($utilisateurs as $ut){
            		$html.= "<li><a href=\"index.php?action=profil&amp;id=" .$ut['user_id']."\" >".$ut['user_pseudo']."</a></li>";
	   	}
		$html.= "
	   <form action=\"index.php?action=search\" method=\"post\"><input type=\"search\" name=\"q\" placeholder=\"Rechercher\">
  		<label for=\"recherche\">Modifier ma recherche :</label><input type=\"submit\" value=\"Ok !\">
	   </form><aside><a href = \"index.php\">Retour à l'accueil</a></aside>";
        } else{
           $html.= "<h3> Aucun utilisateur trouvé</h3>
	   <form action=\"index.php?action=search\" method=\"post\"><input type=\"search\" name=\"q\" placeholder=\"Rechercher\">
  		<label for=\"recherche\">Modifier ma recherche :</label><input type=\"submit\" value=\"Ok !\">
	   </form><aside><a href = \"index.php\">Retour à l'accueil</a></aside>";
        }
       return $html;
    }
?>
