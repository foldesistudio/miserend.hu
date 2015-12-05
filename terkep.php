<?php 
$vars['pageTitle'] = "OSM Térkép";
$vars['template'] = "layout_simpliest_map";

if(isset($_REQUEST['lat']) AND is_numeric($_REQUEST['lat'])) $lat = $_REQUEST['lat']; else $lat = 47.5;
if(isset($_REQUEST['lon']) AND is_numeric($_REQUEST['lon'])) $lon = $_REQUEST['lon']; else $lon = 19.05;
if(isset($_REQUEST['zoom']) AND is_numeric($_REQUEST['zoom'])) $zoom = $_REQUEST['zoom']; else $zoom = 12;

$vars['content'] = <<<EOD
  <script src="http://www.openlayers.org/api/OpenLayers.js"></script>
  <style>
#mapdiv {
  position: absolute;
   width:100%;
   height:100%;
   left:0;
   top:0;
}
  </style>

  <div id="mapdiv"></div>
  
  
  <script>
  var lon = $lon;
  var lat = $lat;
  var zoom = $zoom;

var layerListeners = {
    featureclick: function(e) {
      console.log(e.feature);
        if(typeof e.feature.attributes['url:miserend'] !== "undefined") {
          var html = '<div class="alert alert-success alert-dismissible" role="alert" style="padding:8px;margin-bottom:8px">' + 
                    '<span class="ui-icon ui-icon-check green" style="float:left;margin-right:5px"></span>';
        } else {
          var html = '<div class="alert alert-warning alert-dismissible" role="alert" style="padding:8px;margin-bottom:8px">' + 
                    '<span class="ui-icon ui-icon-alert red" style="float:left;margin-right:5px"></span>';
        }
        html += '<span class="alap">' +
          '<a href="http://www.openstreetmap.org/' + e.feature.fid.replace(".","/") + '" target="_blank">[OSM]</a> ' +
          '<a href="http://www.openstreetmap.org/edit?' + e.feature.fid.replace(".","=") + '" target="_blank">[OSM edit]</a>';
        if(typeof e.feature.attributes['url:miserend'] !== "undefined") {
          html += ' <a href="' + e.feature.attributes['url:miserend'] + '" target="_blank">[Miserend.hu]</a>';
        }
        html += '<br/>';
        for (var k in e.feature.attributes){
          if (e.feature.attributes.hasOwnProperty(k)) {
            html +=  k + " = '" + e.feature.attributes[k] + "'<br/>";
          }
        }
        html += '</span></div>';

        $("#messages").html(html);
        return false;
    },
    nofeatureclick: function(e) {
        $("#messages").html('');
    }
};

   
    map = new OpenLayers.Map("mapdiv",{
          controls:[
              new OpenLayers.Control.Navigation(),
              new OpenLayers.Control.LayerSwitcher(),
              new OpenLayers.Control.Attribution()],
              projection: new OpenLayers.Projection("EPSG:900913"),
              displayProjection: new OpenLayers.Projection("EPSG:4326")
          } );

    map.addControl(new OpenLayers.Control.PanZoomBar());
    map.addControl(new OpenLayers.Control.Permalink('permalsink',null));
  
    map.addLayer(new OpenLayers.Layer.OSM());

    /**
     * Here we create a new style object with rules that determine
     * which symbolizer will be used to render each feature.
     */
    var style = new OpenLayers.Style(
        // the first argument is a base symbolizer
        // all other symbolizers in rules will extend this one
        { 
              strokeColor: "red",
              strokeOpacity: 0.5,
              strokeWidth: 12,
              pointRadius: 2,
              fillColor: "red",
              fillOpacity: 0.25,
              fill: true,
              // label: "${building}" // label will be foo attribute value
        },
        // the second argument will include all rules
        {
            rules: [
                new OpenLayers.Rule({
                    // a rule contains an optional filter
                    filter: new OpenLayers.Filter.Comparison({
                        type: OpenLayers.Filter.Comparison.EQUAL_TO,
                        property: "url:miserend",
                        value: null,
                      }),
                    // if a feature matches the above filter, use this symbolizer
                    symbolizer: {
                        fillColor: "#a39f9f",
                        strokeColor: "#a39f9f"
                    }
                }),
                
                new OpenLayers.Rule({
                    // apply this rule if no others apply
                    elseFilter: true,
                    symbolizer: {
                        strokeColor: "red",
                        fillColor: "red",
                    }
                })                
            ]
        }
    );
    var styleMap = new OpenLayers.StyleMap(style);
    
    var points = new OpenLayers.Layer.Vector( "Templomok",
                    { strategies: [new OpenLayers.Strategy.BBOX()],
                      protocol: new OpenLayers.Protocol.HTTP({
                        url: "http://overpass-api.de/api/interpreter?data=[timeout:30];(node[amenity=place_of_worship][denomination~catholic](bbox);way[amenity=place_of_worship][denomination~catholic](bbox);rel[amenity=place_of_worship][denomination~catholic](bbox););(._;>;);out body;",
                        format: new OpenLayers.Format.OSM(),
                      }),
                      projection: map.displayProjection,
                      styleMap: styleMap,
                      eventListeners: layerListeners
                    });
    
    

    map.addLayer(points);
    
    //Set start centrepoint and zoom
    //TODO: Is it possible to just zoom to extents of defined markers instead?  
    var lonLat = new OpenLayers.LonLat( lon, lat )
          .transform(
            new OpenLayers.Projection("EPSG:4326"), // transform from WGS 1984
            map.getProjectionObject() // to Spherical Mercator Projection
          );
    var zoom=zoom;
    map.setCenter (lonLat, zoom);

    map.events.register("moveend", map, function(evt) { 

      window.history.replaceState('Object', 'Title', $("#OpenLayers_Control_Permalink_37").find('a').attr('href'));
      var latlon = map.getCenter().clone().transform(
            map.getProjectionObject(), // to Spherical Mercator Projection
            new OpenLayers.Projection("EPSG:4326") // transform from WGS 1984            
          );

      $("#osmedit").html("<a href='http://www.openstreetmap.org/edit#map=" + map.getZoom() + "/" + latlon.lat.toFixed(5) + "/" + latlon.lon.toFixed(5) + "' target='_blank'>[OSMEdit]</a>");

    });  
  </script>
EOD;
?>