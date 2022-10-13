<?php

class SPODNOTIFICATION_CTRL_Notifications extends OW_ActionController
{

    public function settings()
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        OW::getDocument()->getMasterPage()->setTemplate(OW::getPluginManager()->getPlugin('spodnotification')->getRootDir() . 'master_pages/main.html');
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('spodnotification')->getStaticCssUrl() . 'notification_settings.css');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodnotification')->getStaticJsUrl() . 'notification_system.js');

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodagora')->getStaticJsUrl() . 'perfect-scrollbar.jquery.js');
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('spodagora')->getStaticCssUrl() . 'perfect-scrollbar.min.css');

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodagora')->getStaticJsUrl() . 'socket_1_7_3.io.js');

        $actions = $this->collectActionList();

        $tplActions = array();

        foreach ( $actions as $action )
        {
            $result = SPODNOTIFICATION_BOL_Service::getInstance()->isUserRegisteredForAction(OW::getUser()->getId(), $action['section'], $action['action'], SPODNOTIFICATION_CLASS_MailEventNotification::$TYPE);

            $action['registered'] = ($result != null) ? true : false;
            $action['frequency']  = ($result != null) ? $result->frequency : 0;
            if ( empty($tplActions[$action['section']]) )
            {
                $tplActions[$action['section']] = array(
                    'label' => $action['sectionLabel'],
                    'icon' => empty($action['sectionIcon']) ? '' : $action['sectionIcon'],
                    'actions' => array()
                );
            }

            if($action['sectionClass'] == 'action')
                $tplActions[$action['section']]['actions'][$action['action']] = $action;
            else
                $tplActions[$action['section']]['actions'][$action['parentAction']]["subAction"][] = $action;
        }


        $agoras = SPODAGORA_BOL_Service::getInstance()->getAgora();
        $rooms = COCREATION_BOL_Service::getInstance()->getAllRooms();
        $visible_rooms = [];

        foreach ($rooms as $room)
        {
            if (COCREATION_BOL_Service::getInstance()->isMemberJoinedToRoom(OW::getUser()->getId(), $room->id) || OW::getUser()->getId() == intval($room->ownerId))
            {
                array_push($visible_rooms, $room);
            }
        }

        $this->assign('actions', $tplActions);
        $this->assign('agoras', $agoras);
        $this->assign('cocreations', $visible_rooms);
        $this->assign('spod_home', OW_URL_HOME);

        $js = UTIL_JsGenerator::composeJsString('
                NOTIFICATION_SETTINGS.userId                                     = {$userId}
                NOTIFICATION_SETTINGS.agoras                                     = {$agoras}
                NOTIFICATION_SETTINGS.ajax_notification_register_user_for_action = {$ajax_notification_register_user_for_action}
            ', array(
            'userId'                                     => OW::getUser()->getId(),
            'ajax_notification_register_user_for_action' => OW::getRouter()->urlFor('SPODNOTIFICATION_CTRL_Ajax', 'registerUserForAction'),
            'agoras'                                     => $agoras
        ));

        OW::getDocument()->addOnloadScript($js);
        OW::getDocument()->addOnloadScript('NOTIFICATION_SETTINGS.init();');
    }

    private function collectActionList()
    {
        if ( empty($this->defaultRuleList) )
        {
            $event = new BASE_CLASS_EventCollector('spodnotification.collect_actions');
            OW::getEventManager()->trigger($event);

            $eventData = $event->getData();
            foreach ( $eventData as $item )
            {
                $this->defaultRuleList[$item['action']] = $item;
            }

            $event = new BASE_CLASS_EventCollector('notifications.collect_actions');
            OW::getEventManager()->trigger($event);

            $eventData = $event->getData();
            foreach ( $eventData as $item )
            {
                $this->defaultRuleList[$item['action']] = $item;
                $this->defaultRuleList[$item['action']]['sectionClass'] = 'action';
            }
        }

        return $this->defaultRuleList;
    }
}