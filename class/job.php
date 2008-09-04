<?php
// $Id: job.php 1026 2008-04-14 15:27:26Z marcan $               //
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
if (!class_exists('SmartPersistableObjectHandler')) {
	include_once(XOOPS_ROOT_PATH . "/modules/smartobject/class/smartobjecthandler.php");
}

define("JOB_READY", 0);
define("JOB_FINISHED", 1);
define("JOB_ONHOLD", 2);

class SmartmailJob extends SmartObject {
    function SmartmailJob() {
        $this->initVar("job_id", XOBJ_DTYPE_INT);
        $this->initVar("job_description", XOBJ_DTYPE_TXTAREA);
        $this->initVar("job_from_addr", XOBJ_DTYPE_TXTBOX);
        $this->initVar("job_from_name", XOBJ_DTYPE_TXTBOX);
        $this->initVar("job_subject", XOBJ_DTYPE_TXTBOX);
        $this->initVar("job_is_html", XOBJ_DTYPE_INT);
        $this->initVar("job_body_html", XOBJ_DTYPE_TXTAREA, '');
        $this->initVar("job_body_txt", XOBJ_DTYPE_TXTAREA, '');
        $this->initVar("job_vars", XOBJ_DTYPE_ARRAY);
        $this->initVar("job_run_after", XOBJ_DTYPE_INT);
        $this->initVar("job_run_not_after", XOBJ_DTYPE_INT);
        $this->initVar("job_status", XOBJ_DTYPE_INT);
        $this->initVar("job_started", XOBJ_DTYPE_INT);
        $this->initVar("job_finished", XOBJ_DTYPE_INT);
    }
    
    /**
     * Replace {NAME} with values from recipient 
     *
     * @param array $recipient
     * @return string
     */
    function getBody($recipient) {
        $body = base64_decode($this->getVar('job_body_html', 'n'));
        foreach ($recipient as $name => $value) {
            $searchVars[] = '{' . strtoupper($name) . '}';
            $replaceVars[] = $value;
        }
        $body = str_replace($searchVars, $replaceVars, $body);
        return $body;
    }
}

class SmartmailJobHandler extends SmartPersistableObjectHandler {
    function SmartmailJobHandler($db) {
        parent::SmartPersistableObjectHandler($db, "job", "job_id", "job_subject", "", "smartmail");
    }
    
    /**
     * Get next job in the job list
     *
     * @return mixed
     */
    function getNextJob() {
        $recipient_handler =& xoops_getmodulehandler('recipient', 'smartmail');
        $sql = "SELECT job.* FROM ".$this->table." job WHERE job_status <= 1 AND job_id IN (SELECT distinct job_id FROM ".$recipient_handler->table." WHERE status = 0) ORDER BY job_run_after ASC LIMIT 0, 1";
        $result = $this->db->query($sql);
        if ($result && $this->db->getRowsNum($result) > 0) {
            $ret = $this->convertResultSet($result);
            return $ret[0];
        }
        echo $this->db->error();
        return false;
    }
    
    /**
     * Check whether a job has more items in the queue and if not return next job
     *
     * @param SmartmailJob $job
     * @return SmartmailJob
     */
    function checkJob($job) {
        $queue_handler =& xoops_getmodulehandler('recipient', 'smartmail');
        $criteria = new CriteriaCompo(new Criteria('job_id', $job->getVar('job_id')));
        $criteria->add(new Criteria('status', 0));
        $count = $queue_handler->getCount($criteria);
        if ($count == 0) {
            // Update job status, job is finished
            $this->updateAll('job_status', JOB_FINISHED, new Criteria('job_id', $job->getVar('job_id')), true);
            // Get the next job
            return $this->getNextJob();
        }
        // there are still items in the queue for this job
        return $job;
    }
}
?>