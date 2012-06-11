<?php
class Publisher extends AppModel
{
var $name = 'Publisher';
var $recursive = 2;





				      var $validate = array(

				        'name'  => VALID_NOT_EMPTY

				    );



 function beforeSave()
    {// Common function to deal with saving of dates when passed from a form - always called before any save...


      if ($this->data['Publisher']['partial']==1)
       {
        $this->data['Publisher']['startdate'] = $this->_getYear('Publisher', 'startdate');
        $this->data['Publisher']['enddate'] = $this->_getYear('Publisher', 'enddate');
        return true;
       }
       else
       {return true;}
    }

    function _getYear($model, $field)
    {
        /* BUG -- ._year breaks on de-dup process, as data does not come from formhelper posts...
         Either hack it with an extra flag to be noticed in the IF statement above OR
         use type conversion TYPE CONVERSION REQUIRED HERE>>> - take a string value initally
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