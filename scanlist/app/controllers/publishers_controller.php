<?php
class PublishersController extends AppController
{
  var $name = 'Publishers';
    var $components = array ('Pagination', 'Filter', 'othAuth'); // Added
    var $helpers = array('Html','Pagination', 'Filter', 'Matchlinker'); // Added
    //var $scaffold;
    var $uses = array('Publisher','Bid','Publisher','Status','Title');




     function addtitle() {

        // arrays for select options

        $this->set('Agreementstatuses', array(1 =>'Please select', 2=>'Unknown', 3=>'In place', 4=>'None', 5=>'In negotiation'));
        $this->set('Publisherstatuses', array(1 =>'Please select', 2=>'Unknown',3=>'Traced',4=>'Untraceable'));
        $this->set('Watchfilestatuses', array(1 =>'Please select', 2=>'N/A', 3=>'Unknown',4=>'Checked - found',5=>'Checked - not found'));
        $this->set('rightshold_status', array(1 =>'Please select', 2=>'No', 3=>'Yes'));


        if (!empty($this->data['Publisher']))
        {

            if ($this->Publisher->save($this->data['Publisher'])) {
                //var_dump($this->data['Publisher']);
                $this->flash('Your publisher info has been saved.','/publishers/index');
            }
			else
			{
				$this->validateErrors($this->Publisher);
				//And render the edit view code
				$this->render();
			}


        }

    }


     function view($id = null) {

        $this->set('Agreementstatuses', array(1 =>'Please select', 2=>'Unknown', 3=>'In place', 4=>'None', 5=>'In negotiation'));
        $this->set('Publisherstatuses', array(1 =>'Please select', 2=>'Unknown',3=>'Traced',4=>'Untraceable'));
        $this->set('Watchfilestatuses', array(1 =>'Please select', 2=>'N/A', 3=>'Unknown',4=>'Checked - found',5=>'Checked - not found'));
        $this->set('rightshold_status', array(1 =>'Please select', 2=>'No', 3=>'Yes'));

        $this->Publisher->id = $id;
        $this->set('mypublisher', $this->Publisher->read());

    }





     function viewdirectfrompub($id = null) {
        // this is to try and pull up a record by catkey from titles to be scanned page or tell the user if there is not one that matches.


        $publetters = $this->data['Publisher']['name'];

        $this->set('Agreementstatuses', array(1=>'N/K',2=>'Failed',3=>'Succeeded'));
        $this->set('Publisherstatuses', array(1=>'N/K',2=>'Traced',3=>'Untraceable'));
        $this->set('Watchfilestatuses', array(1=>'N/K',2=>'OK',3=>'Not OK'));


        // if there is no publisher supplied via the form, message...
        if (!$publetters)
					{
						$this->flash('You must enter rightsholder text to search for.',"/publishers/addtitle/");
						exit;
			}


        $numberpublishers = $this->Publisher->findCount('`name` like \'%' . rtrim($publetters) . '%\'');
        //$publishers = $this->Publisher->findall('`name` like \'%' . rtrim($publetters) . '%\'');


        if ($numberpublishers ==0)
        {
            $this->flash('Rightsholder does not exist. Create a new one and then link.',"/publishers/addtitle/");
        }
        elseif ($numberpublishers ==1)
        {
			 $publisher = $this->Publisher->find('`name` like \'%' . rtrim($publetters) . '%\'');
			 $id = $publisher['Publisher']['id'];
             $this->Publisher->id = $id;
			 $this->set('mypublisher', $this->Publisher->read());
        }

        else # must be many
        {

			 $publisher = $this->Publisher->findall('`name` like \'%' . rtrim($publetters) . '%\'');
			 $this->set('mypublisher', $publisher);

        }



    }



