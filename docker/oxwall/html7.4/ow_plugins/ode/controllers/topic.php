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
 * Forum topic action controller
 *
 * @author Egor Bulgakov <egor.bulgakov@gmail.com>
* @package ow.ow_plugins.forum.controllers
* @since 1.0
*/

class ODE_CTRL_Topic extends FORUM_CTRL_Topic
{

    private $forumService;

    public function __construct()
    {
        parent::__construct();

        $this->forumService = FORUM_BOL_ForumService::getInstance();

        if ( !OW::getRequest()->isAjax() )
        {
            OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'forum', 'forum');
        }
    }

    public function index( array $params )
    {
        if ( !isset($params['topicId']) || ($topicDto = $this->forumService->findTopicById($params['topicId'])) === null )
        {
            throw new Redirect404Exception();
        }

        if ( $topicDto != FORUM_BOL_ForumService::STATUS_APPROVED )
        {
            //throw new Redirect404Exception();
        }

        $forumGroup = $this->forumService->findGroupById($topicDto->groupId);
        $forumSection = $this->forumService->findSectionById($forumGroup->sectionId);

        $isHidden = $forumSection->isHidden;

        $userId = OW::getUser()->getId();
        $isOwner = ( $topicDto->userId == $userId ) ? true : false;

        $postReplyPermissionErrorText = null;

        if ( $isHidden )
        {
            $event = new OW_Event('forum.can_view', array(
                'entity' => $forumSection->entity,
                'entityId' => $forumGroup->entityId
            ), true);
            OW::getEventManager()->trigger($event);

            $canView = $event->getData();

            $isModerator = OW::getUser()->isAuthorized($forumSection->entity);

            $params = array('entity' => $forumSection->entity, 'entityId' => $forumGroup->entityId, 'action' => 'edit_topic');
            $event = new OW_Event('forum.check_permissions', $params);
            OW::getEventManager()->trigger($event);
            $canEdit = $event->getData();

            $params = array('entity' => $forumSection->entity, 'entityId' => $forumGroup->entityId, 'action' => 'add_topic');
            $event = new OW_Event('forum.check_permissions', $params);
            OW::getEventManager()->trigger($event);

            $canPost = $event->getData();

            $postReplyPermissionErrorText = OW::getLanguage()->text($forumSection->entity, 'post_reply_permission_error');

            $canMoveToHidden = BOL_AuthorizationService::getInstance()->isActionAuthorized($forumSection->entity, 'move_topic_to_hidden') && $isModerator;

            //$eventParams = array('pluginKey' => $forumSection->entity, 'action' => 'add_post');
            //TODO Zaph:create action that will check if user allowed to delete post separately from topic
        }
        else
        {
            $isModerator = OW::getUser()->isAuthorized('forum');

            $canView = OW::getUser()->isAuthorized('forum', 'view');
            $canEdit = $isOwner || $isModerator;
            $canPost = OW::getUser()->isAuthorized('forum', 'edit');
            $canMoveToHidden = BOL_AuthorizationService::getInstance()->isActionAuthorized('forum', 'move_topic_to_hidden') && $isModerator;

            //$eventParams = array('pluginKey' => 'forum', 'action' => 'add_post');
        }

        $canLock = $canSticky = $isModerator;

        if ( !$canView && !$isModerator )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('forum', 'view');
            throw new AuthorizationException($status['msg']);
        }

        if ( $forumGroup->isPrivate )
        {
            if ( !$userId )
            {
                throw new AuthorizationException();
            }
            else if ( !$isModerator )
            {
                if ( !$this->forumService->isPrivateGroupAvailable($userId, json_decode($forumGroup->roles)) )
                {
                    throw new AuthorizationException();
                }
            }
        }

        $page = !empty($_GET['page']) && (int) $_GET['page'] ? abs((int) $_GET['page']) : 1;

        //update topic's view count
        $topicDto->viewCount += 1;
        $this->forumService->saveOrUpdateTopic($topicDto);

        //update user read info
        $this->forumService->setTopicRead($topicDto->id, $userId);

        $topicInfo = $this->forumService->getTopicInfo($topicDto->id);
        $postList = $this->forumService->getTopicPostList($topicDto->id, $page);

        OW::getEventManager()->trigger(new OW_Event('forum.topic_post_list', array('list' => $postList)));

        $this->assign('isHidden', $isHidden);

        // adds forum caption if any
        if ( $isHidden )
        {
            $event = new OW_Event('forum.find_forum_caption', array('entity' => $forumSection->entity, 'entityId' => $forumGroup->entityId));
            OW::getEventManager()->trigger($event);

            $eventData = $event->getData();

            /** @var OW_Component $componentForumCaption */
            $componentForumCaption = $eventData['component'];

            if ( !empty($componentForumCaption) )
            {
                $this->assign('componentForumCaption', $componentForumCaption->render());
            }
            else
            {
                $componentForumCaption = false;
                $this->assign('componentForumCaption', $componentForumCaption);
            }

            $eParams = array('entity' => $forumSection->entity, 'entityId' => $forumGroup->entityId, 'action' => 'edit_topic');
            $event = new OW_Event('forum.check_permissions', $eParams);
            OW::getEventManager()->trigger($event);
            if ( $event->getData() )
            {
                $canLock = $canSticky = true;
            }
        }

        $this->assign('postReplyPermissionErrorText', $postReplyPermissionErrorText);
        $this->assign('isHidden', $isHidden);
        $this->assign('isOwner', $isOwner);
        $this->assign('canPost', $canPost);
        $this->assign('canLock', $canLock);
        $this->assign('canSticky', $canSticky);
        $this->assign('canSubscribe', OW::getUser()->isAuthorized('forum', 'subscribe'));
        $this->assign('isSubscribed', $userId && FORUM_BOL_SubscriptionService::getInstance()->isUserSubscribed($userId, $topicDto->id));

        if ( !$postList )
        {
            throw new Redirect404Exception();
        }

        $toolbars = array();
        $lang = OW::getLanguage();

        $langQuote = $lang->text('forum', 'quote');
        $langFlag = $lang->text('base', 'flag');
        $langEdit = $lang->text('forum', 'edit');
        $langDelete = $lang->text('forum', 'delete');

        $iteration = 0;
        $userIds = array();
        $postIds = array();
        $flagItems = array();

        $firstTopicPost = $this->forumService->findTopicFirstPost($topicDto->id);

        foreach ( $postList as &$post )
        {
            /* ODE */
            $datalet = ODE_BOL_Service::getInstance()->getDataletByPostId($post['id'], "forum");

            if(!empty($datalet))
            {
                $post['hasDatalet'] = true;

                // CACHE
/*                OW::getDocument()->addOnloadScript('ODE.loadDatalet("'.$datalet["component"].'",
                                                                    '.$datalet["params"].',
                                                                    ['.$datalet["fields"].'],
                                                                    \''.$datalet["data"].'\',
                                                                    "datalet_placeholder_' . $post['id']. '");');*/

                // NO CACHE
                OW::getDocument()->addOnloadScript('ODE.loadDatalet("'.$datalet["component"].'",
                                                                    '.$datalet["params"].',
                                                                    ['.$datalet["fields"].'],
                                                                    undefined,
                                                                    "datalet_placeholder_' . $post['id']. '");');

            }
            /* ODE */

            $post['text'] = UTIL_HtmlTag::autoLink($post['text']);
            $post['permalink'] = $this->forumService->getPostUrl($post['topicId'], $post['id'], true, $page);
            $post['number'] = ($page - 1) * $this->forumService->getPostPerPageConfig() + $iteration + 1;

            if ( $iteration == 0 )
            {
                $firstPostText = substr(htmlspecialchars(strip_tags($post['text'])), 0, 154);
            }

            // get list of users
            if ( !in_array($post['userId'], $userIds) )
                $userIds[$post['userId']] = $post['userId'];

            $toolbar = array();

            array_push($toolbar, array('class' => 'post_permalink', 'href' => $post['permalink'], 'label' => '#' . $post['number']));

            if ( $userId )
            {
                if ( !$topicDto->locked && ($canEdit || $canPost) )
                {
                    array_push($toolbar, array('id' => $post['id'], 'class' => 'quote_post', 'href' => 'javascript://', 'label' => $langQuote));
                }

                if ( $userId != (int) $post['userId'] )
                {
                    $lagItemKey = 'flag_' . $post['id'];
                    $flagItems[$lagItemKey] = array(
                        'id' => $post['id'],
                        'title' => $post['text'],
                        'href' => $this->forumService->getPostUrl($post['topicId'], $post['id'])
                    );

                    array_push($toolbar, array('label' => $langFlag, 'href' => 'javascript://', 'id' => $lagItemKey, 'class' => 'post_flag_item'));
                }
            }

            if ( $isModerator || ($userId == (int) $post['userId'] && !$topicDto->locked) )
            {
                $href = $iteration == 0 && $page == 1 ?
                    OW::getRouter()->urlForRoute('edit-topic', array('id' => $post['topicId'])) :
                    OW::getRouter()->urlForRoute('edit-post', array('id' => $post['id']));

                array_push($toolbar, array('id' => $post['id'], 'href' => $href, 'label' => $langEdit));

                if ( !($iteration == 0 && $page == 1) )
                {
                    array_push($toolbar, array('id' => $post['id'], 'class' => 'delete_post', 'href' => 'javascript://', 'label' => $langDelete));
                }

                if ( $iteration === 0 && !$isOwner && $isModerator && $topicInfo['status'] == FORUM_BOL_ForumService::STATUS_APPROVAL )
                {
                    $toolbar[] = array('id' => $topicInfo['id'], 'href' => OW::getRouter()->urlForRoute('forum_approve_topic', array('id' => $topicInfo['id'])), 'label' => OW::getLanguage()->text('forum', 'approve_topic'));
                }
            }

            $toolbars[$post['id']] = $toolbar;

            if ( count($post['edited']) && !in_array($post['edited']['userId'], $userIds) )
                $userIds[$post['edited']['userId']] = $post['edited']['userId'];

            $iteration++;

            array_push($postIds, $post['id']);
        }

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'jquery-fieldselection.js');

        $js = UTIL_JsGenerator::newInstance()
            ->newVariable('flagItems', $flagItems)
            ->jQueryEvent(
                '.post_flag_item a', 'click', 'var inf = flagItems[this.id];
                if (inf.id == '.$firstTopicPost->id.' ){
                    OW.flagContent("'.FORUM_BOL_ForumService::FEED_ENTITY_TYPE.'", '.$firstTopicPost->topicId.');
                }
                else{
                    OW.flagContent("'.FORUM_BOL_ForumService::FEED_POST_ENTITY_TYPE.'", inf.id);
                }'
            );

        OW::getDocument()->addOnloadScript($js, 1001);

        $this->assign('toolbars', $toolbars);

        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars($userIds);
        $this->assign('avatars', $avatars);

        $enableAttachments = OW::getConfig()->getValue('forum', 'enable_attachments');
        $this->assign('enableAttachments', $enableAttachments);

        $uid = uniqid();
        $addPostForm = $this->generateAddPostForm($topicDto->id, $uid);
        $this->addForm($addPostForm);

        /* ODE */
        if(OW::getPluginManager()->isPluginActive('spodpr'))
            $this->addComponent('private_room', new SPODPR_CMP_PrivateRoomCard('ow_attachment_btn', array('datalet', 'link')));
        /* ODE */

        $addPostInputId = $addPostForm->getElement('text')->getId();

        if ( $enableAttachments )
        {
            $attachments = FORUM_BOL_PostAttachmentService::getInstance()->findAttachmentsByPostIdList($postIds);
            $this->assign('attachments', $attachments);

            $attachmentCmp = new BASE_CLASS_FileAttachment('forum', $uid);
            $this->addComponent('attachmentsCmp', $attachmentCmp);
        }

        $plugin = OW::getPluginManager()->getPlugin('forum');

        $indexUrl = OW::getRouter()->urlForRoute('forum-default');
        $groupUrl = OW::getRouter()->urlForRoute('group-default', array('groupId' => $topicDto->groupId));
        $deletePostUrl = OW::getRouter()->urlForRoute('delete-post', array('topicId' => $topicDto->id, 'postId' => 'postId'));
        $stickyTopicUrl = OW::getRouter()->urlForRoute('sticky-topic', array('topicId' => $topicDto->id, 'page' => $page));
        $lockTopicUrl = OW::getRouter()->urlForRoute('lock-topic', array('topicId' => $topicDto->id, 'page' => $page));
        $deleteTopicUrl = OW::getRouter()->urlForRoute('delete-topic', array('topicId' => $topicDto->id));
        $getPostUrl = OW::getRouter()->urlForRoute('get-post', array('postId' => 'postId'));
        $moveTopicUrl = OW::getRouter()->urlForRoute('move-topic');
        $subscribeTopicUrl = OW::getRouter()->urlForRoute('subscribe-topic', array('id' => $topicDto->id));
        $unsubscribeTopicUrl = OW::getRouter()->urlForRoute('unsubscribe-topic', array('id' => $topicDto->id));

        $topicInfoJs = json_encode(array('sticky' => $topicDto->sticky, 'locked' => $topicDto->locked, 'ishidden' => $isHidden && !$canMoveToHidden));

        $onloadJs = "
			ForumTopic.deletePostUrl = '$deletePostUrl';
			ForumTopic.stickyTopicUrl = '$stickyTopicUrl';
			ForumTopic.lockTopicUrl = '$lockTopicUrl';
			ForumTopic.subscribeTopicUrl = '$subscribeTopicUrl';
			ForumTopic.unsubscribeTopicUrl = '$unsubscribeTopicUrl';
			ForumTopic.deleteTopicUrl = '$deleteTopicUrl';
			ForumTopic.getPostUrl = '$getPostUrl';
			ForumTopic.add_post_input_id = '$addPostInputId';
			ForumTopic.construct($topicInfoJs);
			";

        OW::getDocument()->addOnloadScript($onloadJs);

        OW::getDocument()->addScript($plugin->getStaticJsUrl() . "forum.js");

        // add language keys for javascript
        $lang->addKeyForJs('forum', 'sticky_topic_confirm');
        $lang->addKeyForJs('forum', 'unsticky_topic_confirm');
        $lang->addKeyForJs('forum', 'lock_topic_confirm');
        $lang->addKeyForJs('forum', 'unlock_topic_confirm');
        $lang->addKeyForJs('forum', 'delete_topic_confirm');
        $lang->addKeyForJs('forum', 'delete_post_confirm');
        $lang->addKeyForJs('forum', 'edit_topic_title');
        $lang->addKeyForJs('forum', 'edit_post_title');
        $lang->addKeyForJs('forum', 'move_topic_title');
        $lang->addKeyForJs('forum', 'confirm_delete_attachment');
        $lang->addKeyForJs('forum', 'forum_quote');
        $lang->addKeyForJs('forum', 'forum_quote_from');

        // first topic's post
        $postDto = $firstTopicPost;

        //posts count on page
        $count = $this->forumService->getPostPerPageConfig();

        $postCount = $this->forumService->findTopicPostCount($topicDto->id);
        $pageCount = ceil($postCount / $count);

        $groupSelect = $this->forumService->getGroupSelectList($topicDto->groupId, $canMoveToHidden, $userId);
        $moveTopicForm = $this->generateMoveTopicForm($moveTopicUrl, $groupSelect, $topicDto);
        $this->addForm($moveTopicForm);

        $Paging = new BASE_CMP_Paging($page, $pageCount, $count);

        $this->assign('paging', $Paging->render());

        if ( $isHidden )
        {
            OW::getNavigation()->deactivateMenuItems(OW_Navigation::MAIN);
            OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, $forumSection->entity, $eventData['key']);

            OW::getDocument()->setHeading(OW::getLanguage()->text($forumSection->entity, 'topic_page_heading', array(
                'topic' => $topicInfo['title'],
                'group' => $topicInfo['groupName'],
                'content' => ''
            )));

            $bcItems = array(
                array(
                    'href' => OW::getRouter()->urlForRoute('group-default', array('groupId' => $forumGroup->getId())),
                    'label' => OW::getLanguage()->text($forumSection->entity, 'view_all_topics')
                )
            );

            $breadCrumbCmp = new BASE_CMP_Breadcrumb($bcItems);
            $this->addComponent('breadcrumb', $breadCrumbCmp);
        }
        else
        {
            $bcItems = array(
                array(
                    'href' => $indexUrl,
                    'label' => $lang->text('forum', 'forum_index')
                ),
                array(
                    'href' => OW::getRouter()->urlForRoute('section-default', array('sectionId' => $topicInfo['sectionId'])),
                    'label' => $topicInfo['sectionName']
                ),
                array(
                    'href' => $groupUrl,
                    'label' => $topicInfo['groupName']
                )
            );

            $breadCrumbCmp = new BASE_CMP_Breadcrumb($bcItems, $lang->text('forum', 'topic_location'));
            $this->addComponent('breadcrumb', $breadCrumbCmp);

            OW::getDocument()->setHeading(OW::getLanguage()->text('forum', 'topic_page_heading', array(
                'topic' => $topicInfo['title'],
                'content' => $topicInfo['status'] == FORUM_BOL_ForumService::STATUS_APPROVED ? '' : OW::getLanguage()->text('forum', 'pending_approval')
            )));
        }

        OW::getDocument()->setHeadingIconClass('ow_ic_script');

        $this->assign('indexUrl', $indexUrl);
        $this->assign('groupUrl', $groupUrl);

        $this->assign('topicInfo', $topicInfo);

        $this->assign('postList', $postList);

        $this->assign('page', $page);

        $this->assign('userId', $userId);
        $this->assign('isModerator', $isModerator);
        $this->assign('canEdit', $canEdit);
        $this->assign('canMoveToHidden', $canMoveToHidden);

        // remember the last forum page
        OW::getSession()->set('last_forum_page', OW_URL_HOME . OW::getRequest()->getRequestUri());

        OW::getDocument()->setTitle($topicInfo['title']);
        OW::getDocument()->setDescription($firstPostText);

        $this->addComponent('search', new FORUM_CMP_ForumSearch(array('scope' => 'topic', 'topicId' => $topicDto->id)));

        $tb = array();

        $toolbarEvent = new BASE_CLASS_EventCollector('forum.collect_topic_toolbar_items', array(
            'topicId' => $topicDto->id,
            'topicDto' => $topicDto
        ));

        OW::getEventManager()->trigger($toolbarEvent);

        foreach ( $toolbarEvent->getData() as $toolbarItem )
        {
            array_push($tb, $toolbarItem);
        }
        $this->assign('tb', $tb);

    }

    protected function generateAddPostForm( $topicId, $uid )
    {
        $form = new FORUM_CLASS_PostForm(
            'add-post-form',
            $uid,
            $topicId,
            false
        );

        $form->setAction(OW::getRouter()->
        urlForRoute('add-post', array('topicId' => $topicId, 'uid' => $uid)));

        $this->addForm($form);

        $odeButton = new Button('ode_open_dialog');
        $odeButton->setValue(OW::getLanguage()->text('ode', 'add_ode_button'));
        $form->addElement($odeButton);

        $field = new HiddenField('ode_datalet');
        $form->addElement($field);

        $field = new HiddenField('ode_fields');
        $form->addElement($field);

        $field = new HiddenField('ode_params');
        $form->addElement($field);

        $field = new HiddenField('ode_data');
        $form->addElement($field);

        $script = "ODE.pluginPreview = 'forum';
        $('#{$odeButton->getId()}').click(function(e){
            previewFloatBox = OW.ajaxFloatBox('ODE_CMP_Preview', {} , {width:'90%', height:'90vh', iconClass: 'ow_ic_add', title: ''});
        });";

        OW::getDocument()->addOnloadScript($script);

        return $form;
    }

    private function generateMoveTopicForm( $actionUrl, $groupSelect, $topicDto )
    {
        $form = new Form('move-topic-form');

        $form->setAction($actionUrl);

        $topicIdField = new HiddenField('topic-id');
        $topicIdField->setValue($topicDto->id);
        $form->addElement($topicIdField);

        $group = new ForumSelectBox('group-id');
        $group->setOptions($groupSelect);
        $group->setValue($topicDto->groupId);
        $group->addAttribute("style", "width: 300px;");
        $group->setRequired(true);
        $form->addElement($group);

        $submit = new Submit('save');
        $submit->setValue(OW::getLanguage()->text('forum', 'move_topic_btn'));
        $form->addElement($submit);

        $form->setAjax(true);

        return $form;
    }

    public function addPost( array $params )
    {
        if ( !isset($params['topicId']) || !($topicId = (int) $params['topicId']) )
        {
            throw new Redirect404Exception();
        }

        $topicDto = $this->forumService->findTopicById($topicId);

        if ( !$topicDto )
        {
            throw new Redirect404Exception();
        }

        $uid = $params['uid'];

        $addPostForm = $this->generateAddPostForm($topicId, $uid);

        if ( OW::getRequest()->isPost() && $addPostForm->isValid($_POST) )
        {
            $data = $addPostForm->getValues();

            if ( $data['topic'] && $data['topic'] == $topicDto->id && !$topicDto->locked ) {
                if (!OW::getUser()->getId()) {
                    throw new AuthenticateException();
                }

                $postDto = $this->forumService->addPost($topicDto, $data);

                /* ODE */
                if (ODE_CLASS_Helper::validateDatalet($_REQUEST['ode_datalet'], $_REQUEST['ode_params'], $_REQUEST['ode_fields']))
                {
                    ODE_BOL_Service::getInstance()->addDatalet(
                        $_REQUEST['ode_datalet'],
                        $_REQUEST['ode_fields'],
                        OW::getUser()->getId(),
                        $_REQUEST['ode_params'],
                        $postDto->id,
                        'forum',
                        $_REQUEST['ode_data']);
                }
                /* ODE */

                $this->redirect($this->forumService->getPostUrl($topicId, $postDto->id));
            }
        }
        else
        {
            $this->redirect(OW::getRouter()->urlForRoute('topic-default', array('topicId' => $topicId)));
        }
    }

    public function deletePost( array $params )
    {
        if ( !isset($params['topicId']) || !($topicId = (int) $params['topicId']) || !isset($params['postId']) || !($postId = (int) $params['postId']) )
        {
            throw new Redirect404Exception();
        }

        $topicDto = $this->forumService->findTopicById($topicId);
        $postDto = $this->forumService->findPostById($postId);

        $userId = OW::getUser()->getId();
        $isModerator = OW::getUser()->isAuthorized('forum');

        $forumGroup = $this->forumService->findGroupById($topicDto->groupId);
        $forumSection = $this->forumService->findSectionById($forumGroup->sectionId);

        if ( $forumSection->isHidden )
        {
            $eParams = array('entity' => $forumSection->entity, 'entityId' => $forumGroup->entityId, 'action' => 'edit_topic');
            $event = new OW_Event('forum.check_permissions', $eParams);
            OW::getEventManager()->trigger($event);

            if ( $event->getData() )
            {
                $isModerator = true;
            }
        }

        if ( $topicDto && $postDto && ($postDto->userId == $userId || $isModerator) )
        {
            $prevPostDto = $this->forumService->findPreviousPost($topicId, $postId);

            if ( $prevPostDto )
            {
                $topicDto->lastPostId = $prevPostDto->id;
                $this->forumService->saveOrUpdateTopic($topicDto);

                $this->forumService->deletePost($postId);
                $postUrl = $this->forumService->getPostUrl($topicId, $prevPostDto->id, false);

                /* ODE */
                ODE_BOL_Service::getInstance()->deleteDataletsById($postId, 'forum');
                /* ODE */
            }
        }
        else
        {
            $postUrl = $this->forumService->getPostUrl($topicId, $postId, false);
        }

        $this->redirect($postUrl);
    }

    public function deleteTopic( array $params )
    {
        if ( !isset($params['topicId']) || !($topicId = (int) $params['topicId']) )
        {
            throw new Redirect404Exception();
        }

        $isModerator = OW::getUser()->isAuthorized('forum');

        $topicDto = $this->forumService->findTopicById($topicId);
        $userId = OW::getUser()->getId();

        $redirectUrl = OW::getRouter()->urlForRoute('topic-default', array('topicId' => $topicId));

        if ( $topicDto )
        {
            $forumGroup = $this->forumService->findGroupById($topicDto->groupId);
            $forumSection = $this->forumService->findSectionById($forumGroup->sectionId);

            if ( $forumSection->isHidden )
            {
                $eParams = array('entity' => $forumSection->entity, 'entityId' => $forumGroup->entityId, 'action' => 'edit_topic');
                $event = new OW_Event('forum.check_permissions', $eParams);
                OW::getEventManager()->trigger($event);

                if ( $event->getData() )
                {
                    $isModerator = true;
                }
            }

            if ( $isModerator || $userId == $topicDto->userId )
            {
                /* ODE */
                ODE_BOL_Service::getInstance()->deleteDataletsById($topicId, 'topic');
                /* ODE */

                $groupId = $topicDto->groupId;
                $this->forumService->deleteTopic($topicId);

                $redirectUrl = OW::getRouter()->urlForRoute('group-default', array('groupId' => $groupId));
            }
        }

        $this->redirect($redirectUrl);
    }

}