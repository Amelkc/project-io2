<?php 
 include_once('traitementLikes.php');
 include_once('accueil.php');

function count_Followers($id){
  $pdo = new PDO('mysql:host=localhost;dbname=instapets', 'root', 'root');
  $followers = $pdo->prepare('SELECT count(*) FROM Followers WHERE user_id = ?');
  $followers->execute(array($id));
  $num = $followers->fetchColumn();
  return (string) $num;
}

function count_Followings($id){
  $pdo = new PDO('mysql:host=localhost;dbname=instapets', 'root', 'root');
  $followers = $pdo->prepare('SELECT count(*) FROM Followings WHERE user_id = ?');
  $followers->execute(array($id));
  $num = $followers->fetchColumn();
  return (string) $num;
}



  function is_Logged_User_Subscribed($id){
    // si la fonction est appelée c que id != userLoggedID donc pas besoin de revérifier
    $pdo = new PDO('mysql:host=localhost;dbname=instapets', 'root', 'root');
    $followers = $pdo->prepare('SELECT count(*) FROM Followings WHERE following_id = ? AND user_id = ?');
    $followers->execute(array($id, $_SESSION['LOGGED_ID']));
    $num = $followers->fetchColumn();
    return ($num > 0 );
  }
  
  function display_Profil($id){

    $pdo = new PDO('mysql:host=localhost;dbname=instapets', 'root', 'root');
    // on recupere l'utilisateur en parametres
    $Recup = $pdo->prepare('SELECT * FROM Users WHERE user_id = ?');
    $Recup->execute(array($id));
    // on vérifie que l'id correspond bien à un utilisateur
    if($Recup->rowCount()==0){$erreur ="<h1>page non trouvée</h1><a href=\"index.php\">Retour à l'accueil</a>"; return $erreur;} 
    $pseudo_profil = $Recup->fetch()['user_pseudo'];


    //pour test admin car sinon les false ne passent pas dans un fetch tout court
    $adm = $pdo->prepare('SELECT user_admin FROM Users WHERE user_id = ?');
    $adm->execute(array($id));
    $result = $adm->fetch();
    $isAdmin = $result !== false ? $result['user_admin'] : false;


    $affichageH2 ="&commat;"; //'@'
    if($id != $_SESSION['LOGGED_ID']){
	    $affichageH2 .= $pseudo_profil;
	    if($isAdmin) {$affichageH2.=" &#9733;"; }// petie étoile de certification ;)
    } else{
      $affichageH2 .= "Moi";
	    if($isAdmin){$affichageH2.=" &#9733;"; }
    }
 
    $html = "<main><div class=\"infos\"><h2>".$affichageH2."</h2>";


    if($id == $_SESSION['LOGGED_ID']){
      $nbFollowers = count_Followers($id) . " abonné(s)";
      $html.= "<div class=\"abo\"><p><a href=\"index.php?action=abonne&amp;id=".$id."\">$nbFollowers</a> &emsp;";
      $nbFollowings = count_Followings($id)." abonnement(s)</p>";
      $html .= "<a href=\"index.php?action=abonnement&amp;id=".$id."\">$nbFollowings</a></p></div>";
    }else {
      $html.="<div class=\"abo\"><p>".count_Followers($id)." abonné(s) &emsp;";
      $html.=count_Followings($id)." abonnement(s)</p></div>";
    }
	  
    if($id != $_SESSION['LOGGED_ID']){
      if(is_Logged_User_Subscribed($id)){
        $html .= "<div class=\"actions\"><button type=\"button\"><a href=\"index.php?action=unfollow&amp;id=".$id."\">Se désabonner</a></button>
        </div></div>";
      }else{
        $html .= "<div class=\"actions\"><button type=\"button\"><a href=\"index.php?action=subscribe&amp;id=".$id."\">Suivre</a></button>
        </div></div>";
      }
    }else{
      $html .=  "<div class=\"actions\"><button type=\"button\"><a href=\"index.php?action=modifier\">Modifier mes infos</a></button>
     <button type=\"button\"><a href=\"index.php?action=deconnexion\">Se déconnecter</a></button></div></div>";
    }

    // afficher les posts du profil
	  $html.="<h3 class=\"publications\">&nbsp;&nbsp;Publications:</h3>";
		$stmt = $pdo->prepare('SELECT Posts.post_title, Posts.post_contenu, Posts.post_picture, Posts.post_id, Users.user_pseudo, Users.user_id 
                  FROM Posts 
                  INNER JOIN Users 
                  ON Posts.user_id = Users.user_id 
                  WHERE Users.user_id = ? 
                  ORDER BY Posts.post_id 
                  DESC LIMIT 20');
    $stmt->execute([$id]);
    $posts = $stmt->fetchAll();
    if(empty($posts)&& $id == $_SESSION['LOGGED_ID']){
      $html .= "<p>il n'y a aucun poste à afficher pour le moment ! poste quelque chose ! </p>";
    } else if(empty($posts)){
      $html .= "<p>il n'y a aucun poste à afficher pour le moment ! </p>";
    }
    foreach ($posts as $post) {
      $html .= "<div class=\"post\"><article>
      <div class=\"publication-horsphoto\"><h3 class=\"titre\">" . htmlspecialchars($post['post_title']) . "</h3>" ;
      $html .=  "<p>".htmlspecialchars($post['post_contenu'])."</p>
      </div>"; 
      if($post['post_picture']!== null){
        $html .= "<div class=\"publication-photo\">
        <img src=\"data:image/jpeg;base64," . base64_encode($post['post_picture']). "\" alt=\"Post Picture\" id=\"pic\" width=\"50\" height=\"50\" >
        <br></div>";
      }
      $html.="</article>";
      $mot = countPostLikes($post['post_id']) > 1 ? " likes" : " like";

      //si c'est notre compte on peut voir les gens qui ont like les posts
      if($id == $_SESSION['LOGGED_ID']){
        $motfin = countPostLikes($post['post_id']) . $mot;
        $html.= "<div class=\"like\"><a href=\"index.php?action=LikedBy&amp;id=".$post['post_id']."\">$motfin </a>" ;
      }else {
        $html.= "<div class=\"like\"><p>".countPostLikes($post['post_id']) . $mot."</p>";
      }

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
      if(isAdmin($_SESSION['LOGGED_ID']) || $id == $_SESSION['LOGGED_ID']){
        $html.="<div class=\"supp\"><button type=\"button\"><a href=\"index.php?action=delete&amp;id=".$post['post_id']."\">Supprimer la publication</a></button>
        </div>"; 
      } 

      $html .= "</div><br>";
		}

	  $html .= "</main><aside>
    <div class=\"recherche\"><form action=\"index.php?action=search\" method=\"post\"><input type=\"search\" name=\"q\" placeholder=\"Rechercher\">
  	<button type=\"submit\">Ok !</button></form></div> 
    <div class=\"redirect\">
		<button type=\"button\"><a href=\"index.php?action=publier\">Publier</a></button>
    <button type=\"button\"><a href=\"index.php?action=accueil\"><i class=\"fa-solid fa-house\" style=\"color: #666100;\"></i></a></button>";
    if($id != $_SESSION['LOGGED_ID']){ $html.="<button type=\"button\"><a href=\"index.php?action=profil&amp;id=".$_SESSION['LOGGED_ID']."\">MonCompte</a></button>";}
    
    $html.="</aside>";
    return $html;
  }

    function follow($id){
      if(isset($_SESSION['LOGGED_MDP']) && isset($_SESSION['LOGGED_PSEUDO'])) {
        $pdo = new PDO('mysql:host=localhost;dbname=instapets', 'root', 'root');
    
        // verifie que le user a follow existe
        $stmt = $pdo->prepare('SELECT * FROM Users WHERE user_id = ?');
        $stmt->execute([$id]);
        $user = $stmt->fetch();
  
        if(!$user) {
          echo "l' user n'existe pas.";
          return;
        }
  
        // si il est deja suivi
        $stmt = $pdo->prepare('SELECT * FROM Followings WHERE user_id = ? AND following_id = ?');
        $stmt->execute([$_SESSION['LOGGED_ID'], $id]);
        $followed = $stmt->fetch();
  
        if($followed) {
        //il va donc l'unfollow
          return unFollow($id);
        } 
        else{
          // sinon il va le follow
          $pdo->beginTransaction();

          $stmt = $pdo->prepare('INSERT INTO Followings (user_id, following_id) VALUES (?, ?)');
          $stmt->execute([$_SESSION['LOGGED_ID'], $id]);

          $stmt = $pdo->prepare('INSERT INTO Followers (user_id, followers_id) VALUES (?, ?)');
          $stmt->execute([$id, $_SESSION['LOGGED_ID']]);

          $pdo->commit();

          return;
        }
      }
    }

    function getUsersWhoFollow($user) {
      $pdo = new PDO('mysql:host=localhost;dbname=instapets', 'root', 'root');
      $stmt = $pdo->prepare('SELECT Users.user_pseudo, Users.user_id 
                   FROM Followers 
                   JOIN Users ON Followers.followers_id = Users.user_id 
                   WHERE Followers.user_id = ? 
                   ORDER BY Users.user_id DESC');
      $stmt->execute(array($user));
      $users = $stmt->fetchAll();
      
      $html = "<h2>Abonnés :</h2><br>";
      if(count($users) > 0) {//si il y des followers
        foreach($users as $user) {
          $html .= "<li><i class=\"fa-solid fa-user\" style=\"color: #ada368;\"></i><a href=\"index.php?action=profil&amp;id=" .$user['user_id']."\">" . $user['user_pseudo'] . "</a></li>";
        }
      } else {
        $html .= "<div class=\"text\"><p>Aucun abonné</p></div>";
      }
      $html .= "<a href=\"index.php?action=profil&amp;id=".$_SESSION['LOGGED_ID']."\"><i class=\"fa-solid fa-user\" style=\"color: #553d00;\"></i>Retour sur mon profil</a>";
      
      return $html;
    }

    function getUsersWhoFollowed($user) {
      $pdo = new PDO('mysql:host=localhost;dbname=instapets', 'root', 'root');
      $stmt = $pdo->prepare('SELECT Users.user_pseudo, Users.user_id 
                    FROM Followings 
                    JOIN Users ON Followings.following_id = Users.user_id 
                    WHERE Followings.user_id = ? 
                    ORDER BY Users.user_id DESC');
      $stmt->execute(array($user));
      $users = $stmt->fetchAll();
      
      $html = "<h2>Abonnements :</h2><br>";
      if(count($users) > 0) {//si il y a des followings
        foreach($users as $user) {
            $html .= "<li><i class=\"fa-solid fa-user\" style=\"color: #ada368;\"></i><a href=\"index.php?action=profil&amp;id=" .$user['user_id']."\">" . $user['user_pseudo'] . "</a></li>";
        }
      } else {
        $html .= "<div class=\"text\"><p>Aucun abonnement</p></div>";
      }
      $html .= "<a href=\"index.php?action=profil&amp;id=".$_SESSION['LOGGED_ID']."\"><i class=\"fa-solid fa-user\" style=\"color: #553d00;\"></i>Retour sur mon profil</a>";
      
      return $html;
  }
  
      

    function unFollow($id){//il faut supprimer le followers et le followings
      if(isset($_SESSION['LOGGED_MDP']) && isset($_SESSION['LOGGED_PSEUDO'])){
        $pdo = new PDO('mysql:host=localhost;dbname=instapets', 'root', 'root');

        $stmt = $pdo->prepare("DELETE FROM Followings WHERE user_id = ? AND following_id = ?");
        $stmt->execute(array($_SESSION['LOGGED_ID'], $id));

        $stmt = $pdo->prepare("DELETE FROM Followers WHERE user_id = ? AND followers_id = ?");
        $stmt->execute(array($id,$_SESSION['LOGGED_ID']));
      }
    }
    

?>
    

    
    
