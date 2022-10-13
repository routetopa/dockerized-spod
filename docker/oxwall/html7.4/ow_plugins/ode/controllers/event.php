<?php

class ODE_CTRL_Event extends OW_ActionController
{

    private $eventService;

    public function __construct()
    {
        parent::__construct();
        $this->eventService = EVENT_BOL_EventService::getInstance();
    }

    public function view( $params )
    {
        $event = $this->getEventForParams($params);

        $cmpId = UTIL_HtmlTag::generateAutoId('cmp');

        $this->assign('contId', $cmpId);

        if ( !OW::getUser()->isAuthorized('event', 'view_event') && $event->getUserId() != OW::getUser()->getId() )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('event', 'view_event');
            throw new AuthorizationException($status['msg']);
        }

        if ( $event->status != 1 && !OW::getUser()->isAuthorized('event') && $event->getUserId() != OW::getUser()->getId()  )
        {
            throw new Redirect403Exception();
        }

        // guest gan't view private events
        if ( (int) $event->getWhoCanView() === EVENT_BOL_EventService::CAN_VIEW_INVITATION_ONLY && !OW::getUser()->isAuthenticated() )
        {
            $this->redirect(OW::getRouter()->urlForRoute('event.private_event', array('eventId' => $event->getId())));
        }

        $eventInvite = $this->eventService->findEventInvite($event->getId(), OW::getUser()->getId());
        $eventUser = $this->eventService->findEventUser($event->getId(), OW::getUser()->getId());

        // check if user can view event
        if ( (int) $event->getWhoCanView() === EVENT_BOL_EventService::CAN_VIEW_INVITATION_ONLY && $eventUser === null && $eventInvite === null && !OW::getUser()->isAuthorized('event') )
        {
            $this->redirect(OW::getRouter()->urlForRoute('event.private_event', array('eventId' => $event->getId())));
        }

        $buttons = array();
        $toolbar = array();

        if ( OW::getUser()->isAuthorized('event') || OW::getUser()->getId() == $event->getUserId() )
        {
            $buttons = array(
                'edit' => array('url' => OW::getRouter()->urlForRoute('event.edit', array('eventId' => $event->getId())), 'label' => OW::getLanguage()->text('event', 'edit_button_label')),
                'delete' =>
                    array(
                        'url' => OW::getRouter()->urlForRoute('event.delete', array('eventId' => $event->getId())),
                        'label' => OW::getLanguage()->text('event', 'delete_button_label'),
                        'confirmMessage' => OW::getLanguage()->text('event', 'delete_confirm_message')
                    )
            );
        }

        $this->assign('editArray', $buttons);

        OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'event', 'main_menu_item');

        $moderationStatus = '';

        if ( $event->status == 2  )
        {
            $moderationStatus = " <span class='ow_remark ow_small'>(".OW::getLanguage()->text('event', 'moderation_status_pending_approval').")</span>";
        }

        $this->setPageHeading($event->getTitle(). $moderationStatus);
        $this->setPageTitle(OW::getLanguage()->text('event', 'event_view_page_heading', array('event_title' => $event->getTitle())));
        $this->setPageHeadingIconClass('ow_ic_calendar');
        OW::getDocument()->setDescription(UTIL_String::truncate(strip_tags($event->getDescription()), 200, '...'));

        $infoArray = array(
            'id' => $event->getId(),
            'image' => ( $event->getImage() ? $this->eventService->generateImageUrl($event->getImage(), false) : null ),
            'date' => UTIL_DateTime::formatSimpleDate($event->getStartTimeStamp(), $event->getStartTimeDisable()),
            'endDate' => $event->getEndTimeStamp() === null || !$event->getEndDateFlag() ? null : UTIL_DateTime::formatSimpleDate($event->getEndTimeDisable() ? strtotime("-1 day", $event->getEndTimeStamp()) : $event->getEndTimeStamp(),$event->getEndTimeDisable()),
            'location' => $event->getLocation(),
            'desc' => UTIL_HtmlTag::autoLink($event->getDescription()),
            'title' => $event->getTitle(),
            'creatorName' => BOL_UserService::getInstance()->getDisplayName($event->getUserId()),
            'creatorLink' => BOL_UserService::getInstance()->getUserUrl($event->getUserId()),
            'moderationStatus' => $event->status
        );

        /* ODE */
        $datalet = ODE_BOL_Service::getInstance()->getDataletByPostId($event->getId(), "event");

        if(!empty($datalet))
        {
            $infoArray['hasDatalet'] = true;

            // CACHE
/*            OW::getDocument()->addOnloadScript('ODE.loadDatalet("'.$datalet["component"].'",
                                                                    '.$datalet["params"].',
                                                                    ['.$datalet["fields"].'],
                                                                    \''.$datalet["data"].'\',
                                                                    "datalet_placeholder_' . $event->getId() .'_event");');*/

            // NO CACHE
            OW::getDocument()->addOnloadScript('ODE.loadDatalet("'.$datalet["component"].'",
                                                                    '.$datalet["params"].',
                                                                    ['.$datalet["fields"].'],
                                                                    undefined,
                                                                    "datalet_placeholder_' . $event->getId() .'_event");');
        }
        /* ODE */

        /* ODE */
        if(OW::getPluginManager()->isPluginActive('spodpr'))
            $this->addComponent('private_room', new SPODPR_CMP_PrivateRoomCard('ow_attachment_btn', array('datalet', 'link')));
        /* ODE */

        $this->assign('info', $infoArray);

        // event attend form
        if ( OW::getUser()->isAuthenticated() && $event->getEndTimeStamp() > time() )
        {
            if ( $eventUser !== null )
            {
                $this->assign('currentStatus', OW::getLanguage()->text('event', 'user_status_label_' . $eventUser->getStatus()));
            }
            $this->addForm(new AttendForm($event->getId(), $cmpId));

            $onloadJs = "
                var \$context = $('#" . $cmpId . "');
                $('#event_attend_yes_btn').click(
                    function(){
                        $('input[name=attend_status]', \$context).val(" . EVENT_BOL_EventService::USER_STATUS_YES . ");
                    }
                );
                $('#event_attend_maybe_btn').click(
                    function(){
                        $('input[name=attend_status]', \$context).val(" . EVENT_BOL_EventService::USER_STATUS_MAYBE . ");
                    }
                );
                $('#event_attend_no_btn').click(
                    function(){
                        $('input[name=attend_status]', \$context).val(" . EVENT_BOL_EventService::USER_STATUS_NO . ");
                    }
                );

                $('.current_status a', \$context).click(
                    function(){
                        $('.attend_buttons .buttons', \$context).fadeIn(500);
                    }
                );
            ";

            OW::getDocument()->addOnloadScript($onloadJs);
        }
        else
        {
            $this->assign('no_attend_form', true);
        }

        if ($event->status == EVENT_BOL_EventService::MODERATION_STATUS_ACTIVE && ( $event->getEndTimeStamp() > time() && ((int) $event->getUserId() === OW::getUser()->getId() || ( (int) $event->getWhoCanInvite() === EVENT_BOL_EventService::CAN_INVITE_PARTICIPANT && $eventUser !== null) ) ) )
        {
            $params = array(
                $event->id
            );

            $this->assign('inviteLink', true);
            OW::getDocument()->addOnloadScript("
                var eventFloatBox;
                $('#inviteLink', $('#" . $cmpId . "')).click(
                    function(){
                        eventFloatBox = OW.ajaxFloatBox('EVENT_CMP_InviteUserListSelect', " . json_encode($params) . ", {width:600, iconClass: 'ow_ic_user', title: " . json_encode(OW::getLanguage()->text('event', 'friends_invite_button_label')) . "});
                    }
                );
                OW.bind('base.avatar_user_list_select',
                    function(list){
                        eventFloatBox.close();
                        $.ajax({
                            type: 'POST',
                            url: " . json_encode(OW::getRouter()->urlFor('EVENT_CTRL_Base', 'inviteResponder')) . ",
                            data: 'eventId=" . json_encode($event->getId()) . "&userIdList='+JSON.stringify(list),
                            dataType: 'json',
                            success : function(data){
                                if( data.messageType == 'error' ){
                                    OW.error(data.message);
                                }
                                else{
                                    OW.info(data.message);
                                }
                            },
                            error : function( XMLHttpRequest, textStatus, errorThrown ){
                                OW.error(textStatus);
                            }
                        });
                    }
                );
            ");
        }

        if ( $event->status == EVENT_BOL_EventService::MODERATION_STATUS_ACTIVE )
        {
            $cmntParams = new BASE_CommentsParams('event', 'event');
            $cmntParams->setEntityId($event->getId());
            $cmntParams->setOwnerId($event->getUserId());
            $this->addComponent('comments', new BASE_CMP_Comments($cmntParams));
        }

        $this->addComponent('userListCmp', new EVENT_CMP_EventUsers($event->getId()));

        $event = new BASE_CLASS_EventCollector(EVENT_BOL_EventService::EVENT_COLLECT_TOOLBAR, array(
            "event" => $event
        ));

        OW::getEventManager()->trigger($event);

        $this->assign("toolbar", $event->getData());
    }

    public function add()
    {
        $language = OW::getLanguage();
        $this->setPageTitle($language->text('event', 'add_page_title'));
        $this->setPageHeading($language->text('event', 'add_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_add');

        OW::getDocument()->setDescription(OW::getLanguage()->text('event', 'add_event_meta_description'));

        OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'event', 'main_menu_item');

        // check permissions for this page
        if ( !OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('event', 'add_event') )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('event', 'add_event');
            throw new AuthorizationException($status['msg']);
        }

        $form = new ODEEventAddForm('event_add');

        /* ODE */
        if(OW::getPluginManager()->isPluginActive('spodpr'))
            $this->addComponent('private_room', new SPODPR_CMP_PrivateRoomCard('ow_attachment_btn_event', array('datalet', 'link')));
        /* ODE */

        if ( date('n', time()) == 12 && date('j', time()) == 31 )
        {
            $defaultDate = (date('Y', time()) + 1) . '/1/1';
        }
        else if ( ( date('j', time()) + 1 ) > date('t') )
        {
            $defaultDate = date('Y', time()) . '/' . ( date('n', time()) + 1 ) . '/1';
        }
        else
        {
            $defaultDate = date('Y', time()) . '/' . date('n', time()) . '/' . ( date('j', time()) + 1 );
        }

        $form->getElement('start_date')->setValue($defaultDate);
        $form->getElement('end_date')->setValue($defaultDate);
        $form->getElement('start_time')->setValue('all_day');
        $form->getElement('end_time')->setValue('all_day');

        $checkboxId = UTIL_HtmlTag::generateAutoId('chk');
        $tdId = UTIL_HtmlTag::generateAutoId('td');
        $this->assign('tdId', $tdId);
        $this->assign('chId', $checkboxId);

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin("event")->getStaticJsUrl() . 'event.js');
        OW::getDocument()->addOnloadScript("new eventAddForm(". json_encode(array('checkbox_id' => $checkboxId, 'end_date_id' => $form->getElement('end_date')->getId(), 'tdId' => $tdId )) .")");

        if ( OW::getRequest()->isPost() )
        {
            if ( !empty($_POST['endDateFlag']) )
            {
                $this->assign('endDateFlag', true);
            }

            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();

                $serviceEvent = new OW_Event(EVENT_BOL_EventService::EVENT_BEFORE_EVENT_CREATE, array(), $data);
                OW::getEventManager()->trigger($serviceEvent);
                $data = $serviceEvent->getData();

                $dateArray = explode('/', $data['start_date']);

                $startStamp = mktime(0, 0, 0, $dateArray[1], $dateArray[2], $dateArray[0]);

                if ( $data['start_time'] != 'all_day' )
                {
                    $startStamp = mktime($data['start_time']['hour'], $data['start_time']['minute'], 0, $dateArray[1], $dateArray[2], $dateArray[0]);
                }

                if ( !empty($_POST['endDateFlag']) && !empty($data['end_date']) )
                {
                    $dateArray = explode('/', $data['end_date']);
                    $endStamp = mktime(0, 0, 0, $dateArray[1], $dateArray[2], $dateArray[0]);

                    $endStamp = strtotime("+1 day", $endStamp);

                    if ( $data['end_time'] != 'all_day' )
                    {
                        $hour = 0;
                        $min = 0;

                        if( $data['end_time'] != 'all_day' )
                        {
                            $hour = $data['end_time']['hour'];
                            $min = $data['end_time']['minute'];
                        }

                        $dateArray = explode('/', $data['end_date']);
                        $endStamp = mktime($hour, $min, 0, $dateArray[1], $dateArray[2], $dateArray[0]);
                    }
                }

                $imageValid = true;
                $datesAreValid = true;
                $imagePosted = false;

                if ( !empty($_FILES['image']['name']) )
                {
                    if ( (int) $_FILES['image']['error'] !== 0 || !is_uploaded_file($_FILES['image']['tmp_name']) || !UTIL_File::validateImage($_FILES['image']['name']) )
                    {
                        $imageValid = false;
                        OW::getFeedback()->error($language->text('base', 'not_valid_image'));
                    }
                    else
                    {
                        $imagePosted = true;
                    }
                }

                if ( empty($endStamp) )
                {
                    $endStamp = strtotime("+1 day", $startStamp);
                    $endStamp = mktime(0, 0, 0, date('n',$endStamp), date('j',$endStamp), date('Y',$endStamp));
                }

                if ( !empty($endStamp) && $endStamp < $startStamp )
                {
                    $datesAreValid = false;
                    OW::getFeedback()->error($language->text('event', 'add_form_invalid_end_date_error_message'));
                }

                if ( $imageValid && $datesAreValid )
                {
                    $event = new EVENT_BOL_Event();
                    $event->setStartTimeStamp($startStamp);
                    $event->setEndTimeStamp($endStamp);
                    $event->setCreateTimeStamp(time());
                    $event->setTitle(htmlspecialchars($data['title']));
                    $event->setLocation(UTIL_HtmlTag::autoLink(strip_tags($data['location'])));
                    $event->setWhoCanView((int) $data['who_can_view']);
                    $event->setWhoCanInvite((int) $data['who_can_invite']);
                    $event->setDescription($data['desc']);
                    $event->setUserId(OW::getUser()->getId());
                    $event->setEndDateFlag( !empty($_POST['endDateFlag']) );
                    $event->setStartTimeDisable( $data['start_time'] == 'all_day' );
                    $event->setEndTimeDisable( $data['end_time'] == 'all_day' );

                    if ( $imagePosted )
                    {
                        $event->setImage(uniqid());
                    }

                    $serviceEvent = new OW_Event(EVENT_BOL_EventService::EVENT_ON_CREATE_EVENT, array(
                        'eventDto' => $event,
                        "imageValid" => $imageValid,
                        "imageTmpPath" => $_FILES['image']['tmp_name']
                    ));
                    OW::getEventManager()->trigger($serviceEvent);

                    $this->eventService->saveEvent($event);

                    /* ODE */
                    if (ODE_CLASS_Helper::validateDatalet($_REQUEST['ode_datalet'], $_REQUEST['ode_params'], $_REQUEST['ode_fields']))
                    {
                        ODE_BOL_Service::getInstance()->addDatalet(
                            $_REQUEST['ode_datalet'],
                            $_REQUEST['ode_fields'],
                            OW::getUser()->getId(),
                            $_REQUEST['ode_params'],
                            $event->id,
                            'event',
                            $_REQUEST['ode_data']);
                    }
                    /* ODE */


                    if ( $imagePosted )
                    {
                        $this->eventService->saveEventImage($_FILES['image']['tmp_name'], $event->getImage());
                    }

                    $eventUser = new EVENT_BOL_EventUser();
                    $eventUser->setEventId($event->getId());
                    $eventUser->setUserId(OW::getUser()->getId());
                    $eventUser->setTimeStamp(time());
                    $eventUser->setStatus(EVENT_BOL_EventService::USER_STATUS_YES);
                    $this->eventService->saveEventUser($eventUser);

                    OW::getFeedback()->info($language->text('event', 'add_form_success_message'));

//                    if ( $event->getWhoCanView() == EVENT_BOL_EventService::CAN_VIEW_ANYBODY )
//                    {
//                        $eventObj = new OW_Event('feed.action', array(
//                                'pluginKey' => 'event',
//                                'entityType' => 'event',
//                                'entityId' => $event->getId(),
//                                'userId' => $event->getUserId()
//                            ));
//                        OW::getEventManager()->trigger($eventObj);
//                    }

                    BOL_AuthorizationService::getInstance()->trackAction('event', 'add_event');


                    $serviceEvent = new OW_Event(EVENT_BOL_EventService::EVENT_AFTER_CREATE_EVENT, array(
                        'eventId' => $event->id,
                        'eventDto' => $event
                    ), array(

                    ));
                    OW::getEventManager()->trigger($serviceEvent);

                    $this->redirect(OW::getRouter()->urlForRoute('event.view', array('eventId' => $event->getId())));
                }
            }
        }

        if( empty($_POST['endDateFlag']) )
        {
            //$form->getElement('start_time')->addAttribute('disabled', 'disabled');
            //$form->getElement('start_time')->addAttribute('style', 'display:none;');

            $form->getElement('end_date')->addAttribute('disabled', 'disabled');
            $form->getElement('end_date')->addAttribute('style', 'display:none;');

            $form->getElement('end_time')->addAttribute('disabled', 'disabled');
            $form->getElement('end_time')->addAttribute('style', 'display:none;');
        }

        $this->addForm($form);
    }

    public function delete( $params )
    {
        $event = $this->getEventForParams($params);

        if ( !OW::getUser()->isAuthenticated() || ( OW::getUser()->getId() != $event->getUserId() && !OW::getUser()->isAuthorized('event') ) )
        {
            throw new Redirect403Exception();
        }

        $this->eventService->deleteEvent($event->getId());

        /* ODE */
        ODE_BOL_Service::getInstance()->deleteDataletsById($event->getId(), 'event');
        /* ODE */

        OW::getFeedback()->info(OW::getLanguage()->text('event', 'delete_success_message'));
        $this->redirect(OW::getRouter()->urlForRoute('event.main_menu_route'));
    }

    public function edit( $params )
    {
        $event = $this->getEventForParams($params);
        $datalet = ODE_BOL_Service::getInstance()->getDataletByPostId($params['eventId'], "event");
        $language = OW::getLanguage();
        $form = new ODEEventAddForm('event_edit');

        $form->getElement('title')->setValue($event->getTitle());
        $form->getElement('desc')->setValue($event->getDescription());
        $form->getElement('location')->setValue($event->getLocation());
        $form->getElement('who_can_view')->setValue($event->getWhoCanView());
        $form->getElement('who_can_invite')->setValue($event->getWhoCanInvite());
        $form->getElement('who_can_invite')->setValue($event->getWhoCanInvite());

        $startTimeArray = array('hour' => date('G', $event->getStartTimeStamp()), 'minute' => date('i', $event->getStartTimeStamp()));
        $form->getElement('start_time')->setValue($startTimeArray);

        $startDate = date('Y', $event->getStartTimeStamp()) . '/' . date('n', $event->getStartTimeStamp()) . '/' . date('j', $event->getStartTimeStamp());
        $form->getElement('start_date')->setValue($startDate);

        if ( $event->getEndTimeStamp() !== null )
        {
            $endTimeArray = array('hour' => date('G', $event->getEndTimeStamp()), 'minute' => date('i', $event->getEndTimeStamp()));
            $form->getElement('end_time')->setValue($endTimeArray);


            $endTimeStamp = $event->getEndTimeStamp();
            if ( $event->getEndTimeDisable() )
            {
                $endTimeStamp = strtotime("-1 day", $endTimeStamp);
            }

            $endDate = date('Y', $endTimeStamp) . '/' . date('n', $endTimeStamp) . '/' . date('j', $endTimeStamp);
            $form->getElement('end_date')->setValue($endDate);
        }

        if ( $event->getStartTimeDisable() )
        {
            $form->getElement('start_time')->setValue('all_day');
        }

        if ( $event->getEndTimeDisable() )
        {
            $form->getElement('end_time')->setValue('all_day');
        }

        $form->getSubmitElement('submit')->setValue(OW::getLanguage()->text('event', 'edit_form_submit_label'));

        $checkboxId = UTIL_HtmlTag::generateAutoId('chk');
        $tdId = UTIL_HtmlTag::generateAutoId('td');
        $this->assign('tdId', $tdId);
        $this->assign('chId', $checkboxId);

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin("event")->getStaticJsUrl() . 'event.js');
        OW::getDocument()->addOnloadScript("new eventAddForm(". json_encode(array('checkbox_id' => $checkboxId, 'end_date_id' => $form->getElement('end_date')->getId(), 'tdId' => $tdId )) .")");

        if ( $event->getImage() )
        {
            $this->assign('imgsrc', $this->eventService->generateImageUrl($event->getImage(), true));
        }

        $endDateFlag = $event->getEndDateFlag();

        if ( OW::getRequest()->isPost() )
        {
            $endDateFlag = !empty($_POST['endDateFlag']);

            //$this->assign('endDateFlag', !empty($_POST['endDateFlag']));

            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();

                $serviceEvent = new OW_Event(EVENT_BOL_EventService::EVENT_BEFORE_EVENT_EDIT, array('eventId' => $event->id), $data);
                OW::getEventManager()->trigger($serviceEvent);
                $data = $serviceEvent->getData();

                $dateArray = explode('/', $data['start_date']);

                $startStamp = mktime(0, 0, 0, $dateArray[1], $dateArray[2], $dateArray[0]);

                if ( $data['start_time'] != 'all_day' )
                {
                    $startStamp = mktime($data['start_time']['hour'], $data['start_time']['minute'], 0, $dateArray[1], $dateArray[2], $dateArray[0]);
                }

                if ( !empty($_POST['endDateFlag']) && !empty($data['end_date']) )
                {
                    $dateArray = explode('/', $data['end_date']);
                    $endStamp = mktime(0, 0, 0, $dateArray[1], $dateArray[2], $dateArray[0]);
                    $endStamp = strtotime("+1 day", $endStamp);

                    if ( $data['end_time'] != 'all_day' )
                    {
                        $hour = 0;
                        $min = 0;

                        if( $data['end_time'] != 'all_day' )
                        {
                            $hour = $data['end_time']['hour'];
                            $min = $data['end_time']['minute'];
                        }

                        $dateArray = explode('/', $data['end_date']);
                        $endStamp = mktime($hour, $min, 0, $dateArray[1], $dateArray[2], $dateArray[0]);
                    }
                }

                $event->setStartTimeStamp($startStamp);

                if ( empty($endStamp) )
                {
                    $endStamp = strtotime("+1 day", $startStamp);
                    $endStamp = mktime(0, 0, 0, date('n',$endStamp), date('j',$endStamp), date('Y',$endStamp));
                }

                if ( $startStamp > $endStamp )
                {
                    OW::getFeedback()->error($language->text('event', 'add_form_invalid_end_date_error_message'));
                    $this->redirect();
                }
                else
                {
                    $event->setEndTimeStamp($endStamp);

                    if ( !empty($_FILES['image']['name']) )
                    {
                        if ( (int) $_FILES['image']['error'] !== 0 || !is_uploaded_file($_FILES['image']['tmp_name']) || !UTIL_File::validateImage($_FILES['image']['name']) )
                        {
                            OW::getFeedback()->error($language->text('base', 'not_valid_image'));
                            $this->redirect();
                        }
                        else
                        {
                            $event->setImage(uniqid());
                            $this->eventService->saveEventImage($_FILES['image']['tmp_name'], $event->getImage());

                        }
                    }

                    $event->setTitle(htmlspecialchars($data['title']));
                    $event->setLocation(UTIL_HtmlTag::autoLink(strip_tags($data['location'])));
                    $event->setWhoCanView((int) $data['who_can_view']);
                    $event->setWhoCanInvite((int) $data['who_can_invite']);
                    $event->setDescription($data['desc']);
                    $event->setEndDateFlag(!empty($_POST['endDateFlag']));
                    $event->setStartTimeDisable( $data['start_time'] == 'all_day' );
                    $event->setEndTimeDisable( $data['end_time'] == 'all_day' );

                    $this->eventService->saveEvent($event);

                    $e = new OW_Event(EVENT_BOL_EventService::EVENT_AFTER_EVENT_EDIT, array('eventId' => $event->id));
                    OW::getEventManager()->trigger($e);

                    OW::getFeedback()->info($language->text('event', 'edit_form_success_message'));

                    /* ODE */
                    if (ODE_CLASS_Helper::validateDatalet($_REQUEST['ode_datalet'], $_REQUEST['ode_params'], $_REQUEST['ode_fields']))
                    {
                        ODE_BOL_Service::getInstance()->privateRoomDatalet(
                            $_REQUEST['ode_datalet'],
                            $_REQUEST['ode_fields'],
                            OW::getUser()->getId(),
                            $_REQUEST['ode_params'],
                            '',
                            $datalet['dataletId']);
                    }
                    /* ODE */

                    $this->redirect(OW::getRouter()->urlForRoute('event.view', array('eventId' => $event->getId())));
                }
            }
        }

        if( !$endDateFlag )
        {
            // $form->getElement('start_time')->addAttribute('disabled', 'disabled');
            // $form->getElement('start_time')->addAttribute('style', 'display:none;');

            $form->getElement('end_date')->addAttribute('disabled', 'disabled');
            $form->getElement('end_date')->addAttribute('style', 'display:none;');

            $form->getElement('end_time')->addAttribute('disabled', 'disabled');
            $form->getElement('end_time')->addAttribute('style', 'display:none;');
        }

        $this->assign('endDateFlag', $endDateFlag);

        $this->setPageHeading($language->text('event', 'edit_page_heading'));
        $this->setPageTitle($language->text('event', 'edit_page_title'));
        OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'event', 'main_menu_item');
        $this->addForm($form);

        if($datalet)
        {
            OW::getDocument()->addOnloadScript('ODE.loadDatalet("'.$datalet["component"].'",
                                                                    '.$datalet["params"].',
                                                                    ['.$datalet["fields"].'],
                                                                    undefined,
                                                                    "datalet_placeholder");');
        }

        /* ODE */
        if(OW::getPluginManager()->isPluginActive('spodpr'))
            $this->addComponent('private_room', new SPODPR_CMP_PrivateRoomCard('ow_attachment_btn_event', array('datalet', 'link')));
        /* ODE */
    }

    private function getEventForParams( $params )
    {
        if ( empty($params['eventId']) )
        {
            throw new Redirect404Exception();
        }

        $event = $this->eventService->findEvent($params['eventId']);

        if ( $event === null )
        {
            throw new Redirect404Exception();
        }

        return $event;
    }
    
}

