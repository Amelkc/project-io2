faire un site web style réseau social un peu comme instagram pour un projet d'université.

Faire fonctionner le site : 

Si la base de donnée n'a pas été créer :
Le fichier base.sql représente l'entiereté de la base de donnée
[ Sur un terminal dans /Applications/MAMP/htdocs/project-io2 faire :
/Applications/MAMP/Library/bin/mysql -u root -p
mdp = root
( quand il y a un changement dans le fichier de la base il faut la supp et la recréer :
DROP DATABASE instapets; )
source /Applications/MAMP/htdocs/project-io2/base.sql (pour la créer)
exit pour sortir du sql. la base de donnée a été créé on peut donc aller sur le site ]

sur le lien http://localhost:8888/phpMyAdmin5/index.php?route=/sql&pos=0&db=instapets&table=Users
on peut modifier les users pour définir les admins en mettant 1 à user_admin après que les comptes soient créé sur le site en lui même

quand on va dans le dossier on arrive direct sur la page de connexion ou d'inscription si l'utilisateur n'as pas encore ouvert le site et s'est connecté