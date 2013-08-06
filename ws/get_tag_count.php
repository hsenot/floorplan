<?php
/**
 * Returns a list of tags and their associated count (for a given schema)
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
    # Connect to PostgreSQL database	
	$pgconn = pgConnection();

	# Construct SQL query
	$sql = "SELECT label,icon,count(*) as c FROM ".$schema.".tag t,".$schema.".tag_building tb WHERE tb.tag_id=t.id GROUP BY t.label,t.icon ORDER BY c DESC,label";

    /*** fetch into an PDOStatement object ***/
	$recordSet = $pgconn->prepare($sql);
	$recordSet->execute();

	header("Content-Type: application/json");
	echo rs2json($recordSet,"rows");
}
catch (Exception $e) {
	trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
}

?>