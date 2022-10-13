<?php
require_once OW::getPluginManager()->getPlugin('spodnotification')->getRootDir()
    . 'lib/vendor/autoload.php';

use ElephantIO\Client;
use ElephantIO\Engine\SocketIO\Version1X;


class COCREATION_CTRL_Ajax extends OW_ActionController
{
    public function loadTemplateDoc($note){
        // POST
        // http://172.16.15.77/etherpad/p/notes_room_241_8rY1B/import
        // Content-Type:multipart/form-data;
        // BODY
        // Content-Disposition: form-data; name="file"; filename="ChatLog ROUTE_TO_PA Technical Meeting 2015_06_30 17_14.rtf"
        // Content-Type: application/msword

        $cfile = new CURLFile(
            OW::getPluginManager()->getPlugin('cocreation')->getStaticDir() . "template/explore-template.docx",
            'application/msword',
            'file');
        /*$cfile = curl_file_create(
            OW::getPluginManager()->getPlugin('cocreation')->getStaticDir() . "template/explore-template.docx",
            'application/msword',
            'file'
        );*/

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_UPLOAD, true);
        curl_setopt($ch, CURLOPT_URL,  $_SERVER['REQUEST_SCHEME'] . "//" . $_SERVER['HTTP_HOST'] . "/etherpad/p/" . /*$note*/'notes_room_241_8rY1B' . "/import");
        curl_setopt($ch, CURLOPT_HTTPHEADER,
            array(
                'Content-Type: multipart/form-data'
            )
        );

