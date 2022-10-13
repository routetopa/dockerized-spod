<?php


class COCREATION_CMP_Members extends OW_Component
{
    public function __construct($owner, $members)
    {
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('cocreation')->getStaticCssUrl() . 'members.css');

        $owner['role'] = 'owner';
        $owner['isJoined'] = true;
        array_unshift($members, $owner);

        $this->assign('members', $members);
    }
}