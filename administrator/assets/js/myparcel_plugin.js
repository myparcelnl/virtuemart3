if (typeof MyParcel == 'undefined') { MyParcel = {}; }
//alert("aa");
var popup; // Handle to popup window
var consignments = {}; // Hyperlinks to consignments that haven't been processed yet
var locked = false; // Lock to prevent more than one consignment being created at a time

function onClickOnUnprocessedConsignment(event) {
    if (!popup || popup.closed) {
        // User closed the popup
        this.remove(); // Delete the link
    } else {
        popup.focus();
    }
}

MyParcel.virtuemart = {
    setConsignmentId: function(orderId, timestamp, consignmentId, tracktrace_link, retour, addresas) {
        //alert("order: " + orderId + "   time:"+timestamp+"   cons:"+consignmentId+"    trac:"+tracktrace_link+"     ret:"+retour);
        var mypa_div = document.createElement('div');

        // print checkbox
        var mypa_check = document.createElement('input');
        mypa_check.className = 'mypaleft mypacheck';
        mypa_check.type = 'checkbox';
        mypa_check.value = consignmentId;

        // pdf image
        var mypa_img = document.createElement('img');
        mypa_img.alt = 'print';
        mypa_img.src = addresas + 'administrator/components/com_myparcel/assets/images/myparcel_pdf.png';
        if (retour == 1) mypa_img.src = addresas + 'administrator/components/com_myparcel/assets/images/myparcel_retour.png';
        mypa_img.style.border = 0;

        // pdf image link
        var mypa_link = document.createElement('a');
        mypa_link.className = 'myparcel-pdf';
        mypa_link.onclick = new Function('return printConsignments(' + consignmentId + ');');
        mypa_link.href = '#';
        mypa_link.appendChild(mypa_img);

        // tracktrace link
        var mypa_track = document.createElement('a');
        mypa_track.target = '_blank';
        mypa_track.href = tracktrace_link;
        mypa_track.innerHTML = 'Track&Trace';

        // shove into DOM
        mypa_div.appendChild(mypa_check);
        mypa_div.appendChild(mypa_track);
        mypa_div.appendChild(mypa_link);
        var orderdiv = document.getElementById('mypa_exist_' + orderId);
        orderdiv.insertBefore(mypa_div, orderdiv.firstChild);

        popup.close();
        locked = false;
    }
};

var lastTimestamp = 0;

function _getTimestamp() {
    var ret = Math.round(new Date().getTime() / 1000);
    if (ret <= lastTimestamp) {
        ret = lastTimestamp + 1; // Make sure it is unique
    }
    return lastTimestamp = ret;
}

function createNewConsignment(orderId, retour) {
    if (locked) {
        if (!popup || popup.closed) {
            // User closed the popup
        } else {
            popup.focus();
            return;
        }
    }
    locked = true;
    var timestamp = _getTimestamp();

    var retourparam = '';
    if (retour == true) retourparam = '&retour=true';

    popup = window.open(
        'components/com_myparcel/myparcel_plugin.php?action=post' + '&order_id=' + orderId + '&timestamp=' + timestamp + retourparam,
        'myparcel',
        'width=730,height=830,dependent,resizable,scrollbars'
    );
    if (window.focus) { popup.focus(); }
    return false;
}

function printConsignments(consignmentList) {
    if (locked) {
        if (!popup || popup.closed) {
            // User closed the popup
        } else {
            popup.focus();
            return;
        }
    }
    locked = true;
    var timestamp = _getTimestamp();

    popup = window.open(
        'components/com_myparcel/myparcel_plugin.php?action=print' + '&consignments=' + consignmentList + '&timestamp=' + timestamp,
        'myparcel',
        'width=415,height=365,dependent,resizable,scrollbars'
    );
    if (window.focus) { popup.focus(); }
    return false;
}

function printConsignmentSelection() {
    var consignmentList = Array();
    var checkboxes = document.getElementsByClassName('mypacheck');
    for (var i = checkboxes.length - 1; i >= 0; i--) {
        if (checkboxes[i].checked == true && checkboxes[i].value != '') {
            consignmentList.push(checkboxes[i].value);
        }
    }
    return (consignmentList.length == 0) ? false : printConsignments(consignmentList.join('|'));
}


/*
 function checkAll(a, b) {
    console.log(a);
    b || (b = "cb");
    if (a.form) {
        for (var c = 0, d = 0, f = a.form.elements.length; d < f; d++) {
            var e = a.form.elements[d];
            if (e.type == a.type && (b && 0 == e.id.indexOf(b) || !b)) e.checked = a.checked, c += !0 == e.checked ? 1 : 0
        }
        if (a.form.boxchecked) a.form.boxchecked.value = c;
        return !0
    }
    for (var e = document.adminForm, c = e.toggle.checked, f = a, g = 0, d = 0; d < f; d++) {
        var h = e[b + "" + d];
        if (h) h.checked = c, g++
    }
    document.adminForm.boxchecked.value = c ? g : 0
}
*/

function processConsignmentSelection(a, b) {
    var consignmentList2 = Array();

    /** Start @Since version 1.0.9 **/
    var checkboxes = document.getElementsByClassName('mypafunc');
    a = checkboxes.length;
    /** End @Since version 1.0.9 **/

    b || (b = "cb");
    for (var e = document.adminForm, c = e.toggle.checked, f = a, g = 0, d = 0; d < f; d++) {
        var h = e[b + "" + d];
        if (h) {
            if (h.checked == true) {
                consignmentList2.push(h.value);
                console.log(h.value);
            }

            g++;
        }
    }
    return (consignmentList2.length > 0 && confirm("Hiermee creëert u   " + consignmentList2.length + "   MyParcel labels.\n\nKlik op OK om door te gaan.")) //return (consignmentList2.length > 0 && confirm("This will create   " + consignmentList2.length + "   labels.\n\nAre you sure?"))
        ?
        processConsignments(consignmentList2.join('|')) :
        false;


    /*
    var consignmentList = Array();
    var checkboxes = document.getElementsByClassName('mypacheck');
    for(var i = checkboxes.length - 1; i >= 0; i--)
    {
    	if(checkboxes[i].checked == true)
    	{
    		consignmentList.push(checkboxes[i].value);
    	}
    }
    return (consignmentList.length > 0 && confirm("This will create   " + consignmentList.length + "   labels.\n\nAre you sure?"))
    ? processConsignments(consignmentList.join('|'))
    : false;
    */
}

function processConsignments(consignmentList) {
    if (locked) {
        if (!popup || popup.closed) {
            // User closed the popup
        } else {
            popup.focus();
            return;
        }
    }
    locked = true;
    var timestamp = _getTimestamp();

    popup = window.open(
        'components/com_myparcel/myparcel_plugin.php?action=process' + '&order_ids=' + consignmentList + '&timestamp=' + timestamp,
        'myparcel',
        'width=415,height=365,dependent,resizable,scrollbars'
    );
    if (window.focus) { popup.focus(); }
    return false;
}

function selectAllConsignmentsForPrint(checkboxas) {
    var checkboxes = document.getElementsByClassName('mypacheck');
    if (checkboxas.checked == true) {
        for (var i = checkboxes.length - 1; i >= 0; i--) {
            checkboxes[i].checked = true;
        }
    } else {
        for (var i = checkboxes.length - 1; i >= 0; i--) {
            checkboxes[i].checked = false;
        }
    }
}