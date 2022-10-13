if (typeof define !== 'function') { var define = require('amdefine')(module) }

define( function(require,exports,module){

    /*

     #MenuView

     A list of available actions, given the current context.

     ## References
     * Sheet
     * SelectionCollection

     */

    var $ = require('jquery');
    var t = require('../templates');
    var RefBinder = require('ref-binder');
    var UploadImage = require('../views/upload_image');
    var MapDialog   = require('../views/map_dialog');
    var View = require('backbone').View;
    var _ = require('underscore');

    var CellMenuView = module.exports = View.extend({

        events: {
            'click .es-menu-button': 'onButtonClick'
        },

        uploadImageDialog : null,
        mapDialog: null,

        initialize: function(o){
            this.models = new RefBinder(this);
            this.data = o.data;
            this.cell = o.cell;
            this.table = o.table,
            this.setSheets(o.data.sheets || null);
            this.setUser(o.data.users.getCurrentUser());
            var current_sheet_id = this.getUser().getCurrentSheetId();
            this.setSheet(o.data.sheets.get(current_sheet_id) || null);

            this.uploadImageDialog =  new UploadImage({
                el: $('#es-modal-box'),
                cell: this.cell,
                table: this.table
            });
        },

        getSheet: function(){
            return this.models.get('sheet');
        },

        setSheet: function(sheet){
            this.models.set('sheet',sheet);
        },

        getSheets: function(){
            return this.models.get('sheets');
        },

        setSheets: function(sheets){
            this.models.set('sheets', sheets);
        },

        getUser: function(){
            return this.models.get('user');
        },

        setUser: function(user){
            this.models.set('user', user, {
                'change_current_sheet_id': 'onChangeCurrentSheetID',
            });
        },

        render: function(){
            this.$el.empty();
            this.$el.html(t.cell_menu());
        },

        onChangeCurrentSheetID: function(){
            var sheet = this.getSheets().get(this.getUser().getCurrentSheetId());
            this.setSheet(sheet);
        },

        onButtonClick: function(e){
            var action = $(e.currentTarget).data('action');
            switch(action){
                case 'add_geo_point':
                    this.addGeoPoint();
                    break;
                case 'add_image':
                    this.addImage();
                    break;
            }
        },

        sortRows:function(){
            this.getSheet().sortRows(this.col_id);
        },

        addGeoPoint:function(){
            //top.postMessage("open-select-merker-map_event", 'http://' + window.location.hostname);

            this.mapDialog =  new MapDialog({
                el: $('#es-modal-box'),
                cell: this.cell,
                table: this.table
            }).render();
        },

        addImage: function(){
            this.uploadImageDialog.render();
        }

    });

});
