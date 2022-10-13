<?php

/**
 * Created by PhpStorm.
 * User: Utente
 * Date: 01/03/2016
 * Time: 16.08
 */
class COCREATION_CMP_AddDatasetForm extends OW_Component
{
    public function __construct()
    {
        $form = new Form('CoCreationEpAddDatasetForm');

        $nameValidator = new StringValidator(0, 40);
        $nameValidator->setErrorMessage(OW::getLanguage()->text('cocreation', 'name_more_than_40_char'));

        $name = new TextField('name');
        $name->setId('dataset_name');
        $name->setDescription(OW::getLanguage()->text('cocreation', 'name_more_than_40_char'));
        $name->setRequired(true);
        $name->addValidator($nameValidator);

        $descriptionValidator = new StringValidator(0, 128);
        $descriptionValidator->setErrorMessage(OW::getLanguage()->text('cocreation', 'description_more_than_128_char'));

        $description  = new TextField('description');
        $description->setId('dataset_description');
        $description->setDescription(OW::getLanguage()->text('cocreation', 'description_max_128_char'));
        $description->setRequired(false);
        $description->addValidator($descriptionValidator);

        $submit = new Button('submit');
        $submit->setId('add_dataset_button');
        $submit->setValue(OW::getLanguage()->text('cocreation', 'add_dataset_button_label'));

        $form->addElement($name);
        $form->addElement($description);
        $form->addElement($submit);

        $this->addForm($form);

        OW::getDocument()->addOnloadScript('
           $("#add_dataset_button").click(function(){
              var event = new CustomEvent(\'create_dataset_form-form_submitted\',{ detail : {\'name\' : $(\'#dataset_name\').val(), \'description\' : $(\'#dataset_description\').val() }});
              window.dispatchEvent(event);
           });
        ');
    }

}