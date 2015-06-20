<!DOCTYPE html>
<!--
Name            : Suriyaraj Dhanasekaran
Student#        : 87500147   
Description     : For the given actor, it checks in the central database to get all the movies the actor and Kevin Bacon have acted together.
-->

<!--common Top part-->
<?php include("top.html"); ?>

<!--common part, between search-all.php and search-kevin.php-->
<?php include("common.php"); ?>

<?php
	if ($id->rowCount() > 0) 
	{
		$id=$id->fetchColumn(0);
?>

<!--Descriptive level-1 heading-->	
<h2>Results for <?php echo $firstname." ".$lastname;?></h2>
<?php
	try
	{
		#Query to fetch the movies acted by a specified actor and Kevin bacon
		$rows=$db->query("select title, year from 
						 (select m.name title, m.year from actors a, movies m, roles r
						  where 1=1 and a.id=$id and a.id=r.actor_id and r.movie_id=m.id) a
						  inner join
						 (select m.name title, m.year from actors a, movies m, roles r
						  where 1=1 and a.first_name='Kevin' and a.last_name='Bacon' and a.id=r.actor_id and r.movie_id=m.id) b
						  using (title, year)
						  order by year desc, title asc");
	}
	catch (PDOException $e) 
	{
		header ("HTTP/1.1 500 Server Error");
		die("HTTP/1.1 500 Server Error: Database Unavailable ({$e->getMessage()})");
	}
	if ($rows->rowCount() > 0) 
	{
		$Movies = array();
		while ($row = $rows->fetch())
		{
		  $Movies[] = array('#'=> $number, 'Title' => $row['title'], 'Year' => $row['year']);
		  $number+=1;
		}
		
		$db=null; #closing the central database connection.
?>
<div>
	<table>
		<!--Table caption-->
		<caption>Films with <?php echo $firstname." ".$lastname;?> and Kevin Bacon</caption>
		<tr>
			<th>#</th>
			<th>Title</th>
			<th>Year</th>
		</tr>
		<?php 
			foreach ($Movies as $Movie)
			{
		?>
		<tr <?php #to make zebra row color
				if (($Movie['#']%2)!=0)
				{
					print "class=\"odd\"";
				}
			 ?>> 
			<td><?=$Movie['#']?></td>
			<td><?=$Movie['Title']?></td>
			<td><?=$Movie['Year']?></td>
		</tr>
		<?php } ?>
	</table>
</div>

<?php } else { ?>
<h3><?php echo $firstname." ".$lastname;?> wasn<?php echo "'";?>t in any films with Kevin Bacon.</h3>
<?php } } else { ?>
<h3>Actor <?php echo $firstname." ".$lastname;?> not found.</h3>
<?php } ?>

<!--common Bottom part-->
<?php include("bottom.html"); ?>