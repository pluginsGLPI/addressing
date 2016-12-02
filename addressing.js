// Compute and Check the input data
function plugaddr_Compute(msg) {
   
   var ipdeb = new Array();
   var ipfin = new Array();
   var subnet = new Array();
   var netmask = new Array();
   var i;
   var val;
   
   document.getElementById("plugaddr_range").innerHTML = "";
   document.getElementById("plugaddr_ipdeb").value = "";
   document.getElementById("plugaddr_ipfin").value = "";
   
   for (var i = 0; i < 4 ; i++) {
      val=document.getElementById("plugaddr_ipdeb"+i).value
      if (val=='' || isNaN(val) || parseInt(val)<0 || parseInt(val)>255) {
         document.getElementById("plugaddr_range").innerHTML=msg+" ("+val+")"
         return false;
      }
      ipdeb[i]=parseInt(val);

      val=document.getElementById("plugaddr_ipfin"+i).value
      if (val=='' || isNaN(val) || parseInt(val)<0 || parseInt(val)>255) {
         document.getElementById("plugaddr_range").innerHTML=msg+" ("+val+")"
         return false;
      }
      ipfin[i]=parseInt(val);
   }

   if (ipdeb[0]>ipfin[0]) {
      document.getElementById("plugaddr_range").innerHTML=msg+" ("+ipdeb[0]+">"+ipfin[0]+")";
      return false;	
   }
   if (ipdeb[0]==ipfin[0] && ipdeb[1]>ipfin[1]) {
      document.getElementById("plugaddr_range").innerHTML=msg+" ("+ipdeb[1]+">"+ipfin[1]+")";
      return false;	
   }
   if (ipdeb[0]==ipfin[0] && ipdeb[1]==ipfin[1] && ipdeb[2]>ipfin[2]) {
      document.getElementById("plugaddr_range").innerHTML=msg+" ("+ipdeb[2]+">"+ipfin[2]+")";
      return false;		
   }
   if (ipdeb[0]==ipfin[0] && ipdeb[1]==ipfin[1] && ipdeb[2]==ipfin[2] && ipdeb[3]>ipfin[3]) {
      document.getElementById("plugaddr_range").innerHTML=msg+" ("+ipdeb[3]+">"+ipfin[3]+")";
      return false;	
   }
   document.getElementById("plugaddr_range").innerHTML=""+ipdeb[0]+"."+ipdeb[1]+"."+ipdeb[2]+"."+ipdeb[3]+" - "+ipfin[0]+"."+ipfin[1]+"."+ipfin[2]+"."+ipfin[3];
   document.getElementById("plugaddr_ipdeb").value=""+ipdeb[0]+"."+ipdeb[1]+"."+ipdeb[2]+"."+ipdeb[3];
   document.getElementById("plugaddr_ipfin").value=""+ipfin[0]+"."+ipfin[1]+"."+ipfin[2]+"."+ipfin[3];
   return true;
}

// Check the input data (from onSubmit)
function plugaddr_Check(msg) {

   if (plugaddr_Compute(msg)) {
      return true;
   }
   alert(msg);
   return false;
}

// Refresh the check message after onChange (from text input)
function plugaddr_ChangeNumber(msg) {

   var lst=document.getElementById("plugaddr_subnet");
   lst.selectedIndex=0;
   plugaddr_Compute(msg);
}

// Refresh the check message after onChange (from list)
function plugaddr_ChangeList(msg) {

   var i;
   var lst=document.getElementById("plugaddr_subnet");
   if (lst.value == "0") return;
   var champ=lst.value.split("/");
   var subnet=champ[0].split(".");
   var netmask=champ[1].split(".", 4);
   var ipdeb = new Array();
   var ipfin = new Array();
   
   netmask[3] = parseInt(netmask[3]).toString();
   if (lst.selectedIndex>0) {
      for (var i=0;i<4;i++) {
         ipdeb[i]=subnet[i]&netmask[i];
         ipfin[i]=ipdeb[i]|(255-netmask[i]);
         if (i==3) {
            ipdeb[3]++;
            ipfin[3]--;
         }
         document.getElementById("plugaddr_ipdeb"+i).value=ipdeb[i];
         document.getElementById("plugaddr_ipfin"+i).value=ipfin[i];
      }
      plugaddr_Compute(msg);
   }
}

// Display initial values
function plugaddr_Init(msg) {

   var ipdeb = new Array();
   var ipfin = new Array();

   var ipdebstr = document.getElementById("plugaddr_ipdeb").value;
   var ipfinstr = document.getElementById("plugaddr_ipfin").value;
   
   document.getElementById("plugaddr_range").innerHTML=""+ipdebstr+" - "+ipfinstr;
   
   ipdeb=ipdebstr.split(".");
   ipfin=ipfinstr.split(".");
   for (i=0;i<4;i++) {
      document.getElementById("plugaddr_ipdeb"+i).value=ipdeb[i];		
      document.getElementById("plugaddr_ipfin"+i).value=ipfin[i];		
   }
}

