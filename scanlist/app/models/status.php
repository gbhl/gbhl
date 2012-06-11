<?php

class Status extends AppModel
{
 var $name = 'Status';
 var $recursive = 2;
 var $hasMany = array(
							'Bid' =>array('className'=>'Bid',
                                 'conditions' => '',
                                 'order'      => '',
                                 'foreignKey' => 'status_id'
                           ));
}


?>