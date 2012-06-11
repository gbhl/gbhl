<?php 


class ReportComponent extends Object
{

/**
 * Place holder for the models array.
 *
 * @var array
 * @access public
 */
    var $model = Array();

/**
 * Place holder for the fields.
 *
 * @var array
 * @access public 
 */
    var $columns = Array();


/**
 * Specify DEFAULT folder off root directory to store reports in. 
 *
 * @var string
 * @access public
 */
    var $path ="app/tmp/reports/";

/**
 * Place holder for the report fields. 
 *
 * @var array
 * @access public
 */
    var $report_fields = Array();


/**
 * Place holder for the order by clause. 
 *
 * @var string
 * @access public
 */
    var $order_by = NULL;

/**
 * Place holder for the report name. 
 *
 * @var string
 * @access public
 */
    var $report_name = NULL;


/**
 * Startup - Link the component to the controller.
 *
 * @param controller
 */
    function startup(&$controller)
    {
        // This method takes a reference to the controller which is loading it.
        // Perform controller initialization here.
        $this->controller =& $controller;
    }
 
/**
 * Initialize the report form by creating links to models
 * and storing table meta data.
 *
 * @models array
 */
    function init_form($models)
    {
        foreach($models as $model=> $value) 
        {
            $this-> model = new $value; 
            $columns = $this->model->loadInfo();
            
            //Extract field names from array
            for($j=0; $j<count($columns->value); $j++) 
            {
                $arr[$value][$j]=$columns->value[$j]['name'];
            }
            
            //If two level deep association exists set value
            if(!empty($model)) 
            {
                $arr['associated_table'][$value]=$model;
            }
        }

        return $arr;
    }
 
/**
 * Initializes the report display.
 *
 * @form array
 */
    function init_display($form)
    {
        //get fields that were selected
        $this->report_fields=$this->get_selected($form);

        //sort fields by priority 
        $this->report_fields=$this->sort_fields($this->report_fields);
    }

/**
 * Extracts all selected fields from form.
 *
 * @form array
 */
    function get_selected($form)
    {
        foreach ($form as $model => $field) {
            foreach ($field as $name) {
                if(!empty($name['include'])) {
                    $arr[]=$name;
                }
            }
        }
        return $arr;
    }

/**
 * Sorts all selected fields from form by priority
 * entered (1-left ... 10-right).
 *
 * @fields array
 */
    function sort_fields($fields)
    {
        for ($i=0; $i < sizeof($fields)-1; $i++) 
        {
            for ($j=0; $j<sizeof($fields)-1-$i; $j++)
            {
                if ($fields[$j]['priority'] > $fields[$j+1]['priority']) 
                {
                    $tmp = $fields[$j];
                    $fields[$j] = $fields[$j+1];
                    $fields[$j+1] = $tmp;
                }
            }
        }

        return $fields;
    }

/**
 * Sets up the order by clause.   
 *
 * @primary string
 * @secondary string
 */
    function get_order_by($primary, $secondary) 
    {
        //Store primary sort if exists
        if(!empty($primary)) 
        {
            $this->order_by=$primary;
        
            //Store secondary sort if exists
            if(!empty($secondary)) 
            {
                $this->order_by.=",".$secondary;
            }            
        }
        else 
        {
            $this->order_by=NULL;
        }
    }

/**
 * Saves the newly created report.
 *
 * @order_by string
 */
    function save_report()
    {
        $content='<? $report_fields=Array(';
        for($i=0; $i<count($this->report_fields); $i++)
        {                    
            //get number of elements
            $total=count($this->report_fields[$i]);
            $counter=0;

            $content.='Array(';
            foreach($this->report_fields[$i] as $report_field => $value) 
            {
                $counter++;

                if($total!=$counter)
                {
                    $content.='"'.$report_field.'" => "'.$value.'", ';
                } 
                else
                {
                    $content.='"'.$report_field.'" => "'.$value.'"';
                }
            }

            if(($i+1)==count($this->report_fields)) 
            {
                $content.=')';
            } 
            else 
            {
                $content.='), ';
            }
        }
        $content.=');'; 
        
        $content.='$order_by="'.$this->order_by.'";';
        $content.='$report_name="'.$this->report_name.'"; ?>';
        
        //Create directory if specified one does not already exist
        if(!is_dir($this->path))
        { 
            mkdir($this->path);
        }
            //$_SERVER['DOCUMENT_ROOT']
        $file_name = $this->report_name.".php"; 
        $handle = fopen($this->path.$file_name, 'w');
        fwrite($handle, $content);
        fclose($handle); 
    }

/**
 * Saves report name.
 *
 * @report_name string
 */
    function save_report_name($report_name)
    {
        $this->report_name=$report_name;
    }

/**
 * Pulls listing of existing reports..
 *
 */
    function existing_reports() 
    {
        //create an array to hold directory list
        $results = array();

        //create a handler for the directory
        $handler = opendir($this->path);

        //keep going until all files in directory have been read
        while ($file = readdir($handler)) 
        {

            // if $file isn't this directory or its parent, add it to the results array
            if ($file != '.' && $file != '..')
            {
                $results[$file] = str_replace(".php", "", $file);
            }
        }

        closedir($handler);

        return $results;
    }

/**
 * Pulls field array from existing report..
 *
 * @report string
 */
    function pull_report($report) 
    {
        //Pull file
        require($this->path.$report);
        
        //Store data
        $this->order_by=$order_by;
        $this->report_fields=$report_fields;
        $this->report_name=$report_name;
    }

/**
 * Deletes an existing report..
 *
 * @report string
 */
    function delete_report($report) 
    {
        unlink($this->path.$report);
    }

}


?>