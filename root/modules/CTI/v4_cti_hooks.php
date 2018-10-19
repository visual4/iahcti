<?php
require_once 'modules/CTI/v4_crm_crypt.php';
class v4_cti_hooks {
    static function user_before_save(RowUpdate $rowUpdate){
        $ctiPass = $rowUpdate->getField('cti_password');
        if ($ctiPass == 'XXXXXX'){
            return;
        }
        $enc_class = new v4_crm_crypt();
        $salt = $enc_class->getSalt();
        $hash = $enc_class->encryptAES($ctiPass, $salt);

        $rowUpdate->set(array(
            'cti_password' => 'XXXXXX',
            'cti_hash' => $hash,
        ));
        return;
    }
    
    public static function page_init(BasePage &$page)
    {
        global $pageInstance;
        $sugar_version = AppConfig::version();
        $version =  array_map('intval', explode(".", $sugar_version));
        if ($version[0] >= 8 && $version[1] >= 5)
            $pageInstance->add_css_include("modules/CTI/css/cti.css");
    }
}
