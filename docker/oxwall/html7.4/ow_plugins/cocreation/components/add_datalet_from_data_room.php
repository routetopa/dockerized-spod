<?php

class COCREATION_CMP_AddDataletFromDataRoom extends OW_Component
{
    public function __construct($dataUrl)
    {
        /*$this->assign('components_url', SPODPR_COMPONENTS_URL);
        $this->assign('dataUrl', $dataUrl);
        $this->assign('deepUrl', ODE_DEEP_URL);
        $this->assign('dataletListUrl', ODE_DEEP_DATALET_LIST);*/
        $this->assign('base_url', OW_URL_HOME);

    }
}