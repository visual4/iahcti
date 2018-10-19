<?php
/**
 * Created by PhpStorm.
 * User: brafreider
 * Date: 07.08.2018
 * Time: 11:51
 */

class ctiCall extends SugarBean
{
    public function validate(RowUpdate $update)
    {
        if ($update->getField('state') == 'REQUESTCALL') {
            require_once 'cti/classes/iCtiAdapter.php';
            $ctiAdapter = AppConfig::setting('cti.adapter') . 'Adapter';

            if (!$ctiAdapter) $ctiAdapter = 'starface';
            if (!is_file('cti/classes/' . $ctiAdapter . '.php'))
                exit();

            require_once 'cti/classes/' . $ctiAdapter . '.php';

            /**
             * @var iCtiAdapter Description
             */
            $ctiAdapter::dialNumber($update->getField('lookup_number'));
            throw new IAHActionCompleted('Anruf weitergeleitet');
        }
    }
}