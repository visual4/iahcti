//*********************************************************************************
// 
// STARFACE SugarCRM Connector is a computer telephony integration module for the
// SugarCRM customer relationship managment program by SugarCRM, Inc.
//
// Copyright (C) 2010 STARFACE GmbH
// 
// This program is free software; you can redistribute it and/or modify it under
// the terms of the GNU General Public License version 3 as published by the
// Free Software Foundation.
// 
// This program is distributed in the hope that it will be useful, but WITHOUT
// ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
// FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
// details.
// 
// You should have received a copy of the GNU General Public License along with
// this program; if not, see http://www.gnu.org/licenses or write to the Free
// Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
// 02110-1301 USA.
// 
// You can contact STARFACE GmbH at Stephanienstr. 102, 76133 Karlsruhe,
// GERMANY or at the e-mail address info@starface-pbx.com
// 
// ********************************************************************************
if (typeof(starface_initialized) == undefined || starface_initialized == undefined)
    var starface_initialized = false;
if (typeof(starface_popup_open) == undefined || starface_popup_open == undefined) {
    var starface_popup_open = false;
    var starface_popup_closed = undefined;
}
if (typeof(starface_current_calls) == undefined || starface_current_calls == undefined) {
    var starface_current_calls = new Array();
}

var hidden, visibilityChange;
if (typeof document.hidden !== "undefined") { // Opera 12.10 and Firefox 18 and later support 
    hidden = "hidden";
    visibilityChange = "visibilitychange";
} else if (typeof document.mozHidden !== "undefined") {
    hidden = "mozHidden";
    visibilityChange = "mozvisibilitychange";
} else if (typeof document.msHidden !== "undefined") {
    hidden = "msHidden";
    visibilityChange = "msvisibilitychange";
} else if (typeof document.webkitHidden !== "undefined") {
    hidden = "webkitHidden";
    visibilityChange = "webkitvisibilitychange";
}

jQuery(document).ready(function()
{
    if (!starface_initialized) {
        if (document.getElementById('dcmenuitems')) {
            var li_el = $('<div></div>');
            li_el.attr('id', 'starface_icon_li');
            $('#leftCol').prepend(li_el);
            var a_el = $('<a></a>');
            a_el.attr('href', 'javascript:toggle_starface_popup();');
            li_el.append(a_el);
            var img_el = $('<img />');
            img_el.attr('src', 'modules/CTI/include/img/Icon-STARFACE-hoerer.png');
            img_el.attr('class', 'icon');
            img_el.attr('id', 'starface_icon');
            a_el.append(img_el);
        }




        // no checking for the login page
        if (location.href.indexOf('action=Login') == -1) {
            if (!document.getElementById('dcmenuitems')) {
                //console.log('alternative');
                jQuery('<div id="SF_ajaxContent" class="headerList" style="display:none;overflow:hidden;padding:0px 0px 0px 10px;"></div>').prependTo('#leftCol');
            }
            checkForNewStates();
            starfaceLoginProbe();

        }

        starface_initialized = true;
    }
    if (typeof document.addEventListener === "undefined" ||
            typeof hidden === "undefined") {
        //console.log('visibility state not supported');
    } else {

        // Handle page visibility change   
        document.addEventListener(visibilityChange, handleVisibilityChange, false);

    }


});

function handleVisibilityChange() {
    //console.log('visibility changed to:' + document[hidden]);
    if (!document[hidden]) {
        checkForNewStates();
        starfaceLoginProbe();
    }
}


function toggle_starface_popup() {
    //depth = 0;
    if (starface_popup_open) {
        //console.log('zu');

        lastLoadedMenu = undefined;
        //DCMenu.closeOverlay();
        starface_popup_open = false;
        starface_popup_closed = true;
    } else {
        //console.log('offen');
        //DCMenu.showView('<div id="SF_ajaxContent"><img border="0" id="starface_loading" src="themes/default/images/loading.gif" /></div>',"starface_icon_li");
        //document.getElementById('starface_icon_li').getElementsByClass('hd')[0].style.display = 'none';
        //checkForNewStates();
        jQuery('div.starface_icon_li div.hd').each(function() {
            jQuery(this).hide();
        });
        jQuery('div.starface_icon_li div.dashletPanel').each(function() {
            jQuery(this).css('width', '480px');
        });
        starface_popup_open = true;
        starface_popup_closed = false;
        data = 'test';


    }
}

function myTimestamp() {
    var d = new Date();
    // formated as yyyy-mm-dd HH:MM:ss
    return d.getFullYear() + '-' +
            pad(d.getMonth() + 1) + '-' + // because month starts at zero
            pad(d.getDate()) + ' ' +
            pad(d.getHours()) + ':' +
            pad(d.getMinutes()) + ':' +
            pad(d.getSeconds());
}

function pad(n) {
    n = n.toString();
    return n.length == 1 ? '0' + n : n;
}

