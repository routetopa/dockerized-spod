<?php

class COCREATION_CLASS_EventHandler extends OW_ActionController
{
    private static $FIBONACCI_FIRST_20_NUMBERS = array(/*0,1,1,2,3,5,8,*/13,21,34,55,89,144,233,377,610,987,1597,2584,4181,6765);
    private static $classInstance;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function init()
    {
        OW::getEventManager()->bind('spodnotification.collect_actions', array($this, 'onCollectNotificationActions'));
        OW::getEventManager()->bind('spod_discussion.add_comment',      array($this, 'sendNotificationOnComment'));
    }

    public function onCollectNotificationActions( BASE_CLASS_EventCollector $e )
    {
        //Only for moderator and admin
        /*if(BOL_AuthorizationService::getInstance()->isModerator() || OW::getUser()->isAdmin()) {

            $e->add(array(
                'section' => COCREATION_CLASS_Consts::PLUGIN_NAME,
                'action' => COCREATION_CLASS_Consts::PLUGIN_ACTION_NEW_ROOM,
                'description' => OW::getLanguage()->text('cocreation', 'email_notifications_setting_room_created'),
                'selected' => false,
                'sectionLabel' => OW::getLanguage()->text('cocreation', 'main_menu_item'),
                'sectionIcon' => 'ow_ic_write',
                'sectionClass' => 'action'
            ));
        }
        //For all users
       $e->add(array(
            'section' => 'cocreation',
            'action'  => 'cocreation_add_comment',
            'description' => OW::getLanguage()->text('cocreation', 'email_notifications_setting_room_comment'),
            'selected' => false,
            'sectionLabel' => $sectionLabel,
            'sectionIcon' => 'ow_ic_write'
        ));*/

        /*$e->add(array(
            'section' => COCREATION_CLASS_Consts::PLUGIN_NAME,
            'action' => COCREATION_CLASS_Consts::PLUGIN_ACTION_NEW_ROOM,
            'description' => OW::getLanguage()->text('cocreation', 'email_notifications_setting_room_created'),
            'selected' => false,
            'sectionLabel' => OW::getLanguage()->text('cocreation', 'main_menu_item'),
            'sectionIcon' => 'ow_ic_write',
            'sectionClass' => 'action'
        ));*/

        $e->add(array(
            'section' => COCREATION_CLASS_Consts::PLUGIN_NAME,
            'action'  => COCREATION_CLASS_Consts::PLUGIN_ACTION_ROOM_INVITATION,
            'description' => OW::getLanguage()->text('cocreation', 'notification_room_invitation_label'),
            'selected' => false,
            'sectionLabel' => OW::getLanguage()->text('cocreation', 'main_menu_item'),
            'sectionIcon' => 'ow_ic_write',
            'sectionClass' => 'action'
        ));

        $e->add(array(
            'section' => COCREATION_CLASS_Consts::PLUGIN_NAME,
            'action'  => COCREATION_CLASS_Consts::PLUGIN_ACTION_JOIN,
            'description' => OW::getLanguage()->text('cocreation', 'notification_room_joined_label'),
            'selected' => false,
            'sectionLabel' => OW::getLanguage()->text('cocreation', 'main_menu_item'),
            'sectionIcon' => 'ow_ic_write',
            'sectionClass' => 'action'
        ));

        $e->add(array(
            'section' => COCREATION_CLASS_Consts::PLUGIN_NAME,
            'action'  => COCREATION_CLASS_Consts::PLUGIN_ACTION_DATASET_PUBLISHED,
            'description' => OW::getLanguage()->text('cocreation', 'notification_dataset_published_label'),
            'selected' => false,
            'sectionLabel' => OW::getLanguage()->text('cocreation', 'main_menu_item'),
            'sectionIcon' => 'ow_ic_write',
            'sectionClass' => 'action'
        ));

        $e->add(array(
            'section' => COCREATION_CLASS_Consts::PLUGIN_NAME,
            'action'  => COCREATION_CLASS_Consts::PLUGIN_ACTION_COMMENT,
            'description' => OW::getLanguage()->text('cocreation', 'notification_discussion_new_comment_label'),
            'selected' => false,
            'sectionLabel' => OW::getLanguage()->text('cocreation', 'main_menu_item'),
            'sectionIcon' => 'ow_ic_write',
            'sectionClass' => 'action'
        ));

        $sub_actions = SPODNOTIFICATION_BOL_Service::getInstance()->isUserRegisteredForSubAction(OW::getUser()->getId(),
            COCREATION_CLASS_Consts::PLUGIN_NAME,
            COCREATION_CLASS_Consts::PLUGIN_ACTION_COMMENT,
            SPODNOTIFICATION_CLASS_MailEventNotification::$TYPE);
        foreach ($sub_actions as $sub_action)
        {
            preg_match_all('!\d+!', $sub_action->action, $room_id);
            $room = COCREATION_BOL_Service::getInstance()->getRoomById($room_id[0][0]);

            $e->add(array(
                'section' => COCREATION_CLASS_Consts::PLUGIN_NAME,
                'action'  => $sub_action->action,
                'description' => OW::getLanguage()->text('cocreation', 'notification_discussion_new_comment_in_room_label', array("room_name" => $room->name)),
                'selected' => false,
                'sectionLabel' => COCREATION_CLASS_Consts::PLUGIN_NAME,
                'sectionIcon' => 'ow_ic_write',
                'sectionClass' => 'subAction',
                'parentAction' => $sub_action->parentAction
            ));
        }

    }

