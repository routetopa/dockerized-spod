<?php

class SPODNOTIFICATION_CLASS_ElasticMailSender extends OW_ActionController implements SPODNOTIFICATION_CLASS_ISender
{
    private static $elastic_api_url = 'https://api.elasticemail.com/v2/email/send';

    private $notification;
    private $targets;

    public function __construct($notification, $targets)
    {
        $this->notification = $notification;
        $this->targets      = $targets;
    }

    public function send()
    {
        $preference = BOL_PreferenceService::getInstance()->findPreference('elastic_mail_api_key');
        $api_key = empty($preference) ? "" : $preference->defaultValue;

        $template_html = OW::getPluginManager()->getPlugin('spodnotification')->getCmpViewDir() . 'email_notification_template_html.html';
        $template_text = OW::getPluginManager()->getPlugin('spodnotification')->getCmpViewDir() . 'email_notification_template_text.html';

        $date = getdate();
        $time = mktime(0, 0, 0, $date['mon'], $date['mday'], $date['year']);

        try
        {
            $post = array(
                'from'            => 'webmaster@routetopa.eu',
                'fromName'        => 'SPOD',
                'apikey'          => $api_key,
                'subject'         => $this->notification->getSubjectMail(),
                'isTransactional' => false);

            foreach ($this->targets as $target)
            {
                $post['to']       = $target->email;
                $post['bodyHtml'] = $this->getEmailContentHtml($target->userId, $this->notification->getHtmlMail(), $template_html, $time);
                $post['bodyText'] = $this->getEmailContentText($this->notification->getTextMail(), $template_text);

                $ch = curl_init();
                curl_setopt_array($ch, array(
                    CURLOPT_URL => self::$elastic_api_url,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => $post,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HEADER => false,
                    CURLOPT_SSL_VERIFYPEER => false
                ));

                $result = curl_exec ($ch);
                curl_close ($ch);
            }
        }
        catch ( Exception $e )
        {
            //
        }
    }

    private function getEmailContentHtml($userId, $content, $template, $time)
    {
        $this->setTemplate($template);

        $this->assign('userName', BOL_UserService::getInstance()->getDisplayName($userId));
        $this->assign('avatarUrl', BOL_AvatarService::getInstance()->getAvatarUrl($userId));
        $this->assign('string', $content);
        $this->assign('time', $time);

        return parent::render();
    }

    private function getEmailContentText($message, $template)
    {
        //SET EMAIL TEMPLATE
        $this->setTemplate($template);

        $this->assign('nl', '%%%nl%%%');
        $this->assign('tab', '%%%tab%%%');
        $this->assign('space', '%%%space%%%');
        $this->assign('string', $message);

        $content = parent::render();
        $search = array('%%%nl%%%', '%%%tab%%%', '%%%space%%%');
        $replace = array("\n", '    ', ' ');

        return str_replace($search, $replace, $content);
    }

}