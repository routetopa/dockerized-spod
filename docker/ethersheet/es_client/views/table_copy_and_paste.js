if (typeof define !== 'function') { var define = require('amdefine')(module) }
define( function(require,exports,module){

    var $         = require('jquery');
    var RefBinder = require('ref-binder');
    var View      = require('backbone').View;
    var self;

    var TableCopyAndPasteFeature = module.exports = View.extend({


        events: {
            'mousedown .es-table-cell': 'cellsSelectionMousedown',
            'mouseover .es-table-cell': 'cellsSelectionMouseover',
            'mouseup .es-table-cell'  : 'cellsSelectionMouseup',
            'dblclick .es-table-cell' : 'cellClicked',
            'keydown textarea': 'inputKeydown'
        },

        es: null,
        table: null,
        data: null,
        model: null,
        isMouseDown: false,
        startColIndex: 0,
        startRowIndex:  0,
        rowStart: 0,
        rowEnd: 0,
        riwIndex: null,
        colStart: 0,
        colEnd: 0,
        colIndex: 0,
        current_cell: null,

        initialize: function(o){
            this.models = new RefBinder(this);
            this.table = o.table;
            this.data  = o.data;
            this.es    = o.es;
            this.setSheet(o.data.sheets.get(o.data.users.getCurrentUser().getCurrentSheetId()) || null);
            self = this;

            //set copy,paste and cut handlers
            ['copy','paste','cut'].forEach(function(event) {
                document.addEventListener(event, function(e) {
                    switch(event) {
                        case 'copy':
                            self.onCopy(e);
                            break;
                        case 'paste':
                            self.onPaste(e);
                            break;
                        case 'cut':
                            self.onCut(e);
                    }
                });
            });

            //bind keyword event
            this.es.keyboard.on('37',this.cellsSelectionKeydown);//left
            this.es.keyboard.on('38',this.cellsSelectionKeydown);//up
            this.es.keyboard.on('39',this.cellsSelectionKeydown);//right
            this.es.keyboard.on('40',this.cellsSelectionKeydown);//down
            this.es.keyboard.on('shift_37',this.cellsSelectionKeydown);
            this.es.keyboard.on('shift_38',this.cellsSelectionKeydown);
            this.es.keyboard.on('shift_39',this.cellsSelectionKeydown);
            this.es.keyboard.on('shift_40',this.cellsSelectionKeydown);
            this.es.keyboard.on('13',this.cellsSelectionKeydown);//enter
            this.es.keyboard.on('27',this.cellsSelectionKeydown);//escape
            this.es.keyboard.on('46',this.cellsSelectionKeydown);//canc
        },

        getSheet: function(){
            return this.models.get('sheet');
        },

        setSheet: function(sheet){
            this.models.set('sheet',sheet);
        },

        getId: function(){
            //userd to get cid
            return this.data.users.getCurrentUser().getCurrentSheetId();
        },

        selectTo: function(cell) {
            var currentColIndex = cell.index();
            currentColIndex = (currentColIndex < 0) ? this.startColIndex : currentColIndex;
            var currentRowIndex = cell.parent().index();
            currentRowIndex = (currentRowIndex < 0) ? this.startRowIndex : currentRowIndex;

            if (currentRowIndex < this.startRowIndex) {
                this.rowStart = currentRowIndex;
                this.rowEnd = this.startRowIndex;
            } else {
                this.rowStart = this.startRowIndex;
                this.rowEnd = currentRowIndex;
            }

            if (currentColIndex < this.startColIndex) {
                this.colStart = currentColIndex;
                this.colEnd = this.startColIndex;
            } else {
                this.colStart = this.startColIndex;
                this.colEnd = currentColIndex;
            }

            for (var i = this.rowStart; i <= this.rowEnd; i++) {
                var rowCells = $('#es-grid-'+this.getId()).find("tr").eq(i).find("td");
                for (var j = this.colStart; j <= this.colEnd; j++) {
                    rowCells.eq(j).addClass("cpselected");
                }
            }
        },

        resetSelection: function(cell){
            try {
                this.current_cell = cell;
                this.table.$grid.find(".cpselected").removeClass("cpselected"); // deselect everything
                cell.addClass("cpselected");
                this.colIndex = this.colStart = this.colEnd = this.startColIndex = cell.index();
                this.rowIndex = this.rowStart = this.rowEnd = this.startRowIndex = cell.parent().index();
            }catch(e){}
        },

        cellsSelectionMousedown: function(e)
        {
            //user try to resize the cell
            this.table.setCellDragTarget(e);
            if (this.table.isDraggingCell()){
                return false;
            }

            if(e.which == 3){
                this.table.$grid.find(".cpselected").removeClass("cpselected"); // deselect everything
                this.table.showCellMenu(e);
                return false;
            }

            this.table.clearOverlays();
            this.isMouseDown = true;
            this.editingCell = false;

            if (e.shiftKey) {
                this.selectTo(cell);
            } else {
                this.resetSelection( $(e.currentTarget));
            }
            return true; // prevent text selection
        },

        cellsSelectionMouseover: function(e){
            if (!this.isMouseDown) return;
            this.table.$grid.find(".cpselected").removeClass("cpselected");
            this.selectTo($(e.currentTarget));
        },

        cellsSelectionMouseup: function(e){
            if(this.table.isDraggingCell()) this.table.cellMouseUp(e);
            this.isMouseDown = false;
        },

        cellClicked: function(e){
           this.editingCell = true;
        },

        //KEYBOARD EVENTS STUFFS

        //hendler for cell editing
        inputKeydown: function(e){

            console.log(e);
            //return unless code is 'enter' or 'tab'
            if(self.editingCell == true){
                e.stopPropagation();
            }

            var code = (e.keyCode ? e.keyCode : e.which);
            if(code != 13 && code != 9 && code != 27) return true;

            //to fix
            /*var cells = this.table.getLocalSelection().getCells();
            _.each(cells, function(cell){
                self.getSheet().commitCell(cell.row_id.toString(), cell.col_id.toString());
            }, this);*/

            switch(code){
                case 13://ENTER
                    self.table.moveSelection(e,1,0);
                    self.editingCell = true;
                    break;
                case 9://TAB
                    self.table.moveSelection(e,0,1);//right
                    break;
                case 27://ESC
                    self.table.clearOverlays();
                    self.editingCell = false;
                    self.resetSelection(self.current_cell);
                    break;
            }
            return false;
        },

        //Keypress when cell in not active. Just for copy & paste feature.
        cellsSelectionKeydown: function(e){

            if(self.editingCell == true){
                e.stopPropagation();
                return true;
            }

            var cell = null;
            var code = (e.keyCode ? e.keyCode : e.which);
            var sheet_table =  $('#es-grid-'+self.getId());
            sheet_table.find(".cpselected").removeClass("cpselected");

            switch(code){
                case 37://LEFT ARROW
                    cell = sheet_table.find("tr").eq(self.rowIndex).find("td").eq( (self.colIndex <= 0) ? 0 : --self.colIndex );
                    break;
                case 38://UP ARROW
                    cell = sheet_table.find("tr").eq((self.rowIndex <= 0) ? 0 :  --self.rowIndex).find("td").eq(self.colIndex);
                    break;
                case 39://RIGHT ARROW
                    cell = sheet_table.find("tr").eq(self.rowIndex).find("td").eq( (self.colIndex < self.getSheet().cols.length) ? ++self.colIndex : self.colIndex );
                    break;
                case 40://DOWN ARROW
                    cell = sheet_table.find("tr").eq( (self.rowIndex < self.getSheet().rows.length) ? ++self.rowIndex : self.rowIndex ).find("td").eq(self.colIndex);
                    break;
                case 13://ENTER
                    //user's dragging a cell to resize it
                    if (self.table.isDraggingCell()) return;

                    cell = sheet_table.find("tr").eq(self.rowIndex).find("td").eq(self.colIndex);
                    self.table.clearOverlays();
                    self.table.getLocalSelection().addCell(self.getSheet().id,$(cell).data().row_id.toString(),$(cell).data().col_id.toString());
                    self.current_cell = cell;
                    self.editingCell = true;
                    break;
                case 46://CANC
                    self.deleteCellsContent();
                    break;
                case 9://TAB
                    self.table.moveSelection(e,0,1);//right
                    break;
                case 27://ESC
                    self.table.clearOverlays();
                    self.editingCell = false;
                    self.resetSelection(self.current_cell);
                    break;
            }

            if (e.shiftKey) {
                self.selectTo(cell);
            } else {
                self.resetSelection(cell)
            }

            return false;
        },

        deleteCellsContent: function(){
            var cell = null;
            for (var i = self.rowStart; i <= self.rowEnd; i++) {
                for (var j = self.colStart; j <= self.colEnd; j++) {
                    cell = self.table.$grid.find("tr").eq(i).find("td").eq(j);
                    self.getSheet().updateCell($(cell).attr('data-row_id'), $(cell).attr('data-col_id'),"");
                }
            }
        },

        //COPY AND PASTE HANDLERS

        onPaste: function(e){
            var cell = null;
            try {
                var clipRows = e.clipboardData.getData('text/plain').split(String.fromCharCode(13));
                for (var i = 0; (i < clipRows.length && i + this.startColIndex < this.getSheet().rowCount()); i++) {
                    clipRows[i] = clipRows[i].split(String.fromCharCode(9));
                    for (var j = 0; (j < clipRows[i].length && j + this.startColIndex < this.getSheet().colCount()); j++) {
                        cell = this.table.$grid.find("tr").eq(i + this.startRowIndex).find("td").eq(j + this.startColIndex);
                        this.getSheet().updateCell($(cell).attr('data-row_id'), $(cell).attr('data-col_id'), clipRows[i][j]);
                    }
                }
                e.clipboardData.setData('text/plain', "");
                e.preventDefault();
            }catch(e){
                console.log("onPaste error : " + e);
            }
        },

        onCopy: function(e){
            try {
                e.clipboardData.setData('text/plain', "");
                var cbData = "";
                for (var i = this.rowStart; i <= this.rowEnd; i++) {
                    var rowCells = this.table.$grid.find("tr").eq(i).find("td");
                    for (var j = this.colStart; j <= this.colEnd; j++)
                        cbData += $(rowCells.eq(j)).text() + ((j != this.colEnd) ? String.fromCharCode(9) : "");

                    if(i != this.rowEnd)cbData += String.fromCharCode(13);
                }
                e.clipboardData.setData('text/plain', cbData);
                e.preventDefault();
            }catch(e){
                console.log("onCopy error : " + e);
            }
        },

        onCut: function(e){
            this.onCopy(e);
            this.deleteCellsContent();
        }

    })
});
