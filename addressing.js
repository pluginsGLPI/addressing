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
   var champ=lst.value.split("/");
   var subnet=champ[0].split(".");
   var netmask=champ[1].split(".");
   var ipdeb = new Array();
   var ipfin = new Array();
   
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
