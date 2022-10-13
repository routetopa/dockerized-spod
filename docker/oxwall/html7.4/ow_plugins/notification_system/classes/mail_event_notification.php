<?php

class SPODNOTIFICATION_CLASS_MailEventNotification extends SPODNOTIFICATION_CLASS_BaseEventNotification
{
    public static $TYPE = 'mail';

    private $html_mail;
    private $text_mail;
    private $subject_mail;

    public function __construct($plugin, $action, $subAction, $ownerId, $targetUserId = null, $subject_mail, $html_mail, $text_mail)
    {
        parent::__construct($plugin, $action, $subAction, $ownerId, $targetUserId);

        $this->subject_mail = $subject_mail;
        $this->html_mail    = $html_mail;
        $this->text_mail    = $text_mail;
    }

    public function send($targets)
    {
        $mail = new SPODNOTIFICATION_CLASS_ElasticMailSender($this, $targets);
        $mail->send();
    }

    public function getBasicMessage(){
        return $this->getHtmlMail();
    }

    public function getHtmlMail()
    {
        return $this->html_mail;
    }

    public function getTextMail()
    {
        return $this->text_mail;
    }

    public function getSubjectMail()
    {
        return $this->subject_mail;
    }

    public function setHtmlMail($html_mail)
    {
        $this->html_mail = $html_mail;
    }

    public function setTextMail($text_mail)
    {
        $this->text_mail = $text_mail;
    }


}