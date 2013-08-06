function init() {

    var buildingId = 149997338;
    var schemaName = 'community';

    // create a vector layer for drawing
    var vector = new OpenLayers.Layer.Vector('Vector Layer', {
        styleMap: new OpenLayers.StyleMap({
            temporary: OpenLayers.Util.applyDefaults({
                pointRadius: 16
            }, OpenLayers.Feature.Vector.style.temporary),
            'default': OpenLayers.Util.applyDefaults({
                pointRadius: 16,
                strokeWidth: 3,
            }, OpenLayers.Feature.Vector.style['default']),
            select: OpenLayers.Util.applyDefaults({
                pointRadius: 16,
                strokeWidth: 3
            }, OpenLayers.Feature.Vector.style.select)
        })
    });

    // create a vector layer for building outline
    var building_outline = new OpenLayers.Layer.Vector('Building Outline', {
        styleMap: new OpenLayers.StyleMap({
            'default': OpenLayers.Util.applyDefaults({
                pointRadius: 16,
                strokeWidth: 6,
            }, OpenLayers.Feature.Vector.style['default'])
        })
    });

    // Empty basemap
    var clearBaseLayer = new OpenLayers.Layer("None", {isBaseLayer: true}); 

    // OpenLayers' EditingToolbar internally creates a Navigation control, we
    // want a TouchNavigation control here so we create our own editing toolbar
    var toolbar = new OpenLayers.Control.Panel({
        displayClass: 'olControlEditingToolbar'
    });
    toolbar.addControls([
        // this control is just there to be able to deactivate the drawing
        // tools
        new OpenLayers.Control({
            displayClass: 'olControlNavigation'
        }),
        new OpenLayers.Control.ModifyFeature(vector, {
            vertexRenderIntent: 'temporary',
            displayClass: 'olControlModifyFeature'
        }),
        new OpenLayers.Control.DrawFeature(vector, OpenLayers.Handler.Point, {
            displayClass: 'olControlDrawFeaturePoint'
        }),
        new OpenLayers.Control.DrawFeature(vector, OpenLayers.Handler.Path, {
            displayClass: 'olControlDrawFeaturePath'
        }),
        new OpenLayers.Control.DrawFeature(vector, OpenLayers.Handler.Polygon, {
            displayClass: 'olControlDrawFeaturePolygon'
        })
    ]);

    // Need to calculate the initial center and zoom level based on the building outline
    map = new OpenLayers.Map({
        div: 'map',
        projection: 'EPSG:900913',
        numZoomLevels: 25,
        controls: [
            new OpenLayers.Control.TouchNavigation({
                dragPanOptions: {
                    enableKinetic: true
                }
            }),
            new OpenLayers.Control.Zoom(),
            toolbar
        ],
        layers: [clearBaseLayer,building_outline, vector],
        center: new OpenLayers.LonLat(0,0),
        zoom: 20,
        theme: null
    });        

    // activate the first control to render the "navigation icon"
    // as active
    toolbar.controls[0].activate();

    // Retrieving the building outline
    $.ajax({
        type : 'GET',
        dataType : 'json',
        data: {building_id:buildingId,schema:schemaName},
        url:'ws/read_building.php',
        success : function(data) {
            var geoJSONObj = new OpenLayers.Format.GeoJSON({
                externalProjection: new OpenLayers.Projection("EPSG:4326"),
                //projection your data is in
                internalProjection: new OpenLayers.Projection("EPSG:900913")
                //projection you map uses to display stuff
            });
            var geoJSON_features = geoJSONObj.read(data);
            building_outline.addFeatures(geoJSON_features);

            // Initialise the map
            var b = geoJSON_features[0].geometry.getBounds();
            var z = building_outline.getZoomForExtent(b);
            map.moveTo(b.getCenterLonLat(),z);
        }
    });    

}