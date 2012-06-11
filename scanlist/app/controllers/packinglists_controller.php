<?php
class PackinglistsController extends AppController
{
  var $name = 'Packinglists';
    var $components = array ('Pagination', 'Filter', 'othAuth'); // Added
    var $helpers = array('Html','Pagination', 'Filter', 'Matchlinker'); // Added
    //var $scaffold;
    var $uses = array('Packinglist','Bid','Status','Title', 'Packinglistline');



    function add($id = null) // auto add linked record with default values and load the edit form with the data
        {


        // defaults are set in the SQL table itself except for user id

        // try and save

			if ($this->Packinglist->save($this->data['Packinglist']))
			{

				// load the form for edit with the new id
				$id = $this->Packinglist->id;
                $this->redirect("/packinglists/edit/" . $id);
		 	}
		 	else // save was a bloomin' failure
		 	{
		 	    $this->flash('Your packing list could not be created. Report to admin.',"/packinglists/index");
		 	}

        }

    function addproblem($id = null) // auto add linked record with default values and load the edit form with the data
        {


        // defaults are set in the SQL table itself except for user id

        // try and save

			if ($this->Packinglist->save($this->data['Packinglist']))
			{

				// load the form for edit with the new id
				$id = $this->Packinglist->id;
                $this->redirect("/packinglists/editproblem/" . $id);
		 	}
		 	else // save was a bloomin' failure
		 	{
		 	    $this->flash('Your problem list could not be created. Report to admin.',"/packinglists/index");
		 	}

        }


     function view($id = null) {

	    $this->set('Packingliststatuses', array(1=>'New', 2=>'In Process', 3=>'Ready to send trolley',4=>'At Internet Archive', 5=>'Scanned by Internet Archive', 6=>'Received from Internet Archive', 7=>'Returned Reshelved and Closed'));


	    $this->Packinglist->id = $id;
	    $this->data = $this->Packinglist->read();
        $this->set('mypackinglist',$this->Packinglist->read());

        // count all the packing list lines for this list so it can be printed in the report

		$packinglistlinecount = $this->Packinglistline->findcount('`packinglist_id` = ' . $id);
		$this->set('mypackinglistlinecount', $packinglistlinecount);

        // now read all the packing list lines for this packing list and set in a var for display purposes.

		$packinglistline = $this->Packinglistline->findall('`packinglist_id` = ' . $id);
		$this->set('mypackinglistlines', $packinglistline);

    }





     function viewdirectfrompub($id = null) {
        // this is to try and pull up a record by catkey from titles to be scanned page or tell the user if there is not one that matches.


        $publetters = $this->data['Packinglist']['name'];

        $this->set('Agreementstatuses', array(1=>'N/K',2=>'Failed',3=>'Succeeded'));
        $this->set('Packingliststatuses', array(1=>'N/K',2=>'Traced',3=>'Untraceable'));
        $this->set('Watchfilestatuses', array(1=>'N/K',2=>'OK',3=>'Not OK'));


        // if there is no packinglist supplied via the form, message...
        if (!$publetters)
					{
						$this->flash('You must enter packinglist text to search for.',"/packinglists/addtitle/");
						exit;
			}


        $numberpackinglists = $this->Packinglist->findCount('`name` like \'%' . rtrim($publetters) . '%\'');
        //$packinglists = $this->Packinglist->findall('`name` like \'%' . rtrim($publetters) . '%\'');


        if ($numberpackinglists ==0)
        {
            $this->flash('Packinglist does not exist. Create a new one and then link.',"/packinglists/addtitle/");
        }
        elseif ($numberpackinglists ==1)
        {
			 $packinglist = $this->Packinglist->find('`name` like \'%' . rtrim($publetters) . '%\'');
			 $id = $packinglist['Packinglist']['id'];
             $this->Packinglist->id = $id;
			 $this->set('mypackinglist', $this->Packinglist->read());
        }

        else # must be many
        {

			 $packinglist = $this->Packinglist->findall('`name` like \'%' . rtrim($publetters) . '%\'');
			 $this->set('mypackinglist', $packinglist);

        }



    }



