<?php

class COCREATION_CTRL_DataRoomList extends OW_ActionController
{

    public function index(array $params)
    {
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('cocreation')->getStaticJsUrl() . 'perfect-scrollbar/css/perfect-scrollbar.min.css');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('cocreation')->getStaticJsUrl() . 'perfect-scrollbar/js/perfect-scrollbar.jquery.js');

        $js = UTIL_JsGenerator::composeJsString('
                ODE.ajax_coocreation_get_dataset      = {$ajax_coocreation_get_dataset}
                ODE.ajax_coocreation_get_dataset_docs = {$ajax_coocreation_get_dataset_docs}
            ', array(
            'ajax_coocreation_get_dataset'      => OW::getRouter()->urlFor('COCREATION_CTRL_Ajax', 'getDatasetByRoomIdAndVersion'),
            'ajax_coocreation_get_dataset_docs' => OW::getRouter()->urlFor('COCREATION_CTRL_Ajax', 'getDatasetDocByRoomIdAndVersion'),
        ));
        OW::getDocument()->addOnloadScript($js);
        
        $this->assign('datasets', $this->formatDatasetData());
        $this->assign('components_url', SPODPR_COMPONENTS_URL);
        $this->assign('language', BOL_LanguageService::getInstance()->getCurrent()->tag);
    }

    public function formatDatasetData()
    {
        $raw_data = COCREATION_BOL_Service::getInstance()->getAllDatasets();
        $dataset = array();

        foreach ($raw_data as $data)
        {
            $data->owners = substr($data->owners, 1, -1);
            $data->owners = str_replace('\\', "", $data->owners);
            $users = json_decode($data->owners);
            $avatars = array();

            foreach ($users as $user)
            {
                $avatar = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($user));
                $avatars[] = array("src" => $avatar[$user]["src"], "href" => $avatar[$user]["url"], "isOwner" => (($user == $data->ownerId) ? true : false));
            }

            $room = COCREATION_BOL_Service::getInstance()->getRoomById($data->roomId);
            $metadata = json_decode($data->metadata);


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
                $resource_name = $data->datasetId;
            }

            $dataset[] = array("ID" => $data->roomId,
                               "VER" => $data->version,
                               "USER" => $avatars,
                               "NAME" =>  str_replace("'","ˈ",$resource_name),
                               "DATA" => date('d/m/Y', strtotime($data->timeStamp)),
                               "DESCRIPTION" => !empty($metadata->description) ? str_replace("'","ˈ", $metadata->description) : ''
                );
        }

        return json_encode($dataset);
    }
}