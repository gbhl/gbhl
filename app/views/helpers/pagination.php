<?php

/* $Id$ */

/*
 * TODO:
 *  - rewrite
 */
class PaginationHelper extends Helper {

    var $helpers = array('Html','Ajax');
    var $_pageDetails = array();
    var $link = '';
    var $show = array();
    var $page;
    var $style;
    var $base;
    var $controller;
    var $admin_route = false;
    var $sort_fields;

    /**
     * Sets the default pagination options.
     *
     * @param array $paging an array detailing the page options
     */
    function setPaging($paging)
    {
        if(!empty($paging))
        {
            $this->admin_route = $paging['admin_route'];
            $this->controller = $paging['controller']; // oth
            $this->base = $paging['base']; // oth
            $this->link = $paging['link'];
            $this->show = $paging['show'];
            $this->page = $paging['page'];
            $this->style = $paging['style'];
            $this->sort_fields = $paging['sort_fields'];


            $pageCount = ceil($paging['count'] / $paging['limit'] );

            $this->_pageDetails = array(
                'page'=>$paging['page'],
                'sort'=>$paging['sort'],
                'recordCount'=>$paging['count'],
                'pageCount' =>$pageCount,
                'nextPage'=> ($paging['page'] < $pageCount) ? $paging['page']+1 : '',
                'previousPage'=> ($paging['page']>1) ? $paging['page']-1 : '',
                'limit'=>$paging['limit']
            );
            return true;
        }
        return false;
    }
    /**
     * Displays limits for the query
     *
     * @param string $text - text to display before limits
     * @param string $separator - display a separate between limits
     *
     **/
    function show($text=null, $separator=null)
    {
        if (empty($this->_pageDetails)) { return false; }
        if ( !empty($this->_pageDetails['recordCount']) )
        {
            $t = '';
            if(is_array($this->show))
            {
                $t = $text.$separator;
                foreach($this->show as $value)
                {
                    $link = preg_replace('/show=(.*?)&/','show='.$value.'&',$this->link);
                    if($this->_pageDetails['limit'] == $value)
                    {
                        $t .= '<em>'.$value.'</em>'.$separator;
                    }
                    else
                    {
                        if($this->style == 'ajax')
                        {
                            $t .= $this->Ajax->linkToRemote($value, array("fallback"=>$this->action."#","url" => $link.$this->_pageDetails['page'],"update" => "ajax_update","method"=>"get")).$separator;
                        }
                        else
                        {
                            $t .= $this->Html->link($value,$link.$this->_pageDetails['page']).$separator;
                        }
                    }
                }
            }
            return $t;
        }
        return false;

    }

    function showSelect($text=null)
    {
        if (empty($this->_pageDetails)) { return false; }
        if ( !empty($this->_pageDetails['recordCount']) )
        {
            $t = '';
            if(is_array($this->show))
            {
                $t = $text;
                //die(pr($this->link));

                $link = preg_replace('/show=(.*?)&/',"show='\+this.options\[this.selectedIndex\].value+'&",$this->link);

                $testbase = $this->Html->base;
                /*
                 if($this->admin_route == true)
                 {
                 $testbase .= CAKE_ADMIN.'/';
                 }
                 $link = $testbase.$this->controller.'/'.$link;
                 //die($link);
                 */
                $link = str_replace('\\','',$link);
                $link = $testbase.$link;

                $pages = $link.$this->_pageDetails['page'];
                $onchange = "onchange=\"document.location= '".$pages."'\"";
                $t .= "<select $onchange >";
                foreach($this->show as $value)
                {
                    $link = preg_replace('/show=(.*?)&/','show='.$value.'&',$this->link);
                    if($this->_pageDetails['limit'] == $value)
                    {
                        $t .= "<option value='$value' selected='selected'>".$value."</option>";

                    }
                    else
                    {
                        if($this->style == 'ajax')
                        {
                            $t .= $this->Ajax->linkToRemote($value, array("fallback"=>$this->action."#","url" => $link.$this->_pageDetails['page'],"update" => "ajax_update","method"=>"get"));
                        }
                        else
                        {
                            $t .= "<option value='$value'>".$value."</option>";
                            //$t .= $this->Html->link($value,$link.$this->_pageDetails['page']);
                        }
                    }
                }
                $t .= "</select>";
            }
            return $t;
        }
        return false;

    }

