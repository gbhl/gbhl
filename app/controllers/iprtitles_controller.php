<?php
class IprtitlesController extends AppController
{
    var $name = 'Iprtitles';
    var $components = array ('Pagination', 'Filter', 'othAuth','Report'); // Added
    var $helpers = array('Html','Pagination', 'Filter'); // Added
    //var $scaffold;
    var $uses = array('Iprtitle','Bib','Status','User');
    var $iprtitles_exist;


    function iprtitleall()
    {
		if (!empty($this->data))
        	{
                   $target = "/bibs/#row" . trim($this->data['Iprtitle']['bib_id']);
	           $this->Iprtitle->save($this->data);
	           $this->flash('Your Iprtitle has been saved  <br/> (Click here to continue)',"$target");
    		}
    } // End function



     function view($id = null) {



        $this->set('datechecked', date("Ymd"));

        $this->set('all_dates_clear_status', array(1 =>'Please select', 2=>'No', 3=>'Yes'));
        $this->set('ipr_statuses', array(1 =>'Please select', 2=>'Unknown', 3=>'Fully cleared', 4=>'Partly cleared', 5=>'No rights permitted'));
        $this->set('sources_checked', array(1 =>'Not stated', 2=>'Catalogue/Mashup', 3=>'Catalogue/Mashup and Physical Vols', 4=>'Catalogue/Mashup and Physical Vols and Archives'));
        $this->set('agreement_statuses', array(1 =>'Please select', 2=>'Unknown', 3=>'As for publisher', 4=>'In place', 5=>'None', 6=>'In negotiation'));
        $this->set('cdmcheckedstatus', array(1=>'Not checked or checking not needed', 2=>'Ready for checking'));
        $this->set('cdmcheckeddecision', array(1=>'Not checked', 2=>'Approved', 3=>"Rejected", 4=>"Pre-1860 only"));



        $this->Iprtitle->id = $id;
        $this->set('iprtitledetails', $this->Iprtitle->read());



    }


