$( document ).ready(function() {

    /*window.addEventListener('page-slider-controllet_selected', function(e){
        ln["localization"] = "en";

        if(e.srcElement.id == "slider_dataset") {
            switch (e.detail.selected) {
                case 0:
                    room.$.slider_dataset.setTitle(ln["slide1Title_" + ln["localization"]], ln["slide1Subtitle_" + ln["localization"]]);
                    room.$.slider_dataset.chevronLeft("invisible");
                    (room.$.dataset_selection.$.selected_url.invalid) ? room.$.slider_dataset.chevronRight(true) : room.$.slider_dataset.chevronRight(false);
                    break;
                case 1:
                    room.$.slider_dataset.setTitle(ln["slide2Title_" + ln["localization"]], ln["slide2Subtitle_" + ln["localization"]]);
                    room.$.slider_dataset.chevronLeft(true);
                    room.$.slider_dataset.chevronRight("invisible");
                    break;
            }
        }
    });

    window.addEventListener('datalet-slider-controllet_selected', function(e){
        room.$.postits_controllet.setPostits(COCREATION.postits[e.detail.dataletId], e.detail.dataletId);

    });

    window.addEventListener('data-ready', function(e) {
        if(e.detail.ready) {
            room.$.slider_dataset.chevronRight(true);
            room.$.dataset_selection.$.selected_url.invalid = false;
        }
        else
            room.$.dataset_selection.$.selected_url.invalid = true;

        room.$.dataset_selection.showDatasetInfo();
    });

    window.addEventListener('select-dataset-controllet_data-url', function(e){
        room.$.slider_dataset.chevronRight(false);
        room.$.select_data_controllet.dataUrl = e.detail.url;
        room.$.select_data_controllet.init();
    });

    window.addEventListener('select-fields-controllet_selected-fields', function(e){
        room.selectedDatasetFields = room.$.select_data_controllet.getSelectedFields();
    });


    window.addEventListener('datalet-slider-controllet_attached', function(e){
        room.$.datalets_slider.setDatalets(COCREATION.datalets);
    });*/

    window.addEventListener('info-list-controllet_attached', function(e){
        room.$.info_list_controllet.setInfo(COCREATION.info);
    });

});

room.splitScreenActive          = false;
room.current_selected_container = null;

room.handleSelectUIMode = function(mode){
    room.$.opera.style.visibility = (!room.splitScreenActive) ?  "hidden" : "visible";
    room.$.discussion.style.visibility  = 'hidden';
    room.$.info.style.visibility        = 'hidden';
    switch(mode){
        case 'opera':
            room.current_selected_container     = room.$.opera;
            room.$.opera.style.visibility       = "visible";
            break;
        case 'discussion':
            room.current_selected_container = room.$.discussion;
            room.$.discussion.style.visibility  = 'visible';
            SPODDISCUSSION.init();
            break;
        case 'info':
            room.current_selected_container = room.$.info;
            room.$.info.style.visibility     = 'visible';
            break;
        case 'split':
            room.$.split_checkbox.checked = !room.$.split_checkbox.checked;
            room.handleSplitScreen(room.$.split_checkbox);
            break;
    }
};

room.handleSplitScreen = function(e){
    room.splitScreenActive  = e.checked;
    if(room.splitScreenActive){//active split screen
        room.$.opera.disabled = true;
        room.$.opera.style.visibility      = "visible";
        //room.$.datalets.style.visibility = 'hidden';
        room.$.info.style.visibility       = 'hidden';
        room.$.discussion.style.visibility = 'visible';

        if(room.current_selected_container === null){
            room.current_selected_container  = room.$.opera;
            room.$.section_menu.selected     = 5;
        }
        room.current_selected_container.style.visibility = "visible";

        $(room.$.info).addClass("split_size_card_right");
        $(room.$.discussion).addClass("split_size_card_right");
        //$(room.$.datalets).addClass("split_size_card_right");
        $(room.$.opera).addClass("split_size_card_left");
    }else{
        room.$.opera.disabled = false;
        room.$.opera.style.visibility      = "visible";
        //room.$.datalets.style.visibility = 'hidden';
        room.$.info.style.visibility       = 'hidden';
        room.$.discussion.style.visibility = 'hidden';

        $(room.$.info).removeClass("split_size_card_right");
        $(room.$.discussion).removeClass("split_size_card_right");
        $(room.$.opera).removeClass("split_size_card_left");
        //$(room.$.datalets).removeClass("split_size_card_right");
    }
};

room.loadDiscussion = function(){
    $.post(OW.ajaxComponentLoaderRsp + "?cmpClass=COCREATION_CMP_DiscussionWrapper",
        {params: "[\"" + COCREATION.roomId + "\"]"},
        function (data, status) {
            data = JSON.parse(data);

            $('#discussion_container').html(data.content);
            //onloadScript
            var onload = document.createElement('script');
            onload.setAttribute("type","text/javascript");
            onload.innerHTML = data.onloadScript;
        });
};