<?php

class Bib extends AppModel
{
   var  $name = 'Bib';
   var  $recursive = 2;

   var $hasMany = array(
				            'Bid' =>array('className'  => 'Bid',
                                 'conditions' => '',
                                 'order'      => '',
                                 'foreignKey' => 'bib_id'
                           ),
                            'Holding' =>array('className'  => 'Holding',
                                 'conditions' => '',
                                 'order'      => '',
                                 'foreignKey' => 'bib_id'
                           )

                                );


				/*      var $validate = array(

				        'title'  => VALID_NOT_EMPTY,
				        '001'   => VALID_NOT_EMPTY,
				        '002'   => VALID_NOT_EMPTY,
				        '008'   => VALID_NOT_EMPTY,
				        '022'   => VALID_NOT_EMPTY,
				        'pub'   => VALID_NOT_EMPTY,
				    );
				*/


}

?>