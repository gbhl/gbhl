<?php
class PackinglistlinesController extends AppController
{
  var $name = 'Packinglistlines';
    var $components = array ('Pagination', 'Filter', 'othAuth'); // Added
    var $helpers = array('Html','Pagination', 'Filter', 'Matchlinker'); // Added
    //var $scaffold;
    var $uses = array('Packinglistline','Bid','Packinglistline','Status','Title');




     function add($id = null) {



        // arrays for select options

        $this->set('Packinglistlinestatuses', array(1=>'OK',2=>'Missing',3=>'Lost',4=>'To Conservation'));
        $this->set('Iarejectreasonstatuses', array(1=>'N/K',2=>'Uncut pages',3=>'Too wide/high',4=>'Too small',5=>'Too thick',6=>'Foldouts',7=>'Too tight gutter',8=>'Too fragile',9=>'Catalogue error'));
        $this->set('Problemstatuses', array(1=>'N/K',2=>'Uncut pages',3=>'Too big',4=>'Too small',5=>'On loan'));


        // if there is an id we are dealing with a duplicate record from the index page
        if ($id) {
           // use sql to get the variables required from the id
           $packinglistdata = $this->Packinglistline->find('`packinglist`.`id` = ' . $id);

           // get and set relevant fields
		   $thechronstartdate = $packinglistdata['Packinglist']['chronology_start'];
		   $this->set('thechronstartdatevalue', $thechronstartdate );
		   $thechronenddate = $packinglistdata['Packinglist']['chronology_end'];
		   $this->set('thechronenddatevalue', $thechronenddate );
		   $theenumstartdate = $packinglistdata['Packinglist']['enum_start'];
		   $this->set('theenumstartdatevalue', $theenumstartdate );
		   $theenumenddate = $packinglistdata['Packinglist']['enum_end'];
		   $this->set('theenumenddatevalue', $theenumenddate );
		   $theseries = $packinglistdata['Packinglist']['series'];
		   $this->set('theseriesvalue', $theseries );
		   $thelinestatus = $packinglistdata['Packinglist']['line_status'];
		   $this->set('thelinestatusvalue', $thelinestatus );
		   $theiarejectreason = $packinglistdata['Packinglist']['ia_reject_reason'];
		   $this->set('theiarejectreasonvalue', $theiarejectreason );

		   // set an incremented line number too.

		   $thelinenumber = $packinglistdata['Packinglistline']['linenumber'];
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

		// use sql to get IPR cdm_date_checked value using title id and save this to a variable.

		//$myiprtitleinfo = $this->Title->0->Iprtitle->find('iprtitles.title_id = ' . $thetitleid);

       // BS 20080410. temp fix to BUG - hardwired this to all titles approved. Not possible to access the field in
       //the Iprtitle model from this Packinglistline controller so the value is always null and thus
       // no post-1860 items can be added to a packinglist whether copyright clear or not. We need a way
       // to access this value or else have it added to the Title table too when a CDM checks a record and
       // approves it.
       $myiprtitleinfo = 2;


		// $this->set('myiprtitleinfovalue', $myiprtitleinfo['Iprtitle']['cdm_date_checked']);
       $this->set('myiprtitleinfovalue', $myiprtitleinfo);

		$thepackinglistid = $this->data['Packinglist']['id'];
		$this->set('thepackinglistidvalue', $thepackinglistid );

		// we need to get the packingline number and store this too. use sql: select count from packinglistline where packlinglistid = id and add "1"



		$numberpackinglistlines = $this->Packinglistline->findCount('`packinglist_id` = ' . $thepackinglistid);
		$this->set('numberpackinglistlinesvalue', ($numberpackinglistlines + 1));



        if (!empty($this->data['Packinglistline']))
        {

           if ($this->Packinglistline->save($this->data['Packinglistline'])) {

                //  if it saves, we need to give the user feedback
                $this->set('addrecordconfirmmessage', "Line " . $this->data['Packinglistline']['linenumber'] . " was saved. Amend the form below to create another line for the same title." );

                // to speed things up, load a new form with the duplicated variables from the previously added record

                $thechronstartdate = $this->data['Packinglistline']['chronology_start'];
                $this->set('thechronstartdatevalue', $thechronstartdate );
                $thechronenddate = $this->data['Packinglistline']['chronology_end'];
                $this->set('thechronenddatevalue', $thechronenddate );
                $theenumstartdate = $this->data['Packinglistline']['enum_start'];
                $this->set('theenumstartdatevalue', $theenumstartdate );
                $theenumenddate = $this->data['Packinglistline']['enum_end'];
                $this->set('theenumenddatevalue', $theenumenddate );
                $theseries = $this->data['Packinglist']['series'];
                $this->set('theseriesvalue', $theseries );
                $thelinestatus = $this->data['Packinglist']['line_status'];
                $this->set('thelinestatusvalue', $thelinestatus );
                $theiarejectreason = $this->data['Packinglist']['ia_reject_reason'];
                $this->set('theiarejectreasonvalue', $theiarejectreason );

                $thetitleid = $this->data['Packinglistline']['title_id'];
                $this->set('thetitleidvalue', $thetitleid );

                // query for title details
                $mytitleinfo = $this->Title->find('`title`.`id` = ' . $thetitleid);

				$thetitlename = $mytitleinfo['Title']['title'];
				$this->set('thetitlenamevalue', $thetitlename );

				$thepressmarkname = $mytitleinfo['Title']['pressmark'];
				$this->set('thepressmarknamevalue', $thepressmarkname );

				$thecatkey = $mytitleinfo['Title']['catkey'];
				$this->set('thecatkeyvalue', $thecatkey );

                 // echo out the chron enum details so people can see the previous record's content to know where they are up to

                $this->set('lastrecchronenuminfo', "***Previous record*** From Chron: <b>" . $thechronstartdate . "</b> To Chron: <b>" . $thechronenddate . "</b> From enum: <b>" . $theenumstartdate . "</b> To enum: <b>" . $theenumenddate);

                // set packinglistid value

				$thepackinglistid = $this->data['Packinglistline']['packinglist_id'];
				$this->set('thepackinglistidvalue', $thepackinglistid );



                // set an incremented line number too.
                $thelinenumber = $this->data['Packinglistline']['linenumber'];
                $this->set('thelinenumbervalue', ($thelinenumber + 1));

            }
			else
			{
				$this->validateErrors($this->Packinglistline);
				//And render the edit view code
				$this->render();
			}


        }

    }

    function duplicate() {

        // arrays for select options
        $this->set('Agreementstatuses', array(1=>'N/K',2=>'Failed',3=>'Succeeded'));
        $this->set('Packinglistlinestatuses', array(1=>'N/K',2=>'Traced',3=>'Untraceable'));
        $this->set('Watchfilestatuses', array(1=>'N/K',2=>'OK',3=>'Not OK'));

      	$thetitlename = $this->data['Title']['title'];
    	$this->set('thetitlenamevalue', $thetitlename );

      	$thepressmarkname = $this->data['Title']['pressmark'];
    	$this->set('thepressmarknamevalue', $thepressmarkname );

      	$thecatkey = $this->data['Title']['catkey'];
    	$this->set('thecatkeyvalue', $thecatkey );

        $thepackinglistid = $this->data['Packinglist']['id'];
        //var_dump($this->data);
        $this->set('thepackinglistidvalue', $thepackinglistid );

        //var_dump($this->data['Packinglistline']);

        if (!empty($this->data['Packinglistline']))
        {

            if ($this->Packinglistline->save($this->data['Packinglistline'])) {
                // to speed things up, load a form with the duplicated variables from this form
                $this->flash('Your packinglistline info has been saved.','/packinglistlines/duplicate');
            }
			else
			{
				$this->validateErrors($this->Packinglistline);
				//And render the edit view code
				$this->render();
			}


        }

    }

     function view($id = null) {

        $this->set('Agreementstatuses', array(1=>'N/K',2=>'Failed',3=>'Succeeded'));
		$this->set('Packinglistlinestatuses', array(1=>'OK',2=>'Missing',3=>'Lost',4=>'To Conservation'));
		$this->set('Iarejectreasonstatuses', array(1=>'N/K',2=>'Uncut pages',3=>'Too wide/high',4=>'Too small',5=>'Too thick',6=>'Foldouts',7=>'Too tight gutter',8=>'Too fragile',9=>'Catalogue error'));

        $this->set('Watchfilestatuses', array(1=>'N/K',2=>'OK',3=>'Not OK'));

        $this->Packinglistline->id = $id;
        $this->set('mypackinglistline', $this->Packinglistline->read());

    }


	function editlimited($id = null)
	{

      if ($this->Packinglistline->save($this->data['Packinglistline']))
      {
          $this->flash('Your reason was updated.',"/packinglists/edit/" . $this->data['Packinglistline']['packinglist_id']);
      }
      else
      {
          $this->flash('Unable to change the reason status. Please contact administrator.',"/packinglists/edit/" . $this->data['Packinglistline']['packinglist_id']);
      }


	}



     function viewdirectfrompub($id = null) {
        // this is to try and pull up a record by catkey from titles to be scanned page or tell the user if there is not one that matches.


        $titleletters = $this->data['Packinglistline']['name'];

        //$this->set('Agreementstatuses', array(1=>'N/K',2=>'Failed',3=>'Succeeded'));
        //$this->set('Packinglistlinestatuses', array(1=>'N/K',2=>'Traced',3=>'Untraceable'));
        //$this->set('Watchfilestatuses', array(1=>'N/K',2=>'OK',3=>'Not OK'));


        // if there is no packinglistline supplied via the form, message...
        if (!$titleletters)
					{
						$this->flash('You must enter a title to search for.',"/packinglistlines/add/");
						exit;
			}


        $numberpackinglistlines = $this->Packinglistline->findCount('`name` like \'%' . rtrim($titleletters ) . '%\'');



        if ($numberpackinglistlines ==0)
        {
            $this->flash('Requested title is not available or does not exist. Try again.',"/packinglistlines/add/");
        }
        elseif ($numberpackinglistlines ==1)
        {
			 $packinglistline = $this->Packinglistline->find('`name` like \'%' . rtrim($titleletters) . '%\'');
			 $id = $packinglistline['Packinglistline']['id'];
             $this->Packinglistline->id = $id;
			 $this->set('mypackinglistline', $this->Packinglistline->read());
        }

        else # must be many
        {

			 $packinglistline = $this->Packinglistline->findall('`name` like \'%' . rtrim($titleletters) . '%\'');
			 $this->set('mypackinglistline', $packinglistline);

        }



    }



     function viewdirect($id = null) {
        // this is to try and pull up a record by catkey from titles to be scanned page or tell the user if there is not one that matches.


        $publetters = $this->data['Packinglistline']['name'];
        $iprid= $this->data['Iprtitle']['title_id'];
        $this->set('IPRidentifier', $iprid);


        $this->set('Agreementstatuses', array(1=>'N/K',2=>'Failed',3=>'Succeeded'));
        $this->set('Packinglistlinestatuses', array(1=>'N/K',2=>'Traced',3=>'Untraceable'));
        $this->set('Watchfilestatuses', array(1=>'N/K',2=>'OK',3=>'Not OK'));


        // if there is no packinglistline supplied via the form, message...
        if (!$publetters)
					{
						$this->flash('You must enter packinglistline text to search for.',"/iprtitles/edit/" . $iprid);
						exit;
			}


        $numberpackinglistlines = $this->Packinglistline->findCount('`name` like \'%' . rtrim($publetters) . '%\'');
        //$packinglistlines = $this->Packinglistline->findall('`name` like \'%' . rtrim($publetters) . '%\'');


        if ($numberpackinglistlines ==0)
        {
            $this->flash('Packinglistline does not exist. Create a new one and then link.',"/packinglistlines/addtitle/");
        }
        elseif ($numberpackinglistlines ==1)
        {
			 $packinglistline = $this->Packinglistline->find('`name` like \'%' . rtrim($publetters) . '%\'');
			 $pubid = $packinglistline['Packinglistline']['id'];
             $this->Packinglistline->id = $pubid;
			 $this->set('mypackinglistline', $this->Packinglistline->read());
        }

        else # must be many
        {

			 $packinglistline = $this->Packinglistline->findall('`name` like \'%' . rtrim($publetters) . '%\'');
			 $this->set('mypackinglistline', $packinglistline);

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

	    // find the bib_id associated with the catkey so we can pull the 856 for each of its packinglistlines

        $myid = $this->Packinglistline->find('`035` like \'%a' . rtrim($catkey) .'\' ');
        # get just the bib_id field.
        $id = $myid['Packinglistline']['id'];
		$this->Packinglistline->id = $id;
		$this->set('mypackinglistline', $this->Packinglistline->read());


    }





     function edit($id = null)
	{


		$this->set('Agreementstatuses', array(1=>'N/K',2=>'Failed',3=>'Succeeded'));
		$this->set('Packinglistlinestatuses', array(1=>'N/K',2=>'Traced',3=>'Untraceable'));
		$this->set('Watchfilestatuses', array(1=>'N/K',2=>'OK',3=>'Not OK'));

        $this->set('Packinglistlinestatuses', array(1=>'OK',2=>'Missing',3=>'Lost',4=>'To Conservation'));
        $this->set('Iarejectreasonstatuses', array(1=>'N/K',2=>'Uncut pages',3=>'Too wide/high',4=>'Too small',5=>'Too thick',6=>'Foldouts',7=>'Too tight gutter',8=>'Too fragile',9=>'Catalogue error'));

		if (empty($this->data))
		{
			$this->Packinglistline->id = $id;
			$this->data = $this->Packinglistline->read();
                        $this->set('mypackinglistline',$this->Packinglistline->read());
		}
		else
		{
		    // first try to save the updated record
			if ($this->Packinglistline->save($this->data['Packinglistline']))
			{

			    $this->flash('Your line has been updated.',"/packinglists/edit/" . $this->data['Packinglistline']['packinglist_id']);

			}
			// if couldn't update the record, show an error
			else
			{
			    $this->flash('There was a problem saving the record. Please contact your administrator.',"/packinglists/index");
			}
		}
	}




  	function delete($id)
	{
	    // before deleting, we need to get the packinglist id so we can return them to the list after deletion
        $mypacklinglistinfo = $this->Packinglistline->find('`Packinglistline`.`id` = ' . $id);
        # get just the packinglist id field.
        $mypacklinglistid = $mypacklinglistinfo['Packinglistline']['packinglist_id'];

        $this->Packinglistline->del($id);
        $this->flash('The packing list line with id: '.$id.' has been deleted. (Click to continue)', '/Packinglists/edit/' . $mypacklinglistid);
  	}

//**********************

        function index() {
                // Totally ripped from http://mho.ath.cx/~cake/exams/filter/
            // BS recursive = 1 seems to indicate it gets related records by foreign key too
            $this->Packinglistline->recursive = 2;
            //var_dump($this->Packinglistline->Field('035'));
           // EC Amendment Condition to filter depreciated material. has been added to filter component (components/filter.php)

        $this->set('Packinglistlinestatuses', array(1=>'N/K',2=>'Traced',3=>'Untraceable'));

        /* Setup filters */
            $this->Filter->init($this, 'Packinglistline');

            $this->Filter->setFilter(aa('id',
            'Record ID'), NULL, a('=','!=', '>', '<') );

            $this->Filter->setFilter(aa('name', 'Name'), NULL, a(
            '~','^','!~', '='));


            $this->Filter->setFilter(aa('packinglistline_status',
            'Status'), NULL, a( '~','^','!~', '='));





            $this->Filter->filter($f, $cond); $this->set('filters', $f);





            /* Setup pagination */
            $this->Pagination->controller = &$this;
            $this->Pagination->show = 30;

            $this->Pagination->init(
                $cond, 'Packinglistline', NULL, array('id', '035', 'place'), 0
            );
                            //'depreciated is NULL'
            $this->set('Packinglistlines', $this->Packinglistline->findAll(
                $cond,'',$this->Pagination->order, $this->Pagination->show,
                $this->Pagination->page
            ));


            //var_dump($this->Bib->packinglistlines);
        }


//**********************




}

?>