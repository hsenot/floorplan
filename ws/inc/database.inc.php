<?php

	function pgConnection() {
		try {
			# Connect to PostgreSQL database
			$conn = new PDO ("pgsql:host=localhost;dbname=building;port=54321","solar","solar", array(PDO::ATTR_PERSISTENT => true));
			return $conn;
		}
		catch (Exception $e) {
			trigger_error("Caught Exception: " . $e->getMessage(), E_USER_ERROR);
		}
	}

?>