<?php

class SPODNOTIFICATION_BOL_RegisteredUser extends OW_Entity
{
    /**
     * @var string
     */
    public $userId;
    /**
     * @var string
     */
    public $type;
    /**
     * @var string
     */
    public $plugin;
    /**
     * @var string
     */
    public $action;
    /**
     * @var string
     */
    public $parentAction;
    /**
     * @var string
     */
    public $frequency;
}
