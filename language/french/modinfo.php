<?php
define("_NL_MI_NAME", "SmartMail");
define("_NL_MI_DESC", "Contrôle les envois et les contenus des bulletins");

define("_NL_MI_NEWSLETTERS", "Bulletins");
define("_NL_MI_DISPATCHES", "Envois");
define("_NL_MI_ADS", "Ads");

define("_NL_MI_B_NEWSLETTER", "Bulletin");
define("_NL_MI_B_CUSTOM", "Bloc personnalisé");

define("_NL_MI_PASSPHRASE", "Mot de passe des bulletins");
define("_NL_MI_PASSPHRASE_DESC", "Expression ajoutée à l'en-têteto des courriels envoyés par le serveur web à la liste d'envoi");

define("_NL_MI_PERMISSIONS", "Privilèges");

define("_NL_MI_MSSI_URL", "URL de l'interface du service d'envoi de courriel");
define("_NL_MI_MSSI_URL_DESC", "URL du service web d'envoi de courriel. Si vous avez installé un service d'envoi de courriel indépendant, indiquez ici l'URL de mssi.php.");
define("_NL_MI_ALLOWED_HOSTS", "IP autorisées");
define("_NL_MI_ALLOWED_HOSTS_DESC", "ces adresses IP peuvent exécuter les tâches cron _build_newsletter.php et _send_newsletter.php");
define("_NL_MI_THROTTLE", "Valeur Throttle");
define("_NL_MI_THROTTLE_DESC", "Nombre maximal de courriels à envoyer en une heure (0 = illimité)");
define("_NL_MI_BATCHSIZE", "Batch size");
define("_NL_MI_BATCHSIZE_DESC", "Nombre de courriels à envoyer à chaque envoi (la configuration du serveur peut limiter ce nombre)");
?>