<?php


class COCREATION_CMP_AddMembers extends OW_Component
{
    public function __construct($roomId)
    {
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

        //$this->assign('friends_info', json_encode($friendsInfo));
        $this->assign('friends_info', base64_encode(json_encode($friendsInfo)));
        $this->assign('components_url', SPODPR_COMPONENTS_URL);

        $form = new Form('CoCreationAddMembersForm');

        $usersValue  = new HiddenField('users_value');
        $usersValue->setValue("");
        $usersValue->setId('users_value');


        $submit = new Submit('submit');
        //$submit->setValue('submit');
        $submit->setId('add_members_button');
        $submit->setValue(OW::getLanguage()->text('cocreation', 'add_members'));


        $form->addElement($usersValue);
        $form->addElement($submit);

        $form->setAction(OW::getRouter()->urlFor('COCREATION_CTRL_Ajax', 'addNewMembersToRoom'). "?roomId=".$roomId);
        $this->addForm($form);

        $js = UTIL_JsGenerator::composeJsString("COCREATION.init();");
        OW::getDocument()->addOnloadScript($js);

    }
}