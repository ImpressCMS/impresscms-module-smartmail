CREATE TABLE `smartmail_newsletter` (
  `newsletter_id` int unsigned NOT NULL auto_increment,
  `newsletter_name` varchar(255) NOT NULL,
  `newsletter_description` text NOT NULL,
  `newsletter_template` varchar(255) NOT NULL default "",
  `newsletter_from_name` varchar(255) NOT NULL default "",
  `newsletter_from_email` varchar(255) NOT NULL default "",
  `newsletter_email` varchar(255) NOT NULL default "",
  `newsletter_confirm_text` text,
  `newsletter_type` int(11) NOT NULL default 1,
  PRIMARY KEY (`newsletter_id`)
) ;

CREATE TABLE `smartmail_rule` (
  `rule_id` int unsigned NOT NULL auto_increment,
  `newsletterid` int unsigned NOT NULL default '0',
  `rule_weekday` tinyint unsigned NOT NULL default 0,
  `rule_timeofday` varchar(15) NOT NULL default '',
  PRIMARY KEY (`rule_id`),
  KEY `newsletterid` (`newsletterid`)
) ;

CREATE TABLE `smartmail_dispatch` (
  `dispatch_id` int unsigned NOT NULL auto_increment,
  `newsletterid` int unsigned NOT NULL,
  `dispatch_time` int unsigned NOT NULL,
  `dispatch_subject` varchar(255) NOT NULL default "",
  `dispatch_status` tinyint NOT NULL default 0,
  `dispatch_content` text,
  `dispatch_receivers` int NOT NULL default 0,
  PRIMARY KEY (`dispatch_id`)
) ;

CREATE TABLE `smartmail_block` (
  `nb_id` int(12) unsigned NOT NULL auto_increment,
  `b_id` int(11) unsigned NOT NULL,
  `newsletterid` int(11) unsigned NOT NULL,
  `dispatchid` int(11) unsigned NOT NULL default 0,
  `nb_title` varchar(255) NOT NULL default '',
  `nb_position` tinyint unsigned NOT NULL default 1,
  `nb_weight` mediumint unsigned NOT NULL default 0,
  `nb_options` text NOT NULL default '',
  `nb_override` int(12) NOT NULL default 0,
  PRIMARY KEY (`nb_id`),
  KEY `bynewsletter` (`newsletterid`, `nb_weight`)
) ;

CREATE TABLE `smartmail_subscriber` (
  `subscriber_id` int unsigned NOT NULL auto_increment,
  `uid` int NOT NULL default 0,
  `newsletterid` int unsigned NOT NULL,
  PRIMARY KEY (`subscriber_id`),
  KEY `newsletterid` (`newsletterid`),
  UNIQUE KEY `user_list` (`uid`,`newsletterid`)
) ;

CREATE TABLE `smartmail_job` (
  `job_id` int(11) NOT NULL auto_increment,
  `job_description` tinytext,
  `job_from_addr` varchar(100) NOT NULL DEFAULT '',
  `job_from_name` varchar(100) NOT NULL DEFAULT '',
  `job_subject` tinytext,
  `job_is_html` tinyint(4) NOT NULL default '0',
  `job_body_html` longtext,
  `job_body_txt` mediumtext,
  `job_vars` text,
  `job_run_after` int(11) NULL default NULL,
  `job_run_not_after` int(11) NULL default NULL,
  `job_status` tinyint(4) NOT NULL default '0',
  `job_started` int(11) NULL default NULL,
  `job_finished` int(11) NULL default NULL,
  PRIMARY KEY  (`job_id`)
);

CREATE TABLE `smartmail_jobattachment` (
  `job_id` int(11) NOT NULL default '0',
  `cid` varchar(40) NOT NULL default '',
  `mime_type` varchar(40) default NULL,
  `value` mediumtext,
  PRIMARY KEY  (`job_id`,`cid`)
) TYPE=MyISAM;

CREATE TABLE `smartmail_recipient` (
  `job_id` int(11) NOT NULL default '0',
  `uid` mediumint(9) NOT NULL default '0',
  `email` varchar(60) NOT NULL default '',
  `vars` text,
  `status` tinyint(4) NOT NULL default '0',
  `status_updated` int(11) NULL default NULL,
  PRIMARY KEY  (`job_id`,`uid`)
) TYPE=MyISAM;