    //Custom on event notification
//    public function sendNotificationRoomCreated($userId, $room)
//    {
        //EMAIL
//        $message = OW::getLanguage()->text('cocreation','notification_room_created', ['ownername' => "<b><a>" . BOL_UserService::getInstance()->getDisplayName($room->ownerId) . "</a></b>"]) .
//            " <b><a href=\"" . str_replace("index/", $room->id, OW::getRouter()->urlFor( 'COCREATION_CTRL_DataRoom' , 'index')) . "\">". $room->name ."</a></b>";
//
//        $event = new OW_Event('notification_system.add_notification', array(
//            'notifications' => [
//                new SPODNOTIFICATION_CLASS_MailEventNotification(
//                    COCREATION_CLASS_Consts::PLUGIN_NAME,
//                    COCREATION_CLASS_Consts::PLUGIN_ACTION_NEW_ROOM,
//                    COCREATION_CLASS_Consts::PLUGIN_ACTION_NEW_ROOM,
//                    $userId,
//                    null,
//                    OW::getLanguage()->text('cocreation','email_notifications_setting_room_created'),
//                    $message,
//                    $message
//                ),
//                new SPODNOTIFICATION_CLASS_MobileEventNotification(
//                    COCREATION_CLASS_Consts::PLUGIN_NAME,
//                    COCREATION_CLASS_Consts::PLUGIN_ACTION_NEW_ROOM,
//                    COCREATION_CLASS_Consts::PLUGIN_ACTION_NEW_ROOM,
//                    $userId,
//                    null,
//                    'CoCreation',
//                    $message,
//                    []
//                )
//            ]
//        ));
//
//        OW::getEventManager()->trigger($event);
//    }

    public function sendNotificationRoomInvitation($userId, $room, $newMemberId)
    {
        $notification_on_invite = $this->getEmailNotificationOnInvite(
            BOL_UserService::getInstance()->getDisplayName($room->ownerId),
            $room->name,
             OW::getRouter()->urlFor('COCREATION_CTRL_Ajax', 'confirmToJoinToRoom') . "?roomId=" . $room->id . "&memberId=" . $newMemberId );

        $event = new OW_Event('notification_system.add_notification', array(
            'notifications' => [
                new SPODNOTIFICATION_CLASS_MailEventNotification(
                    COCREATION_CLASS_Consts::PLUGIN_NAME,
                    COCREATION_CLASS_Consts::PLUGIN_ACTION_ROOM_INVITATION,
                    COCREATION_CLASS_Consts::PLUGIN_ACTION_ROOM_INVITATION,
                    $userId,
                    $newMemberId,
                    OW::getLanguage()->text('cocreation', 'email_invited', array("user_name" => BOL_UserService::getInstance()->getDisplayName($room->ownerId), "room_name" => $room->name)),
                    $notification_on_invite['mail_html'],
                    $notification_on_invite['mail_text']
                ),
                new SPODNOTIFICATION_CLASS_MobileEventNotification(
                    COCREATION_CLASS_Consts::PLUGIN_NAME,
                    COCREATION_CLASS_Consts::PLUGIN_ACTION_ROOM_INVITATION,
                    COCREATION_CLASS_Consts::PLUGIN_ACTION_ROOM_INVITATION,
                    $userId,
                    $newMemberId,
                    'CoCreation',
                    $notification_on_invite['mail_html'],
                    []
                )
            ]
        ));

        OW::getEventManager()->trigger($event);
    }

