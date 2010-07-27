<?php

class BibsController extends AppController {
    var $name = 'Bibs';
    var $components = array ('Pagination', 'Filter','Report');
    var $helpers = array('Html','Pagination','Filter','Matchlinker','Bidbutton' ); // Added 'SerialsMatchLinker'
    var $uses = array('Bib','Bid','Status','User','Holding');


    function view($id = null) {
        $this->Bib->id = $id;
        $this->set('bib', $this->Bib->read());

        // look for source records which were merged with this one and get their IDS
        // select ids where new_id = id


        $oldrecsmerged = $this->Bib->findall(array('new_id'=>$this->Bib->id));
        $this->set('oldrecordsmerged', $oldrecsmerged);


    }

    function viewfromedittitle($id = null) {


        $myid = $this->Holding->find('`035` like \'%a' . rtrim($id) . '\'');

        if ($myid) {
        # get just the bib_id field.
            $id = $myid['Holding']['bib_id'];
            $numbids = $this->Bid->findcount('bib_id = ' . rtrim($id));
        }

        if ($numbids > 0) {
            $this->Bib->id = $id;
            $this->set('bib', $this->Bib->read());
            // look for source records which were merged with this one and get their IDS
            // select ids where new_id = id

            $oldrecsmerged = $this->Bib->findall(array('new_id'=>$this->Bib->id));
            $this->set('oldrecordsmerged', $oldrecsmerged);
        }
        else {

            $this->flash('No linked bids present.','/bibs/index' );


        }


    }

    function viewdepr($id = null) {
    // version for deprecated (merged) recs to ensure no fresh bids can be placed on them but they can be seen)
        $this->Bib->id = $id;
        $this->set('bib', $this->Bib->read());

        // look for source records which were merged with this one and get their IDS
        // select ids where new_id = id


        $oldrecsmerged = $this->Bib->findall(array('new_id'=>$this->Bib->id));
        $this->set('oldrecordsmerged', $oldrecsmerged);


    }

    /* - KILLED BY BERNARD...
     function add() {
        if (!empty($this->data)) {
            if ($this->bib->save($this->data)) {
                $this->flash('Your post has been saved.','/bibs/');
            }
        }
    }
    */

    function dedupselect() {
    // Note, any changes here need to be replicated in index function, and vice-versa
    // Totally ripped from http://mho.ath.cx/~cake/exams/filter/
        $this->Bib->recursive = 1;
        $cond = 'depreciated is NULL';
        /* Setup filters */
        $this->Filter->init($this, 'Bib');

        $this->Filter->setFilter(aa('id',
            'Title ID'), NULL, a('=','!=', '>', '<') );

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

        $this->Filter->setFilter(aa('found_match',
            'Duplicate?'), NULL, a('='));


        $this->Filter->filter($f, $cond); $this->set('filters', $f);

            /* Setup pagination */
        $this->Pagination->controller = &$this;
        $this->Pagination->show = 30;
        $this->Pagination->init(
            $cond, 'Bib', NULL, array('id', 'title', 'pub', 'abbrev_title', 'match_basis', 'places', 'subjects', 'found_match'), 0
        );

        $this->set('Bibs', $this->Bib->findAll(
            $cond, NULL, $this->Pagination->order, $this->Pagination->show,
            $this->Pagination->page
        ));

    }

    function dedup($id = null) {
    // Initial call to set up both records
    // get any record selected by looking through the form params and push onto an array
        $selecteddedups = array();

        // push each checked box of the 30 on the page onto an array
        for ($i = 1; $i <= 30; $i++) {
            $thisselid = "selid" . $i;

            if ($this->params['form'][$thisselid]) {
                array_push($selecteddedups, $this->params['form'][$thisselid]);
            }
        }


        // if only one selected, error out and return

        if (sizeof($selecteddedups) < 2 || sizeof($selecteddedups) > 30) // ie if there are none
        {
            $this->flash('You must select more than one record to dedup and a maximum of 30.',"/bibs/dedupselect");

        }

        // pop each id of the checked box onto a conds statement

        // pop the last one off the end
        $theend =  "id = " . array_pop($selecteddedups);

        // shift the first one off the start
        $the_start = "id = " . array_shift($selecteddedups);

        if (sizeof($selecteddedups) > 0) // ie if there are any others
        {
        // string them together with ORs between


            for ($j = 0; $j < sizeof($selecteddedups); $j++) {

            //$sqlcondscentre .= " OR id=" . array_shift($selecteddedups) . " ";
                $sqlcondscentre .= " OR id=" . $selecteddedups[$j] . " ";
            //array_shift($selecteddedups);
            }

        // add the first and last elements with an extra or



        }

        $sqlconds = $the_start . $sqlcondscentre . " OR " . $theend;



        //echo $sqlconds;

        $cond = $sqlconds ;

        $this->Pagination->controller = &$this;
        $this->Pagination->show = 30;
        $this->set('Todedups', $this->Bib->findAll(
            $cond, NULL, $this->Pagination->order, $this->Pagination->show,
            $this->Pagination->page
        ));

    }

