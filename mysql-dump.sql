
CREATE TABLE `blogs` (
  `id` int(11) NOT NULL auto_increment,
  `url` varchar(250) default NULL,
  `rss` varchar(250) default NULL,
  `title` varchar(250) default NULL,
  `description` text,
  `allow` int(11) default '0',
  PRIMARY KEY  (`id`)
);
CREATE TABLE `posts` (
  `id` int(11) NOT NULL auto_increment,
  `blog` int(11) default NULL,
  `title` varchar(250) default NULL,
  `link` varchar(250) default NULL,
  `date` int(11) default NULL,
  `description` longtext,
  `visits` int(11) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `f_url` (`link`),
  KEY `date` (`date`),
  KEY `blog` (`blog`)
) ENGINE=MyISAM;
