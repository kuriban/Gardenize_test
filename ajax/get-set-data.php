<?php

define("INCMS",1);
require "config/db.php";
require "config/getvar.php";


$servers = [];
$servers[] = ["localhost", "root", "root", "qa", true, false];
db_and_errors_init($servers);
define("PREF", "");

$post_get = new GetVarClass("_POST");
$action		= $post_get-> getvar("action");
$username = $post_get->getvar("username");
$text = $post_get->getvar("text");
$question_id = $post_get->getint("question_id");


$data = [];
if($action == "getUserData"){
	if(!$username){
		$date = [
			"error" => "Неверное имя пользователя"
		];
		echo json_encode($date);
		die();
	}
	$sql = "SELECT q.id, q.status, q.text
				FROM " . PREF . "questions AS q
				WHERE username='$username'";
	$result = query($sql);
	$data = fetch_assoc($result);
	$data["username"] = $username;

}elseif($action == "writeNewQuestion"){
	if($text){
		$sql = "INSERT INTO ".PREF."questions (username,text,status)
						VALUES ('$username','$text','not_answered')";
		query($sql);
	}
}elseif($action == "answer"){
	$sql = "UPDATE ".PREF."questions
					SET status='answered'
					WHERE id=$question_id
					LIMIT 1";
	query($sql);

	$sql = "INSERT INTO ".PREF."answers (question_id,text)
					VALUES ($question_id,'$text')";
	query($sql);
}

$data["listAllQuestions"] 			= getQuestions($username,'all');
$data["listNotAnsweredQuestions"] = getQuestions($username,'not_answered');
$data["listAnsweredQuestions"] 		= getQuestions($username,'answered');

echo json_encode($data);

function getQuestions($username,$status){

	switch($status){
		case 'answered': $subquery = " AND status='answered'";
			break;
		case 'not_answered': $subquery = " AND status='not_answered'";
			break;
		default: $subquery = "";
			break;
	}

	$sql = "SELECT id,text
					FROM ".PREF."questions
					WHERE username='$username' $subquery";
	$result = query($sql);
	$questions = fetch_assocs($result);

	return $questions;
}