<?php

class COCREATION_CMP_ImportDatasetFromSpod extends OW_Component {

    public function __construct($data) {
        $this->assign('components_url', SPODPR_COMPONENTS_URL);
        $this->assign('data', str_replace("'", "&#39;", $data));

        $js = UTIL_JsGenerator::composeJsString('
                ODE.ode_dataset_list = {$ode_dataset_list}
              ', array(
            'ode_dataset_list' => ODE_BOL_Service::getInstance()->getSettingByKey('ode_datasets_list'),
        ));

        OW::getDocument()->addOnloadScript($js);

    }//EndFunction.

}//EndClass.
