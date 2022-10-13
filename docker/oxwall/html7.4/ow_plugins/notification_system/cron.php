<?php

class SPODNOTIFICATION_Cron extends OW_Cron
{
    public function __construct()
    {
        parent::__construct();

        //$this->addJob('chechServerStatus', 60);
        $this->addJob('sendEveryDayNotification'  , 60 * 24);//OneDay
        $this->addJob('sendEveryMouthNotification', 60 * 24 * 30);//OneMonth
        $this->addJob('deleteExpiredNotifications',      60 * 24);
    }

    public function run()
    {
        // TODO: Implement run() method.
    }

    private function chechServerStatus()
    {
        $connection = @fsockopen('localhost', '3000');
        $preference = BOL_PreferenceService::getInstance()->findPreference('spodnotification_admin_run_status');
        $spodnotification_admin_run_status = empty($preference) ? "" : $preference->defaultValue;

        if (!is_resource($connection) && $spodnotification_admin_run_status == "RUN")
        {
            //chdir(OW::getPluginManager()->getPlugin('spodnotification')->getRootDir() . '/lib');
            //shell_exec("sh ./run_server.sh");
            shell_exec("service spod-notification-service start");
        }
    }

    public function sendEveryDayEmailNotification(){
        SPODNOTIFICATION_CLASS_EventHandler::getInstance()->sendNotificationBatchProcess(SPODNOTIFICATION_CLASS_Consts::FREQUENCY_EVERYDAY);
    }

    public function sendEveryMouthEmailNotification(){
        SPODNOTIFICATION_CLASS_EventHandler::getInstance()->sendNotificationBatchProcess(SPODNOTIFICATION_CLASS_Consts::FREQUENCY_EVERYMONTH);
    }

    public function deleteExpiredNotifications(){
        SPODNOTIFICATION_BOL_Service::getInstance()->deleteExpiredNotifications();
    }


}