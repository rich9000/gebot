<?
error_reporting(E_ALL);
ini_set('display_errors', '1');

ob_start();

include('../config/bot-config.php');

//include_once('../libs/ge-functions.php');
include_once($config['path'].'libs/class.ge.php');
include_once($config['path'].'libs/db-functions.php');
include_once($config['path'].'config/db.php');

$USER = 'rich9000';
$PASSWORD = 'fish1234';

$host = 'ge2.seazonegames.com';
$sessID = 'o0mn5u9uv0ala1m66tpi0q0sj1';
$uuid = '63339-33942513251521e5573b4d5.49296834';

$ge = new GalacticEmpire($USER,$PASSWORD,$host);
$ge -> sessID = $sessID;
$ge -> uuid = $uuid;

$systems = array();

$randSleep = rand(23,500);
sleep($randSleep);

/*

// galaxy, start system, end system
$systems[] = array(1,1,50);
$systems[] = array(1,51,100);
$systems[] = array(1,101,150);
$systems[] = array(1,151,200);
$systems[] = array(1,201,250);
$systems[] = array(1,251,300);
$systems[] = array(1,301,350);
$systems[] = array(1,351,400);
$systems[] = array(1,401,450);
$systems[] = array(1,451,500);

$systems[] = array(2,1,50);
$systems[] = array(2,51,100);
$systems[] = array(2,101,150);
$systems[] = array(2,151,200);
$systems[] = array(2,201,250);
$systems[] = array(2,251,300);
$systems[] = array(2,301,350);
$systems[] = array(2,351,400);
$systems[] = array(2,401,450);
$systems[] = array(2,451,500);

$systems[] = array(3,1,50);
$systems[] = array(3,51,100);
$systems[] = array(3,101,150);
$systems[] = array(3,151,200);
$systems[] = array(3,201,250);
$systems[] = array(3,251,300);
$systems[] = array(3,301,350);
$systems[] = array(3,351,400);
$systems[] = array(3,401,450);
$systems[] = array(3,451,500);

$systems[] = array(4,1,50);
$systems[] = array(4,51,100);
$systems[] = array(4,101,150);
$systems[] = array(4,151,200);
$systems[] = array(4,201,250);
$systems[] = array(4,251,300);
$systems[] = array(4,301,350);
$systems[] = array(4,351,400);
$systems[] = array(4,401,450);
$systems[] = array(4,451,500);

shuffle($systems);

$datastring = serialize($systems);
file_put_contents("cron_galaxy_scan.store",$datastring);

exit;
*/

$systems = unserialize( file_get_contents($config['path'].'data//cron_galaxy_scan.store') ); 

// pop one off, scan it, then put it at the beginning
$scanArray = array_pop($systems);

$ge->scanGalaxy($scanArray[0],$scanArray[1],$scanArray[2]);

array_unshift($systems,$scanArray);

// do it again
$scanArray = array_pop($systems);
$ge->scanGalaxy($scanArray[0],$scanArray[1],$scanArray[2]);
array_unshift($systems,$scanArray);
$datastring = serialize($systems);
file_put_contents($config['path'].'data/cron_galaxy_scan.store',$datastring);

$content = ob_get_contents();
file_put_contents($config['path'].'log/cron_galaxy_scan.log',$content);
ob_end_clean();



// if we do 2/50 every hour we should have it done every 25 hours. 