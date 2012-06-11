<?php
class TitleleadersController extends AppController
{
  var $name = 'Titleleaders';
    var $components = array ('Pagination', 'Filter', 'othAuth'); // Added
    var $helpers = array('Html','Pagination', 'Filter', 'Matchlinker'); // Added
    //var $scaffold;
    var $uses = array('Titleleader','Bid','Itemlink','Status','Title');




     function addtitle() {

        // arrays for select options

        $this->set('Agreementstatuses', array(1 =>'Please select', 2=>'Unknown', 3=>'In place', 4=>'None', 5=>'In negotiation'));
        $this->set('Titleleaderstatuses', array(1 =>'Please select', 2=>'Unknown',3=>'Traced',4=>'Untraceable'));
        $this->set('Watchfilestatuses', array(1 =>'Please select', 2=>'N/A', 3=>'Unknown',4=>'Checked - found',5=>'Checked - not found'));
        $this->set('rightshold_status', array(1 =>'Please select', 2=>'No', 3=>'Yes'));


        if (!empty($this->data['Titleleader']))
        {

            if ($this->Titleleader->save($this->data['Titleleader'])) {
                //var_dump($this->data['Titleleader']);
                $this->flash('Your titleleader info has been saved.','/titleleaders/index');
            }
			else
			{
				$this->validateErrors($this->Titleleader);
				//And render the edit view code
				$this->render();
			}


        }

    }


     function view($id = null) {

        //$this->set('Agreementstatuses', array(1 =>'Please select', 2=>'Unknown', 3=>'In place', 4=>'None', 5=>'In negotiation'));
        //$this->set('Titleleaderstatuses', array(1 =>'Please select', 2=>'Unknown',3=>'Traced',4=>'Untraceable'));
        //$this->set('Watchfilestatuses', array(1 =>'Please select', 2=>'N/A', 3=>'Unknown',4=>'Checked - found',5=>'Checked - not found'));
        //$this->set('rightshold_status', array(1 =>'Please select', 2=>'No', 3=>'Yes'));

        $this->Titleleader->id = $id;
        $this->set('mytitleleader', $this->Titleleader->read());

    }





     function viewdirectfrompub($id = null) {
        // this is to try and pull up a record by catkey from titles to be scanned page or tell the user if there is not one that matches.


        $publetters = $this->data['Titleleader']['name'];

        $this->set('Agreementstatuses', array(1=>'N/K',2=>'Failed',3=>'Succeeded'));
        $this->set('Titleleaderstatuses', array(1=>'N/K',2=>'Traced',3=>'Untraceable'));
        $this->set('Watchfilestatuses', array(1=>'N/K',2=>'OK',3=>'Not OK'));


        // if there is no titleleader supplied via the form, message...
        if (!$publetters)
					{
						$this->flash('You must enter rightsholder text to search for.',"/titleleaders/addtitle/");
						exit;
			}


        $numbertitleleaders = $this->Titleleader->findCount('`name` like \'%' . rtrim($publetters) . '%\'');
        //$titleleaders = $this->Titleleader->findall('`name` like \'%' . rtrim($publetters) . '%\'');


        if ($numbertitleleaders ==0)
        {
            $this->flash('Rightsholder does not exist. Create a new one and then link.',"/titleleaders/addtitle/");
        }
        elseif ($numbertitleleaders ==1)
        {
			 $titleleader = $this->Titleleader->find('`name` like \'%' . rtrim($publetters) . '%\'');
			 $id = $titleleader['Titleleader']['id'];
             $this->Titleleader->id = $id;
			 $this->set('mytitleleader', $this->Titleleader->read());
        }

        else # must be many
        {

			 $titleleader = $this->Titleleader->findall('`name` like \'%' . rtrim($publetters) . '%\'');
			 $this->set('mytitleleader', $titleleader);

        }



    }