class ODEEventAddForm extends Form
{

    const EVENT_NAME = 'event.event_add_form.get_element';

    public function __construct( $name )
    {
        parent::__construct($name);

        $militaryTime = Ow::getConfig()->getValue('base', 'military_time');

        $language = OW::getLanguage();

        $currentYear = date('Y', time());

        $title = new TextField('title');
        $title->setRequired();
        $title->setLabel($language->text('event', 'add_form_title_label'));

        $event = new OW_Event(self::EVENT_NAME, array( 'name' => 'title' ), $title);
        OW::getEventManager()->trigger($event);
        $title = $event->getData();

        $this->addElement($title);

        $startDate = new DateField('start_date');
        $startDate->setMinYear($currentYear);
        $startDate->setMaxYear($currentYear + 5);
        $startDate->setRequired();

        $event = new OW_Event(self::EVENT_NAME, array( 'name' => 'start_date' ), $startDate);
        OW::getEventManager()->trigger($event);
        $startDate = $event->getData();

        $this->addElement($startDate);

        $startTime = new EventTimeField('start_time');
        $startTime->setMilitaryTime($militaryTime);

        if ( !empty($_POST['endDateFlag']) )
        {
            $startTime->setRequired();
        }

        $event = new OW_Event(self::EVENT_NAME, array( 'name' => 'start_time' ), $startTime);
        OW::getEventManager()->trigger($event);
        $startTime = $event->getData();

        $this->addElement($startTime);

        $endDate = new DateField('end_date');
        $endDate->setMinYear($currentYear);
        $endDate->setMaxYear($currentYear + 5);

        $event = new OW_Event(self::EVENT_NAME, array( 'name' => 'end_date' ), $endDate);
        OW::getEventManager()->trigger($event);
        $endDate = $event->getData();

        $this->addElement($endDate);

        $endTime = new EventTimeField('end_time');
        $endTime->setMilitaryTime($militaryTime);

        $event = new OW_Event(self::EVENT_NAME, array( 'name' => 'end_time' ), $endTime);
        OW::getEventManager()->trigger($event);
        $endTime = $event->getData();

        $this->addElement($endTime);

        $location = new TextField('location');
        $location->setRequired();
        $location->setLabel($language->text('event', 'add_form_location_label'));

        $event = new OW_Event(self::EVENT_NAME, array( 'name' => 'location' ), $location);
        OW::getEventManager()->trigger($event);
        $location = $event->getData();

        $this->addElement($location);

        $whoCanView = new RadioField('who_can_view');
        $whoCanView->setRequired();
        $whoCanView->addOptions(
            array(
                '1' => $language->text('event', 'add_form_who_can_view_option_anybody'),
                '2' => $language->text('event', 'add_form_who_can_view_option_invit_only')
            )
        );
        $whoCanView->setLabel($language->text('event', 'add_form_who_can_view_label'));

        $event = new OW_Event(self::EVENT_NAME, array( 'name' => 'who_can_view' ), $whoCanView);
        OW::getEventManager()->trigger($event);
        $whoCanView = $event->getData();

        $this->addElement($whoCanView);

        $whoCanInvite = new RadioField('who_can_invite');
        $whoCanInvite->setRequired();
        $whoCanInvite->addOptions(
            array(
                EVENT_BOL_EventService::CAN_INVITE_PARTICIPANT => $language->text('event', 'add_form_who_can_invite_option_participants'),
                EVENT_BOL_EventService::CAN_INVITE_CREATOR => $language->text('event', 'add_form_who_can_invite_option_creator')
            )
        );
        $whoCanInvite->setLabel($language->text('event', 'add_form_who_can_invite_label'));

        $event = new OW_Event(self::EVENT_NAME, array( 'name' => 'who_can_invite' ), $whoCanInvite);
        OW::getEventManager()->trigger($event);
        $whoCanInvite = $event->getData();

        $this->addElement($whoCanInvite);

        $submit = new Submit('submit');
        $submit->setValue($language->text('event', 'add_form_submit_label'));
        $this->addElement($submit);

        $desc = new WysiwygTextarea('desc');
        $desc->setLabel($language->text('event', 'add_form_desc_label'));
        $desc->setRequired();

        $event = new OW_Event(self::EVENT_NAME, array( 'name' => 'desc' ), $desc);
        OW::getEventManager()->trigger($event);
        $desc = $event->getData();

        $this->addElement($desc);

        $imageField = new FileField('image');
        $imageField->setLabel($language->text('event', 'add_form_image_label'));
        $this->addElement($imageField);

        $event = new OW_Event(self::EVENT_NAME, array( 'name' => 'image' ), $imageField);
        OW::getEventManager()->trigger($event);
        $imageField = $event->getData();

        /* ODE */
        $odeButton = new Button('ode_open_dialog');
        //$odeButton->setValue(OW::getLanguage()->text('ode', 'add_ode_button'));
        $odeButton->setValue('');
        $this->addElement($odeButton);

        $field = new HiddenField('ode_datalet');
        $this->addElement($field);

        $field = new HiddenField('ode_fields');
        $this->addElement($field);

        $field = new HiddenField('ode_params');
        $this->addElement($field);

        $field = new HiddenField('ode_data');
        $this->addElement($field);

        $script = "ODE.pluginPreview = 'event';
        $('#{$odeButton->getId()}').click(function(e){
            previewFloatBox = OW.ajaxFloatBox('ODE_CMP_Preview', {} , {width:'90%', height:'90vh', iconClass: 'ow_ic_add', title: ''});
        });";

        OW::getDocument()->addOnloadScript($script);

        /* ODE */

        $this->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);
    }

}

