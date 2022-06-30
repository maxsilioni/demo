<?php
    $sFile = file_get_contents("demotrack.txt");
    $sFile =  str_replace(",\n",",",$sFile);
    $aFile = explode("\n", $sFile);
//echo "<pre>";
//var_export($aFile);
//echo "</pre>";
//die();

    $aPoints = array();
$i = 0;
    foreach ($aFile as $sJson) {
        if ($i % 2 != 0) {
            $i++;
            continue;
        }
        $aJson = json_decode( $sJson, true );

        if (isset($aJson["message"])) {
            $aTemp = array ("address"=>array("lat"=>$aJson['message']['location']['latitude'], "lng"=>$aJson['message']['location']['longitude']));
        } elseif (isset($aJson["edited_message"])) {
            $aTemp = array ("address"=>array("lat"=>$aJson['edited_message']['location']['latitude'], "lng"=>$aJson['edited_message']['location']['longitude']));
        }
        
        array_push($aPoints, $aTemp);
        $i++;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TrackTest</title>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
    <style>
        html,
body,
#map {
  height: 500px;
  width: 100%;
  margin: 0px;
  padding: 0px
}
    </style>
</head>
<body>
    <div id="map" style="border: 2px solid #3872ac;"></div>
</body>
<script src="https://maps.googleapis.com/maps/api/js?v=3.0&sensor=true&language=es&region=AR" type="text/javascript"></script>

<script type="text/javascript">
    var MapPoints = '<?php echo json_encode($aPoints); ?>';

var MY_MAPTYPE_ID = 'custom_style';

function initialize() {

  if (jQuery('#map').length > 0) {

    var locations = jQuery.parseJSON(MapPoints);

    window.map = new google.maps.Map(document.getElementById('map'), {
      mapTypeId: google.maps.MapTypeId.ROADMAP,
      scrollwheel: false
    });

    var infowindow = new google.maps.InfoWindow();
    var flightPlanCoordinates = [];
    var bounds = new google.maps.LatLngBounds();

    for (i = 0; i < locations.length; i++) {
     if(i == 0 || i == locations.length-1 || i % 10 == 0){
	 marker = new google.maps.Marker({
        position: new google.maps.LatLng(locations[i].address.lat, locations[i].address.lng),
        map: map
      });
    }
     // flightPlanCoordinates.push(marker.getPosition());
flightPlanCoordinates.push({ lat: locations[i].address.lat, lng: locations[i].address.lng });
     bounds.extend(marker.position);
//console.log(marker.getPosition());
    }

    map.fitBounds(bounds);

    var flightPath = new google.maps.Polyline({
      map: map,
      path: flightPlanCoordinates,
      strokeColor: "#FF0000",
      strokeOpacity: 1.0,
      strokeWeight: 2
    });

  }
}
google.maps.event.addDomListener(window, 'load', initialize);

</script>
</html>
