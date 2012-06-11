<?php

class TitlesController extends AppController {
        var $name = 'Titles'; var $components = array ('Pagination', 'Filter','Report');
        var $helpers = array('Html','Pagination','Filter','Matchlinker','Digitizedtitlebutton', 'Iprtitlebutton' ); // Added 'SerialsMatchLinker'
        var $uses = array('Title','Bid','Status','User','Holding');


     function view($id = null) {

		$this->set('Highestcopyrights', array(1=>'Pre 1860',2=>'1860 - 1907',3=>'Post 1907'));
		$this->set('Any_scannable_statuses', array(1=>'Yes',2=>'No'));
        $this->set('Any_pre_1860_statuses', array(1=>'Yes',2=>'No'));
        $this->set('Bid_placed', array(0=>'No',1=>'Yes'));
        $this->set('Title_scan_status', array(1=>'New',2=>'Part scanned',3=>'Fully scanned',4=>'Cancelled'));

        $this->Title->id = $id;
        //var_dump($this->Title->id);
        $this->set('mytitle', $this->Title->read());

        //var_dump($this->Title->read());
       // $this->set('bids', $this->Bid->findAll(array('title_id'
       // =>'$this->Title->id')));
    }


     function addtitle() {

        // arrays for select options
        $this->set('Highestcopyrights', array(1=>'Pre 1860',2=>'1860 - 1907',3=>'Post 1907'));
        $this->set('Any_scannable_statuses', array(1=>'Yes',2=>'No'));
        $this->set('Any_pre_1860_statuses', array(1=>'Yes',2=>'No'));


        if ($this->data['Bib']['title']) // must be at least a title!
        {
			// retain variables passed to the form as values if they exist
			$the245title = $this->data['Bib']['title'];
			$this->set('the245value', $the245title);

			$the260pub = $this->data['Bib']['pub'];
			$this->set('the260value', $the260pub);

			$thecatkey = $this->data['Holding']['035'];
			$this->set('theblinkingcatkeyvalue', $thecatkey);

			$thepmark = $this->data['Bib']['pressmark'];
			$this->set('thepressmarkvalue', $thepmark);

         }



        if (!empty($this->data['Title']))
        {

            // get formatted pressmark for sorting.
 			$thepmark = $this->data['Title']['pressmark'];
			ereg("[0-9]+",$thepmark , $digitportion);
			$digitportionnormalised = sprintf("%010d", $digitportion[0]);
			$this->data['Title']['pressmark_sort'] = $digitportionnormalised;


            if ($this->Title->save($this->data['Title'])) {
                $this->flash('Your title has been saved.','/titles/');
            }
			else
			{
				$this->validateErrors($this->Title);
				//And render the edit view code
				$this->render();
			}


        }

    }


        function dedupselect() {
                // Note, any changes here need to be replicated in index function, and vice-versa
                // Totally ripped from http://mho.ath.cx/~cake/exams/filter/
            $this->Title->recursive = 1;
              $cond = 'depreciated is NULL';
        /* Setup filters */
            $this->Filter->init($this, 'Title');

            $this->Filter->setFilter(aa('id',
            'Record ID'), NULL, a('=','!=', '>', '<') );

            $this->Filter->setFilter(aa('title', 'Title'), NULL, a(
            '~','^','!~', '='));

            $this->Filter->setFilter(aa('abbrev_title', 'Abbreviated title'), NULL, a(
            '~','^','!~', '='));


            $this->Filter->setFilter(aa('pub',
            'Publisher'), NULL, a( '~','^','!~', '='));

            $this->Filter->setFilter(aa('match_basis', 'Match method (022, OCLC, TITLE, NO MATCH)'), NULL,
            a('=', '~'));

            $this->Filter->setFilter(aa('places',
            'Place'), NULL, a( '~','^','!~', '='));

            $this->Filter->setFilter(aa('subjects',
            'Subject'), NULL, a( '~','^','!~', '='));



            $this->Filter->filter($f, $cond); $this->set('filters', $f);

            /* Setup pagination */
            $this->Pagination->controller = &$this;
            $this->Pagination->show = 30;
            $this->Pagination->init(
                $cond, 'Title', NULL, array('id', 'title', 'pub', 'abbrev_title', 'match_basis', 'places', 'subjects'), 0
            );

            $this->set('Titles', $this->Title->findAll(
                $cond, NULL, $this->Pagination->order, $this->Pagination->show,
                $this->Pagination->page
            ));

        }

