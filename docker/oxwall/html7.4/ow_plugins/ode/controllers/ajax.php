<?php

require_once OW::getPluginManager()->getPlugin('spodnotification')->getRootDir()
    . 'lib/vendor/autoload.php';

use ElephantIO\Client;
use ElephantIO\Engine\SocketIO\Version1X;


class ODE_CTRL_Ajax extends NEWSFEED_CTRL_Ajax
{

    public function privateRoomDatalet()
    {
        $clean = ODE_CLASS_InputFilter::getInstance()->sanitizeInputs($_REQUEST);
        if ($clean == null){
            /*echo json_encode(array("status" => "error", "massage" => 'Insane inputs detected'));*/
            OW::getFeedback()->info(OW::getLanguage()->text('cocreationep', 'insane_user_email_value'));
            exit;
        }

        /* ODE */
        $results = '';

        //if( ODE_CLASS_Helper::validateDatalet($clean['component'], $clean['params'], $clean['fields']) )
        {
            $results = SPODPR_BOL_Service::getInstance()->dataletCard(OW::getUser()->getId(),
                                                              $clean['component'],
//                                                              isset($clean['fields']) ? $clean['fields'] : '',
                                                              '',
                                                              $clean['params'],
                                                              $clean['data'],
                                                              //isset($_REQUEST['comment']) ? $_REQUEST['comment'] : '',
                                                              isset($clean['dataletId']) ? $clean['dataletId'] : '',
                                                              isset($clean['cardId']) ? $clean['cardId'] : '');
        }
        /* ODE */

        echo json_encode(array("status" => "ok", "cardId" => $results["card-id"], "dataletId" => $results["datalet-id"]));
        exit;
    }

    /*public function modPrivateRoomDatalet()
    {
        SPODPR_BOL_Service::getInstance()->modPrivateRoomDatalet(OW::getUser()->getId(),
            $_REQUEST['id'],
            $_REQUEST['component'],
            $_REQUEST['fields'],
            $_REQUEST['params'],
            $_REQUEST['data']);

        echo json_encode(array("status" => "ok"));
        exit;
    }*/

    public function statusUpdate()
    {
        $clean = ODE_CLASS_InputFilter::getInstance()->sanitizeInputs($_REQUEST);
        if ($clean == null){
            /*echo json_encode(array("status" => "error", "massage" => 'Insane inputs detected'));*/
            OW::getFeedback()->info(OW::getLanguage()->text('cocreationep', 'insane_user_email_value'));
            exit;
        }

        if ( empty($clean['status']) && empty($clean['attachment']) )
        {
            echo json_encode(array(
                "error" => OW::getLanguage()->text('base', 'form_validate_common_error_message')
            ));
            exit;
        }

        if ( !OW::getUser()->isAuthenticated() )
        {
            echo json_encode(false);
            exit;
        }

        $oembed = null;
        $attachId = null;
        $status = empty($clean['status']) ? '' : strip_tags($clean['status']);
        $content = array();

        if ( !empty($clean['attachment']) )
        {
            $content = json_decode($clean['attachment'], true);

            if ( !empty($content) )
            {
                if( $content['type'] == 'photo' && !empty($content['uid']) )
                {
                    $attachmentData = OW::getEventManager()->call('base.attachment_save_image', array(
                        "pluginKey" => "newsfeed",
                        'uid' => $content['uid']
                    ));

                    $content['url'] = $content['href'] = $attachmentData["url"];
                    $attachId = $content['uid'];
                }

                if( $content['type'] == 'video' )
                {
                    $content['html'] = BOL_TextFormatService::getInstance()->validateVideoCode($content['html']);
                }
            }
        }

        $userId = OW::getUser()->getId();

        $event = new OW_Event("feed.before_content_add", array(
            "feedType" => $clean['feedType'],
            "feedId" => $clean['feedId'],
            "visibility" => $clean['visibility'],
            "userId" => $userId,
            "status" => $status,
            "type" => empty($content["type"]) ? "text" : $content["type"],
            "data" => $content
        ));

        OW::getEventManager()->trigger($event);

        $data = $event->getData();

        if ( !empty($data) )
        {
            if ( !empty($attachId) )
            {
                BOL_AttachmentService::getInstance()->deleteAttachmentByBundle("newsfeed", $attachId);
            }

            $item = empty($data["entityType"]) || empty($data["entityId"])
                ? null
                : array(
                    "entityType" => $data["entityType"],
                    "entityId" => $data["entityId"]
                );

            echo json_encode(array(
                "item" => $item,
                "message" => empty($data["message"]) ? null : $data["message"],
                "error" => empty($data["error"]) ? null : $data["error"]
            ));
            exit;
        }

        $status = UTIL_HtmlTag::autoLink($status);
        $out = NEWSFEED_BOL_Service::getInstance()
            ->addStatus(OW::getUser()->getId(), $clean['feedType'], $clean['feedId'], $clean['visibility'], $status, array(
                "content" => $content,
                "attachmentId" => $attachId
            ));

        /* ODE */
        if( ODE_CLASS_Helper::validateDatalet($clean['ode_datalet'], $clean['ode_params'], $clean['ode_fields']) )
        {
            ODE_BOL_Service::getInstance()->addDatalet($clean['ode_datalet'], $clean['ode_fields'],
                OW::getUser()->getId(), $clean['ode_params'], $out['entityId'], 'newsfeed', $clean['ode_data']);
        }
        /* ODE */

        echo json_encode(array(
            "item" => $out
        ));
        exit;
    }

