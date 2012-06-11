<?php
class UsersController extends AppController
{
    var $name = 'Users';
    var $components = array ('Pagination', 'Filter', 'othAuth'); // Added
	var $helpers = array('Html'); // Added

// Activate sscaffolding for simple forms to add new users -- TURN OFF when done!!!!
// Remember to run all passwords through an MD5 string generator before saving form.
//   	var $scaffold;
  	var $uses = array('User');

			function login()
			{

			    if(isset($this->params['data']))
			    {
			        $auth_num = $this->othAuth->login($this->params['data']['User']);
			        $this->set('auth_msg', $this->othAuth->getMsg($auth_num));
			    }
			}

                        function adminlogin()
			{

			    if(isset($this->params['data']))
			    {
			        $auth_num = $this->othAuth->login($this->params['data']['User']);

			        $this->set('auth_msg', $this->othAuth->getMsg($auth_num));
			    }
			}


			function logout()
			{
			    $this->othAuth->logout();
			    $this->flash('You are now logged out !<br/> (Click here to continue)','/');
			}

			function noaccess()
			{
			    $this->flash("You don't have permissions to access this page <br/> (Click here to continue)",'/bibs');
			}

}
?>