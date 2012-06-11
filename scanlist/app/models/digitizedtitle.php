<?php
class Digitizedtitle extends AppModel
{
var $name = 'Digitizedtitle';
var $recursive = 2;
var $belongsTo = array(
			    'Bib' =>array('className'  => 'Title',
                                 'conditions' => '',
                                 'order'      => '',
                                 'foreignKey' => 'title_id'
                           ),

                           'User' =>array('className'  => 'User',
                                 'conditions' => '',
                                 'order'      => '',
                                 'foreignKey' => 'user_id'
                           ),

                           'Title' =>array('className'  => 'Title',
                                 'conditions' => '',
                                 'order'      => '',
                                 'foreignKey' => 'title_id'
                           )
                           );





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