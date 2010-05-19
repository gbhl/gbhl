<?php
class ProblemlistsController extends AppController
{
  var $name = 'Problemlists';
    var $components = array ('Pagination', 'Filter', 'othAuth'); // Added
    var $helpers = array('Html','Pagination', 'Filter', 'Matchlinker'); // Added
    //var $scaffold;
    var $uses = array('Problemlist','Bid','Status','Title', 'Problemlistline');



    function add($id = null) // auto add linked record with default values and load the edit form with the data
        {


        // defaults are set in the SQL table itself except for user id

        // try and save

			if ($this->Problemlist->save($this->data['Problemlist']))
			{

				// load the form for edit with the new id
				$id = $this->Problemlist->id;
                $this->redirect("/problemlists/edit/" . $id);
		 	}
		 	else // save was a bloomin' failure
		 	{
		 	    $this->flash('Your problem list could not be created. Report to admin.',"/problemlists/index");
		 	}

        }

    function addproblem($id = null) // auto add linked record with default values and load the edit form with the data
        {


        // defaults are set in the SQL table itself except for user id

        // try and save

			if ($this->Problemlist->save($this->data['Problemlist']))
			{

				// load the form for edit with the new id
				$id = $this->Problemlist->id;
                $this->redirect("/problemlists/editproblem/" . $id);
		 	}
		 	else // save was a bloomin' failure
		 	{
		 	    $this->flash('Your problem list could not be created. Report to admin.',"/problemlists/index");
		 	}

        }


     function view($id = null) {

	    $this->set('Problemliststatuses', array(1=>'New', 2=>'In Process', 3=>'Ready to send trolley',4=>'At Internet Archive', 5=>'Received from Internet Archive', 6=>'Returned Reshelved and Closed'));


	    $this->Problemlist->id = $id;
	    $this->data = $this->Problemlist->read();
        $this->set('myproblemlist',$this->Problemlist->read());

        // count all the problem list lines for this list so it can be printed in the report

		$problemlistlinecount = $this->Problemlistline->findcount('`problemlist_id` = ' . $id);
		$this->set('myproblemlistlinecount', $problemlistlinecount);

        // now read all the problem list lines for this problem list and set in a var for display purposes.

		$problemlistline = $this->Problemlistline->findall('`problemlist_id` = ' . $id);
		$this->set('myproblemlistlines', $problemlistline);

    }





     function viewdirectfrompub($id = null) {
        // this is to try and pull up a record by catkey from titles to be scanned page or tell the user if there is not one that matches.


        $publetters = $this->data['Problemlist']['name'];

        $this->set('Agreementstatuses', array(1=>'N/K',2=>'Failed',3=>'Succeeded'));
        $this->set('Problemliststatuses', array(1=>'N/K',2=>'Traced',3=>'Untraceable'));
        $this->set('Watchfilestatuses', array(1=>'N/K',2=>'OK',3=>'Not OK'));


        // if there is no problemlist supplied via the form, message...
        if (!$publetters)
					{
						$this->flash('You must enter problemlist text to search for.',"/problemlists/addtitle/");
						exit;
			}


        $numberproblemlists = $this->Problemlist->findCount('`name` like \'%' . rtrim($publetters) . '%\'');
        //$problemlists = $this->Problemlist->findall('`name` like \'%' . rtrim($publetters) . '%\'');


        if ($numberproblemlists ==0)
        {
            $this->flash('Problemlist does not exist. Create a new one and then link.',"/problemlists/addtitle/");
        }
        elseif ($numberproblemlists ==1)
        {
			 $problemlist = $this->Problemlist->find('`name` like \'%' . rtrim($publetters) . '%\'');
			 $id = $problemlist['Problemlist']['id'];
             $this->Problemlist->id = $id;
			 $this->set('myproblemlist', $this->Problemlist->read());
        }

        else # must be many
        {

			 $problemlist = $this->Problemlist->findall('`name` like \'%' . rtrim($publetters) . '%\'');
			 $this->set('myproblemlist', $problemlist);

        }



    }



