<?php

interface IMobileSender
{
    public function __construct($notification, $targets);
    function send();
}