    private function getEmailContentHtml($roomId, $userId)
    {
        $date = getdate();
        $time = mktime(0, 0, 0, $date['mon'], $date['mday'], $date['year']);

        //SET EMAIL TEMPLETE
        $template = OW::getPluginManager()->getPlugin('spodpublic')->getCmpViewDir() . 'email_notification_template_html.html';
        $this->setTemplate($template);

        //USER AVATAR FOR THE NEW MAIL
        $avatar = BOL_AvatarService::getInstance()->getDataForUserAvatars(array(OW::getUser()->getId()))[OW::getUser()->getId()];
        $this->assign('userName', BOL_UserService::getInstance()->getDisplayName($userId));
        $this->assign('string', OW::getLanguage()->text('spodpublic', 'email_txt_comment') . " <b><a href=\"" .
            OW::getRouter()->urlForRoute('spodpublic.main')  . "/#!/" . $roomId . "\">" .
           SPODPUBLIC_BOL_Service::getInstance()->getPublicRoomById($roomId)->subject . "</a></b>");
        $this->assign('avatar', $avatar);
        $this->assign('time', $time);

        return parent::render();

    }

    private function getEmailContentText($roomId){
        $date = getdate();
        $time = mktime(0, 0, 0, $date['mon'], $date['mday'], $date['year']);

        $template = OW::getPluginManager()->getPlugin('spodpublic')->getCmpViewDir() . 'email_notification_template_text.html';
        $this->setTemplate($template);

        $this->assign('nl', '%%%nl%%%');
        $this->assign('tab', '%%%tab%%%');
        $this->assign('space', '%%%space%%%');
        $this->assign('string', " has commented on a discussion in the room <b>" . SPODPUBLIC_BOL_Service::getInstance()->getPublicRoomById($roomId)->name . "</b>");

        $content = parent::render();
        $search = array('%%%nl%%%', '%%%tab%%%', '%%%space%%%');
        $replace = array("\n", '    ', ' ');

        return str_replace($search, $replace, $content);
    }

    private function sendEmailNotificationProcess( $roomId )
    {

        //GET ALL SUBSCRIBED USERS
        $users = SPODPUBLIC_BOL_Service::getInstance()->getSubscribedNotificationUsersForRoom($roomId);

        foreach($users as $user){

            $userId = $user->userId;
            $userService = BOL_UserService::getInstance();
            $user = $userService->findUserById($userId);

            if ( empty($user) )
            {
                return false;
            }

            $email = $user->email;
            try
            {
                $mail = OW::getMailer()->createMail()
                    ->addRecipientEmail($email)
                    ->setTextContent($this->getEmailContentText($roomId))
                    ->setHtmlContent($this->getEmailContentHtml($roomId, $userId))
                    ->setSubject("Something interesting is happening on Agora");

                OW::getMailer()->send($mail);
            }
            catch ( Exception $e )
            {
                //Skip invalid notification
            }

        }
    }

