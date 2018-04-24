<?php

/* database connect */
function dbopen(){
	$conf = & $GLOBALS['config']['db'];
	$dbcon = @mysqli_connect($conf['hostname'], $conf['username'], $conf['password'], $conf['database']);
	if(!$dbcon){
		printf('Could not connect to DB : '.mysqli_connect_error());
		return;
	}

	return $dbcon;
}

/* database close */
function dbclose($dbcon){
	if ($dbcon) mysqli_close($dbcon);
}

/* run query - select list */
function select_list($sql){

	$con = dbopen();

	$result = mysqli_query($con, $sql);
	if($result){
		$num = mysqli_num_rows($result);
		if($result and $num > 0){
			$rows = $result;
		}
	}


	dbclose($con);
	if (!isset($rows)){ return false;}
	return $rows;
}

function select_array($sql){

	$con = dbopen();
	$result = mysqli_query($con, $sql);
	$return_array=[];
	while ($row = mysqli_fetch_assoc($result)) {
		array_push($return_array, $row);
	}

	dbclose($con);
	if (isset($return_array)){ return $return_array; } else {return false;}
}

/* run query - select view */
function select_view($sql){
	$con = dbopen();

	$result = mysqli_query($con, $sql);
	if($result){
		$num = mysqli_num_rows($result);
		if($result and $num > 0){
			$row = mysqli_fetch_assoc($result);
		}
	}

	dbclose($con);
	if (isset($row)){ return $row; } else {return false;}
}

/* run query - insert */
function insert($sql){
	$con = dbopen();

	$result = mysqli_query($con, $sql);
	if($result){
		$id = mysqli_insert_id($con);
	} else {
		$id = 0;
	}

	dbclose($con);

	return $id;
}

/* run query - update, delete */
function update($sql){
	$con = dbopen();

	$result = mysqli_query($con, $sql);

	dbclose($con);

	return $result;
}

/* Clean user input before using it as a sort value for database */
function clean_db_sort($raw_user_input_var) {
	$con = dbopen();
	$remove_chars = array('&', '"', "'", '<', '>'); // remove harmful characters
	
	$clean_user_input = strip_tags($raw_user_input_var);
	$clean_user_input = str_replace($remove_chars, '', $clean_user_input);
	$clean_user_input = mysqli_real_escape_string($con, $clean_user_input);
	
	return $clean_user_input;
	dbclose($con);
}
?>