        $postData = array(
            /*'name'     => 'file',
            'filename' => '@/path/to/file.txt',//the content*/
            'file' => $cfile
        );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        $response = curl_exec($ch);
        curl_close($ch);
    }

    static function getPadReadonlyId($padName) {
        try {
            $document_server_port_preference = BOL_PreferenceService::getInstance()->findPreference('document_server_port_preference');

            $apiurl = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . "/etherpad/api/1/getReadOnlyID?apikey=e20a517df87a59751b0f01d708e2cb6496cf6a59717ccfde763360f68a7bfcec&padID=" . $padName;
            $ch = curl_init();
            // you should put here url of your getinfo.php script
            curl_setopt($ch, CURLOPT_URL, $apiurl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
            $result = json_decode($result);
            return $result->data->readOnlyID;
        }catch(Exception $e){
            return null;
        }
    }

    public function createRoom(){

        if (!OW::getUser()->isAuthenticated())
        {
            try
            {
                $user_id = ODE_CLASS_Tools::getInstance()->getUserFromJWT($_REQUEST['jwt']);
            }
            catch (Exception $e)
            {
                echo json_encode(array("status"  => "ko", "error_message" => $e->getMessage()));
                exit;
            }
        }else{
            $user_id = OW::getUser()->getId();
        }

        /*$clean = ODE_CLASS_InputFilter::getInstance()->sanitizeInputs($_REQUEST);
        if ($clean == null){
            OW::getFeedback()->info(OW::getLanguage()->text('cocreation', 'insane_user_email_value'));
            exit;
        }*/

        $room = COCREATION_BOL_Service::getInstance()->addRoom(
            OW::getUser()->getId(),
            $_REQUEST['name'],
            $_REQUEST['subject'],
            $_REQUEST['description'],
            $_REQUEST['metadata'],
            $_REQUEST['data_from'],
            $_REQUEST['data_to'],
            $_REQUEST['goal'],
            $_REQUEST['invitation_text'],
            empty($_REQUEST['is_open']) ? 0 : 1,
            implode("#######", $_REQUEST['users_value']),
            $_REQUEST['room_type']
        );

        $randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 5);

        switch($_REQUEST['room_type']){
            case "knowledge":
                COCREATION_BOL_Service::getInstance()->addDocToRoom($room->id, 0, "explore", "explore_room_" .$room->id."_".$randomString);
                COCREATION_BOL_Service::getInstance()->addDocToRoom($room->id, 1, "ideas",   "ideas_room_"   .$room->id."_".$randomString);
                COCREATION_BOL_Service::getInstance()->addDocToRoom($room->id, 2, "outcome", "outcome_room_" .$room->id."_".$randomString);
                break;
            case "commentarium":
                COCREATION_BOL_Service::getInstance()->addDocToRoom($room->id, 0, "opera", "commentarium_room_" .$room->id."_".$randomString);
                COCREATION_BOL_Service::getInstance()->addDocToRoom($room->id, 0, "opera", $this->getPadReadonlyId("commentarium_room_" .$room->id."_".$randomString));
                break;
            default:
                //create the sheet for the CoCreation Data room
                //Document for notes related to the dataset
                COCREATION_BOL_Service::getInstance()->addDocToRoom($room->id, 1, "notes", "notes_room_"  .$room->id."_".$randomString);
                COCREATION_BOL_Service::getInstance()->addSheetToRoom($room->id, "dataset", "dataset_room_".$room->id."_".$randomString);
                COCREATION_BOL_Service::getInstance()->createMetadataForRoom($room->id);

                if($_REQUEST['room_type'] == "media")
                    $this->initEthersheetMediaRoom("dataset_room_".$room->id."_".$randomString);
                break;
        }

        //Send message to all members
        foreach($_REQUEST['users_value'] as $user)
        {
            $u = BOL_UserService::getInstance()->findByEmail($user);
            if($u->id != NULL) {
                if(!COCREATION_BOL_Service::getInstance()->isMemberInvitedToRoom($u->id, $room->id)) {
                    COCREATION_BOL_Service::getInstance()->addUserToRoom($room->id, $user, $u->id);
                    $js = "$.post('" .
                        OW::getRouter()->urlFor('COCREATION_CTRL_Ajax', 'confirmToJoinToRoom') . "?roomId=" . $room->id . "&memberId=" . $u->id . "',
                            { mobile : true }, function (data, status) {
                               window.location ='" .
                        str_replace("index/", $room->id, OW::getRouter()->urlFor($room->type == "knowledge" ? 'COCREATION_CTRL_KnowledgeRoom' : 'COCREATION_CTRL_DataRoom', 'index')) . "';});";

                    $message = $_REQUEST['invitation_text'] . "<br><br>" . "<span class=\"ow_button\"><input type=\"button\" value=\"Conform to join\" onclick=\"" . $js . "\"></span>";
                    if (OW::getPluginManager()->isPluginActive('mailbox'))
                        MAILBOX_BOL_ConversationService::getInstance()->createConversation(OW::getUser()->getId(), $u->id, "Join to co-creation room : " . $_REQUEST['name'], $message);
                }else{
                    OW::getFeedback()->info(OW::getLanguage()->text('cocreation', 'feedback_member_already_added'));
                }
            }
        }

//        COCREATION_CLASS_EventHandler::getInstance()->sendNotificationRoomCreated($user_id, $room);

        SPODNOTIFICATION_BOL_Service::getInstance()->registerUserForNotification(
            OW::getUser()->getId(),
            COCREATION_CLASS_Consts::PLUGIN_NAME,
            SPODNOTIFICATION_CLASS_MailEventNotification::$TYPE,
            COCREATION_CLASS_Consts::PLUGIN_ACTION_COMMENT .'_'. $room->id,
            SPODNOTIFICATION_CLASS_Consts::FREQUENCY_IMMEDIATELY,
            COCREATION_CLASS_Consts::PLUGIN_ACTION_COMMENT
        );

        OW::getFeedback()->info(OW::getLanguage()->text('cocreation', 'feedback_create_room_successful'));
        OW::getApplication()->redirect(OW::getRouter()->urlFor('COCREATION_CTRL_Main', 'index'));
    }

    public function deleteRoom(){
        $clean = ODE_CLASS_InputFilter::getInstance()->sanitizeInputs($_REQUEST);
        if ($clean == null){
            echo json_encode(array("status" => "error", "massage" => 'Insane inputs detected'));
            OW::getFeedback()->info(OW::getLanguage()->text('cocreation', 'insane_user_email_value'));
            exit;
        }

        COCREATION_BOL_Service::getInstance()->deleteRoomById($clean['roomId']);
        OW::getFeedback()->info(OW::getLanguage()->text('cocreation', 'feedback_delete_room_successful'));
        echo json_encode(array("status" => "ok", "message" => "dataset successful created in the current room"));
        SPODNOTIFICATION_CLASS_EventHandler::getInstance()->emitNotification(["plugin" => "cocreation",
            "operation" => "deleteRoom",
            "entity_type" => COCREATION_BOL_Service::ROOM_ENTITY_TYPE,
            "entity_id" => $clean['roomId']]);
        exit;
    }

    public function addNewMembersToRoom(){
        if (!OW::getUser()->isAuthenticated())
        {
            try
            {
                $user_id = ODE_CLASS_Tools::getInstance()->getUserFromJWT($_REQUEST['jwt']);
            }
            catch (Exception $e)
            {
                echo json_encode(array("status"  => "ko", "error_message" => $e->getMessage()));
                exit;
            }
        }else{
            $user_id = OW::getUser()->getId();
        }

        $clean = ODE_CLASS_InputFilter::getInstance()->sanitizeInputs($_REQUEST);
        if ($clean == null){
            /*echo json_encode(array("status" => "error", "massage" => 'Insane inputs detected'));*/
            OW::getFeedback()->info(OW::getLanguage()->text('cocreation', 'insane_user_email_value'));
            exit;
        }

        $room = COCREATION_BOL_Service::getInstance()->getRoomById($clean['roomId']);

        $url = "";
        switch($room->type){
            case "data":
                $url = str_replace("index/", $room->id, OW::getRouter()->urlFor('COCREATION_CTRL_DataRoom', 'index') );
                break;
            case "media":
                $url = str_replace("index/", $room->id, OW::getRouter()->urlFor('COCREATION_CTRL_DataRoom', 'index') );
                break;
            case "knowledge":
                $url = str_replace("index/", $room->id, OW::getRouter()->urlFor('COCREATION_CTRL_KnowledgeRoom', 'index'));
                break;
            case "commentarium":
                $url = str_replace("index/", $room->id, OW::getRouter()->urlFor('COCREATION_CTRL_CommentariumRoom', 'index'));
                break;
        }

        foreach($clean['users_value'] as $user){
            $u   = BOL_UserService::getInstance()->findByEmail($user);
            if(!COCREATION_BOL_Service::getInstance()->isMemberInvitedToRoom($u->id, $room->id)) {
                if (!COCREATION_BOL_Service::getInstance()->isMemberJoinedToRoom($u->id, $room->id)) {

                    COCREATION_BOL_Service::getInstance()->addUserToRoom($room->id, $user, $u->id);
                    $js = "$.post('" .
                        OW::getRouter()->urlFor('COCREATION_CTRL_Ajax', 'confirmToJoinToRoom') . "?roomId=" . $room->id . "&memberId=" . $u->id . "',
                        { mobile : true }, function (data, status) {
                           window.location ='" . $url. "';});";

                    $message = $room->invitationText . "<br><br>" . "<span class=\"ow_button\"><input type=\"button\" value=\"Confirm to join\" onclick=\"" . $js . "\"></span>";
                    if (OW::getPluginManager()->isPluginActive('mailbox'))
                        MAILBOX_BOL_ConversationService::getInstance()->createConversation(OW::getUser()->getId(), $u->id, "Join to co-creation room : " . $room->name, $message);

                    COCREATION_CLASS_EventHandler::getInstance()->sendNotificationRoomInvitation($user_id, $room, $u->id);
                }
            }else{
                OW::getFeedback()->info(OW::getLanguage()->text('cocreation', 'feedback_member_already_added'));
            }
        }

        OW::getFeedback()->info(OW::getLanguage()->text('cocreation', 'feedback_members_add_successful'));

        $this->redirect($url);

    }

    public function deleteMemberFromRoom()
    {
        $clean = ODE_CLASS_InputFilter::getInstance()->sanitizeInputs($_REQUEST);
        if ($clean == null) {
            OW::getFeedback()->info(OW::getLanguage()->text('cocreation', 'insane_inputs_detected'));
            exit;
        }

        COCREATION_BOL_Service::getInstance()->deleteMembersFromRoom($clean['userId']);

        SPODNOTIFICATION_CLASS_EventHandler::getInstance()->emitNotification([
            "plugin"      => "cocreation",
            "operation"   => "deleteUser",
            "user_name"   => BOL_AvatarService::getInstance()->getDataForUserAvatars(array($clean['userId']))[$clean['userId']]['title'],
            "entity_type" => COCREATION_BOL_Service::ROOM_ENTITY_TYPE]);
        echo json_encode(array("status" => "ok", "message" => "users has been deleted from this room"));
        OW::getFeedback()->info(OW::getLanguage()->text('cocreation', 'feedback_delete_room_user'));
        //$this->redirect(str_replace("index/", $clean['roomId'], $clean['roomType'] == "knowledge" ? OW::getRouter()->urlFor('COCREATION_CTRL_KnowledgeRoom', 'index') : OW::getRouter()->urlFor('COCREATION_CTRL_DataRoom', 'index') ));
        exit;
    }

    public function addDatasetToRoom()
    {
        $clean = ODE_CLASS_InputFilter::getInstance()->sanitizeInputs($_REQUEST);
        if ($clean == null){
            /*echo json_encode(array("status" => "error", "massage" => 'Insane inputs detected'));*/
            OW::getFeedback()->info(OW::getLanguage()->text('cocreation', 'insane_user_email_value'));
            exit;
        }

        COCREATION_BOL_Service::getInstance()->addDatasetToRoom($clean['roomId'],
                                                                $clean['dataUrl'],
                                                                $clean['datasetName'],
                                                                $clean['datasetDescription'],
                                                                $clean['datasetFields']);


        echo json_encode(array("status" => "ok", "message" => "dataset successful created in the current room"));
        SPODNOTIFICATION_CLASS_EventHandler::getInstance()->emitNotification(["plugin"      => "cocreation",
                                                                              "operation"   => "addDatasetToRoom",
                                                                              "entity_type" => COCREATION_BOL_Service::ROOM_ENTITY_TYPE,
                                                                              "entity_id"   => $clean['roomId']]);
        exit;

    }

    public function getDatasetsForRoom(){
        $clean = ODE_CLASS_InputFilter::getInstance()->sanitizeInputs($_REQUEST);
        if ($clean == null){
            /*echo json_encode(array("status" => "error", "massage" => 'Insane inputs detected'));*/
            OW::getFeedback()->info(OW::getLanguage()->text('cocreation', 'insane_user_email_value'));
            exit;
        }
        $datasets = COCREATION_BOL_Service::getInstance()->getDatasetsByRoomId($clean['roomId']);

        $suggested_datasets = array();
        foreach($datasets as $dataset){
            $d = new stdClass();
            $metas = new stdClass();
            $metas->description = $dataset->description;

            $d->resource_name =  $dataset->name;
            $d->url           =  $dataset->url;
            $d->metas         =  json_encode($metas);
            array_push($suggested_datasets, $d);
        }

        echo json_encode(array("status" => "ok", "suggested_datasets" => json_encode($suggested_datasets)));
        exit;
    }

    public function addDataletToRoom(){

        $clean = ODE_CLASS_InputFilter::getInstance()->sanitizeInputs($_REQUEST);
        if ($clean == null){
            /*echo json_encode(array("status" => "error", "massage" => 'Insane inputs detected'));*/
            OW::getFeedback()->info(OW::getLanguage()->text('cocreation', 'insane_user_email_value'));
            exit;
        }

        if( ODE_CLASS_Helper::validateDatalet($clean['component'], $clean['params'], $clean['fields']) )
        {
            $datalet = ODE_BOL_Service::getInstance()->saveDatalet(
                $clean['component'],
                $clean['fields'],
                OW::getUser()->getId(),
                $clean['params'],
                $clean['data']);

            COCREATION_BOL_Service::getInstance()->addDataletToRoom($clean['roomId'], $datalet->id);

            $datalets = COCREATION_BOL_Service::getInstance()->getDataletsByRoomId($clean['roomId']);
            $room_datalets = array();
            foreach($datalets as $d){
                $datalet         =  ODE_BOL_Service::getInstance()->getDataletById($d->dataletId);
                $datalet->params = json_decode($datalet->params);
                $datalet->data   = str_replace("'","&#39;", $datalet->data);
                $datalet->fields = str_replace("'","&#39;", $datalet->fields);

                $datalet_string = "<" . $datalet->component . " datalet-id='". $datalet->id ."' disable_my_space disable_html_export disable_link fields='[" . rtrim(ltrim($datalet->fields, "}"), "{") . "]'";
                foreach($datalet->params as $key => $value)
                    $datalet_string .= " " . $key . "='" . $value . "'";
                $datalet_string .= "></" . $datalet->component . ">";

                array_push($room_datalets, $datalet_string);
            }

            OW::getFeedback()->info(OW::getLanguage()->text('cocreation', 'feedback_add_datalet_successful'));
            echo json_encode(array("status" => "ok", "message" => "datalet successful created in the current room", "dataletId" => $datalet->id));
            SPODNOTIFICATION_CLASS_EventHandler::getInstance()->emitNotification(["plugin"      => "cocreation",
                                                                                  "operation"   => "addDataletToRoom",
                                                                                  "entity_type" => COCREATION_BOL_Service::ROOM_ENTITY_TYPE,
                                                                                  "entity_id"   => $clean['roomId'],
                                                                                  "user_id"     => OW::getUser()->getId(),
                                                                                  "datalets"    => $room_datalets]);
            exit;
        }else{
            OW::getFeedback()->info(OW::getLanguage()->text('cocreation', 'feedback_add_datalet_fail'));
            echo json_encode(array("status" => "error", "message" => "There are some problems with selected parameters for current datalet"));
            exit;
        }
    }

    public function deleteDataletFromRoom(){
        $clean = ODE_CLASS_InputFilter::getInstance()->sanitizeInputs($_REQUEST);
        if ($clean == null){
            echo json_encode(array("status" => "error", "massage" => 'Insane inputs detected'));
            OW::getFeedback()->info(OW::getLanguage()->text('cocreation', 'insane_user_email_value'));
            exit;
        }

        COCREATION_BOL_Service::getInstance()->deleteDataletFromRoom($clean['roomId'], $clean['dataletId']);
        $datalets = COCREATION_BOL_Service::getInstance()->getDataletsByRoomId($clean['roomId']);
        $room_datalets = array();
        foreach($datalets as $d){
            $datalet         =  ODE_BOL_Service::getInstance()->getDataletById($d->dataletId);
            $datalet->params = json_decode($datalet->params);
            $datalet->data   = str_replace("'","&#39;", $datalet->data);
            $datalet->fields = str_replace("'","&#39;", $datalet->fields);

            $datalet_string = "<" . $datalet->component . " datalet-id='". $datalet->id ."' disable_my_space disable_html_export disable_link fields='[" . rtrim(ltrim($datalet->fields, "}"), "{") . "]'";
            foreach($datalet->params as $key => $value)
                $datalet_string .= " " . $key . "='" . $value . "'";
            $datalet_string .= "></" . $datalet->component . ">";

            array_push($room_datalets, $datalet_string);
        }

        OW::getFeedback()->info(OW::getLanguage()->text('cocreation', 'feedback_delete_datalet_successful'));
        echo json_encode(array("status" => "ok", "message" => "datalet successful deleted in the current room"));
        SPODNOTIFICATION_CLASS_EventHandler::getInstance()->emitNotification(["plugin"           => "cocreation",
                                                                              "operation"        => "deleteDataletFromRoom",
                                                                              "entity_type"      => COCREATION_BOL_Service::ROOM_ENTITY_TYPE,
                                                                              "entity_id"        => $clean['roomId'],
                                                                              "user_id"          => OW::getUser()->getId(),
                                                                              "deleted_position" => $clean['deletedPosition'],
                                                                              "datalets"         => $room_datalets]);
        exit;
    }

    public function addPostitToDatalet()
    {
        $clean = ODE_CLASS_InputFilter::getInstance()->sanitizeInputs($_REQUEST);
        if ($clean == null){
            /*echo json_encode(array("status" => "error", "massage" => 'Insane inputs detected'));*/
            OW::getFeedback()->info(OW::getLanguage()->text('cocreation', 'insane_user_email_value'));
            exit;
        }

        COCREATION_BOL_Service::getInstance()->addPostitToDataletInRoom(
            $clean['roomId'],
            $clean['dataletId'],
            $clean['title'],
            $clean['content']);

        $datalet_postits = COCREATION_BOL_Service::getInstance()->getPostitByDataletId($clean['dataletId']);

        SPODNOTIFICATION_CLASS_EventHandler::getInstance()->emitNotification(["plugin"      => "cocreation",
                                 "operation"   => "addPostitToDatalet",
                                 "postits"     => json_encode($datalet_postits),
                                 "dataletId"   => $clean['dataletId'],
                                 "entity_type" => COCREATION_BOL_Service::ROOM_ENTITY_TYPE,
                                 "entity_id"   => $clean['roomId']]);
        exit;
    }

    public function getRoomDatalets()
    {
        $clean = ODE_CLASS_InputFilter::getInstance()->sanitizeInputs($_REQUEST);
        if ($clean == null){
            /*echo json_encode(array("status" => "error", "massage" => 'Insane inputs detected'));*/
            OW::getFeedback()->info(OW::getLanguage()->text('cocreation', 'insane_user_email_value'));
            exit;
        }

        $datalets = COCREATION_BOL_Service::getInstance()->getDataletsByRoomId($clean['roomId']);
        $room_datalets = array();
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
        }

        /*$datalets = COCREATION_BOL_Service::getInstance()->getDataletsByRoomId($clean['roomId']);
        $room_datalets = array();
        foreach($datalets as $d){
            $datalet         = ODE_BOL_Service::getInstance()->getDataletById($d->dataletId);
            $datalet->params = json_decode($datalet->params);
            $datalet->data   = htmlspecialchars($datalet->data);
            $datalet->fields = htmlspecialchars($datalet->fields);
            array_push($room_datalets, $datalet);
        }*/

        echo json_encode(array("status" => "ok", "datalets" => $room_datalets));
        exit;
    }

    public function confirmToJoinToRoom()
    {
        if (!OW::getUser()->isAuthenticated())
        {
            try
            {
                $user_id = ODE_CLASS_Tools::getInstance()->getUserFromJWT($_REQUEST['jwt']);
            }
            catch (Exception $e)
            {
                echo json_encode(array("status"  => "ko", "error_message" => $e->getMessage()));
                exit;
            }
        }else{
            $user_id = OW::getUser()->getId();
        }

        $clean = ODE_CLASS_InputFilter::getInstance()->sanitizeInputs($_REQUEST);
        if ($clean == null){
            echo json_encode(array("status" => false, "message" => 'Insane inputs detected'));
            OW::getFeedback()->info(OW::getLanguage()->text('cocreation', 'insane_user_email_value'));
            exit;
        }

        $room  = COCREATION_BOL_Service::getInstance()->getRoomById($clean['roomId']);
        COCREATION_BOL_Service::getInstance()->memberJoinToRoom($clean['memberId'], $clean['roomId']);
        COCREATION_CLASS_EventHandler::getInstance()->sendNotificationRoomJoin($user_id, $room, $clean['memberId']);

        SPODNOTIFICATION_BOL_Service::getInstance()->registerUserForNotification(
            $clean['memberId'],
            COCREATION_CLASS_Consts::PLUGIN_NAME,
            SPODNOTIFICATION_CLASS_MailEventNotification::$TYPE,
            COCREATION_CLASS_Consts::PLUGIN_ACTION_COMMENT .'_'. $clean['roomId'],
            SPODNOTIFICATION_CLASS_Consts::FREQUENCY_IMMEDIATELY,
            COCREATION_CLASS_Consts::PLUGIN_ACTION_COMMENT
        );

        if(isset($clean['mobile'])){
            //echo json_encode(array("status" => true, "message" => $message, "data" => $data));
            echo json_encode(array("status" => true, "message" => 'Join successful'));
            exit;
        }else{

            $url = "";
            switch($room->type){
                case "data":
                    $url = str_replace("index/", $room->id, OW::getRouter()->urlFor('COCREATION_CTRL_DataRoom', 'index') );
                    break;
                case "media":
                    $url = str_replace("index/", $room->id, OW::getRouter()->urlFor('COCREATION_CTRL_DataRoom', 'index') );
                    break;
                case "knowledge":
                    $url = str_replace("index/", $room->id, OW::getRouter()->urlFor('COCREATION_CTRL_KnowledgeRoom', 'index'));
                    break;
                case "commentarium":
                    $url = str_replace("index/", $room->id, OW::getRouter()->urlFor('COCREATION_CTRL_CommentariumRoom', 'index'));
                    break;
            }

            //echo json_encode(array("status" => true, "message" => 'Join successful'));
            OW::getApplication()->redirect($url);
            exit;
        }
    }

    public function getSheetData(){

        $clean = ODE_CLASS_InputFilter::getInstance()->sanitizeInputs($_REQUEST);
        if ($clean == null){
            OW::getFeedback()->info(OW::getLanguage()->text('cocreationes', 'insane_values'));
            exit;
        }
        echo json_encode(COCREATION_BOL_Service::getInstance()->getSheetData($clean['sheetName']));
        exit;
    }

    public function getArrayOfObjectSheetData()
    {
        //ser cors header
        $clean = ODE_CLASS_InputFilter::getInstance()->sanitizeInputs($_REQUEST);
        if ($clean == null){
            OW::getFeedback()->info(OW::getLanguage()->text('cocreationes', 'insane_values'));
            exit;
        }

        header("Access-Control-Allow-Origin: *");
        echo json_encode(COCREATION_BOL_Service::getInstance()->getArrayOfObjectSheetData($clean['sheetName']));
        exit;
    }

    public function updateMetadata()
    {
        if (!OW::getUser()->isAuthenticated())
        {
            try
            {
                $user_id = ODE_CLASS_Tools::getInstance()->getUserFromJWT($_REQUEST['jwt']);
            }
            catch (Exception $e)
            {
                echo json_encode(array("status"  => "ko", "error_message" => $e->getMessage()));
                exit;
            }
        }else{
            $user_id = OW::getUser()->getId();
        }

        $clean = $_REQUEST;//ODE_CLASS_InputFilter::getInstance()->sanitizeInputs($_REQUEST);
        if ($clean == null){
            OW::getFeedback()->info(OW::getLanguage()->text('cocreation', 'insane_user_email_value'));
            exit;
        }

        if(COCREATION_BOL_Service::getInstance()->updateMetadata(
                $clean['roomId'],
                $clean['metadata']))
        {

            echo json_encode(array("status" => "ok", "message" => "metadata sucessfully update for current room"));

            SPODNOTIFICATION_CLASS_EventHandler::getInstance()->emitNotification(
            [
                "user_id"     => $user_id,
                "plugin"      => "cocreation",
                "operation"   => "updateMetadata",
                "metadata"    => $clean['metadata'],
                "entity_type" => COCREATION_BOL_Service::ROOM_ENTITY_TYPE,
                "entity_id"   => $clean['roomId']
            ]);
        }else
           echo json_encode(array("status" => "error", "message" => "error in sql syntax"));
        exit;

    }

    public function publishDataset()
    {
        if (!OW::getUser()->isAuthenticated())
        {
            try
            {
                $user_id = ODE_CLASS_Tools::getInstance()->getUserFromJWT($_REQUEST['jwt']);
            }
            catch (Exception $e)
            {
                echo json_encode(array("status"  => "ko", "error_message" => $e->getMessage()));
                exit;
            }
        }else{
            $user_id = OW::getUser()->getId();
        }


        $clean = ODE_CLASS_InputFilter::getInstance()->sanitizeInputs($_REQUEST);
        if ($clean == null){
            OW::getFeedback()->info(OW::getLanguage()->text('cocreation', 'insane_user_email_value'));
            exit;
        }

        COCREATION_BOL_Service::getInstance()->addDataset($clean['roomId'],
                                                          $clean['owners'],
                                                          $clean['datasetId'],
                                                          $clean['data'],
                                                          $clean['notes'],
                                                          $clean['metadata']);


        $room = COCREATION_BOL_Service::getInstance()->getRoomById($clean['roomId']);
        $metadata = json_decode($clean['metadata']);


        $resource_name = "";
        if($metadata->title != "")
        {
            $resource_name = $metadata->title;
        }
        else if(count($room) > 0)
        {
            $resource_name = $room->name;
        }
        else
        {
            $resource_name = $clean['datasetId'];
        }

        COCREATION_CLASS_EventHandler::getInstance()->sendNotificationDatasetPublished($user_id, $resource_name);


        exit;
    }

    public function getNoteHTMLByPadIDApiUrl()
    {
        $clean = ODE_CLASS_InputFilter::getInstance()->sanitizeInputs($_REQUEST);
        if ($clean == null){
            OW::getFeedback()->info(OW::getLanguage()->text('cocreation', 'insane_user_email_value'));
            exit;
        }

        try {
            $document_server_port_preference = BOL_PreferenceService::getInstance()->findPreference('document_server_port_preference');

            //$apiurl = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . ":".$document_server_port_preference->defaultValue."/api/1/getHTML?apikey=e20a517df87a59751b0f01d708e2cb6496cf6a59717ccfde763360f68a7bfcec&padID=" . explode("/", $clean['noteUrl'])[4];
            $apiurl = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . "/etherpad" . "/api/1/getHTML?apikey=e20a517df87a59751b0f01d708e2cb6496cf6a59717ccfde763360f68a7bfcec&padID=" .$clean['noteUrl'];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiurl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
            $result = json_decode($result);
            if($result->message == "ok")
                echo json_encode(array("status" => "ok", "data" => $result->data->html));
            else
                echo json_encode(array("status" => "error", "message" => "error getting note content"));

        }catch(Exception $e){
            echo json_encode(array("status" => "error", "message" => "error getting note content"));
        }finally{
            exit;
        }
    }

    public function getDatasetById()
    {
        $clean = ODE_CLASS_InputFilter::getInstance()->sanitizeInputs($_REQUEST);
        if ($clean == null){
            OW::getFeedback()->info(OW::getLanguage()->text('cocreation', 'insane_user_email_value'));
            exit;
        }

        header("Access-Control-Allow-Origin: *");
        $dataset = COCREATION_BOL_Service::getInstance()->getDatasetById($clean['id']);

        $dataset->owners = substr($dataset->owners, 1, -1);
        $dataset->owners = str_replace('\\', "", $dataset->owners);
        $users = json_decode($dataset->owners);
        $avatars = array();

        foreach ($users as $user)
        {
            $avatar = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($user));
            $avatars[] = array("src" => $avatar[$user]["src"], "href" => $avatar[$user]["url"], "user" => $avatar[$user]["title"]);
        }

        $room = COCREATION_BOL_Service::getInstance()->getRoomById($dataset->roomId);

        echo json_encode(array('resourceUrl' => OW::getRouter()->urlFor('COCREATION_CTRL_Ajax', 'getDatasetByRoomIdAndVersion') . "?room_id=" . $dataset->roomId . "&version=" . $dataset->version,
            "users"=>$avatars,
            "metas"=>$dataset->metadata,
            "roomName" => $room->name ? $room->name : OW::getLanguage()->text('cocreation', 'deteted_room')));
        exit;
    }

    public function getDatasetByRoomIdAndVersion()
    {
        $clean = ODE_CLASS_InputFilter::getInstance()->sanitizeInputs($_REQUEST);
        if ($clean == null){
            OW::getFeedback()->info(OW::getLanguage()->text('cocreation', 'insane_user_email_value'));
            exit;
        }

        header("Access-Control-Allow-Origin: *");
        $dataset = COCREATION_BOL_Service::getInstance()->getDatasetByRoomIdAndVersion($clean['room_id'], $clean['version']);
        echo json_encode(array("records"=> json_decode($dataset->data), "metadata" => json_decode($dataset->metadata)));
        exit;
    }

    public function getDatasetDocByRoomIdAndVersion()
    {
        $clean = ODE_CLASS_InputFilter::getInstance()->sanitizeInputs($_REQUEST);
        if ($clean == null){
            OW::getFeedback()->info(OW::getLanguage()->text('cocreation', 'insane_user_email_value'));
            exit;
        }

        header("Access-Control-Allow-Origin: *");
        $notes = COCREATION_BOL_Service::getInstance()->getDatasetByRoomIdAndVersion($clean['room_id'], $clean['version'])->notes;
        $notes = json_decode($notes);
        echo $notes->data;
        exit;
    }

    public function getAllDataset()
    {
        $datasets = COCREATION_BOL_Service::getInstance()->getAllDatasets();
        $data = array();

        foreach ($datasets as $dataset)
        {
            /*$dataset->owners = substr($dataset->owners, 1, -1);
            $dataset->owners = str_replace('\\', "", $dataset->owners);
            $users = json_decode($dataset->owners);
            $avatars = array();

            foreach ($users as $user)
            {
                $avatar = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($user));
                $avatars[] = array("src" => $avatar[$user]["src"], "href" => $avatar[$user]["url"], "user" => $avatar[$user]["title"]);
            }
            */
            $room = COCREATION_BOL_Service::getInstance()->getRoomById($dataset->roomId);
            $metadata = json_decode($dataset->metadata);

            if($metadata->title != "")
            {
                $resource_name = $metadata->title;
            }
            else if(count($room) > 0)
            {
                $resource_name = $room->name;
            }
            else
            {
                $resource_name = $dataset->datasetId;
            }

            $data[] = array(
                'name' => $resource_name,
                'id' => $dataset->id,
                'p' => 'SPOD_X',
                'version' => $dataset->version
            );

        }

        header("Access-Control-Allow-Origin: *");
        echo json_encode($data);
        exit;
    }

    public function saveRoomForm()
    {
        if (!OW::getUser()->isAuthenticated())
        {
            try
            {
                $user_id = ODE_CLASS_Tools::getInstance()->getUserFromJWT($_REQUEST['jwt']);
            }
            catch (Exception $e)
            {
                echo json_encode(array("status"  => "ko", "error_message" => $e->getMessage()));
                exit;
            }
        }else{
            $user_id = OW::getUser()->getId();
        }

        try
        {
            COCREATION_BOL_Service::getInstance()->addFormToRoom($_REQUEST['roomId'], $_REQUEST['form_template'], $_REQUEST['form']);
            echo json_encode(array("status" => "ok"));
        }
        catch (Exception $e)
        {
            echo json_encode(array("status" => "ko"));
        }
        finally
        {
            exit;
        }
    }

    public function saveRoomFormSubmission()
    {
        if (!OW::getUser()->isAuthenticated())
        {
            try
            {
                $user_id = ODE_CLASS_Tools::getInstance()->getUserFromJWT($_REQUEST['jwt']);
            }
            catch (Exception $e)
            {
                echo json_encode(array("status"  => "ko", "error_message" => $e->getMessage()));
                exit;
            }
        }else{
            $user_id = OW::getUser()->getId();
        }

        try
        {
            COCREATION_BOL_Service::getInstance()->addFormSubmissionToRoom($_REQUEST['roomId'], $user_id, $_REQUEST['submission'], $_SERVER['REMOTE_ADDR']);

            $url = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . "/ethersheet/addrow/" . $_REQUEST['sheet_name'];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $_REQUEST['submission']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($_REQUEST['submission'])));

            $result = curl_exec($ch);
            curl_close($ch);

            echo json_encode(array("status" => "ok", "resutl" => $result, "url" => $url));
        }
        catch (Exception $e)
        {
            echo json_encode(array("status" => "ko"));
        }
        finally
        {
            exit;
        }
    }

    /* Mobile app service */
    public function getMediaRoomsByUserId()
    {
        /*if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }*/

        $clean = ODE_CLASS_InputFilter::getInstance()->sanitizeInputs($_REQUEST);
        if ($clean == null){
            echo json_encode(array("status" => "error", "massage" => 'Insane inputs detected'));
            exit;
        }

        $user_rooms = array();
        $rooms = COCREATION_BOL_Service::getInstance()->getAllRooms();
        foreach ($rooms as $room) {
            if ((COCREATION_BOL_Service::getInstance()->isMemberJoinedToRoom($clean['userId'], $room->id) ||
                $clean['userId'] == intval($room->ownerId)) && $room->type == "media")
            {
                $avatar = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($room->ownerId))[$room->ownerId];
                $room->ownerImage = $avatar['src'];
                $room->ownerName  = $avatar['title'];
                $room->sheetId = COCREATION_BOL_Service::getInstance()->getSheetByRoomId($room->id)[0]->url;
                array_push($user_rooms, $room);
            }
        }

        header("Access-Control-Allow-Origin: *");
        //echo json_encode(array("status" => "ok", "data" => $user_rooms));
        echo json_encode($user_rooms);
        exit;
    }

    public function getCocreationRoomsByUserId()
    {
        /*if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }*/

        $clean = ODE_CLASS_InputFilter::getInstance()->sanitizeInputs($_REQUEST);
        if ($clean == null){
            echo json_encode(array("status" => "error", "massage" => 'Insane inputs detected'));
            exit;
        }

        $user_rooms = array();
        $rooms = COCREATION_BOL_Service::getInstance()->getAllRooms();
        foreach ($rooms as $room) {
            if ($clean['userId'] == intval($room->ownerId) || COCREATION_BOL_Service::getInstance()->isMemberInvitedToRoom($clean['userId'], $room->id))
            {
                $avatar = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($room->ownerId))[$room->ownerId];
                $room->ownerImage = $avatar['src'];
                $room->ownerName  = $avatar['title'];
                $room->sheetId    = COCREATION_BOL_Service::getInstance()->getSheetByRoomId($room->id)[0]->url;
                $room->docs       = COCREATION_BOL_Service::getInstance()->getDocumentsByRoomId($room->id);
                $room->hasJoined  = COCREATION_BOL_Service::getInstance()->isMemberJoinedToRoom($clean['userId'], $room->id);

                array_push($user_rooms, $room);
            }
        }

        header("Access-Control-Allow-Origin: *");
        echo json_encode($user_rooms);
        exit;
    }

    public function getSheetDataByRoomId(){
        $clean = ODE_CLASS_InputFilter::getInstance()->sanitizeInputs($_REQUEST);
        if ($clean == null){
            echo json_encode(array("status" => "error", "massage" => 'Insane inputs detected'));
            exit;
        }

        $sheetName = COCREATION_BOL_Service::getInstance()->getSheetByRoomId($clean['roomId'])[0]->url;

        header("Access-Control-Allow-Origin: *");
        echo json_encode(COCREATION_BOL_Service::getInstance()->getArrayOfObjectSheetData($sheetName));
        exit;
    }

    public function getUserInfo()
    {
        $clean = ODE_CLASS_InputFilter::getInstance()->sanitizeInputs($_REQUEST);
        if ($clean == null) {
            echo json_encode(array("status" => "error", "massage" => 'Insane inputs detected'));
            exit;
        }

        if (isset($clean['email'])) {
            $user = BOL_UserService::getInstance()->findByEmail($clean['email']);
        }else{
            $user = BOL_UserService::getInstance()->findByUsername($clean['username']);
        }

        $avatar = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($user->id))[$user->id];

        $u = new stdClass();
        $u->id       = $user->id;
        $u->username = $avatar['urlInfo']['vars']['username'];
        $u->name     = $avatar['title'];
        $u->image    = $avatar['src'];

        echo json_encode(array("status" => true, "user" => json_encode($u)));
        exit;

    }

    function initEthersheetMediaRoom($collectionId) {
        try {
            //create the sheet first
            $url = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . "/ethersheet/s/".$collectionId;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);

            //generate the headers for media room
            $apiurl = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . "/ethersheet/mediaroom/init/" . $collectionId;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiurl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: text/html; charset=utf-8; application/x-www-form-urlencoded'
            ));
            curl_setopt($ch, CURLOPT_POSTFIELDS,
                "collection_id=".$collectionId);

            $result = curl_exec($ch);
            curl_close($ch);
            $result = json_decode($result);
            return $result;
        }catch(Exception $e){
            return null;
        }finally{
            return null;
        }
    }

    public function createMediaRoomFromMobile(){
        if (!OW::getUser()->isAuthenticated())
        {
            try
            {
                $user_id = ODE_CLASS_Tools::getInstance()->getUserFromJWT($_REQUEST['jwt']);
            }
            catch (Exception $e)
            {
                echo json_encode(array("status"  => "ko", "error_message" => $e->getMessage()));
                exit;
            }
        }else{
            $user_id = OW::getUser()->getId();
        }

        $clean = ODE_CLASS_InputFilter::getInstance()->sanitizeInputs($_REQUEST);
        if ($clean == null){
            echo json_encode(array("status" => false, "message" => 'Insane inputs detected'));
            exit;
        }
        try{
            $room = COCREATION_BOL_Service::getInstance()->addRoom(
                $clean['ownerId'],
                $clean['name'],
                $clean['subject'],
                $clean['description'],
                $clean['metadata'],
                $clean['data_from'],
                $clean['data_to'],
                $clean['goal'],
                $clean['invitation_text'],
                empty($clean['is_open']) ? 0 : 1,
                implode("#######", $clean['users_value']),
                $clean['room_type']
            );

            $randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 5);


            //create the sheet for the CoCreation Data room
            //Document for notes related to the dataset
            COCREATION_BOL_Service::getInstance()->addDocToRoom($room->id, 1, "notes", "notes_room_"  .$room->id."_".$randomString);
            COCREATION_BOL_Service::getInstance()->addSheetToRoom($room->id, "dataset", "dataset_room_".$room->id."_".$randomString);
            COCREATION_BOL_Service::getInstance()->createMetadataForRoom($room->id);

            $result = $this->initEthersheetMediaRoom("dataset_room_".$room->id."_".$randomString);

//            COCREATION_CLASS_EventHandler::getInstance()->sendNotificationRoomCreated($user_id, $room);

            echo json_encode(array("status" => true, "message" => 'room created'));
            exit;

        }catch (exception $e){
            echo json_encode(array("status" => false, "message" => $e->getMessage()/*'Something went wrong, please check the form values!'*/));
            exit;
        }
    }

    public function getMetadataByRoomId()
    {
        $clean = ODE_CLASS_InputFilter::getInstance()->sanitizeInputs($_REQUEST);
        if ($clean == null) {
            echo json_encode(array("status" => "error", "massage" => 'Insane inputs detected'));
            exit;
        }

        $metadata = COCREATION_BOL_Service::getInstance()->getMetadataByRoomId($clean['roomId']);

        echo json_encode(array("status" => true, "metadata" => json_decode($metadata[0])));
        exit;
    }

    public function getDataletsByRoomId()
    {
        $clean = ODE_CLASS_InputFilter::getInstance()->sanitizeInputs($_REQUEST);
        if ($clean == null) {
            echo json_encode(array("status" => "error", "massage" => 'Insane inputs detected'));
            exit;
        }

        $datalets = COCREATION_BOL_Service::getInstance()->getDataletsByRoomId($clean['roomId']);
        $room_datalets = array();
        foreach($datalets as $d) {
            $datalet = ODE_BOL_Service::getInstance()->getDataletById($d->dataletId);
            $datalet->params = json_decode($datalet->params);
            $datalet->data = str_replace("'", "&#39;", $datalet->data);
            $datalet->fields = str_replace("'", "&#39;", $datalet->fields);

            $datalet_string = "<" . $datalet->component . " datalet-id='" . $datalet->id . "' disable_my_space disable_html_export disable_link";
            foreach ($datalet->params as $key => $value)
                $datalet_string .= " " . $key . "='" .  str_replace("\"", "\\\"", $value ) . "'";
            $datalet_string .= "></" . $datalet->component . ">";

            array_push($room_datalets, $datalet_string);
        }

        echo json_encode(array("status" => true, "datalets" => $room_datalets, "datalets_definition" => ODE_CLASS_Tools::getInstance()->get_all_datalet_definitions()));
        exit;
    }

    public function getAllFriends(){

        $clean = ODE_CLASS_InputFilter::getInstance()->sanitizeInputs($_REQUEST);
        if ($clean == null) {
            echo json_encode(array("status" => "error", "massage" => 'Insane inputs detected'));
            exit;
        }

        $friendsInfo = [];
        $users = BOL_UserService::getInstance()->findList(0,10000);
        $roomMembers = COCREATION_BOL_Service::getInstance()->getRoomMembers($clean['roomId']);

        $roomMembersIds = [];
        foreach ($roomMembers as $roomMember)
        {
            array_walk_recursive
            ($roomMember,
                function($item, $key) use (&$roomMembersIds, $roomMember, $clean)
                {
                    if($key == 'userId')
                    {
                        $roomMembersIds[$roomMember->userId] =
                            (COCREATION_BOL_Service::getInstance()->isMemberJoinedToRoom($roomMember->userId, $clean['roomId']))
                               ? COCREATION_CLASS_Consts::USER_STATUS_JOINED
                               : COCREATION_CLASS_Consts::USER_STATUS_PENDING;
                    }
                }
            );

        }

        foreach($users as $user)
        {
            if($user->id == (int)$clean['userId']) continue;
            $avatar = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($user->id));
            $user = BOL_UserService::getInstance()->findUserById($user->id);

            $friendsInfo[] = array(
                "id"       => $user->id,
                "name"     => filter_var(BOL_UserService::getInstance()->getDisplayName($user->id), FILTER_SANITIZE_SPECIAL_CHARS),
                "username" => $user->username,
                "email"    => $user->email,
                "avatar"   => $avatar[$user->id]["src"],
                "url"      => $avatar[$user->id]["url"],
                "status"   => (isset($roomMembersIds[$user->id])) ? $roomMembersIds[$user->id] : COCREATION_CLASS_Consts::USER_STATUS_NOT_INVITED
            );
        }

        echo json_encode(array("status" => true, "friends" => $friendsInfo));
        exit;
    }

    public function addNewMembersToRoomFromMobile(){

        if (!OW::getUser()->isAuthenticated())
        {
            try
            {
                $user_id = ODE_CLASS_Tools::getInstance()->getUserFromJWT($_REQUEST['jwt']);
            }
            catch (Exception $e)
            {
                echo json_encode(array("status"  => "ko", "error_message" => $e->getMessage()));
                exit;
            }
        }else{
            $user_id = OW::getUser()->getId();
        }

        $clean = ODE_CLASS_InputFilter::getInstance()->sanitizeInputs($_REQUEST);
        if ($clean == null){
            echo json_encode(array("status" => false, "message" => 'Insane inputs detected'));
            exit;
        }

        $room = COCREATION_BOL_Service::getInstance()->getRoomById($clean['roomId']);
        foreach($clean['users'] as $user){
            $u   = BOL_UserService::getInstance()->findByEmail($user);
            COCREATION_BOL_Service::getInstance()->addUserToRoom($room->id, $user, $u->id);
            COCREATION_CLASS_EventHandler::getInstance()->sendNotificationRoomInvitation($user_id, $room, $u->id);
        }


        echo json_encode(array("status" => true, "message" => OW::getLanguage()->text('cocreation', 'feedback_members_add_successful')));
        exit;
    }
}