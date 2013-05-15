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

//$ge->scanGalaxy(1);
//$ge->scanGalaxy(2,1,250);
//$ge->scanGalaxy(3,150,500);
//$ge->scanGalaxy(4);

//exit; 

$ge -> scanAllOverview();
$ge -> scanAllFleetInfo();

// save the planet info for use later to speen things up, maybe. 
//$datastring = serialize($ge);
//file_put_contents("ge.store",$datastring);

//$ge = unserialize( file_get_contents('ge.store') );

//$ge ->displayPlanets();

//$ge->displayPlanets();

/*
$attacklist = $ge ->scanSpyReport(true,true);
$ge->attackList = $attacklist;

$ge = unserialize( file_get_contents('ge.store') ); 

var_dump($ge->attackList);

*/

//$ge->scanGalaxy(1);
//$ge->scanGalaxy(2);
//$ge->scanGalaxy(3);
//$ge->scanGalaxy(4);

// 0 is spy reports
$ge->deleteMessageCat('0');

//$ge->probeInactives(1);
//$ge->probeInactives(2);
//$ge->probeInactives(3);
//$ge->probeInactives(4);

$ge->probeInactives(3,1100,1,100);


//$ge->probeFarmers(1);
//$ge->probeFarmers(2);
//$ge->probeFarmers(3);
//$ge->probeFarmers(4);

echo "Sleeping for a bit!\n";
$ge->sleep(0,1,0,0);

//$attacklist = $ge ->scanFarmReport();

$attacklist = $ge ->scanSpyReport(true,true);

//$attacklist = unserialize( file_get_contents('attacklist.store') ); 

$datastring = serialize($attacklist);
file_put_contents("attacklist.store",$datastring);

$list = array();
$i = 0;
foreach ($attacklist as $info){
	
	$info['total'] = intval($info['total']);
		
	while(key_exists($info['total'],$list))	$info['total']++;
	
	$list[ $info['total'] ] = $info;
	
	$i++;
} 

echo "Kill List\n";
ksort($list);
//$list = array_reverse($list,true);
$loopwaitcount = 0; // we will use this to sleep if we find alot of bad planets. 

$fleetToSmallCount = 0;

while($target = array_pop($list)){
		
	echo "Attacking: ".$target['coords']. " Mats: ".$target['total']."\n";
		
	if($target['total'] < 1200) {
				
		echo "Less then 1200k - not worth my time\n";
		continue;
		
	}	
		
	$smallNeeded = round($target['total']/10); // small cargo holds 5k, we only recover 1/2 the mats, so /10 + 1 to keep metal down and crystal up. 
	
	echo "{$target['galaxy']},{$target['system']},$smallNeeded\n";
	
	$attacker = $ge->changeFarmPlanet($target['galaxy'],$target['system'],$smallNeeded);
	
	
	if($attacker['distance'] > 250 ){
	//if($attacker['distance'] < 75 || $attacker['distance'] > 150){
	
		if($target['total'] > 2000) {
			
			echo "\n*** TOO FAR AWAY BUT ALOT OF MATS SO WE HITTING ANYWAY!!!***\n";
			echo "\n*** TOO FAR AWAY BUT ALOT OF MATS SO WE HITTING ANYWAY!!!***\n";
			
		}else {
			echo "To far away, skipping\n";
			continue;
		}
	}
	
	$smallAvailable = $attacker['count'];
	
	if($smallAvailable == false){
	//if($smallAvailable === false || $smallAvailable < $smallNeeded){
				
		array_unshift($list,$target);
		echo "Change Farm Planet returned false, it shouldnt do taht. \n";
		echo "Going around it for now.\n";
		
		$loopwaitcount++;
		
		if($loopwaitcount > 10){
			
			$secondDelay = $ge -> getNextOpenFleetSlotSeconds();
		
			echo "Sleeping $secondDelay + 10 then trying somewhere else.\n";
			
			$ge->sleep( ($secondDelay + 10) );
			
			//sleep($secondDelay + 10);	
			
			$loopwaitcount = 0;
			
		}
		
		continue;
	}
	
	if( ($smallAvailable*2) < $smallNeeded){
		
		
		if($smallAvailable > 100){
			
			echo "**** We have less then half of what we need, but we have at least 100!\n";
			echo "There are lots of stuff there. Maybe you should redirect from other planets. ****\n";
							
			
		} else {
		
			echo "We have less then half of what we need, lets go around\n";
			echo "We may need to sleep a little too\n";
			echo "we will sleep a minute for luck!\n";
			
			$ge->sleep(60);
				
			$fleetToSmallCount++;
			if($fleetToSmallCount > 5){
				
				$fleetToSmallCount = 0;
				
				echo "Lets drop it and continue\n";
				
				continue;
							
			} else {
				
				// just go around for now
				echo "Lets go around.\n";
				array_unshift($list,$target);
				continue;			
				
			}
		
		}
	}
	
	$fleetsize = ($smallNeeded > $smallAvailable) ? $smallAvailable : $smallNeeded; 
		
	echo "We have $smallAvailable small cargo available - $smallNeeded needed\n";
	echo "We are using {$ge->fleetSlotsUsed}/$ge->fleetSlotsTotal fleet slots\n";
	
	// check for fleet slots.
	
	if($ge->fleetSlotsUsed >= ($ge->fleetSlotsTotal -1) ){
		
		// then we have to wait. 
		// we have to go get a list of the incoming fleets
		// wait that many seconds plus a little. and then keep going. 
		
		$secondDelay = $ge -> getNextOpenFleetSlotSeconds();
		
		array_unshift($list,$target);
		
		echo "Too Many fleet slots. Going to sleep then start up again\n";
		
		echo "Sleeping $secondDelay + 10 then attacking\n";
		
		$ge->sleep( ($secondDelay + 10) );
		//sleep($secondDelay + 10);	
		//exit;
		
		// force a change, planet could have changed while we were waiting. 
		$ge->changePlanet($ge->currentPlanet,true);
		
		//continue;
	}
	
	// ATTACK!!!!!!!!!
	
	$ge -> sendFarmMission($target['galaxy'],$target['system'],$target['position'],$fleetsize);		
	
	//update fleet count
	$ge->planetInfo[$ge->currentPlanet]['ships']['ship202']['count'] = $ge->planetInfo[$ge->currentPlanet]['ships']['ship202']['count'] - $fleetsize;
			
}

// ok we are done
// lets see when we can start over again

$secondDelay = $ge -> getNextOpenFleetSlotSeconds(true);

$timeDelay = ($secondDelay + 20)/2;

echo "We are done, lets sleep till all the attackers have attacked, and start again. $timeDelay = ($secondDelay + 20)/2\n";

$ge->sleep($timeDelay);

echo "Running round 2!!!";

//exec('php test.php');

exit;
