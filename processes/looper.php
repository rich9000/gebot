<?

error_reporting(E_ALL);
ini_set('display_errors', '1');

include('../config/bot-config.php');

include_once($config['path'].'libs/class.ge.php');
include_once($config['path'].'libs/db-functions.php');
include_once($config['path'].'config/db.php');


$USER = $config['user'];
$PASSWORD = $config['password'];

//$USER = 'rich9000';
//$PASSWORD = 'fish1234';

$host = $config['host'];
$sessID = $config['phpsessid'];
$uuid = $config['uuid'];

//$host = 'ge2.seazonegames.com';
//$sessID = 'o0mn5u9uv0ala1m66tpi0q0sj1';
//$uuid = '63339-33942513251521e5573b4d5.49296834';


// *************** EDIT STUFF BELOW HERE **********************


// galaxies to farm
$checkGalaxies = array(1,2,3,4);

// Pick a sort column;
//$sortColumn = 'metal';
//$sortColumn = 'crystal';
//$sortColumn = 'deuterium';
$sortColumn = 'total';
$minSortColumn = 1200;// numbers are in thousands, so 1200 = 1.2M
$overRideMinWithTotal = 0; // if you use a sort column other then 'total' you can override if the total is creater then this value, 0 for don't check.
$maxDistance = 250;
$overRideMaxDistanceWithTotal = 2000; // override max distance with total
$overRideMaxDistanceWithSortColumn = 0; // override max distance with Sort column

$fleetSlotsToSave = 1; // number of fleet slots to save for fleet save etc. 

// ******************* END EDIT STUFF IN HERE *********************








$ge = new GalacticEmpire($USER,$PASSWORD,$host);
$ge -> sessID = $sessID;
$ge -> uuid = $uuid;

$ge -> scanAllOverview();
$ge -> scanAllFleetInfo();

// save the planet info for use later to speen things up, maybe. 
$datastring = serialize($ge);
file_put_contents("ge.store",$datastring);

//$ge = unserialize( file_get_contents('ge.store') );


$ge ->displayPlanets();


//$ge->scanGalaxy(1);
//$ge->scanGalaxy(2);
//$ge->scanGalaxy(3);
//$ge->scanGalaxy(4);


// lets get the number of galaxies
$numGalaxies = count($checkGalaxies);
$firstPassArray = array();

