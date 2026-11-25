<?php
require_once __DIR__ . "/config/error_handler.php";
require_once __DIR__ . "/config/session.php";
require_once "db.php";
if($_SERVER["REQUEST_METHOD"]!=="POST"){
	http_response_code(405);
	echo"invalid request.";
	exit;
}
$name = trim($_POST["name"] ? $_POST["name"]:"");
$email = trim($_POST["email"] ?$_POST["email"]:"");
$subject = trim($_POST["subject"] ?$_POST["subject"]:"");
$message = trim($_POST["message"] ?$_POST["message"]:"");

if(
$name === ""||
$email === ""||
$subject === ""||
$message === ""
){
	echo "please fill all fields .";
	exit;
}
if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
	echo "please enter a valid email address.";
	exit;
}
$sql ="
INSERT INTO messages(name,email,subject,message)
VALUES (?, ?, ?, ? )
";
$stmt = $conn->prepare($sql);
if(!$stmt){
	die("prepare failed:" .$conn->error);
}
$stmt->bind_param(
"ssss",
$name,
$email,
$subject,
$message);

if($stmt->execute()){
	echo "success";
}else{
	echo "unable to save your message.";
}
$stmt->close();
$conn->close();
?>
