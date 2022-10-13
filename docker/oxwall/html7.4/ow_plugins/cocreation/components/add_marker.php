<?php

class COCREATION_CMP_AddMarker extends OW_Component
{
    public function __construct()
    {
        $this->assign('components_url', SPODPR_COMPONENTS_URL);
        $this->assign('static_url', OW::getPluginManager()->getPlugin("cocreation")->getStaticUrl());
    }
}