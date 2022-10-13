<?php

class COCREATION_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    const PREF_POCKAN_PLATFORM_URL = "ckan_platform_url";
    const PREF_POCKAN_API_KEY = "ckan_api_key";
    const PREF_POCKAN_DEF_ORGANISATION = "ckan_def_organisation";
    const PREF_POCKAN_DEF_GROUPS = "ckan_def_groups";

    public function settings($params)
    {
        $this->assign('components_url', SPODPR_COMPONENTS_URL);

        $settingsItem = new BASE_MenuItem();
        $settingsItem->setLabel('SETTINGS');
        $settingsItem->setUrl(OW::getRouter()->urlForRoute('cocreation-settings'));
        $settingsItem->setKey('settings');
        $settingsItem->setIconClass( 'ow_ic_gear_wheel' );
        $settingsItem->setOrder( 0 );

        $providersItem = new BASE_MenuItem();
        $providersItem->setLabel('ANALYSIS');
        $providersItem->setUrl(OW::getRouter()->urlForRoute('cocreation-analysis'));
        $providersItem->setKey('analysis');
        $providersItem->setOrder(1);

        $menu = new BASE_CMP_ContentMenu( array( $settingsItem, $providersItem ) );
        $this->addComponent( 'menu', $menu );

        $this->setPageTitle("COCREATION SETTINGS");
        $this->setPageHeading("COCREATION SETTINGS");

        $form = new Form('settings');
        $this->addForm($form);

        $submit = new Submit('add');

        $document_room_field      = new HiddenField('knowledge_room_status');
        $document_room_field->setId("knowledge_room_status");
        $spreadsheet_room_field   = new HiddenField('dataset_room_status');
        $spreadsheet_room_field->setId("dataset_room_status");

        $document_server_port_preference = BOL_PreferenceService::getInstance()->findPreference('document_server_port_preference');
        if(empty($document_server_port_preference) || $document_server_port_preference->defaultValue == 9001) {
            $this->assign('document_server_port_preference', 9001);
        }else{
            $this->assign('document_server_port_preference', $document_server_port_preference->defaultValue);
        }

        $document_server_port_field    = new TextField('document_server_port');
        $document_server_port_field->setId('document_server_port');
        $document_server_port_field->setValue(empty($document_server_port_preference->defaultValue) ? 9001 : $document_server_port_preference->defaultValue);

        $spreadsheet_server_port_preference = BOL_PreferenceService::getInstance()->findPreference('spreadsheet_server_port_preference');
        if(empty($spreadsheet_server_port_preference) || $spreadsheet_server_port_preference->defaultValue == 8001) {
            $this->assign('spreadsheet_server_port_preference', 8001);
        }else{
            $this->assign('spreadsheet_server_port_preference', $spreadsheet_server_port_preference->defaultValue);
        }

        $spreadsheet_server_port_field = new TextField('spreadsheet_server_port');
        $spreadsheet_server_port_field->setId('spreadsheet_server_port');
        $spreadsheet_server_port_field->setValue(empty($spreadsheet_server_port_preference->defaultValue) ? 8001 : $spreadsheet_server_port_preference->defaultValue);

        $form->addElement($document_room_field);
        $form->addElement($spreadsheet_room_field);

        $form->addElement($document_server_port_field);
        $form->addElement($spreadsheet_server_port_field);

        //Set knowledge and dataset room toggle button status based on saved preferences
        $knowledge_room_status_preference = BOL_PreferenceService::getInstance()->findPreference('knowledge_room_status_preference');
        if(empty($knowledge_room_status_preference) || $knowledge_room_status_preference->defaultValue == "false") {
            $this->assign('knowledge_room_status', false);
            $document_room_field->setValue("false");
        }else{
            $this->assign('knowledge_room_status', true);
            $document_room_field->setValue($knowledge_room_status_preference->defaultValue);
        }

        $dataset_room_status_preference = BOL_PreferenceService::getInstance()->findPreference('dataset_room_status_preference');
        if(empty($dataset_room_status_preference) || $dataset_room_status_preference->defaultValue == "false") {
            $this->assign('dataset_room_status', false);
            $spreadsheet_room_field->setValue("false");
        }else{
            $this->assign('dataset_room_status', true);
            $spreadsheet_room_field->setValue($dataset_room_status_preference->defaultValue);
        }

        //PUBLISH ON CKAN - CKAN URL.
        $txtCKANPlatformURL = new TextField($this::PREF_POCKAN_PLATFORM_URL);
        $preference = BOL_PreferenceService::getInstance()->findPreference($this::PREF_POCKAN_PLATFORM_URL);
        $ckan_platform_url_value = empty($preference) ? '' : $preference->defaultValue;
        $txtCKANPlatformURL->setValue($ckan_platform_url_value);
        $form->addElement($txtCKANPlatformURL);

        //PUBLISH ON CKAN - key.
        $txtCKANApiKey = new TextField($this::PREF_POCKAN_API_KEY);
        $preference = BOL_PreferenceService::getInstance()->findPreference($this::PREF_POCKAN_API_KEY);
        $ckan_api_key_value = empty($preference) ? '' : $preference->defaultValue;
        $txtCKANApiKey->setValue($ckan_api_key_value);
        $form->addElement($txtCKANApiKey);

        //PUBLISH ON CKAN - DEFAULT ORGANISATION
        $txtCKANDefOrganisation = new TextField($this::PREF_POCKAN_DEF_ORGANISATION);
        $preference = BOL_PreferenceService::getInstance()->findPreference($this::PREF_POCKAN_DEF_ORGANISATION);
        $ckan_def_organisation_value = empty($preference) ? '' : $preference->defaultValue;
        $txtCKANDefOrganisation->setValue($ckan_def_organisation_value);
        $form->addElement($txtCKANDefOrganisation);

        //PUBLISH ON CKAN - DEFAULT GROUPS
        $txtCKANDefGroups = new TextField($this::PREF_POCKAN_DEF_GROUPS);
        $preference = BOL_PreferenceService::getInstance()->findPreference($this::PREF_POCKAN_DEF_GROUPS);
        $ckan_def_groups_value = empty($preference) ? '' : $preference->defaultValue;
        $txtCKANDefGroups->setValue($ckan_def_groups_value);
        $form->addElement($txtCKANDefGroups);

        $submit->setValue('SAVE');
        $form->addElement($submit);

        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $data = $form->getValues();

            //DOCUMENT SERVER PORT
            $document_server_port_preference = BOL_PreferenceService::getInstance()->findPreference('document_server_port_preference');
            if(empty($document_server_port_preference)) {
                $document_server_port_preference = new BOL_Preference();
            }
            $document_server_port_preference->defaultValue = $data['document_server_port'];
            $document_server_port_preference->key = 'document_server_port_preference';
            $document_server_port_preference->sortOrder = 1;
            $document_server_port_preference->sectionName = 'general';
            BOL_PreferenceService::getInstance()->savePreference($document_server_port_preference);
            $this->assign('document_server_port_preference', $document_server_port_preference->defaultValue);

            //SPREADSHEET SERVER PORT
            $spreadsheet_server_port_preference = BOL_PreferenceService::getInstance()->findPreference('spreadsheet_server_port_preference');
            if(empty($spreadsheet_server_port_preference)) {
                $spreadsheet_server_port_preference = new BOL_Preference();
            }
            $spreadsheet_server_port_preference->defaultValue = $data['spreadsheet_server_port'];
            $spreadsheet_server_port_preference->key = 'spreadsheet_server_port_preference';
            $spreadsheet_server_port_preference->sortOrder = 1;
            $spreadsheet_server_port_preference->sectionName = 'general';
            BOL_PreferenceService::getInstance()->savePreference($spreadsheet_server_port_preference);
            $this->assign('spreadsheet_server_port_preference', $spreadsheet_server_port_preference->defaultValue);

            //KNOWLEDGE ROOM PREFERENCES
            $knowledge_room_status_preference = BOL_PreferenceService::getInstance()->findPreference('knowledge_room_status_preference');
            if(empty($knowledge_room_status_preference)) {
                $knowledge_room_status_preference = new BOL_Preference();
            }

            $knowledge_room_status_preference->key = 'knowledge_room_status_preference';
            $knowledge_room_status_preference->sortOrder = 3;
            $knowledge_room_status_preference->sectionName = 'general';

            $this->assign('knowledge_room_status', $data['knowledge_room_status'] == "true" ? true : false);
            $document_room_field->setValue($data['knowledge_room_status']);
            $knowledge_room_status_preference->defaultValue = $data['knowledge_room_status'];

            BOL_PreferenceService::getInstance()->savePreference($knowledge_room_status_preference);

            //DATASET ROOM PREFERENCES
            $dataset_room_status_preference = BOL_PreferenceService::getInstance()->findPreference('dataset_room_status_preference');
            if(empty($dataset_room_status_preference)) {
                $dataset_room_status_preference = new BOL_Preference();
            }

            $dataset_room_status_preference->key = 'dataset_room_status_preference';
            $dataset_room_status_preference->sortOrder = 4;
            $dataset_room_status_preference->sectionName = 'general';

            $this->assign('dataset_room_status', $data['dataset_room_status'] == "true" ? true : false);
            $spreadsheet_room_field->setValue($data['dataset_room_status']);
            $dataset_room_status_preference->defaultValue = $data['dataset_room_status'];

            BOL_PreferenceService::getInstance()->savePreference($dataset_room_status_preference);

            //PUBLISH ON CKAN: save the CKAN url.
            $ckan_platform_url_preference = BOL_PreferenceService::getInstance()->findPreference($this::PREF_POCKAN_PLATFORM_URL);
            if (empty($ckan_platform_url_preference)) $ckan_platform_url_preference = new BOL_Preference();

            $ckan_platform_url_preference->key = $this::PREF_POCKAN_PLATFORM_URL;
            $ckan_platform_url_preference->sortOrder = 5;
            $ckan_platform_url_preference->sectionName = 'general';
            $ckan_platform_url_preference->defaultValue = $data[$this::PREF_POCKAN_PLATFORM_URL];
            BOL_PreferenceService::getInstance()->savePreference($ckan_platform_url_preference);

            //PUBLISH ON CKAN: save the CKAN key.
            $ckan_api_key_preference = BOL_PreferenceService::getInstance()->findPreference($this::PREF_POCKAN_API_KEY);
            if (empty($ckan_api_key_preference)) $ckan_api_key_preference = new BOL_Preference();

            $ckan_api_key_preference->key = $this::PREF_POCKAN_API_KEY;
            $ckan_api_key_preference->sortOrder = 6;
            $ckan_api_key_preference->sectionName = 'general';
            $ckan_api_key_preference->defaultValue = $data[$this::PREF_POCKAN_API_KEY];
            BOL_PreferenceService::getInstance()->savePreference($ckan_api_key_preference);

            //PUBLISH ON CKAN: save the default organisation.
            $ckan_def_organisation_preference = BOL_PreferenceService::getInstance()->findPreference($this::PREF_POCKAN_DEF_ORGANISATION);
            if (empty($ckan_def_organisation_preference)) $ckan_def_organisation_preference = new BOL_Preference();

            $ckan_def_organisation_preference->key = $this::PREF_POCKAN_DEF_ORGANISATION;
            $ckan_def_organisation_preference->sortOrder = 7;
            $ckan_def_organisation_preference->sectionName = 'general';
            $ckan_def_organisation_preference->defaultValue = $data[$this::PREF_POCKAN_DEF_ORGANISATION];
            BOL_PreferenceService::getInstance()->savePreference($ckan_def_organisation_preference);

            //PUBLISH ON CKAN: save the default groups.
            $ckan_def_groups_preference = BOL_PreferenceService::getInstance()->findPreference($this::PREF_POCKAN_DEF_GROUPS);
            if (empty($ckan_def_groups_preference)) $ckan_def_groups_preference = new BOL_Preference();

            $ckan_def_groups_preference->key = $this::PREF_POCKAN_DEF_ORGANISATION;
            $ckan_def_groups_preference->sortOrder = 7;
            $ckan_def_groups_preference->sectionName = 'general';
            $ckan_def_groups_preference->defaultValue = $data[$this::PREF_POCKAN_DEF_GROUPS];
            BOL_PreferenceService::getInstance()->savePreference($ckan_def_groups_preference);
        }
    }

    public function analysis($params){
        $settingsItem = new BASE_MenuItem();
        $settingsItem->setLabel('SETTINGS');
        $settingsItem->setUrl(OW::getRouter()->urlForRoute('cocreation-settings'));
        $settingsItem->setKey('settings');
        $settingsItem->setIconClass( 'ow_ic_gear_wheel' );
        $settingsItem->setOrder( 0 );

        $providersItem = new BASE_MenuItem();
        $providersItem->setLabel('ANALYSIS');
        $providersItem->setUrl(OW::getRouter()->urlForRoute('cocreation-analysis'));
        $providersItem->setKey('analysis');
        $providersItem->setOrder(1);

        $menu = new BASE_CMP_ContentMenu( array( $settingsItem, $providersItem ) );
        $this->addComponent( 'menu', $menu );

        $exportUrl = OW::getRouter()->urlFor(__CLASS__, 'export');
        $this->assign('exportUrl', $exportUrl);

        $this->assign('cocreation_rooms', COCREATION_BOL_Service::getInstance()->getAllRooms());

        $this->assign('data_room_url',      str_replace("index/", "", OW::getRouter()->urlFor( 'COCREATION_CTRL_DataRoom'      , 'index')));
        $this->assign('knowledge_room_url', str_replace("index/", "", OW::getRouter()->urlFor( 'COCREATION_CTRL_KnowledgeRoom' , 'index')));


    }

    public function export()
    {
        require_once OW::getPluginManager()->getPlugin('spodagoraexporter')->getRootDir() . 'libs/PHPExcel-1.8/Classes/PHPExcel.php';
        $objPHPExcel = new PHPExcel();

        $level = 'A';

        $ex = new OW_Example();
        $ex->andFieldEqual('entityId', $_REQUEST["id"]);
        $comments = SPODDISCUSSION_BOL_DiscussionCommentDao::getInstance()->findListByExample($ex);

        $objPHPExcel->getProperties()->setCreator("ROUTETOPA Project")
            ->setLastModifiedBy("ROUTETOPA Project")
            ->setTitle("Cocreation Snapshot")
            ->setSubject("Cocreation Snapshot")
            ->setDescription("Cocreation Snapshot")
            ->setKeywords("Cocreation Snapshot")
            ->setCategory("Cocreation Snapshot");

        foreach ($comments as $row => $comment)
        {
            $user = BOL_UserService::getInstance()->findUserById($comment->ownerId);
            $cell = $level . ($row+1);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell, $user->username . " : " . $comment->comment . " (".$comment->timestamp.")");
        }

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

        // We'll be outputting an excel file
        header('Content-type: application/vnd.ms-excel');
        // It will be called file.xlsx
        header('Content-Disposition: attachment; filename="cocreation_room.xlsx"');
        // Write file to the browser
        $objWriter->save('php://output');
        die();
    }

    function liveExecuteCommand($cmd)
    {

        while (@ ob_end_flush()); // end all output buffers if any

        $proc = popen("$cmd 2>&1 ; echo Exit status : $?", 'r');

        $live_output     = "";
        $complete_output = "";

        while (!feof($proc))
        {
            $live_output     = fread($proc, 4096);
            $complete_output = $complete_output . $live_output;
            echo "$live_output";
            @ flush();
        }

        pclose($proc);

        // get exit status
        preg_match('/[0-9]+$/', $complete_output, $matches);

        // return exit status and intended output
        return array (
            'exit_status'  => $matches[0],
            'output'       => str_replace("Exit status : " . $matches[0], '', $complete_output)
        );
    }

}