    public function sendNotificationRoomJoin($userId, $room, $memberId)
    {
        $notification_on_join = $this->getEmailNotificationOnJoin(
            BOL_UserService::getInstance()->getDisplayName($memberId),
            $room->name,
            str_replace("index/", $room->id, OW::getRouter()->urlFor($room->type == "knowledge" ? 'COCREATION_CTRL_KnowledgeRoom' : 'COCREATION_CTRL_DataRoom', 'index')));

        $event = new OW_Event('notification_system.add_notification', array(
            'notifications' => [
                new SPODNOTIFICATION_CLASS_MailEventNotification(
                    COCREATION_CLASS_Consts::PLUGIN_NAME,
                    COCREATION_CLASS_Consts::PLUGIN_ACTION_JOIN,
                    COCREATION_CLASS_Consts::PLUGIN_ACTION_JOIN,
                    $userId,
                    $room->ownerId,
                    OW::getLanguage()->text('cocreation', 'email_joined', array("user_name" => BOL_UserService::getInstance()->getDisplayName($userId), "room_name" => $room->name)),
                    $notification_on_join['mail_html'],
                    $notification_on_join['mail_text']
                ),
                new SPODNOTIFICATION_CLASS_MobileEventNotification(
                    COCREATION_CLASS_Consts::PLUGIN_NAME,
                    COCREATION_CLASS_Consts::PLUGIN_ACTION_JOIN,
                    COCREATION_CLASS_Consts::PLUGIN_ACTION_JOIN,
                    $userId,
                    $room->ownerId,
                    'CoCreation',
                    $notification_on_join['mail_html'],
                    []
                )
            ]
        ));

        OW::getEventManager()->trigger($event);
    }

    public function sendNotificationDatasetPublished($userId ,$title)
    {
        $notification_on_publish = $this->getEmailNotificationOnPublish(
            BOL_UserService::getInstance()->getDisplayName($userId),
            $title
        );

        $event = new OW_Event('notification_system.add_notification', array(
            'notifications' => [
                new SPODNOTIFICATION_CLASS_MailEventNotification(
                    COCREATION_CLASS_Consts::PLUGIN_NAME,
                    COCREATION_CLASS_Consts::PLUGIN_ACTION_DATASET_PUBLISHED,
                    COCREATION_CLASS_Consts::PLUGIN_ACTION_DATASET_PUBLISHED,
                    $userId,
                    null,
                    OW::getLanguage()->text('cocreation', 'email_published', array("user_name" => BOL_UserService::getInstance()->getDisplayName($userId), "dataset_name" => $title)),
                    $notification_on_publish['mail_html'],
                    $notification_on_publish['mail_text']
                ),
                new SPODNOTIFICATION_CLASS_MobileEventNotification(
                    COCREATION_CLASS_Consts::PLUGIN_NAME,
                    COCREATION_CLASS_Consts::PLUGIN_ACTION_DATASET_PUBLISHED,
                    COCREATION_CLASS_Consts::PLUGIN_ACTION_DATASET_PUBLISHED,
                    $userId,
                    null,
                    'CoCreation',
                    $notification_on_publish['mail_html'],
                    []
                )
            ]
        ));

        OW::getEventManager()->trigger($event);
    }

    public function sendNotificationOnComment(OW_Event $event){
        $params  = $event->getParams();
        $comment = $params['comment'];

        $room = COCREATION_BOL_Service::getInstance()->getRoomById($comment->entityId);

        $notification_on_comment = $this->getEmailNotificationOnComment(
            BOL_UserService::getInstance()->getDisplayName($comment->ownerId),
            $comment->comment,
            $room->name,
            str_replace("index/", $room->id, OW::getRouter()->urlFor($room->type == "knowledge" ? 'COCREATION_CTRL_KnowledgeRoom' : 'COCREATION_CTRL_DataRoom', 'index'))
           );

        $event = new OW_Event('notification_system.add_notification', array(
            'notifications' => [
                new SPODNOTIFICATION_CLASS_MailEventNotification(
                    COCREATION_CLASS_Consts::PLUGIN_NAME,
                    COCREATION_CLASS_Consts::PLUGIN_ACTION_COMMENT . "_" . $comment->entityId,
                    COCREATION_CLASS_Consts::PLUGIN_ACTION_COMMENT,
                    $comment->ownerId,
                    null,
                    OW::getLanguage()->text('cocreation', 'email_new_comment', array("user_name" => BOL_UserService::getInstance()->getDisplayName($comment->ownerId), "room_name" => $room->name)),
                    $notification_on_comment['mail_html'],
                    $notification_on_comment['mail_text']
                ),
                new SPODNOTIFICATION_CLASS_MobileEventNotification(
                    COCREATION_CLASS_Consts::PLUGIN_NAME,
                    COCREATION_CLASS_Consts::PLUGIN_ACTION_COMMENT . "_" . $comment->entityId,
                    COCREATION_CLASS_Consts::PLUGIN_ACTION_COMMENT,
                    $comment->ownerId,
                    null,
                    'CoCreation',
                    $notification_on_comment['mail_html'],
                    ['room' => $room]
                )
            ]
        ));

        OW::getEventManager()->trigger($event);

    }

