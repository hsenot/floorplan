<?php
/**
 * Inserts a new building (in the schema)
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
	$p_geom = isset($_REQUEST['geom']) ? $_REQUEST['geom'] : '';

	# Opening up DB connection
	$pgconn = pgConnection();

    # Getting the building number (somehow curr_val does not always work)
	$sql = "SELECT nextval('".$schema.".building_id_seq') as c";
	#echo $sql;
	$recordSet = $pgconn->prepare($sql);
	$recordSet->execute();

	while ($row  = $recordSet->fetch(PDO::FETCH_ASSOC))
	{
		# Have to name the column instead of using index 0 to indicate 1st column
		$building_id = $row['c'];
	}

	# Inserting the building
	# Status: 0 => imported, 1=> created by user, 2=> updated by user, 9 => deleted
	$sql = "INSERT INTO ".$schema.".building(id,status,the_geom) VALUES (-".$building_id.",1,ST_GeomFromText('".$p_geom."',4326));";
	#echo $sql;
	$recordSet = $pgconn->prepare($sql);
	$recordSet->execute();


	exit('{"success":"true","building_id":"-'.$building_id.'"}');
}
catch (Exception $e) {
	trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
}

?>