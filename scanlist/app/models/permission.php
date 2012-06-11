<?php
class Permission extends AppModel
{
    var $name = 'Permission';
    var $hasAndBelongsToMany = array('Group' =>
                                      array('className' => 'Group',
                                              'joinTable' => 'groups_permissions'));
}
?>