        function dedup($id = null) {
        // Inital call to set up both records
         $this->Title->id = $this->params['form']['id1'];
         $this->set('title1',$this->Title->read());
         $this->Title->id = $this->params['form']['id2'];
         $this->set('title2', $this->Title->read());
        }

        function dedup2($id=null) {

            // Grab our two title id's from form parameters....
            $idtokeep = $this->params['form']['idtokeep'];
            //$this->set('idtokeep',$idtokeep);
            $idtodel =  $this->params['form']['idtodel'];
            //$this->set('idtodel',$idtodel);
            // Update holdings and bids where id = idtodel set all fkeys to idtokeep

             // Get each bid where title_id = $idtodel and change the fkey
            $bidstochange = $this->Bid->findall(array('title_id'=>$idtodel));
            foreach ($bidstochange as $bidtochange)
                {
                $bidtochange['Bid']['title_id'] = $idtokeep;
                $this->Bid->save($bidtochange['Bid']);
                }

             // repeat for holdings
            $holdingstochange = $this->Holding->findall(array('title_id'=>$idtodel));
            foreach ($holdingstochange as $holdingtochange )
                {
                $holdingtochange['Holding']['title_id'] = $idtokeep;
                $this->Holding->save($holdingtochange['Holding']);
                }

            //-----------------------
             // Code to process 'flat' matches field in current title model - REMOVE THIS ONCE ALL MATCHES ARE HELD IN THE HOLDINGS TABLE
             //First, process on title to depreciate...
               $this->Title->id = $idtodel;
               $grabbedmatch = $this->Title->field('matches');
               $this->set('grabbedmatch',$grabbedmatch);


               // Flag titletodel as depreciated, rather than delete...
               //This following line will need to stay even if 'flat' code is removed...
               $this->Title->saveField('depreciated',1);

              // Switch to title to keep
              $this->Title->id = $idtokeep;
              $newmatch = $this->Title->field('matches'). " " . $grabbedmatch;
              $this->Title->saveField('matches',$newmatch);

              $newmatch ='';
              $grabbedmatch = '';
            // ---------------------------End flat matches code

            // Delete from bis where id = idtodel - not used currently...
             //$this->Title->delete($idtodel);

          // Flash - record has been deduped - pass to view for record to keep!!
          $this->flash('Records has been updated de-duplicated (Click here to continue)',"/titles/view/$idtokeep");
        }



//**********************


        function index() {


                   # for displaying the words rather than numbers in the list
                   $this->set('Highestcopyrights', array(1=>'Pre 1860',2=>'1860 - 1907',3=>'Post 1907'));


                   $this->Title->recursive = 1;
	               //var_dump($this->Title->Field('title'));
	              // EC Amendment Condition to filter depreciated material. has been added to filter component (components/filter.php)


	           /* Setup filters */
	               $this->Filter->init($this, 'Title');

	               $this->Filter->setFilter(aa('catkey',
	               'Catkey'), NULL, a('=','!=', '>', '<') );

	               $this->Filter->setFilter(aa('title', 'Title'), NULL, a(
	               '~','^','!~', '='));

	               $this->Filter->setFilter(aa('any_scannable_status', 'Scannable (1 = Yes, 2 = No)'), NULL,
	               a('=', '!='));

	               $this->Filter->setFilter(aa('highest_copyright_band', 'Copyright Band (1 = <1860, 2 = 1860-1907, 3 = >1907)'), NULL,
	               a('=', '~'));

	               $this->Filter->setFilter(aa('username', 'Username'), NULL,
	               a('=', '~'));


	               $this->Filter->filter($f, $cond); $this->set('filters', $f);





	               /* Setup pagination */
	               $this->Pagination->controller = &$this;
	               $this->Pagination->show = 30;


	               $this->Pagination->init(
	                   $cond, 'Title', NULL, array('date_created', 'catkey', 'title', 'pub', ), 0
	               );
	                               //'depreciated is NULL'
	               $this->set('Titles', $this->Title->findAll(
	                   $cond,'','date_created desc', $this->Pagination->show,
	                   $this->Pagination->page
	               ));



        }


//**********************


