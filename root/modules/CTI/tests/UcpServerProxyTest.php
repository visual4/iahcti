<?php
/**
 * Created by PhpStorm.
 * User: brafreider
 * Date: 12.09.2017
 * Time: 14:32
 */


require_once __DIR__ . "/../include/api/UcpServerFactory.php";
require_once __DIR__ . "/../include/StarfaceHelper.class.php";
require_once __DIR__ . '/../v4_crm_crypt.php';

define('sugarEntry', true);
define('DISABLE_CLEAN_INCOMING_DATA', 1);
$GLOBALS['sugar_version'] = '7.8.15';
chdir('../../../');
require_once 'include/entryPoint.php';
require_once 'include/config/AppConfig.php';

class UcpServerProxyTest extends PHPUnit_Framework_TestCase
{

    /**
     * Starface configuration  muss in 1CRM eingetragen werden
     */
    public function testV20Login()
    {
        $credentials = $this->getAdminUserSettings();
        $starface_user = $credentials['user'];
        $cti_password = $credentials['password'];
        $host = StarfaceHelper::getHostArray();

        $callback = StarfaceHelper::getCallbackArray();

        $server = UcpServerFactory::createUcpServer($starface_user, $cti_password, $host, $callback);

        $server->setDebugLevel(2);

        var_dump($server);

        $res = $server->login();

        $this->assertTrue($res);

        var_dump($res);

    }

    public function testV30Login()
    {
        $credentials = $this->getAdminUserSettings();
        $starface_user = $credentials['user'];
        $cti_password = $credentials['password'];
        $host = StarfaceHelper::getHostArray();

        $callback = StarfaceHelper::getCallbackArray();

        $server = UcpServerFactory::createUcpServer($starface_user, $cti_password, $host, $callback, 'V30');

        $server->setDebugLevel(2);

        var_dump($server);

        $res = $server->login();

        $this->assertTrue($res);
    }

    public function testKeepAlive(){
        $credentials = $this->getAdminUserSettings();
        $starface_user = $credentials['user'];
        $cti_password = $credentials['password'];
        $host = StarfaceHelper::getHostArray();

        $callback = StarfaceHelper::getCallbackArray();

        $server = UcpServerFactory::createUcpServer($starface_user, $cti_password, $host, $callback, 'V30');

        $server->setDebugLevel(1);

        $server->logout();
// probe prueft ob Login vorhanden und sendet gleichzeitig ein KeepAlive. Da erst Logout  muss das erste False sein
        $probe = $server->probe();

        $this->assertFalse($probe);

        $server->login();
// da hier erst ein Login stattfindet muss das Result true sein
        $probe = $server->probe();

        $this->assertTrue($probe);
    }

    public function getAdminUserSettings()
    {
        $admin = ListQuery::quick_fetch('User', '1', ['cti_user_id', 'cti_hash']);
        if (!$admin) throw new Exception('Admin User not found');
        $uid = $admin->getField('cti_user_id');
        $pwd = $admin->getField('cti_hash');
        if (empty($uid) || empty($pwd)) throw new Exception('Starface User Credentials not set');
        $enc = new v4_crm_crypt();
        $cti_password = $enc->decryptAES($pwd, $enc->getSalt());
        return ['user' => $uid, 'password' => $cti_password];

    }

    public function testInsertStarfaceMessage(){
        $callstate = [
            'calledNumber' => '00497114605430',
            'callerNumber' => '004917123456789',
            'state' => 'RINGING',
            'id' => rand(1000, 2000),
            'timestamp' => '2018-10-08',
            'callerName' => 'v4 Test Caller',
            'calledName' => 'v4 Test Called'
        ];
        $user = new stdClass();
        $user->id = 1;
        $user->cti_user_id = 10;

        $sh = new StarfaceHelper();
        $res = $sh->insertStarfaceMessage($callstate, $user);
    }

}