class EventTimeField extends FormElement
{
    private $militaryTime;

    private $allDay = false;

    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct( $name )
    {
        parent::__construct($name);
        $this->militaryTime = false;
    }

    public function setMilitaryTime( $militaryTime )
    {
        $this->militaryTime = (bool) $militaryTime;
    }

    public function setValue( $value )
    {
        if ( $value === null )
        {
            $this->value = null;
        }

        $this->allDay = false;

        if ( $value === 'all_day' )
        {
            $this->allDay = true;
            $this->value = null;
            return;
        }

        if ( is_array($value) && isset($value['hour']) && isset($value['minute']) )
        {
            $this->value = array_map('intval', $value);
        }

        if ( is_string($value) && strstr($value, ':') )
        {
            $parts = explode(':', $value);
            $this->value['hour'] = (int) $parts[0];
            $this->value['minute'] = (int) $parts[1];
        }
    }

    public function getValue()
    {
        if ( $this->allDay === true )
        {
            return 'all_day';
        }

        return $this->value;
    }

    /**
     *
     * @return string
     */
    public function getElementJs()
    {
        $jsString = "var formElement = new OwFormElement('" . $this->getId() . "', '" . $this->getName() . "');";

        /** @var $value Validator  */
        foreach ( $this->validators as $value )
        {
            $jsString .= "formElement.addValidator(" . $value->getJsValidator() . ");";
        }

        return $jsString;
    }

