<?php

OW::getNavigation()->deleteMenuItem('cocreation', 'main');
OW::getNavigation()->deleteMenuItem('cocreation', 'main_menu_item');
BOL_LanguageService::getInstance()->deletePrefix(BOL_LanguageService::getInstance()->findPrefixId('cocreation'));
