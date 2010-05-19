<?php
class AppModel extends Model{
    function __construct($id=false, $table=null, $ds=null)
    {
        parent::__construct($id, $table, $ds);
        if(!defined('SET_NAMES_UTF8'))
        {
            $this->query("SET NAMES 'UTF8'");
            define('SET_NAMES_UTF8', true);
        }
    }

    function unbindAll($params = array())
    {
        foreach($this->__associations as $ass)
        {
            if(!empty($this->{$ass}))
            {
                 $this->__backAssociation[$ass] = $this->{$ass};
                if(isset($params[$ass]))
                {
                    foreach($this->{$ass} as $model => $detail)
                    {
                        if(!in_array($model,$params[$ass]))
                        {
                             $this->__backAssociation = array_merge($this->__backAssociation, $this->{$ass});
                            unset($this->{$ass}[$model]);
                        }
                    }
                }
                else
                {
                    $this->__backAssociation = array_merge($this->__backAssociation, $this->{$ass});
                    $this->{$ass} = array();
                }

            }
        }
        return true;
    }
}
?>