    //get template
    public function getEmailNotificationOnInvite($ownerName, $roomName, $joinUrl)
    {
        $template_html = OW::getPluginManager()->getPlugin('cocreation')->getCmpViewDir() . 'email_notification_invite_template_html.html';
        $template_txt  = OW::getPluginManager()->getPlugin('cocreation')->getCmpViewDir() . 'email_notification_invite_template_text.html';

        $mail_html = $this->getEmailContentHtmlOnInvite($template_html ,$ownerName, $roomName, $joinUrl);
        $mail_text = $this->getEmailContentTextOnInvite($template_txt, $ownerName, $roomName, $joinUrl);

        return ["mail_html" => $mail_html, "mail_text" => $mail_text];
    }

    public function getEmailNotificationOnJoin($ownerName, $roomName, $roomUrl)
    {
        $template_html = OW::getPluginManager()->getPlugin('cocreation')->getCmpViewDir() . 'email_notification_join_template_html.html';
        $template_txt  = OW::getPluginManager()->getPlugin('cocreation')->getCmpViewDir() . 'email_notification_join_template_text.html';

        $mail_html = $this->getEmailContentHtmlOnJoin($template_html ,$ownerName, $roomName, $roomUrl);
        $mail_text = $this->getEmailContentTextOnJoin($template_txt, $ownerName, $roomName, $roomUrl);

        return ["mail_html" => $mail_html, "mail_text" => $mail_text];
    }

    public function getEmailNotificationOnPublish($userName, $datasetName)
    {
        $template_html = OW::getPluginManager()->getPlugin('cocreation')->getCmpViewDir() . 'email_notification_publish_template_html.html';
        $template_txt  = OW::getPluginManager()->getPlugin('cocreation')->getCmpViewDir() . 'email_notification_publish_template_text.html';

        $mail_html = $this->getEmailContentHtmlOnPublish($template_html ,$userName, $datasetName);
        $mail_text = $this->getEmailContentTextOnPublish($template_txt , $userName, $datasetName);

        return ["mail_html" => $mail_html, "mail_text" => $mail_text];
    }

    public function getEmailNotificationOnComment($userName, $comment, $roomName, $roomUrl)
    {
        $template_html = OW::getPluginManager()->getPlugin('cocreation')->getCmpViewDir() . 'email_notification_comment_template_html.html';
        $template_txt  = OW::getPluginManager()->getPlugin('cocreation')->getCmpViewDir() . 'email_notification_comment_template_text.html';

        $mail_html = $this->getEmailContentHtmlOnComment($template_html ,$userName, $comment, $roomName, $roomUrl);
        $mail_text = $this->getEmailContentTextOnComment($template_txt , $userName, $comment, $roomName, $roomUrl);

        return ["mail_html" => $mail_html, "mail_text" => $mail_text];
    }

    //html & text
    private function getEmailContentHtmlOnInvite($template_html, $ownerName, $roomName, $joinUrl)
    {
        //SET EMAIL TEMPLATE
        $this->setTemplate($template_html);

        $this->assign('ownerName', $ownerName);
        $this->assign('roomName',  $roomName);
        $this->assign('joinUrl',   $joinUrl);

        $ownerName = "<b>" . $ownerName . "</b>";
        $roomName = "<b><a class='cocreation' href='" . $joinUrl . "'>" . $roomName . "</a></b>";

        $this->assign('notificationSubject', OW::getLanguage()->text('cocreation', 'email_invited', array("user_name" => $ownerName, "room_name" => $roomName)));

        return parent::render();
    }
    private function getEmailContentTextOnInvite($template_text, $ownerName, $roomName, $joinUrl)
    {
        //SET EMAIL TEMPLATE
        $this->setTemplate($template_text);

        $this->assign('ownerName', $ownerName);
        $this->assign('roomName',  $roomName);
        $this->assign('joinUrl',   $joinUrl);
        $this->assign('nl', '%%%nl%%%');
        $this->assign('tab', '%%%tab%%%');
        $this->assign('space', '%%%space%%%');

        $content = parent::render();
        $search = array('%%%nl%%%', '%%%tab%%%', '%%%space%%%');
        $replace = array("\n", '    ', ' ');

        return str_replace($search, $replace, $content);
    }

