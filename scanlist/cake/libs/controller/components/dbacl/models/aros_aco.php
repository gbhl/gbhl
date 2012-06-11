<?php
/* SVN FILE: $Id: aros_aco.php 4009 2006-11-28 10:19:40Z phpnut $ */
/**
 * Short description for file.
 *
 * Long description for file
 *
 * PHP versions 4 and 5
 *
 * CakePHP :  Rapid Development Framework <http://www.cakephp.org/>
 * Copyright (c)	2006, Cake Software Foundation, Inc.
 *								1785 E. Sahara Avenue, Suite 490-204
 *								Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright		Copyright (c) 2006, Cake Software Foundation, Inc.
 * @link				http://www.cakefoundation.org/projects/info/cakephp CakePHP Project
 * @package			cake
 * @subpackage		cake.cake.libs.controller.components.dbacl.models
 * @since			CakePHP v 0.10.0.1232
 * @version			$Revision: 4009 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2006-11-28 04:19:40 -0600 (Tue, 28 Nov 2006) $
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * Short description for file.
 *
 * Long description for file
 *
 * @package		cake
 * @subpackage	cake.cake.libs.controller.components.dbacl.models
 */
class ArosAco extends AppModel {
/**
 * Cache Queries
 *
 * @var boolean
 */
	var $cacheQueries = false;
/**
 * Model name
 *
 * @var string
 */
	 var $name = 'ArosAco';
/**
 * Table this model uses
 *
 * @var string
 */
	 var $useTable = 'aros_acos';
/**
 * Belongs to association
 *
 * @var array
 */
	 var $belongsTo = array('Aro', 'Aco');
}
?>