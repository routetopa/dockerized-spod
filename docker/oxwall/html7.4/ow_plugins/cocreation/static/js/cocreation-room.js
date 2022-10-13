var room = document.querySelector('template[is="dom-bind"]');

room.inviteNewUsers = function() {
    previewFloatBox = OW.ajaxFloatBox('COCREATION_CMP_AddMembers', {roomId: window.location.pathname.split("/")[window.location.pathname.split("/").length - 1]}, {
        top: '60px',
        width: '60%',
        height: '480px',
        iconClass: 'ow_ic_add',
        title: ''
    });
};

window.addEventListener('datalet-slider-controllet_add', function (e) {
    ODE.pluginPreview = "cocreation";
    switch(COCREATION.room_type)
    {
        case "knowledge":
            previewFloatBox = OW.ajaxFloatBox('ODE_CMP_Preview', {} , {width:'90%', height:'90vh', iconClass:'ow_ic_lens', title:''});
            break;
        case "data":
            previewFloatBox = OW.ajaxFloatBox('COCREATION_CMP_AddDataletFromDataRoom', {dataUrl:ODE.ajax_coocreation_room_get_array_sheetdata} , {width:'90%', height:'90vh', iconClass:'ow_ic_lens', title:''});
            break;
    }
});

window.addEventListener('datalet-slider-controllet_delete', function (e) {
    var c = confirm(OW.getLanguageText('cocreation', 'confirm_delete_datalet'));
    if(c == true) {
        $.post(ODE.ajax_coocreation_room_delete_datalet,
            {
                dataletId: e.detail.dataletId,
                roomId: COCREATION.roomId,
                deletedPosition : room.$.datalets_slider.selected
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
});

window.addEventListener('info-list-controllet_delete_user', function(e){
    $.post(ODE.ajax_coocreation_room_delete_user,
        {
            userId:   e.detail.userId,
            roomId:   COCREATION.roomId,
            roomType: COCREATION.room_type
        },
        function (data, status) {
            data = JSON.parse(data);
            if (data.status == "ok") {
            } else {
                OW.info(OW.getLanguageText('cocreation', 'user_delete_fail'));
            }
        }
    );
});

room.init = function(){
    var socket = io(window.location.origin , {path: "/realtime_notification"/*, transports: [ 'polling' ]*/});
    socket.on('realtime_message_' + COCREATION.entity_type + "_" + COCREATION.roomId, function(rawData) {
        switch(rawData.operation) {
            case "addDatasetToRoom":
                room.loadDatasetsLibrary();
                room.$.syncMessage.innerHTML = OW.getLanguageText('cocreation', 'dataset_successfully_added');
                room.$.syncToast.show();
                break;
            case "deleteDataletFromRoom":
                room.$.datalets_slider.setDatalets(rawData.datalets);
                if (rawData.user_id == COCREATION.user_id) {
                    if (room.$.datalets_slider.selected == rawData.datalets.length && rawData.datalets.length > 0)
                        room.$.datalets_slider.setSelected(1);
                } else {
                    if (room.$.datalets_slider.selected == parseInt(rawData.deleted_position)) {
                        if (room.$.datalets_slider.selected == rawData.datalets.length && rawData.datalets.length > 0)
                            room.$.datalets_slider.setSelected(1);
                    } else if (room.$.datalets_slider.selected > parseInt(rawData.deleted_position))
                        room.$.datalets_slider.setSelected(room.$.datalets_slider.selected);
                }

                room.$.syncMessage.innerHTML = OW.getLanguageText('cocreation', 'datalet_successfully_deleted');
                room.$.syncToast.show();
                break;
            case "addDataletToRoom":
                room.$.datalets_slider.setDatalets(rawData.datalets);
                if (rawData.user_id == COCREATION.user_id) room.$.datalets_slider.setSelected(rawData.datalets.length);
                room.$.syncMessage.innerHTML = OW.getLanguageText('cocreation', 'datalet_successfully_added');
                room.$.syncToast.show();
                break;
            case "addPostitToDatalet":
                COCREATION.postits[rawData.dataletId] = rawData.postits;
                room.$.postits_controllet.setPostits(COCREATION.postits[rawData.dataletId]);
                room.$.syncMessage.innerHTML = OW.getLanguageText('cocreation', 'postit_successfully_added');
                room.$.syncToast.show();
                break;
            case "updateMetadata":
                if (rawData.user_id != COCREATION.user_id) {
                    COCREATION.metadata = rawData.metadata;
                    //room.$.metadata_component.setMetadata(COCREATION.metadata);
                    $("#metadata_iframe")[0].contentWindow.METADATA.realtime_metadata(COCREATION.metadata);

                    room.$.syncMessage.innerHTML = OW.getLanguageText('cocreation', 'metadata_successfully_updated');
                    room.$.syncToast.show();
                }
                break;
            case "deleteRoom":
                var redirect =  window.location.pathname.split("/");
                alert(OW.getLanguageText('cocreation', 'current_room_deleted'));
                window.location.href = window.location.origin + "/cocreation";
                break;
            case "deleteUser":
                room.$.syncMessage.innerHTML = OW.getLanguageText('cocreation', rawData.user_name + ' user_successfully_deleted');
                room.$.syncToast.show();
                break;
        }
    });
};
