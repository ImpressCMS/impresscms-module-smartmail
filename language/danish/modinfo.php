<?php
define("_NL_MI_NAME", "Nyhedsbreve");
define("_NL_MI_DESC", "Styrer udsendelse og indhold af nyhedsbreve");

define("_NL_MI_NEWSLETTERS", "Nyhedsbreve");
define("_NL_MI_DISPATCHES", "Udsendelser");
define("_NL_MI_ADS", "Reklamer");

define("_NL_MI_B_NEWSLETTER", "Nyhedsbrev");
define("_NL_MI_B_CUSTOM", "Fritekst");

define("_NL_MI_PASSPHRASE", "Nyhedsbrev Kodeord");
define("_NL_MI_PASSPHRASE_DESC", "Kodeord, som tilføjes headeren på mails fra webserveren til mailing listen");

define("_NL_MI_PERMISSIONS", "Adgang");

define("_NL_MI_MSSI_URL", "Mail sender service interface URL");
define("_NL_MI_MSSI_URL_DESC", "URL til mail sender webservicen. Hvis du har installeret stand-alone mail sender servicen, skal du skrive URL'en til mssi.php her.");
define("_NL_MI_ALLOWED_HOSTS", "Tilladte IPer");
define("_NL_MI_ALLOWED_HOSTS_DESC", "Følgende IP adresser må køre _send_newsletter.php cron-jobbet");
define("_NL_MI_THROTTLE", "Throttle");
define("_NL_MI_THROTTLE_DESC", "Maksimum antal emails der må sendes i løbet af 1 time (0 = ubegrænset)");
define("_NL_MI_BATCHSIZE", "Batchstørrelse");
define("_NL_MI_BATCHSIZE_DESC", "Antal emails der må sendes på en gang (Server konfiguration kan begrænse dette antal - prøv dig frem)");
?>