        function indexpacker() {




                   $this->Title->recursive = 1;
	               //var_dump($this->Title->Field('title'));
	              // EC Amendment Condition to filter depreciated material. has been added to filter component (components/filter.php)

                  // bers: get pressmark entered to form and formulate as $cond string for below

                  $pressmark = $this->data['Title']['pressmark'];
                  // format the search term presented so it digits only
                  ereg("[0-9]{1,6}", $pressmark, $formatted_pressmark);
                  //$cond = "where strcomp(pressmark, $formatted_pressmark[0]) = -1";

                  //$cond = "where pressmark >=" . $pressmark . "'";

                  $cond = "where title_status !=3 AND pre_1860_bid_placed =1";

                  $pressmark_sort = $this->data['Title']['pressmark_sort'];

                  if ($pressmark_sort)
                  {
                    $cond .= " and pressmark_sort >=" . $pressmark_sort;
                  }

	               /* Setup pagination */
	               $this->Pagination->controller = &$this;
	               $this->Pagination->show = 30;


	               $this->Pagination->init(
	                   $cond, 'Title', NULL, array('date_created', 'catkey', 'pressmark', 'title', ), 0
	               );
	                               //'depreciated is NULL'
	               $this->set('Titles', $this->Title->findAll(
	                   $cond,'','pressmark_sort asc', $this->Pagination->show,
	                   $this->Pagination->page
	               ));



        }

//**********************


        function indexpacker2() {




                   $this->Title->recursive = 1;
	               //var_dump($this->Title->Field('title'));
	              // EC Amendment Condition to filter depreciated material. has been added to filter component (components/filter.php)

                  // bers: get pressmark entered to form and formulate as $cond string for below

                  $pressmark = $this->data['Title']['pressmark'];
                  // format the search term presented so it digits only
                  ereg("[0-9]{1,6}", $pressmark, $formatted_pressmark);
                  //$cond = "where strcomp(pressmark, $formatted_pressmark[0]) = -1";

                  //$cond = "where pressmark >=" . $pressmark . "'";

                  $cond = "where title_status !=3 AND post_1860_bid_placed =1";

                  $pressmark_sort = $this->data['Title']['pressmark_sort'];

                  if ($pressmark_sort)
                  {
                    $cond .= " and pressmark_sort >=" . $pressmark_sort;
                  }


	               /* Setup pagination */
	               $this->Pagination->controller = &$this;
	               $this->Pagination->show = 30;


	               $this->Pagination->init(
	                   $cond, 'Title', NULL, array('date_created', 'catkey', 'pressmark', 'title', ), 0
	               );
	                               //'depreciated is NULL'
	               $this->set('Titles', $this->Title->findAll(
	                   $cond,'','pressmark_sort asc', $this->Pagination->show,
	                   $this->Pagination->page
	               ));



        }

        function indexcdm() {

                   # for cdms to check various things and edit to change statuses as approved.

                   # for displaying the words rather than numbers in the list
                   $this->set('Highestcopyrights', array(1=>'Pre 1860',2=>'1860 - 1907',3=>'Post 1907'));
                  // $this->set('ipr_statuses', array(1 =>'Please select', 2=>'Unknown', 3=>'Fully cleared', 4=>'Partly cleared', 5=>'No rights permitted'));

                   $this->Title->recursive = 1;
	               //var_dump($this->Title->Field('title'));
	              // EC Amendment Condition to filter depreciated material. has been added to filter component (components/filter.php)


	           /* Setup filters */
	               $this->Filter->init($this, 'Title');

	               $this->Filter->setFilter(aa('catkey',
	               'Catkey'), NULL, a('=','!=', '>', '<') );

	               $this->Filter->setFilter(aa('title', 'Title'), NULL, a(
	               '~','^','!~', '='));

	               $this->Filter->setFilter(aa('any_scannable_status', 'Scannable (1 = Yes, 2 = No)'), NULL,
	               a('=', '!='));

	               $this->Filter->setFilter(aa('highest_copyright_band', 'Copyright Band (1 = <1860, 2 = 1860-1907, 3 = >1907)'), NULL,
	               a('=', '~'));


	               $this->Filter->setFilter(aa('username', 'Username'), NULL,
	               a('=', '~'));




	               $this->Filter->filter($f, $cond); $this->set('filters', $f);

                   # only show records which are ready for checking or have already been checked.
                   $cond .= " AND cdm_checked_status = 2 AND cdm_checked_decision = 1";




	               /* Setup pagination */
	               $this->Pagination->controller = &$this;
	               $this->Pagination->show = 30;

	               $this->Pagination->init(
	                   $cond, 'Title', NULL, array('catkey', 'title', 'pub'), 0
	               );
	                               //'depreciated is NULL'
	               $this->set('Titles', $this->Title->findAll(
	                   $cond,'',$this->Pagination->order, $this->Pagination->show,
	                   $this->Pagination->page
	               ));



        }



