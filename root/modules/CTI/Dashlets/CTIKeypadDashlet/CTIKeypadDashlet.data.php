<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * The contents of this file are subject to the SugarCRM Public License Version
 * 1.1.3 ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied.  See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by SugarCRM" logo and
 *    (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * The Original Code is: SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): ______________________________________.
 */



global $current_user;

$dashletData['CTIKeypadDashlet']['searchFields'] = array('priority'       => array('default' => ''),
                                                       'status'         => array('default' => array('Not Started', 'In Progress', 'Pending Input')),
                                                       'date_entered'   => array('default' => ''),
                                                       'date_start'       => array('default' => ''),                                                          
                                                       'date_due'       => array('default' => ''),



                                                       'assigned_user_id' => array('type'    => 'assigned_user_name', 
                                                                                   'default' => $current_user->name));
$dashletData['CTIKeypadDashlet']['columns'] = array('set_complete' => array('width'    => '1', 
                                                                          'label'    => 'LBL_LIST_CLOSE',
                                                                          'default'  => true,
                                                                          'sortable' => false),
                                                   'name' => array('width'   => '40', 
                                                                   'label'   => 'LBL_SUBJECT',
                                                                   'link'    => true,
                                                                   'default' => true),
                                                   'priority' => array('width'   => '10',
                                                                       'label'   => 'LBL_PRIORITY',
                                                                       'default' => true),                                                               
                                                   'date_start' => array('width'   => '10', 
                                                                         'label'   => 'LBL_START_DATE',
                                                                         'default' => true),                                                                                                       
                                                   'time_start' => array('width'   => '10', 
                                                                         'label'   => 'LBL_START_TIME',
                                                                         'default' => false),
                                                   'status' => array('width'   => '8', 
                                                                     'label'   => 'LBL_STATUS'),
                                                   'date_time_due' => array('width'   => '30', 
                                                                       'label'   => 'LBL_DUE_DATE',
                                                                       'related_fields' => array('date_due', 'time_due'),
                                                                       'default' => true),
                                                   'date_entered' => array('width'   => '15',
                                                                           'label'   => 'LBL_DATE_ENTERED'),
                                                   'date_modified' => array('width'   => '15',
                                                                           'label'   => 'LBL_DATE_MODIFIED'),
                                                   'created_by' => array('width'   => '8',
                                                                         'label'   => $app_strings['LBL_CREATED'],
                                                                         'sortable' => false),
                                                   'assigned_user_name' => array('width'   => '8',
                                                                                 'label'   => 'LBL_LIST_ASSIGNED_USER'),
                                                   'contact_name' => array('width'   => '8',
                                                                           'label'   => 'LBL_LIST_CONTACT',


                                                                           // longreach - start added
                                                                           'sortable' => false,
                                                                           'link' => true,
                                                                           'id' => 'contact_id',
                                                                           'ACLTag' => 'CONTACT',
        																	'related_fields' => array('contact_id', 'contact_last_name', 'contact_first_name', 'contact_salutation'),
                                                                           // longreach - end added
                                                                       ),
                                                   'parent_name' => array(
																	        'width'   => '20',
																	        'label'   => 'LBL_LIST_RELATED_TO',
																	        'dynamic_module' => 'PARENT_TYPE',
																	        'id' => 'PARENT_ID',
																	        'icon' => 'PARENT_TYPE',
																	        'link' => true,
																	        'default' => false,
																	        'sortable' => false,
																	        'ACLTag' => 'PARENT',
																	        'related_fields' => array('parent_id', 'parent_type')),



                                                                         );


?>
