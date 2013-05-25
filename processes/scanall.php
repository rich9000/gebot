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



// ******************* END EDIT STUFF IN HERE *********************

$ge = new GalacticEmpire($USER,$PASSWORD,$host);
$ge -> sessID = $sessID;
$ge -> uuid = $uuid;
$ge->scanSpyReport();
exit;
$ge -> scanAllOverview();
$ge -> scanAllFleetInfo();


$ge->probeAll(3,1100,1,500);
