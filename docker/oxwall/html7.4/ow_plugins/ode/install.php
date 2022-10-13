<?php

$path = OW::getPluginManager()->getPlugin('ode')->getRootDir() . 'langs.zip';
BOL_LanguageService::getInstance()->importPrefixFromZip($path, 'ode');

$sql = 'CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'ode_datalet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ownerId` int(11) NOT NULL,
  `post` text,
  `component` text,
  `data` mediumtext,
  `fields` text,
  `params` text,
  `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` enum("approval","approved","blocked") NOT NULL DEFAULT "approved",
  `privacy` varchar(50) NOT NULL DEFAULT "everybody",
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `privacy` (`privacy`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'ode_datalet_post` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `postId` int(11) NOT NULL,
  `dataletId` int(11) NOT NULL,
  `plugin` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `postId` (`postId`),
  KEY `dataletId` (`dataletId`),
  KEY `plugin` (`plugin`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'ode_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` TEXT,
  `value` MEDIUMTEXT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'ode_provider` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL,
	`url` VARCHAR(255) NOT NULL,
	PRIMARY KEY (`id`)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `' . OW_DB_PREFIX . 'ode_provider` (`name`, `url`) VALUES (\'ROUTE-TO-PA\', \'http://ckan.routetopa.eu\');';

OW::getDbo()->query($sql);

OW::getPluginManager()->addPluginSettingsRouteName('ode', 'ode-settings');
