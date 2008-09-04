
Installation
------------

1. Copy everything from src folder into public_html/mssi
2. Create required tables from db/schema.sql script
3. Do required change in public_html/mssi/mssi_config.php

Your MSSI url is http://site.com/mssi/mssi.php ; use this URL in smartmail configuration screen.

Create two cron job which calls following URLs periodically.

http://XOOPS_URL/modules/smartmail/_build_newsletter.php
http://XOOPS_URL/mssi/sender.php 

Done.

[CRON ENTRIES]

5,20,35,50 * * * * wget --output-document=- http://XOOPS_URL/modules/smartmail/_build_newsletter.php 
10,25,40,55 * * * * wget --output-document=- http://XOOPS_URL/mssi/sender.php