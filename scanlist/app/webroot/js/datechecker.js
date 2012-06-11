function post1860datecheck()
{



var ok="true";



var thetitleid = document.getElementById("PackinglistlineTitleId").value;

var startdate = document.getElementById("PackinglistlineChronologyStart").value;
var startdatecap = parseInt(startdate.match(/\d{4}/), 10); 

var enddate = document.getElementById("PackinglistlineChronologyEnd").value;
var enddatecap = parseInt(enddate.match(/\d{4}/), 10);



var iprcheckeddate = document.getElementById("whethercdmchecked").value;

//logic: 
//       check there is a link to title
//       check that startdate has a value. If not, error message.
//       if it does, check that it contains a four digit integer
//       if it doesn't error
//       if it does, check whethercdmchecked. If not, the date must be pre-1860.
//       check enddate is greater than start date.
//       if there is a four digit date in the enddate field, perform the above line check on that too


if (!thetitleid)   {   

      alert("Please link to a title before adding volumes.");
      return false;

}



if (!startdatecap)   {   

      alert("Please enter a four-digit year in the start date field");
      return false;

}
else {

   if (startdatecap > 1860 && !iprcheckeddate)   {   
         alert("This title has not been copyright cleared for post-1860. Please enter a start date before 1861.");
         return false;
         }



}


if (enddatecap)   { 

   if (enddatecap < startdatecap) {
         alert("End date must be greater than start date. Please adjust.");
         return false;
         }

   if (enddatecap > 1860 && !iprcheckeddate)   {   
         alert("This title has not been copyright cleared for post-1860. Please enter an end date before 1861.");
         return false;
         }
   
   
         
}


}












