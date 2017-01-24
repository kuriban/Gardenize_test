<?php

if(!defined("INCMS")) die("Not inside CMS!");

define("DB_DEBUG_NONE", 0);
define("DB_DEBUG_DIE", 1);
define("DB_DEBUG_PROFILE", 2);

function db_connect_error(){
	global $mysqli;
	return $mysqli->connect_errno." ".$mysqli->connect_error;
	}

function db_error(){
	global $mysqli;
	return $mysqli->errno." ".$mysqli->error;
	}

function db_connect($servers){
	global $mysqli;
	foreach($servers as $id=>$server){
		list($dbhost, $dbuser, $dbpass, $dbname, $debug, $on_air) = $server;
		$mysqli = @new mysqli($dbhost, $dbuser, $dbpass, $dbname);
		if(!mysqli_connect_error()){
			define("DB_HOST", $dbhost);
			define("DB_USER", $dbuser);
			define("DB_PASS", $dbpass);
			define("DB_NAME", $dbname);
			define("ON_AIR", $on_air);
			break;
			}
		}

	if($mysqli->connect_errno)
		die ('I cannot connect to the database because: ' . db_connect_error());

	$mysqli->set_charset("utf8");

	return $debug;
	}

function query_silent($sql){
	global $mysqli;
	return $mysqli->query($sql);
	}

function query_debug($sql){
	global $mysqli, $queries_end;
	$time = microtime(true);
	$result = $mysqli->query($sql);
	$queries_end++;
	$delta = round((microtime(true) - $time) * 1000);
	if($delta > 0){
		// echo "\n<!--\n$sql\n$delta ms\n-->\n";
		}
	if($mysqli->errno){
		throw new Exception('Query '.$sql.' failed because: ' . db_error());
		}
	return $result;
	}

function query_profile($sql){
	global $mysqli;
	static $prev = "";

	if($prev){
		$sql2 = "INSERT INTO ".PREF."sql_profiler (sql, duration)
			VALUE $prev";
		$mysqli->query($sql2);
		}

	$time = microtime(true);
	$result = $mysqli->query($sql);
	$duration = microtime(true) - $time;

	$prev = "('$safesql', $duration)";

	return $result;
	}

function db_and_errors_init($servers){
	global $mysqli, $sql_profiler;

	$debug = db_connect($servers);
	define("DEBUGMODE", $debug);

	switch($debug){
	case DB_DEBUG_NONE:
		error_reporting(0);
		function query($sql){
			return query_silent($sql);
			}
		break;
	case DB_DEBUG_DIE:
		error_reporting(E_ALL | E_STRICT);
		function query($sql){
			return query_debug($sql);
			}
		break;
	case DB_DEBUG_PROFILE:
		error_reporting(0);
		function query($sql){
			return query_profile($sql);
			}
		break;
	default:
		die("Unknown debug mode!");
		}
	}

function multi_query($sql){
	global $mysqli;
	$ret = $mysqli->multi_query($sql);
	do{
		if($result = $mysqli->store_result()){
			$result->free();
			}
	} while(@$mysqli->next_result());
	return $ret;
	}

function queryDeferred($sql, $run=false){
	static $sqls = "";
	static $sqls_len = 0;

	if($sql){
		$sqls.= $sql . ";";
		$sqls_len+= strlen($sql) + 1;
	}

	if($sqls_len > 1<<18 || $run){
		multi_query($sqls);
		$sqls = "";
		$sqls_len = 0;
	}
}

function db_stat(){
	global $mysqli;
	return $mysqli->stat();
	}

function insert_id(){
	global $mysqli;
	return $mysqli->insert_id;
	}

function affected_rows(){
	global $mysqli;
	return $mysqli->affected_rows;
	}

function num_rows($result){
	if($result===false || $result===true)
		return 0;
	return $result->num_rows;
	}

function fetch_row($result){
	return ($result===false || $result===true) ? false : $result->fetch_row();
	}

function fetch_rows($result, $depth=0, $unique=false){
	$ret = [];

	$depth = (int)$depth;

	$fields = $result->field_count;
	if($depth > $fields){
		die("Depth $depth > Fields $fields!");
		}

	while($row = $result->fetch_row()){
		$pointer = &$ret;

		for($i = 0; $i < $depth; $i++){
			$elem = array_shift($row);
			$pointer = &$pointer[ $elem ];
			}

		$value = count($row)!=1 ? $row : array_shift($row);
		if($unique){
			$pointer = $value;
			}
		else {
			$pointer[] = $value;
			}
		}

	return $ret;
	}

function fetch_column($result){
	return fetch_rows($result);
	}

function fetch_mapping($result){
	return fetch_rows($result, 1, true);
	}

function fetch_assocs($result){
	$ret = [];
	while($row = fetch_assoc($result)){
		$ret[] = $row;
		}
	return $ret;
	}

function db_result00($sql){
	$result=query($sql);
	$rows=num_rows($result);
	if(!$rows)
		return NULL;
	list($ret)=fetch_row($result);
	return $ret;
	}

function db_results($sql){
	return fetch_rows(query($sql));
	}

function fetch_assoc($result){
	if($result===false || $result===true)
		return false;
	return $result->fetch_assoc();
	}

function fetch_object($result){
	if($result===false || $result===true)
		return false;
	return $result->fetch_object();
	}

function db_escape($str){
	global $mysqli;
	if(!is_array($str))
		$str=$mysqli->real_escape_string($str);
	else {
		foreach($str as $key=>$value){
			$str[$key] = db_escape($value);
			}
		}
	return $str;
	}

function db_unescape($str){
	if(!is_array($str)){
		$sql = "SELECT '$str'";
		$str = (string)db_result00($sql);
		}
	else {
		foreach($str as $key=>$value){
			$str[$key] = db_escape($value);
			}
		}
	return $str;
	}

function db_close(){
	global $mysqli;
	$mysqli->close();
	}

?>
