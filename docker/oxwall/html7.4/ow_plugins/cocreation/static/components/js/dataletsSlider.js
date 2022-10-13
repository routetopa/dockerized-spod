$( document).ready(function(){
});

window.addEventListener('page-slider-controllet_selected', function (e) {

    if(e.srcElement !== undefined && e.srcElement.id == "slider_datalets") {

        room.$.slider_datalets = e.srcElement;

        try {
            room.$.slider_datalets.chevronLeft(true);
            room.$.slider_datalets.chevronRight(true);

            if (e.detail.selected == 0) {
                room.$.slider_datalets.chevronLeft(false);
                room.$.slider_datalets.chevronRight(true);
            } else if (e.detail.selected == ODE.numDataletsInCocreationRooom - 1) {
                room.$.slider_datalets.chevronLeft(true);
                room.$.slider_datalets.chevronRight(false);
            }
            room.$.slider_datalets.setTitle("Datalet " + (e.detail.selected + 1), "");
        }catch(e){console.log(e)}

        room.sliderRefreshCurrentDatalet();
    }
});

window.addEventListener('postit-container-controllet_create-new-postit', function(e){
    var dataletId = e.detail.id.replace("postit_","");
    $.post(ODE.ajax_coocreation_room_add_postit,
        {
            dataletId: dataletId,
            title: e.detail.title,
            content: e.detail.content
        },
        function (data, status) {
            data = JSON.parse(data);
            if (data.status == "ok") {
            }else{
                OW.info(OW.getLanguageText('cocreationep', 'postit_add_fail'));
            }
        }
    );
});

room._addDatalet = function(op){
    ODE.pluginPreview = "cocreation";
    switch(op)
    {
        case "knowledge":
            room.refreshDatasets();
            previewFloatBox = OW.ajaxFloatBox('ODE_CMP_Preview', {} , {width:'90%', height:'90vh', iconClass:'ow_ic_lens', title:''});
            break;
        case "data":
            previewFloatBox = OW.ajaxFloatBox('COCREATION_CMP_AddDataletFromDataRoom', {dataUrl:ODE.ajax_coocreation_room_get_array_sheetdata} , {width:'90%', height:'90vh', iconClass:'ow_ic_lens', title:''});
            break;
    }
};

//trick8
room.sliderRefreshCurrentDatalet = function() {

    setTimeout(function () {
        var datalet = $('div[id^="datalet_placeholder_'+ room.$.slider_datalets.selected + '"]').children()[1];

        if (datalet != undefined && datalet.behavior != undefined && datalet.nodeName != "DATATABLE-DATALET") {
            if (datalet.refresh != undefined)
                datalet.refresh();
            else
                datalet.behavior.presentData();
        }
    }, 1500);
};

room.initSlider = function(){
    var socket = io(window.location.origin , {path: "/realtime_notification"/*, transports: [ 'polling' ]*/});

    socket.on('realtime_message_' + COCREATION.entity_type + "_" + COCREATION.roomId, function(rawData) {
        switch(rawData.operation){
            case "addPostitToDatalet":
                $('#postit_container_' + rawData.dataletId ).html('<postit-container-controllet' +
                    ' id="postit_' + rawData.dataletId + '"' +
                    ' class="postit"' +
                    ' open=true' +
                    ' data=\'' + rawData.postits + '\'>' +
                    '</postit-container-controllet>');
                break;
        }
    });
};

room._handleDeleteClick = function(dataletId) {
    var c = confirm(OW.getLanguageText('cocreation', 'confirm_delete_datalet'));
    if(c == true) {
        $.post(ODE.ajax_coocreation_room_delete_datalet,
            {
                dataletId: dataletId,
                roomId: COCREATION.roomId
            },
            function (data, status) {
                data = JSON.parse(data);
                if (data.status == "ok") {
                } else {
                    OW.info(OW.getLanguageText('cocreation', 'datalet_delete_fail'));
                }
            }
        );
    }
};