     function viewdirect($id = null) {
        // this is to try and pull up a record by catkey from titles to be scanned page or tell the user if there is not one that matches.


        $publetters = $this->data['Publisher']['name'];
        $iprid= $this->data['Iprtitle']['title_id'];
        $this->set('IPRidentifier', $iprid);


        $this->set('Agreementstatuses', array(1 =>'Please select', 2=>'Unknown', 3=>'As for publisher', 4=>'In place', 5=>'None', 6=>'In negotiation'));
        $this->set('Publisherstatuses', array(1 =>'Please select', 2=>'Unknown',3=>'Traced',4=>'Untraceable'));
        $this->set('Watchfilestatuses', array(1 =>'Please select', 2=>'N/A', 3=>'Unknown',4=>'Checked - found',5=>'Checked - not found'));
        $this->set('rightshold_status', array(1 =>'Please select', 2=>'No', 3=>'Yes'));


        // if there is no publisher supplied via the form, message...
        if (!$publetters)
					{
						$this->flash('You must enter rightsholder text to search for.',"/iprtitles/edit/" . $iprid);
						exit;
			}


        $numberpublishers = $this->Publisher->findCount('`name` like \'%' . rtrim($publetters) . '%\'');
        //$publishers = $this->Publisher->findall('`name` like \'%' . rtrim($publetters) . '%\'');


        if ($numberpublishers ==0)
        {
            //set error variable
            $therearenoresults = 'Rightsholder *' . $publetters . '* does not exist. Create a new one and then click back button to link to it.';
            $this->set('Therearenoresults', $therearenoresults);
            //$this->redirect("/iprtitles/edit/" . $iprid);
        }
        elseif ($numberpublishers ==1)
        {
			 $publisher = $this->Publisher->find('`name` like \'%' . rtrim($publetters) . '%\'');
			 $pubid = $publisher['Publisher']['id'];
             $this->Publisher->id = $pubid;
			 $this->set('mypublisher', $this->Publisher->read());
        }

        else # must be many
        {

			 $publisher = $this->Publisher->findall('`name` like \'%' . rtrim($publetters) . '%\'');
			 $this->set('mypublisher', $publisher);

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

	    // find the bib_id associated with the catkey so we can pull the 856 for each of its publishers

        $myid = $this->Publisher->find('`035` like \'%a' . rtrim($catkey) .'\' ');
        # get just the bib_id field.
        $id = $myid['Publisher']['id'];
		$this->Publisher->id = $id;
		$this->set('mypublisher', $this->Publisher->read());


    }





     function edit($id = null)
	{


        $this->set('Agreementstatuses', array(1 =>'Please select', 2=>'Unknown', 3=>'In place', 4=>'None', 5=>'In negotiation'));
        $this->set('Publisherstatuses', array(1 =>'Please select', 2=>'Unknown',3=>'Traced',4=>'Untraceable'));
        $this->set('Watchfilestatuses', array(1 =>'Please select', 2=>'N/A', 3=>'Unknown',4=>'Checked - found',5=>'Checked - not found'));
        $this->set('rightshold_status', array(1 =>'Please select', 2=>'No', 3=>'Yes'));


		if (empty($this->data))
		{
			$this->Publisher->id = $id;
			$this->data = $this->Publisher->read();
                        $this->set('mypublisher',$this->Publisher->read());
		}
		else
		{
			if ($this->Publisher->save($this->data['Publisher']))
			{
				$this->flash('Your rightsholder has been updated.',"/publishers/index");
			}
		}
	}




  	function delete($id)
	{
        $this->Publisher->del($id);
        $this->flash('The publisher with id: '.$id.' has been deleted. (Click to continue)', '/bibs/');
  	}

//**********************

        function index() {
                // Totally ripped from http://mho.ath.cx/~cake/exams/filter/
            // BS recursive = 1 seems to indicate it gets related records by foreign key too
            $this->Publisher->recursive = 2;
            //var_dump($this->Publisher->Field('035'));
           // EC Amendment Condition to filter depreciated material. has been added to filter component (components/filter.php)

        $this->set('Publisherstatuses', array(1 =>'None chosen', 2=>'Unknown',3=>'Traced',4=>'Untraceable'));

        /* Setup filters */
            $this->Filter->init($this, 'Publisher');

            $this->Filter->setFilter(aa('id',
            'Record ID'), NULL, a('=','!=', '>', '<') );

            $this->Filter->setFilter(aa('name', 'Name'), NULL, a(
            '~','^','!~', '='));


            $this->Filter->setFilter(aa('publisher_status',
            'Status (3 = Traced, 4 = Untraceable)'), NULL, a( '~','^','!~', '='));





            $this->Filter->filter($f, $cond); $this->set('filters', $f);





            /* Setup pagination */
            $this->Pagination->controller = &$this;
            $this->Pagination->show = 30;

            $this->Pagination->init(
                $cond, 'Publisher', NULL, array('id', 'name', 'publisher_status'), 0
            );
                            //'depreciated is NULL'
            $this->set('Publishers', $this->Publisher->findAll(
                $cond,'',$this->Pagination->order, $this->Pagination->show,
                $this->Pagination->page
            ));


            //var_dump($this->Bib->publishers);
        }


//**********************




}

?>