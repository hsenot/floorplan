<?php
/**
 * Reads information about a single building (within a schema)
 */

require_once("inc/database.inc.php");
require_once("inc/json.inc.php");

# Schema should always be passed
$schema='';
if (isset($_REQUEST['schema']) and !empty($_REQUEST['schema']))
{
    $schema=$_REQUEST['schema'];
}
else
{
    exit("Parameter 'schema' is required to use the web service.");
}

try {
	# Parameters
	$p_building_id = $_REQUEST['building_id'];

	# Opening up DB connection
	$pgconn = pgConnection();

	# Selecting the building attributes of interest
	$sql = "SELECT id,st_asgeojson(st_transform(b.the_geom,4326),5) AS geojson FROM ".$schema.".building b WHERE b.id =".$p_building_id;
	#echo $sql;
	$recordSet = $pgconn->prepare($sql);
	$recordSet->execute();

	header("Content-Type: application/json");
	echo rs2geojson($recordSet);
}
catch (Exception $e) {
	trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
}

?>