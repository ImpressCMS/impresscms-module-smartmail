<?php
// $Id: _send_newsletter.php 1026 2008-04-14 15:27:26Z marcan $
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
set_time_limit(0); //unlimited, we'll stop when throttle maximum is reached or queue is empty
ini_set("Memory limit", "32M"); //make sure we have enough memory

include "header.php";
ob_end_clean();
$allowed_hosts = array_map('trim', explode(',', $xoopsModuleConfig['allowed_hosts']));
$count = 0;
$starttime = time();
if( in_array($_SERVER['REMOTE_ADDR'],  $allowed_hosts) ) {
    
    // Check lock dir for concurrency collisions avoidance
    $lock_dir = XOOPS_CACHE_PATH.'/smartmail_send_lockdir';

    if( file_exists($lock_dir) ) {
        echo "Lock dir exists, terminating process";
        die;
    }
    if( ! mkdir($lock_dir) ) {
        echo "Could not create lock dir";
        die;
    }
    
    //    header("Content-Type: text/plain; charset: iso-8859-1");
    echo "Starting cron at ".date("H:i:s", $starttime);
    
    $throttle_max = intval($xoopsModuleConfig['throttle_max']);
    $batch_size = intval($xoopsModuleConfig['batch_size']);
    $throttle = 0;
    
    if ($throttle_max > 0) {
        // Get throttle value from database
        $recipient_handler =& xoops_getmodulehandler('recipient');
        $throttle_criteria = new CriteriaCompo(new Criteria('status', 2));
        $throttle_criteria->add(new Criteria('status_updated', time()-3600, '>='));
        $throttle = $recipient_handler->getCount($throttle_criteria);
    }
    
    // If throttle is below treshold
    if ($throttle_max == 0 || $throttle < $throttle_max) {
        // Get job
        $job_handler =& xoops_getmodulehandler('job');
        $job =& $job_handler->getNextJob();

        if (is_object($job) ) {
            // Send mails
            $mailer =& getMailer();
            /* @var $mailer XoopsMailer */
            $mailer->setFromEmail($job->getVar('job_fromemail', 'n'));
            $mailer->setFromName($job->getVar('job_fromname', 'n'));
            $mailer->setSubject(html_entity_decode($job->getVar('job_subject', 'n')));
            $mailer->multimailer->IsHTML($job->getVar('job_is_html') == 1);
            
            $recipient_handler =& xoops_getmodulehandler('recipient');
            $processqueue = true;
            while ($processqueue) {
                // Get next recipient
                $recipient = $recipient_handler->getNextRecipient($job);
                if (is_object($recipient)) {
                    $recipient_data = $recipient->getVar('vars');
                    $recipient_data['email'] = $recipient->getVar('email');
                    $recipient_data['uid'] = $recipient->getVar('uid');

                    $mailer->toEmails = array(); //reset recipients
                    $mailer->errors = array();
                    $mailer->success = array();
                    //$mailer->multimailer->Sender = "bounce@example.com"; // To be implemented with bounce checking
                    $mailer->setBody($job->getBody($recipient_data)); //set body
                    $mailer->setToEmails($recipient_data['email']); // set recipient
                    if (!$mailer->send(true)) {
                        echo "<br />".implode(' - ', $mailer->getErrors(false));
                    }
                    else {
//                        echo "<br />mail sent to ".$recipient_data['email'];
                    }
                    $recipient_handler->updateStatus($recipient, 2);
                    // Count one up for the throttle
                    $throttle++;
                    $count++;
                    if ( ($throttle_max > 0 && $throttle >= $throttle_max) || $count >= $batch_size) {
                        // Check if job is finished
                        $job_handler->checkJob($job);
                        // End processing
                        $processqueue = false;
                    }
                }
                else {
                    // Are we finished with this job? If so, get next job
                    $job =& $job_handler->checkJob($job);
                    if (!is_object($job)) {
                        // Nothing in queue, stop processing
                        $processqueue = false;
                    }
                }
            }
        }
    }
    if( ! rmdir($lock_dir) ) {
        echo "Could not remove lock dir";
    }
} else {
    trigger_error( 'Error '.$_SERVER['REMOTE_ADDR'], E_USER_ERROR );
}
$endtime = time();
echo "<br />Done at ".date("H:i:s", $endtime)."  $count emails sent (".round($count/($endtime-$starttime), 2)." mails/second)";
// include XOOPS_ROOT_PATH."/footer.php";
?>