<?php
class Holding extends AppModel {
    var $name = 'Holding';
    var $recursive = 2;
    var $belongsTo = array(
            'Bib' =>array('className'  => 'Bib',
                            'conditions' => '',
                            'order'      => '',
                            'foreignKey' => 'bib_id'
    ));


    function beforeSave() {// Common function to deal with saving of dates when passed from a form - always called before any save...


        if ($this->data['Holding']['partial']==1) {
            $this->data['Holding']['startdate'] = $this->_getYear('Holding', 'startdate');
            $this->data['Holding']['enddate'] = $this->_getYear('Holding', 'enddate');
            return true;
        }
        else {
            return true;
        }
    }

    function _getYear($model, $field) {
        /* BUG -- ._year breaks on de-dup process, as data does not come from formhelper posts...
         Either hack it with an extra flag to be noticed in the IF statement above OR
         use type conversion TYPE CONVERSION REQUIRED HERE>>> - take a string value initally
        */
        if($this->data[$model][$field . '_year']) {
            return(intval($this->data[$model][$field . '_year']));
        }
        else {
            return(intval($this->data[$model][$field]));
        }
    }
}

