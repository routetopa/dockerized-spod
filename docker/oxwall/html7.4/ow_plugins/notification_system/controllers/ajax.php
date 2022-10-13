<?php

class SPODNOTIFICATION_CTRL_Ajax extends OW_ActionController
{
    public function addUserRegistrationId(){
        $clean = ODE_CLASS_InputFilter::getInstance()->sanitizeInputs($_REQUEST);
        if ($clean == null){
            echo json_encode(array("status" => false, "message" => 'Insane inputs detected'));
            exit;
        }
        try{
             SPODNOTIFICATION_BOL_Service::getInstance()->addUserRegistrationId(
                $clean['userId'],
                $clean['registrationId']
            );

            echo json_encode(array("status" => true, "message" => 'registration ID added'));
            exit;

        }catch (exception $e){
            echo json_encode(array("status" => false, "message" => $e->getMessage()));
            exit;
        }
    }

    public function registerUserForAction(){

        $clean = ODE_CLASS_InputFilter::getInstance()->sanitizeInputs($_REQUEST);
        if ($clean == null){
            echo json_encode(array("status" => "error", "massage" => 'Insane inputs detected'));
            exit;
        }

        if($clean['status'] == "true") {
            SPODNOTIFICATION_BOL_Service::getInstance()->registerUserForNotification(
                $clean['userId'],
                $clean['plugin'],
                $clean['type'],
                $clean['action'],
                $clean['frequency'],
                empty($clean['subAction']) ? null : $clean['subAction']);
        }else{
            SPODNOTIFICATION_BOL_Service::getInstance()->deleteUserForNotification(
                $clean['userId'],
                $clean['plugin'],
                $clean['type'],
                $clean['action']);
        }

        echo json_encode(array("status" => "ok", "message" => 'Your preference was saved'));
        exit;

    }
}