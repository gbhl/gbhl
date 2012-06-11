<?php
class Group extends AppModel
{
    var $name = 'Group';
    var $hasMany = 'User';
    var $hasAndBelongsToMany = array('Permission' =>
                                      array('className' => 'Permission',
                                              'joinTable' => 'groups_permissions'));
}
?>