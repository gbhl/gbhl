<?php
class CataloguesController extends AppController
{
    var $name = 'Catalogues';
    
    function index() {
        $this->set( 'Catalogue', $this->Catalogue->find( 'all' ) );
    }
}
?>