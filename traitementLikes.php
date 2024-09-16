<?php

function likePost($post_id, $user_id) {
  $pdo = new PDO('mysql:host=localhost;dbname=instapets', 'root', 'root');
  if(!isPostLiked($post_id,$user_id)){
    //si la personne n'a jms like on créé la ligne
    $stmt = $pdo->prepare("INSERT INTO Likes (post_id, user_id) VALUES (?, ?) ");
    $stmt->execute([$post_id, $user_id]);
    return;
  }else{
    $stmt = $pdo->prepare("DELETE FROM Likes WHERE post_id = ? AND user_id = ?");
    $stmt->execute([$post_id, $user_id]);  
  }
}

//test si le post est deja dans la base donc like
function isPostLiked($post_id, $user_id) {
  $pdo = new PDO('mysql:host=localhost;dbname=instapets', 'root', 'root');
  $stmt = $pdo->prepare("SELECT * FROM Likes WHERE post_id = ? AND user_id = ?");
  $stmt->execute([$post_id, $user_id]);
  return $stmt->rowCount() > 0;
}
  

//compte le nb de likes pour un post donné
function countPostLikes($post_id) {
  $pdo = new PDO('mysql:host=localhost;dbname=instapets', 'root', 'root');
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM Likes WHERE post_id = ?");
  $stmt->execute([$post_id]);
  return $stmt->fetchColumn();
}

  function getUsersWhoLikedPost($postId) {//recupérer les users qui ont like un post donné
    $pdo = new PDO('mysql:host=localhost;dbname=instapets', 'root', 'root');
    $stmt = $pdo->prepare('SELECT Users.user_pseudo, Users.user_id 
                  FROM Likes 
                  JOIN Users ON Likes.user_id = Users.user_id 
                  WHERE Likes.post_id = ? 
                  ORDER BY Users.user_id DESC');
    $stmt->execute(array($postId));
    $users = $stmt->fetchAll();
    
    $html = "<h2>Les personnes qui ont aimé ce post</h2><br>";
    if(count($users) > 0) {
      foreach($users as $user) {
          $html .= "<li><i class=\"fa-solid fa-user\" style=\"color: #ada368;\"></i><a href=\"index.php?action=profil&amp;id=" .$user['user_id']."\">" . $user['user_pseudo'] . "</a></li>";
      }
    } else {
      $html .= "<div class=\"text\"><p>Aucun like</p></div>";

    }
    $html .= "<a href=\"index.php?action=profil&amp;id=".$_SESSION['LOGGED_ID']."\"><i class=\"fa-solid fa-user\" style=\"color: #553d00;\"></i>Retour sur mon profil</a>";
    
    return $html;
}

  



?>
