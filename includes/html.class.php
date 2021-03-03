<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
class Html {
    /*
      values[] = ['name' =>'', 'value='' ]
     */

    static function select(array $conf, array $values) {
        if (empty($values) || !is_array($values) || !is_array($conf) || empty($conf['name'])) {
            return false;
        }
        !isset($conf['sel']) ? $conf['sel'] = null : null;
        !isset($conf['class']) ? $class = null : $class = ' class="' . $conf['class'] . ' "';
        !isset($conf['onChange']) ? $on_change_submit = null : $on_change_submit = ' onchange="this.form.submit()" ';

        $select = '<select ' . $on_change_submit . $class . 'name="' . $conf['name'] . '">';
        foreach ($values as $value) {
            isset($conf['selected']) && $conf['selected'] == $value['value'] ? $selected = ' selected="selected" ' : $selected = '';
            $select .= '<option ' . $selected . ' value="' . $value['value'] . '">' . $value['name'] . '</option>';
        }
        $select .= '</select>';

        return $select;
    }

    /*
      get_parms['key'] = value
      url = string
      lname = string
      conf = array
     */

    static function link(array $conf, string $url = null, string $s_name = null, array $get_params = null) {
        $opt = '';

        if ($get_params != null) {
            foreach ($get_params as $params_key => $params_value) {
                !isset($params) ? $params = '?' . $params_key . '=' . $params_value : $params .= '&' . $params_key . '=' . $params_value;
            }
        } else {
            $params = '';
        }
        empty($s_name) ? $s_name = $url : null;
        isset($conf['class']) ? $opt .= 'class="' . $conf['class'] . '" ' : null;
        isset($conf['_blank']) ? $opt .= 'target=_blank ' : null;
        isset($conf['id']) ? $opt .= 'id="' . $conf['id'] . '" ' : null;

        return '<a ' . $opt . ' href="' . $url . $params . '">' . $s_name . '</a>';
    }

    static function input(array $conf) {
        $opt = '';

        !empty($conf['id']) ? $opt .= 'id="' . $conf['id'] . '" ' : null;
        !empty($conf['class']) ? $opt .= 'class="' . $conf['class'] . '" ' : null;
        !empty($conf['type']) ? $opt .= 'type="' . $conf['type'] . '" ' : $opt .= ' type="submit" ';
        !empty($conf['name']) ? $opt .= 'name="' . $conf['name'] . '" ' : null;
        !empty($conf['size']) ? $opt .= 'size="' . $conf['size'] . '" ' : null;
        !empty($conf['value']) ? $opt .= 'value="' . $conf['value'] . '" ' : $opt .= ' value="" ';

        if (!empty($conf['label_caption']) && empty($conf['id'])) {
            $label_conf['for'] = $conf['id'];
            !empty($conf['label_class']) ? $label_conf['class'] = $conf['label_class'] : null;
            self::label($label_conf, $conf['label_caption']);
        }
        $input = '<input ' . $opt . ' />';

        return $input;
    }

    static function form(array $conf, string $content) {
        $opt = '';

        isset($conf['id']) ? $opt = 'id="' . $conf['id'] . '" ' : null;
        isset($conf['class']) ? $opt = 'class="' . $conf['class'] . '" ' : null;
        isset($conf['method']) ? $opt = 'method="' . $conf['method'] . '" ' : null;
        isset($conf['action']) ? $opt = 'action="' . $conf['action'] . '" ' : null;

        return '<form ' . $opt . ' >' . $content . '</form>';
    }

    static function span(array $conf, string $content) {
        $opt = '';

        isset($conf['id']) ? $opt = 'id="' . $conf['id'] . '" ' : null;
        isset($conf['class']) ? $opt = 'class="' . $conf['class'] . '" ' : null;

        return '<span ' . $opt . ' >' . $content . '</span>';
    }

    static function div(array $conf, string $content) {
        $opt = '';

        isset($conf['id']) ? $opt = 'id="' . $conf['id'] . '" ' : null;
        isset($conf['class']) ? $opt = 'class="' . $conf['class'] . '" ' : null;

        return '<div ' . $opt . ' >' . $content . '</div>';
    }

    static function label(array $conf, string $caption) {
        $opt = '';

        if (isset($conf['for'])) {
            $opt = 'for="' . $conf['for'] . '" ';
        } else {
            return null;
        }
        isset($conf['class']) ? $opt = 'class="' . $conf['class'] . '" ' : null;

        return '<label ' . $opt . '>' . $caption . ' </label>';
    }

}
