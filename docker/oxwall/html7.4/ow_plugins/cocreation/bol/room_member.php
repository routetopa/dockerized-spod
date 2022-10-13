<?php

class COCREATION_BOL_RoomMember extends OW_Entity
{
    public $roomId;
    public $email;
    public $isJoined;
    public $role; // owner 0, collaborator 1, annotator 2 dataEntry 3 ...
    public $userId;
}