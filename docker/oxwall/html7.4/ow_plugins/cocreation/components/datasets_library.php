<?php

/**
 * Created by PhpStorm.
 * User: Utente
 * Date: 01/03/2016
 * Time: 16.08
 */
class COCREATION_CMP_DatasetsLibrary extends OW_Component
{
    public function __construct($roomId)
    {
        //selectedfields="[{"field":"Column","value":"Citta","index":1},{"field":"Column","value":"Descrizione","index":2}]"
        //get all dataset for current room
        $datasets = COCREATION_BOL_Service::getInstance()->getDatasetsByRoomId($roomId);
        foreach($datasets as $d){
            $d->selectedfields = "[";
            $d->fields = json_decode($d->fields);
            for($i=0; $i < count($d->fields);$i++)
                $d->selectedfields .= '{"field":"Column","value":"'. $d->fields[$i] .'","index":'. ($i+1) . '},';
            $d->selectedfields[strlen($d->selectedfields) - 1] = ']';

        }
        $this->assign('datasets', $datasets);

        $suggested_datasets = array();
        foreach($datasets as $dataset){
            $d = new stdClass();
            $metas = new stdClass();
            $metas->description = $dataset->description;

            $d->resource_name =  $dataset->name;
            $d->url           =  $dataset->url;
            $d->metas         =  json_encode($metas);
            array_push($suggested_datasets, $d);
        }

        $this->assign('components_url', SPODPR_COMPONENTS_URL);
        //$this->assign('datasets_list', ODE_BOL_Service::getInstance()->getSettingByKey('ode_datasets_list'));

        $js = UTIL_JsGenerator::composeJsString('
                SPODPUBLICROOM = {}
                SPODPUBLICROOM.suggested_datasets       = {$cocreation_room_suggested_datasets}
            ', array(
               'numDataletsInRoom'                   => count(COCREATION_BOL_Service::getInstance()->getDataletsByRoomId($roomId)),
               'cocreation_room_suggested_datasets'  => json_encode($suggested_datasets)
        ));

        OW::getDocument()->addOnloadScript($js);

        OW::getLanguage()->addKeyForJs('cocreation', 'dataset_successfully_added');
        OW::getLanguage()->addKeyForJs('cocreation', 'dataset_successfully_added');
    }

}