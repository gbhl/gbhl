-- phpMyAdmin SQL Dump
-- version 3.3.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 19. Mai 2010 um 10:51
-- Server Version: 5.0.67
-- PHP-Version: 5.2.12


--
-- Datenbank: `bhlscanlist`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `import_bibs`
--

CREATE TABLE IF NOT EXISTS `import_bibs` (
  `id` int(30) NOT NULL auto_increment,
  `001` text,
  `002` text,
  `008` text,
  `022` text,
  `author` text,
  `abbrev_title` varchar(1000) default NULL,
  `title` varchar(1000) default NULL,
  `pub` varchar(1000) default NULL,
  `match_basis` varchar(1000) default NULL,
  `depreciated` tinyint(1) default NULL,
  `subjects` varchar(1000) default NULL,
  `places` varchar(1000) default NULL,
  `new_id` int(30) default NULL,
  `new_title` tinyint(1) default NULL,
  `found_match` int(20) default NULL,
  `checked` int(20) default NULL,
  `ldr` varchar(200) default NULL,
  `001_b` varchar(200) default NULL,
  `002_b` varchar(100) default NULL,
  `008_b` varchar(200) default NULL,
  `035` varchar(200) default NULL,
  `210` varchar(200) default NULL,
  `245` varchar(800) default NULL,
  `260` varchar(500) default NULL,
  `site_b` varchar(20) default NULL,
  `flag_b` varchar(5) default NULL,
  `newtitle_b` varchar(800) default NULL,
  `t245stripped` varchar(800) default NULL,
  PRIMARY KEY  (`id`),
  KEY `id` (`id`),
  KEY `title` (`title`(333)),
  KEY `pub` (`pub`(333)),
  KEY `abbrev_title` (`abbrev_title`(333)),
  KEY `t245stripped` (`t245stripped`(333))
) TYPE=MyISAM  AUTO_INCREMENT=94595 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `import_holdings`
--

CREATE TABLE IF NOT EXISTS `import_holdings` (
  `id` int(11) NOT NULL auto_increment,
  `bib_id` int(11) NOT NULL,
  `sourceid` int(20) NOT NULL,
  `035` text,
  `hol_1` text,
  `hol_2` text,
  `hol_3` text,
  `hol_4` text,
  `subject` text,
  `e_856` text,
  `place` text,
  `match_basis` text,
  `oclc` text,
  `user_id` int(10) default NULL,
  `orig_bib_id` int(10) default NULL,
  PRIMARY KEY  (`id`),
  KEY `bib_id` (`bib_id`),
  KEY `035` (`035`(200))
) TYPE=MyISAM  COMMENT='holdings' AUTO_INCREMENT=101 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `import_index`
--

CREATE TABLE IF NOT EXISTS `import_index` (
  `IDImportIndex` int(11) NOT NULL auto_increment,
  `bib_id` int(11) NOT NULL,
  `title` text NOT NULL,
  `author` text NOT NULL,
  `publisher` text NOT NULL,
  `oclc` int(11) NOT NULL,
  PRIMARY KEY  (`IDImportIndex`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;
