<?php
class Itemlink extends AppModel
{
var $name = 'Itemlink';
var $recursive = 2;

  var $hasOne = array(
                            'Titleleader' =>array('className'  => 'Titleleader',
                                 'conditions' => '',
                                 'order'      => '',
                                 'foreignKey' => 'titleleader_id'
                           )

                                );








 function beforeSave()
    {// Common function to deal with saving of dates when passed from a form - always called before any save...


      if ($this->data['Itemlink']['partial']==1)
       {
        $this->data['Itemlink']['startdate'] = $this->_getYear('Itemlink', 'startdate');
        $this->data['Itemlink']['enddate'] = $this->_getYear('Itemlink', 'enddate');
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