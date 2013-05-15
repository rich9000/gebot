<?

error_reporting(E_ALL);
ini_set('display_errors', '1');

//include_once('../libs/ge-functions.php');
include_once('../libs/class.ge.php');
include_once('../libs/db-functions.php');
include_once('../config/db.php');

$USER = 'rich9000';
$PASSWORD = 'fish1234';

$host = 'ge2.seazonegames.com';

$sessID = 'o0mn5u9uv0ala1m66tpi0q0sj1';

$uuid = '63339-33942513251521e5573b4d5.49296834';

$ge = new GalacticEmpire($USER,$PASSWORD,$host);
$ge -> sessID = $sessID;
$ge -> uuid = $uuid;




$command = "/game.php?page=galaxy&mode=1&galaxy=$galaxy&system=$system";
$results = $ge->sendGetRequest($command);


echo $results;

exit;










//game.php?page=buildings
//game.php?page=buildings&mode=research
//game.php?page=buildings&mode=fleet
//game.php?page=buildings&mode=defense





/*

$results = $ge->sendGetRequest('/game.php?page=buildings&mode=defense');

$buildings = $ge->getStrsBetween($results,'<div class="b_top">' ,'<div class="desc">');

foreach ($buildings as $html=>$foo){
		
	$namepart = $ge->getStrBetween($html,'<h6>','</h6>');
	$nameparts = explode(' (',$namepart);
	
	$name = $nameparts[0];
	
	if( isset($nameparts[1]) ){
		
		
		$level = trim($nameparts[1],'Available: )');
		
	} else{
		
		$level = 0;
	
	}
	
	$bid = $ge->getStrBetween($html,'&gid=',"',");
	
	
	$metalparts = $ge->getStrBetween($html,'<td>Metal:</td><td class="','</td>');
	if(!$metalparts){
		$metalcost = 0;
	} else {
		$metalsplit = explode('">',$metalparts);
		$metalcost = $metalsplit[1];
	}
	
	
	$crystalparts = $ge->getStrBetween($html,'<td>Crystal:</td><td class="','</td>');
	if(!$crystalparts){
		$crystalcost = 0;
	} else {
		$crystalsplit = explode('">',$crystalparts);
		$crystalcost = $crystalsplit[1];
	}
	
	$deuteriumparts = $ge->getStrBetween($html,'<td>Deuterium:</td><td class="','</td>');
	if(!$deuteriumparts){
		$deuteriumcost = 0;
	} else {
		$deuteriumsplit = explode('">',$deuteriumparts);
		$deuteriumcost = $deuteriumsplit[1];
	}
	
	
	$trimmetalcost = $ge->trimMaterial($metalcost) * 1000;
	$trimcrystalcost = $ge->trimMaterial($crystalcost) * 1000;
	$trimdeuteriumcost = $ge->trimMaterial($deuteriumcost) * 1000;
	
	
	
	echo "Name:$name Available:$level ID:$bid\n";
	echo "Upgrade Cost $metalcost ($trimmetalcost) / $crystalcost ($trimcrystalcost) / $deuteriumcost ($trimdeuteriumcost)\n\n";
	
	
	//echo $html."\n\n\n\n";
	
	//exit;
	
}



exit;









$results = $ge->sendGetRequest('/game.php?page=buildings&mode=fleet');

$buildings = $ge->getStrsBetween($results,'<div class="b_top">' ,'<div class="desc">');

foreach ($buildings as $html=>$foo){
		
	$namepart = $ge->getStrBetween($html,'<h6>','</h6>');
	$nameparts = explode(' (',$namepart);
	
	$name = $nameparts[0];
	
	if( isset($nameparts[1]) ){
		
		
		$level = trim($nameparts[1],'Available: )');
		
	} else{
		
		$level = 0;
	
	}
	
	$bid = $ge->getStrBetween($html,'&gid=',"',");
	
	
	$metalparts = $ge->getStrBetween($html,'<td>Metal:</td><td class="','</td>');
	if(!$metalparts){
		$metalcost = 0;
	} else {
		$metalsplit = explode('">',$metalparts);
		$metalcost = $metalsplit[1];
	}
	
	
	$crystalparts = $ge->getStrBetween($html,'<td>Crystal:</td><td class="','</td>');
	if(!$crystalparts){
		$crystalcost = 0;
	} else {
		$crystalsplit = explode('">',$crystalparts);
		$crystalcost = $crystalsplit[1];
	}
	
	$deuteriumparts = $ge->getStrBetween($html,'<td>Deuterium:</td><td class="','</td>');
	if(!$deuteriumparts){
		$deuteriumcost = 0;
	} else {
		$deuteriumsplit = explode('">',$deuteriumparts);
		$deuteriumcost = $deuteriumsplit[1];
	}
	
	
	$trimmetalcost = $ge->trimMaterial($metalcost) * 1000;
	$trimcrystalcost = $ge->trimMaterial($crystalcost) * 1000;
	$trimdeuteriumcost = $ge->trimMaterial($deuteriumcost) * 1000;
	
	
	
	echo "Name:$name Available:$level ID:$bid\n";
	echo "Upgrade Cost $metalcost ($trimmetalcost) / $crystalcost ($trimcrystalcost) / $deuteriumcost ($trimdeuteriumcost)\n\n";
	
	
	//echo $html."\n\n\n\n";
	
	//exit;
	
}



exit;





















$results = $ge->sendGetRequest('/game.php?page=buildings&mode=research');

$buildings = $ge->getStrsBetween($results,'<div class="b_top">' ,'<div class="desc">');

foreach ($buildings as $html=>$foo){
		
	$namepart = $ge->getStrBetween($html,'<h6>','</h6>');
	$nameparts = explode(' (',$namepart);
	
	$name = $nameparts[0];
	
	if( isset($nameparts[1]) ){
		
		
		$level = trim($nameparts[1],'level )');
		
	} else{
		
		$level = 0;
	
	}
	
	$bid = $ge->getStrBetween($html,'&gid=',"',");
	
	
	$metalparts = $ge->getStrBetween($html,'<td>Metal:</td><td class="','</td>');
	if(!$metalparts){
		$metalcost = 0;
	} else {
		$metalsplit = explode('">',$metalparts);
		$metalcost = $metalsplit[1];
	}
	
	
	$crystalparts = $ge->getStrBetween($html,'<td>Crystal:</td><td class="','</td>');
	if(!$crystalparts){
		$crystalcost = 0;
	} else {
		$crystalsplit = explode('">',$crystalparts);
		$crystalcost = $crystalsplit[1];
	}
	
	$deuteriumparts = $ge->getStrBetween($html,'<td>Deuterium:</td><td class="','</td>');
	if(!$deuteriumparts){
		$deuteriumcost = 0;
	} else {
		$deuteriumsplit = explode('">',$deuteriumparts);
		$deuteriumcost = $deuteriumsplit[1];
	}
	
	
	$trimmetalcost = $ge->trimMaterial($metalcost) * 1000;
	$trimcrystalcost = $ge->trimMaterial($crystalcost) * 1000;
	$trimdeuteriumcost = $ge->trimMaterial($deuteriumcost) * 1000;
	
	
	
	echo "Name:$name Level:$level ID:$bid\n";
	echo "Upgrade Cost $metalcost ($trimmetalcost) / $crystalcost ($trimcrystalcost) / $deuteriumcost ($trimdeuteriumcost)\n";
	
	
	//echo $html."\n\n\n\n";
	
	//exit;
	
}

*/

