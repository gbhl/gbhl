<?php
class DigitizedtitlesController extends AppController
{
    var $name = 'Digitizedtitles';
    var $components = array ('Pagination', 'Filter', 'othAuth','Report'); // Added
    var $helpers = array('Html','Pagination', 'Filter'); // Added
    //var $scaffold;
    var $uses = array('Digitizedtitle','Bib','Status','User');
    var $digitizedtitles_exist;


    function digitizedtitleall()
    {
		if (!empty($this->data))
        	{
                   $target = "/bibs/#row" . trim($this->data['Digitizedtitle']['bib_id']);
	           $this->Digitizedtitle->save($this->data);
	           $this->flash('Your Digitizedtitle has been saved  <br/> (Click here to continue)',"$target");
    		}
    } // End function



    function add($id = null) // auto add linked record with default values and load the edit form with the data
        {

        // get the title record id from the form as we need this to load the edit form later

        $id = $this->data['Digitizedtitle']['title_id'];

        // defaults are set in the SQL table itself except for user id

        // try and save

			if ($this->Digitizedtitle->save($this->data['Digitizedtitle']))
			{

				// load the form for edit
                $this->redirect("/digitizedtitles/edit/" . $id);
		 	}
		 	else // save was a bloomin' failure
		 	{
		 	    $this->flash('Your electronic status information could not be saved. Report to admin.',"/titles/index");
		 	}

        }




    function edit($id = null)
	{


        $this->set('on_google_status', array(1=>'No', 2=>'Yes'));
        $this->set('on_google_scholar_status', array(1=>'No', 2=>'Yes'));
        $this->set('on_pub_website_status', array(1=>'No', 2=>'Yes'));
        $this->set('mashup_856_status', array(1=>'NK', 2=>'No', 3=>'Yes'));


        $this->set('digitized_title_status_options', array(1=>'Already scanned', 2=>'Some scanned', 3=>'None scanned', 4=>'No status'));
        $this->set('scanned_by_options', array(1=>'Not applicable', 2=>'BHL', 3=>'Commercial', 4=>'Other - open access'));

  		$mytitle_id = $id;

	    // find all the fields for the record with title_id above
		$thewholerecord = $this->Digitizedtitle->find('`title_id` = ' . $mytitle_id);
//var_dump($thewholerecord);
	    // extract the id only and off we go...
		$id = $thewholerecord['Digitizedtitle']['id'];

		$this->Digitizedtitle->id = $id;
		$this->data = $this->Digitizedtitle->read();
	    $this->set('digitizedtitle',$this->Digitizedtitle->read());
	}


	function editandsave($id = null)
    {


			if ($this->Digitizedtitle->save($this->data['Digitizedtitle']))
			{
			    $this->flash('Your electronic status information was saved.',"/titles/index");
				//$this->redirect("/digitizedtitles/edit/" . $id);
			}

    }


    function editfromdigitizedtitlelist($id = null)
	{
            $this->set('Statuses', array(1=>'Started',2=>'Complete',3=>'On hold'));
		if (empty($this->data))
		{
			$this->Digitizedtitle->id = $id;
			$this->data = $this->Digitizedtitle->read();
                        $this->set('digitizedtitle',$this->Digitizedtitle->read());
		}
		else
		{
			if ($this->Digitizedtitle->save($this->data['Digitizedtitle']))
			{
				$this->flash('Your digitizedtitle has been updated.',"/digitizedtitles");
			}
		}
	}



	function index()
	{     		// Totally ripped from http://mho.ath.cx/~cake/exams/filter/
  		 $this->Digitizedtitle->recursive = 2;

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
            'Digitizedtitle',
            NULL,
            array('id', 'title_id', 'user_id', 'partial','start_date', 'end_date', 'status_id','notes'),
            0
        );
        // Add condititon - depreciated = FALSE
        $this->set('Digitizedtitles', $this->Digitizedtitle->findAll(
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
        $this->set('Digitizedtitles', $this->Digitizedtitle->findAll('', null, '', 15));
    }


  	function delete($id)
	{
        $this->Digitizedtitle->del($id);
        $this->flash('The digitizedtitle with id: '.$id.' has been deleted. (Click to continue)', '/bibs/');
  	}

  	function deletefromdigitizedtitleslist($id)
	{
        $this->Digitizedtitle->del($id);
        $this->flash('The digitizedtitle with id: '.$id.' has been deleted. (Click to continue)', '/digitizedtitles/');
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
                $rejected_digitizedtitles = array(); // Those that have been digitizedtitle upon
                $rejected_notfound = array();
                $accepted_digitizedtitles = array();
                $accepted_bib_ids = array();
                $dump =array();// EC DEBUGGING
                // EC DEBUG $this->set('stuff',$this->params['form']["uploadedfile"]['tmp_name']) ;
                $digitizedtitles= fopen($this->params['form']["uploadedfile"]['tmp_name'],'rb');
                //$results = $this->Batch->process($thedata);

                     for($line=fgets($digitizedtitles); !feof($digitizedtitles);$line=fgets($digitizedtitles))
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

                    //HOLDINGS CODE  elseif    (($this->Digitizedtitle->find("$rel_bib[id]",'bib_id'))or($this->Digitizedtitle->find("$rel_holding[bib_id]",'bib_id')))

                    elseif($rel_bib['Digitizedtitle']) // PROBLEM HERE!!!!
                    {
                      // Add them to bibs rejected - digitizedtitle already inplace,   end for loop
                      array_push($rejected_digitizedtitles , $line);

                    }
                    else
                    {
                        // Record the original local control numbers that can be digitizedtitle upon
                        array_push($accepted_digitizedtitles, $line);

                        // Also, return the PK bib ID's of
                        //HOLDINGS CODE  if (!$rel_bib) {$line = $rel_holding[bib_id];} else {$line = $rel_bib;}
                        //HOLDINGS CODE $line = $rel_bib;
                        array_push($accepted_bib_ids, $rel_bib_id);
                    }

                    }// End for

                 fclose($digitizedtitles);
                $i =0;
                // loop through returned bib ids array and create new digitizedtitles for each!!!
                foreach ($accepted_bib_ids as $accepted_bib_id)
                {

                $this->data['Digitizedtitle']['user_id'] =  $this->data['User']['id'];
                $this->data['Digitizedtitle']['bib_id'] = $accepted_bib_id;
                $this->data['Digitizedtitle']['status_id'] = 1;
                $this->data['Digitizedtitle']['partial'] = 0;

                $this->Digitizedtitle->save($this->data);
                $this->data['Digitizedtitle']['id'] = NULL; // HACK to ensure insert, not update in the model class
                $i++;
                }
               $this->set('count',$i);
              $this->set('dump',$rel_bib); // EC DEBUGGING MODEL DUMP
              $this->set('accepted_digitizedtitles',$accepted_digitizedtitles);
              $this->set('rejected_digitizedtitles',$rejected_digitizedtitles);
              $this->set('rejected_notfound',$rejected_notfound);


           } // End if

        }  // End function



}
 ?>