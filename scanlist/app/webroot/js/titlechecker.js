function titlepickedcheck()
{



var ok="true";


var thetitleid = document.getElementById("ProblemlistlineTitleId").value;


//logic: 
//       check there is a title id (catkey). If not error


if (!thetitleid)   {   

      alert("Please link to a title before adding your problems.");
      return false;

}



}