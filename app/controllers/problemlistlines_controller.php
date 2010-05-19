<?php
class ProblemlistlinesController extends AppController
{
  var $name = 'Problemlistlines';
    var $components = array ('Pagination', 'Filter', 'othAuth'); // Added
    var $helpers = array('Html','Pagination', 'Filter', 'Matchlinker'); // Added
    //var $scaffold;
    var $uses = array('Problemlistline','Bid','Problemlistline','Status','Title');




     function add($id = null) {

        // arrays for select options

        $this->set('Problemlistlinestatuses', array(1=>'OK',2=>'Missing',3=>'Lost',4=>'To Conservation'));
        $this->set('Iarejectreasonstatuses', array(1=>'N/K',2=>'Uncut pages',3=>'Too big',4=>'Too small'));
        $this->set('Problemstatuses', array(1=>'N/K',2=>'Uncut pages',3=>'Too big',4=>'Too small',5=>'On loan',6=>'Foldouts',7=>'At conservation',8=>'Too Fragile', 9=>'Missing'));

        if ($id) {
           // use sql to get the variables required from the id
           $problemlistdata = $this->Problemlistline->find('`problemlist`.`id` = ' . $id);
		   // set an incremented line number too.

		   $thelinenumber = $problemlistdata['Problemlistline']['linenumber'];
		   $this->set('thelinenumbervalue', ($thelinenumber + 1));

        }


		// get various vars for a new add
		$thetitlename = $this->data['Title']['title'];
		$this->set('thetitlenamevalue', $thetitlename);

		$thepressmarkname = $this->data['Title']['pressmark'];
		$this->set('thepressmarknamevalue', $thepressmarkname );

		$thecatkey = $this->data['Title']['catkey'];
		$this->set('thecatkeyvalue', $thecatkey );

		$thetitleid = $this->data['Title']['id'];
		$this->set('thetitleidvalue', $thetitleid);


		$theproblemlistid = $this->data['Problemlist']['id'];
		$this->set('theproblemlistidvalue', $theproblemlistid );

		// we need to get the problemline number and store this too. use sql: select count from problemlistline where packlinglistid = id and add "1"

		$thelinenumber = $this->Problemlistline->findCount('`problemlist_id` = ' . $theproblemlistid);
		$this->set('thelinenumbervalue', ($thelinenumber + 1));


			if (!empty($this->data['Problemlistline']))
			{

				if ($this->Problemlistline->save($this->data['Problemlistline'])) {

					//  if it saves, we need to give the user feedback
					//$this->set('addrecordconfirmmessage', "Line " . $this->data['Problemlistline']['linenumber'] . " was saved. Amend the form below to create another line for the same title." );

					$this->flash('Your problem has been added.',"/problemlists");


					// to speed things up, load a new form with the duplicated variables from the previously added record
				}
				else
				{
					$this->validateErrors($this->Problemlistline);
					//And render the edit view code
					$this->render();
				}


			}

    }

    function duplicate() {

        // arrays for select options
        $this->set('Agreementstatuses', array(1=>'N/K',2=>'Failed',3=>'Succeeded'));
        $this->set('Problemlistlinestatuses', array(1=>'N/K',2=>'Traced',3=>'Untraceable'));
        $this->set('Watchfilestatuses', array(1=>'N/K',2=>'OK',3=>'Not OK'));

      	$thetitlename = $this->data['Title']['title'];
    	$this->set('thetitlenamevalue', $thetitlename );

      	$thepressmarkname = $this->data['Title']['pressmark'];
    	$this->set('thepressmarknamevalue', $thepressmarkname );

      	$thecatkey = $this->data['Title']['catkey'];
    	$this->set('thecatkeyvalue', $thecatkey );

        $theproblemlistid = $this->data['Problemlist']['id'];
        //var_dump($this->data);
        $this->set('theproblemlistidvalue', $theproblemlistid );

        //var_dump($this->data['Problemlistline']);

        if (!empty($this->data['Problemlistline']))
        {

            if ($this->Problemlistline->save($this->data['Problemlistline'])) {
                // to speed things up, load a form with the duplicated variables from this form
                $this->flash('Your problemlistline info has been saved.','/problemlistlines/duplicate');
            }
			else
			{
				$this->validateErrors($this->Problemlistline);
				//And render the edit view code
				$this->render();
			}


        }

    }

