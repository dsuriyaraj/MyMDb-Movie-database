<!DOCTYPE html>
<!--
Name            : Suriyaraj Dhanasekaran
Student#        : 87500147
Tasks Completed : New movie will be added to the private remote database and then its corresponding record will be updated/inserted in the directors_genre table.   
Description: 
	1.User enters the movie details.
	2.Perform the validation for the entered details.   
	3.Display validation errors(check for database constraints)if any, else insert the movie in the database.
	4.Upon successful insertion, Update(record exists)/insert(record doesn't exists) the corresponding record in the directors_genre table.
	5.Close the database connection
Assumption:
	1.Each movie consists of a 3 actors.
	2.Each movie is directed by a single director.
	3.Each movie belongs to only one genre.
	4.Movie Year is defaulted to 2015.
-->

<?php include("top.html"); ?>
<?php
	try
	{	
		#Connecting to the private remote database
		$db = new PDO("mysql:dbname=dsuriya;host=dbserver.mss.icics.ubc.ca", "dsuriya", "a87500147");
		#Query to fetch all the movie names
		$movies=$db->query("select name from movies");
		#Query to fetch all the actors
		$actors1=$db->query("select first_name, last_name from actors order by first_name");
		$actors2=$db->query("select first_name, last_name from actors order by first_name");
		$actors3=$db->query("select first_name, last_name from actors order by first_name");
		#Query to fetch all the directors
		$directors=$db->query("select first_name, last_name from directors");
		#Query to fetch the genres
		$genres=$db->query("select distinct genre from movies_genres");
		$movie_name="";
	} 
	catch (PDOException $e) 
	{
		header ("HTTP/1.1 500 Server Error");
		die("HTTP/1.1 500 Server Error: Database Unavailable ({$e->getMessage()})");
	}
?>

<h2>Add a movie to private MyMDb database</h2>
<p>Enter the details for a new movie</p>
<div>
	<form method="post" action="add-film.php">
		<fieldset>
			<legend>Add movie</legend>
			<div>
				<span>Movie Name</span><input type="text" name="MovieName" size="12" pattern="[a-zA-Z0-9\s]{2,30}" required="required" placeholder="title" value="<?php echo $movie_name;?>">
				<br/><br/>
				<label>Actor-1<select name="Actor1">
					<?php
						while ($actor = $actors1->fetch())
						{ 
					?>		<option value="<?php echo $actor['first_name'].", ".$actor['last_name'];?>"><?php echo $actor['first_name'].", ".$actor['last_name'];?></option>
				<?php } ?>
				</select></label>
				<br/><br/>
				<label>Actor-2<select name="Actor2">
					<?php
						while ($actor = $actors2->fetch())
						{ 
					?>		<option value="<?php echo $actor['first_name'].", ".$actor['last_name'];?>"><?php echo $actor['first_name'].", ".$actor['last_name'];?></option>
				<?php } ?>
				</select></label>
				<br/><br/>
				<label>Actor-3<select name="Actor3">
					<?php
						while ($actor = $actors3->fetch())
						{ 
					?>		<option value="<?php echo $actor['first_name'].", ".$actor['last_name'];?>"><?php echo $actor['first_name'].", ".$actor['last_name'];?></option>
				<?php } ?>
				</select></label>
				<br/><br/>
				<label>Director<select name="Director">
					<?php
						while ($director = $directors->fetch())
						{ 
					?>		<option value="<?php echo $director['first_name'].", ".$director['last_name'];?>"><?php echo $director['first_name'].", ".$director['last_name'];?></option>
				<?php } ?>
				</select></label>
				<br/><br/>
				<label>Genre<select name="Genre">
					<?php
						while ($genre = $genres->fetch())
						{ 
					?>		<option value="<?php echo $genre['genre'];?>"><?php echo $genre['genre'];?></option>
				<?php } ?>
				</select></label>
				<br/><br/>
				<input name="Add" type="Submit" value="Add" />
			</div>
		</fieldset>
	</form>
</div>
<?php
	#Function to display the validation errors
	function form_errors($errors=array()) 
	{
	  $validation_errors = "";
	  if (!empty($errors)) 
	  {
		foreach ($errors as $key => $error) 
		{
			$validation_errors .= "(*){$key}: {$error}<br/>";
		}
	  }
	  return $validation_errors;
	}
	
	if(isset($_POST["Add"]))
	{
		$movie_name=trim($_POST["MovieName"]);
		$actor1=trim($_POST["Actor1"]);
		$actor2=trim($_POST["Actor2"]);
		$actor3=trim($_POST["Actor3"]);
		$director=trim($_POST["Director"]);
		$genre=trim($_POST["Genre"]);
		$validation = array();
		
		#Validate duplicate actors (Unique Constraint)
		if(($actor1==$actor2)||($actor1==$actor3)||($actor2==$actor3))
		{
			$validation['Actors']="Duplicate values Entered";
		}
		
		#Validate Movie name (Not null constraint)
		if (!isset($movie_name) || $movie_name === "") 
		{
			$validation['Movie Name'] = "Value can't be left blank";
		}
		
		#Validate whether the movie already exists (Unique Constraint/Primary key)
		$result=$db->query("select count(1) from movies where name='$movie_name' ");

		if($result && $result->fetchColumn(0)>0)
		{
			$validation['Movie'] = "Already exists";
		}
		
		#calling validation error display function
		$validation_errors=form_errors($validation); 
		
		#proceed only if there is no validation errors
		if($validation_errors=="")
		{
			$Director=explode(", ",$director);
			$d_fname=$Director[0];
			$d_lname=$Director[1];
			
			try
			{
				#Query to fetch the director_id
				$id=$db->query("select id from directors where first_name='$d_fname' and last_name='$d_lname'");
				$d_id=$id->fetchColumn(0);
				
				#Insert in to movie table
				$result=$db->exec("insert into movies(id, name,year)(select max(id)+1 id, '$movie_name' name,'2015' year from movies)");
				$message="Movie is added Successfully in the private database ";
			}
			catch (PDOException $e) 
			{
				header ("HTTP/1.1 500 Server Error");
				die("HTTP/1.1 500 Server Error: Database Unavailable ({$e->getMessage()})");
			} 
			
			#Query to check whether the record exists in director_genre table(to avoid duplicate records)
			$id=$db->query("select count(1) from directors_genres where director_id=$d_id and genre='$genre'");
			if($id && $id->fetchColumn(0)>0)
			{
				#Update the director_genre table (if the record exists)
				try
				{
					$result1=$db->exec("update directors_genres set prob=0.8 where director_id=$d_id and genre='$genre'");
					$message.="and a record is updated in the directors_genres table";
				}
				catch (PDOException $e) 
				{
					header ("HTTP/1.1 500 Server Error");
					die("HTTP/1.1 500 Server Error: Database Unavailable ({$e->getMessage()})");
				}
			}
			else
			{
				#Insert in the director_genre table (if the record doesn't exists)
				try
				{
					$result=$db->exec("insert into directors_genres(director_id, genre, prob) values($d_id,'$genre',1)");
					$message.="and a record is updated in the directors_genres table";
				}
				catch (PDOException $e) 
				{
					header ("HTTP/1.1 500 Server Error");
					die("HTTP/1.1 500 Server Error: Database Unavailable ({$e->getMessage()})");
				} 
			}?>
			
			<!--displaying the success message-->
			<strong><font color="green"> <?php echo $message;?></font></strong>
		<?php }
		else
		{ ?>
			<!--displaying validation errors if any-->
			<strong><font color="red"><?php echo $validation_errors;?></font></strong>
		<?php }
	}
?>

<?php include("bottom.html"); ?>