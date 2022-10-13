<?php

$path = OW::getPluginManager()->getPlugin('spodnotification')->getRootDir() . 'langs.zip';
BOL_LanguageService::getInstance()->importPrefixFromZip($path, 'spodnotification');

OW::getPluginManager()->addPluginSettingsRouteName('spodnotification', 'notification-settings');

$sql = 'CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'spod_notification_notification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `notification` text,
  `timestamp` int(11),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'spod_notification_registered_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `type` text,
  `plugin` text,
  `action` text,
  `parentAction` text,
  `frequency` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'spod_notification_user_registration_id` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `registrationId` text,
  `timestamp` int(11),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;
';

OW::getDbo()->query($sql);