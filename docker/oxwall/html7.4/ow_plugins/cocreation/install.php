<?php

$path = OW::getPluginManager()->getPlugin('cocreation')->getRootDir() . 'langs.zip';
BOL_LanguageService::getInstance()->importPrefixFromZip($path, 'cocreation');

$sql = 'CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'cocreation_room` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255),
  `ownerId` int(11) NOT NULL,
  `name` varchar(255),
  `subject` varchar(255),
  `description` varchar(255),
  `metadata` TINYINT(1),
  `from` date,
  `to` date,
  `goal` varchar(255),
  `invitationText` varchar(255),
  `isOpen` tinyint(1),
  `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'cocreation_room_doc` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roomId` int(11) NOT NULL,
  `url` varchar(255),
  `description` varchar(255),
  `templateId` int(11),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'cocreation_room_dataset` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roomId` int(11) NOT NULL,
  `url` varchar(255),
  `name` varchar(255),
  `description` varchar(255),
  `fields` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'cocreation_room_member` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roomId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `email` varchar(255),
  `isJoined` tinyint(1),
  `role` int(11),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'cocreation_room_datalet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roomId` int(11) NOT NULL,
  `dataletId` int(11),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'cocreation_room_postit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roomId` int(11) NOT NULL,
  `dataletId` int(11),
  `title` varchar(255),
  `content` varchar(255),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'cocreation_room_sheet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roomId` int(11) NOT NULL,
  `url` varchar(255),
  `description` varchar(255),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'cocreation_room_metadata` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roomId` int(11) NOT NULL,
  `metadata` mediumtext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'cocreation_dataset` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owners` text,
  `roomId` int(11) NOT NULL,
  `datasetId` varchar(255) NOT NULL,
  `version` int(11) NOT NULL,
  `data` mediumtext,
  `notes` mediumtext,
  `metadata` mediumtext,
  `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;


CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'cocreation_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255),
  `url` varchar(255),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'cocreation_room_form` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roomId` int(11) NOT NULL,
  `formTemplate` mediumtext,
  `form` mediumtext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'cocreation_room_form_submission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roomId` int(11) NOT NULL,
  `userId` int(11),
  `submission` mediumtext,
  `ip` varchar(16),
  `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE DATABASE IF NOT EXISTS etherpadLite CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;
CREATE TABLE IF NOT EXISTS etherpadLite.store (`key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL, `value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin, PRIMARY KEY (`key`)) ENGINE=InnoDB;
ALTER TABLE etherpadLite.store ENGINE=InnoDB;

CREATE DATABASE IF NOT EXISTS ethersheet;
CREATE TABLE IF NOT EXISTS ethersheet.sheets (sheetid VARCHAR(40), sheetdata MEDIUMTEXT, PRIMARY KEY (sheetid));
CREATE TABLE IF NOT EXISTS ethersheet.users (user_id VARCHAR(255), color VARCHAR(7), nickname VARCHAR(255), PRIMARY KEY (user_id));

';



OW::getDbo()->query($sql);

$authorization = OW::getAuthorization();
$groupName = 'cocreation';
$authorization->addGroup($groupName);
$authorization->addAction($groupName, 'view', true);
$authorization->addAction($groupName, 'add_comment');
$authorization->addAction($groupName, 'Publish on CKAN');

OW::getNavigation()->deleteMenuItem('pluginname', 'bottom_menu_item');