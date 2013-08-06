<?php
/**
 * Returns the bounding box for a tagged layer (within a schema)
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
    $tag_label = $_REQUEST['tag'];
    # Connect to PostgreSQL database
    $conn = pgConnection();
    # Calculating the extent of the layer formed by buildings tagged a certain way
    $sql = "select st_xmin(b) as xmin,st_ymin(b) as ymin,st_xmax(b) as xmax,st_ymax(b) as ymax from (SELECT ST_Extent(b.the_geom) as b FROM ".$schema.".building b,".$schema.".tag_building tb, ".$schema.".tag t WHERE b.status <> 9 AND b.id=tb.building_id AND tb.tag_id=t.id AND t.label='".$tag_label."') t";
    #echo $sql."\n";

    $recordSet = $conn->prepare($sql);
    $recordSet->execute();

    # Build GeoJSON
    echo rs2json($recordSet,"rows");
}
catch (Exception $e) {
  trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
}

?>