     function view($id = null) {

        $this->set('Agreementstatuses', array(1=>'N/K',2=>'Failed',3=>'Succeeded'));
		$this->set('Problemlistlinestatuses', array(1=>'OK',2=>'Missing',3=>'Lost',4=>'To Conservation'));
		$this->set('Iarejectreasonstatuses', array(1=>'N/K',2=>'Uncut pages',3=>'Too big',4=>'Too small'));
		$this->set('Problemstatuses', array(1=>'N/K',2=>'Uncut pages',3=>'Too big',4=>'Too small',5=>'On loan',6=>'Foldouts',7=>'At conservation',8=>'Too Fragile', 9=>'Missing'));

        $this->set('Watchfilestatuses', array(1=>'N/K',2=>'OK',3=>'Not OK'));

        $this->Problemlistline->id = $id;
        $this->set('myproblemlistline', $this->Problemlistline->read());

    }


	function editlimited($id = null)
	{

      if ($this->Problemlistline->save($this->data['Problemlistline']))
      {
          $this->flash('Your reason was updated.',"/problemlists/edit/" . $this->data['Problemlistline']['problemlist_id']);
      }
      else
      {
          $this->flash('Unable to change the reason status. Please contact administrator.',"/problemlists/edit/" . $this->data['Problemlistline']['problemlist_id']);
      }


	}



     function viewdirectfrompub($id = null) {
        // this is to try and pull up a record by catkey from titles to be scanned page or tell the user if there is not one that matches.


        $titleletters = $this->data['Problemlistline']['name'];

        //$this->set('Agreementstatuses', array(1=>'N/K',2=>'Failed',3=>'Succeeded'));
        //$this->set('Problemlistlinestatuses', array(1=>'N/K',2=>'Traced',3=>'Untraceable'));
        //$this->set('Watchfilestatuses', array(1=>'N/K',2=>'OK',3=>'Not OK'));


        // if there is no problemlistline supplied via the form, message...
        if (!$titleletters)
					{
						$this->flash('You must enter a title to search for.',"/problemlistlines/add/");
						exit;
			}


        $numberproblemlistlines = $this->Problemlistline->findCount('`name` like \'%' . rtrim($titleletters ) . '%\'');



        if ($numberproblemlistlines ==0)
        {
            $this->flash('Requested title is not available or does not exist. Try again.',"/problemlistlines/add/");
        }
        elseif ($numberproblemlistlines ==1)
        {
			 $problemlistline = $this->Problemlistline->find('`name` like \'%' . rtrim($titleletters) . '%\'');
			 $id = $problemlistline['Problemlistline']['id'];
             $this->Problemlistline->id = $id;
			 $this->set('myproblemlistline', $this->Problemlistline->read());
        }

        else # must be many
        {

			 $problemlistline = $this->Problemlistline->findall('`name` like \'%' . rtrim($titleletters) . '%\'');
			 $this->set('myproblemlistline', $problemlistline);

        }



    }



