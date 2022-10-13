/* MENU HANDLING */
var selected_menu = $(".cocoreation_create_room_left_menu_selected")[0];

$(document).on("click", ".cocoreation_create_room_left_menu li", function () {
    if (this == selected_menu)
        return;

    var last_input = ".in-" + $(selected_menu).attr("class").split(" ")[0];
    if ($(last_input + " :input").val() != "") {
        $(selected_menu).removeClass("error");
        $(selected_menu).addClass("check");
    }
    else {
        $(selected_menu).removeClass("check");
        $(selected_menu).addClass("error");
    }

    selected_menu = this;
    var clicked_class = $(this).attr('class').split(" ")[0];
    var in_class = ".in-" + clicked_class;
    $(".cocoreation_create_room_content .step").hide();
    $(".cocoreation_create_room_left_menu li").removeClass("cocoreation_create_room_left_menu_selected");
    $(this).addClass("cocoreation_create_room_left_menu_selected");
    $(in_class).show();
});
/* MENU HANDLING */
