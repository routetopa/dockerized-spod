<?php

class SPODNOTIFICATION_BOL_RegisteredUserDao extends OW_BaseDao
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
     * @var SPODNOTIFICATION_BOL_RegisteredUserDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return SPODNOTIFICATION_BOL_RegisteredUserDao
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
        return 'SPODNOTIFICATION_BOL_RegisteredUser';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'spod_notification_registered_user';
    }

    public function updateFrequency($uid, $plugin, $action, $frequency = 1)
    {
        $query = 'UPDATE ' . $this->getTableName() . ' SET ' . 'frequency=' . $frequency .
            ' WHERE userId='  . $uid . ' AND plugin=\'' . $plugin . '\' AND action=\'' . $action . '\'';

        return $this->dbo->query($query);
    }
}