    /**
     * Displays current result information
     *
     * @param string $text - text to preceeding the number of results
     *
     **/
    function result($text)
    {
        if (empty($this->_pageDetails)) { return false; }
        if ( !empty($this->_pageDetails['recordCount']) )
        {
            if($this->_pageDetails['recordCount'] > $this->_pageDetails['limit'])
            {
                $start_row = $this->_pageDetails['page'] > 1 ? (($this->_pageDetails['page']-1)*$this->_pageDetails['limit'])+1:'1';
                $end_row = ($this->_pageDetails['recordCount'] < ($start_row + $this->_pageDetails['limit']-1)) ? $this->_pageDetails['recordCount'] : ($start_row + $this->_pageDetails['limit']-1);
                $t = $text.$start_row.'-'.$end_row.' of '.$this->_pageDetails['recordCount'];
            }
            else
            {
                $t = $text.$this->_pageDetails['recordCount'];
            }
            return $t;
        }
        return false;
    }
    /**
     * Returns a list of page numbers separated by $separator
     *
     * @param string $separator - defaults to null
     *
     **/
    function pageNumbers($pre = null,$post = null,$separator=null)
    {
        if (empty($this->_pageDetails) || $this->_pageDetails['pageCount'] == 1) { return $pre.'1'.$post; }
        $t = array();
        $text = '';
        $pc = 1;
        do
        {
            if($pc == $this->_pageDetails['page'])
            {
                $text = $pre.$pc.$post;
            }
            else
            {
                if($this->style == 'ajax')
                {
                    $text = $this->Ajax->linkToRemote($pc, array("fallback"=>$this->action."#","url" =>$this->link.$pc,"update" => "ajax_update","method"=>"get"));
                }
                else
                {
                    //$text = $this->Html->link($pc,'/'.CAKE_ADMIN.'/'.$this->link.$pc);
                    $text = $this->Html->link($pc,$this->link.$pc);
                }
            }

            $t[] = $text;
            $pc++;
        }
        while ($pc<=$this->_pageDetails['pageCount']);

        $t = implode($separator, $t);

        return $t;
    }

    /**
     * Returns a "Google style" list of page numbers
     *
     * @param string $separator - defaults to null
     * @param string $prefix - defaults to null. If set, displays prefix before page links.
     * @param int $pageSetLength - defaults to 10. Maximum number of pages to show.
     * @param string $prevLabel - defaults to null. If set, displays previous link.
     * @param string $nextLabel - defaults to null. If set, displays next link.
     *
     **/
    function googleNumbers($pre = '<span>',$post = '</span>',$separator=null, $prefix=null, $pageSetLength=10, $prevLabel=null, $nextLabel=null)
    {
        if (empty($this->_pageDetails) || $this->_pageDetails['pageCount'] == 1) { return $pre.'1'.$post; }

        $t = array();

        $modulo = $this->_pageDetails['page'] % $pageSetLength;
        if ($modulo)
        { // any number > 0
            $prevSetLastPage = $this->_pageDetails['page'] - $modulo;
        }
        else
        { // 0, last page of set
            $prevSetLastPage = $this->_pageDetails['page'] - $pageSetLength;
        }
        //$nextSetFirstPage = $prevSetLastPage + $pageSetLength + 1;

        if ($prevLabel) $t[] = $this->prevPage($prevLabel);

        // loops through each page number
        $pageSet = $prevSetLastPage + $pageSetLength;
        if ($pageSet > $this->_pageDetails['pageCount']) $pageSet = $this->_pageDetails['pageCount'];

        for ($pageIndex = $prevSetLastPage+1; $pageIndex <= $pageSet; $pageIndex++)
        {
            if ($pageIndex == $this->_pageDetails['page'])
            {
                $t[] = $pre.$pageIndex.$post;
            }
            else
            {
                if($this->style == 'ajax')
                {
                    $t[] = $this->Ajax->link($pageIndex,$this->link.$pageIndex, array("update" => $this->updateId, 'loading' => "Element.show('".$this->loadingId."');", 'complete' => "Element.hide('".$this->loadingId."');", "method"=>"get"));
                }
                else
                {

                    $t[] = $this->Html->link($pageIndex,$this->link.$pageIndex);
                }
            }
        }

        if ($nextLabel) $t[] = $this->nextPage($nextLabel);

        $t = implode($separator, $t);

        return $prefix.$t;
    }

    /**
     * Displays a link to the previous page, where the page doesn't exist then
     * display the $text
     *
     * @param string $text - text display: defaults to next
     *
     **/
    function prevPage($text='prev',$pre=null, $post=null)
    {
        if (empty($this->_pageDetails)) {  return $pre.$text.$post; }
        if ( !empty($this->_pageDetails['previousPage']) )
        {
            if($this->style == 'ajax')
            {
                $t = $this->Ajax->linkToRemote($text, array("fallback"=>$this->action."#","url" => $this->link.$this->_pageDetails['previousPage'],"update" => "ajax_update","method"=>"get"));
            }
            else
            {
                $t = $this->Html->link($text,$this->link.$this->_pageDetails['previousPage']);
            }
            return $t;
        }
        return $pre.$text.$post;
    }
    /**
     * Displays a link to the next page, where the page doesn't exist then
     * display the $text
     *
     * @param string $text - text to display: defaults to next
     *
     **/
    function nextPage($text='next',$pre=null, $post=null)
    {
        if (empty($this->_pageDetails)) { return $pre.$text.$post; }
        if (!empty($this->_pageDetails['nextPage']))
        {
            if($this->style == 'ajax')
            {
                $t = $this->Ajax->linkToRemote($text, array("fallback"=>$this->action."#","url" => $this->link.$this->_pageDetails['nextPage'],"update" => "ajax_update","method"=>"get"));
            }
            else
            {
                $t = $this->Html->link($text,$this->link.$this->_pageDetails['nextPage']);
            }
            return $t;
        }
        return $pre.$text.$post;
    }

