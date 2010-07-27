<?php // -*- mode: php; indent-tabs-mode: t -*-

/*--- EC AMENDMENT + ADDED EXTRA ITEM IN CONDITIONS ARRAY + DEPRECIATED is NULL --*/

/* $Id$ */

/*
var $columns = array('primary_key' => array('name' => 'integer primary key'),
    'string' => array('name' => 'varchar', 'limit' => '255'),
    'text' => array('name' => 'text'),
    'integer' => array('name' => 'integer', 'limit' => '11', 'formatter' => 'intval'),
    'float' => array('name' => 'float', 'formatter' => 'floatval'),
    'datetime' => array('name' => 'timestamp', 'format' => 'YmdHis', 'formatter' => 'date'),
    'timestamp' => array('name' => 'timestamp', 'format' => 'YmdHis', 'formatter' => 'date'),
    'time' => array('name' => 'timestamp', 'format' => 'His', 'formatter' => 'date'),
    'date' => array('name' => 'date', 'format' => 'Ymd', 'formatter' => 'date'),
    'binary' => array('name' => 'blob'),
    'boolean' => array('name' => 'integer', 'limit' => '1'));
*/

/*
 * TODO:
 *  - DONE associative array - all arrays
 *  - DONE join operator - OR AND
 *  - helper - filterLink() (filter[type][name][]=^&filter[value][name][]=test&filter_request=1)
 *      use correct index based on current filters
 *  - NULL support
 *  - possibility to filter (rewrite, reformat..) input/output value
 *  - possibility to filter (rewrite, reformat..) column for sql condition
 *      (DATE_FORMAT(..), CONVERT(), ..)
 *  - DONE force icase (using UPPER())
 *  - improve handling of datetime/timestamp where search value date only
 *  - add some javascript to improve user interaction
 */
class FilterComponent extends Object {

    /**
     * Model name on which we will filter records
     */
    var $model;

    /**
     * Controller object in which we will using filter
     */
    var $controller;

    var $dbDriver;
    var $dbObj;

    /**
     * Allowed filters for specific column type
     *  ~ contanins
     *  = equals
     *  ^ begins
     *  < > less/greather than
     *  <= >= less/greather than or equal
     *
     *  = ~ can be prefixed with ! (not equals/contains)
     */
    var $filterType = array(
        'string' => array('^' => 1, '~' => 1, '!~' => 1, '=' => 1, '!=' => 1, ),
        'integer' =>  array('=' => 1, '!=' => 1, '<' => 1, '>' => 1, '<=' => 1, '>=' => 1, ),
        'datetime' => array('=' => 1, '!=' => 1, '<' => 1, '>' => 1, '<=' => 1, '>=' => 1, ),
        'timestamp' => array('=' => 1, '!=' => 1, '<' => 1, '>' => 1, '<=' => 1, '>=' => 1, ),
        'date' => array('=' => 1, '!=' => 1, '<' => 1, '>' => 1, '<=' => 1, '>=' => 1, ),
        'time' => array('=' => 1, '!=' => 1, '<' => 1, '>' => 1, '<=' => 1, '>=' => 1, ),
        'text' => array('^' => 1, '~' => 1, '!~' => 1, '=' => 1, '!=' => 1, ),
    );

    /**
     * Array with defined filters (column => (types => (), values => (), label => label, op => op))
     * Op is operator which can be & for AND, | for OR
     */
    var $filters = array();

    /**
     * Array with model fields (field => type, ..)
     */
    var $modelFields = array();

    /**
     * Currently used filters (column => (type1[, type2..]), (value1[, value2..]))
     */
    var $currentFilters = array();

    var $link;

    /**
     * Whether or not force using UPPER(column) LIKE UPPER('value')
     */
     var $forceIcase = 0;

    /**
     *
     */
    function init(&$controller, $model = NULL, $link = NULL)
    {
        if (empty($controller->uses)) {
            return $this->_debug("Controller '" . get_class($controller) . "' doesn't use any model");
        }

        $uses = is_array($controller->uses) ? $controller->uses : array($controller->uses);
        if ($model) {
            if (! in_array($model, $uses)) {
                return $this->_debug("Model '$model' is not used by controller '" . get_class($controller) . "'");
            }
            $this->model = $model;
        }
        else {
            $this->model = reset($uses);
        }

        $this->controller =& $controller;
        foreach ($this->controller->{$this->model}->_tableInfo->value as $k => $v) {
            $this->modelFields[strtolower($v['name'])] = strtolower($v['type']);
        }
        // Load fields from tables with associations
        foreach( $this->controller->{$this->model}->hasMany as $subModel => $properties ) {
            foreach( $this->controller->{$this->model}->{$subModel}->_tableInfo->value as $k => $v ) {
                $this->modelFields[strtolower($subModel . "." . $v['name'])] = strtolower($v['type']);
            }
        }

        //die( var_export( $this->modelFields, true ) );

        $this->_restoreState();
//      $this->currentFilters = array();


        if ($link !== NULL) {
            $this->link = $link;
        }
        else {

            $this->link = $this->controller->webroot;
            $this->link .= ! empty($this->controller->params['admin'])
                ? $this->controller->params['admin'] . '/' : '';
            $this->link .= ! empty($this->controller->params['url']['url'])
                ? $this->controller->params['url']['url'] : '';
        }

        /* Determine database connection and driver */
        $obj =& ConnectionManager::getInstance();
        $vars = get_object_vars($obj->config);
        $this->dbDriver = $vars[$this->controller->{$this->model}->useDbConfig]['driver'];
        $this->dbObj =& ConnectionManager::getDataSource($this->controller->{$this->model}->useDbConfig);

        return true;
    }

