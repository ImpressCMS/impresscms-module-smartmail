<?php
define("_NL_MI_NAME", "SmartMail");
define("_NL_MI_DESC", "Contr�le les envois et les contenus des bulletins");

define("_NL_MI_NEWSLETTERS", "Bulletins");
define("_NL_MI_DISPATCHES", "Envois");
define("_NL_MI_ADS", "Ads");

define("_NL_MI_B_NEWSLETTER", "Bulletin");
define("_NL_MI_B_CUSTOM", "Bloc personnalis�");

define("_NL_MI_PASSPHRASE", "Mot de passe des bulletins");
define("_NL_MI_PASSPHRASE_DESC", "Expression ajout�e � l'en-t�teto des courriels envoy�s par le serveur web � la liste d'envoi");

define("_NL_MI_PERMISSIONS", "Privil�ges");

define("_NL_MI_MSSI_URL", "URL de l'interface du service d'envoi de courriel");
define("_NL_MI_MSSI_URL_DESC", "URL du service web d'envoi de courriel. Si vous avez install� un service d'envoi de courriel ind�pendant, indiquez ici l'URL de mssi.php.");
define("_NL_MI_ALLOWED_HOSTS", "IP autoris�es");
define("_NL_MI_ALLOWED_HOSTS_DESC", "ces adresses IP peuvent ex�cuter les t�ches cron _build_newsletter.php et _send_newsletter.php");
define("_NL_MI_THROTTLE", "Valeur Throttle");
define("_NL_MI_THROTTLE_DESC", "Nombre maximal de courriels � envoyer en une heure (0 = illimit�)");
define("_NL_MI_BATCHSIZE", "Batch size");
define("_NL_MI_BATCHSIZE_DESC", "Nombre de courriels � envoyer � chaque envoi (la configuration du serveur peut limiter ce nombre)");
?>