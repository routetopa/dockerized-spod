<?php

class COCREATION_CTRL_DataRoom extends OW_ActionController
{
    public function index(array $params)
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $is_owner = false;

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodtchat')->getStaticJsUrl() . 'vendor/livequery-1.1.1/jquery.livequery.js');

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('cocreation')->getStaticJsUrl() . 'perfect-scrollbar/js/min/perfect-scrollbar.jquery.min.js');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('cocreation')->getStaticJsUrl() . 'vendor/socket.io.js');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('cocreation')->getStaticJsUrl() . 'cocreation.js');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('cocreation')->getStaticJsUrl() . 'cocreation-room.js');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('cocreation')->getStaticJsUrl() . 'cocreation-data.js');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('cocreation')->getStaticJsUrl() . 'vendor/qualicy/jsprivacychecker.js');

        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('cocreation')->getStaticJsUrl() . 'perfect-scrollbar/css/perfect-scrollbar.css');
        OW::getDocument()->getMasterPage()->setTemplate(OW::getPluginManager()->getPlugin('cocreation')->getRootDir() . 'master_pages/general.html');
        $this->assign('components_url', SPODPR_COMPONENTS_URL);

        if(COCREATION_BOL_Service::getInstance()->isMemberJoinedToRoom(OW::getUser()->getId(), intval($params['roomId'])) ||
           BOL_AuthorizationService::getInstance()->isModerator() ||
           OW::getUser()->isAdmin())
            $this->assign('isMember', true);
        else
            $this->assign('isMember', false);


        //Set info for current co-creation room
        $room = COCREATION_BOL_Service::getInstance()->getRoomById($params['roomId']);
        $owner = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($room->ownerId))[$room->ownerId];
        $this->assign('owner', $owner);

        if(intval($room->ownerId) == OW::getUser()->getId()) {
            $this->assign('ownerUserActive', true);
            $this->assign('isMember', true);
            $is_owner = true;
        }else
            $this->assign('ownerUserActive', false);

        $this->assign('room', $room);

        $datalets = COCREATION_BOL_Service::getInstance()->getDataletsByRoomId($params['roomId']);
        $room_datalets = array();
        foreach($datalets as $d){
            $datalet         =  ODE_BOL_Service::getInstance()->getDataletById($d->dataletId);
            $datalet->params = json_decode($datalet->params);
            $datalet->data   = str_replace("'","&#39;", $datalet->data);
            $datalet->fields = str_replace("'","&#39;", $datalet->fields);

            $datalet_string = "<" . $datalet->component . " datalet-id='". $datalet->id ."'  disable_my_space disable_html_export disable_link";
            foreach($datalet->params as $key => $value)
                $datalet_string .= " " . $key . "='" . $value . "'";
            $datalet_string .= "></" . $datalet->component . ">";

            array_push($room_datalets, $datalet_string);
        }

        //Get room members
        $room_members = COCREATION_BOL_Service::getInstance()->getRoomMembers($params['roomId']);
        $members    = array();
        $membersIds = array($room->ownerId);

        foreach($room_members as $member) {
            $user   = BOL_UserService::getInstance()->findByEmail($member->email);
            $avatar = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($user->id))[$user->id];
            $avatar['isJoined'] = $member->isJoined;
            $avatar['role'] = $member->role;
            array_push($members, $avatar);
            array_push($membersIds, $user->id);
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

        $sheetUrl = COCREATION_BOL_Service::getInstance()->getSheetByRoomId($params['roomId'])[0]->url;
        $noteUrl = COCREATION_BOL_Service::getInstance()->getDocumentsByRoomId($params['roomId'])[0]->url;

        $this->assign('spreadsheet', "/ethersheet/s/" . $sheetUrl);
        $this->assign('notes', "/etherpad/p/" . $noteUrl);

        $data = COCREATION_BOL_Service::getInstance()->getSheetData($sheetUrl);
        $headers = array();
        foreach($data as $serie) array_push($headers, $serie->name);
        $this->assign('headers', $headers);
        $this->assign('data', json_encode($data));

        $metadata = COCREATION_BOL_Service::getInstance()->getMetadataByRoomId($params['roomId']);

        $form = COCREATION_BOL_Service::getInstance()->getFormByRoomId($params['roomId']);

        /* NEW DISCUSSION AGORA LIKE */
        $this->addComponent('discussion', new SPODDISCUSSION_CMP_Discussion($room->id));
        /* NEW DISCUSSION AGORA LIKE */

        /* INFO */
        $this->addComponent('info_cmp', new COCREATION_CMP_Info($room));
        /* MEMBERS */
        $this->addComponent('members_cmp', new COCREATION_CMP_Members($owner, $members));

        /* METADATA IFRAME SRC */
        $metadata_url = "";
        switch($room->metadata)
        {
            case 1 : $metadata_url = OW::getPluginManager()->getPlugin('cocreation')->getStaticUrl() . 'pages/metadata/common_core/metadata_common_core.html'; break;
            case 2 : $metadata_url = OW::getPluginManager()->getPlugin('cocreation')->getStaticUrl() . 'pages/metadata/dcat_ap_it/metadata_dcat_ap_it.html'  ;   break;
        }
        $this->assign('metadata_url', $metadata_url);
        /* METADATA IFRAME SRC */

        /* FORM IFRAME SRC */
        $form_url = $is_owner ? OW::getPluginManager()->getPlugin('cocreation')->getStaticUrl() . 'pages/form/form_template.html' : OW::getPluginManager()->getPlugin('cocreation')->getStaticUrl() . 'pages/form/form.html';
        $this->assign('form_url', $form_url);
        /* FORM IFRAME SRC */

        $this->assign("toolbar_color", ($room->type == "data") ? "#4CAF50" : "#FF9800");
        $this->assign('datalet_definition_import', ODE_CLASS_Tools::getInstance()->get_all_datalet_definitions());

        //Publish on CKAN Authorization check.
        //It is useful to decide whether to show the Publish on CKAN or not.
        $ckan_platform_url_preference = BOL_PreferenceService::getInstance()->findPreference(COCREATION_CTRL_Admin::PREF_POCKAN_PLATFORM_URL);
        $ckan_api_key_preference = BOL_PreferenceService::getInstance()->findPreference(COCREATION_CTRL_Admin::PREF_POCKAN_API_KEY);
        $ckan_def_org_key_preference = BOL_PreferenceService::getInstance()->findPreference(COCREATION_CTRL_Admin::PREF_POCKAN_DEF_ORGANISATION);
        $ckan_def_groups_key_preference = BOL_PreferenceService::getInstance()->findPreference(COCREATION_CTRL_Admin::PREF_POCKAN_DEF_GROUPS);
        $canPublishOnCKAN = OW::getAuthorization()->isUserAuthorized(OW::getUser()->getId(), "cocreation", "Publish on CKAN");
        $canPublishOnCKAN = $canPublishOnCKAN
            && (!empty($ckan_platform_url_preference) && strlen($ckan_platform_url_preference->defaultValue) > 0)
            && (!empty($ckan_api_key_preference) && strlen($ckan_api_key_preference->defaultValue) > 0);
        $this->assign('canPublishOnCKAN', $canPublishOnCKAN);

        $ckan_platform_url_preference_default_value = "";
        $ckan_api_key_preference_default_value = "";
        if ($canPublishOnCKAN) {
            $this->assign('PublishOnCKAN_platform_url', $ckan_platform_url_preference->defaultValue);
            $this->assign('PublishOnCKAN_api_key', $ckan_api_key_preference->defaultValue);
            $ckan_platform_url_preference_default_value = $ckan_platform_url_preference->defaultValue;
            $ckan_api_key_preference_default_value = $ckan_api_key_preference->defaultValue;
            $ckan_def_organisation_preference_value = $ckan_def_org_key_preference->defaultValue;
            $ckan_def_groups_preference_value = $ckan_def_groups_key_preference->defaultValue;
        }

        $spreadsheet_server_port_pref = BOL_PreferenceService::getInstance()->findPreference('spreadsheet_server_port_preference');
        $spreasheet_server_port = empty($spreadsheet_server_port_pref->defaultValue) ? "8001" : $spreadsheet_server_port_pref->defaultValue;

        $js = UTIL_JsGenerator::composeJsString('
                ODE.ajax_coocreation_room_get_datalets        = {$ajax_coocreation_room_get_datalets}
                ODE.ajax_coocreation_room_get_array_sheetdata = {$ajax_coocreation_room_get_array_sheetdata}
                ODE.ajax_coocreation_room_update_metadata     = {$ajax_coocreation_room_update_metadata}
                ODE.ajax_coocreation_room_add_datalet         = {$ajax_coocreation_room_add_datalet}
                ODE.ajax_coocreation_room_delete_datalet      = {$ajax_coocreation_room_delete_datalet}
                ODE.ajax_coocreation_room_publish_dataset     = {$ajax_coocreation_room_publish_dataset}
                ODE.ajax_coocreation_room_get_html_note       = {$ajax_coocreation_room_get_html_note}
                ODE.ajax_coocreation_room_delete_user         = {$ajax_coocreation_room_delete_user}
                ODE.ajax_coocreation_room_save_form           = {$ajax_coocreation_room_save_form}
                COCREATION.sheetName                          = {$sheetName}
                COCREATION.roomId                             = {$roomId}
                COCREATION.room_type                          = "data"
                COCREATION.entity_type                        = {$entity_type}
                COCREATION.room_members                       = {$room_members}
                COCREATION.datalets                           = {$roomDatalets}
                COCREATION.metadata                           = {$room_metadata}
                COCREATION.form                               = {$form}
                COCREATION.user_id                            = {$userId}
                COCREATION.info                               = {$roomInfo}
                COCREATION.spreadsheet_server_port            = {$spreasheet_server_port}
                COCREATION.sheet_images_url                   = {$sheet_images_url}
                COCREATION.sheet_remove_image_url             = {$sheet_remove_image_url}
                COCREATION.user_info                          = {$user_info}
                COCREATION.owner                              = {$owner}
                COCREATION.metadata_url                       = {$metadata_url}
                COCREATION.metadata_type                      = {$metadata_type}
            ', array(
               'ajax_coocreation_room_get_datalets'        => OW::getRouter()->urlFor('COCREATION_CTRL_Ajax', 'getRoomDatalets'),
               'ajax_coocreation_room_get_array_sheetdata' => OW::getRouter()->urlFor('COCREATION_CTRL_Ajax', 'getArrayOfObjectSheetData') . "?sheetName=" . $sheetUrl,
               'ajax_coocreation_room_update_metadata'     => OW::getRouter()->urlFor('COCREATION_CTRL_Ajax', 'updateMetadata'),
               'ajax_coocreation_room_add_datalet'         => OW::getRouter()->urlFor('COCREATION_CTRL_Ajax', 'addDataletToRoom')          . "?roomId="  . $params['roomId'],
               'ajax_coocreation_room_delete_datalet'      => OW::getRouter()->urlFor('COCREATION_CTRL_Ajax', 'deleteDataletFromRoom'),
               'ajax_coocreation_room_publish_dataset'     => OW::getRouter()->urlFor('COCREATION_CTRL_Ajax', 'publishDataset'),
               'ajax_coocreation_room_get_html_note'       => OW::getRouter()->urlFor('COCREATION_CTRL_Ajax', 'getNoteHTMLByPadIDApiUrl')  . "?noteUrl="  . $noteUrl,
               'ajax_coocreation_room_delete_user'         => OW::getRouter()->urlFor('COCREATION_CTRL_Ajax', 'deleteMemberFromRoom'),
               'ajax_coocreation_room_save_form'           => $is_owner ? OW::getRouter()->urlFor('COCREATION_CTRL_Ajax', 'saveRoomForm') : OW::getRouter()->urlFor('COCREATION_CTRL_Ajax', 'saveRoomFormSubmission'),
               'sheetName'                                 => $sheetUrl,
               'roomId'                                    => $params['roomId'],
               'entity_type'                               => COCREATION_BOL_Service::ROOM_ENTITY_TYPE,
               'room_members'                              => json_encode($membersIds),
               'roomDatalets'                              => $room_datalets,
               'room_metadata'                             => $metadata->metadata,
               'form'                                      => empty($form) ? '' : ($is_owner ? $form->formTemplate : $form->form),
               'userId'                                    => OW::getUser()->getId(),
               'roomInfo'                                  => json_encode($info),
               'spreasheet_server_port'                    => $spreasheet_server_port,
               'sheet_images_url'                          => OW_URL_HOME . "ethersheet/images/" . $sheetUrl,
               'ckan_platform_url_preference'              => $ckan_platform_url_preference_default_value,
               'ckan_api_key_preference'                   => $ckan_api_key_preference_default_value,
               'ckan_def_organisation_preference'          => empty($ckan_def_organisation_preference_value) ? "" : $ckan_def_organisation_preference_value,
               'ckan_def_groups_preference'                => empty($ckan_def_groups_preference_value) ? "" : $ckan_def_groups_preference_value,
               'sheet_remove_image_url'                    => OW_URL_HOME . "ethersheet/remove/image",
               'user_info'                                 => ['username' => BOL_UserService::getInstance()->findUserById(OW::getUser()->getId())->username, 'mail' => BOL_UserService::getInstance()->findUserById(OW::getUser()->getId())->email],
               'owner'                                     => ['username' => BOL_UserService::getInstance()->findUserById($room->ownerId)->username, 'mail' => BOL_UserService::getInstance()->findUserById($room->ownerId)->email],
               'metadata_url'                              => $metadata_url,
               'metadata_type'                             => $room->metadata
        ));
        OW::getDocument()->addOnloadScript($js);
        OW::getDocument()->addOnloadScript("data_room.init();");

        OW::getLanguage()->addKeyForJs('cocreation', 'confirm_delete_datalet');
        OW::getLanguage()->addKeyForJs('cocreation', 'datalet_successfully_added');
        OW::getLanguage()->addKeyForJs('cocreation', 'datalet_successfully_deleted');
        OW::getLanguage()->addKeyForJs('cocreation', 'datalet_delete_fail');
        OW::getLanguage()->addKeyForJs('cocreation', 'room_delete_fail');
        OW::getLanguage()->addKeyForJs('cocreation', 'user_delete_fail');
        OW::getLanguage()->addKeyForJs('cocreation', 'user_successfully_deleted');
        OW::getLanguage()->addKeyForJs('cocreation', 'dataset_successfully_published');
        OW::getLanguage()->addKeyForJs('cocreation', 'dataset_successfully_added');
        OW::getLanguage()->addKeyForJs('cocreation', 'metadata_successfully_saved');
        OW::getLanguage()->addKeyForJs('cocreation', 'metadata_successfully_updated');
        OW::getLanguage()->addKeyForJs('cocreation', 'error_metadata_updates');
        OW::getLanguage()->addKeyForJs('cocreation', 'privacy_message_datalet_published');
        OW::getLanguage()->addKeyForJs('cocreation', 'current_room_deleted');
    }

}