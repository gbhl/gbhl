<?php
class Problemlist extends AppModel
{
var $name = 'Problemlist';
var $recursive = 2;



   var $hasMany = array(
				            'Problemlistline' =>array('className'  => 'Problemlistline',
                                 'conditions' => '',
                                 'order'      => '',
                                 'foreignKey' => 'problemlist_id')

                                );








}

?>