    /**
     *
     */
    function setFilter($col, $values = NULL, $type = NULL)
    {
        if (is_array($col)) {
            list($col, $label) = each($col);
        }
        else {
            $label = $col;
        }
        $col = strtolower($col);

        if (! isset($this->modelFields[$col])) {
            return $this->_debug("Field '$col' doesn't exists in model '{$this->model}'");
        }

        if (! $this->_checkTypes($col, $type)) {
            return false;
        }

        $this->filters[$col] = array(
            'label' => $label,
            'types' => array_flip($type),
            'values' => is_array($values) ? $values : array()
        );
    }

    /**
     *
     */
    function filter(&$filters, &$conditions)
    {
        if (! empty($_REQUEST['filter_request'])) {
            $this->_updateFilters();
        }
//      pr($this->currentFilters);
//      pr($this->filters);

//      $this->currentFilters = array();

        $conditions = $this->_buildSQLCondition();
        $filters = array($this->filters, $this->currentFilters, $this->link);
//      pr($filters);
    }

    /**
     *
     */
    function _updateFilters()
    {
        if (empty($_REQUEST['filter'])) {
            return;
        }
        $filters = $_REQUEST['filter'];

        /* Add / update existing filters */
        if (isset($filters['type']) && isset($filters['value'])) {
            foreach ($filters['type'] as $field => $types) {
                if (! isset($this->filters[$field])) {
                    $this->_debug("There are no defined rules for filtering by field '$field'");
                    continue;
                }

                $values = isset($filters['value'][$field]) ? $filters['value'][$field] : array();

                foreach ($types as $id => $type) {
                    if (! isset($this->filters[$field]['types'][$type])) {
                        $this->_debug("There are not defined rule for filtering field '$field' by type '$type'");
                        continue;
                    }

                    $value = isset($values[$id]) ? $values[$id] : false;

                    if (count($this->filters[$field]['values'])
                        && ! isset($this->filters[$field]['values'][$value])) {
                        $this->_debug("Custom values for field '$field' are not allowed");
                        continue;
                    }

                    $this->currentFilters[$field]['types'][$id] = $type;
                    $this->currentFilters[$field]['values'][$id] = $value;

                }
                $this->currentFilters[$field]['op'] = isset($filters['join'][$field])
                    ? $filters['join'][$field] : '&';
            }
        }

        /* Request to add filter */
        if (! empty($filters['add'])) foreach ($filters['add'] as $field) {
            if (! isset($this->filters[$field])) {
                continue;
            }
            if (! isset($this->currentFilters[$field])) {
                $this->currentFilters[$field] = array(
                    'types' => array(false),
                    'values' => array(false)
                );
            }
            else {
                $this->currentFilters[$field]['types'][] = false;
                $this->currentFilters[$field]['values'][] = false;
            }

        }

        /* Request to remove filter */
        if (! empty($filters['remove'])
            && (! $filters['js_enabled'] || ($filters['js_enabled'] && $filters['rm_clicked'])))
            foreach ($filters['remove'] as $field => $values) {
                foreach ($values as $id => $tmp) {
                    if (isset($this->currentFilters[$field]['types'][$id])) {
                        unset($this->currentFilters[$field]['types'][$id]);
                        unset($this->currentFilters[$field]['values'][$id]);
                    }
                }
                if (empty($this->currentFilters[$field]['types'])) {
                    unset($this->currentFilters[$field]);
                }
            }

        $this->_saveState();
    }

    /**
     *
     */
    function _buildSQLCondition()
    {
        $conditions = array();

        foreach ($this->currentFilters as $field => $def) {
            if ($str = $this->_buildSQLConditionPartial($field, $def)) {
                $conditions[] = $str;
            }
        }
          // EC AMENDMENT -- Add permenant filter on depreciated is null here...

          $conditions[] = 'depreciated is NULL';
        if ($conditions) {
            return '(' . join(' AND ', $conditions) . ')';
        }

        return NULL;
    }

    /**
     *
     */
    function _buildSQLConditionPartial($field, $def)
    {
        $conditions = array();

        foreach ($def['types'] as $id => $type) {
            if (! $type) {
                continue;
            }
            $conditions[] = sprintf('%s %s %s',
                $this->__field($field),
                $this->__operator($field, $type),
                $this->__value($field, $type, $def['values'][$id])
            );
        }

        if (count($conditions) > 1) {
            return '(' . join(
                $this->currentFilters[$field]['op'] == '|' ? ' OR ' : ' AND ',
                $conditions
            ) . ')';
        }
        else if (count($conditions)) {
            return current($conditions);
        }

        return '';
    }


