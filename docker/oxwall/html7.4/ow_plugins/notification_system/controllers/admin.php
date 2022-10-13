<?php


class SPODNOTIFICATION_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function settings($params)
    {
        $this->setPageTitle('Notification system');
        $this->setPageHeading('Notification system');

        $form = new Form('settings');
        $this->addForm($form);

        $submit = new Submit('add');

        /*$field = new HiddenField('running');
        $form->addElement($field);

        $connection = @fsockopen('localhost', '3000');

        if (is_resource($connection))
        {
            $submit->setValue('STOP');
            $this->assign('running', 'running');
            $field->setValue(1);
        }
        else
        {
            $submit->setValue('START');
            $this->assign('running', 'not running');
            $field->setValue(0);
        }*/

        $firebase_api_key = new TextField('firebase_api_key');
        $preference = BOL_PreferenceService::getInstance()->findPreference('firebase_api_key');
        $setting_firebase_api_key = $preference->defaultValue;
        $firebase_api_key->setValue($setting_firebase_api_key);
        $firebase_api_key->setRequired();
        $form->addElement($firebase_api_key);

        $elastic_mail_api_key = new TextField('elastic_mail_api_key');
        $preference = BOL_PreferenceService::getInstance()->findPreference('elastic_mail_api_key');
        $setting_elastic_mail_api_key = $preference->defaultValue;
        $elastic_mail_api_key->setValue($setting_elastic_mail_api_key);
        $elastic_mail_api_key->setRequired();
        $form->addElement($elastic_mail_api_key);

        $form->addElement($submit);

        if ( OW::getRequest()->isPost() && $form->isValid($_POST))
        {
            $data = $form->getValues();

            //SAVE FIREBASE API KEY PREFERENCES
            $firebase_preference = BOL_PreferenceService::getInstance()->findPreference('firebase_api_key');
            if(empty($firebase_preference))
                $firebase_preference = new BOL_Preference();

            $firebase_preference->key = 'firebase_api_key';
            $firebase_preference->sortOrder = 1;
            $firebase_preference->sectionName = 'general';
            $firebase_preference->defaultValue =  $data['firebase_api_key'];
            BOL_PreferenceService::getInstance()->savePreference($firebase_preference);

            //SAVE ELASTIC MAIL API KEY PREFERENCES
            $elastic_mail_preference = BOL_PreferenceService::getInstance()->findPreference('elastic_mail_api_key');
            if(empty($elastic_mail_preference))
                $elastic_mail_preference = new BOL_Preference();

            $elastic_mail_preference->key = 'elastic_mail_api_key';
            $elastic_mail_preference->sortOrder = 1;
            $elastic_mail_preference->sectionName = 'general';
            $elastic_mail_preference->defaultValue =  $data['elastic_mail_api_key'];
            BOL_PreferenceService::getInstance()->savePreference($elastic_mail_preference);

        }
    }
}