// Display initial values
function plugaddr_CheckFilter(msg) {
    if (plugaddr_IsFilter(msg)) {
      return true;
   }
   alert(msg);
   return false;
}

// Display initial values
function plugaddr_IsFilter(msg) {

   var ipdeb = new Array();
   var ipfin = new Array();
   var subnet = new Array();
   var netmask = new Array();
   var i;
   var val;
   
   document.getElementById("plugaddr_range").innerHTML = "";
   document.getElementById("plugaddr_ipdeb").value = "";
   document.getElementById("plugaddr_ipfin").value = "";
   
   for (var i = 0; i < 4 ; i++) {
      val=document.getElementById("plugaddr_ipdeb"+i).value
      if (val=='' || isNaN(val) || parseInt(val)<0 || parseInt(val)>255) {
         document.getElementById("plugaddr_range").innerHTML=msg+" ("+val+")"
         return false;
      }
      ipdeb[i]=parseInt(val);

      val=document.getElementById("plugaddr_ipfin"+i).value
      if (val=='' || isNaN(val) || parseInt(val)<0 || parseInt(val)>255) {
         document.getElementById("plugaddr_range").innerHTML=msg+" ("+val+")"
         return false;
      }
      ipfin[i]=parseInt(val);
   }

   if (ipdeb[0]>ipfin[0]) {
      document.getElementById("plugaddr_range").innerHTML=msg+" ("+ipdeb[0]+">"+ipfin[0]+")";
      return false;	
   }
   if (ipdeb[0]==ipfin[0] && ipdeb[1]>ipfin[1]) {
      document.getElementById("plugaddr_range").innerHTML=msg+" ("+ipdeb[1]+">"+ipfin[1]+")";
      return false;	
   }
   if (ipdeb[0]==ipfin[0] && ipdeb[1]==ipfin[1] && ipdeb[2]>ipfin[2]) {
      document.getElementById("plugaddr_range").innerHTML=msg+" ("+ipdeb[2]+">"+ipfin[2]+")";
      return false;		
   }
   if (ipdeb[0]==ipfin[0] && ipdeb[1]==ipfin[1] && ipdeb[2]==ipfin[2] && ipdeb[3]>ipfin[3]) {
      document.getElementById("plugaddr_range").innerHTML=msg+" ("+ipdeb[3]+">"+ipfin[3]+")";
      return false;	
   }

   document.getElementById("plugaddr_range").innerHTML=""+ipdeb[0]+"."+ipdeb[1]+"."+ipdeb[2]+"."+ipdeb[3]+" - "+ipfin[0]+"."+ipfin[1]+"."+ipfin[2]+"."+ipfin[3];
   document.getElementById("plugaddr_ipdeb").value=""+ipdeb[0]+"."+ipdeb[1]+"."+ipdeb[2]+"."+ipdeb[3];
   document.getElementById("plugaddr_ipfin").value=""+ipfin[0]+"."+ipfin[1]+"."+ipfin[2]+"."+ipfin[3];
   return true;
}

function nameIsThere(params) {
    var root_doc = params;
    var nameElm = $('input[id*="name_reserveip"]');
    var typeElm = $('select[name="type"]');
    var divNameItemElm = $('div[id="nameItem"]');
    $.ajax({
        url: root_doc + '/plugins/addressing/ajax/addressing.php',
        type: "POST",
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

function getAddressingFormData(form) {
    var unindexed_array = form.serializeArray();
    var indexed_array = {};

    $.map(unindexed_array, function (n, i) {
        indexed_array[n['name']] = n['value'];
    });

    return JSON.stringify(indexed_array);
}

function plugaddr_loadForm(action, modal, params) {
    var formInput;
    if (params.form != undefined) {
        formInput = getAddressingFormData($('form[name="' + params.form + '"]'));
    }

    $.ajax({
        url: params.root_doc + '/plugins/addressing/ajax/addressing.php',
        type: "POST",
        dataType: "html",
        data: {
            'action': action,
            'params': params,
            'pdf_action': params.pdf_action,
            'formInput': formInput,
            'modal': modal,
        },
        success: function (response, opts) {
            $('#' + modal).html(response);

            switch (action) {
                case 'showForm':
                    $('#' + modal).dialog({
                        autoOpen: true,
                        height: params.height,
                        width: params.width,
                        overflow: "none"
                    });
                    break;
            }
        }
    });
}
