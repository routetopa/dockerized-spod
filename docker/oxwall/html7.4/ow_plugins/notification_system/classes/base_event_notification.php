<?php

class SPODNOTIFICATION_CLASS_BaseEventNotification
{
    public $plugin;
    public $action;
    public $subAction;
    public $targetUserId;
    public $ownerId;

    public function __construct($plugin, $action, $subAction, $ownerId, $targetUserId=null)
    {
        $this->plugin       = $plugin;
        $this->action       = $action;
        $this->subAction    = $subAction;
        $this->ownerId      = $ownerId;
        $this->targetUserId = $targetUserId;
    }

    public function save()
    {
        SPODNOTIFICATION_BOL_Service::getInstance()->addNotification($this);
    }

    public function getBasicMessage(){}

}