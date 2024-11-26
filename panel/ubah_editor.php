<?php 
	session_start();
	
	$editor = $_SESSION['editor'];
	
	if($editor == true)
	{
		$_SESSION['editor'] = false;
	}
	else
	{
		$_SESSION['editor'] = true;
	}
	
	header('Location: index.php?pg=banksoal');	
?>