if (typeof define !== 'function') { var define = require('amdefine')(module) }
define( function(require,exports,module){

    /*

     #FunctionMenuView

     A list of features to search, go to specific row and go to first and last row in table with pagination.

     ## References
     * Sheet
     * Table

     */
    var $          = require('jquery');
    var t          = require('../templates');
    var config     = require('../config');
    var RefBinder  = require('ref-binder');
    var hightlight = require('jquery.highlight-5');
    var View = require('backbone').View;

    var self;

    var FunctionMenuView = module.exports = View.extend({

        events: {
            'search #filter_key'           : 'onSearch',
            'keyup #filter_key'            : 'onSearch',
            'click #pagination_prev'       : 'prev',
            'click #pagination_next'       : 'next',
            'click a.page'                 : 'onPageSelection',
            'change #chunk_length'         : 'onChunkChange',
            'click .search_arrows_enabled' : 'getNewResult'
        },

        //pagination controller variables
        chunk : config.DEFAULT_CHUNK_SIZE,
        size  : 1,
        code  : "",
        page  : 1,
        step  : 3,

        //scrolling feature variables
        table            : null,
        lastScrollTop    : 0,
        max_scroll       : 0,
        rows_in_last_page: 0,
        rows_delta       : 10,
        first_index_row  : 0,
        last_row_index   : 0,

        //search
        current_results  : {},//map with current search results
        current_selected_result : 0,
        search_key : "",
        previous_result_cells : [],

        initialize: function(o){
            this.models = new RefBinder(this);
            this.data = o.data;
            this.table = o.table;

            this.setSheet(o.data.sheets.get(o.data.users.getCurrentUser().getCurrentSheetId()) || null);
            this.size = Math.floor(this.getSheet().allRows.length / this.chunk);
            this.rows_in_last_page = this.getSheet().allRows.length % this.chunk;
            if(this.rows_in_last_page > 0) this.size += 1;

            self = this;
        },

        recalculateMetrix: function(){
            this.size = Math.floor(this.getSheet().allRows.length / this.chunk);
            this.rows_in_last_page = this.getSheet().allRows.length % this.chunk;
        },

        getFirstRowIndex: function(){
            return this.first_index_row;
        },

        getSheet: function(){
            return this.models.get('sheet');
        },

        setSheet: function(sheet){
            this.models.set('sheet',sheet);
        },

        render: function(){
            if(this.size > 1) this.getPagination();
            this.$el.html(t.table_function_menu({pagination_code : this.code}));
        },

        //SEARCH STUFFS

        resetSearchBar: function(){
            _.each(this.previous_result_cells, function(cell){
                cell.removeHighlight();
            });

            $('#search_up').removeClass('search_arrows_enabled');
            $('#search_down').removeClass('search_arrows_enabled');
            $('#search_info').html("0 of 0");
            this.current_selected_result = 0;
            this.current_results = {};
        },

        setSearchArrows: function(){
            var results_len = Object.keys(this.current_results).length;
            var search_up   = $('#search_up');
            var search_down = $('#search_down');
            if(this.current_selected_result == 1 && this.current_selected_result < results_len){
                search_up.removeClass('search_arrows_enabled');
                search_down.addClass('search_arrows_enabled');
            }else if(this.current_selected_result == results_len){
                search_down.removeClass('search_arrows_enabled');
                search_up.addClass('search_arrows_enabled');
            }else{
                search_up.addClass('search_arrows_enabled');
                search_down.addClass('search_arrows_enabled');
            }
        },

        gotoSearchResult: function(){
            //get the page related to the current result and go to the page
            var current_cell_keys = this.current_results[Object.keys(this.current_results)[this.current_selected_result - 1]];
            this.pageSelection(this.getPageForRow(current_cell_keys.row_id));
            //scroll to the cell related to the current result
            var cell = $('td[data-row_id="' + current_cell_keys.row_id + '"][data-col_id="' + current_cell_keys.col_id + '"]');
            this.scrollTo(cell);
        },

        getNewResult: function(e){
            var results_len = Object.keys(this.current_results).length;
            if(e.target.id === "search_down"&& this.current_selected_result < results_len){
                this.current_selected_result++;
            }else if(this.current_selected_result > 1){
                this.current_selected_result--;

            }
            $('#search_info').html(this.current_selected_result + " of " + results_len);
            this.setSearchArrows();
            this.gotoSearchResult();
        },

        searchKeyInCells: function(){
            delete this.current_results;
            this.current_results = {};
            self.search_key = self.search_key.toLowerCase();
            _.each(this.getSheet().getCells(), function (cell,row_key) {
                _.each(cell, function(c,col_key){
                    if(!(_.isUndefined(c) || _.isEmpty(c))){
                        if(_.has(c, 'value')){
                            var value = c.value.toLowerCase();
                            if(value.includes(self.search_key))
                                self.current_results[row_key + col_key] = {row_id : row_key , col_id : col_key};
                        }
                    }
                });
            });
            this.current_selected_result = (Object.keys(this.current_results).length > 0) ?  1 : 0;
        },

        highlightSearchResults: function(){
            //unhighlight
            _.each(this.previous_result_cells, function(cell){
                cell.removeHighlight();
            });
            //highlight
            this.previous_result_cells = [];
            _.each(this.getSheet().rows, function(row){
                _.each(self.getSheet().cols, function(col){
                    if(row + col in self.current_results){
                        var cell = $('td[data-row_id="' + row + '"][data-col_id="' + col + '"]');
                        cell.highlight(self.search_key);
                        self.previous_result_cells.push(cell);
                    }
                })
            });
        },

        onSearch: _.debounce(function(e){
            if(e.keyCode == 13){//ENTER
                this.getNewResult({target :{id : "search_down"}});
                return;
            }

            this.search_key = $("#filter_key").val();
            this.searchKeyInCells();

            if(this.current_selected_result == 0 || this.search_key === ""){
                this.resetSearchBar();
            }else{
                this.highlightSearchResults();
                this.setSearchArrows();
                this.gotoSearchResult();
                $('#search_info').html((this.current_selected_result != 0) ? "1 of " + Object.keys(this.current_results).length : "0 of 0");
            }
        }, 500),

        //PAGINATION STUFFS

        // add pages by number (from [s] to [f])
        add: function(s, f) {
            for (var i = s; i < f; i++) {
                if(i == this.page)
                    this.code += '<a class="active">' + i + '</a>';
                else
                    this.code += '<a class="page">' + i + '</a>';
            }
        },

        // add last page with separator
        last: function() {
            this.code += '<a>...</a><a class="page">' + this.size + '</a>';
        },

        // add first page with separator
        first: function() {
            this.code += '<a class="page">1</a><a>...</a>';
        },

        // find pagination type
        getPagination: function() {

            this.code = '<a id="pagination_prev">&#9668;</a>';// previous button

            if (this.size < this.step * 2 + 6) {
                this.add(1, this.size + 1);
            }else if (this.page < this.step * 2 + 1) {
                this.add(1, this.step * 2 + 4);
                this.last();
            }else if (this.page > this.size - this.step * 2) {
                this.first();
                this.add(this.size - this.step * 2 - 2, this.size + 1);
            }else {
                this.first();
                this.add(this.page - this.step, this.page + this.step + 1);
                this.last();
            }
            this.code +=  '<a id="pagination_next">&#9658;</a>'; // next button

            $('#pagination_controller').html(this.code);
        },

        // previous page
        prev: function() {
            self.page--;
            if (self.page < 1) {
                self.page = 1;
            }
            self.getPagination();
            $('#pagination_controller').html(self.code);
        },

        // next page
        next: function() {
            self.page++;
            if (self.page > self.size) {
                self.page = self.size;
            }
            self.pageSelection();
        },

        pageSelection: function(page, direction){
            //if(page != undefined) this.page = page;
            if(_.isUndefined(direction) && !_.isUndefined(page) ) this.page = page;
            this.getPagination();
            this.goToPageInGrid();
            if(_.isUndefined(direction) || direction > 0)
                this.table.$grid.scrollTop(this.rows_delta);
            else
                this.table.$grid.scrollTop($(".es-row-headers").height() - (this.rows_delta * this.chunk));
        },

        onPageSelection: function(e){
            self.page = +e.target.innerHTML;
            self.pageSelection();
        },

        goToPageInGrid: function(){
            var offset_last, offset_first;
            offset_first = (this.chunk * (this.page - 1));
            offset_last  = (this.chunk * this.page);

            if(this.page == 1){
                this.first_index_row = 0;
                this.last_row_index  = offset_last + this.rows_delta;
            }else if(this.page == this.size){
                this.first_index_row = offset_first - this.rows_delta;
                this.last_row_index  = offset_last  + this.rows_delta + this.rows_in_last_page;
            }else{
                this.first_index_row = offset_first - this.rows_delta;
                this.last_row_index  = offset_last + this.rows_delta;
            }

            this.getSheet().rows = this.getSheet().allRows.slice(this.first_index_row, this.last_row_index);
            this.table.render();

            this.highlightSearchResults();
        },

        getPageForRow: function(row){
            return Math.floor(Object.values(this.getSheet().allRows).indexOf(row) / this.chunk ) + 1;
        },

        scrollTo: function(cell){
            var low_limit = (10 * (this.chunk / 2));
            //vertical scroll
            var v_offset = $(cell)[0].offsetTop;
            this.table.$grid.scrollTop(0);
            if(v_offset < low_limit){
                this.table.$grid.scrollTop(this.rows_delta);
            }else{
                this.table.$grid.scrollTop(v_offset );
            }
            //horizintal scroll
            this.table.$grid.scrollLeft(cell.offset().left - low_limit);
        },

        scroll: function(grid_el){
            if (grid_el.scrollTop > this.lastScrollTop ) {
                //down
                let delta = 20;
                this.max_scroll = (grid_el.scrollHeight - this.table.$grid.height()) + 8;
                if((grid_el.scrollTop >= this.max_scroll - delta) && this.page < this.size) {
                    this.page++;
                    this.pageSelection(this.page - 1, 1);
                }
            } else {
                //up
                if(grid_el.scrollTop == 0 && this.page > 1) {
                    this.page--;
                    this.pageSelection(this.page + 1, 0);
                }
            }
            this.lastScrollTop = grid_el.scrollTop;
        },

        onChunkChange: function(e){
            this.chunk = +e.target.options[e.target.selectedIndex].value;

            this.size = Math.floor(this.getSheet().allRows.length / this.chunk);
            this.rows_in_last_page = this.getSheet().allRows.length % this.chunk;
            if(this.rows_in_last_page > 0) this.size += 1;

            if(this.page > this.size) this.page = this.size;

            if(this.size > 1) this.pageSelection();

        }
    });
});