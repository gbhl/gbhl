<?php
class User extends AppModel
{
    var $name = 'User';
    var $hasMany = array(
							'Bid' =>array('className'  => 'Bid',
                                 'conditions' => '',
                                 'order'      => '',
                                 'foreignKey' => 'user_id'
                           ));

    var $belongsTo = 'Group';

    var $recursive = 1;

}
?>
