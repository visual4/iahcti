<?php

/**
 * Created by PhpStorm.
 * User: brafreider
 * Date: 04.02.14
 * Time: 16:44
 */
class v4_crm_crypt
{

    protected $encryption = 'rijndael-256';

    public function encryptAES($string, $key)
    {
        // Setzt den Verschlüsselungsalgorithmus
        // und setzt den Output Feedback (OFB) Modus
        $cp = mcrypt_module_open($this->encryption, '', 'ofb', '');

        // Ermittelt den Initialisierungsvector, der für die Modi CBC, CFB
        // und OFB benötigt wird.
        // Der Initialisierungsvector muss beim Entschlüsseln den selben
        // Wert wie beim Verschlüsseln haben.
        // Windows unterstützt nur MCRYPT_RAND
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
            $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($cp), MCRYPT_RAND);
        else
            $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($cp), MCRYPT_DEV_RANDOM);

        // Ermittelt die Anzahl der Bits, welche die Schlüssellänge
        // des Keys festlegen
        $ks = mcrypt_enc_get_key_size($cp);

        // Erstellt den Schlüssel, der für die Verschlüsselung genutzt wird
        $key = substr(hash('sha512', $key, true), 0, $ks);
        // Initialisiert die Verschlüsselung
        mcrypt_generic_init($cp, $key, $iv);

        // Verschlüsselt die Daten
        $encrypted = mcrypt_generic($cp, $string);

        // Deinitialisiert die Verschlüsselung
        mcrypt_generic_deinit($cp);

        // Schließt das Modul
        mcrypt_module_close($cp);

        return base64_encode(serialize(array(($encrypted),($iv))));

    }

    function decryptAES($string, $key)
    {
        $hashArray = unserialize(base64_decode($string));

        $iv = $hashArray[1];
        $content = $hashArray[0];
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

        return trim($decrypted);

    }

    public function getSalt(){
        return AppConfig::setting('config.unique_key').AppConfig::setting('cti.host');
    }

} 