        function indexcdmundo() {

                   # for cdms to undo approvals.

                   # for displaying the words rather than numbers in the list
                   $this->set('Highestcopyrights', array(1=>'Pre 1860',2=>'1860 - 1907',3=>'Post 1907'));
                  // $this->set('ipr_statuses', array(1 =>'Please select', 2=>'Unknown', 3=>'Fully cleared', 4=>'Partly cleared', 5=>'No rights permitted'));

                   $this->Title->recursive = 1;
	               //var_dump($this->Title->Field('title'));
	              // EC Amendment Condition to filter depreciated material. has been added to filter component (components/filter.php)


	           /* Setup filters */
	               $this->Filter->init($this, 'Title');

	               $this->Filter->setFilter(aa('catkey',
	               'Catkey'), NULL, a('=','!=', '>', '<') );

	               $this->Filter->setFilter(aa('title', 'Title'), NULL, a(
	               '~','^','!~', '='));

	               $this->Filter->setFilter(aa('any_scannable_status', 'Scannable (1 = Yes, 2 = No)'), NULL,
	               a('=', '!='));

	               $this->Filter->setFilter(aa('highest_copyright_band', 'Copyright Band (1 = <1860, 2 = 1860-1907, 3 = >1907)'), NULL,
	               a('=', '~'));


	               $this->Filter->setFilter(aa('username', 'Username'), NULL,
	               a('=', '~'));




	               $this->Filter->filter($f, $cond); $this->set('filters', $f);

                   # only show records which are ready for checking or have already been checked.
                   $cond .= " AND cdm_checked_status = 2 AND (cdm_checked_decision = 2 || cdm_checked_decision = 4)";




	               /* Setup pagination */
	               $this->Pagination->controller = &$this;
	               $this->Pagination->show = 30;

	               $this->Pagination->init(
	                   $cond, 'Title', NULL, array('catkey', 'title', 'pub'), 0
	               );
	                               //'depreciated is NULL'
	               $this->set('Titles', $this->Title->findAll(
	                   $cond,'',$this->Pagination->order, $this->Pagination->show,
	                   $this->Pagination->page
	               ));



        }






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
            $models =    Array ('Title');

            //Set array of fields
            $this->set('report_form', $this->Report->init_form($models));

            //Set current controller
            $this->set('cur_controller', $this->name);

