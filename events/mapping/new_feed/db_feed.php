
<?php
echo "begin". "<br/>";
  function random_hex_color(){
    return sprintf("%02X%02X%02X", mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
  }

require_once("../../config_database/config.php");

	$BASE_URL = "https://query.yahooapis.com/v1/public/yql";
     
    // Form YQL query and build URI to YQL Web service
    $yql_query = "select latitude,longitude,name,description from upcoming.events where woeid in (select woeid from geo.places where text='pittsburgh, pa' limit 1) and max_date = '2011-10-10'";
    $yql_query_url = $BASE_URL . "?q=" . urlencode($yql_query) . "&format=json";
//echo $yql_query_url;
    // Make call with cURL
    $session = curl_init($yql_query_url);
    curl_setopt($session, CURLOPT_RETURNTRANSFER,true);
    $json = curl_exec($session);
    // Convert JSON to PHP object 
    $phpObj =  json_decode($json);


    // Confirm that results were returned before parsing
    if(!is_null($phpObj->query->results)){
      // Parse results and extract data to display
      foreach($phpObj->query->results->event as $event){
	$name = $mysqli->real_escape_string($event->name);
	$lat = $mysqli->real_escape_string($event->latitude);
	$long = $mysqli->real_escape_string($event->longitude);
	$desc = $mysqli->real_escape_string($event->description);
	$fill_color = random_hex_color();
	$stroke_color = random_hex_color();
	$sql_idea = "INSERT INTO idea(idea_name, idea_msg, feed_type) VALUES ('$name', '$desc', '1')";
	$sql_circle = "INSERT INTO circle(circle_fillColor, circle_strokeColor) VALUES ('$fill_color', '$stroke_color')";

	if(!$mysqli->query($sql_circle)){
		printf("Failed to insert " . $fill_color . " into database". $mysqli->error. "<br/>");
		var_dump($sql_circle);
	}
	$id_circle = $mysqli->insert_id;
	if(!$mysqli->query($sql_idea)){
		printf("Failed to insert " . $name . " into database". $mysqli->error. "<br/>");
		var_dump($sql_idea);
	}
	$id_idea = $mysqli->insert_id;
	$sql_node = "INSERT INTO node(node_lat, node_long, frn_idea, frn_circle) VALUES ('$lat', '$long', '$id_idea', '$id_circle')";
	if(!$mysqli->query($sql_node)){
		printf("Failed to insert " . $lat . " " . $long . " into database". $mysqli->error);
		var_dump($sql_idea);
	}
	unset($sql_node);
	unset($sql_idea);
	//echo $name. " " . $lat . " " . $long .  "<br/>" . $desc ."<br/> <br/>";
      }
    
    // No results were returned
    
     // $events = "Sorry, no events matching Pittsburgh";
    //}
    // Display results and unset the global array $_GET
    echo $events;
    unset($_GET);
  }
else echo "nope";
?>
