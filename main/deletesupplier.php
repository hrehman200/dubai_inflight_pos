<?php
	include_once('../connect.php');
	$id=$_GET['id'];
	$result = $db->prepare("DELETE FROM supliers WHERE suplier_id= :memid");
	$result->bindParam(':memid', $id);
	$result->execute();
?>