    function dedup2($id=null) {

    // Grab our bib id's from form parameters....
        $idtokeep = $this->params['form']['idtokeep'];
        $this->set('idtokeep',$idtokeep);

        $idtodel =  $this->params['form']['idtodel'];

        // use split to get all the *to deletes* into an array
        $tomergeids = split("#", $idtodel);

        // last element is always blank, so pop it off

        array_pop($tomergeids);

        $this->set('tomergeids',$tomergeids);

        // Update holdings and bids where id = $tomergeids and set all fkeys to idtokeep

        // loop through array of ids to merge
        foreach ($tomergeids as $tomergeid ) {

        // Get each bid where bib_id = $tomergeid and change the fkey
            $bidstochange = $this->Bid->findall(array('bib_id'=>$tomergeid));
            foreach ($bidstochange as $bidtochange) {
                $bidtochange['Bid']['orig_bib_id'] = $bidtochange['Bid']['bib_id'];
                $bidtochange['Bid']['bib_id'] = $idtokeep;
                $this->Bid->save($bidtochange['Bid']);
            }

            // Add id of record merged with (new master) to field new_id
            // Deprecate all merged records (except new main one) so it doesn't show in searching etc...

            $changerecs = $this->Bib->findall('`id`=' . $tomergeid,null,null,null,null,-1);
            foreach ($changerecs as $changerec) {

                $changerec['Bib']['depreciated'] = 1;
                $changerec['Bib']['new_id'] = $idtokeep;
                $bersvalue = $this->Bib->save($changerec['Bib']);


            }

            // repeat for holdings
            $holdingstochange = $this->Holding->findall('`bib_id`=' . $tomergeid,null,null,null,null,-1);
            foreach ($holdingstochange as $holdingtochange ) {
                $holdingtochange['Holding']['orig_bib_id'] = $holdingtochange['Holding']['bib_id'];
                $holdingtochange['Holding']['bib_id'] = $idtokeep;
                $bersvalue = $this->Holding->save($holdingtochange['Holding']);

            }



        }


        // Flash - record has been deduped - pass to view for record to keep!!
        $this->flash('Records have been merged (Click here to continue)',"/bibs/view/$idtokeep");
    }


    function undup($id=null) {

    // Grab our bib id's from record to be unduped
        $idofmergedrec = $this->params['form']['idofmergedrec'];
        $this->set('idofmergedrec',$idofmergedrec);

        // reset bids where orig_bib_id = idofmergedrec

        $bidstoreset = $this->Bid->findall(array('bib_id'=>$idofmergedrec));
        foreach ($bidstoreset as $bidtoreset ) {
            if ($bidtoreset['Bid']['orig_bib_id'] != NULL) {
                $bidtoreset['Bid']['bib_id'] = $bidtoreset['Bid']['orig_bib_id'];
                $bidtoreset['Bid']['orig_bib_id'] = NULL;
                $this->Bid->save($bidtoreset['Bid']);
            }
        }


        // repeat for holdings
        $holdingstochange = $this->Holding->findall(array('bib_id'=>$idofmergedrec));
        foreach ($holdingstochange as $holdingtochange ) {
            if ($holdingtochange['Holding']['orig_bib_id'] != NULL) {
                $holdingtochange['Holding']['bib_id'] = $holdingtochange['Holding']['orig_bib_id'];
                $holdingtochange['Holding']['orig_bib_id'] = NULL;
                $bersvalue = $this->Holding->save($holdingtochange['Holding']);
            }

        }


        // reset new_id
        // Undeprecate all merged records (except new main one) so it doesn't show in searching etc...

        $changerecs = $this->Bib->findall(array('new_id'=>$idofmergedrec));
        foreach ($changerecs as $changerec) {

            $changerec['Bib']['depreciated'] = NULL;
            $changerec['Bib']['new_id'] = NULL;
            $bersvalue = $this->Bib->save($changerec['Bib']);


        }



        // Flash - record has been unduped - pass to view for record to keep!!
        $this->flash('Records and bids have been split (Click here to continue)',"/bibs/view/$idofmergedrec");
    }





    //**********************

