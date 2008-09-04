-- phpMyAdmin SQL Dump
-- version 2.8.2.4
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Oct 10, 2006 at 07:56 AM
-- Server version: 4.1.21
-- PHP Version: 4.4.2
-- 
-- Database: `sudhaker_mssi`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `mssi_job`
-- 

CREATE TABLE `mssi_job` (
  `job_id` int(11) NOT NULL auto_increment,
  `description` tinytext collate latin1_general_ci NOT NULL,
  `mail_from_addr` varchar(100) collate latin1_general_ci NOT NULL,
  `mail_from_name` varchar(100) collate latin1_general_ci NOT NULL,
  `mail_subject` tinytext collate latin1_general_ci NOT NULL,
  `mail_is_html` tinyint(4) NOT NULL default '0',
  `mail_body_html` longtext collate latin1_general_ci NOT NULL,
  `mail_body_txt` mediumtext collate latin1_general_ci,
  `vars` text collate latin1_general_ci NOT NULL,
  `run_after` int(11) NULL default NULL,
  `run_not_after` int(11) NULL default NULL,
  `job_status` tinyint(4) NOT NULL default '0',
  `job_started` int(11) NULL default NULL,
  `job_finished` int(11) NULL default NULL,
  PRIMARY KEY  (`job_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `mssi_job`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `mssi_job_attachment`
-- 

CREATE TABLE `mssi_job_attachment` (
  `job_id` int(11) NOT NULL default '0',
  `cid` varchar(40) collate latin1_general_ci NOT NULL default '',
  `mime_type` varchar(40) collate latin1_general_ci default NULL,
  `value` mediumtext collate latin1_general_ci,
  PRIMARY KEY  (`job_id`,`cid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- 
-- Dumping data for table `mssi_job_attachment`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `mssi_subscriber`
-- 

CREATE TABLE `mssi_subscriber` (
  `job_id` int(11) NOT NULL default '0',
  `uid` mediumint(9) NOT NULL default '0',
  `email` varchar(60) collate latin1_general_ci NOT NULL default '',
  `vars` text collate latin1_general_ci NOT NULL,
  `status` tinyint(4) NOT NULL default '0',
  `status_updated` int(11) NULL default NULL,
  PRIMARY KEY  (`job_id`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- 
-- Dumping data for table `mssi_subscriber`
-- 

