<?php

/*********************************************************************************
 *
 * STARFACE SugarCRM Connector is a computer telephony integration module for the
 * SugarCRM customer relationship managment program by SugarCRM, Inc.
 *
 * Copyright (C) 2010 STARFACE GmbH
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 * You can contact STARFACE GmbH at Stephanienstr. 102, 76133 Karlsruhe,
 * GERMANY or at the e-mail address info@starface-pbx.com
 *
 ********************************************************************************/

class StarfaceHelper
{
    public static function checkRequestParams(array $params, $method = "GET")
    {
        // TODO : differ between GET and POST
        foreach ($params as $param) {
            if (!isset($_REQUEST[$param])) {
                session_destroy();
                header("Location: " . $GLOBALS['sugar_config']['site_url']);
                exit();
            }

        }
    }


    public static function checkCurrentUser()
    {
        // check current user, redirect to homepage when not logged in
        if (!isset($_SESSION['authenticated_user_id'])) {
            session_destroy();
            //print json_encode(array(".")); //###insignio### don't show ["."] on logout screen
            exit(0);
        }
        $current_user = new User();
        $result = $current_user->retrieve($_SESSION['authenticated_user_id']);
        if ($result == null) {
            session_destroy();
            //print json_encode(array(".")); //###insignio###
            exit(0);
        }
        return $result;

    }

    public static function getHostArray()
    {
        $host = array(
            "host" => AppConfig::setting('cti.host'),
            "path" => AppConfig::setting('cti.uri'),
            "port" => AppConfig::setting('cti.port'),
            "method" => ((AppConfig::setting('cti.https')) ? true : false),

        );
        return $host;
    }

    public static function getCallbackArray($result = false)
    {
        $callback = array(
            "host" => AppConfig::setting('cti.callback_host'),
            "path" => AppConfig::setting('cti.callback_uri'),
            "port" => AppConfig::setting('cti.callback_port'),
            "method" => AppConfig::setting('cti.callback_https') ? 'https' : 'http',

        );
        return $callback;
    }


    public static function clearStarfaceLogFor($sfUserID, $dbObj)
    {

        // clean up database table starface_log
        $cleanQuery = "DELETE  from cti_call WHERE cti_user = '%s'";

        $cleanQuery = sprintf($cleanQuery, $sfUserID);

        //$innerResultSet = $dbObj->query($cleanQuery, false);
        if ($dbObj->checkError()) {
            StarfaceHelper::log("clearStarfaceLogFor :: query failed: " . $cleanQuery, "StarfaceHelper.log");
        }

    }

    public static function log($str, $file = "log.log", $append = true)
    {
        error_log($str, 3, $file);
    }


    public function queryModuleByPhoneNumber($module, $number)
    {
        require_once('include/ListView/ListFormatter.php');
        $fmt = new \ListFormatter($module);
        $lq =& $fmt->getQuery();

        $filter =& $fmt->getFilterForm();

        $filter->loadFilterLayout('Standard');

        $filter_data = array(
            'any_phone' => $number
        );


        $fmt->loadFilter($filter_data);
        $lq->addField('name');
        $lq->addField('_display');
        $res = $fmt->getQueryResult(0, 5, 'date_modified DESC');
        if (!$res || $res->failed)
            return [];
        return $res->getRowResults();
    }