    public function addComment()
    {
        //$clean = ODE_CLASS_InputFilter::getInstance()->sanitizeInputs($_REQUEST);
        $clean = $_REQUEST;
        if ($clean == null){
            /*echo json_encode(array("status" => "error", "massage" => 'Insane inputs detected'));*/
            OW::getFeedback()->info(OW::getLanguage()->text('cocreationep', 'insane_user_email_value'));
            exit;
        }

        $errorMessage = false;
        $isMobile = !empty($clean['isMobile']) && (bool) $clean['isMobile'];
        $params = $this->getParamsObject();

        if ( empty($clean['commentText']) && empty($clean['attachmentInfo']) && empty($clean['oembedInfo']) )
        {
            $errorMessage = OW::getLanguage()->text('base', 'comment_required_validator_message');
        }
        else if ( !OW::getUser()->isAuthorized($params->getPluginKey(), 'add_comment') )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus($params->getPluginKey(), 'add_comment');
            $errorMessage = $status['msg'];
        }
        else if ( BOL_UserService::getInstance()->isBlocked(OW::getUser()->getId(), $params->getOwnerId()) )
        {
            $errorMessage = OW::getLanguage()->text('base', 'user_block_message');
        }

        if ( $errorMessage )
        {
            exit(json_encode(array('error' => $errorMessage)));
        }

        $commentText = empty($clean['commentText']) ? '' : trim($clean['commentText']);
        $attachment = null;

        if ( BOL_TextFormatService::getInstance()->isCommentsRichMediaAllowed() && !$isMobile )
        {
            if ( !empty($clean['attachmentInfo']) )
            {
                $tempArr = json_decode($clean['attachmentInfo'], true);
                OW::getEventManager()->call('base.attachment_save_image', array('uid' => $tempArr['uid'], 'pluginKey' => $tempArr['pluginKey']));
                $tempArr['href'] = $tempArr['url'];
                //$tempArr['type'] = 'photo';
                $attachment = json_encode($tempArr);
            }
            else if ( !empty($clean['oembedInfo']) )
            {
                $tempArr = json_decode($clean['oembedInfo'], true);
                // add some actions
                $attachment = json_encode($tempArr);
            }
        }

        $comment = BOL_CommentService::getInstance()->addComment($params->getEntityType(), $params->getEntityId(), $params->getPluginKey(), OW::getUser()->getId(), $commentText, $attachment);

        /* ODE */
        if( ODE_CLASS_Helper::validateDatalet($clean['datalet']['component'], $clean['datalet']['params'], $clean['datalet']['fields']) )
        {
            ODE_BOL_Service::getInstance()->addDatalet(
                $clean['datalet']['component'],
                $clean['datalet']['fields'],
                OW::getUser()->getId(),
                $clean['datalet']['params'],
                $comment->getId(),
                $clean['plugin'],
                $clean['datalet']['data']);

            /*if(OW::getPluginManager()->isPluginActive('spodpublic') && $clean['plugin'] == "public-room")
            {
                if( $delta = SPODPUBLIC_BOL_Service::getInstance()->addStat($clean['publicRoom'], 'opendata') )
                {
                    //Add post on What's New when users add more than $delta datalets
                    $event = new OW_Event('feed.action', array(
                        'pluginKey' => 'spodpublic',
                        'entityType' => 'spodpublic_public-room-comment',
                        'entityId' => $comment->id,
                        'userId' => OW::getUser()->getId(),
                    ), array(
                        'time' => time(),
                        'roomId' => $clean['publicRoom'],
                        'commentId' => $comment->id,
                        'string' => array('key' => 'spodpublic+post_comment_datalet', 'vars' => array('roomId' => $clean['publicRoom'],
                            'roomSubject' => SPODPUBLIC_BOL_Service::getInstance()->getPublicRoomById($clean['publicRoom'])->subject, 'post' => $delta))
                    ));
                    OW::getEventManager()->trigger($event);
                    //End add post on What's New
                }
            }*/
        }
        /* ODE */

