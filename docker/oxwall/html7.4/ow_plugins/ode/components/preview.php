<?php

class ODE_CMP_Preview extends OW_Component
{
    public function __construct($component="data-sevc-controllet")
    {
        $this->assign("component", $component);
        $this->assign('urlHome', OW_URL_HOME );

        $js = UTIL_JsGenerator::composeJsString('
                ODE.ode_dataset_list = {$ode_dataset_list}
              ', array(
            'ode_dataset_list' => ODE_BOL_Service::getInstance()->getSettingByKey('ode_datasets_list'),
        ));

        OW::getDocument()->addOnloadScript($js);
    }
}