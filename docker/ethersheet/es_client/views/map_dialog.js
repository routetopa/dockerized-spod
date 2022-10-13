if (typeof define !== 'function') { var define = require('amdefine')(module) }

define(function(require,exports,module){
    var t        = require('../templates');
    var MapView  = require('../views/map_view');

    var self;

    var MapDialog = module.exports = MapView.extend({

        events: {
            'change #interaction_type' : 'changeInteraction',
            'click #sat_map_button'    : 'makeVisibleSatLayer',
            'click #add_map_button'    : 'addToMap',
            'click #es-modal-close'    : 'close'
        },

        selected_interaction: "Marker",

        initialize: function (o) {
            Object.getPrototypeOf(this.constructor.prototype).initialize.call(this, o);
            self = this;
        },

        render: function () {
            $('#es-modal-overlay').show();
            Object.getPrototypeOf(this.constructor.prototype).render.call(this, t.map_dialog({}));

            //Instantiate with some options and add the Control
            this.geocoder = new Geocoder('nominatim', {
                 provider: 'photon',
                 lang: 'en',
                 placeholder: 'Search for ...',
                 limit: 5,
                 keepOpen: true,
                 preventDefault : true
             });

             this.overlay = new ol.Overlay({
                 element: document.getElementById('popup'),
                 offset: [0, -40]
             });

             this.map.addControl(this.geocoder);

             //Listen when an address is chosen
             this.geocoder.on('addresschosen', function(evt){
                 self.olview.setCenter(evt.coordinate);
                 self.olview.setZoom(16);
                 if(self.selected_interaction == "Marker") {
                     self.setMarker(evt.coordinate);
                 }
             });

             this.map.on('click', function(evt) {
                 if(self.selected_interaction == "Marker") {
                    self.setMarker(evt.coordinate);
                 }
             });
        },

        changeInteraction: function(e){
            this.selected_interaction = $(e.currentTarget).find(":selected").html();
            this.addInteraction();
        },

        addInteraction : function() {
            if(self.draw != null) self.map.removeInteraction(self.draw);
            var geometryFunction, maxPoints, interaction_type = self.selected_interaction;
            switch(self.selected_interaction){
                case "Square":
                    interaction_type = "Circle";
                    geometryFunction = ol.interaction.Draw.createRegularPolygon(4);
                    break;
                case "Box":
                    interaction_type = "LineString";
                    maxPoints = 2;
                    geometryFunction = function(coordinates, geometry) {
                        if (!geometry) {
                            geometry = new ol.geom.Polygon(null);
                        }
                        var start = coordinates[0];
                        var end = coordinates[1];
                        geometry.setCoordinates([
                            [start, [start[0], end[1]], end, [end[0], start[1]], start]
                        ]);
                        return geometry;
                    };
                    break;
            }

            if(self.selected_interaction != "Marker") {
                self.draw = new ol.interaction.Draw({
                    source: self.source,
                    type: interaction_type ,
                    geometryFunction: geometryFunction,
                    maxPoints: maxPoints,
                    style : self.getTextStyle(-12)
                });

                self.map.addInteraction(self.draw);

                self.draw.on('drawend', function(e) {
                    e.feature.setStyle(self.getTextStyle(-12));
                });
            }
        },

        getTextStyle: function(offsetX) {
            return new ol.style.Style({
                fill: new ol.style.Fill({
                    color: 'rgba(255, 255, 255, 0.6)'
                }),
                stroke: new ol.style.Stroke({
                    color: '#ffff00',
                    width: 4
                }),
                text : new ol.style.Text({
                    fill : new ol.style.Fill({
                        color : '#000'
                    }),
                    stroke : new ol.style.Stroke({
                        color : '#fff',
                        width : 4
                    }),
                    text : this.label,
                    font : '20px Verdana',
                    offsetX : offsetX ? offsetX : 0,
                    offsetY : 12
                })
            });
        },

        getGeoJSON: function(){
            var allFeatures = this.draw_layer.getSource().getFeatures();
            var format = new ol.format.GeoJSON();
            return format.writeFeatures(allFeatures);
        },

        makeVisibleSatLayer: function(e){
            this.satLayer.setVisible(!this.satLayer.getVisible());
        },

        addToMap: function(e){
            var data;
            if(this.selected_interaction == "Marker") {
                data =  (!_.isUndefined(self.coords)) ? _.clone(self.coords).reverse().join(",") : "";
            }else{
                data = self.getGeoJSON();
            }
            self.table.getSheet().updateCell($(self.cell).attr('data-row_id'), $(self.cell).attr('data-col_id'),data);
            $(this.$el).empty();
            $('#es-modal-overlay').hide();
            $(".es-overlay").remove();
        },

        close : function(){
            $(this.$el).empty();
        }
    })
});



