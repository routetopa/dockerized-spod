if (typeof define !== 'function') { var define = require('amdefine')(module) }
define(function (require, exports, module) {

  var _ = require('underscore');
  var helpers = require('es_client/helpers');
  module.exports = module.exports || {};
  module.exports['cell_format_dialog'] = _.template("<div id='es-modal-close'>[close]</div><h2>Format Cell</h2>\n<div class='es-format-toggle' id='es-bg-red'>bg red</div>\n<div class='es-format-toggle' id='es-bg-white'>bg white</div>\n<div class='es-format-toggle' id='es-usd'>money</div>\n");
  module.exports['col_menu'] = _.template("<ul class=\"es-menu-list\">\n  <li\n        id=\"es-menu-add-column\"\n        class=\"es-menu-button i18n\"\n        data-action=\"add_column\"\n        data-i18n=\"add_column\">\n  </li><li\n        id=\"es-menu-remove-column\"\n        class=\"es-menu-button i18n\"\n        data-action=\"remove_column\"\n        data-i18n=\"remove_column\">\n  </li><li\n        id=\"es-menu-sort-rows\"\n        class=\"es-menu-button i18n\"\n        data-action=\"sort_row\"\n        data-i18n=\"sort_row\">\n  </li>\n</ul>\n<div class='clear'></div>\n");
  module.exports['row_menu'] = _.template("<ul class=\"es-menu-list\" id=\"add-row-menu-trigger\">\n  <li\n        id=\"es-menu-add-row\"\n        class=\"es-menu-button i18n\"\n            data-i18n=\"add_row\">\n  </li></ul><ul class=\"es-menu-list\"><li\n        id=\"es-menu-remove-row\"\n        class=\"es-menu-button i18n\"\n        data-action=\"remove_row\"\n        data-i18n=\"remove_row\">\n  </li>\n</ul>\n<ul  class=\"es-menu-list\" id=\"add-row-menu\"><li class=\"es-menu-button i18n\" data-action=\"add_row\"  data-i18n=\"add_row_single\"></li><li class=\"es-menu-button i18n\" data-i18n=\"add_row_10\" data-action=\"add_row_multiple\"></li></ul><div class='clear'></div>\n");
  /*isislab*/
  module.exports['cell_menu'] = _.template("<ul class=\"es-menu-list\">\n  <li\n        id=\"es-add-geo-point\"\n        class=\"es-menu-button i18n\"\n        data-action=\"add_geo_point\"\n        data-i18n=\"add_geo_point\"></li><li id=\"es-menu-add-image\" class=\"es-menu-button i18n\" data-action=\"add_image\" data-i18n=\"add_image\"></li></ul>\n<div class='clear'></div>\n");
  module.exports['table_function_menu'] = _.template("<div id=\"es-table-function-menu\"> <div class=\"chunk_selection\"><label>Show <select id=\"chunk_length\" name=\"chunk_length\" aria-controls=\"chunk\"><option value=\"50\">50</option><option value=\"100\">100</option></select> entries</label></div><div id=\"pagination_controller\" class=\"pagination\"><%= pagination_code %></div><div class=\"search_panel\"><input name=\"filter_key\" id=\"filter_key\" type=\"search\" pattern=\".{1,}\" results=\"5\" aria-controls=\"filter\" placeholder=\"Search\"><div id=\"search_info\">0 of 0</div><div id=\"search_separator\"></div><div id=\"search_up\" class=\"search_arrows\"></div><div id=\"search_down\" class=\"search_arrows\"></div></div></div>");
  module.exports['upload_image_dialog'] = _.template("<div id='es-modal-close'>[close]</div><h2 class=\"es-modal-title\">Upload image</h2><form id=\"upload_image_form\"><label for=\"image_file\" class=\"custom-button\">Select file</label><input type=\"file\" name=\"image_file\"  id=\"image_file\" ><input type='submit' value='Upload'  id=\"upload_image_button\" class=\"custom-button\"><input type=\"hidden\" name=\"sheet_id\" value=\"<%=sheet_id%>\"><div id=\"dragzone\"><div id=\"dragzone-caption\" class=\"i18n\" data-i18n=\"dragzone_caption\">Drag file here</div></div></form>");
  module.exports['map_view'] = _.template("<div id=\"map\"></div>");
  module.exports['map_dialog'] = _.template("<div id='es-modal-close'>[close]</div><h2 class=\"es-modal-title\">Map dialog</h2><div id=\"controls\"><div id=\"add_map_button\" class=\"custom-button map_dialog_button\">Add to sheet</div><div id=\"sat_map_button\" class=\"custom-button map_dialog_button\">Sat</div><div class=\"styled-select\"><select id=\"interaction_type\"><option>Marker</option><option>LineString</option><option>Polygon</option><option>Square</option><option>Box</option></select></div></div><div id=\"map\"></div>");
  /*end isislab*/

  /*START Import CSV dialog*/
  //module.exports['import_dialog'] = _.template("<div id='es-modal-close'>[close]</div><h2>Import CSV</h2>\n<form action=\"/ethersheet/import/csv\" method=\"post\" enctype=\"multipart/form-data\">\n  <input type=\"file\" name=\"csv_file\"><input type='submit' value='Upload'>\n  <input type=\"hidden\" name=\"sheet_id\" value=\"<%=sheet_id%>\">\n</form>\n\n\n");
  $.get('/es_client/templates/import_dialog.jst', function (data) { module.exports['import_dialog'] = _.template(data); });
  $.get('/es_client/templates/import_shape_file_dialog.jst', function (data) { module.exports['import_shape_file_dialog'] = _.template(data); });
  /*END Import CSV dialog */

  //CHANGED FOR DATA QUALITY
  module.exports['qualitychecker_menu'] = _.template("<div style='padding: 5px;'><p>It analysis the dataset to discover possible quality issues (it may take time).</p><div id='button-run-qualitychecker' class='es-button'>Run</div><div id='txtMessage' class='white-panel' style='display: none;'>No warnings detected.</div><div id='panelQualitychecker' class='white-panel' style='height: 200px; display: none; background-color: lightyellow'><div id='txtQualityIssues' class='search_info' style='width: 165px;'>Warning 0 of 0</div><div class='search_separator'></div><div id='prev_issue' class='search_arrows search_up'></div><div id='next_issue' class='search_arrows search_down'></div> <div class='horizontal_separator'></div>\n <div id='issue_description' class='search_info' style='width: 100%;'></div> </div></div> \n <div id='spiQualitychecker' class='sk-cube-grid'><div class='sk-cube sk-cube1'></div><div class='sk-cube sk-cube2'></div>  <div class='sk-cube sk-cube3'></div>  <div class='sk-cube sk-cube4'></div>  <div class='sk-cube sk-cube5'></div>  <div class='sk-cube sk-cube6'></div>  <div class='sk-cube sk-cube7'></div>  <div class='sk-cube sk-cube8'></div>   <div class='sk-cube sk-cube9'></div><p class='search_info'>running...</p></div>\n </div>\n");
  module.exports['es_container'] = _.template("<div id=\"es-container\">\n  <div id=\"es-header\">\n    <div class=\"es-sidebar-toggle i18n\" id=\"es-sheet-icon\" data-i18n=\"[title]manageSheets\"></div>\n    <div class=\"es-sidebar-toggle i18n\" id=\"es-function-icon\" data-i18n=\"[title]functionHelp\"></div> \n  <div class=\"es-sidebar-toggle i18n\" id=\"es-qualitychecker-icon\" data-i18n=\"[title]qualityCheck\"></div> \n  <!--<div class=\"es-sidebar-toggle i18n\" id=\"es-style-icon\" data-i18n=\"[title]styleSheet\"></div> -->\n    <!--<div class=\"es-sidebar-toggle i18n\" id=\"es-activity-icon\" data-i18n=\"[title]viewTimeline\"></div> -->\n\n    <div id=\"es-logo\"><h1><%=title%></h1> </div>\n    <div id=\"connection_status_message\" style=\"position:relative;float:right;\"></div>\n   <div class=\"clear\"></div>\n  </div>\n  <div id=\"es-panel-0\" class=\"es-panel\">\n    <div class='menu-container' id='es-sheet-menu-container'> </div>\n    <div class='menu-container' id=\"es-function-menu-container\"> </div> \n    <div class='menu-container' id=\"es-style-menu-container\"> </div>\n    <div class='menu-container' id=\"es-activity-menu-container\"> </div>\n    <div class='menu-container' id=\"es-qualitychecker-menu-container\"> </div>\n    </div>\n  <div id=\"es-panel-1\" class=\"es-panel\">\n   <div id=\"offline_overlay\"></div>\n    <div id=\"es-table-container\"></div>\n    <div id=\"es-table-function-menu\"></div>\n  </div>\n  <div id=\"es-modal-overlay\"><div id=\"es-modal-box\"></div></div>\n<div id=\"cell-preview\"></div></div>\n");
  //module.exports['es_container'] = _.template("<div id=\"es-container\">\n  <div id=\"es-header\">\n    <div class=\"es-sidebar-toggle i18n\" id=\"es-sheet-icon\" data-i18n=\"[title]manageSheets\"></div>\n    <div class=\"es-sidebar-toggle i18n\" id=\"es-function-icon\" data-i18n=\"[title]functionHelp\"></div> \n    <!--<div class=\"es-sidebar-toggle i18n\" id=\"es-style-icon\" data-i18n=\"[title]styleSheet\"></div> -->\n    <!--<div class=\"es-sidebar-toggle i18n\" id=\"es-activity-icon\" data-i18n=\"[title]viewTimeline\"></div> -->\n\n    <div id=\"es-logo\"><h1><%=title%></h1> </div>\n    <div id=\"connection_status_message\" style=\"position:relative;float:right;\"></div>\n   <div class=\"clear\"></div>\n  </div>\n  <div id=\"es-panel-0\" class=\"es-panel\">\n    <div class='menu-container' id='es-sheet-menu-container'> </div>\n    <div class='menu-container' id=\"es-function-menu-container\"> </div> \n    <div class='menu-container' id=\"es-style-menu-container\"> </div>\n    <div class='menu-container' id=\"es-activity-menu-container\"> </div>\n  </div>\n  <div id=\"es-panel-1\" class=\"es-panel\">\n   <div id=\"offline_overlay\"></div>\n    <div id=\"es-table-container\"></div>\n    <div id=\"es-table-function-menu\"></div>\n  </div>\n  <div id=\"es-modal-overlay\"><div id=\"es-modal-box\"></div></div>\n<div id=\"cell-preview\"></div></div>\n");

  module.exports['expression_editor'] = _.template("<textarea class=\"es-expression-editor-input\" type='text'></textarea>\n");
  module.exports['function_menu'] = _.template("<ul class='es-menu-list'>\n<% _.each(eh.userFunctions,function(func){ %>\n  <% if(!func || !func.def){ return }; %>\n  <li class=\"es-menu-button\" data-action=<%= func.def %> > <%= func.def %> - <%= func.desc  %> </li>\n<% }); %>\n</ul>\n");
  //module.exports['sheet_list'] = _.template("<ul class='es-menu-list'>\n<% var _ = require('underscore'); %>\n<% var cls = \"\" %>\n<% sheets.each(function(sheet){ %>\n\t<li class=\"es-menu-button\" id=<%=sheet.id%> > \n  \t<span class=\"es-sheet-name\" style=\"float:left;padding-top:5px;\"><%=sheet.meta.title%> </span>\n  \t<form class=\"es-sheet-edit-form\" style=\"display:none;float:left;padding-top:5px;height:22px;width:155px;\">\n    \t<input class='es-sheet-input-name' type=\"text\" style=\"width: 145px; font-size: 16px; border: 1px solid #ccc;\" value=\"<%=sheet.meta.title%>\">\n    \t<input class='es-sheet-input-id' type=\"hidden\" value=\"<%=sheet.id%>\">\n  \t</form>\n \t\t<button class=\"es-sheet-control\" id=\"delete-sheet\" data-sheet-id=\"<%=sheet.id%>\"><img src=\"/es_client/icons/es-trashcan.png\" height=16 width=16></button>\n  \t<button class=\"es-sheet-control\" id=\"rename-sheet\" data-sheet-id=\"<%=sheet.id%>\"><img src=\"/es_client/icons/es_pen.png\" height=16 width=16></button>\n  \t<div class=\"clear\"></div>\n\t</li>\n<%});%>\n</ul>\n<ul id='es-menu-grid'>\n\t<li id='es-add-sheet-button' class=\"es-menu-grid-button\"><img src=\"/es_client/icons/ethersheet-icon-newtable.png\"><span class=\"i18n\" data-i18n=\"newSheet\"></span></li>\n\t<a href=\"/ethersheet/export/s/<%=current_sheet_id%>.csv\" target=_blank><li id=\"es-export-csv\" class=\"es-menu-grid-button\"><img src=\"/es_client/icons/ethersheet-icon-export.png\"><span class=\"i18n\" data-i18n=\"exportCSV\"></span></li></a>\n\t<li id=\"es-import-csv\" class=\"es-menu-grid-button\"><img src=\"/es_client/icons/ethersheet-icon-import.png\"><span>Import CSV</span></li>\n</ul>\n");
  module.exports['sheet_list'] = _.template("<ul class='es-menu-list'>\n<% var _ = require('underscore'); %>\n<% var cls = \"\" %>\n<% sheets.each(function(sheet){ %>\n\t<li class=\"es-menu-button\" id=<%=sheet.id%> > \n  \t<span class=\"es-sheet-name\" style=\"float:left;padding-top:5px;\"><%=sheet.meta.title%> </span>\n  \t<form class=\"es-sheet-edit-form\" style=\"display:none;float:left;padding-top:5px;height:22px;width:155px;\">\n    \t<input class='es-sheet-input-name' type=\"text\" style=\"width: 145px; font-size: 16px; border: 1px solid #ccc;\" value=\"<%=sheet.meta.title%>\">\n    \t<input class='es-sheet-input-id' type=\"hidden\" value=\"<%=sheet.id%>\">\n  \t</form>\n \t\t<button class=\"es-sheet-control\" id=\"delete-sheet\" data-sheet-id=\"<%=sheet.id%>\"><img src=\"/es_client/icons/es-trashcan.png\" height=16 width=16></button>\n  \t<button class=\"es-sheet-control\" id=\"rename-sheet\" data-sheet-id=\"<%=sheet.id%>\"><img src=\"/es_client/icons/es_pen.png\" height=16 width=16></button>\n  \t<div class=\"clear\"></div>\n\t</li>\n<%});%>\n</ul>\n<ul id='es-menu-grid'>\n\t<li id='es-add-sheet-button' class=\"es-menu-grid-button\"><img src=\"/es_client/icons/ethersheet-icon-newtable.png\"><span class=\"i18n\" data-i18n=\"newSheet\"></span></li>\n\t<a href=\"/ethersheet/export/s/<%=current_sheet_id%>.csv\" target=_blank><li id=\"es-export-csv\" class=\"es-menu-grid-button\"><img src=\"/es_client/icons/ethersheet-icon-export.png\"><span class=\"i18n\" data-i18n=\"exportCSV\"></span></li></a>\n\t<li id=\"es-import-csv\" class=\"es-menu-grid-button\"><img src=\"/es_client/icons/ethersheet-icon-import.png\"><span>Import CSV</span></li>\n\n\t<li id=\"es-import-shape-file\" class=\"es-menu-grid-button\"><img src=\"/es_client/icons/ethersheet-icon-import.png\"><span>Import ShapeFile</span></li>\n</ul>\n");
  module.exports['sheet_table'] = _.template("<div id=\"es-table-<%= id %>\" class=\"es-table-view\">\n  <table id=\"es-column-headers-<%=id%>\" class=\"es-column-headers es-table\">\n    <thead></thead>\n    <tbody></tbody>\n  </table>\n  <table id=\"es-row-headers-<%=id%>\" class=\"es-row-headers es-table\">\n    <thead></thead>\n    <tbody></tbody>\n  </table>\n  <div id=\"es-grid-container-<%= id %>\" class=\"es-grid-container\">\n    <table id=\"es-grid-<%= id %>\" class=\"es-grid es-table\">\n      <thead></thead>\n      <tbody id=\"es-data-table-<%=id%>\"></tbody>\n    </table>\n  </div>\n  <div class=\"es-table-corner\">\n    <div class=\"es-logo es-sidebar-toggle\">ES</div>\n  </div>\n</div>\n");
  module.exports['table'] = _.template("<% var _ = require('underscore'); %>\n<% var clsAry = ['even','odd']; %>\n<% _.each(sheet.rowIds(), function(row_id,i){ %>\n<% if(i == 0){var cls = 'first'} else {var cls = clsAry[i % 2]} %>\n  <tr id=\"<%= row_id %>\" class=\"es-table-row es-row-<%= cls %>\" data-row_id=\"<%= row_id %>\" style=\"height:<%= sheet.getRowHeight(row_id) %>px;\">\n    <% if(i == 0) { %>\n      <% _.each(sheet.colIds(), function(col_id){ %>\n        <td id=\"<%= row_id %>-<%= col_id %>\" style=\"width:<%= sheet.getColWidth(col_id) %>px;\" class=\"<%= sheet.getCellFormatString(row_id,col_id) %>\" data-row_id=\"<%= row_id %>\" data-col_id=\"<%= col_id %>\" data-value=\"<%= sheet.getCellValue(row_id,col_id)%>\"><%= sheet.getCellDisplayById(row_id,col_id) %></td>\n      <% }) %>\n    <% } else { %>\n      <% _.each(sheet.colIds(), function(col_id){ %>\n        <td id=\"<%= row_id %>-<%= col_id %>\" class=\"<%= sheet.getCellFormatString(row_id,col_id) %>\" data-row_id=\"<%= row_id %>\" data-col_id=\"<%= col_id %>\" data-value=\"<%= sheet.getCellValue(row_id,col_id)%>\"><%= sheet.getCellDisplayById(row_id,col_id) %></td>\n      <% }) %>\n    <% } %>\n  </tr>\n<% }) %>\n");
  var templateWrapper = function (template) {
    return function (data) {
      data = data || {};
      data.require = require;
      return template(data);
    }
  };
  for (i in module.exports) module.exports[i] = templateWrapper(module.exports[i]);

});