<?php
/**
 * Returns the count of active buildings (within a schema)
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
	# Opening up DB connection
	$pgconn = pgConnection();

	# SQL query construction
	$sql = "SELECT count(*) as c FROM ".$schema.".building WHERE status <> 9";
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