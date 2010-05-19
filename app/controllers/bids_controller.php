<?php
class BidsController extends AppController
{
    var $name = 'Bids';
    var $components = array ('Pagination', 'Filter', 'othAuth','Report'); // Added
    var $helpers = array('Html','Pagination', 'Filter'); // Added
    //var $scaffold;
    var $uses = array('Bid','Bib','Status','User');
    var $bids_exist;

     function createReport()
    {
        if (!empty($this->data))
        {
            //Determine if user is pulling existing report or deleting report
            if(isset($this->params['form']['existing']))
            {
                if($this->params['form']['existing']=='Pull')
                {
                    //Pull report
                    $this->Report->pull_report($this->data['Misc']['saved_reports']);
                }
                else if($this->params['form']['existing']=='Delete')
                {
                    //Delete report
                    $this->Report->delete_report($this->data['Misc']['saved_reports']);

                    //Return user to form
                    $this->flash('Your report has been deleted.','/'.$this->name.'/'.$this->action.'');
                }
            }
            else
            {
                //Filter out fields
                $this->Report->init_display($this->data);

                //Set sort parameter
                if(!isset($this->params['form']['order_by_primary'])) { $this->params['form']['order_by_primary']=NULL; }
                if(!isset($this->params['form']['order_by_secondary'])) { $this->params['form']['order_by_secondary']=NULL; }
                $this->Report->get_order_by($this->params['form']['order_by_primary'], $this->params['form']['order_by_secondary']);

                //Store report name
                if(!empty($this->params['form']['report_name']))
                {
                    $this->Report->save_report_name($this->params['form']['report_name']);
                }

                //Store report if save was executed
                if($this->params['form']['submit']=='Create And Save Report')
                {
                    if(empty($this->params['form']['report_name']))
                    {
                        //Return user to form
                        $this->flash('Must enter a report name when saving.','/'.$this->name.'/'.$this->action.'');
                    }
                    else
                    {
                        $this->Report->save_report();
                    }
                }
            }

            //Set report fields
            $this->set('report_fields', $this->Report->report_fields);

            //Set report name
            $this->set('report_name', $this->Report->report_name);

            //Allow search to go 2 associations deep
            $this->{$this->modelClass}->recursive = 2;

            //Set report data
            $this->set('report_data', $this->{$this->modelClass}->findAll(NULL,NULL,$this->Report->order_by));
        }
        else
        {
            //Setup options for report component
            /*
                You can setup a level two association by doing the following:
                "VehicleDriver"=>"Employee" ie $models = Array ("Vehicle", "VehicleDriver"=>"Employee");
                Please note that all fields within a level two association cannot be sorted.
            */
            $models =    Array ('Bib','Bid','Status','User');

            //Set array of fields
            $this->set('report_form', $this->Report->init_form($models));

            //Set current controller
            $this->set('cur_controller', $this->name);

            //Pull all existing reports
            $this->set('existing_reports', $this->Report->existing_reports());
        }
    }


    function bidall()
    {
		if (!empty($this->data))
        	{
                   $target = "/bibs/#row" . trim($this->data['Bid']['bib_id']);
	           $this->Bid->save($this->data);
	           $this->flash('Your Bid has been saved  <br/> (Click here to continue)',"$target");
    		}
    } // End function



    function bidpartial($id = null)
        {
        $this->set('bid_so_far', $this->data['Bid']);
        }


    function edit($id = null)
	{
            $this->set('Statuses', array(1=>'Started',2=>'Complete',3=>'On hold'));
		if (empty($this->data))
		{
			$this->Bid->id = $id;
			$this->data = $this->Bid->read();
                        $this->set('bid',$this->Bid->read());
		}
		else
		{
			if ($this->Bid->save($this->data['Bid']))
			{
				$this->flash('Your bid has been updated.',"/bibs");
			}
		}
	}

    function editfrombidlist($id = null)
	{
            $this->set('Statuses', array(1=>'Started',2=>'Complete',3=>'On hold'));
		if (empty($this->data))
		{
			$this->Bid->id = $id;
			$this->data = $this->Bid->read();
                        $this->set('bid',$this->Bid->read());
		}
		else
		{
			if ($this->Bid->save($this->data['Bid']))
			{
				$this->flash('Your bid has been updated.',"/bids");
			}
		}
	}