        /*SEND EMAIL TO SUBSCRIBED USERS*/

        //$this->sendEmailNotificationProcess($clean['publicRoom']);

        /*END SEND EMAIL TO SUBSCRIBED USERS*/

        /* */
        // trigger event comment add
        $event = new OW_Event('base_add_comment', array(
            'entityType' => $params->getEntityType(),
            'entityId' => $params->getEntityId(),
            'userId' => OW::getUser()->getId(),
            'commentId' => $comment->getId(),
            'pluginKey' => $params->getPluginKey(),
            'attachment' => json_decode($attachment, true)
        ));

        OW::getEventManager()->trigger($event);

        BOL_AuthorizationService::getInstance()->trackAction($params->getPluginKey(), 'add_comment');

        if ( $isMobile )
        {
            $commentListCmp = new BASE_MCMP_CommentsList($params, $clean['cid']);
        }
        else
        {
            //if($params->getPluginKey() == "spodpublic")
            //{
             //   $commentListCmp = new SPODPUBLIC_CMP_CommentsList($params, $clean['cid']);
            //}else{
                $commentListCmp = new BASE_CMP_CommentsList($params, $clean['cid']);
            //}
        }
        /* */

        /*if(OW::getPluginManager()->isPluginActive('spodpublic') && !empty($clean['publicRoom']) && $params->getPluginKey() == 'spodpublic')
        {
            // Send realtime notification
            try
            {
                $last_comment = end($commentListCmp->components);

                $client = new Client(new Version1X('http://localhost:3000'));
                $client->initialize();
                $client->emit('realtime_notification', ['plugin' => $params->getPluginKey(),
                              'room_id' => $clean['publicRoom'],
                              'message' => $comment->message,
                              'message_id' => $comment->id,
                              'parent_id' => $params->getEntityId(),
                              'user_id' => OW::getUser()->getId(),
                              'user_display_name' => $last_comment->assignedVars["currentUserInfo"]["title"],
                              'user_avatar' => $last_comment->assignedVars["currentUserInfo"]["src"],
                              'user_url' => $last_comment->assignedVars["currentUserInfo"]["url"],
                              'comment_level' => $last_comment->params->level,
                              'sentiment' => $clean['sentiment'],
                              'component' => $clean['datalet']['component'],
                              'params' => $clean['datalet']['params'],
                              'fields' => $clean['datalet']['fields'],

                              'contextId'  => $last_comment->cmpContextId,
                              'uid'        => $last_comment->id,
                              'textAreaId' => $last_comment->assignedVars["taId"],
                              'attchId'    => $last_comment->assignedVars["attchId"],
                              'attchUid'   => $last_comment->components["attch"]->assignedVars["previewId"]]);
                $client->close();
            }
            catch(Exception $e)
            {}
            // Send realtime notification

            // Add sentiment to a comment
            SPODPUBLIC_BOL_Service::getInstance()->addCommentSentiment($clean['publicRoom'],$comment->getId(),$clean['sentiment']);

            // Update Stat for comment
            if( empty($clean['datalet']['component']) && ($delta = SPODPUBLIC_BOL_Service::getInstance()->addStat($clean['publicRoom'], 'comments')) )
            {
                //Add post on What's New when users add more than $delta comments
                $event = new OW_Event('feed.action', array(
                    'pluginKey' => 'spodpublic',
                    'entityType' => 'spodpublic_public-room-comment',
                    'entityId' => $comment->id,
                    'userId' => OW::getUser()->getId(),
                ), array(
                    'time' => time(),
                    'roomId' => $clean['publicRoom'],
                    'commentId' => $comment->id,
                    'string' => array('key' => 'spodpublic+post_comment', 'vars' => array('roomId' => $clean['publicRoom'],
                            'roomSubject' => SPODPUBLIC_BOL_Service::getInstance()->getPublicRoomById($clean['publicRoom'])->subject,
                            'comment' => $comment->message, 'post' => $delta))
                ));
                OW::getEventManager()->trigger($event);
                //End add post on What's New
            }
        }*/

