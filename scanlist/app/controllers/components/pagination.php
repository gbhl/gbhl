<?php

/* $Id$ */

/*
 $this->Pagination->controller = &$this;
 $this->Pagination->init($conds,'Article',
 array('cat','man','q'),
 array('code'=> 'Ref.',
 'name'=> 'Designation',
 'category_id' => 'Categorie',
 'manufacturer_id'=> 'Fabricant'));
*/

class PaginationComponent extends Object
{
    // Configuration/Default variables
    var $style = 'html'; // Choice between html or ajax style
    var $page = 1; // Start Page
    var $show = 10; // Items per page
    var $sortBy = 'id'; // Default sort column
    var $direction = 'asc'; // Default sort order
    var $resultsPerPage = array(2,5,10,20,50,100); // Choices for results per page.

    var $controller = true; // To give access to the controller
    var $order; // Place holder for the sql order string
    var $count;

    function init($conds = null,$ModelClass = '',$prefix = array(),$sort_filter = null,$model_recursive= 0)
    {
        // oth (little fix by olivvv)
        $strpos=false;
        if(defined('CAKE_ADMIN')){
            $strpos = strpos($this->controller->action,CAKE_ADMIN.'_');
        }
        if($strpos === false)
        {
            $function = $this->controller->action;
            $paging['admin_route'] = false;
        }
        else
        {
            $function = substr($this->controller->action,strlen(CAKE_ADMIN.'_'));
            $paging['admin_route'] = true;
            //die(pr($function));

        }

        if(!empty($this->controller->params['pass']))
        {
            $function .= '/'.implode('/',$this->controller->params['pass']);
        }
        //die(pr(CAKE_ADMIN));
        // oth
        $str_prefix = '';
        if(is_array($prefix))
        {
            if(!empty($prefix))
            {
                for($i = 0, $c = count($prefix); $i < $c; $i++)
                {
                    $str_prefix .= $prefix[$i].'='.$this->controller->params['url'][$prefix[$i]].'&';
                }
            }
        }

        $info =  $this->controller->$ModelClass->loadInfo();
        //die(pr($info));
        if(is_array($sort_filter) && !empty($sort_filter))
        {
            foreach($info->value as $inf)
            {
                if(array_key_exists($inf['name'],$sort_filter))
                {
                    $sort_fields[$inf['name']] = $sort_filter[$inf['name']];
                }
                elseif(in_array($inf['name'],$sort_filter))
                {
                    $sort_fields[$inf['name']] = $inf['name'];
                }
            }
        }
        else
        {
            foreach($info->value as $inf)
            {
                $sort_fields[$inf['name']] = $inf['name'];
            }
        }

        $this->_restoreState();
        if (isset($_GET['show'])) {
            $this->show = (int) $_GET['show'];
        }
        if (isset($_GET['page'])) {
            $this->page = (int) $_GET['page'];
        }
        if (isset($_GET['sort']) && isset($sort_fields[$_GET['sort']])) {
            $this->sortBy = $_GET['sort'];
        }
        if (isset($_GET['direction']) && in_array($_GET['direction'], array('asc', 'desc'))) {
            $this->direction = $_GET['direction'];
        }

        if($ModelClass == '') $ModelClass = $this->controller->modelClass;
        $this->order = $ModelClass.".".$this->sortBy.' '.strtoupper($this->direction);

        $count = $this->controller->$ModelClass->findCount($conds,$model_recursive);
        $this->count = $count;
        $this->trimResultsPerPage($count);
        $this->checkPage($count);

        $paging['style'] = $this->style;
        if ($this->style=="ajax") {
            $paging['link'] = '/bare/'.$this->controller->name."/";
        } else {
            $paging['link'] = "";
        }

        $paging['controller'] = Inflector::underscore($this->controller->name); // oth
        $paging['base'] = $function; // oth

        // oth
        if(!$paging['admin_route'])
        {

            $paging['link'] .=
                '/'.$paging['controller'].'/'.$function.
                '/?'.$str_prefix.'show='.$this->show.
                '&sort='.$this->sortBy.'&direction='.$this->direction.'&page=';

        }
        else
        {

            $paging['link'] .=
                '/'.CAKE_ADMIN.'/'.$paging['controller'].'/'.
                $function.'/?'.$str_prefix.'show='.$this->show.
                '&sort='.$this->sortBy.'&direction='.$this->direction.'&page=';

        }

        $paging['sort'] = $this->sortBy;
        $paging['sort_fields'] = $sort_fields;
        $paging['count'] = $count;
        $paging['page'] = $this->page;
        $paging['limit'] = $this->show;
        $paging['show'] = $this->resultsPerPage;

        $this->controller->set('paging',$paging);
        $this->_saveState();
    }

    // Don't give the choice to display pages with no results
    function trimResultsPerPage ($count = 0) {
        while (($limit = current($this->resultsPerPage))&&(!isset($capKey))) {
            if ($limit >= $count) {
                $capKey = key($this->resultsPerPage);
            }
            next($this->resultsPerPage);
            if (isset($capKey)) {
                array_splice($this->resultsPerPage, ($capKey+1));
            }
        }
    }

    // Show last page if page number returns no results.
    function checkPage ($count = 0) {
        if ((($this->page-1)*$this->show)>=$count) {
            $this->page = floor($count/$this->show+0.99);
        }
    }

    /**
     *
     */
    function _saveState()
    {
        $store = array(
            'show' => $this->show,
            'page' => $this->page,
            'sortBy' => $this->sortBy,
            'direction' => $this->direction,
        );
        $this->controller->Session->write(
            get_class($this->controller) . 'pagination', $store
        );
    }

    /**
     *
     */
    function _restoreState()
    {
        $data = $this->controller->Session->read(
            get_class($this->controller) . 'pagination'
        );
        if (isset($data['show'])) {
            $this->show = $data['show'];
            $this->page = $data['page'];
            $this->sortBy = $data['sortBy'];
            $this->direction = $data['direction'];
        }
    }
}

?>