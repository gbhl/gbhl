<?php
class Problemlistline extends AppModel
{
var $name = 'Problemlistline';
var $recursive = 2;
var $belongsTo = array(


                           'Title' =>array('className'  => 'Title',
                                 'conditions' => '',
                                 'order'      => '',
                                 'foreignKey' => 'title_id'
                           ),
                           'Problemlist' =>array('className'  => 'Problemlist',
                                 'conditions' => '',
                                 'order'      => '',
                                 'foreignKey' => 'problemlist_id'
                           )

                           );

       // BS must achieve via javascript for now as now way to combine conditions properly in cake
//       var $validate = array(
//
//				        'problemlist_id'  => VALID_NOT_EMPTY,
//				        'title_id'   => VALID_NOT_EMPTY
//				    );



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