    public function insertStarfaceMessage($callstate, & $user)
    {
        global $timedate;

        $internalDigits = AppConfig::setting('cti.internal_digits', 2);

        $numberToSearchFor = $callstate['calledNumber'];
        $direction = 'IN'; // als Default, da intern vermittelt weder PROCEEDING noch RINGING ist
        if ($callstate['state'] == 'PROCEEDING') {
            $direction = 'OUT';
        } elseif ($callstate['state'] == 'RINGING') {
            $direction = 'IN';
            if (strlen($callstate['callerNumber']) <= $internalDigits) {
                // intern vermittelt, umgekehrte Reihenfolge
                $numberToSearchFor = $callstate['calledNumber'];
            } else {
                // normal bei eingehend
                $numberToSearchFor = $callstate['callerNumber'];
            }
        }

        if (
            !$user->id ||
            !$user->cti_user_id ||
            /* teilweise wird im ersten Callback kein State übergeben*/
            empty(trim($callstate['state'])) ||
            /* keine Nummern übergeben */
            (!$callstate['callerNumber'] && !$callstate['calledNumber']) ||
            /* von intern nach intern */
            (strlen($callstate['callerNumber']) <= $internalDigits && strlen($callstate['calledNumber']) <= $internalDigits)
        ) {

            error_log('Anruf nicht geloggt: ' . $numberToSearchFor . ' Richtung: ' . $direction);
            return false;
        }


        $lq = new ListQuery('ctiCall');
        $lq->addSimpleFilter('assigned_user_id', $user->id);
        $lq->addSimpleFilter('cti_id', $callstate['id']);
        $res = $lq->runQuerySingle('date_modified DESC');

        if (!$res->failed) {
            $rowUpdate = RowUpdate::for_result($res, null, $user->id);
            $new_data = [
                'log' => $res->getField('log') . "\n#".$callstate['state'].'# (update)',
            ];
        } else {
            $rowUpdate = RowUpdate::blank_for_model('ctiCall', $user->id);
            $contact = current($this->queryModuleByPhoneNumber('Contact', substr($numberToSearchFor, 2)));
            if ($contact) {
                $contact_id = $contact->getPrimaryKeyValue();
                $new_data = [
                    'contact_id' => $contact_id,
                ];
            } else {
                // find lead
                $lead = current($this->queryModuleByPhoneNumber('Lead', substr($numberToSearchFor, 2)));
                if ($lead) {
                    $lead_id = $lead->getPrimaryKeyValue();
                    $new_data = [
                        'lead_id' => $lead_id,
                    ];
                }
            }
            $new_data = array_merge($new_data, [
                'log' => '#'.$callstate['state'].'# (new)',
                'lookup_number' => $numberToSearchFor,
                'cti_user' => $user->cti_user_id,
                'cti_id' => $callstate['id'],
                'timestamp' => $callstate['timestamp'],
                'caller_number' => $callstate['callerNumber'],
                'caller_name' => utf8_encode($callstate['callerName']),
                'called_number' => $callstate['calledNumber'],
                'called_name' => utf8_encode($callstate['calledName']),
                'assigned_user_id' => $user->id,
                'modified_user_id' => $user->id,
                'direction' => $direction,
            ]);
        }
        $update = array(
            'state' => $callstate['state'],
        );
        $update = array_merge($update, $new_data);
        $now = new DateTime();
        $oldState = $res->getField('state');
        if ($callstate['state'] == 'CONNECTED' && $oldState != 'CONNECTED') {
            $update['start'] = $now->format('Y-m-d H:i:s');
        }
        if ($callstate['state'] == 'HANGUP' && $oldState != 'HANGUP') {
            if ($oldState == 'CONNECTED') {
                $update['status'] = 'Held';
            } else {
                $update['status'] = 'Missed';
            }
            $update['end'] = $now->format('Y-m-d H:i:s');
            $dateInterval = date_diff(new Datetime($res->getField('start')), $now);
            $update['duration'] = $dateInterval->format('%i');
        }
        $rowUpdate->set($update);

        if (!$rowUpdate->validate()) {
            StarfaceHelper::log(print_r($rowUpdate->errors, 1));
            return false;
        }

        $rowUpdate->save();

        return true;
    }

    private function checkStarfaceTable()
    {
        // <se@starface.de>: ??
    }

    public function retrieveByStarfaceUser($starfaceUsername)
    {
        require_once('modules/Users/User.php');
        $user = new User();

        $query = 'SELECT  id
					FROM users 
					WHERE cti_user_id = \'' . $starfaceUsername . '\' 
					limit 1';

        $r = $user->db->query($query, false);

        if ($a = $user->db->fetchByAssoc($r)) {
            return $a['id'];
        } else {
            return null;
        }

    }

}
