<?php

class COCREATION_CMP_PublishDatasetOnCkan extends OW_Component {

    public function __construct() {
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('cocreation')->getStaticJsUrl() . 'vendor/CKANClient.js');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('cocreation')->getStaticJsUrl() . 'vendor/jquery-confirm/jquery-confirm.js');
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('cocreation')->getStaticJsUrl() . 'vendor/jquery-confirm/jquery-confirm.css');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('cocreation')->getStaticUrl() . 'components/js/publishOnCKAN.js');
        $this->assign('components_url', SPODPR_COMPONENTS_URL);

        //Publish on CKAN Authorization check.
        $ckan_platform_url_preference = BOL_PreferenceService::getInstance()->findPreference(COCREATION_CTRL_Admin::PREF_POCKAN_PLATFORM_URL);
        $ckan_api_key_preference = BOL_PreferenceService::getInstance()->findPreference(COCREATION_CTRL_Admin::PREF_POCKAN_API_KEY);
//        $ckan_def_organisation_preference = BOL_PreferenceService::getInstance()->findPreference(COCREATION_CTRL_Admin::PREF_POCKAN_DEF_ORGANISATION);
//        $ckan_def_groups_preference = BOL_PreferenceService::getInstance()->findPreference(COCREATION_CTRL_Admin::PREF_POCKAN_DEF_GROUPS);
        $canPublishOnCKAN = OW::getAuthorization()->isUserAuthorized(OW::getUser()->getId(), "cocreation", "Publish on CKAN");
        $canPublishOnCKAN = $canPublishOnCKAN
            && (!empty($ckan_platform_url_preference) && strlen($ckan_platform_url_preference->defaultValue) > 0)
            && (!empty($ckan_api_key_preference) && strlen($ckan_api_key_preference->defaultValue) > 0);
        $this->assign('canPublishOnCKAN', $canPublishOnCKAN);
        if ($canPublishOnCKAN) {
            $this->assign('PublishOnCKAN_platform_url', $ckan_platform_url_preference->defaultValue);
            $this->assign('PublishOnCKAN_api_key', $ckan_api_key_preference->defaultValue);
            //$this->assign('PublishOnCKAN_def_organisation', $ckan_def_organisation_preference->defaultValue);
            //$this->assign('PublishOnCKAN_def_groups', $ckan_def_groups_preference->defaultValue);
        } else {
            $this->assign('PublishOnCKAN_platform_url', '');
            $this->assign('PublishOnCKAN_api_key', '');
            //$this->assign('PublishOnCKAN_def_organisation', '');
            //$this->assign('PublishOnCKAN_def_groups', '');
        }
    }//EndConstructor.

}//EndClass.