    private function getTimeString( $hour, $minute )
    {
        if ( $this->militaryTime )
        {
            $hour = $hour < 10 ? '0' . $hour : $hour;
            return $hour . ':' . $minute;
        }
        else
        {
            if ( $hour == 12 )
            {
                $dp = 'pm';
            }
            else if ( $hour > 12 )
            {
                $hour = $hour - 12;
                $dp = 'pm';
            }
            else
            {
                $dp = 'am';
            }

            $hour = $hour < 10 ? '0' . $hour : $hour;
            return $hour . ':' . $minute . $dp;
        }
    }

    /**
     * @see FormElement::renderInput()
     *
     * @param array $params
     * @return string
     */
    public function renderInput( $params = null )
    {
        parent::renderInput($params);

        for ( $hour = 0; $hour <= 23; $hour++ )
        {
            $valuesArray[$hour . ':0'] = array('label' => $this->getTimeString($hour, '00'), 'hour' => $hour, 'minute' => 0);
            $valuesArray[$hour . ':30'] = array('label' => $this->getTimeString($hour, '30'), 'hour' => $hour, 'minute' => 30);
        }

        $optionsString = UTIL_HtmlTag::generateTag('option', array('value' => ""), true, OW::getLanguage()->text('event', 'time_field_invitation_label'));

        $allDayAttrs = array( 'value' => "all_day"  );

        if ( $this->allDay )
        {
            $allDayAttrs['selected'] = 'selected';
        }

        $optionsString = UTIL_HtmlTag::generateTag('option', $allDayAttrs, true, OW::getLanguage()->text('event', 'all_day'));

        foreach ( $valuesArray as $value => $labelArr )
        {
            $attrs = array('value' => $value);

            if ( !empty($this->value) && $this->value['hour'] === $labelArr['hour'] && $this->value['minute'] === $labelArr['minute'] )
            {
                $attrs['selected'] = 'selected';
            }

            $optionsString .= UTIL_HtmlTag::generateTag('option', $attrs, true, $labelArr['label']);
        }

        return UTIL_HtmlTag::generateTag('select', $this->attributes, true, $optionsString);
    }
}