    function add($id = null) // auto add linked record with default values and load the edit form with the data
        {

        // get the title record id from the form as we need this to load the edit form later

        $id = $this->data['Iprtitle']['title_id'];

        // defaults are set in the SQL table itself except for user id



        // try and save

			if ($this->Iprtitle->save($this->data['Iprtitle']))
			{

                //var_dump($this->data);
				// load the form for edit
                $this->redirect("/iprtitles/edit/" . $id);
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
        $this->set('priority_statuses', array(1 =>'Normal', 2=>'Low', 3=>'High'));
        $this->set('sources_checked', array(1 =>'Please select', 2=>'Catalogue/Mashup', 3=>'Catalogue/Mashup and Physical Vols', 4=>'Catalogue/Mashup and Physical Vols and Archives'));
        $this->set('agreement_statuses', array(1 =>'Please select', 2=>'Unknown', 3=>'As for publisher', 4=>'In place', 5=>'None', 6=>'In negotiation'));
        $this->set('cdmcheckedstatus', array(1=>'Not checked or checking not needed', 2=>'Ready for checking'));
        $this->set('cdmcheckeddecision', array(1=>'Not checked', 2=>'Approved', 3=>"Rejected", 4=>"Pre-1860 only"));

  		$mytitle_id = $id;

	    // find all the fields for the record with title_id above
		$thewholerecord = $this->Iprtitle->find('`title_id` = ' . $mytitle_id);


	    // extract the id only and off we go...
		$id = $thewholerecord['Iprtitle']['id'];
		$this->Iprtitle->id = $id;
		$this->data = $this->Iprtitle->read();
	    $this->set('iprtitle',$this->Iprtitle->read());
	}


	function editandsave($id = null)
    {

     $thelevelofthepersonloggedon = $this->data['Iprtitle']['authlevel'];
     $thetitleidentifier = $this->data['Iprtitle']['title_id'];
     $thecheckedstatus = $this->data['Iprtitle']['cdm_checked_status'];
     $thepublisherid = $this->data['Iprtitle']['publisher_id'];
     $thedaterangewecanscan = $this->data['Iprtitle']['all_this_title_dates_ok_status'];
     $theiprstatuschosen = $this->data['Iprtitle']['ipr_status'];
     $theprioritystatus= $this->data['Iprtitle']['priority'];




     // we need to reset the date and username of a CDM submitting an update if the status is "not checked", as we only want this info to be recorded when an actual decision has been made
     $thecheckeddecision = $this->data['Iprtitle']['cdm_checked_decision'];
     if ($thecheckeddecision ==  1) // ie not checked
     {
       $this->data['Iprtitle']['cdm_date_checked'] = NULL;
       $this->data['Iprtitle']['cdm_checker_username'] = NULL;
     }

     // if user tries to submit with "ready for checking" various checks occur before allowing them to save

	 if ($thecheckedstatus == 2)
	 {

			 if ($thepublisherid == NULL || $thepublisherid == 0) // if no publisher is linked, don't let them save
			 {
				 $this->flash('Your must link the title to a rightsholder before submitting.',"/iprtitles/edit/" . $thetitleidentifier);
			 }
			 elseif ($thedaterangewecanscan == 1) // or if the entire range scanning decision hasn't been selected
             {
                 $this->flash('Your must choose whether the entire date range can be scanned or not, before submitting.',"/iprtitles/edit/" . $thetitleidentifier);
             }
             elseif ($theiprstatuschosen == 1) // or if the IPR status hasn't been selected
             {
                 $this->flash('Your must choose an IPR status before submitting.',"/iprtitles/edit/" . $thetitleidentifier);
             }

			 else
			 {
					if ($this->Iprtitle->save($this->data['Iprtitle']))
					{
						if ($thelevelofthepersonloggedon >= 200)
						   $this->flash('Your IPR information was saved.',"/titles/index");
						else // must be a reviewer level
						   $this->flash('Your IPR information was reviewed.',"/titles/indexcdm");
					}

			 }


     }
     else
	 {
			if ($this->Iprtitle->save($this->data['Iprtitle']))
			{
				if ($thelevelofthepersonloggedon >= 200)
				   $this->flash('Your IPR information was saved.',"/titles/index");
				else // must be a reviewer level
				   $this->flash('Your IPR information was reviewed.',"/titles/indexcdm");
			}

	 }

    }


	function editlimited($id = null)
	{

      if ($this->Iprtitle->save($this->data['Iprtitle']))
      {
          $this->flash('Your notes were saved.',"/titles/index");
      }
      else
      {
          $this->flash('Unable to save your notes. Please contact administrator.',"/titles/index");
      }


	}



    function editfromiprtitlelist($id = null)
	{
            $this->set('Statuses', array(1=>'Started',2=>'Complete',3=>'On hold'));
		if (empty($this->data))
		{
			$this->Iprtitle->id = $id;
			$this->data = $this->Iprtitle->read();
                        $this->set('iprtitle',$this->Iprtitle->read());
		}
		else
		{
			if ($this->Iprtitle->save($this->data['Iprtitle']))
			{
				$this->flash('Your iprtitle has been updated.',"/iprtitles");
			}
		}
	}



	function index()
	{     		// Totally ripped from http://mho.ath.cx/~cake/exams/filter/
  		 $this->Iprtitle->recursive = 2;

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
            'Iprtitle',
            NULL,
            array('id', 'title_id', 'user_id', 'partial','start_date', 'end_date', 'status_id','notes'),
            0
        );
        // Add condititon - depreciated = FALSE
        $this->set('Iprtitles', $this->Iprtitle->findAll(
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
        $this->set('Iprtitles', $this->Iprtitle->findAll('', null, '', 15));
    }


  	function delete($id)
	{
        $this->Iprtitle->del($id);
        $this->flash('The iprtitle with id: '.$id.' has been deleted. (Click to continue)', '/bibs/');
  	}

  	function deletefromiprtitleslist($id)
	{
        $this->Iprtitle->del($id);
        $this->flash('The iprtitle with id: '.$id.' has been deleted. (Click to continue)', '/iprtitles/');
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
                $rejected_iprtitles = array(); // Those that have been iprtitle upon
                $rejected_notfound = array();
                $accepted_iprtitles = array();
                $accepted_bib_ids = array();
                $dump =array();// EC DEBUGGING
                // EC DEBUG $this->set('stuff',$this->params['form']["uploadedfile"]['tmp_name']) ;
                $iprtitles= fopen($this->params['form']["uploadedfile"]['tmp_name'],'rb');
                //$results = $this->Batch->process($thedata);

                     for($line=fgets($iprtitles); !feof($iprtitles);$line=fgets($iprtitles))
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

                    //HOLDINGS CODE  elseif    (($this->Iprtitle->find("$rel_bib[id]",'bib_id'))or($this->Iprtitle->find("$rel_holding[bib_id]",'bib_id')))

                    elseif($rel_bib['Iprtitle']) // PROBLEM HERE!!!!
                    {
                      // Add them to bibs rejected - iprtitle already inplace,   end for loop
                      array_push($rejected_iprtitles , $line);

                    }
                    else
                    {
                        // Record the original local control numbers that can be iprtitle upon
                        array_push($accepted_iprtitles, $line);

                        // Also, return the PK bib ID's of
                        //HOLDINGS CODE  if (!$rel_bib) {$line = $rel_holding[bib_id];} else {$line = $rel_bib;}
                        //HOLDINGS CODE $line = $rel_bib;
                        array_push($accepted_bib_ids, $rel_bib_id);
                    }

                    }// End for

                 fclose($iprtitles);
                $i =0;
                // loop through returned bib ids array and create new iprtitles for each!!!
                foreach ($accepted_bib_ids as $accepted_bib_id)
                {

                $this->data['Iprtitle']['user_id'] =  $this->data['User']['id'];
                $this->data['Iprtitle']['bib_id'] = $accepted_bib_id;
                $this->data['Iprtitle']['status_id'] = 1;
                $this->data['Iprtitle']['partial'] = 0;

                $this->Iprtitle->save($this->data);
                $this->data['Iprtitle']['id'] = NULL; // HACK to ensure insert, not update in the model class
                $i++;
                }
               $this->set('count',$i);
              $this->set('dump',$rel_bib); // EC DEBUGGING MODEL DUMP
              $this->set('accepted_iprtitles',$accepted_iprtitles);
              $this->set('rejected_iprtitles',$rejected_iprtitles);
              $this->set('rejected_notfound',$rejected_notfound);


           } // End if

        }  // End function



}
 ?>