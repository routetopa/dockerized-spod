<?php

OW::getRouter()->addRoute(new OW_Route('notification-settings', '/spodnotification/settings', 'SPODNOTIFICATION_CTRL_Admin', 'settings'));
OW::getRouter()->addRoute(new OW_Route('spodnotification.test', '/spodnotification/test', "SPODNOTIFICATION_CTRL_Test", 'index'));
OW::getRouter()->addRoute(new OW_Route('spodnotification-user-settings', 'email-notification', 'SPODNOTIFICATION_CTRL_Notifications', 'settings'));

SPODNOTIFICATION_CLASS_EventHandler::getInstance()->init();

/*function spodnotification_preference_menu_item( BASE_CLASS_EventCollector $event )
{
    $router = OW_Router::getInstance();
    $language = OW::getLanguage();

    $menuItems = array();

    $menuItem = new BASE_MenuItem();

    $menuItem->setKey('email_spodnotification');
    $menuItem->setLabel($language->text( 'spodnotification', 'dashboard_menu_item'));
    $menuItem->setIconClass('ow_ic_mail');
    $menuItem->setUrl($router->urlForRoute('spodnotification-settings'));
    $menuItem->setOrder(3);

    $event->add($menuItem);
}

OW::getEventManager()->bind('base.preference_menu_items', 'spodnotification_preference_menu_item');*/

function spodnotification_add_console_item( BASE_CLASS_EventCollector $event )
{
    $event->add(array('label' => OW::getLanguage()->text('spodnotification', 'console_menu_label'), 'url' => OW_Router::getInstance()->urlForRoute('spodnotification-user-settings')));
}

OW::getEventManager()->bind('base.add_main_console_item', 'spodnotification_add_console_item');