class AttendForm extends Form
{

    public function __construct( $eventId, $contId )
    {
        parent::__construct('event_attend');
        $this->setAction(OW::getRouter()->urlFor('EVENT_CTRL_Base', 'attendFormResponder'));
        $this->setAjax();
        $hidden = new HiddenField('attend_status');
        $this->addElement($hidden);
        $eventIdField = new HiddenField('eventId');
        $eventIdField->setValue($eventId);
        $this->addElement($eventIdField);
        $this->setAjaxResetOnSuccess(false);
        $this->bindJsFunction(Form::BIND_SUCCESS, "function(data){
            var \$context = $('#" . $contId . "');



            if(data.messageType == 'error'){
                OW.error(data.message);
            }
            else{
                $('.current_status span.status', \$context).empty().html(data.currentLabel);
                $('.current_status span.link', \$context).css({display:'inline'});
                $('.attend_buttons .buttons', \$context).fadeOut(500);

                if ( data.eventId != 'undefuned' )
                {
                    OW.loadComponent('EVENT_CMP_EventUsers', {eventId: data.eventId},
                    {
                      onReady: function( html ){
                         $('.userList', \$context).empty().html(html);

                      }
                    });
                }

                $('.userList', \$context).empty().html(data.eventUsersCmp);
                OW.trigger('event_notifications_update', {count:data.newInvCount});
                OW.info(data.message);
            }
        }");
    }
}