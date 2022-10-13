<?php

/**
 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.

 * ---
 * Copyright (c) 2011, Oxwall Foundation
 * All rights reserved.

 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the
 * following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice, this list of conditions and
 *  the following disclaimer.
 *
 *  - Redistributions in binary form must reproduce the above copyright notice, this list of conditions and
 *  the following disclaimer in the documentation and/or other materials provided with the distribution.
 *
 *  - Neither the name of the Oxwall Foundation nor the names of its contributors may be used to endorse or promote products
 *  derived from this software without specific prior written permission.

 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/**
 * Update Status Component
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package ow_plugins.newsfeed.components
 * @since 1.0
 */

class ODE_CMP_UpdateStatus extends NEWSFEED_CMP_UpdateStatus
{
    public function __construct( $feedAutoId, $feedType, $feedId, $actionVisibility = null )
    {
        parent::__construct($feedAutoId, $feedType, $feedId, $actionVisibility = null);

        // ADD DATALET DEFINITIONS
        $this->assign('datalet_definition_import', ODE_CLASS_Tools::getInstance()->get_all_datalet_definitions());

//        if(OW::getPluginManager()->isPluginActive('spodpr'))
//            $this->addComponent('private_room', new SPODPR_CMP_PrivateRoomCard('ow_attachment_btn', array('datalet', 'link')));
    }

    /**
     *
     * @param int $feedAutoId
     * @param string $feedType
     * @param int $feedId
     * @param int $actionVisibility
     * @return Form
     */
    public function createForm( $feedAutoId, $feedType, $feedId, $actionVisibility )
    {
        $form = parent::createForm($feedAutoId, $feedType, $feedId, $actionVisibility);

        $odeButton = new Button('ode_open_dialog');
//        $odeButton->setValue(OW::getLanguage()->text('ode', 'add_ode_button'));
        $odeButton->setValue("");
        $form->addElement($odeButton);

        $mySpaceButton = new Button('my_space');
        $mySpaceButton->setValue("");
        $form->addElement($mySpaceButton);

        $preference_maplet = BOL_PreferenceService::getInstance()->findPreference('maplet_is_visible_whatsnew');
        $preference_maplet_value = empty($preference_maplet->defaultValue) ? 0: $preference_maplet->defaultValue;
        $this->assign('maplet_visible', $preference_maplet_value);
        if($preference_maplet_value)
        {
            $mapButton = new Button('map_open_dialog');
            $mapButton->setValue("");
            $form->addElement($mapButton);
        }

        $preference_splod = BOL_PreferenceService::getInstance()->findPreference('splod_is_visible_whatsnew');
        $preference_splod_value = empty($preference_splod->defaultValue) ? 0 : $preference_splod->defaultValue;
        $this->assign('splod_visible', $preference_splod_value);
        if($preference_splod_value)
        {
            $splodButton = new Button('splod_open_dialog');
            $splodButton->setValue("");
            $form->addElement($splodButton);
        }

        $field = new HiddenField('ode_datalet');
        $form->addElement($field);

        $field = new HiddenField('ode_fields');
        $form->addElement($field);

        $field = new HiddenField('ode_params');
        $form->addElement($field);

        $field = new HiddenField('ode_data');
        $form->addElement($field);


        $script = "ODE.pluginPreview = 'newsfeed';
            $('#{$odeButton->getId()}').click(function(e){
                ODE.pluginPreview = 'newsfeed';
                //$('#ode_controllet_placeholder').slideToggle('fast');
                previewFloatBox = OW.ajaxFloatBox('ODE_CMP_Preview', {} , {top:'56px', width:'calc(100vw - 112px)', height:'calc(100vh - 112px)', iconClass: 'ow_ic_add', title: ''});
            });
            $('#{$mySpaceButton->getId()}').click(function(e){
                previewFloatBox = OW.ajaxFloatBox('SPODPR_CMP_PrivateRoomCardViewer', {data:['datalet']}, {top:'56px', width:'calc(100vw - 112px)', height:'calc(100vh - 112px)', iconClass: 'ow_ic_add', title: ''});
            });
        ";

        if($preference_maplet_value)
        {
            $script .= "$('#{$mapButton->getId()}').click(function(e){
                            previewFloatBox = OW.ajaxFloatBox('ODE_CMP_Preview', {component:'map-controllet'} , {top:'56px', width:'calc(100vw - 112px)', height:'calc(100vh - 112px)', iconClass: 'ow_ic_add', title: ''});
                        });";
        }

        if($preference_splod_value)
        {
            $script .= "$('#{$splodButton->getId()}').click(function(e){
                            previewFloatBox = OW.ajaxFloatBox('ODE_CMP_Preview', {component:'splod-visualization-controllet'} , {top:'56px', width:'calc(100vw - 112px)', height:'calc(100vh - 112px)', iconClass: 'ow_ic_add', title: ''});
                        });";
        }

        OW::getDocument()->addOnloadScript($script);

        $form->setAction( OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlFor('ODE_CTRL_Ajax', 'statusUpdate')) );

        return $form;
    }

}