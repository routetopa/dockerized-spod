<?php


class COCREATION_CMP_CreateRoom extends OW_Component
{
    public function __construct($room_type)
    {
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('cocreation')->getStaticJsUrl() . 'input-menu.js');
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('cocreation')->getStaticCssUrl() . 'input-menu.css');

        $friendsInfo = [];
        $users = BOL_UserService::getInstance()->findList(0,10000);

        foreach($users as $user)
        {
            $avatar = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($user->id));
            $user = BOL_UserService::getInstance()->findUserById($user->id);

            $friendsInfo[] = array(
                              "id" => $user->id,
                              "name" => filter_var(BOL_UserService::getInstance()->getDisplayName($user->id), FILTER_SANITIZE_SPECIAL_CHARS),
                              "username" => $user->username,
                              "email" => $user->email,
                              "avatar" => $avatar[$user->id]["src"],
                              "url" => $avatar[$user->id]["url"]
                            );
        }

        $this->assign('friends_info', json_encode($friendsInfo));
        $this->assign('components_url', SPODPR_COMPONENTS_URL);

        $form = new Form('CoCreationAddRoomForm');

        $name = new TextField('name');
        $name->setRequired(true);

        $subject  = new TextField('subject');
//        $subject->setRequired(true);

        $description  = new TextField('description');
//        $description->setRequired(true);

        $metadata = new Selectbox('metadata');
        $metadata->addOptions(array("1"=>"Common core", "2"=>"DCAT-AP_IT"));
        $metadata->setValue(2);
        $metadata->setRequired(true);

        $goal  = new TextField('goal');
//        $goal->setRequired(false);

        $invitationText  = new TextField('invitation_text');
//        $invitationText->setRequired(false);

        $usersValue  = new HiddenField('users_value');
        $usersValue->setValue("");
        $usersValue->setId('users_value');
//        $usersValue->setRequired(false);

        $managerOp  = new HiddenField('manager_op');
        $managerOp->setValue("requestToAddRoom");
        $managerOp->setId('manager_op');

        $roomType  = new HiddenField('room_type');
        $roomType->setId('room_type');
        $roomType->setValue($room_type);

        $submit = new Submit('submit');
        $submit->setId('submit_new_room');
        $submit->setValue(OW::getLanguage()->text('cocreation', 'create_room_button_label'));

        $form->addElement($name);
        $form->addElement($subject);
        $form->addElement($description);
        $form->addElement($metadata);
        $form->addElement($goal);
        $form->addElement($invitationText);
        $form->addElement($usersValue);
        $form->addElement($managerOp);
        $form->addElement($roomType);
        $form->addElement($submit);

        $form->setAction(OW::getRouter()->urlFor('COCREATION_CTRL_Ajax', 'createRoom'));
        $this->addForm($form);

        OW::getDocument()->addOnloadScript("
           COCREATION.init();
           $('#submit_new_room').click(function(){
              $('#submit_overlay').css('display','block')
           });
        ");

    }
}