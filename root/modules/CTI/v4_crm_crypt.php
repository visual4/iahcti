<?php

/**
 * Created by PhpStorm.
 * User: brafreider
 * Date: 04.02.14
 * Time: 16:44
 */

use Defuse\Crypto\Key;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\KeyProtectedByPassword;

require_once __DIR__ . '/vendor/autoload.php';

class v4_crm_crypt
{

    protected $encryption = 'rijndael-256';

    public function encryptAES($string, $key)
    {
        $skey = AppConfig::setting('visual4.k');
        if (empty($skey)) {
            $skey = $this->createKey($key);
        }

        $pkey = KeyProtectedByPassword::loadFromAsciiSafeString($skey);
        try {
        $dkey = $pkey->unlockKey($key);
        } catch (Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $ex) {
            $skey = $this->createKey($key);
            $pkey = KeyProtectedByPassword::loadFromAsciiSafeString($skey);
            $dkey = $pkey->unlockKey($key);
        }

        return Crypto::encrypt($string, $dkey);

    }

    function decryptAES($string, $key)
    {

        if (empty($string)) {
            return '';
        }
        $skey = AppConfig::setting('visual4.k');
        if (empty($skey)) {
            $skey = $this->createKey($key);
        }
        $pkey = KeyProtectedByPassword::loadFromAsciiSafeString($skey);
        $dkey = $pkey->unlockKey($key);
        try {

            $decrypted = Crypto::decrypt($string, $dkey);

        } catch (Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $ex) {

            $hashArray = unserialize(base64_decode($string));


            $iv = $hashArray[1];
            $content = $hashArray[0];

            if (empty($content)) return '';

            // Setzt den Verschlüsselungsalgorithmus
            // und setzt den Output Feedback (OFB) Modus
            $cp = mcrypt_module_open($this->encryption, '', 'ofb', '');

            // Ermittelt die Anzahl der Bits, welche die Schlüssellänge des Keys festlegen
            $ks = mcrypt_enc_get_key_size($cp);

            // Erstellt den Schlüssel, der für die Verschlüsselung genutzt wird
            $key = substr(hash('sha512', $key, true), 0, $ks);

            // Initialisiert die Verschlüsselung
            mcrypt_generic_init($cp, $key, $iv);

            // Entschlüsselt die Daten
            $decrypted = mdecrypt_generic($cp, $content);

            // Beendet die Verschlüsselung
            mcrypt_generic_deinit($cp);

            // Schließt das Modul
            mcrypt_module_close($cp);
        }

        return trim($decrypted);

    }

    public function getSalt()
    {
        return AppConfig::setting('config.unique_key') . AppConfig::setting('cti.host');
    }

    /**
     * @param $key
     * @param $skey
     */
    public function createKey($key)
    {
        $skey = KeyProtectedByPassword::createRandomPasswordProtectedKey($key);
        $xkey = $skey->saveToAsciiSafeString();
        AppConfig::set_local('visual4.k', $xkey);
        AppConfig::save_local();
        return $xkey;
    }

} 