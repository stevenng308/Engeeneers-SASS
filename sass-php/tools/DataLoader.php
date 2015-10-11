<?
	require_once('../Database.php');

	$database = new Database();
	if($database->getDbConnection()->connect_error){
		die('ERROR!! CONNECTION FAILED! ' . $database->getDbConnection()->connect_error  . "\r\n");
	} else {
		echo 'SUCCESS!! ' . $database->getDbConnection()->host_info . "\r\n";
	}

	if($database->checkTables()){
		echo "data loading complete. \r\n";
	} else {
		echo "ERROR!! Cannot load data because a table was not created. \r\n"; //EXCEPTION
	}
?>
