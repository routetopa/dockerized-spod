<?php

class SPODNOTIFICATION_CTRL_Test extends OW_ActionController
{
    private $API_ACCESS_KEY = 'AAAAXpjiVSk:APA91bEuKsj68o8MLkMHIC_wNv1ajzd0W-Nraikxz51TuG5NNImUyNKp2pD39posqmQXUXC-qeOgve6OwVQrgUweGRz32sqcZH70i1volkTp4qJv5EJ21GsQaMAHff3U5wgAn8iGxD6b';

    public function index()
    {
        $commentsParams = new BASE_CommentsParams('spodnotification', SPODTCHAT_BOL_Service::ENTITY_TYPE);
        $commentsParams->setEntityId(4096);
        $commentsParams->setDisplayType(BASE_CommentsParams::DISPLAY_TYPE_WITH_LOAD_LIST);
        $commentsParams->setCommentCountOnPage(5);
        $commentsParams->setOwnerId((OW::getUser()->getId()));
        $commentsParams->setAddComment(TRUE);
        $commentsParams->setWrapInBox(false);
        $commentsParams->setShowEmptyList(false);

        $commentsParams->level = 0;
        $commentsParams->nodeId = 0;

        SPODTCHAT_CMP_Comments::$NUMBER_OF_NESTED_LEVEL = 1;

        $commentCmp = new SPODTCHAT_CMP_Comments($commentsParams);
        $this->addComponent('comments', $commentCmp);
    }

    public function testFirebaseNotification(){

        $clean = ODE_CLASS_InputFilter::getInstance()->sanitizeInputs($_REQUEST);
        if ($clean == null){
            echo json_encode(array("status" => "error", "massage" => 'Insane inputs detected'));
            exit;
        }

        $notification_data = array(
            'user_id'    => '1',
            'comment_id' => '1'
        );

        $notification_body = array(
            'plugin' => 'agora',
            'action' => 'add_comment',
            'data'   => $notification_data
        );

        $notification = array
        (
            'title'	=> 'SPOD Local',
            'body' 	=> $notification_body
            /*'icon'	=> 'myicon',
            'sound' => 'mySound'*/
        );

        $fields = array
        (
            'to'		    => $clean['registrationId'],
            'notification'	=> $notification
        );


        $headers = array
        (
            'Authorization: key=' . $this->API_ACCESS_KEY,
            'Content-Type: application/json'
        );
        #Send Reponse To FireBase Server
        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
        $result = curl_exec($ch );
        curl_close( $ch );
        #Echo Result Of FireBase Server
        echo $result;
    }

}