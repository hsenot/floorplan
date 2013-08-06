<?php
/**
 * Deletes a building and its tag associations (within a schema)
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
	$p_building_ids = $_REQUEST['building_ids'];

	# Opening up DB connection
	$pgconn = pgConnection();

	# Deleting the building by setting its status to 9
	# Status: 0 => imported, 1=> created by user, 2=> updated by user, 9 => deleted
	$sql = "UPDATE ".$schema.".building SET status=9 WHERE id in (".$p_building_ids.")";
	#echo $sql;
	$recordSet = $pgconn->prepare($sql);
	$recordSet->execute();

	# Update the tag building table to reflect the fact that the building is no longer tagged
	$sql = "DELETE FROM ".$schema.".tag_building WHERE building_id in (".$p_building_ids.")";
	#echo $sql;
	$recordSet = $pgconn->prepare($sql);
	$recordSet->execute();

	# Remove all tags that have no more association to any building
	$sql = "DELETE FROM ".$schema.".tag t WHERE t.id not in (SELECT distinct(tb.tag_id) FROM ".$schema.".tag_building tb)";
	#echo $sql;
	$recordSet = $pgconn->prepare($sql);
	$recordSet->execute();

	exit('{"success":"true","building_ids":"'.$p_building_ids.'"}');
}
catch (Exception $e) {
	trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
}

?>