            //Pull all existing reports
            $this->set('existing_reports', $this->Report->existing_reports());
        }
    }


        function edit($id = null) {

                // arrays for select options
		        $this->set('Highestcopyrights', array(1=>'Pre 1860',2=>'1860 - 1907',3=>'Post 1907'));
		        $this->set('Any_scannable_statuses', array(1=>'Yes',2=>'No'));
		        $this->set('Any_pre_1860_statuses', array(1=>'Yes',2=>'No'));
		        $this->set('e_statuses', array(1=>'NK',2=>'Yes',3=>'No'));
                $this->set('Bid_placed', array(0=>'No',1=>'Yes'));
                $this->set('Title_scan_status', array(1=>'New',2=>'Part scanned',3=>'Fully scanned',4=>'Cancelled'));


                if (empty($this->data)) {
                        $this->Title->id = $id;
                        $this->data = $this->Title->read();
                        $this->set('mytitle',$this->Title->read());

                } else {

						// get formatted pressmark for sorting.
						$thepmark = $this->data['Title']['pressmark'];
						ereg("[0-9]+",$thepmark , $digitportion);
						$digitportionnormalised = sprintf("%010d", $digitportion[0]);
						$this->data['Title']['pressmark_sort'] = $digitportionnormalised;

                        if ($this->Title->save($this->data['Title'])) {
                                $this->flash('Your record has been
                                updated (Click here to continue)','/titles');
                        }
                }
        }


		 function pickupatitle($id = null) {

		   // this is to try and pull up a record by catkey from titles to be scanned page or tell the user if there is not one that matches.

            $this->set('Highestcopyrights', array(1=>'Pre 1860',2=>'1860 - 1907',3=>'Post 1907'));
            $this->set('Any_scannable_statuses', array(1=>'Yes',2=>'No'));
             $this->set('Any_pre_1860_statuses', array(1=>'Yes',2=>'No'));


			$titleletters = $this->data['Packinglistline']['name'];

			$thepackinglistid = $this->data['Packinglistline']['packinglist_id'];
			$this->set('thepackinglistidvalue', $thepackinglistid );


			//$this->set('Packinglistlinestatuses', array(1=>'N/K',2=>'Traced',3=>'Untraceable'));


			// if there is no packinglistline supplied via the form, message...
			if (!$titleletters)
						{
							$this->flash('You must enter a title to search for.',"/packinglists/edit/" . $thepackinglistid);
							//$this->flash('You must enter a title to search for.',"/packinglistlines/add/");
							//exit;
				}

//////////////GUTS OF THE LOGIC AS TO WHETHER A TITLE CAN BE ADDED TO A PACKING LIST IS HERE ////////
            // for both the count and the select, we need to restrict on titles which
            // a) have titles.any_scannable_status = Y (int 1) AND
            // b) have digitizedtitles.digitized_title_status != Already scanned (int 1) AND
            // c) have iprtitles.ipr_status != No rights permitted (int 5) AND
            // d) have
            //  iprtitles.cdm_checked_decision != rejected (int 3) & titles.any_pre_1860 = Y (int 1)
            //    OR
            //  iprtitles.cdm_checked_decision = accepted (int 2)  & highest_copyright_band post-1860 (int 2 or 3)

            // have a conds variable here to avoid repeating the long syntax

            $conds = '`title` like \'%' . rtrim($titleletters ) . '%\' AND any_scannable_status =\'1\' AND (digitized_title_status !=\'1\' || digitized_title_status is null) AND ipr_status != \'5\' AND ((cdm_checked_decision != \'3\' AND any_pre_1860 = \'1\') || (cdm_checked_decision = \'2\' AND highest_copyright_band >= 2) ) ';

///////////////////////////////////////////////////////////////////////////////////////////////////////
            //var_dump($conds);


			$numbertitles = $this->Title->findCount($conds);
			$this->set('thenumberoftitles', $numbertitles);



			if ($numbertitles ==0)
			{
				$this->flash('Requested title is not available or does not exist. Try again.',"/packinglists/edit/" . $thepackinglistid);
			}
			elseif ($numbertitles ==1)
			{
				 $apukkatitle = $this->Title->find($conds);

				 $id = $apukkatitle['Title']['id'];
				 $this->Title->id = $id;
				 $this->set('myapukkatitle', $this->Title->read());
			}

			else # must be many
			{

				 $apukkatitle = $this->Title->findall($conds);
				 $this->set('myapukkatitle', $apukkatitle);

			}



		 }


		 function pickupatitle_problemlist($id = null) {

		   // this is to try and pull up a record by catkey from titles to be scanned page or tell the user if there is not one that matches.

            $this->set('Highestcopyrights', array(1=>'Pre 1860',2=>'1860 - 1907',3=>'Post 1907'));
            $this->set('Any_scannable_statuses', array(1=>'Yes',2=>'No'));


			$titleletters = $this->data['Problemlistline']['name'];

			$theproblemlistid = $this->data['Problemlistline']['problemlist_id'];
			$this->set('theproblemlistidvalue', $theproblemlistid );


			// if there is no problemlistline supplied via the form, message...
			if (!$titleletters)
						{
							$this->flash('You must enter a title to search for.',"/problemlists/edit/" . $theproblemlistid);
							//$this->flash('You must enter a title to search for.',"/problemlistlines/add/");
							//exit;
				}

            // Very open. Just look for anything already on the title list
            // have a conds variable here to avoid repeating the long syntax


            $conds = '`title` like \'%' . rtrim($titleletters ) . '%\'';

			//$numbertitles = $this->Title->findCount('`title` like \'%' . rtrim($titleletters ) . '%\'');

			$numbertitles = $this->Title->findCount($conds);
			$this->set('thenumberoftitles', $numbertitles);


			if ($numbertitles ==0)
			{
				$this->flash('Requested title is not available or does not exist. Try again.',"/problemlists/edit/" . $theproblemlistid);
			}
			elseif ($numbertitles ==1)
			{
				 $apukkatitle = $this->Title->find($conds);

				 $id = $apukkatitle['Title']['id'];
				 $this->Title->id = $id;
				 $this->set('myapukkatitle', $this->Title->read());
			}

			else # must be many
			{

				 $apukkatitle = $this->Title->findall($conds);
				 $this->set('myapukkatitle', $apukkatitle);

			}



		 }

		 function getbycatkey($id = null) {


            //if (!$id)
            //{
            //  $this->flash('No record matches. (Click here to continue)','/titles/addtitle');
            //}
            //else
            //{

                $id = $this->params['form']['data']['Title']['catkey'];

				$this->set('Highestcopyrights', array(1=>'Pre 1860',2=>'1860 - 1907',3=>'Post 1907'));
				$this->set('Any_scannable_statuses', array(1=>'Yes',2=>'No'));


				$this->Title->id = $id;
				//var_dump($this->Title->id);
				$this->set('mytitle', $this->Title->read());
            //}

		}




}

?>