<?php

include_once('traitementLikes.php');
	
	function isAdmin($usertoCheck){
		//pour savoir si admin
		$pdo = new PDO('mysql:host=localhost;dbname=instapets', 'root', 'root'); //il faut appeler la base avant
		$stm = $pdo->prepare('SELECT user_id, user_admin FROM Users WHERE user_id = ?');
		$stm->execute(array($usertoCheck));
		$isadmin = $stm->fetch()['user_admin'] ;
		return $isadmin;
	}
    function display_Accueil(){
	
       	$html =  "<main>";

        $html.="<h3 class=\"publications\">&nbsp;&nbsp;Dernières Publications:</h3><div class=\"post\">";
        $pdo = new PDO('mysql:host=localhost;dbname=instapets', 'root', 'root');
		//pour avoir les posts
		$stmt = $pdo->prepare('SELECT Posts.post_title, Posts.post_contenu, Posts.post_picture, Posts.post_id, Users.user_pseudo, Users.user_id
					FROM Posts 
					INNER JOIN Users ON Posts.user_id = Users.user_id 
					LEFT JOIN Followings ON Posts.user_id = Followings.following_id AND Followings.user_id = ?
					WHERE Posts.user_id = ? OR Followings.following_id IS NOT NULL
					ORDER BY Posts.post_id DESC 
					LIMIT 20');
		$stmt->execute([$_SESSION['LOGGED_ID'], $_SESSION['LOGGED_ID']]);
		$posts = $stmt->fetchAll();
	    
		if(empty($posts)){
			$html .= "<p> il n'y a aucun poste à afficher pour le moment ! abonne toi à des utilisateurs ou poste quelque chose ! </p> ";
		}

		foreach ($posts as $post) {
			
			$html .= "<article><div class=\"publication-horsphoto\"><h3 class=\"titre\">" . htmlspecialchars($post['post_title']) . "</h3><p>" ;
			$html.="<p class=\"meta\">Posted by <a href=\"index.php?action=profil&amp;id=".$post['user_id']."\"><i class=\"fa-solid fa-user\" style=\"color: #553d00;\"></i>" . htmlspecialchars($post['user_pseudo'])."</a></p>";
			
			$html .= htmlspecialchars($post['post_contenu']) . "</p></div>";
			if($post['post_picture']!== null){
				$html .= "<img src=\"data:image/jpeg;base64," . base64_encode($post['post_picture']). "\" alt=\"Post Picture\" id=\"pic\" width=\"50\" height=\"50\"><br>";
		  	}
			$html.="</article>";
			$mot = countPostLikes($post['post_id']) > 1 ? " likes" : " like";
			$html.= "<div class=\"like\"><p>".countPostLikes($post['post_id']) . $mot."</p>";
			
			if(isPostLiked($post['post_id'], $_SESSION['LOGGED_ID'])){
				$html .= "<form method=\"post\">
				<button type=\"submit\" name=\"unlike{$post['post_id']}\"><i id=\"unlike\" class=\"fa-solid fa-heart\" style=\"color: #e32400;\"></i></button>
				</form></div>";
				if(isset($_POST['unlike' . $post['post_id']])){
					likePost($post['post_id'], $_SESSION['LOGGED_ID']);
				}

				
			}else{
				$html .= "<form method=\"post\">
				<button type=\"submit\" name=\"like{$post['post_id']}\"><i id=\"like\" class=\"fa-regular fa-heart\" style=\"color: #e32400;\"></i>Like!</button>
				</form></div>";
				if(isset($_POST['like' . $post['post_id']])){
					likePost($post['post_id'], $_SESSION['LOGGED_ID']);
				}

			}
			
			if((isAdmin($_SESSION['LOGGED_ID']))||($_SESSION['LOGGED_ID']==$post['user_id'])) {
				$html .= "<div class=\"supp\"><button type=\"button\"><a href=\"index.php?action=delete&amp;id=".$post['post_id']."\">Supprimer la publication</a></button></div>"; 
			}
			
		}
	
	    $html = $html. "</div></main><aside><div class=\"recherche\"><form action=\"index.php?action=search\" method=\"post\"><input type=\"search\" name=\"q\" placeholder=\"Rechercher\">
        <input type=\"submit\" value=\"Ok !\"></form></div>
	<div class=\"redirect\">
       <button type=\"button\"><a href=\"index.php?action=publier\">Publier</a></button><button type=\"button\"><a href=\"index.php?action=accueil\"><i class=\"fa-solid fa-house\" style=\"color: #666100;\"></i></a></button>";
   $html.="<button type=\"button\"><a href=\"index.php?action=profil&amp;id=".$_SESSION['LOGGED_ID']."\">MonCompte</a></button>";
    
    $html.="</div></aside>";

 
        return $html;
    }
?>
