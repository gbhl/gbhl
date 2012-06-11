<?php
class bidbuttonhelper extends Helper

{
var $output;

 function draw_buttons($row)
	{	$output ='';
    if ($row['Bid'])
		{
		$bids = $row['Bid'];

		foreach ($bids as $bid)
				{
                if($bid['partial']==1)
                {$output = '<b>Partial bid</b>';}

                else
                {$output = '<b>Full bid</b>';}
				}
		}

	else
	{	$output = '<b>no bids</b>';
	}

	return $output;
	}


 function draw_buttons_full($row)
	{
	$output ='';

    if ($row['Bid'])
		{
		$bids = $row['Bid'];

		foreach ($bids as $bid)
				{
                if($bid['partial']==1)
                {$output = '<b>Partial bid in place</b>';}

                else
                {$output = '<b>Complete bid already made</b>';}
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