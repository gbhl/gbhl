<?php
class IprlogdatesController extends AppController
{
    var $name = 'Iprlogdates';
    var $components = array ('Pagination', 'Filter', 'othAuth','Report'); // Added
    var $helpers = array('Html','Pagination', 'Filter'); // Added
    //var $scaffold;
    var $uses = array('Iprlogdate','Bib','Status','User');
    var $iprlogdates_exist;


    function iprlogdateall()
    {
		if (!empty($this->data))
        	{
                   $target = "/bibs/#row" . trim($this->data['Iprlogdate']['bib_id']);
	           $this->Iprlogdate->save($this->data);
	           $this->flash('Your Iprlogdate has been saved  <br/> (Click here to continue)',"$target");
    		}
    } // End function



     function view($id = null) {



        $this->set('datechecked', date("Ymd"));

        $this->set('all_dates_clear_status', array(1 =>'Please select', 2=>'No', 3=>'Yes'));
        $this->set('ipr_statuses', array(1 =>'Please select', 2=>'Unknown', 3=>'Fully cleared', 4=>'Partly cleared', 5=>'No rights permitted', 6=>'Request sent', 7=>'Request reply received', 8=>'More info sent to publisher', 9=>'No reply from publisher'));
        $this->set('agreement_statuses', array(1 =>'Please select', 2=>'Unknown', 3=>'As for publisher', 4=>'In place', 5=>'None', 6=>'In negotiation'));
        $this->set('cdmcheckedstatus', array(1=>'Not checked', 2=>'Ready for checking'));



        $this->Iprlogdate->id = $id;
        $this->set('iprlogdatedetails', $this->Iprlogdate->read());



    }


    function add($id = null) // auto add linked record with default values and load the edit form with the data
        {

        // get the title record id from the form as we need this to load the edit form later

        $id = $this->data['Iprlogdate']['title_id'];

        // defaults are set in the SQL table itself except for user id



        // try and save

			if ($this->Iprlogdate->save($this->data['Iprlogdate']))
			{

                //var_dump($this->data);
				// load the form for edit
                $this->redirect("/iprlogdates/edit/" . $id);
		 	}
		 	else // save was a bloomin' failure
		 	{
		 	    $this->flash('Your electronic status information could not be saved. Report to admin.',"/titles/index");
		 	}

        }




    function edit($id = null)
	{


	    // retain variables passed to the form as values if they exist
	    $the260 = $this->data['Publisher']['name'];
	    $this->set('the260value', $the260);

	    $thepublisherid = $this->data['Publisher']['id'];
		$this->set('thepublisheridvalue', $thepublisherid );


	    $theformername = $this->data['Publisher']['former_name'];
		$this->set('theformernamevalue', $theformername);

		$thecountrybased = $this->data['Publisher']['country_based'];
		$this->set('thecountrybasedvalue', $thecountrybased);

		$theagstatus = $this->data['Publisher']['agreement_status'];
		$this->set('theagstatusvalue', $theagstatus);

		$thepubstat = $this->data['Publisher']['publisher_status'];
        $this->set('thepubstatvalue', $thepubstat);



        $this->set('datechecked', date("Ymd"));

        $this->set('all_dates_clear_status', array(1 =>'Please select', 2=>'No', 3=>'Yes'));
        $this->set('ipr_statuses', array(1 =>'Please select', 2=>'Unknown', 3=>'Fully cleared', 4=>'Partly cleared', 5=>'No rights permitted'));
        $this->set('agreement_statuses', array(1 =>'Please select', 2=>'Unknown', 3=>'As for publisher', 4=>'In place', 5=>'None', 6=>'In negotiation'));
        $this->set('cdmcheckedstatus', array(1=>'Not checked', 2=>'Ready for checking'));
        $this->set('cdmcheckeddecision', array(1=>'Not checked', 2=>'Approved', 3=>"Rejected", 4=>"Pre-1860 only"));

  		$mytitle_id = $id;

	    // find all the fields for the record with title_id above
		$thewholerecord = $this->Iprlogdate->find('`title_id` = ' . $mytitle_id);

	    // extract the id only and off we go...
		$id = $thewholerecord['Iprlogdate']['id'];

		$this->Iprlogdate->id = $id;
		$this->data = $this->Iprlogdate->read();
	    $this->set('iprlogdate',$this->Iprlogdate->read());
	}


	function editandsave($id = null)
    {

     // only allow a save with submission status 'ready for checking' if the title has been linked to a publisher

     $thetitleidentifier = $this->data['Iprlogdate']['title_id'];
     $thecheckedstatus = $this->data['Iprlogdate']['cdm_checked_status'];
     $thepublisherid = $this->data['Iprlogdate']['publisher_id'];


	 if ($thecheckedstatus == 2 && ($thepublisherid == NULL || $thepublisherid == 0))
     {
         $this->flash('Your must link the title to a publisher before submitting.',"/iprlogdates/edit/" . $thetitleidentifier);
     }
	 else
	 {
			if ($this->Iprlogdate->save($this->data['Iprlogdate']))
			{
			    // this saves to the iprlogdate table too
			    $this->Iprlogdate->save($this->data['Iprlogdate']);
			    $this->flash('Your IPR information was saved.',"/titles/index");
				//$this->redirect("/iprlogdates/edit/" . $id);
			}

     }


    }


    function editfromiprlogdatelist($id = null)
	{
            $this->set('Statuses', array(1=>'Started',2=>'Complete',3=>'On hold'));
		if (empty($this->data))
		{
			$this->Iprlogdate->id = $id;
			$this->data = $this->Iprlogdate->read();
                        $this->set('iprlogdate',$this->Iprlogdate->read());
		}
		else
		{
			if ($this->Iprlogdate->save($this->data['Iprlogdate']))
			{
				$this->flash('Your iprlogdate has been updated.',"/iprlogdates");
			}
		}
	}



