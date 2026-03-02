// Display initial values
function plugaddr_Init(msg) {

    var ipdeb;
    var ipfin;

    var ipdebstr = document.getElementById("plugaddr_ipdeb").value;
    var ipfinstr = document.getElementById("plugaddr_ipfin").value;

    document.getElementById("plugaddr_range").innerHTML = "" + ipdebstr + " - " + ipfinstr;
}

function nameIsThere(params) {
    var root_doc = params;
    var nameElm = $('input[id*="name_reserveip"]');
    var typeElm = $('select[name="type"]');
    var divNameItemElm = $('div[id="nameItem"]');
    $.ajax({
        url: root_doc + '/front/addressing.php',
        type: "GET",
        dataType: "json",
        data: {
            action: 'isName',
            name: (nameElm.length != 0) ? nameElm.val() : '0',
            type: (typeElm.length != 0) ? typeElm.val() : '0',
        },
        success: function (json) {
            if (json) {
                divNameItemElm.show();
            } else {
                divNameItemElm.hide();
            }
        }
    });
}
