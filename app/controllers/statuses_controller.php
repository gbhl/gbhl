<?php
class StatusesController extends AppController
{
    var $name = 'Statuses';
    var $components = array ('Pagination', 'Filter', 'othAuth'); // Added
	var $helpers = array('Html'); // Added
   	var $scaffold;
  	var $uses = array('Status');

  	}

 ?>