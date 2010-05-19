<?php
class BidsController extends AppController
{
    var $name = 'Bids';
    var $components = array ('Pagination', 'Filter', 'othAuth'); // Added
	var $helpers = array('Html'); // Added
   	var $scaffold;
  	var $uses = array('Bid');

  	}

 ?>