// start of loop
// lets loop forever
$i=0; // for galaxy pick. 
while(1){

	// get the current galaxy to work on. 
	// current galaxy = the remainder of i devided by the number of total galaxies to check.
	// example: if checkarray = array(1,3,4)
	// 9%3 = 0, 10%3 = 1, 11%3=2, 12%3 = 0; which are the indexes of our checkarray
	$galaxy = $checkGalaxies[$i%$numGalaxies];
	$nextGalaxy = $checkGalaxies[($i+1)%$numGalaxies];
			
	echo "*** Starting Galaxy $galaxy ***\n";
	echo "*** Starting Galaxy $galaxy ***\n";
	echo "*** Starting Galaxy $galaxy ***\n";
		
	// lets clean out the spy reports
	$ge->deleteMessageCat('0');
	
	if(in_array($galaxy,$firstPassArray)){
		// we have already done a first pass, just scan farmers
		$ge->probeFarmers($galaxy);
		
	} else {
		
		$ge->probeInactives($galaxy);
		$firstPassArray[] = $galaxy; // next time it will find it and do a farm probe	
	}
			
	echo "Probes are done. Sleeping for 1.5 minutes to let the reports make it back.\n";
	$ge->sleep(30,1,0,0);
	
	// create an attacklist from the current spy reports. It will only contain defenseless planets
	/* Attack list will look like this:
	$attackList[$attackcount]['total'] = round($totalMats);
	$attackList[$attackcount]['metal'] = $metal;
	$attackList[$attackcount]['crystal'] = $crystal;
	$attackList[$attackcount]['deuterium'] = $deut;				
	$attackList[$attackcount]['galaxy'] = $coords[0];
	$attackList[$attackcount]['system'] = $coords[1];
	$attackList[$attackcount]['position'] = $coords[2];
	$attackList[$attackcount]['planet'] = $coords[2];
	$attackList[$attackcount]['coords'] = "{$coords[0]}:{$coords[1]}:{$coords[2]}"; 
	*/	
	$attacklist = $ge ->scanSpyReport(true,true);
	
	// we will make a new list and have the index be the colomn we want to sort by. 
	// for now its total but we could change it to the resource we need.
	$list = array();
	foreach ($attacklist as $info){
			
		$info[$sortColumn] = intval($info[$sortColumn]); // make sure its an int
		
		while(key_exists($info[$sortColumn],$list))	$info[$sortColumn]++; // add 1 to the value untill its unique. this will make it easier to sort.
				
		// use the sort column as the index so we can sort them by index
		$list[ $info[$sortColumn] ] = $info;		
	}
	
	// we have a new list with our sortcolumn as the index. now sort it.
	
	echo "Sorting Kill List\n";
	ksort($list); // kill list sorted by the key 
	
	// lets loop around our kill list
	
	// not sure if we will use these but we might
	$loopwaitcount = 0; // we will use this to sleep if we find alot of bad planets. 
	$fleetToSmallCount = 0; // not sure what this is for
	
	// we are pulling them off one at a time like a queue.
	// we will put them back in if we are not ready for them
	while($target = array_pop($list)){
	
		echo "Attacking: ".$target['coords']. " $sortColumn: ".$target[$sortColumn]."K Total Mats: ".$target['total']."K\n";
		
		// check if we meet our minimum requirements
		if($target[$sortColumn] < $minSortColumn) {

			// we don't meet it but we have enough total to make it worth our time. 
			if($overRideMinWithTotal != 0 && $target['total'] > $overRideMinWithTotal){

				echo "Not enough $minSortColumn, but total is exceeds $overRideMinWithTotal\n";
									
			} else {
										
				echo "Less then {$minSortColumn}K - not worth my time\n";
				continue;
												
			}
					
		}	
			
		// figure out how many small cargo's we need
		$smallNeeded = round($target['total']/10); // small cargo holds 5k, we only recover 1/2 the mats so
		
		echo "We need $smallNeeded small cargos in {$target['galaxy']}:{$target['system']}\n";
		
		$attacker = $ge->changeFarmPlanet($target['galaxy'],$target['system'],$smallNeeded);
		
		// check distance
		if($attacker['distance'] > $maxDistance ){
		//if($attacker['distance'] < 75 || $attacker['distance'] > 150){
		
			if($overRideMaxDistanceWithTotal != 0 && $target['total'] > $overRideMaxDistanceWithTotal) {
				
				echo "\n*** TOO FAR AWAY BUT OVERRIDEN WITH OVERRIDEMAXDISTANCEWITH TOTAL!!!***\n";
				echo "\n*** TOO FAR AWAY BUT OVERRIDEN WITH OVERRIDEMAXDISTANCEWITH TOTAL!!!***\n";
				echo "\n*** TOO FAR AWAY BUT OVERRIDEN WITH OVERRIDEMAXDISTANCEWITH TOTAL!!!***\n";
				
			} else if($overRideMaxDistanceWithSortColumn != 0 && $target[$sortColumn] > $overRideMaxDistanceWithSortColumn){
			
		
				echo "\n*** TOO FAR AWAY BUT OVERRIDEN WITH OVERRIDEMAXDISTANCEWITH SORTCOLUMN!!!***\n";
				echo "\n*** TOO FAR AWAY BUT OVERRIDEN WITH OVERRIDEMAXDISTANCEWITH SORTCOLUMN!!!***\n";
				echo "\n*** TOO FAR AWAY BUT OVERRIDEN WITH OVERRIDEMAXDISTANCEWITH SORTCOLUMN!!!***\n";
				
			} else {
						
				echo "To far away, skipping\n";
				continue;
				
			}
		}
		
		
		$smallAvailable = $attacker['count'];
	
		// if there are none available, go around
		if($smallAvailable == false){
					
			//put planet back on attack list
			array_unshift($list,$target);
			
			echo "Change Farm Planet returned false, it shouldnt do taht. \n";
			echo "Going around it for now.\n";
			
			// in case we start an infinite loop, sleep a little bit.
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
		
		// we have less then 1/2 the small cargo's we need.
		if( ($smallAvailable*2) < $smallNeeded){
		
			echo "We have less then half of what we need, lets go around\n";
			echo "We may need to sleep a little too\n";
			echo "we will sleep a minute for luck!\n";
			
			$ge->sleep(60);
				
			// we we are looping too fast, just throw them away
			$fleetToSmallCount++;
			if($fleetToSmallCount > 5){
				
				$fleetToSmallCount = 0;
				
				echo "Lets drop it and continue\n";
				
				continue;
							
			} else {
			//else come back to them later
			
				// just go around for now
				echo "Lets go around.\n";
				array_unshift($list,$target);
				continue;			
				
			}
		}
				
		// we have enough, but available might be less then we need so adjust to smallneeded or available which ever is smaller
		$fleetsize = ($smallNeeded > $smallAvailable) ? $smallAvailable : $smallNeeded; 
			
		echo "We have $smallAvailable small cargo available - $smallNeeded needed\n";
		echo "We are using {$ge->fleetSlotsUsed}/$ge->fleetSlotsTotal fleet slots\n";
		
		// check for fleet slots.
		if($ge->fleetSlotsUsed >= ($ge->fleetSlotsTotal - $fleetSlotsToSave) ){
			
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
		
	
	// incriment count one.
	$i++;
} // end of while(1)
		


exit;