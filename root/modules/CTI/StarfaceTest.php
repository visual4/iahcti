<?php
/**
 * Created by PhpStorm.
 * User: brafreider
 * Date: 19.10.2017
 * Time: 15:33
 */

require_once("modules/CTI/include/api/UcpServerConnection.php");
require_once("modules/CTI/include/api/UcpServerFactory.php");
require_once("modules/CTI/include/StarfaceHelper.class.php");
require_once 'modules/CTI/v4_crm_crypt.php';


echo "<div class='tabDetailView'>";
checkCtiServer();

echo '<h2>Einstellungen aktueller Benutzer:</h2>';
checkCtiUser();

echo '<h2>Einstellungen alle Benutzer:</h2>';
checkCtiAllUsers();

echo '<h2>CTI Log</h2>';
checkCtiLog();


echo "</div>";

function checkCtiServer()
{
    echo '<h2>Einstellungen Starface Server:</h2>';
    $method = AppConfig::setting('cti.https') ? 'https://' : 'http://';
    $port = AppConfig::setting('cti.port') ? ':' . AppConfig::setting('cti.port') : '';
    $url = $method . AppConfig::setting('cti.host') . $port . AppConfig::setting('cti.uri');
    echo 'URL: ' . $url . '<br/>';

    $c = curl_init();
    curl_setopt_array($c, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 1,
        CURLOPT_SSL_VERIFYPEER => 0,
    ));

    $result = curl_exec($c);
    $code = curl_getinfo($c, CURLINFO_HTTP_CODE);
    $errors = curl_error($c);
    if ($errors) echo $errors . '<br/>';
//HTTP/1.1 400 Bad Request
//Server: Apache-Coyote/1.1
//Transfer-Encoding: chunked
//Date: Thu, 19 Oct 2017 14:18:33 GMT
//Connection: close


    if (empty($errors) && !empty($result) && strstr($result, 'Server: Apache-Coyote') && $code == '400') {
        echo 'http-Verbindung ok<br/>';
    } else {
        echo 'http-Verbindung nicht ok!! Prüfen Sie die 1CRM CTI-Einstellungen, https sollte aktiviert sein und der passende Port normalerweise 443. 
Ausserdem muss die Starface-Anlage vom CRM aus Netzwerktechnisch erreichbar sein.<br/>';
    }

    curl_close($c);
}

function checkCtiUser()
{
    global $current_user;
    $username = $current_user->cti_user_id;
    $password = $current_user->cti_password;
    $new_password = $current_user->cti_hash;
    if (empty($username) || empty($password) || (empty($new_password) && $password != 'XXXXXX')) {
        echo 'CTI Benutzername oder Passwort nicht im aktuellen Benutzer hinterlegt.<br/>';
        return;
    } else {
        echo 'CTI Benutzername und Passwort im aktuellen Benutzer hinterlegt. <br/>';
    }
    if ($password != 'XXXXXX') echo 'Das CTI Passwort ist unverschlüsselt in der Datenbank gespeichert, bitte geben Sie das Passwort in Ihren Benutzereinstellungen erneut ein!<br/>';
    else {
        $enc = new v4_crm_crypt();
        $password = $enc->decryptAES($current_user->cti_hash, $enc->getSalt());
    }
    $host = StarfaceHelper::getHostArray();
    $callback = StarfaceHelper::getCallbackArray();
    $server = UcpServerFactory::createUcpServer($username, $password, $host, $callback);
    echo 'Loginversuch mit den hinterlegten Daten:<pre>';
    $server->setDebugLevel(1);
    $loginReturn = $server->login();
    echo '</pre>';
    if ($loginReturn === true) {
        echo 'Loginversuch erfolgreich, ausgehende Anrufe sollten klappen.<br/>';
    }

}

function checkCtiAllUsers()
{
    $lq = new ListQuery('User');
    $lq->addSimpleFilter('status', 'Active');
    $ListResult = $lq->runQuery();
    echo "<table class='tabDetailView'><tr><td class='tabDetailViewDF'>Name</td><td class='tabDetailViewDF'>CTI Login</td><td class='tabDetailViewDF'>Passwort</td></tr>";
    foreach ($ListResult as $rowResult) {
        echo '<tr><td class="tabDetailViewDF">' . $rowResult->getField('user_name') . '</td>';
        echo '<td class="tabDetailViewDF">' . $rowResult->getField('cti_user_id', 'LEER!') . '</td>';
        $cti_password = $rowResult->getField('cti_password');
        if (!empty($cti_password)) {
            if ($cti_password == 'XXXXXX') {
                $password = 'OK (Verschlüsselt)';
            } else {
                $password = 'Klartext';
            }
        } else {
            $password = 'LEER!';
        }
        echo '<td class="tabDetailViewDF">' . $password . '</td></tr>';
    }
    echo '</table>';
}

function checkCtiLog()
{
    $lq = new ListQuery('ctiCall');
    $fields = $lq->getAvailableFields();
    $lq->addFields($fields);
    $listResult = $lq->runQuery(0, 20, false, 'date_modified DESC');
    echo "<table class='tabDetailView'><tr>";
    foreach ($fields as $field) {
        echo "<td class='tabDetailViewDF'>" . $field . "</td>";
    }
    echo "</tr>\n";
    foreach ($listResult as $rowResult) {
        echo "<tr>";
        foreach ($fields as $field) {
            echo "<td class='tabDetailViewDF'>" . $rowResult->getField($field) . "</td>";
        }
        echo "</tr>\n";
    }
    echo "</table>";

    if (!$listResult->count()) {
        echo 'aktuell keine Daten in der Tabelle cti_log.';
    }

    echo "<h3>Ausgabe der Logdatei</h3>In die Logdatei werden Informationen zur Callback-Verbindung Starface->1CRM geschrieben, sobald der Debugmodus in der Administration aktiv ist.<br>\n";
    if (!is_dir('logs')) mkdir('logs');
    $filename = 'logs/cti_error.log';
    echo '<pre>';
       echo nl2br(htmlspecialchars(readfile($filename)));
    echo "</pre>";

}