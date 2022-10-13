<?php
class SPODNOTIFICATION_BOL_Service
{
    /**
     * Singleton instance.
     *
     * @var SPODNOTIFICATION_BOL_Service
     */
    private static $classInstance;

    private $defaultRuleList = array();

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return SPODNOTIFICATION_BOL_Service
     */
    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
    }

    public function getAllNotifications()
    {
        return SPODNOTIFICATION_BOL_NotificationDao::getInstance()->findAll();
    }

    public function getAllNotificationsByFrequency($frequency)
    {
        $results = null;
        switch($frequency){
            case SPODNOTIFICATION_CLASS_Consts::FREQUENCY_IMMEDIATELY:
                $example = new OW_Example();
                $example->setOrder('timestamp DESC');
                $results = SPODNOTIFICATION_BOL_NotificationDao::getInstance()->findListByExample($example);
                $results = array($results[0]);
                break;
            case SPODNOTIFICATION_CLASS_Consts::FREQUENCY_EVERYDAY:
                $today_timestamp     = strtotime('today midnight');
                $tomorrow_timestamp  = strtotime('tomorrow midnight');

                $example = new OW_Example();
                $example->andFieldBetween('timestamp', $today_timestamp, $tomorrow_timestamp);
                $results = SPODNOTIFICATION_BOL_NotificationDao::getInstance()->findListByExample($example);
                break;
            case SPODNOTIFICATION_CLASS_Consts::FREQUENCY_EVERYMONTH:
                $current_month_timestamp = strtotime('first day of this month', time());
                $next_month_timestamp    = strtotime('first day of next month', time());

                $example = new OW_Example();
                $example->andFieldBetween('timestamp', $current_month_timestamp, $next_month_timestamp);
                $results = SPODNOTIFICATION_BOL_NotificationDao::getInstance()->findListByExample($example);
                break;
        }

        foreach ($results as &$result)
        {
            $result->notification = unserialize($result->notification);
        }

        return $results;
    }

    public function getNotificationByPlugin($plugin)
    {
        $example = new OW_Example();
        $example->andFieldEqual('plugin', $plugin);
        $result = SPODNOTIFICATION_BOL_NotificationDao::getInstance()->findListByExample($example);
        return $result;
    }

    public function deleteNotificationById($id)
    {
        SPODNOTIFICATION_BOL_NotificationDao::getInstance()->deleteById($id);
    }

    public function deleteExpiredNotifications()
    {
        $current_month_timestamp = strtotime('first day of this month', time());

        $example = new OW_Example();
        $example->andFieldLessThan('timestamp', $current_month_timestamp);
        SPODNOTIFICATION_BOL_NotificationDao::getInstance()->deleteByExample($example);
    }

    public function addNotification(SPODNOTIFICATION_CLASS_BaseEventNotification $obj){
        $notification               = new SPODNOTIFICATION_BOL_Notification();
        $notification->notification = serialize($obj);
        $notification->timestamp    = time();

        SPODNOTIFICATION_BOL_NotificationDao::getInstance()->save($notification);
    }

    public function registerUserForNotification($userId, $plugin, $type, $action, $frequency, $parentAction=null)
    {
        if($this->isUserRegisteredForAction($userId,$plugin,$action,$type) != null){
            SPODNOTIFICATION_BOL_RegisteredUserDao::getInstance()->updateFrequency($userId,$plugin,$action, $frequency);
            return;
        }

        if($parentAction && $this->isUserRegisteredForAction($userId,$plugin,$parentAction,$type) == null){
            $this->registerUserForNotification($userId, $plugin, $type, $parentAction, SPODNOTIFICATION_CLASS_Consts::FREQUENCY_IMMEDIATELY, null);
        }

        $reguser               = new SPODNOTIFICATION_BOL_RegisteredUser();
        $reguser->userId       = $userId;
        $reguser->plugin       = $plugin;
        $reguser->type         = $type;
        $reguser->action       = $action;
        $reguser->parentAction = $parentAction;
        $reguser->frequency    = $frequency;
        SPODNOTIFICATION_BOL_RegisteredUserDao::getInstance()->save($reguser);
    }

    public function isUserRegisteredForAction($userId, $plugin, $action, $type){
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('plugin', $plugin);
        $example->andFieldEqual('action', $action);
        $example->andFieldEqual('type',   $type);
        $result = SPODNOTIFICATION_BOL_RegisteredUserDao::getInstance()->findObjectByExample($example);
        return $result;
    }


    public static function isUserRegisteredForSubAction($userId, $plugin, $action, $type)
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('plugin', $plugin);
        $example->andFieldEqual('parentAction', $action);
        $example->andFieldEqual('type',   $type);
        $result = SPODNOTIFICATION_BOL_RegisteredUserDao::getInstance()->findListByExample($example);
        return $result;
    }

    public function deleteRegisteredUser($userId)
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('userId', $userId);
        SPODNOTIFICATION_BOL_RegisteredUserDao::getInstance()->deleteByExample($ex);
    }

    public function deleteUserForNotification($userId, $plugin, $type, $action){
        $ex = new OW_Example();
        $ex->andFieldEqual('userId', $userId);
        $ex->andFieldEqual('plugin', $plugin);
        $ex->andFieldEqual('type', $type);
        $ex->andFieldEqual('action', $action);
        SPODNOTIFICATION_BOL_RegisteredUserDao::getInstance()->deleteByExample($ex);
    }

    public function getRegisteredUsersForNotification($notification, $frequency)
    {
        $r_u_tn = SPODNOTIFICATION_BOL_RegisteredUserDao::getInstance()->getTableName();
        $b_u_tn = "ow_base_user";

        $sql = "SELECT userId, email, username FROM {$r_u_tn} JOIN {$b_u_tn} ON {$r_u_tn}.userId = {$b_u_tn}.id WHERE";

        $sql .= " plugin = '{$notification->plugin}' ";
        $sql .= " AND frequency = '{$frequency}' ";
        $sql .= " AND type = '" . (new ReflectionClass(get_class($notification)))->getStaticPropertyValue("TYPE") . "' ";
        $sql .= " AND {$b_u_tn}.id != '{$notification->ownerId}' ";

        if(!empty($notification->targetUserId)) {
            $sql .= " AND userId = '{$notification->targetUserId}' ";
            $sql .= " AND action = '{$notification->action}' ";
        }else{
            $sql .= " AND action = '" . (($notification->subAction == $notification->action) ? $notification->action : $notification->subAction) . "' ";

            if($notification->subAction != $notification->action)
                $sql .= " AND userId IN (SELECT userId FROM {$r_u_tn} WHERE action = '{$notification->action}')";
        }

        $dbo = OW::getDbo();
        return $dbo->queryForObjectList($sql,'SPODNOTIFICATION_BOL_RegisteredUserContract');
    }

    public function getRegistredByAction($action, $userId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('action', $action);
        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('type', 'mail');

        $result = SPODNOTIFICATION_BOL_RegisteredUserDao::getInstance()->findObjectByExample($example);
        return $result;
    }

    public function addUserRegistrationId($userId, $registrationId)
    {
        if($this->getRegistrationIdForUser($userId) != null){
           SPODNOTIFICATION_BOL_UserRegistrationIdDao::getInstance()->updateRegistrationId($userId, $registrationId);
        }else{
            $r                 = new SPODNOTIFICATION_BOL_UserRegistrationId();
            $r->userId         = $userId;
            $r->registrationId = $registrationId;
            $r->timestamp      = time();
            SPODNOTIFICATION_BOL_UserRegistrationIdDao::getInstance()->save($r);
        }
    }

    public function getRegistrationIdForUser($userId){
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        $result = SPODNOTIFICATION_BOL_UserRegistrationIdDao::getInstance()->findObjectByExample($example);
        return !empty($result) ? $result->registrationId : null;
    }

}
