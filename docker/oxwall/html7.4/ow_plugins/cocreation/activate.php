<?php

OW::getPluginManager()->addPluginSettingsRouteName('cocreation', 'cocreation-settings');

OW::getNavigation()->addMenuItem(OW_Navigation::MAIN, 'cocreation.index', 'cocreation', 'main_menu_item', OW_Navigation::VISIBLE_FOR_ALL);
BOL_LanguageService::getInstance()->importPrefixFromZip(OW::getPluginManager()->getPlugin('cocreation')->getRootDir() . 'langs.zip', 'cocreation');

$authorization = OW::getAuthorization();
$groupName = 'cocreation';
$authorization->addAction($groupName, 'Publish on CKAN');