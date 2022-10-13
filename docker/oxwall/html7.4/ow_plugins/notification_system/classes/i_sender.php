<?php

interface SPODNOTIFICATION_CLASS_ISender
{
    public function __construct($notification, $targets);
    function send();
}