<?php 
  function felicitations($modif){
   $message = $modif ? "Modifications enregistrées !" : "Merci pour votre inscription !";
   $html = "
    <h2>".$message."<h2>
    <br>
    <button type=\"button\" class=\"btn1\">
    <i class=\"fa-sharp fa-solid fa-cat fa-lg\" style=\"color: #B67645;\"></i><a href=\"index.php?action=profil&amp;id=".$_SESSION['LOGGED_ID']."\" >Voir mon profil</a></button>
    <button type=\"button\" class=\"btn2\">
    <i class=\"fa-sharp fa-solid fa-dog fa-flip-horizontal fa-lg\" style=\"color: #B67645;\"></i><a href=\"index.php\">Retour à l'accueil</a></button>";
    return $html;
  }

?>

