/* ODE PLUGIN JS */

ODE = {};

ODE.internationalization = {
    "add_datalet_it"  : "Aggiungi datalet",
    "open_my_space_it": "Apri My space",

    "add_datalet_en"  : "Add datalet",
    "open_my_space_en": "Open My space",

    "add_datalet_fr"  : "Ajouter visualisation",
    "open_my_space_fr": "Ouvrir Mon espace",

    "add_datalet_nl"  : "Add datalet",
    "open_my_space_nl": "Open My space"
};

ODE.init = function()
{
    /*window.addEventListener('DOMContentLoaded', function() {
        console.log("DOMContentLoaded");
    });

    window.addEventListener("load", function(event) {
        console.log("load");
    });*/

    //Hide like comment on Agora notification post (check if remove is faster than hide)
    $('.agora_notification').parent().parent().parent().find('.ow_newsfeed_left').css("display", "none");
    //$('.agora_notification').parent().parent().parent().find('.ow_newsfeed_left').remove();

    //Disable show/hide behaviour in newsfeed post
    $('.ow_newsfeed_context_menu_wrap, .ow_newsfeed_line').unbind("hover");

    ComponentService.components_url = ODE.deep_components;
    ComponentService.deep_url       = ODE.deep_url;

    window.addEventListener('my-space_card-selected', function(e){

        var datalet = e.detail.getAttribute("datalet-type");
        var params = JSON.parse(e.detail.getAttribute("datalet-preset"));
        var staticData = e.detail.getAttribute("datalet-data");

        var data = {
            detail: {
                data: {
                    datalet : datalet,
                    params  : params,
                    staticData : staticData
                }
            }
        };

        ODE.savedDataletListener(data);

    });

    window.addEventListener('generic-cards-container-controllet_card-selected', function(e){

        // var fields = e.detail.selectedElement.getAttribute("fields");
        // fields = fields.substring(1, fields.length-1).split('","');

        var datalet = e.detail.selectedElement.getAttribute("datalet");
        var params = JSON.parse(e.detail.selectedElement.getAttribute("preset"));
        var staticData = e.detail.selectedElement.getAttribute("static-data") ? e.detail.selectedElement.getAttribute("static-data") : JSON.stringify($(e.detail.selectedElement).find('#content').children()[0].data).replace(/'/g, "&#39;");

        var data = {
            detail: {
                data: {
                    datalet : datalet,
                    // fields  : fields,
                    params  : params,
                    staticData : staticData
                }
            }
        };

        ODE.savedDataletListener(data);

    });

    $('#open_window_button').click(function (e){
        ODE.pluginPreview = 'newsfeed';
    });

};

ODE.addOdeOnComment = function()
{
    var ta = $('.ow_comments_input textarea');
    $.each(ta, function(idx, obj) {
        if ( $(obj).attr('data-preview-added') ) {
            return;
        } else {
            $(obj).attr('data-preview-added', true);
        }
        var id = obj.id;

        // Add ODE on Comment
        var odeElem = $(obj).parent().find('.ow_attachments').first().prepend($('<a title="'+ODE.internationalization["add_datalet_"+ODE.user_language]+'" href="javascript://" style="background: url(' + ODE.THEME_IMAGES_URL + 'datalet_blue_rect.svg) no-repeat center;" data-id="' + id + '"></a>'));
        odeElem = odeElem.children().first();
        odeElem.click(function (e) {
            ODE.pluginPreview = 'comment';
            ODE.commentTarget = e.target;
            previewFloatBox = OW.ajaxFloatBox('ODE_CMP_Preview', {} , {width:'90%', height:'90vh', iconClass:'ow_ic_lens', title:''});
        });

        // Add PRIVATE_ROOM on Comment
        if(ODE.is_private_room_active)
        {
            var prElem = $(obj).parent().find('.ow_attachments').first().prepend($('<a title="'+ODE.internationalization["open_my_space_"+ODE.user_language]+'" href="javascript://" style="background: url(' + ODE.THEME_IMAGES_URL + 'myspace_blue_rect.svg) no-repeat center;" data-id="' + id + '"></a>'));
            prElem = prElem.children().first();
            prElem.click(function (e) {
                ODE.pluginPreview = 'comment';
                ODE.commentTarget = e.target;
                $('.ow_submit_auto_click').show();
                previewFloatBox = OW.ajaxFloatBox('SPODPR_CMP_PrivateRoomCardViewer', {data:['datalet']}, {top:'56px', width:'calc(100vw - 112px)', height:'calc(100vh - 112px)', iconClass: 'ow_ic_add', title: ''});
            });
        }

    });
};

// Listen for datalet event
ODE.savedDataletListener = function(e)
{
    var data = e.detail.data;
    ODE.setDataletValues(data);

    switch(ODE.pluginPreview)
    {
        case 'newsfeed' :
            $('#ode_controllet_placeholder').show('fast',()=>{
                ODE.loadDatalet(data.datalet, data.params, data.fields, data.staticData.replace(new RegExp("'", 'g'), " "), 'ode_controllet_placeholder');
            });
            break;
        case 'comment' :
            $(ODE.commentTarget).closest(".ow_tooltip_body").append($('<div class="comment_datalet_placeholder" id="'+$(ODE.commentTarget).attr("data-id")+'_placeholder" />'));
            ODE.loadDatalet(data.datalet, data.params, data.fields, data.staticData.replace(new RegExp("'", 'g'), " "), $(ODE.commentTarget).attr("data-id")+'_placeholder');
            //$(ODE.commentTarget).parent().first().prepend($('<a class="ode_done" style="background: url(' + ODE.THEME_IMAGES_URL + 'ic_ok_gray.svg) no-repeat center;"></a>'));
            break;
        case 'tchat' :
            $(ODE.commentTarget).closest(".ow_comments_form_wrap").append($('<div class="comment_datalet_placeholder" id="'+$(ODE.commentTarget).attr("data-id")+'_placeholder" />'));
            ODE.loadDatalet(data.datalet, data.params, data.fields, data.staticData.replace(new RegExp("'", 'g'), " "), $(ODE.commentTarget).attr("data-id")+'_placeholder');
            break;
        case 'agora' :
            AGORA.dataltet_preview_added();
            ODE.loadDatalet(data.datalet, data.params, data.fields, data.staticData.replace(new RegExp("'", 'g'), " "), ODE.commentTarget);
            break;
        case 'discussion' :
            SPODDISCUSSION.dataltet_preview_added();
            ODE.loadDatalet(data.datalet, data.params, data.fields, data.staticData.replace(new RegExp("'", 'g'), " "), ODE.commentTarget);
            break;
        case 'event' :
        case 'forum' :
            //$('.ode_done').first().append($('<div class="ode_done" style="background:url(' + ODE.THEME_IMAGES_URL + 'ic_ok_gray.svg) no-repeat center; height:20px; width:20px; float:left"></div>'));
            ODE.loadDatalet(data.datalet, data.params, data.fields, data.staticData.replace(new RegExp("'", 'g'), " "), 'datalet_placeholder');
            break;
        case 'private-room' :
            ODE.privateRoomDatalet();
            break;
        case 'cocreation':
            ODE.cocreationRoomDatalet();
            break;
        default : break;
    }

    if(typeof previewFloatBox != 'undefined')
        previewFloatBox.close();
};

ODE.cocreationRoomDatalet = function(){

    $.ajax({
        type: 'post',
        url: ODE.ajax_coocreation_room_add_datalet,
        data: ODE.dataletParameters,
        dataType: 'JSON',
        success: function(data){
            if(typeof previewFloatBox !== 'undefined') previewFloatBox.close();
            ODE.numDataletsInCocreationRooom++;
            window.dispatchEvent(new CustomEvent('ode-datalet-added-cocreation-room',{ detail : {'datalet_params' : ODE.dataletParameters, 'dataletId' : data.dataletId}}));
            ODE.reset();
        },
        error: function( XMLHttpRequest, textStatus, errorThrown ){
            OW.error(textStatus);
        },
        complete: function(){}
    });
};

ODE.privateRoomDatalet = function ()
{
    if(SPODPR.dataletOpened == undefined || SPODPR.cardOpened == undefined)
    {
        delete ODE.dataletParameters['dataletId'];
        delete ODE.dataletParameters['cardId'];
    }
    else
    {
        $.extend(ODE.dataletParameters, {dataletId: SPODPR.dataletOpened, cardId: SPODPR.cardOpened});
    }

    $.ajax({
        type: 'post',
        url: ODE.ajax_private_room_datalet,
        data: ODE.dataletParameters,
        dataType: 'JSON',
        success: function(data){
            previewFloatBox.close();

            if(ODE.dataletParameters.cardId == undefined)
                add_card(ODE.dataletParameters,data.cardId, data.dataletId);
            else
                replace_datalet_card(ODE.dataletParameters);
        },
        error: function( XMLHttpRequest, textStatus, errorThrown ){
            OW.error(textStatus);
        },
        complete: function(){}
    });
};

ODE.setDataletValues = function (data)
{
    let params = JSON.stringify($.extend(data.params, data.context));


    $('input[name=ode_datalet]').val(data.datalet);
    // $('input[name=ode_fields]').val('"'+data.fields.join('","')+'"');
    $('input[name=ode_params]').val(params);
    $('input[name=ode_data]').val(data.staticData);

    ODE.dataletParameters.component = data.datalet;
    ODE.dataletParameters.params    = params;
    // ODE.dataletParameters.fields    = '"'+data.fields.join('","')+'"';
    ODE.dataletParameters.data      = data.staticData;
    ODE.dataletParameters.comment   = data.params.description;
    ODE.dataletParameters.title     = data.params.datalettitle;
};

ODE.loadDatalet = function(component, params, fields, cache, placeholder)
{
    if(typeof cache == 'undefined' || cache == null)
        cache = '';
    else if(typeof cache != 'string')
        cache = JSON.stringify(cache).replace(/'/g, "&#39;");

    $.extend(params, {data: cache});

    // $.extend(params, {data:(!cache || typeof cache == 'undefined') ? '' : cache.replace("'", "&#39;")});

    ComponentService.getComponent({
        component   : component,
        params      : params,
        fields      : fields,
        placeHolder : placeholder
    });
};

ODE.odeLoadNewItem = function(params, preloader, id, callback)
{
    var self = window.ow_newsfeed_feed_list[id];

    if ( typeof preloader == 'undefined' )
    {
        preloader = true;
    }

    if (preloader)
    {
        var $ph = self.getPlaceholder();
        this.$listNode.prepend($ph);
    }
    this.loadItemMarkup(id, params, function($a) {
        this.$listNode.prepend($a.hide());

        if ( callback )
        {
            callback.apply(self);
        }

        self.adjust();
        if ( preloader )
        {
            var h = $a.height();
            $a.height($ph.height());
            $ph.replaceWith($a.css('opacity', '0.1').show());
            $a.animate({opacity: 1, height: h}, 'fast');
        }
        else
        {
            $a.animate({opacity: 'show', height: 'show'}, 'fast');
        }
    });

};

ODE.loadItemMarkup = function(id, params, callback)
{
    var self = window.ow_newsfeed_feed_list[id];

    params.feedData = self.data;
    params.cycle = params.cycle || {lastItem: false};

    params = JSON.stringify(params);

    NEWSFEED_Ajax(window.ODE.ajax_load_item, {p: params}, function( markup ) {

        if ( markup.result == 'error' )
        {
            return false;
        }

        var $m = $(markup.html);
        callback.apply(self, [$m]);
        OW.bindAutoClicks($m);

        self.processMarkup(markup);
    });
};

ODE.dataletParameters =
{
    component:'',
    params:'',
    fields:'',
    data:'',
    comment:''
};

ODE.commentSendMessage = function(message, context)
{
    var self = context;

    if(self.pluginKey == "spodpublic")
    {
        //1 neutral - 2 up - 3 down
        var sentiment = $("#comment_sentiment_"+self.entityId).attr('sentiment');
    }

    var dataToSend = {
        entityType: self.entityType,
        entityId: self.entityId,
        displayType: self.displayType,
        pluginKey: self.pluginKey,
        ownerId: self.ownerId,
        cid: self.uid,
        attchUid: self.attchUid,
        commentCountOnPage: self.commentCountOnPage,
        commentText: message,
        initialCount: self.initialCount,
        datalet: ODE.dataletParameters,
        plugin: ODE.pluginPreview,
        publicRoom: (typeof parent.ODE.publicRoom === 'undefined') ? '' : parent.ODE.publicRoom,
        sentiment: (typeof sentiment === 'undefined') ? '' : sentiment
    };

    if( self.attachmentInfo ){
        dataToSend.attachmentInfo = JSON.stringify(self.attachmentInfo);
    }
    else if( self.oembedInfo ){
        dataToSend.oembedInfo = JSON.stringify(self.oembedInfo);
    }

    $.ajax({
        type: 'post',
        //url: self.addUrl,
        url: ODE.ajax_add_comment,
        data: dataToSend,
        dataType: 'JSON',
        success: function(data){
            self.repaintCommentsList(data);

            //OW.trigger('base.photo_attachment_uid_update', {uid:self.attchUid, newUid:data.newAttachUid});
            OW.trigger('base.file_attachment', {uid:self.attchUid, newUid:data.newAttachUid});

            self.eventParams.commentCount = data.commentCount;
            OW.trigger('base.comment_added', self.eventParams);
            self.attchUid = data.newAttachUid;

            self.$formWrapper.removeClass('ow_preloader');
            self.$commentsInputCont.show();

            /* ODE */
            // Remove ic_ok icon from comment field
            $(ODE.commentTarget).parent().find('.ode_done').remove();
            $("#" + $(ODE.commentTarget).attr("data-id") + '_placeholder').remove();
            ODE.commentTarget = null;
            ODE.reset();
            /* ODE */

            $('.ow_file_attachment_preview').html("");

        },
        error: function( XMLHttpRequest, textStatus, errorThrown ){
            OW.error(textStatus);
        },
        complete: function(){

        }
    });

    self.$textarea.val('').keyup().trigger('input.autosize');
};

OwComments.prototype.initTextarea = function()
{
    OW.bind('base.update_attachment',
        function(data){
            if( data.uid == self.attchUid ){
                self.attachmentInfo = data;
                self.$textarea.focus();
                self.submitHandler = self.realSubmitHandler;
                OW.trigger('base.comment_attachment_added', self.eventParams);
            }
        }
    );

    /* ODE */
    ODE.reset();
    ODE.addOdeOnComment();
    /* ODE */

    var self = this;
    this.realSubmitHandler = function(){

        self.initialCount++;

        //self.sendMessage(self.$textarea.val());
        ODE.commentSendMessage(self.$textarea.val(), self);

        self.attachmentInfo = false;
        self.oembedInfo = false;
        self.$hiddenBtnCont.hide();
        if( this.mediaAllowed ){
            OWLinkObserver.getObserver(self.textAreaId).resetObserver();
        }
        self.$attchCont.empty();
        OW.trigger('base.photo_attachment_reset', {pluginKey:self.pluginKey, uid:self.attchUid});
        OW.trigger('base.comment_add', self.eventParams);

        self.$formWrapper.addClass('ow_preloader');
        self.$commentsInputCont.hide();

    };

    this.submitHandler = this.realSubmitHandler;

    this.$textarea
        .bind('keypress comment.test',
            function(e){
                if( e.isButton || (e.which === 13 && !e.shiftKey) ){
                    e.stopImmediatePropagation();
                    var textBody = $(this).val();

                    if ( $.trim(textBody) == '' && !self.attachmentInfo && !self.oembedInfo ){
                        OW.error(self.labels.emptyCommentMsg);
                        return false;
                    }

                    self.submitHandler();
                    return false;
                }
            }
        )
        .one('focus', function(){$(this).removeClass('invitation').val('').autosize({callback:function(data){OW.trigger('base.comment_textarea_resize', self.eventParams);}});});

    this.$hiddenBtnCont.unbind('click').click(function(){self.submitHandler();});

    if( this.mediaAllowed ){
        OWLinkObserver.observeInput(this.textAreaId, function( link ){
            if( !self.attachmentInfo ){
                self.$attchCont.html('<div class="ow_preloader" style="height: 30px;"></div>');
                this.requestResult( function( r ){
                    self.$attchCont.html(r);
                    self.$hiddenBtnCont.show();

                    OW.trigger('base.comment_attach_media', {})
                });
                this.onResult = function( r ){
                    self.oembedInfo = r;
                    if( $.isEmptyObject(r) ){
                        self.$hiddenBtnCont.hide();
                    }
                };
            }
        });
    }
};

ODE.reset = function()
{
    $('#ode_controllet_placeholder').hide();
    $('input[name=ode_datalet]').val("");
    $('input[name=ode_fields]').val("");
    $('input[name=ode_params]').val("");

    ODE.dataletParameters.component = "";
    ODE.dataletParameters.params    = "";
    ODE.dataletParameters.fields    = "";
    ODE.dataletParameters.data      = "";
    ODE.dataletParameters.comment   = "";

};

ODE.showHelper =  function() {
    var dialog = "";
    var name = self.location.pathname;
    //if(name.match(/\/public-room*/)) name = "/public-room";
    if (name.match(/\/agora\/*/)) {
        var address = self.location.pathname;
        var publicRoom;
        var last = address.charAt(address.length - 1);
        if (!isNaN(last))
            publicRoom = self.location.pathname;
        else
            name = "/agora";

    }

    //Issy issue
    //if (name.match(/\/*cocreation*/) || name.match(/\/*cocreation\/*/)) name = "/cocreation";
    if (name.match(/\/*data-room*/)) name = "/cocreation/data-room";
    if (name.match(/\/*knowledge*/)) name = "/cocreation/knowledge-room";



    switch(name){
        case "/spodpr":
            dialog = 'SPODPR_CMP_HelperMySpace';
            break;
        case "/index":
            dialog = 'ODE_CMP_HelperWhatsNew';
            break;
        case "/users":
            dialog = 'ODE_CMP_HelperUsers';
            break;
        case "/users/latest":
            dialog = 'ODE_CMP_HelperUsers';
            break;
        case "/users/online":
            dialog = 'ODE_CMP_HelperUsers';
            break;
        case "/users/search":
            dialog = 'ODE_CMP_HelperUsers';
            break;
        case "/cocreation":
            dialog = 'COCREATION_CMP_HelperCocreation';
            break;
        case "/cocreation/":
            dialog = 'COCREATION_CMP_HelperCocreation';
            break;
        case "/cocreation/knowledge-room":
            dialog = 'COCREATION_CMP_HelperCocreationKnowledgeRoom';
            break;
        case "/cocreation/data-room":
            dialog = 'COCREATION_CMP_HelperCocreationDataRoom';
            break;
        case "/agora":
            var frame_body = $("#public_room_iframe").contents().find("body").html();
            dialog = "SPODAGORA_CMP_HelperAgora";
            if(frame_body == undefined || frame_body == ""){
                dialog = "SPODAGORA_CMP_HelperAgora";
            }
            break;
        case publicRoom:
            dialog = "SPODAGORA_CMP_HelperPublicRoom";
            break;
        default:
            dialog = "ODE_CMP_HelperDefault";
            break;
    }

    //var d = dialog + "Nl";
    //OW.ajaxFloatBox(d, {} , {width:'90%', height:'70vh', iconClass:'ow_ic_lens', title:''});
    OW.ajaxFloatBox(dialog +  ODE.user_language.charAt(0).toUpperCase() +  ODE.user_language.slice(1) , {} , {width:'90%', height:'70vh', iconClass:'ow_ic_lens', title:''});
};