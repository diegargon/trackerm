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
      values[] = ['name' =>'', value='' ]
     */

    static function select(array $conf, array $values) {
        $opt = '';
        if (empty($values) || !is_array($values) || !is_array($conf) || empty($conf['name'])) {
            return false;
        }
        !isset($conf['sel']) ? $conf['sel'] = null : null;
        isset($conf['class']) ? $opt = ' class="' . $conf['class'] . ' "' : null;
        isset($conf['onChange']) ? $opt .= ' onchange="this.form.submit()" ' : null;
        !empty($conf['size']) ? $opt .= 'size="' . $conf['size'] . '" ' : null;
        $select = '';
        (!empty($conf['onChange']) || !empty($conf['form'])) ? $select .= '<form method="post">' : null;

        $select .= '<select ' . $opt . ' name="' . $conf['name'] . '">';
        isset($conf['sel_none']) ? $select .= '<option value="0"></option>' : null;
        foreach ($values as $value) {
            isset($conf['selected']) && $conf['selected'] == $value['value'] ? $selected = ' selected="selected" ' : $selected = '';
            $select .= '<option ' . $selected . ' value="' . $value['value'] . '">' . $value['name'] . '</option>';
        }
        $select .= '</select>';
        (!empty($conf['onChange']) || !empty($conf['form'])) ? $select .= '</form>' : null;

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
        $input = '';

        !empty($conf['id']) ? $opt .= 'id="' . $conf['id'] . '" ' : null;
        !empty($conf['class']) ? $opt .= 'class="' . $conf['class'] . '" ' : null;
        !empty($conf['type']) ? $opt .= 'type="' . $conf['type'] . '" ' : $opt .= ' type="submit" ';
        !empty($conf['name']) ? $opt .= 'name="' . $conf['name'] . '" ' : null;
        !empty($conf['size']) ? $opt .= 'size="' . $conf['size'] . '" ' : null;
        !empty($conf['min']) ? $opt .= 'min="' . $conf['min'] . '" ' : null;
        !empty($conf['max']) ? $opt .= 'max="' . $conf['max'] . '" ' : null;
        !empty($conf['vertical']) ? $opt .= 'orient="vertical" ' : null;
        !empty($conf['disabled']) ? $opt .= 'disabled ' : null;
        isset($conf['value']) ? $opt .= 'value="' . $conf['value'] . '" ' : null;

        if (!empty($conf['label_caption']) && !empty($conf['id'])) {
            $label_conf['for'] = $conf['id'];
            !empty($conf['label_class']) ? $label_conf['class'] = $conf['label_class'] : null;
            $input .= self::label($label_conf, $conf['label_caption']);
        }
        $input .= '<input ' . $opt . ' />';

        return $input;
    }

    static function form(array $conf, string $content) {
        $opt = '';

        isset($conf['id']) ? $opt .= ' id="' . $conf['id'] . '" ' : null;
        isset($conf['class']) ? $opt .= ' class="' . $conf['class'] . '" ' : null;
        isset($conf['method']) ? $opt .= ' method="' . $conf['method'] . '" ' : null;
        isset($conf['action']) ? $opt .= ' action="' . $conf['action'] . '" ' : null;

        return '<form ' . $opt . ' >' . $content . '</form>';
    }

    static function span(array $conf, string $content) {
        $opt = '';

        isset($conf['id']) ? $opt .= ' id="' . $conf['id'] . '" ' : null;
        isset($conf['class']) ? $opt .= ' class="' . $conf['class'] . '" ' : null;

        return '<span ' . $opt . ' >' . $content . '</span>';
    }

    static function div(array $conf, string $content) {
        $opt = '';

        isset($conf['id']) ? $opt .= ' id="' . $conf['id'] . '" ' : null;
        isset($conf['class']) ? $opt .= ' class="' . $conf['class'] . '" ' : null;

        return '<div ' . $opt . ' >' . $content . '</div>';
    }

    static function label(array $conf, string $caption) {
        $opt = '';

        if (isset($conf['for'])) {
            $opt .= ' for="' . $conf['for'] . '" ';
        } else {
            return null;
        }
        isset($conf['class']) ? $opt .= ' class="' . $conf['class'] . '" ' : null;

        return '<label ' . $opt . '>' . $caption . ' </label>';
    }

    static function head(array $conf, string $caption) {
        $opt = '';

        !empty($conf['h']) ? $h = $conf['h'] : $h = 1;
        isset($conf['class']) ? $opt = 'class="' . $conf['class'] . '" ' : null;

        $head = '<h' . $h . ' ' . $opt . '>';
        $head .= $caption;
        $head .= '</h' . $h . '>';
        return $head;
    }

    static function p(array $conf, string $caption) {
        $opt = '';

        isset($conf['class']) ? $opt .= ' class="' . $conf['class'] . '" ' : null;
        $p = '<p ' . $opt . '>' . $caption . '</p>';

        return $p;
    }

    static function br(array $conf) {
        $opt = '';

        isset($conf['class']) ? $opt .= ' class="' . $conf['class'] . '" ' : null;
        $br = '</br ' . $opt . '>';

        return $br;
    }

}
