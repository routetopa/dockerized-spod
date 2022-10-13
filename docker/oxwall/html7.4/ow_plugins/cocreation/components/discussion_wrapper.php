<?php

/**
 * Created by PhpStorm.
 * User: Utente
 * Date: 28/10/2016
 * Time: 14.38
 */
class COCREATION_CMP_DiscussionWrapper extends OW_Component
{
    public function __construct($roomId)
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        //comment and rate
        $commentsParams = new SPODTCHAT_CLASS_CommentsParams('cocreation', COCREATION_BOL_Service::ROOM_ENTITY_TYPE);
        $commentsParams->setEntityId($roomId);
        $commentsParams->setDisplayType(BASE_CommentsParams::DISPLAY_TYPE_WITH_LOAD_LIST);
        //$commentsParams->setCommentCountOnPage(5);
        $commentsParams->setOwnerId((OW::getUser()->getId()));
        $commentsParams->setAddComment(true);
        $commentsParams->setWrapInBox(false);
        $commentsParams->setShowEmptyList(false);
        $commentsParams->setCommentPreviewMaxCharCount(5000);
        $commentsParams->setCommentEntityType(COCREATION_BOL_Service::COMMENT_ENTITY_TYPE);
        $commentsParams->setNumberOfNestedLevel(2);

        $commentsParams->level  = 0;
        $commentsParams->nodeId = 0;

        /* ODE */
        if (OW::getPluginManager()->isPluginActive('spodpr'))
            $this->addComponent('private_room', new SPODPR_CMP_PrivateRoomCard('ow_attachment_btn', array('datalet', 'link')));
        /* ODE */

        $commentCmp = new SPODTCHAT_CMP_Comments($commentsParams, 1, COCREATION_BOL_Service::COMMENT_ENTITY_TYPE, $roomId);
        $this->addComponent('comments', $commentCmp);

    }

}