<?php

class SPODNOTIFICATION_CLASS_FirebaseSender implements SPODNOTIFICATION_CLASS_ISender
{
    private $notification;
    private $targets;

    public function __construct($notification, $targets)
    {
        $this->notification = $notification;
        $this->targets      = $targets;
    }

    public function send()
    {
        $preference = BOL_PreferenceService::getInstance()->findPreference('firebase_api_key');
        $api_key = empty($preference) ? "" : $preference->defaultValue;

        $headers = array
        (
            'Authorization: key=' . $api_key,
            'Content-Type: application/json'
        );

        $notification = array
        (
            'title'	=> $this->notification->getTitle(),
            'body' 	=> array(
                'plugin'  => $this->notification->plugin,
                'action'  => $this->notification->action,
                'message' => $this->notification->getMessage(),
                'data'    => $this->notification->getData()
            )
        );

        $fields = array
        (
            'notification' => $notification
        );

        foreach ($this->targets as $target)
        {
            $fields['to'] = SPODNOTIFICATION_BOL_Service::getInstance()->getRegistrationIdForUser($target->userId);

            try
            {
                #Send Reponse To FireBase Server
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                $result = curl_exec($ch);
                curl_close($ch);
                #Echo Result Of FireBase Server
                //echo $result;
            }
            catch (Exception $ex)
            {
                $ex->getMessage();
            }
        }
    }
}