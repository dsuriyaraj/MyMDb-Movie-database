<!DOCTYPE html>
<!--
Name            : Suriyaraj Dhanasekaran
Student#        : 87500147   
Description     : For the given actor, it checks in the central database to get all the movies the actor has acted.
-->

<!--common Top part-->
<?php include("top.html"); ?>

<!--common part, between search-all.php and search-kevin.php-->
<?php include("common.php"); ?>
	
<?php
	try
	{
		$id=$id->fetchColumn(0);
		#Query to fetch all the movies acted by a specified actor
		$rows=$db->query("select m.name title, m.year from actors a, movies m, roles r where 1=1
						  and a.id=$id and a.id=r.actor_id and r.movie_id=m.id
						  order by year desc, title asc");
    }
	catch (PDOException $e) 
	{
		header ("HTTP/1.1 500 Server Error");
		die("HTTP/1.1 500 Server Error: Database Unavailable ({$e->getMessage()})");
	}
?>

<!--Descriptive level-1 heading-->					  
<h2>Results for <?php echo $firstname." ".$lastname;?></h2>
<?php
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
<div id="table_admin" class="span7">
	<table>
		<!--Table caption-->
		<caption>Films Acted by <?php echo $firstname." ".$lastname;?></caption>
		<tr>
			<th>#</th>
			<th>Title</th>
			<th>Year</th>
		</tr>
		<?php 
			foreach ($Movies as $Movie)
			{
		?>
		<tr <?php 
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
<?php }else { ?>
<h3>Actor <?php echo $firstname." ".$lastname;?> not found.</h3>
<?php } ?>	

<!--common Bottom part-->
<?php include("bottom.html"); ?>