<?php
class iprtitlebuttonhelper extends Helper

{
var $output;

 function draw_buttons($row)
	{
	$output ='';


        if ($row['Iprtitle'])
  		{
		$iprtitles = $row['Iprtitle'];


			foreach ($iprtitles as $iprtitle) {

					if($iprtitles['cdm_checked_status']) // ie if there is one which allows us to tell whether to show an add or edit button!
					{

					     if ($iprtitles['cdm_checked_decision'] == 1) // ie if not yet reviewed by a CDM
					     {
					        $output = 'edit';
					     }
					     else
					     {
					        $output = 'view';
					     }
					}
					else
					{
					   $output = 'add'; // this will force an add button
					}
			}
	    }

	return $output;

	}


 function draw_buttons_cdm($row)
	{
	$output ='';


        if ($row['Iprtitle'])
  		{
		$iprtitles = $row['Iprtitle'];


			foreach ($iprtitles as $iprtitle) {

					if($iprtitles['cdm_checked_status'] == 2) // ie ready to check, so allows us to tell whether to show an edit button!
					{

					  if($iprtitles['cdm_checked_decision'] == 1)
					  {
                	     $output = 'edit';
                	  }
                	  else
                	  {
                	     $output = 'editdone';
                	  }
					}
					else
					{
					   $output = '';
					}
			}
	    }

	return $output;

	}


 function draw_buttons_full($row)
	{
	$output ='';

    if ($row['Iprtitle'])
		{
		$iprtitles = $row['Iprtitle'];

		foreach ($iprtitles as $iprtitle)
				{
                if($iprtitle['partial']==1)
                {$output = '<b>Partial iprtitle in place</b>';}

                else
                {$output = '<b>Complete iprtitle already made</b>';}
				}
		}

	else
	{
	$output = 'none';
	}

	return $output;
	}


}

?>