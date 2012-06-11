<?php
class digitizedtitlebuttonhelper extends Helper

{
var $output;

 function draw_buttons($row)
	{
	$output ='';


        if ($row['Digitizedtitle'])
  		{
		$digitizedtitles = $row['Digitizedtitle'];


			foreach ($digitizedtitles as $digitizedtitle) {

					if($digitizedtitles['digitized_title_status']) // ie if there is one which allows us to tell whether to show an add or edit button!
					{
					  $output = 'edit';
					}
					else
					{
					   $output = 'add'; // this will force an add button
					}
			}
	    }

	return $output;

	}


 function draw_buttons_full($row)
	{
	$output ='';

    if ($row['Digitizedtitle'])
		{
		$digitizedtitles = $row['Digitizedtitle'];

		foreach ($digitizedtitles as $digitizedtitle)
				{
                if($digitizedtitle['partial']==1)
                {$output = '<b>Partial digitizedtitle in place</b>';}

                else
                {$output = '<b>Complete digitizedtitle already made</b>';}
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