    /**
     *
     */
    function _checkTypes($col, &$type)
    {
        if (! isset($this->filterType[$this->modelFields[$col]])) {
            return $this->_debug("Filtering on column '$col' of type '{$this->modelFields[$col]}' is not supported");
        }

        if ($type === NULL) {
            list($k, $v) = each($this->filterType[$this->modelFields[$col]]);
            $type = array($k);
            return true;
        }

        if (! is_array($type)) {
            $type = array($type);
        }

        foreach ($type as $t) {
            if (! isset($this->filterType[$this->modelFields[$col]][$t])) {
                return $this->_debug("Unsupported filter '$t' for column type '{$this->modelFields[$col]}'");
            }
        }

        return true;
    }

    /**
     *
     */
    function _saveState()
    {
        $this->controller->Session->write(
            get_class($this->controller) . 'filter', $this->currentFilters
        );
    }

    /**
     *
     */
    function _restoreState()
    {
        $data = $this->controller->Session->read(
            get_class($this->controller) . 'filter'
        );
        if (! empty($data)) {
            $this->currentFilters = $data;
        }
    }

    /**
     *
     */
    function _debug($msg)
    {
        debug($msg);
        return false;
    }

    /**
     *
     */
    function __field($field)
    {
        if ($this->modelFields[$field] == 'string' && $this->forceIcase && $this->dbDriver != 'postgres') {
            return 'UPPER(' . $this->controller->{$this->model}->escapeField($field) . ')';
        }
        else if( strpos( $field, '.' ) > 0 ) {
            return ucfirst( $field );
        }
        return $this->controller->{$this->model}->escapeField($field);
    }

    /**
     *
     */
    function __operator($field, $type)
    {
        switch ($this->modelFields[$field]) {
            case 'string':
            case 'text':
                if (in_array($type, array('^', '~', '='))) {
                    if ($this->dbDriver == 'postgres') {
                        return 'ILIKE';
                    }
                    return 'LIKE';
                }

                if (in_array($type, array('!~', '!='))) {
                    if ($this->dbDriver == 'postgres') {
                        return 'NOT ILIKE';
                    }
                    return 'NOT LIKE';
                }

                die("Unsupported operator '$type' for string field");
                break;

            case 'integer':
            case 'datetime':
            case 'date':
            case 'time':
                switch ($type) {
                    case '=';
                    return '=';
                    case '!=';
                    return '!=';
                    case '<';
                    return '<';
                    case '>';
                    return '>';
                    case '<=';
                    return '<=';
                    case '>=';
                    return '>=';
                }
                die("Unsupported operator '$type' for {$this->modelFields[$field]} field");

            default:
                die("Cannot create operator from type '$type' on field '$field'");
        }
    }

    /**
     *
     */
    function __value($field, $type, $value)
    {
        switch ($this->modelFields[$field]) {
            case 'string':
            case 'text':
                $value = str_replace('_', '\\_', $value);
                $value = str_replace('%', '\\%', $value);

                switch ($type) {
                    case '=':
                    case '!=':
                        break;

                    case '^':
                        $value .= '%';
                        break;

                    case '~':
                    case '!~':
                        $value = '%' . $value . '%';
                        break;

                    default:
                        die("Unsupported operator '$type' for {$this->modelFields[$field]} field");
                }

                $value = $this->dbObj->value($value);

                if ($this->dbDriver != 'postgres' && $this->forceIcase) {
                    $value = 'UPPER(' . $value . ')';
                }
                return $value;

            case 'integer':
                return (int) preg_replace('/[^0-9]/', '', $value);

            case 'datetime':
            case 'timestamp':
                if (isset($this->dbObj->columns[$field]['formatter'])) {
                    $value = call_user_func(
                        $this->dbObj->columns[$field]['formatter'],
                        $this->dbObj->columns[$field]['format'],
                        strtotime($value)
                    );
                }
                else {
                    $value = date('Y-m-d H:i:s', strtotime($value));
                }
                return $this->dbObj->value($value);

            case 'date':
                if (isset($this->dbObj->columns[$field]['formatter'])) {
                    $value = call_user_func(
                        $this->dbObj->columns[$field]['formatter'],
                        $this->dbObj->columns[$field]['format'],
                        strtotime($value)
                    );
                }
                else {
                    $value = date('Y-m-d', strtotime($value));
                }
                return $this->dbObj->value($value);

            case 'time':
                if (isset($this->dbObj->columns[$field]['formatter'])) {
                    $value = call_user_func(
                        $this->dbObj->columns[$field]['formatter'],
                        $this->dbObj->columns[$field]['format'],
                        strtotime($value)
                    );
                }
                else {
                    $value = date('H:i:s', strtotime($value));
                }
                return $this->dbObj->value($value);
        }
    }
}

?>