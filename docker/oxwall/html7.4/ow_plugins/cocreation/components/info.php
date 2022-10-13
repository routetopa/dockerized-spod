<?php


class COCREATION_CMP_Info extends OW_Component
{
    public function __construct($room)
    {
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('cocreation')->getStaticCssUrl() . 'info.css');

        $this->assign("name", $room->name);
        $this->assign("description", $room->description);
    }
}