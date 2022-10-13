<?php

class COCREATION_CTRL_KnowledgeRoom extends OW_ActionController
{

    function getOutcomeReadonlyPadID($url) {
        try {
            $document_server_port_preference = BOL_PreferenceService::getInstance()->findPreference('document_server_port_preference');

            $apiurl = $_SERVER['REQUEST_SCHEME'] . "//" . $_SERVER['HTTP_HOST'] . ":{$document_server_port_preference}/api/1/getReadOnlyID?apikey=e20a517df87a59751b0f01d708e2cb6496cf6a59717ccfde763360f68a7bfcec&padID=" . explode("/", $url)[4];
            $ch = curl_init();
            // you should put here url of your getinfo.php script
            curl_setopt($ch, CURLOPT_URL, $apiurl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
            $result = json_decode($result);
            return $_SERVER['REQUEST_SCHEME'] . "//" . $_SERVER['HTTP_HOST'] . ":{$document_server_port_preference}/ro/" . $result->data->readOnlyID;
        }catch(Exception $e){
            return $url;
        }
    }

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
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('cocreation')->getStaticJsUrl()                . 'cocreation-knowledge.js', 'text/javascript');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('cocreation')->getStaticJsUrl()                . 'perfect-scrollbar/js/min/perfect-scrollbar.jquery.min.js', 'text/javascript');
        OW::getDocument()->getMasterPage()->setTemplate(OW::getPluginManager()->getPlugin('cocreation')->getRootDir() . 'master_pages/general.html');

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

            if(intval($room->ownerId) == OW::getUser()->getId()) {
                $this->assign('ownerUserActive', true);
                $this->assign('isMember', true);
            }else
                $this->assign('ownerUserActive', false);

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

            //Set room shared documents
            $documents = COCREATION_BOL_Service::getInstance()->getDocumentsByRoomId($params['roomId']);
            $this->assign('documents', array(
                OW_URL_HOME . "etherpad/p/" . $documents[0]->url,
                OW_URL_HOME . "etherpad/p/" . $documents[1]->url,
                OW_URL_HOME . "etherpad/p/" . $documents[2]->url));

            //get all dataset for current room
            $this->addComponent('datasets_library', new COCREATION_CMP_DatasetsLibrary($params['roomId']));

            $datalets = COCREATION_BOL_Service::getInstance()->getDataletsByRoomId($params['roomId']);
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
            }

            $this->assign('components_url', SPODPR_COMPONENTS_URL);
            $ode_datasets_list = ODE_BOL_Service::getInstance()->getSettingByKey('ode_datasets_list');
            $this->assign('datasets_list', empty($ode_datasets_list->value) ? "" : $ode_datasets_list->value);

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
                SPODPUBLICROOM = {}
                /*SPODPUBLICROOM.suggested_datasets      = {$cocreation_room_suggested_datasets}*/
                COCREATION.roomId                        = {$roomId}
                COCREATION.entity_type                   = {$entity_type}
                COCREATION.room_type                     = "knowledge"
                COCREATION.datalets                      = {$roomDatalets}
                COCREATION.postits                       = {$roomPostits}
                COCREATION.info                          = {$roomInfo}
                COCREATION.user_id                       = {$userId}
                COCREATION.suggested_datasets            = {$suggested_datasets}
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
                'roomDatalets'                         => $room_datalets,
                'roomPostits'                          => $postits,
                'roomInfo'                             => json_encode($info),
                'userId'                               => OW::getUser()->getId(),
                'suggested_datasets'                   => $this->getSuggestedDatasets($params['roomId'])
            ));

            OW::getDocument()->addOnloadScript($js);
            OW::getDocument()->addOnloadScript("room.init()");

            OW::getLanguage()->addKeyForJs('cocreation', 'room_menu_cocreation');
            OW::getLanguage()->addKeyForJs('cocreation', 'room_menu_data');
            OW::getLanguage()->addKeyForJs('cocreation', 'room_menu_tools');
            OW::getLanguage()->addKeyForJs('cocreation', 'dataset_add_fail');
            OW::getLanguage()->addKeyForJs('cocreation', 'dataset_fields_empty');
            OW::getLanguage()->addKeyForJs('cocreation', 'room_menu_data');
            OW::getLanguage()->addKeyForJs('cocreation', 'room_menu_cocreation');
            OW::getLanguage()->addKeyForJs('cocreation', 'room_menu_tools');
            OW::getLanguage()->addKeyForJs('cocreation', 'dataset_successfully_added');
        }
    }

    private function getSuggestedDatasets($roomId)
    {
        $suggested_datasets = COCREATION_BOL_Service::getInstance()->getDatasetsByRoomId($roomId);
        $suggested = [];

        foreach ($suggested_datasets as $suggested_dataset)
        {
            $obj = new StdClass();
            $obj->name = $suggested_dataset->name;
            $obj->url = $suggested_dataset->url;
            $obj->p = "suggested";
            $suggested[] = $obj;
        }

        return $suggested;
    }
}