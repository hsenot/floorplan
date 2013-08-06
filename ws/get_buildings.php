<?php
/**
 * Returns a GeoJSON of BBOXed, possibly tagged buildings (within a schema)
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
    # Bounding box processing
    $bbox = $_REQUEST['bbox'];
    $bbox=trim($bbox);
    $ca = split(",",$bbox);
    # Calculating map center based on bounding box
    $map_center_lng = ($ca[0]+$ca[2])/2;
    $map_center_lat = ($ca[1]+$ca[3])/2;

    # Existence of tag means that we search by tag
    if (isset($_REQUEST['tag']) and !empty($_REQUEST['tag']))
    {
        $tag_label = $_REQUEST['tag'];
        $sql_tag_part = " ,".$schema.".tag_building tb, ".$schema.".tag t WHERE b.id=tb.building_id AND tb.tag_id=t.id AND t.label='".$tag_label."' AND";
    }
    else
    {
        $sql_tag_part = " WHERE";
    }

    # Opening up DB connection
    $conn = pgConnection();

    # Build SQL SELECT statement and return the geometry as a GeoJSON element in EPSG: 4326
    $sql = "SELECT b.id, (select count(*) from ".$schema.".tag_building tb where tb.building_id=b.id) as c,st_asgeojson(st_transform(b.the_geom,4326),5) AS geojson".
        " FROM ".$schema.".building b".
        $sql_tag_part .
        " ST_Intersects(ST_Envelope(ST_Union(ST_SetSRID(ST_Point(".$ca[0].",".$ca[1]."),4326),ST_SetSRID(ST_Point(".$ca[2].",".$ca[3]."),4326))),b.the_geom)".
        " AND status <> 9".
        " ORDER BY ST_Distance(b.the_geom,ST_SetSRID(ST_Point(".$map_center_lng.",".$map_center_lat."),4326)) ASC".
        " LIMIT 100";

    #echo $sql;
    $recordSet = $conn->prepare($sql);
    $recordSet->execute();

    # Build GeoJSON
    echo rs2geojson($recordSet);
}
catch (Exception $e) {
    trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
}

?>