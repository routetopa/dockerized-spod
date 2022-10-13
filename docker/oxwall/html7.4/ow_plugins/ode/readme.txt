For remove Sign Up link in up-right menu
ow_system_plugins/base/classes/console_event_handler.php
    Comment lines 101 - 102
    //$item = new BASE_CMP_ConsoleButton($language->text('base', 'console_item_sign_up_label'), OW::getRouter()->urlForRoute('base_join'));
    //$event->addItem($item, 1);

For restrict access only to logged-in user site-wide
/ow_core/application.php
    Add after line 134
    if ( !OW::getUser()->isAuthenticated() && strpos($uri, "openwall") === FALSE && strpos($uri, "openid") === FALSE && strpos($uri, "sign-in") === FALSE)
    {
    $this->redirect(OW::getRouter()->getBaseUrl() . "openwall");
    return;
    }

Add Polymer Polyfill to all the platform pages as first js
/ow_core/application.php
    Add after 527
    /* ODE */
    try
    {
        $document->addScript('http://deep.routetopa.eu/COMPONENTS/bower_components/webcomponentsjs/webcomponents-lite.js', 'text/javascript', (-100));
    }
    catch (InvalidArgumentException $e)
    {

    }
    /* ODE */