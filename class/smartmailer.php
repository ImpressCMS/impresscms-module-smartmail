<?php
// $Id: smartmailer.php 1026 2008-04-14 15:27:26Z marcan $           //
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
// Authors: Jan Keller Pedersen (AKA Mithrandir) & Jannik Nielsen (Bitkid)   //
// URL: http://www.idg.dk/ http://www.xoops.org/ http://www.web-udvikling.dk //
// Project: The XOOPS Project                                                //
// ------------------------------------------------------------------------- //
require_once XOOPS_ROOT_PATH."/modules/smartmail/class/newslettermailer.php";
class NewsletterSmartMailer extends NewsletterMailer {
    var $email;

    function NewsletterSmartMailer(&$dispatch) {
        $this->NewsletterMailer($dispatch);
        $this->email = $this->dispatch->newsletter->getVar('newsletter_email', 'n');
    }

    /**
     * Send newsletter via the SmartMail Mail Sender Service Interface
     *
     * @param array $recipients
     * @return bool
     */
    function send(&$recipients) {
        // Make chunks of 1.000 recipients each
        $recipients = array_chunk($recipients, 10000);

        // Debug data - will be removed
        //    	$filename = XOOPS_UPLOAD_PATH."/mailtest.xml";
        //        $fp = fopen($filename, "w");
        //        fwrite($fp, $output);
        //        fclose($fp);

        include_once(XOOPS_ROOT_PATH."/modules/smartmail/class/mssi/nusoap.php");
        /**
         * @todo Change to dynamic, module config option
         */
        $client = new soapclient_base($GLOBALS['xoopsModuleConfig']['mssi_url'].'?wsdl', true);
        $client->setDebugLevel(0);

        $err = $client->getError();
        if ($err) {
            // Display the error
            $this->displaySoapErrors($client);
            return false;
        }
        include_once(XOOPS_ROOT_PATH."/modules/smartmail/class/mssi/job_builder.php");
        foreach (array_keys($recipients) as $i) {
            $job = new JobBuilder($this->subject, $this->fromEmail, $this->fromName, time(), time()+3600, $GLOBALS['xoopsModuleConfig']['newsletter_passphrase']);
            $job->setMailTemplate($this->subject, $this->body, '');
            foreach (array_keys($recipients[$i]) as $k) {
                $re =& $recipients[$i][$k];
                $job->addSubscriber(array('subscriber_id' => $re['subscriber_id'], 'uid' => $re['uid'], 'email' => $re['email'], 'uname' => $re['uname'], 'name' => $re['name']));
            }
            $result = $client->call('submitJob', array('jobXml' => $job->getXml()));

            // Check for a fault
            if ($client->fault) {
                echo '<h2>Fault</h2><pre>';
                print_r($result);
                echo '</pre>';
                $this->displaySoapErrors($client);
                return false;
            } else {
                // Check for errors
                $err = $client->getError();
                if ($err) {
                    // Display the error
                    $this->displaySoapErrors($client);
                    return false;
                } else {
                    // Display the result
                    echo '<pre>';
                    print_r($result);
                    echo '</pre>';
                }
            }
            //$this->displaySoapErrors($client);
            unset($recipients[$i], $job, $result);
        }

        return true;
    }

    /**
     * Display debug information from SOAP call
     *
     * @param soapclient_base $client
     */
    function displaySoapErrors(&$client) {
        echo '<h2>Error</h2><pre>' . $client->error_str .'</pre>';
        // Display the request and response
        echo '<h2>Request</h2>';
        echo '<pre>' . $client->request . '</pre>';
        echo '<h2>Response</h2>';
        echo '<pre>' . $client->response . '</pre>';
        // Display the debug messages
        echo '<h2>Debug</h2>';
        echo '<pre>' . $client->debug_str . '</pre>';
    }
}