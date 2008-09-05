<?php
define("_NL_MI_NAME", "SmartMail");
define("_NL_MI_DESC", "Controls dispatches and contents of newsletters");

define("_NL_MI_NEWSLETTERS", "Newsletters");
define("_NL_MI_DISPATCHES", "Dispatches");
define("_NL_MI_ADS", "Ads");

define("_NL_MI_B_NEWSLETTER", "Newsletter");
define("_NL_MI_B_CUSTOM", "Custom block");

define("_NL_MI_PASSPHRASE", "Newsletter passphrase");
define("_NL_MI_PASSPHRASE_DESC", "Passphrase that is added to the header of mails sent from the web server to the mailing list");

define("_NL_MI_PERMISSIONS", "Permissions");

define("_NL_MI_MSSI_URL", "Mail sender service interface URL");
define("_NL_MI_MSSI_URL_DESC", "URL to the mail sender webservice. If you have installed the stand-alone mail sender service, please input the URL to mssi.php here.");
define("_NL_MI_ALLOWED_HOSTS", "Allowed IPs");
define("_NL_MI_ALLOWED_HOSTS_DESC", "These IP addresses can execute the _build_newsletter.php and _send_newsletter.php cron-jobs");
define("_NL_MI_THROTTLE", "Throttle value");
define("_NL_MI_THROTTLE_DESC", "Maximum number of mails to send within one hour (0 = unlimited)");
define("_NL_MI_BATCHSIZE", "Batch size");
define("_NL_MI_BATCHSIZE_DESC", "Number of mails to send in one go (Server configuration may limit this number)");
?>