<?php

class SPODNOTIFICATION_BOL_UserRegistrationIdDao extends OW_BaseDao
{
    /**
     * Constructor.
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Singleton instance.
     *
     * @var SPODNOTIFICATION_BOL_UserRegistrationIdDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return SPODNOTIFICATION_BOL_UserRegistrationIdDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'SPODNOTIFICATION_BOL_UserRegistrationId';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'spod_notification_user_registration_id';
    }

    public function updateRegistrationId($userId, $registrationid )
    {
        $query = 'UPDATE ' . $this->getTableName() . ' SET ' .
            'registrationId=\'' . $registrationid .'\'' .
            ' WHERE userId=' . $userId;

        return $this->dbo->query($query);
    }

}