    function index() {
    // Totally ripped from http://mho.ath.cx/~cake/exams/filter/
    // BS recursive = 1 seems to indicate it gets related records by foreign key too
        $this->Bib->recursive = 1;
        //var_dump($this->Bib->Field('title'));
        // EC Amendment Condition to filter depreciated material. has been added to filter component (components/filter.php)


        /* Setup filters */
        $this->Filter->init($this, 'Bib');

        $this->Filter->setFilter(aa('id',
            'Title ID'), NULL, a('=','!=', '>', '<') );

        $this->Filter->setFilter(aa('t245stripped', 'Title'), NULL, a(
            '~','^','!~', '='));

        $this->Filter->setFilter(aa('210', 'Abbreviated title'), NULL, a(
            '~','^','!~', '='));


        $this->Filter->setFilter(aa('pub',
            'Publisher'), NULL, a( '~','^','!~', '='));

        $this->Filter->setFilter(aa('match_basis', 'Match method (022, OCLC, TITLE, NO MATCH)'), NULL,
            a('=', '~'));

        $this->Filter->setFilter(aa('places',
            'Place'), NULL, a( '~','^','!~', '='));

        $this->Filter->setFilter(aa('subjects',
            'Subject'), NULL, a( '~','^','!~', '='));

        $this->Filter->setFilter(aa('found_match',
            'Duplicate'), NULL, a('='));

        // Filter for field 035, which includes the BHL-Title ID
        var_export( $this->Filter->setFilter(aa('Holding.035', 'BHL TitleID'), NULL, a('=')), true );

        $this->Filter->filter($f, $cond);
        $this->set('filters', $f);

        //die( var_export( $cond, true ) );

        //$this->Bib->hasMany['Holding']['conditions'] = " `035` LIKE '(BHLTID)%4231%' ";
        /*$this->Bib->bindModel(array(
                'hasOne' => array(
                        'CheckHolding' => array(
                                'className' => 'Holding',
                                'fields' => 'CheckHolding.035 AS hold_035',
                                'type' => 'inner'
                        )
                )
                ), false);
        /*$data = $this->User->find('all', array(
                'group' => 'User.id',
                'recursive' => 0, // At least 0 for joins to work
                'conditions' => array(
                        'num_documents >=' => 3
                ),
                'order' => 'num_documents DESC'
        ));*/

        //$cond = "hold_035 LIKE '(BHLTID)%4231%'";

        $cond = array( "Holding.035" => "LIKE '(BHLTID)%4231%'" );

        $ids_result = $this->Bib->Holding->findAll( $cond, array('Holding.bib_id'), null, null, 1, -1 );

        $ids = array();
        foreach( $ids_result as $row ) {
            $ids[] = $row['bib_id'];
        }

        $this->set( 'Bibs', $this->Bib->findAll( array('Bib.id' => $ids ) ) );


        //$cond = "";

        /* Setup pagination */
        //$this->Pagination->controller = &$this;
        //$this->Pagination->show = 30;

        /*$this->Pagination->init(
            $cond, 'Bib', NULL, array('bibs.id', 'newtitle_b', '260', '210', 'match_basis', 'places', 'subjects', 'found_match'), 0
        );*/
        //'depreciated is NULL'
        /*$this->set('Bibs', $this->Bib->findAll(
            $cond,'',$this->Pagination->order, $this->Pagination->show,
            $this->Pagination->page, 1
        ));*/

        //$this->set( 'Bibs', $this->Bib->findAll($cond, null, null, null, 1, 2 ) );

    //var_dump($this->Bib->holdings);
    }


    //**********************




    function createReport() {
        if (!empty($this->data)) {
        //Determine if user is pulling existing report or deleting report
            if(isset($this->params['form']['existing'])) {
                if($this->params['form']['existing']=='Pull') {
                //Pull report
                    $this->Report->pull_report($this->data['Misc']['saved_reports']);
                }
                else if($this->params['form']['existing']=='Delete') {
                    //Delete report
                        $this->Report->delete_report($this->data['Misc']['saved_reports']);

                        //Return user to form
                        $this->flash('Your report has been deleted.','/'.$this->name.'/'.$this->action.'');
                    }
            }
            else {
            //Filter out fields
                $this->Report->init_display($this->data);

                //Set sort parameter
                if(!isset($this->params['form']['order_by_primary'])) { $this->params['form']['order_by_primary']=NULL; }
                if(!isset($this->params['form']['order_by_secondary'])) { $this->params['form']['order_by_secondary']=NULL; }
                $this->Report->get_order_by($this->params['form']['order_by_primary'], $this->params['form']['order_by_secondary']);

                //Store report name
                if(!empty($this->params['form']['report_name'])) {
                    $this->Report->save_report_name($this->params['form']['report_name']);
                }

                //Store report if save was executed
                if($this->params['form']['submit']=='Create And Save Report') {
                    if(empty($this->params['form']['report_name'])) {
                    //Return user to form
                        $this->flash('Must enter a report name when saving.','/'.$this->name.'/'.$this->action.'');
                    }
                    else {
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
        else {
        //Setup options for report component
            /*
                You can setup a level two association by doing the following:
                "VehicleDriver"=>"Employee" ie $models = Array ("Vehicle", "VehicleDriver"=>"Employee");
                Please note that all fields within a level two association cannot be sorted.
            */
            $models =    Array ('Bib');

            //Set array of fields
            $this->set('report_form', $this->Report->init_form($models));

            //Set current controller
            $this->set('cur_controller', $this->name);

            //Pull all existing reports
            $this->set('existing_reports', $this->Report->existing_reports());
        }
    }


    function edit($id = null) {
        if (empty($this->data)) {
            $this->Bib->id = $id;
            $this->data = $this->Bib->read();
            $this->set('bib',$this->Bib->read());
        } else {
            if ($this->Bib->save($this->data['Bib'])) {
                $this->flash('The bib record has been
                                updated (Click here to continue)','/bibs/dedupselect');
            }
        }
    }




}

?>