        exit(json_encode(array(
                'newAttachUid' => BOL_CommentService::getInstance()->generateAttachmentUid($params->getEntityType(), $params->getEntityId()),
                'entityType' => $params->getEntityType(),
                'entityId' => $params->getEntityId(),
                'commentList' => $commentListCmp->render(),
                'onloadScript' => OW::getDocument()->getOnloadScript(),
                'commentCount' => BOL_CommentService::getInstance()->findCommentCount($params->getEntityType(), $params->getEntityId())
            )
          )
        );
    }

    private function getParamsObject()
    {
        $clean = ODE_CLASS_InputFilter::getInstance()->sanitizeInputs($_REQUEST);
        if ($clean == null){
            /*echo json_encode(array("status" => "error", "massage" => 'Insane inputs detected'));*/
            OW::getFeedback()->info(OW::getLanguage()->text('cocreationep', 'insane_user_email_value'));
            exit;
        }

        $errorMessage = false;

        $entityType = !isset($clean['entityType']) ? null : trim($clean['entityType']);
        $entityId = !isset($clean['entityId']) ? null : (int) $clean['entityId'];
        $pluginKey = !isset($clean['pluginKey']) ? null : trim($clean['pluginKey']);

        if ( !$entityType || !$entityId || !$pluginKey )
        {
            $errorMessage = OW::getLanguage()->text('base', 'comment_ajax_error');
        }

        $params = new BASE_CommentsParams($pluginKey, $entityType);
        $params->setEntityId($entityId);

        if ( isset($clean['ownerId']) )
        {
            $params->setOwnerId((int) $clean['ownerId']);
        }

        if ( isset($clean['commentCountOnPage']) )
        {
            $params->setCommentCountOnPage((int) $clean['commentCountOnPage']);
        }

        if ( isset($clean['displayType']) )
        {
            $params->setDisplayType($clean['displayType']);
        }

        if ( isset($clean['initialCount']) )
        {
            $params->setInitialCommentsCount((int) $clean['initialCount']);
        }

        if ( isset($clean['loadMoreCount']) )
        {
            $params->setLoadMoreCount((int) $clean['loadMoreCount']);
        }

        if ( $errorMessage )
        {
            echo json_encode(array(
                'error' => $errorMessage
            ));

            exit();
        }

        return $params;
    }

    public function getDataletInfo()
    {
        $clean = ODE_CLASS_InputFilter::getInstance()->sanitizeInputs($_REQUEST);
        if ($clean == null){
            echo json_encode(array("status" => "error", "massage" => 'Insane inputs detected'));
            exit;
        }

        $id_post    = $clean["post_id"];
        $id_datalet = $clean["datalet_id"];

        $datalet_info = ODE_BOL_Service::getInstance()->getDataletInfo($id_post, $id_datalet);
        $user         = BOL_UserService::getInstance()->getDisplayName($datalet_info["ownerId"]);

        if(!empty($id_post))
        {
            $post_info = ODE_BOL_Service::getInstance()->getPostInfo($id_post, $clean["is_public_room"]);

            if($clean["is_public_room"] == "true")
            {
                // Public room
                echo json_encode(array("timestamp" => $datalet_info["timestamp"], "user" => $user, "comment" => $post_info["message"]));
            }else{
                // Newsfeed
                $post_info = json_decode($post_info["data"]);
                echo json_encode(array("timestamp" => $datalet_info["timestamp"], "user" => $user, "comment" => $post_info->status));
            }
        }else {
            // My Space
            echo json_encode(array("timestamp" => $datalet_info["timestamp"], "user" => $user));
        }

        exit;
    }
}