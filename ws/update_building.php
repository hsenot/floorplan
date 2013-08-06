<?php
/**
 * Updates an existing building and its tags (within a schema)
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
	$p_name = isset($_REQUEST['building_name']) ? $_REQUEST['building_name'] : '';
	$p_name = str_replace("'", "''", $p_name);
	$p_tags = isset($_REQUEST['hidden-tags']) ? $_REQUEST['hidden-tags'] : '';
	$p_tags_arr=explode(",",$p_tags);
	# Merging back the array and adding the quotes
	$p_tags_list="'".implode("','",$p_tags_arr)."'";
	$p_building_id = $_REQUEST['building_id'];

	# Opening up DB connection
	$pgconn = pgConnection();

	# Inserting the observation
	# Status: 0 => imported, 1=> created by user, 2=> updated by user, 9 => deleted
	$sql = "UPDATE ".$schema.".building SET (name,status) = ('".$p_name."',2) WHERE id=".$p_building_id;
	#echo $sql;
	$recordSet = $pgconn->prepare($sql);
	$recordSet->execute();

	# Delete previous tag associations
	$sql = "DELETE FROM ".$schema.".tag_building WHERE building_id=".$p_building_id;
	#echo $sql;
	$recordSet = $pgconn->prepare($sql);
	$recordSet->execute();

	# Only if there are some tags associted to this update
	if (!empty($p_tags))
	{
		# Insert all tags that were not previously in the database
		$sql = "INSERT INTO ".$schema.".tag(label) SELECT x FROM unnest(array[".$p_tags_list."]) AS x WHERE x NOT IN (SELECT label FROM ".$schema.".tag)";
		#echo $sql;
		$recordSet = $pgconn->prepare($sql);
		$recordSet->execute();

		# Now, associate the new tags to the building
		# It is assumed here that the tags already exist in the tag table
		$sql = "INSERT INTO ".$schema.".tag_building(tag_id,building_id) SELECT t.id,".$p_building_id." FROM ".$schema.".tag t WHERE t.label in (".$p_tags_list.")";
		#echo $sql;
		$recordSet = $pgconn->prepare($sql);
		$recordSet->execute();
	}

	# Remove all tags that have no more association to any building
	$sql = "DELETE FROM ".$schema.".tag t WHERE t.id not in (SELECT distinct(tb.tag_id) FROM ".$schema.".tag_building tb)";
	#echo $sql;
	$recordSet = $pgconn->prepare($sql);
	$recordSet->execute();

	exit('{"success":"true","building_id":"'.$p_building_id.'"}');
}
catch (Exception $e) {
	trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
}

?>