	function index()
	{     		// Totally ripped from http://mho.ath.cx/~cake/exams/filter/
  		 $this->Iprlogdate->recursive = 2;

        /* Setup filters */
        $this->Filter->init($this);
        $this->Filter->setFilter(aa('id', 'Record key'), NULL, a('=','!=')) ;
        $this->Filter->setFilter(aa('user_id', 'User ID'), NULL, a('=','!=')) ;
        $this->Filter->setFilter(aa('status_id', 'Status ID'), NULL, a('=','!=')) ;
        $this->Filter->setFilter(aa('startdate', 'Start Date'), NULL, a('=','!=','<','>')) ;
        $this->Filter->setFilter(aa('enddate', 'End Date'), NULL, a('=','!=','<','>')) ;
        $this->Filter->filter($f, $cond);
        $this->set('filters', $f);

        /* Setup pagination */
        $this->Pagination->controller = &$this;
        $this->Pagination->show = 30;
        $this->Pagination->init(
            $cond,
            'Iprlogdate',
            NULL,
            array('id', 'title_id', 'user_id', 'partial','start_date', 'end_date', 'status_id','notes'),
            0
        );
        // Add condititon - depreciated = FALSE
        $this->set('Iprlogdates', $this->Iprlogdate->findAll(
            $cond,
            NULL,
            $this->Pagination->order,
            $this->Pagination->show,
            $this->Pagination->page
        ));
	}




     function rss()
    {
        $this->layout = 'xml';
        $this->set('Iprlogdates', $this->Iprlogdate->findAll('', null, '', 15));
    }


  	function delete($id)
	{
        $this->Iprlogdate->del($id);
        $this->flash('The iprlogdate with id: '.$id.' has been deleted. (Click to continue)', '/bibs/');
  	}

  	function deletefromiprlogdateslist($id)
	{
        $this->Iprlogdate->del($id);
        $this->flash('The iprlogdate with id: '.$id.' has been deleted. (Click to continue)', '/iprlogdates/');
  	}


        function batch()
        {
            $this->set('users',$this->User->generateList('User.id','User.username'));
            $gid = 1 && $strict_gid_check = true; // stops normal users from accessing this controller
        }



        function batchProcess()
        {

            $gid = 1 && $strict_gid_check = true; // stops normal users from accessing this controller
            // NOTE FOR BERS - COMMENTED OUT LINES CONTAIN ADDITIONAL CODE TO COPE WITH extra holdings table - may need to be added in. Also dodgy logic on the 'or' statements. Pap.
           if (!empty($this->data))
           {
                $rejected_iprlogdates = array(); // Those that have been iprlogdate upon
                $rejected_notfound = array();
                $accepted_iprlogdates = array();
                $accepted_bib_ids = array();
                $dump =array();// EC DEBUGGING
                // EC DEBUG $this->set('stuff',$this->params['form']["uploadedfile"]['tmp_name']) ;
                $iprlogdates= fopen($this->params['form']["uploadedfile"]['tmp_name'],'rb');
                //$results = $this->Batch->process($thedata);

                     for($line=fgets($iprlogdates); !feof($iprlogdates);$line=fgets($iprlogdates))
                    {
                    $line=trim($line);


                    $rel_bib = $this->Bib->find("matches like '%$line%'");
                    //HOLDINGS CODE $rel_holding = $this->Holding->find("$line",'localctrl');

                    $rel_bib_id = $rel_bib['Bib']['id'];


                    array_push($dump,$rel_bib);
                    //HOLDINGS CODE if (!$rel_bib) or (!$rel_holding))
                    if (!$rel_bib_id)
                    {
                      // Add them to bibs rejected - not found,   end for loop
                      array_push($rejected_notfound, $line);
                    }

                    //HOLDINGS CODE  elseif    (($this->Iprlogdate->find("$rel_bib[id]",'bib_id'))or($this->Iprlogdate->find("$rel_holding[bib_id]",'bib_id')))

                    elseif($rel_bib['Iprlogdate']) // PROBLEM HERE!!!!
                    {
                      // Add them to bibs rejected - iprlogdate already inplace,   end for loop
                      array_push($rejected_iprlogdates , $line);

                    }
                    else
                    {
                        // Record the original local control numbers that can be iprlogdate upon
                        array_push($accepted_iprlogdates, $line);

                        // Also, return the PK bib ID's of
                        //HOLDINGS CODE  if (!$rel_bib) {$line = $rel_holding[bib_id];} else {$line = $rel_bib;}
                        //HOLDINGS CODE $line = $rel_bib;
                        array_push($accepted_bib_ids, $rel_bib_id);
                    }

                    }// End for

                 fclose($iprlogdates);
                $i =0;
                // loop through returned bib ids array and create new iprlogdates for each!!!
                foreach ($accepted_bib_ids as $accepted_bib_id)
                {

                $this->data['Iprlogdate']['user_id'] =  $this->data['User']['id'];
                $this->data['Iprlogdate']['bib_id'] = $accepted_bib_id;
                $this->data['Iprlogdate']['status_id'] = 1;
                $this->data['Iprlogdate']['partial'] = 0;

                $this->Iprlogdate->save($this->data);
                $this->data['Iprlogdate']['id'] = NULL; // HACK to ensure insert, not update in the model class
                $i++;
                }
               $this->set('count',$i);
              $this->set('dump',$rel_bib); // EC DEBUGGING MODEL DUMP
              $this->set('accepted_iprlogdates',$accepted_iprlogdates);
              $this->set('rejected_iprlogdates',$rejected_iprlogdates);
              $this->set('rejected_notfound',$rejected_notfound);


           } // End if

        }  // End function



}
 ?>