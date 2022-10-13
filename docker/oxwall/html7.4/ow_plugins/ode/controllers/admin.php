<?php

require_once OW::getPluginManager()->getPlugin('ode')->getRootDir() . 'lib/httpful.phar';

use Httpful\Request;
use Httpful\Http;
use Httpful\Mime;

class ODE_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function settings($params)
    {
        $settingsItem = new BASE_MenuItem();
        $settingsItem->setLabel('SETTINGS');
        $settingsItem->setUrl( OW::getRouter()->urlForRoute( 'ode-settings' ) );
        $settingsItem->setKey( 'settings' );
        $settingsItem->setIconClass( 'ow_ic_gear_wheel' );
        $settingsItem->setOrder( 0 );

        $providersItem = new BASE_MenuItem();
        $providersItem->setLabel('PROVIDERS');
        $providersItem->setUrl( OW::getRouter()->urlForRoute( 'ode-providers' ) );
        $providersItem->setKey( 'providers' );
//        $providersItem->setIconClass( 'ow_ic_help' );
        $providersItem->setOrder( 1 );

        $toolsItem = new BASE_MenuItem();
        $toolsItem->setLabel('TOOLS');
        $toolsItem->setUrl( OW::getRouter()->urlForRoute( 'ode-tools' ) );
        $toolsItem->setKey( 'tools' );
        $toolsItem->setOrder( 2 );

        $menu = new BASE_CMP_ContentMenu( array( $settingsItem, $providersItem, $toolsItem ) );
        $this->addComponent( 'menu', $menu );

        $this->setPageTitle('ODE SETTINGS');
        $this->setPageHeading('ODE SETTINGS');

        $form = new Form('settings');
        $this->addForm($form);

        /* DEEP ULR */
        $deepUrl = new TextField('deep_url');
        $preference = BOL_PreferenceService::getInstance()->findPreference('ode_deep_url');
        $ode_deep_url = empty($preference) ? "http://deep.routetopa.eu/DEEP/" : $preference->defaultValue;
        $deepUrl->setValue($ode_deep_url);
        $deepUrl->setRequired();
        $form->addElement($deepUrl);

        /* DEEP DATALET LIST */
        $deepDataletList = new TextField('deep_datalet_list');
        $preference = BOL_PreferenceService::getInstance()->findPreference('ode_deep_datalet_list');
        $ode_deep_datalet_list = empty($preference) ? "http://deep.routetopa.eu/DEEP/datalets-list" :  $preference->defaultValue;
        $deepDataletList->setValue($ode_deep_datalet_list);
        $deepDataletList->setRequired();
        $form->addElement($deepDataletList);

        /* DEEP CLIENT */
        $deepClient = new TextField('deep_client');
        $preference = BOL_PreferenceService::getInstance()->findPreference('ode_deep_client');
        $ode_deep_client = empty($preference) ? "http://deep.routetopa.eu/DEEPCLIENT/js/deepClient.js" : $preference->defaultValue;
        $deepClient->setValue($ode_deep_client);
        $deepClient->setRequired();
        $form->addElement($deepClient);

        /* DEEP COMPONENTS */
        $componentsUrl = new TextField('components_url');
        $preference = BOL_PreferenceService::getInstance()->findPreference('spodpr_components_url');
        $spodpr_components_url = empty($preference) ? "http://deep.routetopa.eu/COMPONENTS/" : $preference->defaultValue;
        $componentsUrl->setValue($spodpr_components_url);
        $componentsUrl->setRequired();
        $form->addElement($componentsUrl);

        /* WEBCOMPONENT JS */
        $webcomponents = new TextField('webcomponents_js');
        $preference = BOL_PreferenceService::getInstance()->findPreference('ode_webcomponents_js');
        $ode_webcomponents_js = empty($preference) ? "http://deep.routetopa.eu/COMPONENTS/bower_components/webcomponentsjs/webcomponents-lite.js" : $preference->defaultValue;
        $webcomponents->setValue($ode_webcomponents_js);
        $webcomponents->setRequired();
        $form->addElement($webcomponents);

        /* DATALET POLYFILL */
        $datalet_polyfill = new TextField('datalet_polyfill');
        $preference = BOL_PreferenceService::getInstance()->findPreference('ode_datalet_polyfill');
        $ode_datalet_polyfill = empty($preference) ? "http://deep.routetopa.eu/COMPONENTS/datalets/lib/js/vendors/webcomponents_polyfill_ff/webcomponents-hi-sd-ce.js" : $preference->defaultValue;
        $datalet_polyfill->setValue($ode_datalet_polyfill);
        //$datalet_polyfill->setRequired();
        $form->addElement($datalet_polyfill);


        /* ULTRACLARITY URL */
        $ultraClarityUrl = new TextField('ultra_clarity_url');
        $preference = BOL_PreferenceService::getInstance()->findPreference('ode_ultra_clarity_url');
        $ode_ultra_clarity_url = empty($preference) ? "" : $preference->defaultValue;
        $ultraClarityUrl->setValue($ode_ultra_clarity_url);
        $ultraClarityUrl->setRequired();
        $form->addElement($ultraClarityUrl);


        /* ELASTICMAIL APIKEY */
        $elasticMailAPIKey = new TextField('elastic_mail_api_key');
        $preference = BOL_PreferenceService::getInstance()->findPreference('elastic_mail_api_key');
        $elastic_mail_api_key = empty($preference) ? "" : $preference->defaultValue;
        $elasticMailAPIKey->setValue($elastic_mail_api_key);
        $elasticMailAPIKey->setRequired();
        $form->addElement($elasticMailAPIKey);

        $submit = new Submit('add');
        $submit->setValue('SUBMIT');
        $form->addElement($submit);

        if ( OW::getRequest()->isPost() && $form->isValid($_POST))
        {
            $data = $form->getValues();

            /* ode_deep_url */
            $preference = BOL_PreferenceService::getInstance()->findPreference('ode_deep_url');

            if(empty($preference))
                $preference = new BOL_Preference();

            $preference->key = 'ode_deep_url';
            $preference->sectionName = 'general';
            $preference->defaultValue = $data['deep_url'];
            $preference->sortOrder = 1;
            BOL_PreferenceService::getInstance()->savePreference($preference);

            /* ode_deep_datalet_list */
            $preference = BOL_PreferenceService::getInstance()->findPreference('ode_deep_datalet_list');

            if(empty($preference))
                $preference = new BOL_Preference();

            $preference->key = 'ode_deep_datalet_list';
            $preference->sectionName = 'general';
            $preference->defaultValue = $data['deep_datalet_list'];
            $preference->sortOrder = 2;
            BOL_PreferenceService::getInstance()->savePreference($preference);

            /* ode_deep_client */
            $preference = BOL_PreferenceService::getInstance()->findPreference('ode_deep_client');

            if(empty($preference))
                $preference = new BOL_Preference();

            $preference->key = 'ode_deep_client';
            $preference->sectionName = 'general';
            $preference->defaultValue = $data['deep_client'];
            $preference->sortOrder = 3;
            BOL_PreferenceService::getInstance()->savePreference($preference);

            /* spodpr_components_url */
            $preference = BOL_PreferenceService::getInstance()->findPreference('spodpr_components_url');

            if(empty($preference))
                $preference = new BOL_Preference();

            $preference->key = 'spodpr_components_url';
            $preference->sectionName = 'general';
            $preference->defaultValue = $data['components_url'];
            $preference->sortOrder = 1;
            BOL_PreferenceService::getInstance()->savePreference($preference);

            /* ode_webcomponents_js */
            $preference = BOL_PreferenceService::getInstance()->findPreference('ode_webcomponents_js');

            if(empty($preference))
                $preference = new BOL_Preference();

            $preference->key = 'ode_webcomponents_js';
            $preference->sectionName = 'general';
            $preference->defaultValue = $data['webcomponents_js'];
            $preference->sortOrder = 4;
            BOL_PreferenceService::getInstance()->savePreference($preference);

            /* ode_datalet_polyfill */
            $preference = BOL_PreferenceService::getInstance()->findPreference('ode_datalet_polyfill');

            if(empty($preference))
                $preference = new BOL_Preference();

            $preference->key = 'ode_datalet_polyfill';
            $preference->sectionName = 'general';
            $preference->defaultValue = $data['datalet_polyfill'];
            $preference->sortOrder = 5;
            BOL_PreferenceService::getInstance()->savePreference($preference);

            /* ode_ultra_clarity_url */
            $preference = BOL_PreferenceService::getInstance()->findPreference('ode_ultra_clarity_url');

            if(empty($preference))
                $preference = new BOL_Preference();

            $preference->key = 'ode_ultra_clarity_url';
            $preference->sectionName = 'general';
            $preference->defaultValue = $data['ultra_clarity_url'];
            $preference->sortOrder = 6;
            BOL_PreferenceService::getInstance()->savePreference($preference);

            /* elastic_mail_api_key */
            $preference = BOL_PreferenceService::getInstance()->findPreference('elastic_mail_api_key');

            if(empty($preference))
                $preference = new BOL_Preference();

            $preference->key = 'elastic_mail_api_key';
            $preference->sectionName = 'general';
            $preference->defaultValue = $data['elastic_mail_api_key'];
            $preference->sortOrder = 7;
            BOL_PreferenceService::getInstance()->savePreference($preference);


        }
    }

    public function providers()
    {
        $settingsItem = new BASE_MenuItem();
        $settingsItem->setLabel('SETTINGS');
        $settingsItem->setUrl( OW::getRouter()->urlForRoute( 'ode-settings' ) );
        $settingsItem->setKey( 'settings' );
        $settingsItem->setIconClass( 'ow_ic_gear_wheel' );
        $settingsItem->setOrder( 0 );

        $providersItem = new BASE_MenuItem();
        $providersItem->setLabel('PROVIDERS');
        $providersItem->setUrl( OW::getRouter()->urlForRoute( 'ode-providers' ) );
        $providersItem->setKey( 'providers' );
//        $providersItem->setIconClass( 'ow_ic_help' );
        $providersItem->setOrder( 1 );

        $toolsItem = new BASE_MenuItem();
        $toolsItem->setLabel('TOOLS');
        $toolsItem->setUrl( OW::getRouter()->urlForRoute( 'ode-tools' ) );
        $toolsItem->setKey( 'tools' );
        $toolsItem->setOrder( 2 );

        $menu = new BASE_CMP_ContentMenu( array( $settingsItem, $providersItem, $toolsItem ) );
        $this->addComponent( 'menu', $menu );

        $this->setPageTitle('ODE PROVIDERS');
        $this->setPageHeading('ODE PROVIDERS');

        $form = new Form('providers');
        $this->addForm($form);

        $name = new TextField('name');
        $name->setRequired();
        $form->addElement($name);

        $url = new TextField('url');
        $url->setRequired();
        $form->addElement($url);

        $submit = new Submit('addProvider');
        $submit->setValue('ADD');
        $form->addElement($submit);

        if ( OW::getRequest()->isPost() && $form->isValid($_POST))
        {
            $data = $form->getValues();

            ODE_BOL_Service::getInstance()->addProvider($data['name'], $data['url']);

            $this->redirect(OW::getRouter()->urlForRoute('ode-providers'));
        }

        $providersList = array();
        $deleteUrls = array();
        $providers = ODE_BOL_Service::getInstance()->getProviderList();
        foreach ( $providers as $provider )
        {
            /* @var $contact ODE_BOL_Provider */
            $providersList[$provider->id]['name'] = $provider->name;
            $providersList[$provider->id]['url'] = $provider->url;
            $deleteUrls[$provider->id] = OW::getRouter()->urlFor(__CLASS__, 'delete', array('id' => $provider->id));
        }
        $this->assign('providersList', $providersList);
        $this->assign('deleteUrls', $deleteUrls);
        $this->assign('createDatasetCache', OW::getRouter()->urlFor(__CLASS__, 'createDatasetCache'));
    }

    public function tools()
    {
        $settingsItem = new BASE_MenuItem();
        $settingsItem->setLabel('SETTINGS');
        $settingsItem->setUrl( OW::getRouter()->urlForRoute( 'ode-settings' ) );
        $settingsItem->setKey( 'settings' );
        $settingsItem->setIconClass( 'ow_ic_gear_wheel' );
        $settingsItem->setOrder( 0 );

        $providersItem = new BASE_MenuItem();
        $providersItem->setLabel('PROVIDERS');
        $providersItem->setUrl( OW::getRouter()->urlForRoute( 'ode-providers' ) );
        $providersItem->setKey( 'tools' );
//        $providersItem->setIconClass( 'ow_ic_help' );
        $providersItem->setOrder( 1 );

        $toolsItem = new BASE_MenuItem();
        $toolsItem->setLabel('TOOLS');
        $toolsItem->setUrl( OW::getRouter()->urlForRoute( 'ode-tools' ) );
        $toolsItem->setKey( 'tools' );
        $toolsItem->setOrder( 2 );

        $menu = new BASE_CMP_ContentMenu( array( $settingsItem, $providersItem, $toolsItem ) );
        $this->addComponent( 'menu', $menu );

        $this->setPageTitle('ODE TOOLS');
        $this->setPageHeading('ODE TOOLS');

        $form = new Form('settings');
        $this->addForm($form);

        /* SPLOD IsVisible */
        $SPLOD_visible = new CheckboxField('SPLOD_visible');
        $preference = BOL_PreferenceService::getInstance()->findPreference('splod_is_visible_whatsnew');
        $splod_pref = empty($preference) ? "0" : $preference->defaultValue;
        $SPLOD_visible->setValue($splod_pref);
        $form->addElement($SPLOD_visible);

        /* MAPLET IsVisible */
        $Maplet_visible = new CheckboxField('Maplet_visible');
        $preference = BOL_PreferenceService::getInstance()->findPreference('maplet_is_visible_whatsnew');
        $maplet_pref = empty($preference) ? "0" : $preference->defaultValue;
        $Maplet_visible->setValue($maplet_pref);
        $form->addElement($Maplet_visible);

        $submit = new Submit('add');
        $submit->setValue('SAVE');
        $form->addElement($submit);

        if ( OW::getRequest()->isPost() && $form->isValid($_POST))
        {
            $data = $form->getValues();

            /* splod */
            $preference = BOL_PreferenceService::getInstance()->findPreference('splod_is_visible_whatsnew');

            if(empty($preference))
                $preference = new BOL_Preference();

            $preference->key = 'splod_is_visible_whatsnew';
            $preference->sectionName = 'general';
            $preference->defaultValue = $data['SPLOD_visible'] ? $data['SPLOD_visible'] : 0;
            $preference->sortOrder = 1;
            BOL_PreferenceService::getInstance()->savePreference($preference);

            /* maplet */
            $preference = BOL_PreferenceService::getInstance()->findPreference('maplet_is_visible_whatsnew');

            if(empty($preference))
                $preference = new BOL_Preference();

            $preference->key = 'maplet_is_visible_whatsnew';
            $preference->sectionName = 'general';
            $preference->defaultValue = $data['Maplet_visible'] ? $data['Maplet_visible'] : 0;
            $preference->sortOrder = 1;
            BOL_PreferenceService::getInstance()->savePreference($preference);
        }

    }

    public function delete( $params )
    {
        if ( isset($params['id']) )
        {
            ODE_BOL_Service::getInstance()->deleteProvider((int) $params['id']);
        }
        $this->redirect(OW::getRouter()->urlForRoute('ode-providers'));
    }

    public function createDatasetCache()
    {
        ODE_BOL_Service::getInstance()->saveSetting('ode_datasets_list', $this->datasetsListBuilder());
        $this->redirect(OW::getRouter()->urlForRoute('ode-providers'));
    }

    /**** GET DATASETS LIST ****/