     function viewdirect($id = null) {
        // this is to try and pull up a record by catkey from titles to be scanned page or tell the user if there is not one that matches.


        $publetters = $this->data['Packinglist']['name'];
        $iprid= $this->data['Iprtitle']['title_id'];
        $this->set('IPRidentifier', $iprid);


        $this->set('Agreementstatuses', array(1=>'N/K',2=>'Failed',3=>'Succeeded'));
        $this->set('Packingliststatuses', array(1=>'N/K',2=>'Traced',3=>'Untraceable'));
        $this->set('Watchfilestatuses', array(1=>'N/K',2=>'OK',3=>'Not OK'));


        // if there is no packinglist supplied via the form, message...
        if (!$publetters)
					{
						$this->flash('You must enter packinglist text to search for.',"/iprtitles/edit/" . $iprid);
						exit;
			}


        $numberpackinglists = $this->Packinglist->findCount('`name` like \'%' . rtrim($publetters) . '%\'');
        //$packinglists = $this->Packinglist->findall('`name` like \'%' . rtrim($publetters) . '%\'');


        if ($numberpackinglists ==0)
        {
            $this->flash('Packinglist does not exist. Create a new one and then link.',"/packinglists/addtitle/");
        }
        elseif ($numberpackinglists ==1)
        {
			 $packinglist = $this->Packinglist->find('`name` like \'%' . rtrim($publetters) . '%\'');
			 $pubid = $packinglist['Packinglist']['id'];
             $this->Packinglist->id = $pubid;
			 $this->set('mypackinglist', $this->Packinglist->read());
        }

        else # must be many
        {

			 $packinglist = $this->Packinglist->findall('`name` like \'%' . rtrim($publetters) . '%\'');
			 $this->set('mypackinglist', $packinglist);

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

	    // find the bib_id associated with the catkey so we can pull the 856 for each of its packinglists

        $myid = $this->Packinglist->find('`035` like \'%a' . rtrim($catkey) .'\' ');
        # get just the bib_id field.
        $id = $myid['Packinglist']['id'];
		$this->Packinglist->id = $id;
		$this->set('mypackinglist', $this->Packinglist->read());


    }





     function edit($id = null)
	 {


		$this->set('Packingliststatuses', array(1=>'New', 2=>'In Process', 3=>'Ready to send trolley',4=>'At Internet Archive', 5=>'Scanned by Internet Archive', 6=>'Received from Internet Archive', 7=>'Returned Reshelved and Closed'));
        $this->set('myPackinglistlinestatuses', array(1=>'OK',2=>'Missing',3=>'Lost',4=>'To Conservation'));
        $this->set('myIarejectreasonstatuses', array(1=>'N/K',2=>'Uncut pages',3=>'Too big',4=>'Too small'));


    	if (empty($this->data))
		{
			$this->Packinglist->id = $id;
			$this->data = $this->Packinglist->read();
            $this->set('mypackinglist',$this->Packinglist->read());
            // now read all the packing list lines for this packing list and set in a var for display purposes.

		    $packinglistline = $this->Packinglistline->findall('`packinglist_id` = ' . $id);
		    $this->set('mypackinglistlines', $packinglistline);

		}
		else
		{
		// check that the status is not NEW in the database. If it is not, do not allow the user to reset it to NEW
        $id = $this->data['Packinglist']['id'];
		$thispackinglist = $this->Packinglist->find('`id` = ' . $id);
		$currentpkstatus = $thispackinglist['Packinglist']['packing_list_status'];
			if ($currentpkstatus != 1) // ie not NEW
			{
			  if ($this->data['Packinglist']['packing_list_status'] == 1) // user is trying to reset to NEW
			  {
				  $this->flash('You cannot reset the status to NEW.',"/packinglists/edit/" . $id);
				  exit();
			  }
			}

			// if it gets this far, try to save.
			if ($this->Packinglist->save($this->data['Packinglist']))
				{
					$this->flash('Your Packing list has been updated.',"/packinglists/index");
				}

		}
	}

//**********************
     function oldeditproblem($id = null)
	 {

        // set some status options
		$this->set('Packingliststatuses', array(1=>'New', 2=>'In Process', 3=>'Ready to send trolley',4=>'At Internet Archive', 5=>'Scanned by Internet Archive', 6=>'Received from Internet Archive', 7=>'Returned Reshelved and Closed'));
        $this->set('myPackinglistlinestatuses', array(1=>'OK',2=>'Missing',3=>'Lost',4=>'To Conservation'));
        $this->set('myIarejectreasonstatuses', array(1=>'N/K',2=>'Uncut pages',3=>'Too big',4=>'Too small'));


    	if (empty($this->data))
		{

		    // packing_list id for a problemlist always to be hardwired 999999
		    $this->Packinglist->id = 999999;
			$this->data = $this->Packinglist->read();
            $this->set('mypackinglist',$this->Packinglist->read());
            // now read all the problem list lines for packing list 999999 and set in a var for display purposes.

		    $packinglistline = $this->Packinglistline->findall('`packinglist_id` = ' . $id);
		    $this->set('mypackinglistlines', $packinglistline);

		}
		else
		{
		//$id = $this->data['Packinglist']['id'];
		$thispackinglist = $this->Packinglist->find('`id` = ' . $id);

		// try to save.
		if ($this->Packinglist->save($this->data['Packinglist']))
			{
				$this->flash('Your Problem list has been updated.',"/packinglists/index");
			}

		}
	}

//**********************

     function editproblem($id = null)
	 {

        // set some status options
		$this->set('Packingliststatuses', array(1=>'New', 2=>'In Process', 3=>'Ready to send trolley',4=>'At Internet Archive', 5=>'Scanned by Internet Archive', 6=>'Received from Internet Archive', 7=>'Returned Reshelved and Closed'));
        $this->set('myPackinglistlinestatuses', array(1=>'OK',2=>'Missing',3=>'Lost',4=>'To Conservation'));
        $this->set('myProblemstatuses', array(1=>'N/K',2=>'Uncut pages',3=>'Too big',4=>'Too small',5=>'On loan'));

        /* Setup filters */
            $this->Filter->init($this, 'Packinglist');

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


		    // packing_list id for a problemlist always to be hardwired 999999
		    $this->Packinglist->id = 999999;
		    $this->data = $this->Packinglist->read();

			$this->Pagination->init(
				$cond, 'Packinglist', NULL, array('id', 'title', 'problem_status'), 0
            );


            $this->set('mypackinglist', $this->Packinglist->findAll(
                $cond,'',$this->Pagination->order, $this->Pagination->show,
                $this->Pagination->page
            ));

            // now read all the problem list lines for packing list 999999 and set in a var for display purposes.

		    $packinglistline = $this->Packinglistline->findall('`packinglist_id` = ' . $id);
		    $this->set('mypackinglistlines', $packinglistline);


     }


  	function delete($id)
	{


        // if the packinglist has no lines associated with it, we can delete it.
		$numberpackinglistlines = $this->Packinglistline->findCount('`packinglist_id` = ' . $id);
        if ($numberpackinglistlines == 0) // no lines so we can just delete
        {
            $this->Packinglist->del($id);
            $this->flash('The packinglist with id: '.$id.' has been deleted. (Click to continue)', '/packinglists/');
        }
        else
        {
            $this->flash('The packinglist with id: '.$id.' cannot be deleted as it has lines attached. (Click to continue)', '/packinglists/');
        }
  	}

//**********************

        function index() {


        $this->set('Packingliststatuses', array(1=>'New', 2=>'In Process', 3=>'Ready to send trolley',4=>'At Internet Archive', 5=>'Scanned by Internet Archive', 6=>'Received from Internet Archive', 7=>'Returned Reshelved and Closed'));

        /* Setup filters */
            $this->Filter->init($this, 'Packinglist');

            $this->Filter->setFilter(aa('id',
            'Record ID'), NULL, a('=','!=', '>', '<') );

            $this->Filter->setFilter(aa('create_date', 'Date'), NULL, a(
            '<','>','='));


            $this->Filter->setFilter(aa('packing_list_status',
            'Status (1 - 5)'), NULL, a( '~','^','!~', '='));

           $this->Filter->setFilter(aa('library',
            'Library'), NULL, a( '~','^','!~', '='));




            $this->Filter->filter($f, $cond); $this->set('filters', $f);





            /* Setup pagination */
            $this->Pagination->controller = &$this;
            $this->Pagination->show = 30;

            $this->Pagination->init(
                $cond, 'Packinglist', NULL, array('id', 'create_date', 'packing_list_status'), 0
            );
                            //'depreciated is NULL'
            $this->set('Packinglists', $this->Packinglist->findAll(
                $cond,'','create_date desc', $this->Pagination->show,
                $this->Pagination->page
            ));


            //var_dump($this->Bib->packinglists);
        }


//**********************




}

?>