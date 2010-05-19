<?php

class Title extends AppModel
{
   var  $name = 'Title';
   var  $recursive = 2;

   var $hasMany = array(

                           'User' =>array('className'  => 'User',
                                 'conditions' => '',
                                 'order'      => '',
                                 'foreignKey' => 'title_id'
                           ),
                           'Holding' =>array('className'  => 'Holding',
                                 'conditions' => '',
                                 'order'      => '',
                                 'foreignKey' => 'title_id'
                           ),
                           'Packinglistline' =>array('className'  => 'Packinglistline',
                                 'conditions' => '',
                                 'order'      => '',
                                 'foreignKey' => 'title_id'
                           ),
                           'Problemlistline' =>array('className'  => 'Problemlistline',
                                 'conditions' => '',
                                 'order'      => '',
                                 'foreignKey' => 'title_id'
                           )



                                );



    var $hasOne = array('Digitizedtitle' =>array('className'  => 'Digitizedtitle',
                                 'conditions' => '',
                                 'order'      => '',
                                 'foreignKey' => 'title_id'
                           ),

                       'Iprtitle' =>array('className'  => 'Iprtitle',
					                                    'conditions' => '',
					                                    'order'      => '',
					                                    'foreignKey' => 'title_id'
                           )


                  );


				      var $validate = array(

				        'catkey'  => VALID_NOT_EMPTY,
				        'title'   => VALID_NOT_EMPTY
				    );


}

?>