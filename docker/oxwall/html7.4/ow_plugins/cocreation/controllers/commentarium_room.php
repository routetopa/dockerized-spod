<?php

class COCREATION_CTRL_CommentariumRoom extends OW_ActionController
{
    public function index(array $params)
    {
        //COCOCO
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('cocreation')->getStaticUrl()              . 'css/cocreation-room.css');
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('cocreation')->getStaticJsUrl()            . 'perfect-scrollbar/css/perfect-scrollbar.min.css');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('cocreation')->getStaticJsUrl()                . 'cocreation.js', 'text/javascript');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('cocreation')->getStaticJsUrl()                . 'masonry.pkgd.min.js', 'text/javascript');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('cocreation')->getStaticJsUrl()                . 'cocreation-room.js', 'text/javascript');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('cocreation')->getStaticJsUrl()                . 'cocreation-commentarium.js', 'text/javascript');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('cocreation')->getStaticJsUrl()                . 'perfect-scrollbar/js/min/perfect-scrollbar.jquery.min.js', 'text/javascript');
        OW::getDocument()->getMasterPage()->setTemplate(OW::getPluginManager()->getPlugin('cocreation')->getRootDir()      . 'master_pages/general.html');

        if ( isset($params['roomId'])){

            if(COCREATION_BOL_Service::getInstance()->isMemberJoinedToRoom(OW::getUser()->getId(), intval($params['roomId'])) ||
                BOL_AuthorizationService::getInstance()->isModerator() ||
                OW::getUser()->isAdmin())
                $this->assign('isMember',true);
            else
                $this->assign('isMember',false);

            //Set info for current co-creation room
            $room = COCREATION_BOL_Service::getInstance()->getRoomById($params['roomId']);
            $this->assign('owner', BOL_AvatarService::getInstance()->getDataForUserAvatars(array($room->ownerId))[$room->ownerId]);
            $this->assign('currentUserId', OW::getUser()->getId());

            $documents = COCREATION_BOL_Service::getInstance()->getDocumentsByRoomId($params['roomId']);
            $document = $documents[0]->url;
            if(intval($room->ownerId) == OW::getUser()->getId()) {
                $this->assign('ownerUserActive', true);
                $this->assign('isMember', true);
                $this->assign('role', '0');
                $document = OW_URL_HOME . "etherpad/p/" . $document;
            }else {
                $this->assign('ownerUserActive', false);
                $this->assign('role', '1');
                $document = OW_URL_HOME . "etherpad/p/" . COCREATION_CTRL_Ajax::getPadReadonlyId($documents[0]->url);
            }

            $this->assign('document', $document);
            $this->assign('room', $room);

            //Get room members
            $room_members = COCREATION_BOL_Service::getInstance()->getRoomMembers($params['roomId']);
            $members = array();
            foreach($room_members as $member) {
                $user   = BOL_UserService::getInstance()->findByEmail($member->email);
                $avatar = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($user->id))[$user->id];
                $avatar['isJoined'] = $member->isJoined == "1" ? true : false;
                array_push($members, $avatar);
            }

            $this->assign('members', $members);

            $info                 = new stdClass();
            $info->name           = $room->name;
            $info->subject        = $room->subject;
            $info->description    = $room->description;
            $info->from           = $room->from;
            $info->to             = $room->to;
            $info->goal           = $room->goal;
            $info->invitationText = $room->invitationText;
            $info->owner = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($room->ownerId))[$room->ownerId];
            $info->members = $members;

            $this->assign('currentUser' , BOL_AvatarService::getInstance()->getDataForUserAvatars(array(OW::getUser()->getId()))[OW::getUser()->getId()]);

            /* NEW DISCUSSION AGORA LIKE */
            $this->addComponent('discussion', new SPODDISCUSSION_CMP_Discussion($room->id));
            /* NEW DISCUSSION AGORA LIKE */

           /* $datalets = COCREATION_BOL_Service::getInstance()->getDataletsByRoomId($params['roomId']);
            $room_datalets = array();
            $postits       = array();
            foreach($datalets as $d){
                $datalet         =  ODE_BOL_Service::getInstance()->getDataletById($d->dataletId);
                $datalet->params = json_decode($datalet->params);
                $datalet->data   = str_replace("'","&#39;", $datalet->data);
                $datalet->fields = str_replace("'","&#39;", $datalet->fields);

                $datalet_string = "<" . $datalet->component . " datalet-id='". $datalet->id ."' fields='[" . rtrim(ltrim($datalet->fields, "}"), "{") . "]'";
                foreach($datalet->params as $key => $value)
                    $datalet_string .= " " . $key . "='" . $value . "'";
                $datalet_string .= "></" . $datalet->component . ">";

                array_push($room_datalets, $datalet_string);

                $datalet_postits = COCREATION_BOL_Service::getInstance()->getPostitByDataletId($datalet->id);
                $postits[$datalet->id] = json_encode($datalet_postits);
            }*/

            $this->assign('components_url', SPODPR_COMPONENTS_URL);
            $ode_datasets_list = ODE_BOL_Service::getInstance()->getSettingByKey('ode_datasets_list');
            $this->assign('datasets_list', empty($ode_datasets_list->value) ? '' : $ode_datasets_list->value);

            $js = UTIL_JsGenerator::composeJsString('
                ODE.current_room_url                     = {$current_room_url}
                ODE.ajax_coocreation_room_add_dataset    = {$ajax_coocreation_room_add_dataset}
                ODE.ajax_coocreation_room_get_datasets   = {$ajax_coocreation_room_get_datasets}
                ODE.ajax_coocreation_room_add_datalet    = {$ajax_coocreation_room_add_datalet}
                ODE.ajax_coocreation_room_delete_datalet = {$ajax_coocreation_room_delete_datalet}
                ODE.ajax_coocreation_room_get_datalets   = {$ajax_coocreation_room_get_datalets}
                ODE.ajax_coocreation_room_add_postit     = {$ajax_coocreation_room_add_postit}
                ODE.ajax_coocreation_room_delete_user    = {$ajax_coocreation_room_delete_user}
                ODE.numDataletsInCocreationRooom         = {$numDataletsInRoom}
                ODE.pluginPreview                        = "cocreation"
             
                COCREATION.roomId                        = {$roomId}
                COCREATION.entity_type                   = {$entity_type}
                COCREATION.room_type                     = "commentarium"
                //COCREATION.datalets                      = {$roomDatalets}
                COCREATION.info                          = {$roomInfo}
                COCREATION.user_id                       = {$userId}
            ', array(
                'current_room_url'                     => str_replace("/index", "", OW::getRouter()->urlFor('COCREATION_CTRL_KnowledgeRoom', 'index')) . $params['roomId'],
                'ajax_coocreation_room_add_dataset'    => OW::getRouter()->urlFor('COCREATION_CTRL_Ajax', 'addDatasetToRoom')   . "?roomId="  . $params['roomId'],
                'ajax_coocreation_room_get_datasets'   => OW::getRouter()->urlFor('COCREATION_CTRL_Ajax', 'getDatasetsForRoom') . "?roomId="  . $params['roomId'],
                'ajax_coocreation_room_add_datalet'    => OW::getRouter()->urlFor('COCREATION_CTRL_Ajax', 'addDataletToRoom')   . "?roomId="  . $params['roomId'],
                'ajax_coocreation_room_delete_datalet' => OW::getRouter()->urlFor('COCREATION_CTRL_Ajax', 'deleteDataletFromRoom'),
                'ajax_coocreation_room_get_datalets'   => OW::getRouter()->urlFor('COCREATION_CTRL_Ajax', 'getRoomDatalets')    . "?roomId="  . $params['roomId'],
                'ajax_coocreation_room_add_postit'     => OW::getRouter()->urlFor('COCREATION_CTRL_Ajax', 'addPostitToDatalet') . "?roomId="  . $params['roomId'],
                'ajax_coocreation_room_delete_user'    => OW::getRouter()->urlFor('COCREATION_CTRL_Ajax', 'deleteMemberFromRoom'),
                'numDataletsInRoom'                    => count(COCREATION_BOL_Service::getInstance()->getDataletsByRoomId($params['roomId'])),
                'roomId'                               => $params['roomId'],
                'entity_type'                          => COCREATION_BOL_Service::ROOM_ENTITY_TYPE,
                //'roomDatalets'                         => $room_datalets,
                'roomInfo'                             => json_encode($info),
                'userId'                               => OW::getUser()->getId(),
            ));

            OW::getDocument()->addOnloadScript($js);
            OW::getDocument()->addOnloadScript("room.init()");

            OW::getLanguage()->addKeyForJs('cocreation', 'room_menu_cocreation');
            OW::getLanguage()->addKeyForJs('cocreation', 'room_menu_data');
            OW::getLanguage()->addKeyForJs('cocreation', 'room_menu_tools');
            OW::getLanguage()->addKeyForJs('cocreation', 'room_menu_data');
            OW::getLanguage()->addKeyForJs('cocreation', 'room_menu_cocreation');
            OW::getLanguage()->addKeyForJs('cocreation', 'room_menu_tools');
        }
    }

}