$results = $ge->sendGetRequest('/game.php?page=buildings');

$buildings = $ge->getStrsBetween($results,'<div class="b_top">' ,'<div class="desc">');

foreach ($buildings as $html=>$foo){
	
	$namepart = $ge->getStrBetween($html,'<h6>','</h6>');
	$nameparts = explode(' (',$namepart);
	
	$name = $nameparts[0];
	
	if( isset($nameparts[1]) ){
		
		
		$level = trim($nameparts[1],'level )');
		
	} else{
		
		$level = 0;
	
	}
	
	$bid = $ge->getStrBetween($html,'&gid=',"',");
	
	
	$metalparts = $ge->getStrBetween($html,'<td>Metal:</td><td class="','</td>');
	if(!$metalparts){
		$metalcost = 0;
	} else {
		$metalsplit = explode('">',$metalparts);
		$metalcost = $metalsplit[1];
	}
	
	
	$crystalparts = $ge->getStrBetween($html,'<td>Crystal:</td><td class="','</td>');
	if(!$crystalparts){
		$crystalcost = 0;
	} else {
		$crystalsplit = explode('">',$crystalparts);
		$crystalcost = $crystalsplit[1];
	}
	
	$deuteriumparts = $ge->getStrBetween($html,'<td>Deuterium:</td><td class="','</td>');
	if(!$deuteriumparts){
		$deuteriumcost = 0;
	} else {
		$deuteriumsplit = explode('">',$deuteriumparts);
		$deuteriumcost = $deuteriumsplit[1];
	}
	
	
	$trimmetalcost = $ge->trimMaterial($metalcost) * 1000;
	$trimcrystalcost = $ge->trimMaterial($crystalcost) * 1000;
	$trimdeuteriumcost = $ge->trimMaterial($deuteriumcost) * 1000;
	
	
	
	//echo "Name:$name Level:$level ID:$bid\n";
	//echo "Upgrade Cost $metalcost ($trimmetalcost) / $crystalcost ($trimcrystalcost) / $deuteriumcost ($trimdeuteriumcost)\n";
	
	
	//echo $html."\n\n\n\n";
	
	//exit;
	
}

$bgque = $ge->getStrBetween($results,'<ul class="plastic bque">','</ul>');

$bqueli = $ge->getStrsBetween($bgque,'<li>' ,'</li>');

$qsize = count($bqueli);

echo "Q Size $qsize\n";

if($qsize > 0){
	$first = true;
	foreach ($bqueli as $li => $blah){
		
		echo "$li \n\n";
		
			
		
		$first = false;
		
	}

}
exit;



echo $results;