//    public function datasetTree()
//    {
//        header('content-type: application/json');
//        header("Access-Control-Allow-Origin: *");
//        echo $this->datasetTreeBuilder();
//        die();
//    }

    public function datasetsListBuilder()
    {
        $step = 100;
        $maxDatasetPerProvider = isset($_REQUEST['maxDataset']) ? $_REQUEST['maxDataset'] : 1;

        $providersDatasets = [];
        $providers = ODE_BOL_Service::getInstance()->getProviderList();

        foreach ($providers as $p) {

            $providerDatasetCounter = 0;
            $start = 0;

            // Build providers
            $providersDatasets[$p->id] = ['p_name' => $p->name, 'p_url' => $p->url, 'p_datasets' => []];

            // Try CKAN
            while($providerDatasetCounter < $maxDatasetPerProvider) {
                $ch = curl_init($p->url . "/api/3/action/package_search?start=" . $start . "&rows=" . $step);//1000 limit!
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                $res = curl_exec($ch);
                $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                if (200 == $retcode)
                {
                    $data = json_decode($res, true);

                    if(count($data["result"]["results"]))
                    {
//                        $providersDatasets[$p->id]['p_datasets'] = array_merge($providersDatasets[$p->id]['p_datasets'], $this->getCkanDatasets($data, $p->id));

                        $a = $this->getCkanDatasets($data, $p->id);
                        $l_a = count($a);
                        for($j = 0; $j < $l_a; $j++) {
                            $providersDatasets[$p->id]['p_datasets'][] = $a[$j];
                        }

                        $start += $step;
                        $providerDatasetCounter += count($data["result"]["results"]);
                    }
                    else
                    {
                        break;
                    }
                }
                else
                {
                    break;
                }
            }

            // Try ODS
            $ch = curl_init($p->url . "/api/datasets/1.0/search/?rows=-1");
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $res = curl_exec($ch);
            $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if (200 == $retcode) {
                $data = json_decode( $res, true );
//                $providersDatasets[$p->id]['p_datasets'] = array_merge($providersDatasets[$p->id]['p_datasets'], $this->getOpenDataSoftDatasets($data, $p->id));

                $a = $this->getOpenDataSoftDatasets($data, $p->id);
                $l_a = count($a);
                for($j = 0; $j < $l_a; $j++) {
                    $providersDatasets[$p->id]['p_datasets'][] = $a[$j];
                }
                continue;
            }


            // Try DKAN
            $ch = curl_init($p->url . "/?q=api/3/action/current_package_list_with_resources");
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $res = curl_exec($ch);
            $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if (200 == $retcode) {
                $data = json_decode( $res, true );

                $a = $this->getDKANDatasets($data, $p->id);
                $l_a = count($a);
                for($j = 0; $j < $l_a; $j++) {
                    $providersDatasets[$p->id]['p_datasets'][] = $a[$j];
                }
                continue;
            }

        }

        return json_encode($providersDatasets);
    }