	function index()
	{     		// Totally ripped from http://mho.ath.cx/~cake/exams/filter/
  		 $this->Bid->recursive = 2;

        /* Setup filters */
        $this->Filter->init($this);
        $this->Filter->setFilter(aa('id', 'Record key'), NULL, a('=','!=')) ;
        $this->Filter->setFilter(aa('user_id', 'User ID'), NULL, a('=','!=')) ;
        $this->Filter->setFilter(aa('status_id', 'Status ID'), NULL, a('=','!=')) ;
        $this->Filter->setFilter(aa('norm_title', 'Title'), NULL, a('~','^','!~', '=')) ;
        $this->Filter->setFilter(aa('startdate', 'Start Date'), NULL, a('=','!=','<','>')) ;
        $this->Filter->setFilter(aa('enddate', 'End Date'), NULL, a('=','!=','<','>')) ;
        $this->Filter->filter($f, $cond);
        $this->set('filters', $f);

        /* Setup pagination */
        $this->Pagination->controller = &$this;
        $this->Pagination->show = 30;

        $this->Pagination->init(
            $cond,
            'Bid',
            NULL,
            array('id', 'title_id', 'title', 'norm_title', 'user_id', 'partial','startdate', 'enddate', 'status_id','notes','excepts'),
            0
        );


        // Add condititon - depreciated = FALSE
        $this->set('Bids', $this->Bid->findAll(
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
        $this->set('Bids', $this->Bid->findAll('', null, '', 15));
    }


  	function delete($id)
	{
        $this->Bid->del($id);
        $this->flash('The bid with id: '.$id.' has been deleted. (Click to continue)', '/bibs/');
  	}

  	function deletefrombidslist($id)
	{
        $this->Bid->del($id);
        $this->flash('The bid with id: '.$id.' has been deleted. (Click to continue)', '/bids/');
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
                $rejected_bids = array(); // Those that have been bid upon
                $rejected_notfound = array();
                $accepted_bids = array();
                $accepted_bib_ids = array();
                $dump =array();// EC DEBUGGING
                // EC DEBUG $this->set('stuff',$this->params['form']["uploadedfile"]['tmp_name']) ;
                $bids= fopen($this->params['form']["uploadedfile"]['tmp_name'],'rb');
                //$results = $this->Batch->process($thedata);

                     for($line=fgets($bids); !feof($bids);$line=fgets($bids))
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

                    //HOLDINGS CODE  elseif    (($this->Bid->find("$rel_bib[id]",'bib_id'))or($this->Bid->find("$rel_holding[bib_id]",'bib_id')))

                    elseif($rel_bib['Bid']) // PROBLEM HERE!!!!
                    {
                      // Add them to bibs rejected - bid already inplace,   end for loop
                      array_push($rejected_bids , $line);

                    }
                    else
                    {
                        // Record the original local control numbers that can be bid upon
                        array_push($accepted_bids, $line);

                        // Also, return the PK bib ID's of
                        //HOLDINGS CODE  if (!$rel_bib) {$line = $rel_holding[bib_id];} else {$line = $rel_bib;}
                        //HOLDINGS CODE $line = $rel_bib;
                        array_push($accepted_bib_ids, $rel_bib_id);
                    }

                    }// End for

                 fclose($bids);
                $i =0;
                // loop through returned bib ids array and create new bids for each!!!
                foreach ($accepted_bib_ids as $accepted_bib_id)
                {

                $this->data['Bid']['user_id'] =  $this->data['User']['id'];
                $this->data['Bid']['bib_id'] = $accepted_bib_id;
                $this->data['Bid']['status_id'] = 1;
                $this->data['Bid']['partial'] = 0;

                $this->Bid->save($this->data);
                $this->data['Bid']['id'] = NULL; // HACK to ensure insert, not update in the model class
                $i++;
                }
               $this->set('count',$i);
              $this->set('dump',$rel_bib); // EC DEBUGGING MODEL DUMP
              $this->set('accepted_bids',$accepted_bids);
              $this->set('rejected_bids',$rejected_bids);
              $this->set('rejected_notfound',$rejected_notfound);


           } // End if

        }  // End function



}
 ?>