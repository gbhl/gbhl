<?php
class EmptycachesController extends AppController
{
    var $name = 'Emptycaches';


			function index()
			{
			    clearCache();
			    $this->flash('Caches cleared !<br/> (Click here to continue)','/');
			}

			function noaccess()
			{
			    $this->flash("You don't have permissions to access this page <br/> (Click here to continue)",'/bibs');
			}

}
?>