//    private function getCkanDatasets($data, $provider_id) {
//        $treemapdata = array();
//        $datasets = $data['result']['results'];
//        $datasetsCnt = count( $datasets );
//        for ($i = 0; $i < $datasetsCnt; $i++) {
//            $ds = $datasets[$i];
//            $resourcesCnt = count($ds['resources']);
//            if($resourcesCnt > 1) {
//                $resources = array();
//                for ($j = 0; $j < $resourcesCnt; $j++)
//                    $resources[] = $ds['resources'][$j]['name'];
//                $treemapdata[] = array(
//                    'name' => $ds['name'],
//                    'id' => $ds['id'],
//                    'p' => 'CKAN_' . $provider_id,
//                    'resources' => $resources
//                );
//            }
//            else
//                $treemapdata[] = array(
//                    'name' => $ds['name'],
//                    'id' => $ds['id'],
//                    'p' => 'CKAN_' . $provider_id
//                );
//        }
//        return $treemapdata;
//    }

    private function getCkanDatasets($data, $provider_id) {
        $filter = ['csv', 'ods', 'xls', 'xlsx'];

        $treemapdata = array();
        $datasets = $data['result']['results'];
        $datasetsCnt = count( $datasets );
        for ($i = 0; $i < $datasetsCnt; $i++) {
            $ds = $datasets[$i];
            $resourcesCnt = count($ds['resources']);
            $resources = array();
            for ($j = 0; $j < $resourcesCnt; $j++)
//                if (strcasecmp($ds['resources'][$j]['format'], 'csv') == 0)

                if (in_array(strtolower($ds['resources'][$j]['format']), $filter))
                    $resources[] = $ds['resources'][$j]['name'];
                else
                    $resources[] = [$ds['resources'][$j]['name'], 'disabled'];

//                $resources[] =  $this->sanitizeInput($ds['resources'][$j]['name']);

                if (count($resources) == 1)
                    $treemapdata[] = array(
                        'name' => $ds['title'] ? $this->sanitizeInput($ds['title']) : $this->sanitizeInput($ds['name']),
                        'id' => $ds['id'],
                        'p' => 'CKAN_' . $provider_id
                    );
                else if(count($resources) > 1)
                    $treemapdata[] = array(
                        'name' => $ds['title'] ? $this->sanitizeInput($ds['title']) : $this->sanitizeInput($ds['name']),
                        'id' => $ds['id'],
                        'p' => 'CKAN_' . $provider_id,
                        'resources' => $resources
                    );
        }
        return $treemapdata;
    }

    private function getOpenDataSoftDatasets($data, $provider_id) {
        $treemapdata = array();
        $datasets = $data['datasets'];
        $datasetsCnt = count( $datasets );
        for ($i = 0; $i < $datasetsCnt; $i++) {
            $ds = $datasets[$i];

            @$treemapdata[] = array(
                'name' => $this->sanitizeInput($ds['metas']['title']),
                'id' => $this->sanitizeInput($ds['datasetid']),
                'p' => 'ODS_' . $provider_id
            );
        }
        return $treemapdata;
    }

    private function getDKANDatasets($data, $provider_id)
    {
        $treemapdata = array();
        $datasets = $data['result'];
        $datasetsCnt = count( $datasets );

        for ($i = 0; $i < $datasetsCnt; $i++)
        {
            $ds = $datasets[$i];
            $resourcesCnt = count($ds['resources']);
            $resources = array();

            for ($j = 0; $j < $resourcesCnt; $j++)
                $resources[] =  $this->sanitizeInput($ds['resources'][$j]['title']);

            if (count($resources) == 1)
                $treemapdata[] = array(
                    'name' => $this->sanitizeInput($ds['title']),
                    'id' => $ds['id'],
                    'p' => 'DKAN_' . $provider_id
                );
            else if(count($resources) > 1)
                $treemapdata[] = array(
                    'name' => $this->sanitizeInput($ds['title']),
                    'id' => $ds['id'],
                    'p' => 'DKAN_' . $provider_id,
                    'resources' => $resources
                );
        }

        return $treemapdata;

    }

    protected function sanitizeInput($str)
    {
//        return str_replace("'", "&#39;", !empty($str) ? $str : "");
        return $str;
    }

}