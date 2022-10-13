<?php

class COCREATION_BOL_Service
{
    const ROOM_ENTITY_TYPE    = 'cocreation_room_entity';
    const COMMENT_ENTITY_TYPE = 'cocreation_comment_entity';

    private static $classInstance;
    private $sheetDBconnection;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
        $this->sheetDBconnection = new PDO("mysql:host=".OW_DB_HOST.";dbname=ethersheet;",
                                           OW_DB_USER,
                                           OW_DB_PASSWORD,
                                           array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8;',
                                                 PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true)
        );
    }

    //SHEET
    public function getSheetData($sheetName){
        $data = array();
        try {
            $stmt = $this->sheetDBconnection->query("SELECT * FROM store WHERE store.key LIKE '%" . $sheetName . "%'");
            if(!$stmt) return $data;
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($result)) return $data;
            $result = json_decode($result[count($result) - 1]['value']);

            if(count($result) == 0) return $data;

            $stmt = $this->sheetDBconnection->query("SELECT * FROM store WHERE store.key LIKE '" . $result[0] . ":rows'");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $rows = json_decode($rows[0]['value'], true);

            $stmt = $this->sheetDBconnection->query("SELECT * FROM store WHERE store.key LIKE '" . $result[0] . ":cols'");
            $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $cols = json_decode($cols[0]['value'], true);

            $stmt  = $this->sheetDBconnection->query("SELECT * FROM store WHERE store.key LIKE '" . $result[0] . ":cells'");
            $cells = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($cells[0]['value'] == "{}") return $data;
            $cells = json_decode($cells[0]['value'], true);

            foreach($cols as $col){
                if(!array_key_exists($rows[0], $cells)) break;
                if($cells[$rows[0]] || $cells[$rows[0]][$col]['value'] == "") break;
                $obj = new stdClass();
                $obj->name =  $cells[$rows[0]][$col]['value'];//filter_var(str_replace('"',"",$cells[$rows[0]][$col]['value']), FILTER_SANITIZE_STRING);
                $obj->data = array();
                array_push($data, $obj);
            }

            for($i = 1; $i < $rows; $i++){
                $wrong_values = 0;
                for($j = 0; $j < count($data); $j++){
                    if($cells[$rows[$i]][$cols[$j]]['value'] == "") {
                        array_push($data[$j]->data , "");
                        $wrong_values++; continue;
                    };
                    if($cells[$rows[$i]][$cols[$j]]['type'] == 'string')
                        array_push($data[$j]->data,$cells[$rows[$i]][$cols[$j]]['value']);// filter_var(str_replace('"',"",$cells[$rows[$i]][$cols[$j]]['value']), FILTER_SANITIZE_STRING));
                    else
                        array_push($data[$j]->data, floatval($cells[$rows[$i]][$cols[$j]]['value']));
                }
                if($wrong_values == count($data)) break;
            }
        }catch (PDOException $e){
            return null;
        }
        return $data;
    }


    public function getArrayOfObjectSheetData($sheetName){
        $data = array();
        try {
            $stmt = $this->sheetDBconnection->query("SELECT * FROM store WHERE store.key LIKE '%" . $sheetName . "%'");
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $result = json_decode($result[count($result) - 1]['value']);

            if(count($result) == 0) return $data;

            $stmt = $this->sheetDBconnection->query("SELECT * FROM store WHERE store.key LIKE '" . $result[0] . ":rows'");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $rows = json_decode($rows[0]['value'], true);

            $stmt = $this->sheetDBconnection->query("SELECT * FROM store WHERE store.key LIKE '" . $result[0] . ":cols'");
            $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $cols = json_decode($cols[0]['value'], true);

            $stmt  = $this->sheetDBconnection->query("SELECT * FROM store WHERE store.key LIKE '" . $result[0] . ":cells'");
            $cells = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $cells = json_decode($cells[0]['value'], true);

            $headers = array();
            foreach($cols as $col){
                if(!isset($cells[$rows[0]][$col]) || $cells[$rows[0]][$col]['value'] == "") break;
                //array_push($headers, filter_var(str_replace('"',"",$cells[$rows[0]][$col]['value']), FILTER_SANITIZE_STRING));
                array_push($headers, trim(str_replace("`","ˈ",str_replace("'","ˈ",$cells[$rows[0]][$col]['value']))));
                //array_push($headers, $cells[$rows[0]][$col]['value']);
            }

            for($i = 1; $i < count($cells); $i++){
                $wrong_values = 0;
                $obj = new stdClass();
                for($j = 0; $j < count($headers); $j++){
                    if(!isset($cells[$rows[$i]][$cols[$j]]) || $cells[$rows[$i]][$cols[$j]]['value'] == "") {
                        $obj->{$headers[$j]} = "";
                        $wrong_values++; continue;
                    }
                    if($cells[$rows[$i]][$cols[$j]]['type'] == 'string')
                        $obj->{$headers[$j]} = trim($cells[$rows[$i]][$cols[$j]]['value']);//filter_var(str_replace('"',"",$cells[$rows[$i]][$cols[$j]]['value']), FILTER_SANITIZE_STRING);
                    else
                        $obj->{$headers[$j]} = floatval($cells[$rows[$i]][$cols[$j]]['value']);
                }
                if($wrong_values == count($headers)){ $wrong_values = 0; continue;}
                array_push($data, $obj);
            }
        }catch (PDOException $e){
            return null;
        }
        return $data;
    }

    public function addSheetToRoom($roomId, $description, $url){
        $roomSheet = new COCREATION_BOL_RoomSheet();

        $roomSheet->roomId      = $roomId;
        $roomSheet->description = $description;
        $roomSheet->url         = $url;

        COCREATION_BOL_RoomSheetDao::getInstance()->save($roomSheet);
    }

    public function getSheetByRoomId($roomId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('roomId', $roomId);
        $result = COCREATION_BOL_RoomSheetDao::getInstance()->findListByExample($example);
        return $result;
    }

    //METADATA

    public function createMetadataForRoom($roomId){
        $roomMetadata = new COCREATION_BOL_RoomMetadata();

        $roomMetadata->roomId   = $roomId;
        $roomMetadata->metadata = '';

        COCREATION_BOL_RoomMetadataDao::getInstance()->save($roomMetadata);
    }

    public function updateMetadata($roomId, $metadata)
    {
        $example = new OW_Example();
        $example->andFieldEqual('roomId', $roomId);
        $room_meta_data = COCREATION_BOL_RoomMetadataDao::getInstance()->findObjectByExample($example);

        $room_meta_data->metadata = $metadata;

        try
        {
            COCREATION_BOL_RoomMetadataDao::getInstance()->save($room_meta_data);
            return true;
        }
        catch (Exception $ex)
        {
            return false;
        }
    }

    public function getMetadataByRoomId($roomId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('roomId', $roomId);
        $result = COCREATION_BOL_RoomMetadataDao::getInstance()->findObjectByExample($example);
        return $result;
    }

    public function getFormByRoomId($roomId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('roomId', $roomId);
        $result = COCREATION_BOL_RoomFormDao::getInstance()->findObjectByExample($example);
        return $result;
    }

    public function deleteMetadataFromRoom($roomId){
        $example = new OW_Example();
        $example->andFieldEqual('roomId', $roomId);
        COCREATION_BOL_RoomMetadataDao::getInstance()->deleteByExample($example);
    }

    //TEMPLATES

    public function addTemplate($name, $description, $url)
    {
        $template = new COCREATION_BOL_Template();

        $template->name        = $name;
        $template->description = $description;
        $template->url         = $url;

        COCREATION_BOL_TemplateDao::getInstance()->save($template);
    }

    public function getAllTemplates()
    {
        return COCREATION_BOL_TemplateDao::getInstance()->findAll();
    }

    public function removeTemplate($id)
    {
        COCREATION_BOL_TemplateDao::getInstance()->deleteById($id);
    }

    //ROOMS

    public function addRoom($ownerId, $name, $subject,
                            $description, $metadata, $from, $to,
                            $goal, $invitationText, $isOpen,
                            $invitedUserArray, $roomType)
    {

        $room = new COCREATION_BOL_Room();

        $room->ownerId        = $ownerId;
        $room->name           = $name;
        $room->subject        = $subject;
        $room->description    = $description;
        $room->metadata       = $metadata;
        $room->from           = $from;
        $room->to             = $to;
        $room->goal           = $goal;
        $room->invitationText = $invitationText;
        $room->isOpen         = $isOpen;
        $room->type           = $roomType;

        COCREATION_BOL_RoomDao::getInstance()->save($room);

        foreach($invitedUserArray as $user)
        {
            $u   = BOL_UserService::getInstance()->findByEmail($user);
            if($u->id != NULL) $this->addUserToRoom($room->id, $user, $u->id);
        }

        return $room;

        //$this->addDocToRoom($room->id, $templateId);
    }

    public function getAllRooms()
    {
        return COCREATION_BOL_RoomDao::getInstance()->findAll();
    }

    public function getAllRoomOrderedByDate(){
        //daodaodao
        $example = new OW_Example();
        $example->setOrder('`timestamp` DESC');
        return COCREATION_BOL_RoomDao::getInstance()->findListByExample($example);
    }

    public function getRoomById($id){
        $example = new OW_Example();
        $example->andFieldEqual('id', $id);
        $result = COCREATION_BOL_RoomDao::getInstance()->findObjectByExample($example);
        return $result;
    }

    public function deleteRoomById($roomId){

        COCREATION_BOL_RoomDao::getInstance()->deleteById($roomId);
        $this->deleteAllDataletsFromRoom($roomId);
        $this->deleteAllPostitsFromRoom($roomId);
        $this->deleteDatasetsFromRoom($roomId);
        $this->deleteMetadataFromRoom($roomId);
        $this->deleteMembersFromRoom($roomId);
    }

    // FORM

    public function addFormToRoom($roomId, $formTemplate, $form)

    {
        $ex = new OW_Example();
        $ex->andFieldEqual('roomId', $roomId);

        $roomForm = COCREATION_BOL_RoomFormDao::getInstance()->findObjectByExample($ex);

        if(empty($roomForm))
            $roomForm = new COCREATION_BOL_RoomForm();

        $roomForm->roomId       = $roomId;
        $roomForm->formTemplate = $formTemplate;
        $roomForm->form         = $form;

        COCREATION_BOL_RoomFormDao::getInstance()->save($roomForm);
    }

    public function addFormSubmissionToRoom($roomId, $userId, $submission, $ip)
    {
       $roomForm = new COCREATION_BOL_RoomFormSubmission();

        $roomForm->roomId     = $roomId;
        $roomForm->userId     = $userId;
        $roomForm->submission = $submission;
        $roomForm->ip         = $ip;

        COCREATION_BOL_RoomFormSubmissionDao::getInstance()->save($roomForm);
    }

    //USER AND MEMBER

    public function addUserToRoom($roomId, $email, $userId, $isJoined = 0, $role = 1)
    {
        $roomMember = new COCREATION_BOL_RoomMember();

        $roomMember->roomId   = $roomId;
        $roomMember->email    = $email;
        $roomMember->isJoined = $isJoined;
        $roomMember->role     = $role;
        $roomMember->userId   = $userId;

        COCREATION_BOL_RoomMemberDao::getInstance()->save($roomMember);
    }

    public function deleteMembersFromRoom($userId){
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        COCREATION_BOL_RoomMemberDao::getInstance()->deleteByExample($example);
    }

    public function getRoomMembers($roomId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('roomId', $roomId);
        $result = COCREATION_BOL_RoomMemberDao::getInstance()->findListByExample($example);
        return $result;
    }

    public function memberJoinToRoom($memberId, $roomId){
        COCREATION_BOL_RoomMemberDao::getInstance()->updateJoin($memberId, $roomId);
    }

    public function isMemberInvitedToRoom($memberId, $roomId) {
        $example = new OW_Example();
        $example->andFieldEqual('userId', intval($memberId));
        $example->andFieldEqual('roomId', intval($roomId));
        $result = COCREATION_BOL_RoomMemberDao::getInstance()->findListByExample($example);
        if(count($result) == 0) return false;
        else return true;
    }

    public function isMemberJoinedToRoom($memberId, $roomId){
        $example = new OW_Example();
        $example->andFieldEqual('userId', intval($memberId));
        $example->andFieldEqual('roomId', intval($roomId));
        $result = COCREATION_BOL_RoomMemberDao::getInstance()->findListByExample($example);
        if(count($result) == 0) return false;
        return ($result[0]->isJoined == "1") ? true : false;
    }

    //DOCUMENTS

    public function addDocToRoom($roomId, $templateId, $description, $url)
    {
        $roomDoc = new COCREATION_BOL_RoomDoc();

        $roomDoc->roomId      = $roomId;
        $roomDoc->description = $description;
        $roomDoc->url         = $url;
        $roomDoc->templateId  = $templateId;

        COCREATION_BOL_RoomDocDao::getInstance()->save($roomDoc);
    }

    public function getAllDocuments()
    {
        return COCREATION_BOL_RoomDocDao::getInstance()->findAll();
    }

    public function getDocumentsByRoomId($roomId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('roomId', $roomId);
        $result = COCREATION_BOL_RoomDocDao::getInstance()->findListByExample($example);
        return $result;
    }

    //DATALETS

    public function addDataletToRoom($roomId, $dataletId){
        $datalet = new COCREATION_BOL_RoomDatalet();

        $datalet->roomId    = $roomId;
        $datalet->dataletId = $dataletId;

        COCREATION_BOL_RoomDataletDao::getInstance()->save($datalet);

    }

    public function deleteDataletFromRoom($roomId, $dataletId ){
        $ex = new OW_Example();
        $ex->andFieldEqual('roomId', $roomId);
        $ex->andFieldEqual('dataletId', $dataletId);

        if(!empty($dataletId))
        {
            $e = new OW_Example();
            $e->andFieldEqual('id', $dataletId);
            ODE_BOL_DataletDao::getInstance()->deleteByExample($e);
        }

        COCREATION_BOL_RoomDataletDao::getInstance()->deleteByExample($ex);
        $this->deletePostitsFromDatalet($dataletId);
    }

    public function deleteAllDataletsFromRoom($roomId){

        $datalets = $this->getDataletsByRoomId($roomId);

        if(count($datalets) > 0){
            foreach($datalets as $datalet) {
                $e = new OW_Example();
                $e->andFieldEqual('id', $datalet->dataletId);
                ODE_BOL_DataletDao::getInstance()->deleteByExample($e);
            }
        }

        $ex = new OW_Example();
        $ex->andFieldEqual('roomId', $roomId);
        COCREATION_BOL_RoomDataletDao::getInstance()->deleteByExample($ex);

    }

    public function getDataletsByRoomId($roomId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('roomId', $roomId);
        $example->setOrder("dataletId");
        $result = COCREATION_BOL_RoomDataletDao::getInstance()->findListByExample($example);
        return $result;
    }

    //DATASETS

    public function addDatasetToRoom($roomId, $url, $name, $description, $fields){
        $dataset = new COCREATION_BOL_RoomDataset();

        $dataset->roomId      = $roomId;
        $dataset->url         = $url;
        $dataset->name        = $name;
        $dataset->description = $description;
        $dataset->fields      = $fields;

        COCREATION_BOL_RoomDatasetDao::getInstance()->save($dataset);
    }

    public function getDatasetsByRoomId($roomId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('roomId', $roomId);
        $result = COCREATION_BOL_RoomDatasetDao::getInstance()->findListByExample($example);
        return $result;
    }

    public function deleteDatasetsFromRoom($roomId){
        $example = new OW_Example();
        $example->andFieldEqual('roomId', $roomId);
        COCREATION_BOL_RoomDatasetDao::getInstance()->deleteByExample($example);
    }

    //POSTITS

    public function addPostitToDataletInRoom($roomId, $dataletId, $title, $content){

        $postit = new COCREATION_BOL_RoomPostit();

        $postit->roomId      = $roomId;
        $postit->dataletId   = $dataletId;
        $postit->title       = htmlspecialchars($title, ENT_QUOTES);
        $postit->content     = htmlspecialchars($content, ENT_QUOTES);

        COCREATION_BOL_RoomPostitDao::getInstance()->save($postit);
    }

    public function getPostitByDataletId($dataletId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('dataletId', $dataletId);
        $result = COCREATION_BOL_RoomPostitDao::getInstance()->findListByExample($example);
        for($i=0;$i < count($result);$i++) $result[$i]->content = htmlspecialchars($result[$i]->content);
        return $result;
    }

    public function deletePostitsFromDatalet($dataletId){
        $example = new OW_Example();
        $example->andFieldEqual('dataletId', $dataletId);
        COCREATION_BOL_RoomPostitDao::getInstance()->deleteByExample($example);
    }

    public function deleteAllPostitsFromRoom($roomId){
        $example = new OW_Example();
        $example->andFieldEqual('roomId', $roomId);
        COCREATION_BOL_RoomPostitDao::getInstance()->deleteByExample($example);
    }

    //COCREATION DATASETS

    public function addDataset($roomId,
                               $owners,
                               $datasetId,
                               $data,
                               $notes,
                               $metadata)
    {
        //get last version and up it
        $version = 1;
        $result = $this->getDatasetsByDatasetId($datasetId);
        if(count($result) > 0){
            $version = $result[count($result) - 1]->version + 1;
        }

        $dataset = new COCREATION_BOL_Dataset();

        $dataset->roomId                             = $roomId;
        $dataset->owners                             = json_encode($owners);
        $dataset->datasetId                          = $datasetId;
        $dataset->version                            = $version;
        $dataset->data                               = $data;
        $dataset->notes                              = $notes;
        $dataset->metadata                           = json_encode($metadata);

        COCREATION_BOL_DatasetDao::getInstance()->save($dataset);
    }

    public function getAllDatasets()
    {
        $sql = "SELECT t1.id, t1.owners, t1.roomId, t1.datasetId, t2.ownerId, t1.metadata, t1.version, t1.timeStamp
                FROM ". OW_DB_PREFIX ."cocreation_dataset as t1 LEFT JOIN ". OW_DB_PREFIX ."cocreation_room as t2 ON t2.id = t1.roomId 
                ORDER BY roomId DESC, version DESC;";
        return OW::getDbo()->queryForObjectList($sql, 'COCREATION_BOL_Dataset');
    }

//    public function getAllDatasets()
//    {
//        $sql = "SELECT * FROM ". OW_DB_PREFIX ."cocreation_dataset order by roomId DESC, version DESC";
//        return OW::getDbo()->queryForObjectList($sql, 'COCREATION_BOL_Dataset');
//    }

    public function getDatasetById($datasetId)
    {
        return COCREATION_BOL_DatasetDao::getInstance()->findById($datasetId);
    }

    public function getDatasetByRoomIdAndVersion($roomId, $version)
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('roomId', $roomId);
        $ex->andFieldEqual('version', $version);
        return COCREATION_BOL_DatasetDao::getInstance()->findObjectByExample($ex);
    }

    public function getDatasetsByDatasetId($datasetId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('datasetId', $datasetId);
        $result = COCREATION_BOL_DatasetDao::getInstance()->findListByExample($example);
        return $result;
    }
}