    private function getEmailContentHtmlOnJoin($template_html, $ownerName, $roomName, $roomUrl)
    {
        //SET EMAIL TEMPLATE
        $this->setTemplate($template_html);

        $this->assign('ownerName', $ownerName);
        $this->assign('roomName',  $roomName);
        $this->assign('roomUrl',   $roomUrl);

        $ownerName = "<b>" . $ownerName . "</b>";
        $roomName = "<b><a class='cocreation' href='" . $roomUrl . "'>" . $roomName . "</a></b>";

        $this->assign('notificationSubject', OW::getLanguage()->text('cocreation', 'email_joined', array("user_name" => $ownerName, "room_name" => $roomName)));

        return parent::render();
    }
    private function getEmailContentTextOnJoin($template_text, $ownerName, $roomName, $roomUrl)
    {
        //SET EMAIL TEMPLATE
        $this->setTemplate($template_text);

        $this->assign('ownerName', $ownerName);
        $this->assign('roomName',  $roomName);
        $this->assign('roomUrl',   $roomUrl);
        $this->assign('nl', '%%%nl%%%');
        $this->assign('tab', '%%%tab%%%');
        $this->assign('space', '%%%space%%%');

        $content = parent::render();
        $search = array('%%%nl%%%', '%%%tab%%%', '%%%space%%%');
        $replace = array("\n", '    ', ' ');

        return str_replace($search, $replace, $content);
    }

    private function getEmailContentHtmlOnPublish($template_html, $userName, $datasetName)
    {
        //SET EMAIL TEMPLATE
        $this->setTemplate($template_html);

        $this->assign('userName',    $userName);
        $this->assign('datasetName', $datasetName);

        $userName = "<b>" . $userName . "</b>";
        $datasetName = "<b><a class='cocreation' href='" . OW_URL_HOME . "cocreation/data-room-list" . "'>" . $datasetName . "</a></b>";

        $this->assign('notificationSubject', OW::getLanguage()->text('cocreation', 'email_published', array("user_name" => $userName, "dataset_name" => $datasetName)));

        return parent::render();
    }
    private function getEmailContentTextOnPublish($template_text, $userName, $datasetName)
    {
        //SET EMAIL TEMPLATE
        $this->setTemplate($template_text);

        $this->assign('userName',    $userName);
        $this->assign('datasetName', $datasetName);
        $this->assign('nl', '%%%nl%%%');
        $this->assign('tab', '%%%tab%%%');
        $this->assign('space', '%%%space%%%');

        $content = parent::render();
        $search = array('%%%nl%%%', '%%%tab%%%', '%%%space%%%');
        $replace = array("\n", '    ', ' ');

        return str_replace($search, $replace, $content);
    }

    private function getEmailContentHtmlOnComment($template_html, $userName, $comment, $roomName, $roomUrl)
    {
        //SET EMAIL TEMPLATE
        $this->setTemplate($template_html);

        $this->assign('userName', $userName);
        $this->assign('comment',  $comment);
        $this->assign('roomName', $roomName);
        $this->assign('roomUrl',  $roomUrl);

        $userName = "<b>" . $userName . "</b>";
        $roomName = "<b><a class='cocreation' href='" . $roomUrl . "'>" . $roomName . "</a></b>";

        $this->assign('notificationSubject', OW::getLanguage()->text('cocreation', 'email_new_comment', array("user_name" => $userName, "room_name" => $roomName)));

        return parent::render();
    }
    private function getEmailContentTextOnComment($template_text, $userName, $comment, $roomName, $roomUrl)
    {
        //SET EMAIL TEMPLATE
        $this->setTemplate($template_text);

        $this->assign('userName', $userName);
        $this->assign('comment',  $comment);
        $this->assign('roomName', $roomName);
        $this->assign('roomUrl',   $roomUrl);
        $this->assign('nl', '%%%nl%%%');
        $this->assign('tab', '%%%tab%%%');
        $this->assign('space', '%%%space%%%');

        $content = parent::render();
        $search = array('%%%nl%%%', '%%%tab%%%', '%%%space%%%');
        $replace = array("\n", '    ', ' ');

        return str_replace($search, $replace, $content);
    }
}