     function viewdirect($id = null) {
        // this is to try and pull up a record by catkey from titles to be scanned page or tell the user if there is not one that matches.


        $publetters = $this->data['Problemlistline']['name'];
        $iprid= $this->data['Iprtitle']['title_id'];
        $this->set('IPRidentifier', $iprid);


        $this->set('Agreementstatuses', array(1=>'N/K',2=>'Failed',3=>'Succeeded'));
        $this->set('Problemlistlinestatuses', array(1=>'N/K',2=>'Traced',3=>'Untraceable'));
        $this->set('Watchfilestatuses', array(1=>'N/K',2=>'OK',3=>'Not OK'));


        // if there is no problemlistline supplied via the form, message...
        if (!$publetters)
					{
						$this->flash('You must enter problemlistline text to search for.',"/iprtitles/edit/" . $iprid);
						exit;
			}


        $numberproblemlistlines = $this->Problemlistline->findCount('`name` like \'%' . rtrim($publetters) . '%\'');
        //$problemlistlines = $this->Problemlistline->findall('`name` like \'%' . rtrim($publetters) . '%\'');


        if ($numberproblemlistlines ==0)
        {
            $this->flash('Problemlistline does not exist. Create a new one and then link.',"/problemlistlines/addtitle/");
        }
        elseif ($numberproblemlistlines ==1)
        {
			 $problemlistline = $this->Problemlistline->find('`name` like \'%' . rtrim($publetters) . '%\'');
			 $pubid = $problemlistline['Problemlistline']['id'];
             $this->Problemlistline->id = $pubid;
			 $this->set('myproblemlistline', $this->Problemlistline->read());
        }

        else # must be many
        {

			 $problemlistline = $this->Problemlistline->findall('`name` like \'%' . rtrim($publetters) . '%\'');
			 $this->set('myproblemlistline', $problemlistline);

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

	    // find the bib_id associated with the catkey so we can pull the 856 for each of its problemlistlines

        $myid = $this->Problemlistline->find('`035` like \'%a' . rtrim($catkey) .'\' ');
        # get just the bib_id field.
        $id = $myid['Problemlistline']['id'];
		$this->Problemlistline->id = $id;
		$this->set('myproblemlistline', $this->Problemlistline->read());


    }





     function edit($id = null)
	{



        $this->set('Problemlistlinestatuses', array(1=>'OK',2=>'Missing',3=>'Lost',4=>'To Conservation'));
        $this->set('Iarejectreasonstatuses', array(1=>'N/K',2=>'Uncut pages',3=>'Too big',4=>'Too small'));
        $this->set('Problemstatuses', array(1=>'N/K',2=>'Uncut pages',3=>'Too big',4=>'Too small',5=>'On loan',6=>'Foldouts',7=>'At conservation',8=>'Too Fragile', 9=>'Missing'));

		if (empty($this->data))
		{
			$this->Problemlistline->id = $id;
			$this->data = $this->Problemlistline->read();
                        $this->set('myproblemlistline',$this->Problemlistline->read());
		}
		else
		{
		    // first try to save the updated record
			if ($this->Problemlistline->save($this->data['Problemlistline']))
			{

			    $this->flash('Your line has been updated.',"/problemlists/edit/" . $this->data['Problemlistline']['problemlist_id']);

			}
			// if couldn't update the record, show an error
			else
			{
			    $this->flash('There was a problem saving the record. Please contact your administrator.',"/problemlists/index");
			}
		}
	}




  	function delete($id)
	{
	    // before deleting, we need to get the problemlist id so we can return them to the list after deletion
        $mypacklinglistinfo = $this->Problemlistline->find('`Problemlistline`.`id` = ' . $id);
        # get just the problemlist id field.
        $mypacklinglistid = $mypacklinglistinfo['Problemlistline']['problemlist_id'];

        $this->Problemlistline->del($id);
        $this->flash('The problem list line with id: '.$id.' has been deleted. (Click to continue)', '/Problemlists/edit/' . $mypacklinglistid);
  	}

//**********************

        function index() {
                // Totally ripped from http://mho.ath.cx/~cake/exams/filter/
            // BS recursive = 1 seems to indicate it gets related records by foreign key too
            $this->Problemlistline->recursive = 2;
            //var_dump($this->Problemlistline->Field('035'));
           // EC Amendment Condition to filter depreciated material. has been added to filter component (components/filter.php)

        $this->set('Problemlistlinestatuses', array(1=>'N/K',2=>'Traced',3=>'Untraceable'));

        /* Setup filters */
            $this->Filter->init($this, 'Problemlistline');

            $this->Filter->setFilter(aa('id',
            'Record ID'), NULL, a('=','!=', '>', '<') );

            $this->Filter->setFilter(aa('name', 'Name'), NULL, a(
            '~','^','!~', '='));


            $this->Filter->setFilter(aa('problemlistline_status',
            'Status'), NULL, a( '~','^','!~', '='));





            $this->Filter->filter($f, $cond); $this->set('filters', $f);





            /* Setup pagination */
            $this->Pagination->controller = &$this;
            $this->Pagination->show = 30;

            $this->Pagination->init(
                $cond, 'Problemlistline', NULL, array('id', '035', 'place'), 0
            );
                            //'depreciated is NULL'
            $this->set('Problemlistlines', $this->Problemlistline->findAll(
                $cond,'',$this->Pagination->order, $this->Pagination->show,
                $this->Pagination->page
            ));


            //var_dump($this->Bib->problemlistlines);
        }


//**********************




}

?>