NOTIFICATION_SETTINGS = {};

NOTIFICATION_SETTINGS.init = function ()
{
    $(".notification_left").perfectScrollbar();
    $(".notification_right").perfectScrollbar();
    $(".right_panel_content").perfectScrollbar();
    $("#live_panel").perfectScrollbar();

    $(".notification_right_arrow").on('click', NOTIFICATION_SETTINGS.show_subaction);
    $(".notification_step").on('click', NOTIFICATION_SETTINGS.change_step);
    $(".notification_info_subaction_delete").on('click', NOTIFICATION_SETTINGS.delete_subaction);
    NOTIFICATION_SETTINGS.set_frequency();
    NOTIFICATION_SETTINGS.realtime_notifications();
};

NOTIFICATION_SETTINGS.realtime_notifications = function ()
{
    var socket = io(window.location.origin, {path: "/realtime_notification"});

    for(let i=0; i < NOTIFICATION_SETTINGS.agoras.length; i++)
    {
        socket.on('realtime_message_' + NOTIFICATION_SETTINGS.agoras[i].id, function (data) {
            let node =  $("#live_template").clone();
            node.find("a.style-scope").attr("href", data.user_url);
            node.find("img").attr("src", data.user_avatar);
            node.find(".right_panel_live_content_text").html(data.comment);
            node.find(".agora_link").html(NOTIFICATION_SETTINGS.agoras[i].subject);
            node.find(".agora_link").attr("href", window.location.origin + '/agora/' + NOTIFICATION_SETTINGS.agoras[i].id);
            node.show();
            $("#live_panel").prepend(node);
        });
    }
};

NOTIFICATION_SETTINGS.delete_subaction = function (e)
{
    let target = $(e.currentTarget);

    $.post(NOTIFICATION_SETTINGS.ajax_notification_register_user_for_action,
        {
            userId    : NOTIFICATION_SETTINGS.userId,
            plugin    : target.parent().attr("data-plugin"),
            action    : target.parent().attr("data-action"),
            type      : "mail",
            status    : "false"
        },
        function (data, status) {}
    );

    if(target.parents().eq(2).find(".notification_setting_subaction_panel").size() === 1)
        target.parents().eq(2).find(".notification_right_arrow").css("visibility","hidden");

    target.parents().eq(1).remove();
};

NOTIFICATION_SETTINGS.set_frequency = function ()
{
    $(".notification_step").each(function() {
        if($(this).attr("data-value") == $(this).parent().attr("data-frequency-selected"))
        {
            $(this).addClass("selected");
            $(this).parents().eq(3).find(".notification_subaction_frequency").attr("class",$(this).attr("class") + " notification_subaction_frequency");
        }
    });
};

NOTIFICATION_SETTINGS.change_step = function (e)
{
    let target = $(e.currentTarget);
    target.parent().find(".notification_step").removeClass("selected");
    target.addClass("selected");
    $(target).parents().eq(3).find(".notification_subaction_frequency").attr("class",$(target).attr("class") + " notification_subaction_frequency");

    let status = (target.attr("data-value") == 0) ? "false" : "true";

    $.post(NOTIFICATION_SETTINGS.ajax_notification_register_user_for_action,
        {
            userId    : NOTIFICATION_SETTINGS.userId,
            plugin    : target.parent().attr("data-plugin"),
            action    : target.parent().attr("data-action"),
            type      : "mail",
            frequency : target.attr("data-value"),
            status    : status
        },
        function (data, status) {}
    );
};

NOTIFICATION_SETTINGS.show_subaction = function (e)
{
    let target = $(e.currentTarget).parents().eq(3).find(".notification_setting_subaction_panel");
    let target_sub_container = $(e.currentTarget).parents().eq(3).find(".notification_info_subaction_container");

    if(!target[0].open) {
        $(e.currentTarget).css("animation", "rotate_90_cw 1s forwards");
        target[0].open = true;

        target.css("animation", "open_subaction 1s forwards");
        target_sub_container.css("transition", "opacity 1s ease-in");
        target_sub_container.delay(200).queue(function(next){
          $(this).css("opacity", 1);
          next();
        });
    }else{
        $(e.currentTarget).css("animation", "rotate_90_ccw 1s forwards");
        target[0].open = false;

        target_sub_container.css("transition", "opacity 0.2s ease-out");
        target_sub_container.css("opacity", 0);
        target.css("animation", "close_subaction 1s forwards");
    }
};

/*$(document).ready(function()
{
    NOTIFICATION_SETTINGS.init();
});*/