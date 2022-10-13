<?php

class COCREATION_CTRL_Main extends OW_ActionController
{
    public function index()
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        OW::getDocument()->getMasterPage()->setTemplate(OW::getPluginManager()->getPlugin('cocreation')->getRootDir() . 'master_pages/general_main.html');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('cocreation')->getStaticJsUrl() . 'cocreation.js', 'text/javascript');

        $this->assign('components_url', SPODPR_COMPONENTS_URL);

        $invitations      = array();
        $visible_rooms    = array();

        $rooms = COCREATION_BOL_Service::getInstance()->getAllRoomOrderedByDate();
        foreach ($rooms as $room) {
            if (COCREATION_BOL_Service::getInstance()->isMemberJoinedToRoom(OW::getUser()->getId(), $room->id) ||
                OW::getUser()->getId() == intval($room->ownerId)
            ) {
                $owner = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($room->ownerId));
                $room->owner = $owner[$room->ownerId]['title'];
                $room->ownerSrc = $owner[$room->ownerId]['src'];
                $room->ownerUrl = $owner[$room->ownerId]['url'];

                switch($room->type)
                {
                    case "knowledge":
                        $room->url = OW_URL_HOME.'cocreation/knowledge-room/'.$room->id;
                        break;
                    case "commentarium":
                        $room->url = OW_URL_HOME.'cocreation/commentarium-room/'.$room->id;
                        break;
                    default:
                        $room->url = OW_URL_HOME.'cocreation/data-room/'.$room->id;
                        break;
                }

                array_push($visible_rooms, $room);
            } else {
                $members = COCREATION_BOL_Service::getInstance()->getRoomMembers($room->id);
                $isInvited = false;
                foreach($members as $member){
                    if(intval($member->userId) == OW::getUser()->getId()){
                        $isInvited = true;
                        break;
                    }
                }
                if($isInvited){

                    $roomUrl = null;
                    switch($room->type){
                        case "data":
                            $roomUrl = str_replace("index/", $room->id, OW::getRouter()->urlFor('COCREATION_CTRL_DataRoom', 'index') );
                            break;
                        case "media":
                            $roomUrl = str_replace("index/", $room->id, OW::getRouter()->urlFor('COCREATION_CTRL_DataRoom', 'index') );
                            break;
                        case "knowledge":
                            $roomUrl = str_replace("index/", $room->id, OW::getRouter()->urlFor('COCREATION_CTRL_KnowledgeRoom', 'index'));
                            break;
                        case "commentarium":
                            $roomUrl = str_replace("index/", $room->id, OW::getRouter()->urlFor('COCREATION_CTRL_CommentariumRoom', 'index'));
                            break;
                    }

                    $u = BOL_UserService::getInstance()->findUserById(intval($room->ownerId));
                    $js = "$.post('" .
                        OW::getRouter()->urlFor('COCREATION_CTRL_Ajax', 'confirmToJoinToRoom') . "?roomId=" . $room->id . "&memberId=" . OW::getUser()->getId() . "',
                    { mobile : true }, function (data, status) {
                       window.location ='" . $roomUrl. "';});";

                    array_push($invitations, "&#x25cf;  <b>" . $u->username . "</b> " . OW::getLanguage()->text('cocreation', 'room_invitation_text_toast') . "<b> " . $room->name .
                        "</b> <input class=\"confirm_button\" type=\"button\" value=\"" . OW::getLanguage()->text('cocreation', 'room_confirm_to_join') .
                        "\" onclick=\"" . $js . "\">");
                }
            }
        }

        $js = UTIL_JsGenerator::composeJsString('
                ODE.ajax_coocreation_delete_room = {$ajax_coocreation_delete_room}
            ', array(
               'ajax_coocreation_delete_room' => OW::getRouter()->urlFor('COCREATION_CTRL_Ajax', 'deleteRoom')
        ));
        OW::getDocument()->addOnloadScript($js);

        $this->assign('invitations', $invitations);
        $this->assign('cocreation_rooms', $visible_rooms);
       /*$this->assign('partialRoomUrl', str_replace("index/", "", OW::getRouter()->urlFor(($room->type == "data") ? 'COCREATION_CTRL_DataRoom'
                                                                                                                  : 'COCREATION_CTRL_KnowledgeRoom', 'index')));*/
        $this->assign('partialRoomUrl',OW_URL_HOME.'cocreation/');
        $this->assign('isActive', true);
        $this->assign('userId', OW::getUser()->getId());
        $this->assign('urlHome', OW_URL_HOME );

        OW::getLanguage()->addKeyForJs('cocreation', '  current_room_deleted');
        OW::getLanguage()->addKeyForJs('cocreation', 'confirm_delete_room');
        OW::getLanguage()->addKeyForJs('cocreation', 'room_delete_successful');
        OW::getLanguage()->addKeyForJs('cocreation', 'room_delete_fail');
    }
}