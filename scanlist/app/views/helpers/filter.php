<?php // -*- mode: php; indent-tabs-mode: t -*-

/* $Id$ */


class FilterHelper extends Helper {
    var $helpers = array();
    var $filters;
    var $currentFilters;
    var $link;
    var $labels = array(
        '~' => 'contains',
        '!~' => 'doesn\'t contain',
        '^' => 'begins with',
        '=' => 'is',
        '!=' => 'is not',
        '<' => 'less than',
        '>' => 'greater than',
        '|' => 'or',
        '&' => 'and',
        'Update' => 'Update',
        'Add filter' => 'Add search filter',
    );

    /**
     *
     *
     *
     *
     **/
    
    function setFiltering($filters)
    {
        if (empty($filters[0])) {
            return '';
        }

        $this->filters = $filters[0];
        $this->currentFilters = $filters[1];
        $this->link = $filters[2];
//         pr($this->currentFilters);

        $buf = '';
        foreach ($this->filters as $field => $def) {
            $buf .= $this->_buildFilter($field, $def);
        }

        return $this->_html($buf);
    }

    /**
     *
     */
    function _html($data)
    {
        $buf = '<form action="' . $this->link . '" method="post" name="filter_form"><div class="filter">';
        $buf .= $this->_js();
        $buf .= '<table class="filter">' . $data;
        $buf .= $this->_controls() . '</table>';
        $buf .= '<input type="hidden" name="filter_request" value="1" /></div></form>';

        return $buf;
    }

    /**
     *
     */
    function _controls()
    {
        $buf = '<tr><td colspan="4">' . $this->labels['Add filter'] . ': ';
        $buf .= '<select name="filter[add][]" onchange="this.form.submit()"><option value=""></option>';
        foreach ($this->filters as $k => $v) {
            $buf .= sprintf('<option value="%s">%s</option>',
                htmlspecialchars($k), htmlspecialchars($v['label']));
        }
        $buf .= '</select><input type="submit" value="' . $this->labels['Update'] . '" />';

        return $buf;
    }

    /**
     *
     */
    function _buildFilter($field, $def)
    {
        if (empty($this->currentFilters[$field])) {
            return '';
        }

        $types = $this->currentFilters[$field]['types'];
        $values = $this->currentFilters[$field]['values'];

        $buf = ''; $i = 0;
        foreach ($types as $id => $type) {
            $buf .= '<tr>';
            if (! $i) {
                $buf .= '<td class="filter-field">' . $this->filters[$field]['label'] . '</td>';
            }
            else {
                if ($i == 1) {
                    $buf .= '<td class="filter-join-select">' . $this->_joinTypeSelect($field) . '</td>';
                }
                else {
                    $buf .= '<td class="filter-join">' . $this->_joinType($field) . '</td>';
                }
            }
            $buf .= '<td class="filter-type">';
            $i++;

            /* Give user chance to select which type of filter he need */
            if (count($this->filters[$field]['types']) > 1) {
                $buf .= sprintf('<select name="filter[type][%s][%d]" class="filter-type">' . "\n",
                    $field, $id);

                foreach ($this->filters[$field]['types'] as $k => $v) {
                    $buf .= sprintf('<option value="%s"%s>%s</option>' . "\n",
                        $k,
                        $type == $k ? ' selected="selected"' : '',
                        $this->labels[$k]
                    );
                }
                $buf .= '</select>' . "\n";
            }

            /* Only one type of filter is defined */
            else {
                $buf .= sprintf('<input type="hidden" name="filter[type][%s][%d]" value="%s" />',
                    $field, $id, key($this->filters[$field]['types']));
                $buf .= sprintf('<span class="filter-type">%s</span>',
                    $this->labels[key($this->filters[$field]['types'])]);
            }

            $buf .= '</td><td class="value">';

            $value = $values[$id];

            /* Only predefined values */
            if (count($this->filters[$field]['values'])) {
                $buf .= sprintf('<select name="filter[value][%s][%d]" class="filter-value">' . "\n",
                    $field, $id);

                foreach ($this->filters[$field]['values'] as $k => $v) {
                    $buf .= sprintf('<option value="%s"%s>%s</option>' . "\n",
                        htmlspecialchars($k),
                        $value == $k ? ' selected="selected"' : '',
                        htmlspecialchars($v)
                    );
                }
                $buf .= '</select>' . "\n";
            }

            /* Custom query */
            else {
                $buf .= sprintf('<input type="text" name="filter[value][%s][%d]" value="%s" />',
                    $field, $id, htmlspecialchars($value));
            }

            $buf .= '</td><td class="button">';
            $buf .= sprintf('<input type="submit" name="filter[remove][%s][%d]" ' .
                'onmousedown="document.getElementById(&quot;filter-rm-clicked&quot).value = 1;" value="-" />',
                $field, $id);
            $buf .= '</td></tr>';

        }

        return $buf;
    }

    /**
     *
     */
    function _joinType($field)
    {
        return $this->labels[$this->currentFilters[$field]['op']];
    }

    /**
     *
     */
    function _joinTypeSelect($field)
    {
        $buf = sprintf('<select name="filter[join][%s]" class="filter-join">',
            $field);

        $buf .= sprintf('<option value="%s"%s>%s</option>',
            '&amp;',
            $this->currentFilters[$field]['op'] == '&' ? ' selected="selected"' : '',
            htmlspecialchars($this->labels['&'])
        );

        $buf .= sprintf('<option value="%s"%s>%s</option>',
            '|',
            $this->currentFilters[$field]['op'] == '|' ? ' selected="selected"' : '',
            htmlspecialchars($this->labels['|'])
        );

        return $buf;
    }

    /**
     *
     */
    function _js()
    {
        $buf = '<input type="hidden" name="filter[js_enabled]" id="filter-js-enabled" value="0" />' . "\n";
        $buf .= '<input type="hidden" name="filter[rm_clicked]" id="filter-rm-clicked"  value="0" />' . "\n";
        $buf .= '<script type="text/javascript"><!--' . "\n";
        $buf .= 'document.getElementById("filter-js-enabled").value = 1;' . "\n";
        $buf .= '// --></script>' . "\n";

        return $buf;
    }

}

?>