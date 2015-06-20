<!--
Name            : Suriyaraj Dhanasekaran
Student#        : 87500147   
Description     : This PHP File contains the common code shared between search-all.php and search-kevin.php
-->

<?php
	try
	{
		#Connecting to central database
		$dbunix_socket = '/ubc/icics/mss/cics516/db/cur/mysql/mysql.sock';
		$dbuser        = 'cics516';
		$dbpass        = 'cics516password';
		$dbname        = 'cics516';
		try 
		{
			$db = new PDO ("mysql:unix_socket=$dbunix_socket;dbname=$dbname", $dbuser, $dbpass);
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} 
		catch (PDOException $e) 
		{
			header ("HTTP/1.1 500 Server Error");
			die("HTTP/1.1 500 Server Error: Database Unavailable ({$e->getMessage()})");
		}
		
		#Getting the user entered values through GET method
		$firstname=trim($_GET["firstname"]); 
		$lastname=trim($_GET["lastname"]);
		$fname=$firstname.'%';
		$fname = strtolower($db->quote($fname));
		$lname = strtolower($db->quote($lastname));
		$number=1;
		
		#Query to pick the correct ID, if collisions exists.
		$id=$db->query("select min(id) from actors a where 1=1
						and id in(select id from actors where 1=1 and lower(first_name) like $fname and lower(last_name)=$lname) 
						and film_count=(select max(film_count) from actors b where lower(first_name) like $fname and lower(last_name)=$lname)
						group by id;");
	}
	catch (PDOException $e) 
	{
		header ("HTTP/1.1 500 Server Error");
		die("HTTP/1.1 500 Server Error: Database Unavailable ({$e->getMessage()})");
	}
?>