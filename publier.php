<?php
    function publier(){
        $html = "<main>
        <h2>Nouvelle publication</h2>
        <form method=\"POST\" enctype=\"multipart/form-data\">

        <label for=\"post_title\">Titre:</label>
        <input type=\"text\" name=\"post_title\" required><br>
        
        <label for=\"post_contenu\">Contenu:</label><br>
        <textarea name=\"post_contenu\" rows=\"5\" required></textarea><br>
        
        <label for=\"post_picture\">Image:</label>
        <input type=\"file\" accept=\".jpeg, .jpg, .png, .mp4, .avi, .mov, .flv\" name=\"post_picture\"><br>
        
        <button type=\"submit\" name=\"submit\">Publier</button>
        </form>

        <div class=\"retour\"><a href=\"index.php?action=profil&amp;id=".$_SESSION['LOGGED_ID']."\">Retour sur mon profil</a></div>
        </main>";


        $pdo = new PDO('mysql:host=localhost;dbname=instapets', 'root', 'root');

        $user_id = $_SESSION['LOGGED_ID'];
        //qd le form a été envoyé
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $post_title = $_POST['post_title'];
            $post_contenu = $_POST['post_contenu'];
            $post_picture = null;


     
            if(isset($_FILES['post_picture']['error']) && $_FILES['post_picture']['error'] == 0) {
                $post_picture = file_get_contents($_FILES['post_picture']['tmp_name']);
            
            } else if((isset($_FILES['post_picture']['error'])) && $_FILES['post_picture']['error'] != 4){
                $html.= "<script type=\"text/javascript\">window.alert('Une erreur est survenue, veuillez réessayer')</script><button type=\"button\"><a href=\"index.php?action=publier\">Réessayer</a></button>";
                return $html;

            }
            

            $stmt = $pdo->prepare('INSERT INTO Posts (user_id, post_title, post_contenu, post_picture) VALUES (?, ?, ?, ?)');
            $stmt->execute([$user_id, $post_title, $post_contenu, $post_picture]);
            $html.= "<script type=\"text/javascript\">window.alert('Publication publiée avec succès !')</script><div class=\"retour\"><a href=\"index.php\">Retour à mon fil d'actualité</a></div>";
            return $html;

        }
        return $html;
       
    }   

    function delet($id){//pour supprimer le post il faut avant supprimer tous les likes associer car il y a une clé étrangère
	    $db = new PDO('mysql:host=localhost;dbname=instapets', 'root', 'root');

        $stmt2 = $db->prepare("DELETE FROM Likes WHERE post_id = ?");
        $stmt2->execute([$id]);

	    $stmt = $db->prepare("DELETE FROM Posts WHERE post_id = ?");
	    $stmt->execute([$id]);
    }

    


    ?>