     function viewdirect($id = null) {
        // this is to try and pull up a record by catkey from titles to be scanned page or tell the user if there is not one that matches.


        $publetters = $this->data['Problemlist']['name'];
        $iprid= $this->data['Iprtitle']['title_id'];
        $this->set('IPRidentifier', $iprid);


        $this->set('Agreementstatuses', array(1=>'N/K',2=>'Failed',3=>'Succeeded'));
        $this->set('Problemliststatuses', array(1=>'N/K',2=>'Traced',3=>'Untraceable'));
        $this->set('Watchfilestatuses', array(1=>'N/K',2=>'OK',3=>'Not OK'));


        // if there is no problemlist supplied via the form, message...
        if (!$publetters)
					{
						$this->flash('You must enter problemlist text to search for.',"/iprtitles/edit/" . $iprid);
						exit;
			}


        $numberproblemlists = $this->Problemlist->findCount('`name` like \'%' . rtrim($publetters) . '%\'');
        //$problemlists = $this->Problemlist->findall('`name` like \'%' . rtrim($publetters) . '%\'');


        if ($numberproblemlists ==0)
        {
            $this->flash('Problemlist does not exist. Create a new one and then link.',"/problemlists/addtitle/");
        }
        elseif ($numberproblemlists ==1)
        {
			 $problemlist = $this->Problemlist->find('`name` like \'%' . rtrim($publetters) . '%\'');
			 $pubid = $problemlist['Problemlist']['id'];
             $this->Problemlist->id = $pubid;
			 $this->set('myproblemlist', $this->Problemlist->read());
        }

        else # must be many
        {

			 $problemlist = $this->Problemlist->findall('`name` like \'%' . rtrim($publetters) . '%\'');
			 $this->set('myproblemlist', $problemlist);

        }



    }



     function linkdirect($id = null) {
        // this is to try and pull up a record by catkey from titles to be scanned page or tell the user if there is not one that matches.


        // pull this from the parameter
        $catkey = $id;

        // if there is no catkey supplied via the form, message...
        if (!$catkey)
					{
						$this->flash('No catkey in the record, so nothing to look up! Must be unlinked record.',"/titles/addtitle");
			}

	    // find the bib_id associated with the catkey so we can pull the 856 for each of its problemlists

        $myid = $this->Problemlist->find('`035` like \'%a' . rtrim($catkey) .'\' ');
        # get just the bib_id field.
        $id = $myid['Problemlist']['id'];
		$this->Problemlist->id = $id;
		$this->set('myproblemlist', $this->Problemlist->read());


    }





     function edit($id = null)
	 {


		$this->set('Problemliststatuses', array(1=>'New', 2=>'In Process', 3=>'Ready to send trolley',4=>'At Internet Archive', 5=>'Received from Internet Archive', 6=>'Returned Reshelved and Closed'));
        $this->set('myProblemlistlinestatuses', array(1=>'OK',2=>'Missing',3=>'Lost',4=>'To Conservation'));
        $this->set('myIarejectreasonstatuses', array(1=>'N/K',2=>'Uncut pages',3=>'Too big',4=>'Too small'));
        $this->set('Problemstatuses', array(1=>'N/K',2=>'Uncut pages',3=>'Too big',4=>'Too small',5=>'On loan'));

    	if (empty($this->data))
		{
			$this->Problemlist->id = $id;
			$this->data = $this->Problemlist->read();
            $this->set('myproblemlist',$this->Problemlist->read());
            // now read all the problem list lines for this problem list and set in a var for display purposes.

		    $problemlistline = $this->Problemlistline->findall('`problemlist_id` = ' . $id);
		    $this->set('myproblemlistlines', $problemlistline);

		}
		else
		{
		// check that the status is not NEW in the database. If it is not, do not allow the user to reset it to NEW
        $id = $this->data['Problemlist']['id'];
		$thisproblemlist = $this->Problemlist->find('`id` = ' . $id);
		$currentpkstatus = $thisproblemlist['Problemlist']['problem_list_status'];
			if ($currentpkstatus != 1) // ie not NEW
			{
			  if ($this->data['Problemlist']['problem_list_status'] == 1) // user is trying to reset to NEW
			  {
				  $this->flash('You cannot reset the status to NEW.',"/problemlists/edit/" . $id);
				  exit();
			  }
			}

			// if it gets this far, try to save.
			if ($this->Problemlist->save($this->data['Problemlist']))
				{
					$this->flash('Your Problem list has been updated.',"/problemlists/index");
				}

		}
	}

//**********************
     function oldeditproblem($id = null)
	 {

        // set some status options
		$this->set('Problemliststatuses', array(1=>'New', 2=>'In Process', 3=>'Ready to send trolley',4=>'At Internet Archive', 5=>'Received from Internet Archive', 6=>'Returned Reshelved and Closed'));
        $this->set('myProblemlistlinestatuses', array(1=>'OK',2=>'Missing',3=>'Lost',4=>'To Conservation'));
        $this->set('myIarejectreasonstatuses', array(1=>'N/K',2=>'Uncut pages',3=>'Too big',4=>'Too small'));


    	if (empty($this->data))
		{

		    // problem_list id for a problemlist always to be hardwired 999999
		    $this->Problemlist->id = 999999;
			$this->data = $this->Problemlist->read();
            $this->set('myproblemlist',$this->Problemlist->read());
            // now read all the problem list lines for problem list 999999 and set in a var for display purposes.

		    $problemlistline = $this->Problemlistline->findall('`problemlist_id` = ' . $id);
		    $this->set('myproblemlistlines', $problemlistline);

		}
		else
		{
		//$id = $this->data['Problemlist']['id'];
		$thisproblemlist = $this->Problemlist->find('`id` = ' . $id);

		// try to save.
		if ($this->Problemlist->save($this->data['Problemlist']))
			{
				$this->flash('Your Problem list has been updated.',"/problemlists/index");
			}

		}
	}

//**********************