// look for new events sent from starface server:
var checkNo = 0;
function checkForNewStates() {
    //checkNo ++;
    if (checkNo < 3) {//SUGAR.themes.theme_name
        jQuery.getJSON('cti/checkForNewStates.php?dcmenu=' + (document.getElementById('dcmenuitems') ? 'true' : 'false') + '&current_theme=' + SUGAR.themes.theme_name,
                {
                    myTimestamp: myTimestamp()
                },
        function(data) {
            checkData(data);
            // wait n milliseconds for the next call

        });
        if (typeof hidden === "undefined" || !document[hidden]) {
            //console.log('added new timeout for state check - hidden: ' + document[hidden]);
            setTimeout('checkForNewStates()', 5000);
        }
    }
}

function checkData(data) {
    //console.log(starface_current_calls);
    jQuery('#starface_loading').hide();
    if (!data || data['counter'] == 0) {
        if (document.getElementById('dcmenuitems')) {
            if (starface_popup_open) {
                if (starface_popup_closed == undefined) {
                    lastLoadedMenu = undefined;
                    //DCMenu.closeOverlay();
                    starface_popup_open = false;
                } else
                    jQuery("#SF_ajaxContent").html(data['html']);
            }
        } else {
            jQuery("#SF_ajaxContent").hide();
            jQuery("#SF_ajaxContent").empty();
        }
        return;
    }
    jQuery("#starface_no_calls").hide();
    var active_calls = new Array();
    jQuery.each(data, function(entryIndex, entry) {
        if (entryIndex != 'no_calls_html') {

            // response is not empty, lets walk through the json array

            if (document.getElementById('dcmenuitems')) {

                active_calls[entry['cti_id']] = entry['cti_id'];
                if (!starface_current_calls[entry['cti_id']]) {
                    starface_popup_closed = undefined;
                    starface_current_calls[entry['cti_id']] = 'show';
                }
                if (!starface_popup_open && starface_popup_closed == undefined) {
                    DCMenu.showView('<div id="SF_ajaxContent"></div>', "starface_icon_li");
                    jQUery('div.starface_icon_li div.hd').each(function() {
                        jQuery(this).hide();
                    });
                    jQUery('div.starface_icon_li div.dashletPanel').each(function() {
                        jQuery(this).css('width', '480px');
                    });
                    starface_popup_open = true;
                }
            } else {

                active_calls[entry['cti_id']] = entry['cti_id'];
                jQuery("#SF_ajaxContent").show();
                starface_current_calls[entry['cti_id']] = 'show';
            }
            var sfDiv = jQuery("div#" + entry['cti_id']);
            if (starface_current_calls[entry['cti_id']] != 'hide') {
                if (!sfDiv.is("div")) {
                    jQuery("#SF_ajaxContent").append(entry['html']);
                    sfDiv = jQuery("div#" + entry['cti_id']);
                    jQuery('.sf_open_memo', sfDiv).click(function() {
                        var newHREF = "index.php?module=Calls&action=EditView&return_module=Calls&return_action=DetailView&parent_type=Contacts";
                        newHREF += "&direction=" + entry['direction'];
                        newHREF += "&status=Held";
                        newHREF += "&parent_id=" + entry['contact_id'];
                        newHREF += "&parent_name=" + entry['full_name'];
                        location.href = newHREF;
                    });
                } else {
                    jQuery(".sf_state", sfDiv).text(entry['state']);

                }
            }
        }
    });

    var starface_hide_window = true;
    for (idx in starface_current_calls) {
        if (!active_calls[idx]) {
            jQuery("div#" + idx).hide();
            delete(starface_current_calls[idx]);
            //starface_current_calls[idx] = 'hide';
        } else if (starface_current_calls[idx] == 'show')
            starface_hide_window = false;

    }
    if (starface_hide_window) {

        if (document.getElementById('dcmenuitems')) {
            if (starface_popup_closed == undefined) {
                lastLoadedMenu = undefined;
                DCMenu.closeOverlay();
                starface_popup_open = false;
                starface_popup_closed = true;
            }
            jQuery("#SF_ajaxContent").html(data['no_calls_html']);
        } else {
            jQuery("#SF_ajaxContent").hide();
            jQuery("#SF_ajaxContent").empty();
        }
    }

    jQuery("div.sf_info").each(function() {
        var thisID = jQuery(this).attr('id');
        var grepd = jQuery.grep(data, function(i) {
            return i['cti_id'] == thisID;
        });
        //alert("grepd: " + grepd.length);
        if (grepd.length == 0) {
            jQuery("#" + thisID).remove();
        }


    });

}

function starfaceLoginProbe() {
    jQuery.get('cti/starfaceLoginProbe.php',
            {
                myTimestamp: myTimestamp()
            },
    function(data) {
        // wait n milliseconds for the next call
        if (typeof hidden === "undefined" || !document[hidden]) {
            setTimeout('starfaceLoginProbe()', 45000);
        }
    });
}


function starface_close_call(cti_id) {
    jQuery('#' + cti_id).hide();
    starface_current_calls[cti_id] = 'hide';
    var starface_hide_window = true;
    for (idx in starface_current_calls) {
        if (starface_current_calls[idx] == 'show')
            starface_hide_window = false;

    }
    if (starface_hide_window) {
        lastLoadedMenu = undefined;
        DCMenu.closeOverlay();
        starface_popup_open = false;
        starface_popup_closed = undefined;
    }
}