    function SelectSorter($text=null)
    {
        // b0rken! cannot change sorting direction

        if (empty($this->_pageDetails)) { return false; }
        if ( !empty($this->_pageDetails['recordCount']) )
        {
            $t = '';
            if(is_array($this->sort_fields))
            {
                $t = $text;
//                 die(pr($this->link));
                preg_match('/direction=(.*?)&/',$this->link,$sort_dir);

                //$sort_dir = (isset($sort_dir[1]) && in_array($sort_dir[1],array('asc,desc')))?$sort_dir[1]: 'asc';
                if(isset($sort_dir[1]) && in_array($sort_dir[1],array('asc','desc')))
                {
                    $sort_dir = $sort_dir[1];
                }
                else
                {
                    $sort_dir = 'asc';
                }

                // toggle direction
                if($sort_dir == 'asc')
                {
                    $sort_dir = 'desc';
                    $sort_symbol = '(+)';
                }
                else
                {
                    $sort_dir = 'asc';
                    $sort_symbol = '(-)';
                }

                $link = preg_replace('/sort=(.*?)&/',"sort='\+this.options\[this.selectedIndex\].value+'&",$this->link);


                $testbase = $this->Html->base;

                $link = str_replace('\\','',$link);
                $link = $testbase.$link;

                $pages = $link.$this->_pageDetails['page'];
                $onchange = "onchange=\"document.location= '".$pages."'\"";
                $t .= "<select $onchange >";
                foreach($this->sort_fields as $k => $value)
                {
                    $link = preg_replace('/sort=(.*?)&/','sort='.$value.'&',$this->link);
                    if($this->_pageDetails['sort'] == $k)
                    {
                        $curr_link = preg_replace('/sort=(.*?)&/',"sort=$value&",$this->link);
                        $curr_link = preg_replace('/direction=(.*?)&/',"direction=$sort_dir&",$this->link);
                        $t .= "<option value='$k' selected='selected' onclick=\"document.location='".$testbase.$curr_link."'\">".$value.$sort_symbol."</option>";

                    }
                    else
                    {
                        if($this->style == 'ajax')
                        {
                            $t .= $this->Ajax->linkToRemote($value, array("fallback"=>$this->action."#","url" => $link.$this->_pageDetails['page'],"update" => "ajax_update","method"=>"get"));
                        }
                        else
                        {
                            $t .= "<option value='$k'>".$value."</option>";
                            //$t .= $this->Html->link($value,$link.$this->_pageDetails['page']);
                        }
                    }
                }
                $t .= "</select>";
            }
            return $t;
        }
        return false;

    }

    /**
     *
     */
    function sortLink($field, $title = NULL, $default = false)
    {
        if ($title === NULL) {
            $title = $field;
        }

        if ($default) {
            $link = str_replace('sort=' . $this->_pageDetails['sort'],
                'sort=' . $field, $this->link) . $this->_pageDetails['page'];
            $link = preg_replace('/direction=(desc|asc)/', 'direction=asc', $link);

            return $this->Html->link($title, $link, NULL, false, 0);
        }

        preg_match('/direction=(asc|desc)/',$this->link, $dir);
        preg_match('/sort=([a-z_]+)/',$this->link, $col);

        if (empty($col[1]) || $col[1] != $field) {
            $link = str_replace('sort=' . $this->_pageDetails['sort'],
                'sort=' . $field, $this->link) . $this->_pageDetails['page'];
            $link = preg_replace('/direction=(desc|asc)/', 'direction=asc', $link);

            return $this->Html->link($title, $link, NULL, false, 0);
        }

        $title .= $dir[1] == 'asc' ? '&nbsp;&gt;' : '&nbsp;&lt;';
        $dir[1] = $dir[1] == 'asc' ? 'desc' : 'asc';

        $link = $this->link . $this->_pageDetails['page'];
        $link = preg_replace('/direction=(desc|asc)/', 'direction=' . $dir[1], $link);

        return $this->Html->link($title, $link, NULL, false, 0);
    }
}

?>