     function editproblem($id = null)
	 {

        // set some status options
		$this->set('Problemliststatuses', array(1=>'New', 2=>'In Process', 3=>'Ready to send trolley',4=>'At Internet Archive', 5=>'Received from Internet Archive', 6=>'Returned Reshelved and Closed'));
        $this->set('myProblemlistlinestatuses', array(1=>'OK',2=>'Missing',3=>'Lost',4=>'To Conservation'));
        $this->set('myProblemstatuses', array(1=>'N/K',2=>'Uncut pages',3=>'Too big',4=>'Too small',5=>'On loan'));

        /* Setup filters */
            $this->Filter->init($this, 'Problemlist');

            $this->Filter->setFilter(aa('id',
            'Record ID'), NULL, a('=','!=', '>', '<') );

            $this->Filter->setFilter(aa('[Title][title]', 'Title'), NULL, a(
            '<','>','='));


            $this->Filter->setFilter(aa('problem_status',
            'Problem Status'), NULL, a( '~','^','!~', '='));


           $this->Filter->filter($f, $cond); $this->set('filters', $f);

            /* Setup pagination */
            $this->Pagination->controller = &$this;
            $this->Pagination->show = 30;


		    // problem_list id for a problemlist always to be hardwired 999999
		    $this->Problemlist->id = 999999;
		    $this->data = $this->Problemlist->read();

			$this->Pagination->init(
				$cond, 'Problemlist', NULL, array('id', 'title', 'problem_status'), 0
            );


            $this->set('myproblemlist', $this->Problemlist->findAll(
                $cond,'',$this->Pagination->order, $this->Pagination->show,
                $this->Pagination->page
            ));

            // now read all the problem list lines for problem list 999999 and set in a var for display purposes.

		    $problemlistline = $this->Problemlistline->findall('`problemlist_id` = ' . $id);
		    $this->set('myproblemlistlines', $problemlistline);


     }


  	function delete($id)
	{


        // if the problemlist has no lines associated with it, we can delete it.
		$numberproblemlistlines = $this->Problemlistline->findCount('`problemlist_id` = ' . $id);
        if ($numberproblemlistlines == 0) // no lines so we can just delete
        {
            $this->Problemlist->del($id);
            $this->flash('The problemlist with id: '.$id.' has been deleted. (Click to continue)', '/problemlists/');
        }
        else
        {
            $this->flash('The problemlist with id: '.$id.' cannot be deleted as it has lines attached. (Click to continue)', '/problemlists/');
        }
  	}

//**********************

        function index() {


        $this->set('Problemliststatuses', array(1=>'New', 2=>'In Process', 3=>'Ready to send trolley',4=>'At Internet Archive', 5=>'Received from Internet Archive', 6=>'Returned Reshelved and Closed'));

        /* Setup filters */
            $this->Filter->init($this, 'Problemlist');

            $this->Filter->setFilter(aa('id',
            'Record ID'), NULL, a('=','!=', '>', '<') );

            $this->Filter->setFilter(aa('create_date', 'Date'), NULL, a(
            '<','>','='));


            $this->Filter->setFilter(aa('problem_list_status',
            'Status (1 - 5)'), NULL, a( '~','^','!~', '='));

           $this->Filter->setFilter(aa('library',
            'Library'), NULL, a( '~','^','!~', '='));




            $this->Filter->filter($f, $cond); $this->set('filters', $f);





            /* Setup pagination */
            $this->Pagination->controller = &$this;
            $this->Pagination->show = 30;

            $this->Pagination->init(
                $cond, 'Problemlist', NULL, array('id', 'create_date', 'problem_list_status'), 0
            );
                            //'depreciated is NULL'
            $this->set('Problemlists', $this->Problemlist->findAll(
                $cond,'','create_date desc', $this->Pagination->show,
                $this->Pagination->page
            ));


            //var_dump($this->Bib->problemlists);
        }


//**********************




}

?>