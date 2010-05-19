<?php
class Packinglist extends AppModel
{
var $name = 'Packinglist';
var $recursive = 2;



   var $hasMany = array(
				            'Packinglistline' =>array('className'  => 'Packinglistline',
                                 'conditions' => '',
                                 'order'      => '',
                                 'foreignKey' => 'packinglist_id')

                                );








}

?>