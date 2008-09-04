<?php
// $Id: _build_newsletter.php 1026 2008-04-14 15:27:26Z marcan $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.xoops.org/>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //
set_time_limit(1200); // Tests show 150 seconds for 40000 subscribers, but we want to be certain that time will not run out
ini_set("Memory limit", "128M"); //make sure we have enough memory - SOAP calls can be VERY memory-demanding with many subscribers

include "header.php";
$allowed_hosts = array_map('trim', explode(',', $xoopsModuleConfig['allowed_hosts']));
if( in_array($_SERVER['REMOTE_ADDR'],  $allowed_hosts) ) {
    // Check lock dir for concurrency collisions avoidance
    $lock_dir = XOOPS_CACHE_PATH.'/smartmail_build_lockdir';

    //header("Content-Type: text/plain; charset: iso-8859-1");
    echo "Starting cron at ".date("H:i:s", time())."<br />";
    if( file_exists($lock_dir) ) {
        echo "Lock dir exists, terminating process";
        die;
    }
    if( ! mkdir($lock_dir) ) {
        echo "Could not create lock dir";
        die;
    }

    if (!isset($xoopsTpl) || !is_object($xoopsTpl)) {
        include_once(XOOPS_ROOT_PATH."/class/template.php");
        $xoopsTpl = new XoopsTpl();
    }

    $dispatch_handler = xoops_getmodulehandler('dispatch', 'smartmail');
    /* @var $dispatch_handler NewsletterDispatchHandler */
    $dispatches = $dispatch_handler->getReadyDispatches();

    echo "Queue: " . count($dispatches) . "<br />";

    if (count($dispatches) > 0) {
        foreach (array_keys($dispatches) as $i) {
            $newsletter = $dispatches[$i]->getNewsletter();
            if ($dispatches[$i]->send(false)) {
                echo $newsletter->getVar('newsletter_name')." Sent" . "<br />";
            }
            else {
                echo $newsletter->getVar('newsletter_name')." could not be sent" . "<br/>";
            }
        }
    }

    if( ! rmdir($lock_dir) ) {
        echo "Could not remove lock dir";
    }
    echo "<br />Done at ".date("H:i:s", time());
} else {
    trigger_error( 'Error '.$_SERVER['REMOTE_ADDR'], E_USER_ERROR );
}

// include XOOPS_ROOT_PATH."/footer.php";
?>