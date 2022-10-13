var COCREATION = {};

COCREATION.friend_info;
COCREATION.searchField;
COCREATION.openDataPanel;

COCREATION.init = function()
{
    $('.floatbox_body').on('scroll', function(){COCREATION.hideAutocomplete()});
    COCREATION.friend_info = JSON.parse(atob($('#friends_info').val()));
    COCREATION.searchField = ["name", "username", "email"];

    // var scope = Polymer.dom(document).querySelector('#scope');
    // scope.dateFrom = scope.dateTo = new Date();
    // $("#data_from").val(moment(new Date()).format("LL")) ;

    // scope.dateFormat = function(date, format) {
    //     return moment(date).format(format);
    // };

    // scope.dismissDialog = function(event) {
    //     if (event.detail.confirmed){
    //         if(COCREATION.openDataPanel == "from")
    //             scope.dateFrom = scope.$.picker.date;
    //         else
    //             scope.dateTo = scope.$.picker.date;
    //     }
    // };
    //
    // scope.showDialog = function(e) {
    //     var args = e.target.getAttribute('data-args');
    //     COCREATION.openDataPanel = args;
    //     this.$.dialog.toggle();
    // };
};


COCREATION.autocomplete = function()
{
    var searchString = $("#members").val();
    var list = $("#suggested_friends");
    var needToShow = false;


    list.hide();

    if(searchString.length > 0)
    {
        if(searchString.indexOf(" ") >= 0 && COCREATION.isEmail(searchString))
        {
            COCREATION.addUser(searchString);
        }
        else
        {

            $("#suggested_friends_table").empty();

            var pos = $("#members").position();
            var h = $("#members").height();
            // var w = $("#members").width();

            // var top = 0;
            // var left = 0;
            //
            // var l = $("#add_label")[0];
            // if(l == undefined) {
            //     top = 48;
            //     left = 300;
            // }
            //
            // list.css({
            //     top: pos.top + h + top + 4,
            //     left: pos.left + left,
            //     position: 'absolute'
            // });

            list.css({
                top: pos.top + h + 4,
                left: pos.left,
                position: 'absolute'
            });

            for (var i = 0; i < COCREATION.friend_info.length; i++) {
                var elem = COCREATION.friend_info[i];

                for (var property in elem) {
                    if (elem.hasOwnProperty(property) && COCREATION.searchField.indexOf(property) >= 0 && elem[property].toLowerCase().indexOf(searchString.toLowerCase()) >= 0) {
                        var tr = COCREATION.createSuggestionList(elem, i);
                        $("#suggested_friends_table").append(tr);
                        needToShow = true;
                        break;
                    }
                }
            }

            if (needToShow)
                list.show();
        }
    }
};

COCREATION.hideAutocomplete = function()
{
    if($("#suggested_friends").is(":visible"))
    {
        $("#suggested_friends").hide();
        $("#members").blur();
    }
};

COCREATION.createSuggestionList = function(elem, friendIndex)
{
    var tr_template = $("#suggested_friends_tr_template").clone();
    tr_template = tr_template[0];
    tr_template.setAttribute("id", friendIndex);
    var avatar = $(".ow_avatar a", tr_template);
    avatar[0].setAttribute("href", elem["url"]);
    var avatar = $(".ow_avatar a img", tr_template);
    avatar[0].setAttribute("src", elem["avatar"]);
    var name = $(".suggested_name", tr_template);
    name[0].innerHTML = elem["name"];
    var email = $(".suggested_email", tr_template);
    email[0].innerHTML = elem["email"];
    $(tr_template).css("display", "block");

    return tr_template;
};

COCREATION.addUser = function(email)
{
    var div_template = $("#added_suggested_contact_template").clone();
    div_template = div_template[0];
    div_template.setAttribute("id", email);

    var name = $(".added_suggested_contact_text", div_template);
    name[0].innerHTML = email; // NAME

    $("#added_suggested_contact_container").append(div_template);
    $("#members").val("");

    var members_value = $("#users_value").val()+" "+email;
    $("#users_value").val(members_value);
};

COCREATION.addSuggestedUser = function(elem)
{
    var friendIndex = elem.getAttribute("id");

    var div_template = $("#added_suggested_contact_template").clone();
    div_template = div_template[0];
    div_template.setAttribute("id", COCREATION.friend_info[friendIndex]["email"]);

    var name = $(".added_suggested_contact_text", div_template);
    name[0].innerHTML = COCREATION.friend_info[friendIndex]["name"];

    $("#added_suggested_contact_container").append(div_template);
    $("#members").val("");

    var members_value = $("#users_value").val() + "#######" + COCREATION.friend_info[friendIndex]["email"];
    $("#users_value").val(members_value);
    //$("#members").width($("#members").width() - $(div_template).width() - 10); //PER AFFIANCARE CONTATTI A BARRA
};

COCREATION.removeFromSuggestionList = function(elem)
{
    $(elem).parent().remove();
};

COCREATION.isEmail = function(check)
{
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(check.trim());
};