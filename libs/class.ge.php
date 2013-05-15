<?/**
 * Galactic Empires is a class containing information about and tools for the users account.
 * 
 * Written by Richard Carroll
 * v1.0
 *
 */
class GalacticEmpire {
		
	// setup stuff
	/**
	 * Account user. not currently used.
	 *
	 * @var string
	 */
	private $user;
	
	/**
	 * account password. not currently used
	 *
	 * @var string
	 */
	private $password;
	
	/**
	 * Url of the ge server.
	 *
	 * @var string
	 */
	private $host;

	/**
	 * the uid of the the accouint. hopefully be figured out automagically at some point. 
	 *
	 * @var string
	 */
	public $uuid;
	
	/**
	 * Php Session ID. Currently gotten from packet capture. Hopefully will be able to login and get it at some point. 
	 *
	 * @var string
	 */
	public $sessID;
		
	/**
	 * Current number of active fleets
	 *
	 * @var int
	 */
	public $fleetSlotsUsed = 0;
	
	/**
	 * Our total number of fleet slots.
	 *
	 * @var int
	 */
	public $fleetSlotsTotal = 0;
	
	/**
	 * Number of Enemies Inbound. Found from the overview page. 
	 *
	 * @var int
	 */
	public $fleetSlotsEnemy = 0;
	
	/**
	 * Array of Planet Info indexed by planet Id. 
	 * 
	 * 		$this->planetInfo[$cp]['metal'];
	 * 		$this->planetInfo[$cp]['crystal'];
	 *   	$this->planetInfo[$cp]['deuterium'];			
	 * 		$this->planetInfo[$cp]['energy'];
	 * 		$this->planetInfo[$cp]['coords'] = $coords;
	 * 		$this->planetInfo[$cp]['galaxy'] = $coordparts[0];
	 * 		$this->planetInfo[$cp]['system'] = $coordparts[1];
	 * 		$this->planetInfo[$cp]['position'] = $coordparts[2];
	 * 		$this->planetInfo[$cp]['name']; 
	 * 		$this->planetInfo[$cp]['p_status'];
	 * 	
	 * 		$this->planetInfo[$cp]['buildings']; // an array of building info
	 * 		$this->planetInfo[$cp]['research']; // an array of research info
	 * 		$this->planetInfo[$cp]['fleet']; // an array of fleet info
	 * 		$this->planetInfo[$cp]['defense']; // an array defense info
	 * 					
	 *
	 * @var array
	 */
	public $planetInfo;
	
	/**
	 * The planet Id of the planet we are currently issueing orders from. 
	 * Gets changed when we issue a change planet.
	 *
	 * @var int
	 */
	public $currentPlanet;
	
	/**
	 * The galaxy we are currently located in. Not used for much and may not be accurate. 
	 *
	 * @var int
	 */
	public $currentGalaxy;
	
	/**
	 * This will be the list of information about planets that we want to attack. 
	 * Not fully implimented but will be used when we are multi-tasking
	 *
	 * @var int
	 */
	public $attackList = array();
	
	/**
	 * When we do issue a scan all. This lets us know that we have not scanned before and that we do not know
	 * the current planet we are at. 
	 *
	 * @var bool
	 */
	public $firstScan  = true; // will get set to false after first planet scan	
	
	/**
	 * True/False if we should output debug info to our debug file.
	 *
	 * @var bool
	 */
	public $debug = false; 
	
	/**
	 * File we store debug info to
	 *
	 * @var string
	 */
	public $debugfile = 'class.ge.debug';
	
	/**
	 * Galactic Empire Constructor
	 * 
	 *  It will do the connecting at some point, right now it does very little.
	 *
	 * @param string $user user name
	 * @param string_type $password password
	 * @param string $host url of server
	 * 
	 * @return GalacticEmpire
	 */
	function GalacticEmpire($user,$password,$host){
		
		$this->user = $user;
		$this->password = $password;
		$this->host = $host;
		
		$this->planetInfo = array();
		$this->attackList = array();
			
	}
	
	
	// need to figure out how to do this
	function logIn(){
		
		
		
		
	}
			
	function scanAllOverview(){
		
		
		if($this->firstScan){
			
			$this->firstScan = false;
				
			$this->scanOverview(); // get some info at least 
			// we don't have info for the first one. so lets check another then loop around them all		
			$planets = $this->planetInfo;
			$testplanet = array_pop($planets);
			$this->scanOverview($testplanet['id']);
			//var_dump($testplanet);
			
			foreach ($this->planetInfo as $info){
	
				$this->currentPlanet = $info['id'];
				$this->scanOverview($info['id']);
							
			}
			
			
			// now scan the moons
			foreach ($this->planetInfo as $info){
				
				if($info['type'] == 'planet') continue;
				
				$this->currentPlanet = $info['id'];
				$this->scanOverview($info['id']);
							
			}					
		
		} else {
			// its just a regular scan
			// scan them all
			
			foreach ($this->planetInfo as $info){
	
				$this->currentPlanet = $info['id'];
				$this->scanOverview($info['id']);
							
			}
						
		}
		
	}
	
	
	
	
	function scanOverview($cp = false){
		
		
		//echo "Scanning $cp\n";
		
		
		$cmd = ($cp == false) ? '/game.php?page=overview' : '/game.php?page=overview&cp='.$cp.'&re=0'; 
		// not sure what the re=0 means
		
		$response = $this -> sendGetRequest($cmd);
				
		//Flying Fleet <small class="counter"><span class="green">12</span>/<span class="red">1</span></small></div></div>
		// lets get the usedFleetSlots because this info is here
		// Flying Fleet <small class="counter"><span class="green">12</span>/<span class="red">1</span></small>
		
		$slotsHtml = $this->getStrBetween($response,'Flying Fleet <small class="counter">',"</small>");
						
		if($slotsHtml){
			
			$this->fleetSlotsUsed = $this->getStrBetween($slotsHtml,'<span class="green">','</span');
			$this->fleetSlotsEnemy = $this->getStrBetween($slotsHtml,'<span class="red">','</span');
			
			//echo "Fleet Spots {$this->fleetSlotsUsed} / {$this -> fleetSlotsEnemy}\n";
			
		}
		
		
		
		
		// get the Currently selected planet. There is no info here, but we will look for moon.
		$currentHtml = $this->getStrBetween($response,'<li>','</li>');
				
		// we have a current planet
		// lets see what info we can get
		if($cp){
						
			// kill the alert
			if(@$this->planetInfo[$this->currentPlanet]['type'] == 'moon') {
				
			//	echo $currentHtml;
				
			//	echo "\n\n\n";
				
			//	echo $response;
				
			//	exit;	
				
			}
			
			
			
			
			
			
			// chop it down
			$metal = $this->getStrBetween($response,'$(\'#metal\').html(\'<span class=','/span>');
			// chop it down some more and trim it
			$metal = $this->trimMaterial( $this->getStrBetween($metal,'>','<') );
			
			$crystal = $this->getStrBetween($response,'$(\'#crystal\').html(\'<span class=','/span>') ;
			$crystal = $this->trimMaterial( $this->getStrBetween($crystal,'>','<') );
					
			$deuterium = ($this->getStrBetween($response,'$(\'#deuterium\').html(\'<span','/span>') );
			$deuterium = $this->trimMaterial( $this->getStrBetween($deuterium,'>','<') );
			
			$energy = $this->getStrBetween($response,'$(\'#energy\').html(\'<span class="','/span>');
			$energy = $this->trimMaterial( $this->getStrBetween($response,'>','<') );

			$this->planetInfo[$cp]['metal'] = $metal;
			$this->planetInfo[$cp]['crystal'] = $crystal;
			$this->planetInfo[$cp]['deuterium'] = $deuterium;
			$this->planetInfo[$cp]['energy'] = $energy;
			
			$currentname = $this->getStrBetween($currentHtml,"renameplanet','#s2',true,'slide');\">",'</span>');
			
			$coords =  $this->getStrBetween($currentHtml,'[',']');
			
			$coordparts = explode(':',$coords);
			
			$this->planetInfo[$cp]['coords'] = $coords;
			$this->planetInfo[$cp]['galaxy'] = $coordparts[0];
			$this->planetInfo[$cp]['system'] = $coordparts[1];
			$this->planetInfo[$cp]['position'] = $coordparts[2];
			
			$this->planetInfo[$cp]['name'] = $currentname;
			
			$this->planetInfo[$cp]['p_status'] = $this->getStrBetween($currentHtml,'<div class="status"><p>','<');
				
			
			
					
					
		}
			
		
		// check for moon
		$moonId = $this->getStrBetween($currentHtml,"<div class=\"moon\" onclick=\"loadPage('game.php?page=overview&cp=",'&re=0');
		
		if($moonId){
								
			if($cp) $this -> planetInfo[$moonId]['parent'] = $cp;
			
			$this->planetInfo[$moonId]['type'] = 'moon';			
			$this->planetInfo[$moonId]['id'] = $moonId;			
			
		}
				
		// get the list of planets, it does not include the currently selected planet. 
		$listHtml = $this->getStrsBetween($response,'<li class="arrow pl">','</li>');
				
		// we didn't get anything usefull from the 	
		foreach ($listHtml as $string => $vals){
			
			$id = $this->getStrBetween($string,'game.php?page=overview&cp=','&re=0');
						
			$this->planetInfo[$id]['id'] = $id;
			$this->planetInfo[$id]['type'] = 'planet';
						
			$coords =  $this->getStrBetween($string,'[',']');
			
			//echo "found coords $id $coords\n";
			
			$coordparts = explode(':',$coords);
			
			$this->planetInfo[$id]['coords'] = $coords;
			$this->planetInfo[$id]['galaxy'] = $coordparts[0];
			$this->planetInfo[$id]['system'] = $coordparts[1];
			$this->planetInfo[$id]['position'] = $coordparts[2];
			
			
			
						
			$name = $this->getStrBetween($string,'&re=0\');"><p>',' [');
			
			$this->planetInfo[$id]['name'] = $name;
			
			$pStatus = $this->getStrBetween($string,'<br/>','<');
			
			$this->planetInfo[$id]['p_status'] = $pStatus;	
			
		
						
			//echo "\n".$string."\n";
			
		}
		
		//var_dump($this->planetInfo);
		
	}
	
	
	
