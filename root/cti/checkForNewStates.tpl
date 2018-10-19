<!-- BEGIN: call -->
<div class="sf_info6pro" id="{CTI_ID}">
    <div class="leftColumnModuleHead">
        <div class="leftColumnModuleName">
            {CALL_TYPE} &nbsp;
            <!-- BEGIN: call_settings --><img border="0" width="15" height="15"
                                              src="modules/CTI/include/img/Icon-preferences.png"
                                              onclick="window.location='index.php?module=Users&action=EditView&record={CURRENT_USER_ID}';"
                                              style="cursor:pointer;"/>&nbsp;<!-- END: call_settings -->
            <img border="0" width="15" height="15" src="modules/CTI/include/img/Icon-schliessen.png"
                 onclick="starface_close_call('{CTI_ID}');" style="cursor:pointer;"/>
        </div>
    </div>
    <div class="subMenuOuter flat-top sidebarMenu">
        <table width="100%">
            <tr>
                <td class="sf_info_main_td" align="left" width="220" colspan="4">
                    <table cellpadding="0" cellspacin="0" class="sf_data6pro">

                        <tr>
                            <td width="30">{MOD.SFLBL_PHONE}:</td>
                            <td>{PHONE_NUMBER}</td>
                        </tr>
                        <!-- BEGIN: contact_link -->
                        <tr>
                            <td>{MOD.SFLBL_CONTACT}:</td>
                            <td>
                                <a href="index.php?action=DetailView&module=Contacts&record={CONTACT_ID}">
                                    {FULL_NAME}
                                </a>
                            </td>
                        </tr>
                        <!-- END: contact_link -->
                        <!-- BEGIN: lead_link -->
                        <tr>
                            <td>{MOD.SFLBL_LEAD}:</td>
                            <td>
                                <a href="index.php?action=DetailView&module=Leads&record={CONTACT_ID}">
                                    {FULL_NAME}
                                </a>
                            </td>
                        </tr>
                        <!-- END: lead_link -->
                        <!-- BEGIN: company_link -->
                        <tr>

                            <td>{MOD.SFLBL_COMPANY}:</td>
                            <td>
                                <a href="index.php?action=DetailView{COMPANY_LINK}">
                                    {COMPANY}
                                </a>

                            </td>
                            <td></td>

                        </tr>
                        <tr>
                            <td>
                                {MOD.LBL_BALANCE}:
                            </td>
                            <td>
                                {BALANCE}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                {MOD.LBL_TEMPERATURE}:
                            </td>
                            <td>
                                {TEMPERATURE}
                            </td>
                        </tr>

                        <!-- END: company_link -->
                    </table>
                </td>
            </tr>
            <tr>
                <td class="sf_info_main_td" colspan="4" width="180">
                    <table cellpadding="0" cellspacin="0" width="100%" class="starface_icons">
                        <th colspan="4">'<span class="sf_state">{STATE}</span>'</th>
                        </tr>
                        <tr>
                            <td style="padding-left:0px;">
                                <!-- BEGIN: account_exists -->
                                <div style="width:32px;height:30px;"><a
                                            href="index.php?module=Accounts&action=DetailView&record={COMPANY_ID}"><img
                                                src="modules/CTI/include/img/Icon_account_tranparent.png"
                                                border="0"/></a></div>
                                <!-- END: account_exists -->
                                <!-- BEGIN: account_exists_show -->
                                <div style="width:32px;height:30px;"><a
                                            href="index.php?module=Accounts&action=DetailView&record={COMPANY_ID}"><img
                                                src="modules/CTI/include/img/Icon_account_activ_tranparent.png"
                                                border="0"/></a></div>
                                <!-- END: account_exists_show -->
                                <!-- BEGIN: account_add -->
                                <div style="width:32px;height:30px;"><a
                                            href="index.php?module=Accounts&action=EditView&phone_office={PHONE_NUMBER_URL}"><img
                                                src="modules/CTI/include/img/Icon_account_plus_tranparent.png"
                                                border="0"/></a></div>
                                <!-- END: account_add -->
                            </td>
                            <td>
                                <!-- BEGIN: contact_exists -->
                                <div style="width:32px;height:30px;"><a
                                            href="index.php?module=Contacts&action=DetailView&record={CONTACT_ID}"><img
                                                src="modules/CTI/include/img/Icon_contact_tranparent.png"
                                                border="0"/></a></div>
                                <!-- END: contacts_exist -->
                                <!-- BEGIN: contact_exists_show -->
                                <div style="width:32px;height:30px;"><a
                                            href="index.php?module=Contacts&action=DetailView&record={CONTACT_ID}"><img
                                                src="modules/CTI/include/img/Icon_contact_activ_tranparent.png"
                                                border="0"/></a></div>
                                <!-- END: contacts_exist_show -->
                                <!-- BEGIN: contact_add -->
                                <div style="width:32px;height:30px;"><a
                                            href="index.php?module=Contacts&action=EditView&phone_work={PHONE_NUMBER_URL}"><img
                                                src="modules/CTI/include/img/Icon_contact_plus_tranparent.png"
                                                border="0"/></a></div>
                                <!-- END: contact_add -->
                            </td>
                            <td>
                                <!-- BEGIN: lead_exists -->
                                <div style="width:32px;height:30px;"><a
                                            href="index.php?module=Leads&action=DetailView&record={LEAD_ID}"><img
                                                src="modules/CTI/include/img/Icon_lead_tranparent.png" border="0"/></a>
                                </div>
                                <!-- END: lead_exists -->
                                <!-- BEGIN: lead_exists_show -->
                                <div style="width:32px;height:30px;"><a
                                            href="index.php?module=Leads&action=DetailView&record={LEAD_ID}"><img
                                                src="modules/CTI/include/img/Icon_lead_activ_tranparent.png"
                                                border="0"/></a></div>
                                <!-- END: lead_exists_show -->
                                <!-- BEGIN: lead_add -->
                                <div style="width:32px;height:30px;"><a
                                            href="index.php?module=Leads&action=EditView&phone_work={PHONE_NUMBER_URL}"><img
                                                src="modules/CTI/include/img/Icon_lead_plus_tranparent.png" border="0"/></a>
                                </div>
                                <!-- END: lead_add -->
                            </td>
                            <td style="padding-right:0px;">
                                <div style="width:32px;height:30px;"><a
                                            href="index.php?module=Calls&status=Held&action=EditView{ADD_NOTE_LINK}"><img
                                                src="modules/CTI/include/img/Icon_notiz_plus_tranparent.png"
                                                border="0"/></a></div>
                            </td>
                            <td style="padding-right:0px;">
                                <div style="width:32px;height:30px;"><a
                                            href="index.php?module=Cases&action=EditView{ADD_CASE_LINK}"><img
                                                src="modules/CTI/include/img/Icon_servicefall_plus_tranparent.png"
                                                border="0"/></a></div>
                            </td>
                        </tr>

                    </table>
                </td>
            </tr>
        </table>
    </div>
</div>
<!-- END: call -->



