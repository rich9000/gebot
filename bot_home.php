<?

include('config/db.php');




?>

Welcome to the bot home.


<h2>Open Planets</h2>
<?

/*

$galaxy = 1;

while($galaxy < 5){

	$system = 1;

	while($system <= 500){
		
		$sql = mysql_query("select * from bot_planets where planet_position = 8 and planet_galaxy = $galaxy and planet_system = $system");
		
		if(!mysql_num_rows($sql)){
			
			echo "$galaxy:$system:8<br/>\n";
			
		}
		
		
		$system++;
	}
	
	$galaxy++;
}

*/
?>

<h2>List of Inactive Planets</h2>

<table>
<tr>
	<td>Name</td>
	<td>Player</td>
	<td>Rank</td>
	<td>Coords</td>
	<td>M/C/D</td>
	<td>Total Resources</td>
	<td>Farm</td>
	<td>Defense</td>
	<td>Fleet</td>

</tr>
<?


$query = "select *, planet_metal + planet_crystal + planet_deuterium as total from bot_planets where planet_defense_count < 40000 and planet_status ='inactive' order by total desc";
//$sql = mysql_query("select *, planet_metal + planet_crystal + planet_deuterium as total from bot_planets where planet_status = 'inactive' order by planet_galaxy, planet_system, planet_position");

echo $query;
$sql = mysql_query($query);
while($planet = mysql_fetch_assoc($sql)){
	
	
	$fleet = unserialize($planet['planet_fleet_array']);
	$defense = unserialize($planet['planet_defense_array']);
	
	
	
	
	?>	
	
	<tr>	
		<td><?=$planet['planet_name']?></td>
		<td><?=$planet['fk_p_name']?></td>
		<td><?=$planet['fk_p_rank']?></td>
		<td><?=$planet['planet_coords']?></td>
		<td><?=$planet['planet_metal']?>/<?=$planet['planet_crystal']?>/<?=$planet['planet_deuterium']?></td>
		<td><?=$planet['total']?></td>
		<td><?=$planet['planet_farm']?></td>
		<td><?=$planet['planet_defense_count']?></td>
		<td><?=$planet['defense_ratio']?></td>
		<td><?=$planet['planet_fleet_count']?></td>
	</tr>
	
	<?
	if($fleet || $defense){
		?>
		
		<tr><td colspan="9">
			<pre>
<? var_dump($fleet);?>
<? var_dump($defense);?>
		
			</pre>
		</td></tr>
		
		
		<?
		
		
		
	}
	
	
	
}




?>

</table>