     function viewdirect($id = null) {
        // this is to try and pull up a record by catkey from titles to be scanned page or tell the user if there is not one that matches.


        $publetters = $this->data['Titleleader']['name'];
        $iprid= $this->data['Iprtitle']['title_id'];
        $this->set('IPRidentifier', $iprid);


        $this->set('Agreementstatuses', array(1=>'N/K',2=>'Failed',3=>'Succeeded'));
        $this->set('Titleleaderstatuses', array(1=>'N/K',2=>'Traced',3=>'Untraceable'));
        $this->set('Watchfilestatuses', array(1=>'N/K',2=>'OK',3=>'Not OK'));


        // if there is no titleleader supplied via the form, message...
        if (!$publetters)
					{
						$this->flash('You must enter rightsholder text to search for.',"/iprtitles/edit/" . $iprid);
						exit;
			}


        $numbertitleleaders = $this->Titleleader->findCount('`name` like \'%' . rtrim($publetters) . '%\'');
        //$titleleaders = $this->Titleleader->findall('`name` like \'%' . rtrim($publetters) . '%\'');


        if ($numbertitleleaders ==0)
        {
            //set error variable
            $therearenoresults = 'Rightsholder *' . $publetters . '* does not exist. Create a new one and then click back button to link to it.';
            $this->set('Therearenoresults', $therearenoresults);
            //$this->redirect("/iprtitles/edit/" . $iprid);
        }
        elseif ($numbertitleleaders ==1)
        {
			 $titleleader = $this->Titleleader->find('`name` like \'%' . rtrim($publetters) . '%\'');
			 $pubid = $titleleader['Titleleader']['id'];
             $this->Titleleader->id = $pubid;
			 $this->set('mytitleleader', $this->Titleleader->read());
        }

        else # must be many
        {

			 $titleleader = $this->Titleleader->findall('`name` like \'%' . rtrim($publetters) . '%\'');
			 $this->set('mytitleleader', $titleleader);

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

	    // find the bib_id associated with the catkey so we can pull the 856 for each of its titleleaders

        $myid = $this->Titleleader->find('`035` like \'%a' . rtrim($catkey) .'\' ');
        # get just the bib_id field.
        $id = $myid['Titleleader']['id'];
		$this->Titleleader->id = $id;
		$this->set('mytitleleader', $this->Titleleader->read());


    }





     function edit($id = null)
	{


       // $this->set('Agreementstatuses', array(1 =>'Please select', 2=>'Unknown', 3=>'In place', 4=>'None', 5=>'In negotiation'));
       // $this->set('Titleleaderstatuses', array(1 =>'Please select', 2=>'Unknown',3=>'Traced',4=>'Untraceable'));
       // $this->set('Watchfilestatuses', array(1 =>'Please select', 2=>'N/A', 3=>'Unknown',4=>'Checked - found',5=>'Checked - not found'));
       // $this->set('rightshold_status', array(1 =>'Please select', 2=>'No', 3=>'Yes'));


		if (empty($this->data))
		{
			$this->Titleleader->id = $id;
			$this->data = $this->Titleleader->read();
                        $this->set('mytitleleader',$this->Titleleader->read());
		}
		else
		{
			if ($this->Titleleader->save($this->data['Titleleader']))
			{
				$this->flash('Your holdings statement has been updated.',"/titleleaders/index");
			}
		}
	}




  	function delete($id)
	{
        $this->Titleleader->del($id);
        $this->flash('The titleleader with id: '.$id.' has been deleted. (Click to continue)', '/bibs/');
  	}

//**********************

        function index() {
                // Totally ripped from http://mho.ath.cx/~cake/exams/filter/
            // BS recursive = 1 seems to indicate it gets related records by foreign key too
            $this->Titleleader->recursive = 2;
            //var_dump($this->Titleleader->Field('035'));
           // EC Amendment Condition to filter depreciated material. has been added to filter component (components/filter.php)

        //$this->set('Titleleaderstatuses', array(1 =>'None chosen', 2=>'Unknown',3=>'Traced',4=>'Untraceable'));

        /* Setup filters */
            $this->Filter->init($this, 'Titleleader');

            $this->Filter->setFilter(aa('id',
            'Record ID'), NULL, a('=','!=', '>', '<') );

            $this->Filter->setFilter(aa('title_short', 'Title'), NULL, a(
            '~','^','!~', '='));


            $this->Filter->setFilter(aa('manualattnflag',
            'Status (1 = To check)'), NULL, a('='));


            $this->Filter->setFilter(aa('itemcount',
            'No. items'), NULL, a('='));

            $this->Filter->setFilter(aa('contributor', 'Institution'), NULL, a(
            '~','^','!~', '='));



            $this->Filter->filter($f, $cond); $this->set('filters', $f);





            /* Setup pagination */
            $this->Pagination->controller = &$this;
            $this->Pagination->show = 30;

            $this->Pagination->init(
                $cond, 'Titleleader', NULL, array('id', 'title_short', 'manualattnflag', 'itemcount', 'contributor'), 0
            );
                            //'depreciated is NULL'
            $this->set('Titleleaders', $this->Titleleader->findAll(
                $cond,'',$this->Pagination->order, $this->Pagination->show,
                $this->Pagination->page
            ));


            //var_dump($this->Bib->titleleaders);
        }


//**********************




}

?>