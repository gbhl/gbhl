<?php
class HoldingsController extends AppController
{
  var $name = 'Holdings';
    var $components = array ('Pagination', 'Filter', 'othAuth'); // Added
    var $helpers = array('Html','Pagination', 'Filter', 'Matchlinker'); // Added
    //var $scaffold;
    var $uses = array('Holding','Bid','Holding','Status','Title');


     function view($id = null) {

        $this->Holding->id = $id;
        $this->set('myholding', $this->Holding->read());

    }

     function viewdirect($id = null) {
        // this is to try and pull up a record by catkey from titles to be scanned page or tell the user if there is not one that matches.


        // pull this from the form input variable
        $catkey = $this->data['Title']['catkey'];
        $place = $this->data['Title']['place'];

        // if there is no catkey supplied via the form, message...
        if (!$catkey)
					{
						$this->flash('You must enter a catkey to search for.',"/titles/addtitle");
						exit;
			}

        // find id using catkey in the *where* SQL criteria which writes the first record to an array
        $myid = $this->Holding->find('`Holding`.`035` like \'%a' . rtrim($catkey) .'\' AND place = \'' . $place . '\'');
        # get just the id field.
        $id = $myid['Holding']['id'];

        if ($id)
        {

        // need extra piece of logic to check the catkey does not already duplicate a title record. If it does, warn and abandon.
        $mytitlecatkey = $this->Title->find('`catkey` = ' . rtrim($catkey));
			if ($mytitlecatkey)
			{
				$this->flash('Title already trawled for! (click here to edit it)','/titles/edit/' . $mytitlecatkey['Title']['id']);
			}
			else # must be a fresh record never considered before
			{
				// use the id to get the right record
				$this->Holding->id = $id;
				$this->set('myholding', $this->Holding->read());
			}

        }
        else
        {
        $this->flash('No matching record. Please enter a new record instead',"/titles/addtitle");
        exit;
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

	    // find the bib_id associated with the catkey so we can pull the 856 for each of its holdings

        $myid = $this->Holding->find('`035` like \'%a' . rtrim($catkey) .'\' ');
        # get just the bib_id field.
        $id = $myid['Holding']['id'];
		$this->Holding->id = $id;
		$this->set('myholding', $this->Holding->read());


    }






     function edit($id = null)
	{

		if (empty($this->data))
		{
			$this->Holding->id = $id;
			$this->data = $this->Holding->read();
                        $this->set('bid',$this->Holding->read());
		}
		else
		{
			if ($this->Holding->save($this->data['Bid']))
			{
				$this->flash('Your Holdings have been updated.',"/bibs");
			}
		}
	}

  	function delete($id)
	{
        $this->Holding->del($id);
        $this->flash('The holding with id: '.$id.' has been deleted. (Click to continue)', '/bibs/');
  	}

//**********************

        function index() {
                // Totally ripped from http://mho.ath.cx/~cake/exams/filter/
            // BS recursive = 1 seems to indicate it gets related records by foreign key too
            $this->Holding->recursive = 2;
            //var_dump($this->Holding->Field('035'));
           // EC Amendment Condition to filter depreciated material. has been added to filter component (components/filter.php)


        /* Setup filters */
            $this->Filter->init($this, 'Holding');

            $this->Filter->setFilter(aa('id',
            'Record ID'), NULL, a('=','!=', '>', '<') );

            $this->Filter->setFilter(aa('035', 'Control'), NULL, a(
            '~','^','!~', '='));


            $this->Filter->setFilter(aa('place',
            'Place'), NULL, a( '~','^','!~', '='));





            $this->Filter->filter($f, $cond); $this->set('filters', $f);





            /* Setup pagination */
            $this->Pagination->controller = &$this;
            $this->Pagination->show = 30;

            $this->Pagination->init(
                $cond, 'Holding', NULL, array('id', '035', 'place'), 0
            );
                            //'depreciated is NULL'
            $this->set('Holdings', $this->Holding->findAll(
                $cond,'',$this->Pagination->order, $this->Pagination->show,
                $this->Pagination->page
            ));


            //var_dump($this->Bib->holdings);
        }


//**********************




}

?>