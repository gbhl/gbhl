<?php
class Bhlmetadata extends AppModel
{
var $name = 'Bhlmetadata';
var $recursive = 2;

 var $hasMany = array(
				          'Itemlink' =>array('className'  => 'Itemlink',
                                 'conditions' => '',
                                 'order'      => '',
                                 'foreignKey' => 'bhlmetadata_id'
                           )

                                );

var $hasOne = array(

					   'Titleleader' =>array('className'  => 'Titleleader',
					  								'conditions' => '',
													'order'      => '',
													'foreignKey' => 'Titleleader_id'
												)


								);



				      var $validate = array(

				        'title_short'  => VALID_NOT_EMPTY

				    );



 function beforeSave()
    {// Common function to deal with saving of dates when passed from a form - always called before any save...


      if ($this->data['Bhlmetadata']['partial']==1)
       {
        $this->data['Bhlmetadata']['startdate'] = $this->_getYear('Bhlmetadata', 'startdate');
        $this->data['Bhlmetadata']['enddate'] = $this->_getYear('Bhlmetadata', 'enddate');
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