	function parseAndUpdateMats($response,$cp=false){
				
		if(!$cp) $cp = $this->currentPlanet;
				
		// chop it down
		$metal = $this->getStrBetween($response,'$(\'#metal\').html(\'<span class=','/span>');
		// chop it down some more and trim it
		$metal = $this->trimMaterial( $this->getStrBetween($metal,'>','<') );
		
		$crystal = $this->getStrBetween($response,'$(\'#crystal\').html(\'<span class=','/span>') ;
		$crystal = $this->trimMaterial( $this->getStrBetween($crystal,'>','<') );
				
		$deuterium = ($this->getStrBetween($response,'$(\'#deuterium\').html(\'<span','/span>') );
		$deuterium = $this->trimMaterial( $this->getStrBetween($deuterium,'>','<') );
		
		$energy = $this->getStrBetween($response,'$(\'#energy\').html(\'<span class="','/span>');
		$energy = $this->trimMaterial( $this->getStrBetween($response,'>','<') );

		$this->planetInfo[$cp]['metal'] = $metal;
		$this->planetInfo[$cp]['crystal'] = $crystal;
		$this->planetInfo[$cp]['deuterium'] = $deuterium;
		$this->planetInfo[$cp]['energy'] = $energy;
		
			
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/**
	 * Echo All Planets Info Display
	 *
	 */
	function displayPlanets(){
		
		echo "\nAll Planets\n\n";
		
		foreach ($this->planetInfo as $id=>$info){
			
			echo $this->getPlanetInfoDisplay($id)."\n";
			
		}		
	}
	
	
	/**
	 * Gets the display info of a planet
	 *
	 * @param int $id planet ID
	 * @param bool $html return html instead of plain text. defaults to false/plaintext
	 * @return string display text
	 */
	function getPlanetInfoDisplay($id,$html = false){
		
		$info = $this->planetInfo[$id];
		
		$return = '';
		$return .= "Name: {$info['name']} [{$info['coords']}] [{$info['galaxy']}:{$info['system']}:{$info['position']}] ({$info['type']}) ($id)\n";
		$return .= "Production Status: {$info['p_status']}\n";
		$return .= "Resources: {$info['metal']}/{$info['crystal']}/{$info['deuterium']}/{$info['energy']}\n";
				
		if($info['ships']){
			
			$return .= "Ships:\n";
			foreach ($info['ships'] as $ship){
				$return .= "   ".$ship['name'].": ".$ship['count']."\n";
			}
			
			
			
		}
		
		$return .= "\n";
		
		return $return;
				
	}
	
	
	function scanAllFleetInfo(){
		
		foreach ($this->planetInfo as $info){
				
			$this->scanFleetInfo($info['id']);
						
		}
		
		
		
		
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	function scanBuildingInfo($cp){
						
		if($this->currentPlanet != $cp){
			
			$this -> changePlanet($cp);
			
		}
		
		$results = $ge->sendGetRequest('/game.php?page=buildings');				
		
		
		// get a container for each building	
		$buildings = $ge->getStrsBetween($results,'<div class="b_top">' ,'<div class="desc">');
		
		// loop through each building
		foreach ($buildings as $html=>$foo){
			
			// name is between the <h6>Crystal Storage (level 11)</h6>
			$namepart = $ge->getStrBetween($html,'<h6>','</h6>');
			
			// break the parts in half - Crystal Storage (level 11)
			$nameparts = explode(' (',$namepart);
			
			//first part is the name 
			$name = $nameparts[0];
			
			// second part is "level 11)" need to trim off level and the )		
			// but only if it exists
			if( isset($nameparts[1]) ){
								
				$level = trim($nameparts[1],'level )');
				
			} else{
				
				$level = 0;
			
			}
			
			//  onclick="loadPage('game.php?page=infos&gid=33',
			// building id is right there				   ^^
			$bid = $ge->getStrBetween($html,'&gid=',"',");			
			
			// <td>Metal:</td><td class="noresources">2.56M</td>
			// the noresources can change so lets get the bigger chunk
			$metalparts = $ge->getStrBetween($html,'<td>Metal:</td><td class="','</td>');
			// if theres no bigger chunk, it doesn't need it
			if(!$metalparts){
				$metalcost = 0;
			} else {
				// we are left with -->noresources">2.56M<--
				// chop it in half at the > and take the second part
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
			
			
			$trimmetalcost = $ge->trimMaterial($metalcost);
			$trimcrystalcost = $ge->trimMaterial($crystalcost);
			$trimdeuteriumcost = $ge->trimMaterial($deuteriumcost);
			
			$this->planetInfo[$cp]['buildings'][$bid]['id'] 		= $bid;
			$this->planetInfo[$cp]['buildings'][$bid]['name'] 		= $name;
			$this->planetInfo[$cp]['buildings'][$bid]['level'] 		= $level;
			$this->planetInfo[$cp]['buildings'][$bid]['metal'] 		= $trimmetalcost;
			$this->planetInfo[$cp]['buildings'][$bid]['crystal'] 	= $trimcrystalcost;
			$this->planetInfo[$cp]['buildings'][$bid]['deuterium'] 	= $trimdeuteriumcost;
			
			echo "Name:$name Level:$level ID:$bid\n";
			echo "Upgrade Cost $metalcost ($trimmetalcost) / $crystalcost ($trimcrystalcost) / $deuteriumcost ($trimdeuteriumcost)\n";
			
			
		}
		
		
		
		
		
						
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	function scanFleetInfo($cp){
						
		if($this->currentPlanet != $cp){
			
			$this -> changePlanet($cp);
			
		}
		
		$response = $this->sendGetRequest('/game.php?page=fleet');
		
		$fleetcountstring = $this->getStrBetween($response,'>Fleets: ',' <'); 
		$fleetcountparts = explode(' / ',$fleetcountstring);
		
		$this->fleetSlotsUsed = $fleetcountparts[0];
		$this->fleetSlotsTotal = $fleetcountparts[1];
				
		$trs = $this->getStrsBetween($response,'<tr','</tr>');
						
		// first to are desc and header
		array_shift($trs);
		array_shift($trs);
		
		// reset the ships array.
		$this->planetInfo[$cp]['ships'] = array();
		
		foreach ($trs as $html => $info){
					
			$shipname = $this->getStrBetween($html,'height="20"><td>','<');
			
			if(!$shipname) continue;
			
			//onclick="maxShip('ship212');
			$shipid = $this->getStrBetween($html,'onclick="maxShip(\'','\');');
			
			$count = $this -> getStrBetween($html,'shortInfo();">','<');
			
			$this->planetInfo[$cp]['ships'][$shipid]['id'] = $shipid;
			$this->planetInfo[$cp]['ships'][$shipid]['name'] = $shipname;
			$this->planetInfo[$cp]['ships'][$shipid]['count'] = $this->trimShipCount($count);
			
		}
						
	}
			
	/**
	 * Changes to another planet.
	 *
	 * @param int $cp Changed to this planet ID
	 * @param bool $force Change even if we think we are already at the current planet
	 * @return bool Always returns true for now
	 */
	function changePlanet($cp,$force = false){
		
		
		if($this->currentPlanet != $cp || $force == true){
				
			$response = $this->sendGetRequest('/game.php?page=overview&cp='.$cp.'&re=0');
			
			$this->currentPlanet=$cp;
			$this->currentGalaxy = $this->planetInfo[$cp]['galaxy'];
							
		} else {
			
			echo "Already on planet $cp -> ".$this->planetInfo[$cp]['name']."\n";
			
		}
		
		return true;
		
	}
	
	
	/**
	 * Switch to a planet with the most probes in the $galaxy.
	 *
	 * @param int $galaxy
	 * @return int the count of the probes
	 */
	function changeSpyPlanet($galaxy){
		
		echo "Looking for planet with most probes\n";
		
		$planet = false;
		$count = 0;
				
		foreach ($this->planetInfo as $info){
							
			if($info['galaxy'] != $galaxy) continue;

			//echo $info['coords']."<- Galaxy:$galaxy\n";
					
			if(key_exists('ship210',$info['ships'])){
				
				
				
				//ship210 probe
				if($info['ships']['ship210']['count'] > $count){
					$planet = $info['id'];
					$count = $info['ships']['ship210']['count'];					
				}
			}
		
		}
		
		
		if($planet) {
			
			echo "Winner:\n";
			
			echo $this->getPlanetInfoDisplay($planet);

			$this->changePlanet($planet);
						
		} 
		
		return $count;
			
	}
	
	
	/**
	 * Switches to the closest planet with enough small cargo's to attack a farm 
	 * planet, or takes the closest planet that doesn't have enough small cargos
	 * 
	 * this needs some work, but it works good enough to make resources.  
	 *
	 * @param int $galaxy target galaxy
	 * @param int_type $system target system
	 * @param int $smallCargoNeeded number of small cargo ships needed
	 * @return array an array of info ([distance],small cargo[count],current [planet] uid)
	 */
	function changeFarmPlanet($galaxy,$system,$smallCargoNeeded = 0){
				
		echo "Looking for planet near $galaxy:$system that has small cargos: $smallCargoNeeded\n";
		
		$planet = false;
		$count = 0;
		$distance = 500;

		$goodPlanets = array(); // these will be the list that do have enough
		$badPlanets = array(); // these will be the list that don't have enough.	
		
		foreach ($this->planetInfo as $info){
							
			if($info['galaxy'] != $galaxy) continue;
			
			//var_dump($info);

			echo 'Looking at:'.$info['coords']."<- Galaxy:$galaxy\n";
					
			if(key_exists('ship202',$info['ships'])){
				
				echo $info['name']." has ".$info['ships']['ship202']['count']."\n";
				
				//ship202 -> Small Cargo
				// good planets have more then then the small cargos needed				
				if($info['ships']['ship202']['count'] > $smallCargoNeeded){					
					
					$goodPlanets[$info['id']]['count'] = $info['ships']['ship202']['count'];
					$goodPlanets[$info['id']]['distance'] = abs($system - $info['system']);
										
				} else if($info['ships']['ship202']['count'] > 0){
				// bad planets have some cargos but not enough.
				
					$badPlanets[$info['id']]['count'] = $info['ships']['ship202']['count'];
					$badPlanets[$info['id']]['distance'] = abs($system - $info['system']);
										
				}
			}
		
		}
		
		if(!count($goodPlanets) && !count($badPlanets)) return false;
		
		
		// if there are good planets. find the closest one and set its distance count and planet (id)
		if(count($goodPlanets)){
						
			foreach ($goodPlanets as $id => $info){
				
				if(!$planet) {
					$planet = $id;
					$distance = $info['distance'];
					$count = $info['count'];
				} else {
					
					if($info['distance'] < $distance){
						
						$planet = $id;
						$distance = $info['distance'];
						$count = $info['count'];
						
					}
					
				}
				
				
			}
			
			
			
		} else {
		// there are no good planets, take the closest bad planet and use it. 
		// this could get improved. we could compare distance with amounts or something	
			
			foreach ($badPlanets as $id => $info){
				
				if(!$planet) {
					$planet = $id;
					$distance = $info['distance'];
					$count = $info['count'];
					
				} else {
					
					if($info['distance'] < $distance){
						
						$planet = $id;
						$distance = $info['distance'];
						$count = $info['count'];						
					}
					
				}
				
				
			}
			
		}
		
		//var_dump($goodPlanets);
		
		//var_dump($badPlanets);
		
		$return =array();
		$return['distance'] = $distance;
		$return['count'] = $count;
		$return['planet'] = $planet;
		
		echo "Winner:\n";
		echo $this->getPlanetInfoDisplay($planet)."\n";
				
		$this->changePlanet($planet);
		$this->scanFleetInfo($this->currentPlanet); // not sure why we scan fleet after we change, but maybe its a good idea
		
		return $return;
					
	}
		
	function probeFarmers($galaxy,$limit = false){

		$probesToSend = 5;
		$sleepTime = 5;
		
		$this->changeSpyPlanet($galaxy);
				
		$this->scanFleetInfo($this->currentPlanet);
		
		$fleetSlotsAvailable = $this->fleetSlotsTotal - $this->fleetSlotsUsed;
		
		$query = "select * from bot_planets where planet_farm='yes' and planet_galaxy = '$galaxy' order by planet_system";
		
		$sql = mysql_query($query);
		
		$count = mysql_num_rows($sql);
		
		echo "Probing $count farmer(s) in galaxy $galaxy\n";
		
		$i = 0;
		
		while ($planet = mysql_fetch_assoc($sql)) {	
			
			if($limit){
				
				if($i >= $limit) break;
				
				$i++;			
				
			}
										
			$system = $planet['planet_system'];
			$position = $planet['planet_position'];	
			
			echo $count--." Probes Inbound -> $galaxy:$system:$position \n";			
			
			$command = "/FleetAjax.php?action=send&thisgalaxy=1&thissystem=1&thisplanet=1&thisplanettype=1&mission=6&galaxy=$galaxy&system=$system&planet=$position&planettype=1&ship210=$probesToSend";	
			
			$response = $this->sendGetRequest($command);
			
			//echo $response;
			//Fleet has been sent<br/> 10 Espionage Probe at 1:336:7...Probes Inbound to 1:336:7
			$probeCount = $this->getStrBetween($response,'Fleet has been sent<br/> ',' Espionage Probe');
			
			if(!$probeCount){
			
				echo "Probe Count False -- Might be a problem.\n";
				
				echo $response."\n";
				
				
				if($response == "Player under noob protection."){
					
					echo "NOOOBIE!!!! Continueing Updating DB\n";
					
					$time = time();
					
					$query = "update bot_planets set planet_farm = 'no', planet_udate = '$time'  where planet_coords = '$galaxy:$system:$position'";
					$rslt = mysql_query($query);
					
					if(!$rslt || mysql_affected_rows() == 0){
						
						echo "bad result or no affected rows on update\n";
						echo $query;
						echo "\n";
						
					}
					
					continue;
					
				} else if($response == 'The planet does not exist'){
					
					echo "Planet Gone!!!! Removing from  DB\n";
					
					$query = "delete from bot_planets where planet_coords = '$galaxy:$system:$position'";
					$rslt = mysql_query($query);
					
					if(!$rslt || mysql_affected_rows() == 0){
						
						echo "bad result or no affected rows on delete\n";
						echo $query;
						echo "\n";
						
					}
					
				} else if( $this->getStrBetween($response,'; ',' | ') == 'No sufficient probes'	){
					
					
					echo "Ran out of probes, Going to sleep for an extra 20 seconds.\n";
					echo $response;
					
					
					$nextReturnShipSeconds = $this->getNextOpenFleetSlotSeconds();
					
					$this->sleep($nextReturnShipSeconds + 5);
					
					//$sleep($sleepTime * 3);
					
					//$sleepTime++;
					
					$response = $this->sendGetRequest($command);
					echo "Tried again: $response\n";
					
				}else if($response == 'You have no more slots available fleet'){
					
					echo "Ran out of fleet slots!";
					
					$secondWait = $this->getNextOpenFleetSlotSeconds();
					
					echo $response."\n";
					
					echo "Going to wait $secondWait + 3 seconds then continue";
					
					$this->sleep($secondWait + 5);
					//sleep($secondWait + 3);
										
					$response = $this->sendGetRequest($command);
					echo "Tried again: $response\n";
					
					
				} else {
					
					
					echo "Probe Count False -- Might be a problem.\n";
				
					echo $response."\n";
				}
				
			
			} else {
				
				
				if($probeCount < $probesToSend){
					
					echo "Running out of probes, Going to sleep for an little bit.\n";
					echo $response;
					
					$nextReturnShipSeconds = $this->getNextOpenFleetSlotSeconds();
					
					$this->sleep($nextReturnShipSeconds + 3);
					
					
					//sleep($sleepTime * 3);
					//$sleepTime++;
				
				} else {
					
					// no reason to sleep as long as we don't run out of probes. 
					//sleep($sleepTime);
					
				}
			}

		}		
		
	}	
	
	function probeInactives($galaxy,$rank = 1100,$firstSystem=1,$lastSystem=500){
		
		$probesToSend = 10;
		$sleepTime = 12;
		
		$this->changeSpyPlanet($galaxy);
				
		$this->scanFleetInfo($this->currentPlanet);
		
		$fleetSlotsAvailable = $this->fleetSlotsTotal - $this->fleetSlotsUsed;
		
		$query = "select * from bot_planets where fk_p_rank < $rank and planet_status = 'inactive' and planet_galaxy = $galaxy and planet_system >= $firstSystem and planet_system <= $lastSystem order by planet_system asc";
		
		$sql = mysql_query($query);
		
		$count = mysql_num_rows($sql);
		
		echo "Probing $count Inactive(s) in galaxy $galaxy\n";
		
		while ($planet = mysql_fetch_assoc($sql)) {			
										
			$system = $planet['planet_system'];
			$position = $planet['planet_position'];	
			
			echo $count--."Probes Inbound -> $galaxy:$system:$position \n";			
			
			$command = "/FleetAjax.php?action=send&thisgalaxy=1&thissystem=1&thisplanet=1&thisplanettype=1&mission=6&galaxy=$galaxy&system=$system&planet=$position&planettype=1&ship210=$probesToSend";	
			
			$response = $this->sendGetRequest($command);
			
			//echo $response;
			//Fleet has been sent<br/> 10 Espionage Probe at 1:336:7...Probes Inbound to 1:336:7
			$probeCount = $this->getStrBetween($response,'Fleet has been sent<br/> ',' Espionage Probe');
			
			if(!$probeCount){
			
				echo "Probe Count False -- Might be a problem.\n";
				
				echo $response."\n";
				
				
				if($response == "Player under noob protection."){
					
					echo "NOOOBIE!!!! Continueing Updating DB\n";
					
					$time = time();
					
					$query = "update bot_planets set planet_farm = 'no', planet_udate = '$time'  where planet_coords = '$galaxy:$system:$position'";
					$rslt = mysql_query($query);
					
					if(!$rslt || mysql_affected_rows() == 0){
						
						echo "bad result or no affected rows on update\n";
						echo $query;
						echo "\n";
						
					}
					
					continue;
					
				} else if($response == 'The planet does not exist'){
					
					echo "Planet Gone!!!! Removing from  DB\n";
					
					$query = "delete from bot_planets where planet_coords = '$galaxy:$system:$position'";
					$rslt = mysql_query($query);
					
					if(!$rslt || mysql_affected_rows() == 0){
						
						echo "bad result or no affected rows on delete\n";
						echo $query;
						echo "\n";
						
					}
					
				} else if( $this->getStrBetween($response,'; ',' | ') == 'No sufficient probes'	){
										
					$secondWait = $this->getNextOpenFleetSlotSeconds();
					
					//echo $response."\n";
					
					echo "Ran out of probes, going to wait till next fleet comes in and try again\n";
					
					$this->sleep($secondWait + 3);
					
					$response = $this->sendGetRequest($command);
					echo "Tried again: $response\n";
					
					
					
				}else if($response == 'You have no more slots available fleet'){
					
					echo "Ran out of fleet slots!";
					
					$secondWait = $this->getNextOpenFleetSlotSeconds();
					
					echo $response."\n";
					
					echo "Going to wait until next fleet comes in and try again\n";
					
					$this->sleep($secondWait + 3);
					
					$response = $this->sendGetRequest($command);
					echo "Tried again: $response\n";
					
					
				} else {
					
					
					echo "Probe Count False -- Might be a problem.\n";
				
					echo $response."\n";
				}
				
			
			} else {
				
				
				if($probeCount < $probesToSend){
					
					echo "Running out of probes\n";
					echo $response;
					
					$secondWait = $this->getNextOpenFleetSlotSeconds();
					
					echo $response."\n";
					
					echo "Going to wait until next fleet comes in and continue\n";
					
					$this->sleep($secondWait + 3);
					
					$sleepTime++;
				
				} else {
					
					
					
					//sleep($sleepTime);
					
				}
			}

		}		
		
	}
		
	// defaults to all;
	/**
	 * Deletes the category of messages;
	 * 1 = spyreports
	 * 100 = all.
	 *
	 * @param int $cat
	 */
	function deleteMessageCat($cat=100){ 
			
		$this->sendGetRequest("/game.php?page=messages&mode=show&action=delete&messcat=$cat");
		
	}
		
	// we need a sleep command that wont timeout the ssh session
	function sleep($seconds =0,$minutes = 0,$hours=0,$days = 0,$scanGalaxy = true){
			
		$total = $seconds + ($minutes * 60) + ($hours * 60 * 60) + ($days *60*60*24);
		
		
		// if total is greater then 10 minutes, scan a section from the scan store
		if($total > 60*10 && $scanGalaxy){
			
			echo "We are waiting for at least 10 mintues, lets go ahead and scan from the scan store\n";
			
			$starttime = time();
			$this->scanFromScanStore();
					
			$endtime = time();
						
			$total = $total - ($endtime - $starttime);
			
			if($total < 0) $total = 0;
			
			echo "We have $total seconds left after scanning!";
						
		}
		
		// stuff for display	
		$seconds = $total%60;
		$minutes = intval($total/60);
		$hours = intval($total/(60*60));
		$days = intval($total/(60*60*24));
				
		echo "Sleeping $seconds second(s) $minutes minute(s) $hours hour(s) $days day(s)\n";
				
		
		// get the number of total minutes
		$inc = floor($total / 60);
		
		// get the remainder seconds
		$leftover = $total % 60;
		
		// add a dot every 60 seconds so ssh doesn't time out and to add some feedback to user
		for ($i = 0; $i<$inc;$i++){

			// echo a * every 10 minutes		
			if($i%10==0 && $i != 0){
				echo "*";
			} else {
				echo ".";
			}
			sleep(60);
			
		}	
				
		sleep($leftover);
		
		echo "\n";
		
		
	}
	
	
	
	
	
	
	
	function scanFromScanStore(){
						
		$systems = unserialize( file_get_contents('/usr/local/www/apache22/data/framework/plugins/bot/processes/cron_galaxy_scan.store') ); 
		$scanArray = array_pop($systems);
		array_unshift($systems,$scanArray);
		$datastring = serialize($systems);
		file_put_contents('/usr/local/www/apache22/data/framework/plugins/bot/processes/cron_galaxy_scan.store',$datastring);
		
		$this->scanGalaxy($scanArray[0],$scanArray[1],$scanArray[2]);
				
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	function scanGalaxy($galaxy,$startsystem = 1,$end = 500){
				
		$system = $startsystem;
				
		while($system <= $end){
		
			$command = "/game.php?page=galaxy&mode=1&galaxy=$galaxy&system=$system";
			$response = $this->sendGetRequest($command);
			
			$items = $this->getStrsBetween($response,'<li','</li>');
			
			$i = 0;
			
			echo "Scanned Galaxy $galaxy System $system\n\n";
			
			if(count($items) < 14) {
								
				echo $response;
				
			}
			
			foreach($items as $html => $info){
				
				$i++; 
				
				// there should be more then 75 characters if its occupied
				if(strlen($html) > 80){
								
					$rank =  $this->getStrBetween($html,' ( ranked ',')');
					if(!$rank) {
					
						echo "Bad Planet at $galaxy:$system:$i No Rank!!!!!!\n";
										
						continue;
					}
					
					
					$inactive = $this->getStrBetween($html,'<font class="inactive">','</font>');
				
					$lineparts = explode('<br/>',$html);
					$planetName = $lineparts[1];
										
					$id = $this->getStrBetween($html,'game.php?page=galaxy&action=profile&id=',"'");
					$position = $this->getStrBetween($html,'<div class="gn">','</div>');
					$player =  $this->getStrBetween($html,'<div class="g_right" style="width:200px;"><p>',' ( ');
										
					if(strlen($lineparts[2]) > 21){
						
						$debrisInfo = $this->getStrBetween($lineparts[2],'Debris(M/C): ','</font>');
						
						$debrisParts = explode(' / ',$debrisInfo);
						$debrisMetal = $debrisParts[0];
						$debrisCrystal = $debrisParts[1];
						
						
					} else {
						
						$debrisCrystal = 0;
						$debrisMetal = 0;
						
					}
					
						
				 	if($inactive == 'I'){
						$inactiveDisplay = 'inactive';
						
					} else {
						
						$inactiveDisplay = '';
					}
							
					echo "Position $i($galaxy:$system:$position)\n";
					echo "Player Name(Rank): $player ($rank) $inactiveDisplay\n";
					echo "Planet Name (ID): $planetName ($id)\n";
					echo "Debris(M/C) $debrisMetal / $debrisCrystal\n";
					
					$player = mysql_real_escape_string($player);
					$planetName = mysql_real_escape_string($planetName);
					
					$time = time();
										
					$farm = (!$inactiveDisplay) ? $farm = ",planet_farm='no'" : $farm = '';
															
					$query = "
		INSERT INTO bot_players (p_name,p_status,p_rank,p_cdate,p_udate) 
		VALUES ('$player','$inactiveDisplay','$rank','$time','$time')
		ON DUPLICATE KEY UPDATE
		p_status='$inactiveDisplay', p_rank='$rank',p_udate = '$time';
		
		";
					$rslt = mysql_query($query);
					if(!$rslt) echo $query;
										
					$query = "
		INSERT INTO bot_planets	(planet_id,planet_name,planet_galaxy,planet_system,planet_position,planet_coords,planet_status,fk_p_name,fk_p_rank,planet_cdate,planet_udate)
		VALUES ('$id','$planetName','$galaxy','$system','$position','$galaxy:$system:$position','$inactiveDisplay','$player','$rank','$time','$time')
		ON DUPLICATE KEY UPDATE
		planet_id='$id',planet_name='$planetName',planet_galaxy='$galaxy',planet_system='$system',planet_position='$position',planet_coords='$galaxy:$system:$position',planet_status='$inactiveDisplay'$farm,fk_p_name='$player',fk_p_rank='$rank',planet_udate='$time';
		
		";			
										
					$rslt = mysql_query($query);				
					if(!$rslt) echo $query;
					
				} else {
					
					
					//echo "Updating DB\n";
					
					//echo "Planet Gone!!!! Removing from  DB\n";
					$query = "
					delete from bot_planets where planet_coords = '$galaxy:$system:$i'
					";
					echo "Position $i empty: \n";
					//mysql_query($query);
					$rslt = mysql_query($query);
					
					if(!$rslt){
						
						echo $query;
						
					}
									
				}
		
			}
			$system++;
			
		}
	
	
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	function sendGetRequest($command,$returnHeaders = false,$postvalues = false){
		
		
		
		
		echo "Sending Request: $command\n";
		
		$host = $this->host;
		$sessID = $this->sessID;
		$uuid = $this->uuid;
		
		
		
		
		if(!$postvalues){
			
			$type = 'GET';
			$postdata 		= '';
			$extraheaders 	= '';
			
		} else {
			
			$type = 'POST';
			
			$postdata = '';
			
			foreach ($postvalues as $key => $val){
				
				$postdata .= "$key=$val&";				
				
			}

			$postdata = trim($postdata,'&');	
			
			$length = strlen($postdata);	
			
			$extraheaders = "Content-Length: $length\r\n";
			$extraheaders .= "Origin: file://\r\n";
		
		}		
			
		$msg = '';
		//$msg .= "POST /$path HTTP/1.1");
		$msg .= "$type {$command}&uuid={$uuid} HTTP/1.0\r\n";
		$msg .= "Host: $host\r\n";
		$msg .= "Connection: Keep-Alive\r\n";
		
		$msg .= $extraheaders;
		
		$msg .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$msg .= "Accept: text/html, */*\r\n";
		$msg .= "x-att-deviceid: SAMSUNG-SGH-I747/I747UCDLK3\r\n";
		$msg .= "x-wap-profile: http://wap.samsungmobile.com/uaprof/SGH-I747.xml\r\n";
		$msg .= "User-Agent: Mozilla/5.0 (Linux; U; Android 4.1.1; en-us; SAMSUNG-SGH-I747 Build/JRO03L) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30\r\n";
		//$msg .= "Accept-Encoding: gzip,deflate\r\n";
		$msg .= "Accept-Language: en-US\r\n";
		$msg .= "Accept-Charset: utf-8, iso-8859-1, utf-16, *;q=0.7\r\n";
		$msg .= "Cookie: PHPSESSID=$sessID; valid=1\r\n";
		$msg .= "\r\n";
		$msg .= $postdata;
		
		//$msg .= "\r\n";
		
		//exit;
		
		
		
		$fp = pfsockopen ($host, 80, $errno, $errstr, 30);
		
		if (!$fp) {
			
			echo "ERROR_STRING=$errstr ERR_NO($errno)<br>\n";
			return false;
			
			
		} else {
			
			fwrite ($fp, $msg);
			
			$response = '';
			
			while (!feof($fp)){
				
				$response .= fgets ($fp,1024);
			}
			
			fclose ($fp);
			
		}
		
		
		
		if(!$returnHeaders){
			
			$parts = explode("\r\n\r\n", $response, 2);
			$response = $parts[1];
			
		}
		
		
		if($this->debug == true){
						
			$debug = "*** sendGetRequest($command,$returnHeaders = false,$postvalues = false) ***\n\n";
			$debug .= "** SENT MESSAGE **\n".$msg."\n** END SENT MESSAGE **\n\n";
			$debug .= "** RESPONSE **\n".$response."\n**END RESPONSE**";
						
			file_put_contents($this->debugfile,$debug,FILE_APPEND);
						
		}
			
		return $response; 
		
		
	}

	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

	
	/**
	 * Utility Function to get a string between 2 other strings. 
	 *
	 * @param string $s string to be searched
	 * @param string $s1 first tag
	 * @param string tag $s2 closing tag, false for dupe of opening tag
	 * @param int $offset
	 * @return int position of string or false if not found
	 */
	function getStrBetween($s,$s1,$s2=false,$offset=0) {
	    /*====================================================================
	    Function to scan a string for items encapsulated within a pair of tags
	
	    getStrsBetween(string, tag1, <tag2>, <offset>
	
	    If no second tag is specified, then match between identical tags
	
	    Returns an array indexed with the encapsulated text, which is in turn
	    a sub-array, containing the position of each item.
	
	    Notes:
	    strpos($needle,$haystack,$offset)
	    substr($string,$start,$length)
	
	    ====================================================================*/
	
	    if( $s2 === false ) { $s2 = $s1; }
	    $result = array();
	    $L1 = strlen($s1);
	    $L2 = strlen($s2);
	
	    if( $L1==0 || $L2==0 ) {
	        return false;
	    }
	
	    do {
	        $pos1 = strpos($s,$s1,$offset);
	
	        if( $pos1 !== false ) {
	            $pos1 += $L1;
	
	            $pos2 = strpos($s,$s2,$pos1);
	
	            if( $pos2 !== false ) {
	                $key_len = $pos2 - $pos1;
	
	                return substr($s,$pos1,$key_len);
	
	               
	            } else {
	                $pos1 = false;
	            }
	        }
	    } while($pos1 !== false );
	
	    return false;
	}
	
	
	 
	/**
	 * Utility Function to get multiple strings between 2 other strings. Kind of a kludge, adopted from other purposes. 
	 *
	 * @param string $s string to be searched
	 * @param string $s1 first tag
	 * @param string tag $s2 closing tag, false for dupe of opening tag
	 * @param int $offset
	 * @return array array with keys being string found and values being positions or false if not found
	 */
	function getStrsBetween($s,$s1,$s2=false,$offset=0) {
	    /*====================================================================
	    Function to scan a string for items encapsulated within a pair of tags
	
	    getStrsBetween(string, tag1, <tag2>, <offset>
	
	    If no second tag is specified, then match between identical tags
	
	    Returns an array indexed with the encapsulated text, which is in turn
	    a sub-array, containing the position of each item.
	
	    Notes:
	    strpos($needle,$haystack,$offset)
	    substr($string,$start,$length)
	
	    ====================================================================*/
	
	    if( $s2 === false ) { $s2 = $s1; }
	    $result = array();
	    $L1 = strlen($s1);
	    $L2 = strlen($s2);
	
	    if( $L1==0 || $L2==0 ) {
	        return false;
	    }
	
	    do {
	        $pos1 = strpos($s,$s1,$offset);
	
	        if( $pos1 !== false ) {
	            $pos1 += $L1;
	
	            $pos2 = strpos($s,$s2,$pos1);
	
	            if( $pos2 !== false ) {
	                $key_len = $pos2 - $pos1;
	
	                $this_key = substr($s,$pos1,$key_len);
	
	                if( !array_key_exists($this_key,$result) ) {
	                    $result[$this_key] = array();
	                }
	
	                $result[$this_key][] = $pos1;
	
	                $offset = $pos2 + $L2;
	            } else {
	                $pos1 = false;
	            }
	        }
	    } while($pos1 !== false );
	
	    return $result;
	}
		
	function trimMaterial($val){
		
		if( strlen(trim($val,'M')) < strlen($val)  ){
			//its in M
			
			$val = trim($val,'m');
			$val = $val * 1000;		
			
		} else if( strlen(trim($val,'K')) < strlen($val)  ){
			// its a K
			
			$val = trim($val,'K');
					
		} else {
			
			$val = round($val /1000,2);
			
		}
		
		return $val;	
	}
	
	function trimShipCount($val){
		
		 if( strlen(trim($val,'K')) < strlen($val) ){
		 	
		 	$return = $val * 1000;
		 	
		 } else {
		 	
		 	$return = $val; 
		 	
		 }
		
		return $return;
	}

	
	function scanSpyReport($updateDB = true,$showList = false,$logfile = false ){
		
		$attackList = array();
		$totalArray = array();
		
		$attackcount = 0;
				
		// message catagories list
		// we do this so it looks like we know what we are doing
		$command = "/game.php?page=messages";
		$response = $this->sendGetRequest($command);
				
		// cat 0 is spy reports
		$command = "/game.php?page=messages&mode=show&messcat=0";
		$response = $this->sendGetRequest($command);
		
		$items = $this-> getStrsBetween($response,'<li','</li>');
		foreach($items as $html => $info){
					
			$messid = $this-> getStrBetween($html,'messid=',"','");
			$time = $this-> getStrBetween($html,'<small>','</small>');
			$planetName = $this-> getStrBetween($html,'Report espionage ',' [');
	
			echo "Message Brief: $time $planetName $messid\n";
		
			$command = "/game.php?page=messages&mode=read&messid=$messid";
			$messageResponse = $this->sendGetRequest($command);
					
			$planetName = $this-> getStrBetween($messageResponse,'Report espionage ',' [');
			$coordParts = $this-> getStrBetween($messageResponse,'[',']');
			$coords = explode(':',$coordParts);
					
			echo "Full Message: \n";
			echo "Planet: $planetName {$coords[0]}:{$coords[1]}:{$coords[2]}\n";
							
			$metal = $this-> getStrBetween($messageResponse,'Metal</td><td align=right>','</td>');
			$crystal = $this-> getStrBetween($messageResponse,'Crystal</td></td><td align=right>','</td>');
			$deut = $this-> getStrBetween($messageResponse,'Deuterium</td><td align=right>','</td>');
			
			echo "$metal / $crystal / $deut (m/c/d)\n";
			
			$metal = $this->trimMaterial($metal);
			$crystal = $this->trimMaterial($crystal);
			$deut = $this->trimMaterial($deut);
			$totalMats = $metal+$crystal+$deut;
			
			echo $totalMats."Total mats! <- ";
			
			$time = time();
			
			$query = "update bot_planets set planet_metal='$metal', planet_crystal = '$crystal',planet_deuterium='$deut', planet_udate = '$time' where planet_coords = '{$coords[0]}:{$coords[1]}:{$coords[2]}'";
			
			//echo "$query\n";
	
			// WE ALWAYS WANT TO UPDATE THIS ONE
			$rslt = mysql_query($query);
			if(!$rslt) echo $query;
			
			$tables = $this-> getStrsBetween($messageResponse,'<table>','</table>');
			
			if(count($tables) == 1){
				
				echo "Only One Table - Setting to NO farm if UPDATE DB is TRUE\n";
				
				if($updateDB){
					
					$query = "update bot_planets set planet_farm = 'no', planet_udate='$time' where planet_coords = '$coordParts'";
					
					$rslt = mysql_query($query);
					
					if(!$rslt) echo $query;
					
				}
				
				$totalArray[$totalMats] = $coordParts;
				
				continue;
								
			}
		
			// remove the first table, it was the materails table
			array_shift($tables);	
			
			$dataArray = array();
			foreach ($tables as $table=>$pos){

				$rows = $this-> getStrsBetween($table,'<tr>','</tr>');
				
				$rowcount = 0;
				$tableLabel = '';
				foreach ($rows as $row => $pos){
							
					if($rowcount == 0){
						
						//echo $row."\n";
						$tableLabel = $this-> getStrBetween($row,'<td colspan="2">','</td>');
										 
					} else {
						
						
						$column = $this-> getStrBetween($row,'<td align=left>','</td>');
						$value = $this-> getStrBetween($row,'<td align=right>','</td>');
										
						$dataArray[$tableLabel][$column] = $value;
						
					}
							
					$rowcount++;
				}
						
			}
	
			if(!key_exists('Fleet',$dataArray)){
						
				echo "No Fleet!\n";
				$dataArray['Fleet'] = false;
				$fleet = 0;
				
			} else {
				
				$count = count($dataArray['Fleet']);
				foreach ($dataArray['Fleet'] as $type => $valcount){
					
					// put list of things we are not afraid of.
					if($type == 'Solar Satellite' ) {
						
						$count--;
						
					}
					
					
				}
				
				if($count == 0){
				
					$fleet = 0;
					echo "No fleet only solar!\n";
				
				} else {
					var_dump($dataArray['Fleet']);
					$fleet = true;
								
					
				}
						
			}
	
			if(!key_exists('Defense',$dataArray)){
				
				$dataArray['Defense'] = false;
				$defense = 0;
				echo "No Defense!\n";
				
			} else {
				
				
				$count = count($dataArray['Defense']);
				
				echo "defense Count = $count\n";
				
				foreach ($dataArray['Defense'] as $building => $valcount){
					// needs some work here.
					if($building == 'Anti-Ballistic Missiles' || $building == 'Interplanetary Missiles'  || $building == 'Small Shield Dome' || $building == 'Large Shield Dome') {
						
						
						echo "found $building\n";
						
						$count--;
						
					} else if($building == 'Rocket Launcher' && $valcount < 3){
												
						echo "found $building - but only $valcount\n";
						$count--;						
						
					}
								
				}
						
				if($count == 0){
					echo "No Defense! Only Missiles and/or Shields! $count\n";
					var_dump($dataArray['Defense']);
					$defense = 0;
					
				} else {
					var_dump($dataArray['Defense']);
					$defense = true;
							
				}
				
				//var_dump($dataArray['Defense']);
						
			}
	
			//var_dump($dataArray);
			
			echo "\n";
			$time = time();
			
			
			
			
			
			
			$fleet_count = 0;
			
			if($dataArray['Fleet']){
									
				foreach ($dataArray['Fleet'] as $key => $val){
					
					$fleet_count += $val;
										
				}				
			}
				
			$fleetstring = serialize($dataArray['Fleet']);
				
			
			
			$defense_count = 0;
			
			if($dataArray['Defense']){
				
				foreach ($dataArray['Defense'] as $key => $val){
					
					$defense_count += $val;
					
				}
				
				
				
			} 
			$defensestring = serialize($dataArray['Defense']);
		
				
			
			
			// update fleet and defense info into db		
			$query = "update bot_planets set planet_defense_count='$defense_count', planet_fleet_count = 'planet_count', planet_defense_array = '$defensestring', planet_fleet_array = '$fleetstring' where planet_coords = '{$coords[0]}:{$coords[1]}:{$coords[2]}'";
			
			//echo "$query\n";
			// WE ALWAYS WANT TO UPDATE THIS ONE
			$rslt = mysql_query($query);
			if(!$rslt) echo $query;
			
			
			
			
			
			
			
			
			
			
			
			if($logfile){
				
				$logdata = "";
				
				$totalMats = round($totalMats);
				
				$logdata .= "$planetName {$coords[0]}:{$coords[1]}:{$coords[2]} $metal/$crystal/$deut $totalMats\n";
				
				if($dataArray['Fleet']){
					
					$logdata .= "Fleet\n";
					
					foreach ($dataArray['Fleet'] as $key => $val){
						
						$logdata .= "	$val $key\n";
						
						
					}
					
					
					
				}
				
				if($dataArray['Defense']){
					
					$logdata .= "Defense\n";
					
					foreach ($dataArray['Defense'] as $key => $val){
						
						$logdata .= "	$val $key\n";						
						
					}					
				}
							
				file_put_contents('log/probed_info.log',$logdata, FILE_APPEND);
							
			}
						
			if(!$defense &&  !$fleet){
						
				if($updateDB){
				
					echo "setting to farm status!\n\n";
					$query = "update bot_planets set planet_farm = 'yes', planet_udate = '$time'  where planet_coords = '$coordParts'";
					$rslt = mysql_query($query);
					
					if(!$rslt) echo $query;
					
				}
				
				
												
				$attackList[$attackcount]['total'] = round($totalMats);
				$attackList[$attackcount]['metal'] = $metal;
				$attackList[$attackcount]['crystal'] = $crystal;
				$attackList[$attackcount]['deuterium'] = $deut;				
				$attackList[$attackcount]['galaxy'] = $coords[0];
				$attackList[$attackcount]['system'] = $coords[1];
				$attackList[$attackcount]['position'] = $coords[2];
				$attackList[$attackcount]['planet'] = $coords[2];
				$attackList[$attackcount]['coords'] = "{$coords[0]}:{$coords[1]}:{$coords[2]}"; 
				$attackList[$attackcount]['defense'] = $dataArray['Defense']; 
				$attackList[$attackcount]['fleet'] = $dataArray['Fleet']; 
				$attackcount++;
				
				
				while(key_exists((string) $totalMats,$totalArray) ){
					
					$totalMats = $totalMats + 1;
					
				}
				
				$totalArray[$totalMats] = $coordParts."<-- farm";
							
			} else {
				
				
			
				
				if($updateDB){
					
					$query = "update bot_planets set planet_farm = 'no', planet_udate='$time' where planet_coords = '$coordParts'";
					
					$rslt = mysql_query($query);
					
					if(!$rslt) echo $query."\n";
					
										
				}
								
				while(key_exists((string) $totalMats,$totalArray) ){
					
					$totalMats = $totalMats + 1;
					
				}
				
				$totalArray[$totalMats] = $coordParts;
								
			}
					
		}
		
		
		
		if($showList){		
			
			ksort($totalArray);
			
			
			foreach ($totalArray as $total => $coords){
								
				echo "$total : $coords\n";
				
			}
		
		}
		
		return $attackList;
			
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	function scanFarmReport($showList = false){
		
		$attackList = array();
		$totalArray = array();
		
		$attackcount = 0;
				
		// message catagories list
		// we do this so it looks like we know what we are doing
		$command = "/game.php?page=messages";
		$response = $this->sendGetRequest($command);
				
		// cat 0 is spy reports
		$command = "/game.php?page=messages&mode=show&messcat=0";
		$response = $this->sendGetRequest($command);
		
		$items = $this-> getStrsBetween($response,'<li','</li>');
		foreach($items as $html => $info){
					
			$messid = $this-> getStrBetween($html,'messid=',"','");
			$time = $this-> getStrBetween($html,'<small>','</small>');
			$planetName = $this-> getStrBetween($html,'Report espionage ',' [');
	
			echo "Message Brief: $time $planetName $messid\n";
		
			$command = "/game.php?page=messages&mode=read&messid=$messid";
			$messageResponse = $this->sendGetRequest($command);
					
			$planetName = $this-> getStrBetween($messageResponse,'Report espionage ',' [');
			$coordParts = $this-> getStrBetween($messageResponse,'[',']');
			$coords = explode(':',$coordParts);
					
			echo "Full Message: \n";
			echo "Planet: $planetName {$coords[0]}:{$coords[1]}:{$coords[2]}\n";
							
			$metal = $this-> getStrBetween($messageResponse,'Metal</td><td align=right>','</td>');
			$crystal = $this-> getStrBetween($messageResponse,'Crystal</td></td><td align=right>','</td>');
			$deut = $this-> getStrBetween($messageResponse,'Deuterium</td><td align=right>','</td>');
			
			echo "$metal / $crystal / $deut (m/c/d)\n";
			
			$metal = $this->trimMaterial($metal);
			$crystal = $this->trimMaterial($crystal);
			$deut = $this->trimMaterial($deut);
			$totalMats = $metal+$crystal+$deut;
			
			
			echo $totalMats."Total mats! <- ";
			
			$time = time();
			
			$query = "update bot_planets set planet_metal='$metal', planet_crystal = '$crystal',planet_deuterium='$deut', planet_udate = '$time' where planet_coords = '{$coords[0]}:{$coords[1]}:{$coords[2]}'";
	
			// WE ALWAYS WANT TO UPDATE THIS ONE
			$rslt = mysql_query($query);
			if(!$rslt) echo $query;
		
												
			$attackList[$attackcount]['total'] = round($totalMats);
			$attackList[$attackcount]['metal'] = $metal;
			$attackList[$attackcount]['crystal'] = $crystal;
			$attackList[$attackcount]['deuterium'] = $deut;				
			$attackList[$attackcount]['galaxy'] = $coords[0];
			$attackList[$attackcount]['system'] = $coords[1];
			$attackList[$attackcount]['position'] = $coords[2];
			$attackList[$attackcount]['planet'] = $coords[2];
			$attackList[$attackcount]['coords'] = "{$coords[0]}:{$coords[1]}:{$coords[2]}"; 
			$attackcount++;

		
		
		
		}
			
		return $attackList;
			
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

	
	
	
	
	
	
	
	
	
	/**
	 * Utility that gets a list of all systems with empty $slot
	 *
	 * @param int $slot
	 * @return array of empty coords.
	 */
	function getEmptyPlanetSlot($slot){
			
		$galaxy = 1;
		
		while($galaxy < 5){
			
			$system = 1;
			
			while($system < 500){
					
				$sql = mysql_query("select * from bot_planets where planet_galaxy=$galaxy and planet_system = $system and planet_position = $slot");
				
				if(!mysql_numrows($sql)){
					
					$return[] = "$galaxy:$system:$slot";
					
				}
						
				$system++;
			}
			
			
			$galaxy++;
			
		}		
		
		return $return;
		
	}
	
	
	
	
	
	
	
	
	function sendFarmMission($galaxy,$system,$position,$smallCargoCount,$planettype = 1){
		
		// planettype 1 planet 2 debris, 3 moon
		
		// open the fist page prefilled out with these get stuff
		$result = $this->sendGetRequest("/game.php?page=fleet&galaxy=$galaxy&system=$system&planet=$position&planettype=1&target_mission=1");

		$maintable  = $this->getStrBetween($result,'<table>','</table>');
		
		$mainkeyparts = $this->getStrsBetween($maintable,'name="','"');
		// start with the list of ships available;
		$postarray = array_keys($mainkeyparts);
		
		// we know that ship202 is our small cargo sooo lets send some:
		$postarray['ship202'] = $smallCargoCount;
		
		
		$hiddens = $this->getStrsBetween($result,'<input type="hidden"','/>');
		// add all the hidden values too
		foreach ($hiddens as $input => $blah){
			
			$name = $this->getStrBetween($input,'name="','"');
			$value = $this->getStrBetween($input,'value="','"');
			
			$postarray[$name] = $value;
			
		}
		
		//var_dump($shiplist);
		//var_dump($postarray);
		
		$result = $this->sendGetRequest('/game.php?page=fleet1',false,$postarray);
		
		$postarray = array();
		
		$hiddens = $this->getStrsBetween($result,'<input type="hidden"','/>');
		// add all the hidden values too
		foreach ($hiddens as $input => $blah){
			
			$name = $this->getStrBetween($input,'name="','"');
			$value = $this->getStrBetween($input,'value="','"');
			
			$postarray[$name] = $value;
			
		}
		
		// we need to add the non hiddens
		
		$postarray['galaxy'] = $galaxy;
		$postarray['system'] = $system;
		$postarray['planet'] = $position;
		$postarray['planettype'] = $planettype;
		$postarray['acs_target_mr'] = urlencode('0:0:0');
		$postarray['speed'] = 10;
		 
		$result = $this->sendGetRequest('/game.php?page=fleet2',false,$postarray);


		$postarray = array();
		
		$hiddens = $this->getStrsBetween($result,'<input type="hidden"','/>');
		// add all the hidden values too
		foreach ($hiddens as $input => $blah){
			
			$name = $this->getStrBetween($input,'name="','"');
			$value = $this->getStrBetween($input,'value="','"');
			
			$postarray[$name] = $value;
			
		}
		
		$postarray['mission']=1;
		$postarray['resource1']='';
		$postarray['resource2']='';
		$postarray['resource3']='';
		$postarray['holdingtime']=0;
		
		$result = $this->sendGetRequest('/game.php?page=fleet3',false,$postarray);
		
	}
	
	
	
	
	
	
	
	/**
	 * Gets the seconds of the soonest returning fleet, can also get the latest
	 *
	 * @param bool $getHighest defaults to false, set to true to get latest instead of soonest
	 * @return int seconds
	 */
	function getNextOpenFleetSlotSeconds($getHighest = false){
		
		
		$this->debug = true;
		$response = $this->sendGetRequest('/game.php?page=fleet&mode=flying');
		$this->debug = false;
		
		
		$times = $this->getStrsBetween($response,'<small class="counter">','</small');
		
		$lowestTime = false;
		
		foreach ($times as $timestring => $blag){
			
			echo "$timestring ";
			$timeparts = explode(' ', $timestring);
			
			$partcount = count($timeparts);
			
			if($partcount == 3){
				
				$hours = trim($timeparts[0],'h');
				$minutes = trim($timeparts[1],'m');
				$seconds = trim($timeparts[2],'s');
					
			} elseif($partcount == 2){
				$hours = 0;
				$minutes = trim($timeparts[0],'m');
				$seconds = trim($timeparts[1],'s');
						
			} else if($partcount == 1){
				
				$hours = 0;
				$minutes = 0;
				$seconds = trim($timeparts[0],'s');
						
			} else {
				// something went wrong here.
				return false; 
				
			}
				
			$totalseconds = ($hours * 60 * 60) + ($minutes * 60) + $seconds;
			
			echo "Total Seconds: $totalseconds\n";
			
			if($getHighest == true) {
							
				if($lowestTime === false) $lowestTime = $totalseconds;
							
				if($lowestTime < $totalseconds) $lowestTime = $totalseconds;
				
				
			} else {
				
				if($lowestTime === false) $lowestTime = $totalseconds;
							
				if($lowestTime > $totalseconds) $lowestTime = $totalseconds;
			
			}
				
		}
				
		if($lowestTime === false) $lowestTime = 0;
		
		return $lowestTime;
		
		
		
		
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}

