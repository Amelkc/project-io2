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
	

        $html ="<main><h3 class=\"publications\">&nbsp;&nbsp;Dernières Publications:</h3>";
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
			$html .= "<p> il n'y a aucun poste à afficher pour le moment ! abonne toi à des utilisateurs ou ajoute quelque chose ! </p> ";
		}

		foreach ($posts as $post) {
			
			$html .= "<div class=\"post\"><article>
			<div class=\"publication-horsphoto\"><h3 class=\"titre\">" . htmlspecialchars($post['post_title']) . "</h3><p>" ;
			$html.="<p class=\"meta\">Posted by <a href=\"index.php?action=profil&amp;id=".$post['user_id']."\"><i class=\"fa-solid fa-user\" style=\"color: #553d00;\"></i>" . htmlspecialchars($post['user_pseudo'])."</a></p>";
			
			$html .= htmlspecialchars($post['post_contenu']) . "</p></div>";
			if($post['post_picture']!== null){//si il y a une photo seulement on l'affiche
				$html .= "<div class=\"publication-photo\">
				<img src=\"data:image/jpeg;base64," . base64_encode($post['post_picture']). "\" alt=\"Post Picture\" id=\"pic\" width=\"80\" height=\"80\">
				<br></div>";
		  	}
			$html.="</article>";
			$mot = countPostLikes($post['post_id']) > 1 ? " likes" : " like"; //fontion qui vient de traitementLikes.php 
			$html.= "<div class=\"like\"><p>".countPostLikes($post['post_id']) . $mot."</p>";
			
			if(isPostLiked($post['post_id'], $_SESSION['LOGGED_ID'])){//fontion qui vient de traitementLikes.php 
				$html .= "<form method=\"post\">
				<button type=\"submit\" name=\"unlike{$post['post_id']}\"><i id=\"unlike\" class=\"fa-solid fa-heart\" style=\"color: #e32400;\"></i></button>
				</form></div>";
				if(isset($_POST['unlike' . $post['post_id']])){
					likePost($post['post_id'], $_SESSION['LOGGED_ID']);//fontion qui vient de traitementLikes.php 
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
			$html .= "</div><br>";
		}
	
	    $html = $html. "</main><aside>
		<div class=\"recherche\"><form action=\"index.php?action=search\" method=\"post\"><input type=\"search\" name=\"q\" placeholder=\"Rechercher\">
        <input type=\"submit\" value=\"Ok !\"></form></div>
		<div class=\"redirect\">
       	<button type=\"button\"><a href=\"index.php?action=publier\">Publier</a></button>
		<button type=\"button\"><a href=\"index.php?action=accueil\"><i class=\"fa-solid fa-house\" style=\"color: #666100;\"></i></a></button>
		<button type=\"button\"><a href=\"index.php?action=profil&amp;id=".$_SESSION['LOGGED_ID']."\">MonCompte</a></button>
		</div></aside>";
    

 
        return $html;
    }
?>
