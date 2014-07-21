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

jQuery(document).ready(function () {
    if (!starface_initialized) {

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

        starface_popup_open = false;
        starface_popup_closed = true;
    } else {

        starface_popup_open = true;
        starface_popup_closed = false;
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

function checkForNewStates() {

    jQuery.getJSON('cti/checkForNewStates.php',
        {
            myTimestamp: myTimestamp()
        },
        function (data) {
            checkData(data);


        });
    if (typeof hidden === "undefined" || !document[hidden]) {
        //console.log('added new timeout for state check - hidden: ' + document[hidden]);
        setTimeout('checkForNewStates()', 5000);
    }

}

function checkData(data) {
    //console.log(starface_current_calls);
    if (!data || data['counter'] == 0) {

        jQuery("#SF_ajaxContent").hide();
        jQuery("#SF_ajaxContent").empty();

        return;
    }
    var active_calls = new Array();
    jQuery.each(data, function (entryIndex, entry) {

        active_calls[entry['cti_id']] = entry['cti_id'];
        jQuery("#SF_ajaxContent").show();
        starface_current_calls[entry['cti_id']] = 'show';

        var sfDiv = jQuery("div#" + entry['cti_id']);
        if (starface_current_calls[entry['cti_id']] != 'hide') {
            if (!sfDiv.is("div")) {
                jQuery("#SF_ajaxContent").append(entry['html']);
                sfDiv = jQuery("div#" + entry['cti_id']);

            } else {
                jQuery(".sf_state", sfDiv).text(entry['state']);

            }
        }

    });

    var starface_hide_window = true;
    for (idx in starface_current_calls) {
        if (idx != "" && !active_calls[idx]) {
            jQuery("div#" + idx).hide();
            delete(starface_current_calls[idx]);
            //starface_current_calls[idx] = 'hide';
        } else if (starface_current_calls[idx] == 'show')
            starface_hide_window = false;

    }
    if (starface_hide_window) {

        jQuery("#SF_ajaxContent").hide();
        jQuery("#SF_ajaxContent").empty();

    }


}

function starfaceLoginProbe() {
    jQuery.get('cti/starfaceLoginProbe.php',
        {
            myTimestamp: myTimestamp()
        },
        function (data) {
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

        starface_popup_open = false;
        starface_popup_closed = undefined;
    }
}

