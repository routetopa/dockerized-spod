<?php
class COCREATION_CMP_Helper extends OW_Component
{
    public function __construct()
    {
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('cocreation')->getStaticCssUrl() . 'perfect-scrollbar.min.css');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('cocreation')->getStaticJsUrl() . 'perfect-scrollbar.jquery.js');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('cocreation')->getStaticJsUrl() . 'helper.js');

        $this->assign("staticResourcesUrl", OW::getPluginManager()->getPlugin('cocreation')->getStaticUrl());
        $this->assign('components_url', SPODPR_COMPONENTS_URL);
    }
}