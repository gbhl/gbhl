<?php
class Bid extends AppModel
{
var $name = 'Bid';
var $recursive = 2;
var $belongsTo = array(
			    'Bib' =>array('className'  => 'Bib',
                                 'conditions' => '',
                                 'order'      => '',
                                 'foreignKey' => 'bib_id'
                           ),

                           'User' =>array('className'  => 'User',
                                 'conditions' => '',
                                 'order'      => '',
                                 'foreignKey' => 'user_id'
                           ),

                           'Status' =>array('className'  => 'Status',
                                 'conditions' => '',
                                 'order'      => '',
                                 'foreignKey' => 'status_id')
                           );


    function beforeSave()
    {// Common function to deal with saving of dates when passed from a form - always called before any save...
      
      
      if ($this->data['Bid']['partial']==1)
       {
        $this->data['Bid']['startdate'] = $this->_getYear('Bid', 'startdate');
        $this->data['Bid']['enddate'] = $this->_getYear('Bid', 'enddate');
        return true;
       }
       else
       {return true;}
    }

    function _getYear($model, $field)
    {
        /* BUG -- ._year breaks on de-dup process, as data does not come from formhelper posts...
        
         Possibly fixed, but still echoing warnings...
         
        */
        if($this->data[$model][$field . '_year']) 
        {    
        return(intval($this->data[$model][$field . '_year']));
        }
        else 
        {
        return(intval($this->data[$model][$field]));    
        }    
    }
}
?>