<?php
/**
 * Returns the list of allowable tags (within a schema) to support typeAhead
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

	# Construct SQL query
	$sql = "SELECT label as tag FROM ".$schema.".tag ORDER BY label";

    /*** fetch into an PDOStatement object ***/
	$recordSet = $pgconn->prepare($sql);
	$recordSet->execute();

	header("Content-Type: application/json");
	echo rs2json($recordSet,"tags");
}
catch (Exception $e) {
	trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
}

?>