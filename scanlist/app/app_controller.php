<?php



class AppController extends controller
  {

  	var $components = array ('othAuth'); // Added
	var $helpers = array('Html','OthAuth'); // Added
	// Authorisation - stick any view/controller that requires a login to access in the following array
	  var $othAuthRestrictions = array('Add','edit','delete','bidall','bidpartial','pages/admin','dedup','dedupselect0','undup','batch','edit','addtitle','digitizedtitles/edit','digitizedtitles/add', 'iprtitles/edit','iprtitles/add');
// these are the global restrictions, they are very important. all the permissions defined above are weighted against these restrictions to calculate the total allow or deny for a specific request.
			  function beforeFilter()
			    {

			        $auth_conf = array(
			                    'mode'  => 'oth',
			                    'login_page'  => '/users/login',
			                    'logout_page' => '/users/logout',
			                    'access_page' => '/bibs/index',
			                    'hashkey'     => 'MySEcEeTHaSHKeYz',
			                    'noaccess_page' => '/users/noaccess',
			                    'strict_gid_check' => false);

			        $this->othAuth->controller = &$this;
			        $this->othAuth->init($auth_conf);
			        $this->othAuth->check();


			    }

 }

?>