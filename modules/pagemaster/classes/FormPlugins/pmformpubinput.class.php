<?php
/**
 * PageMaster
 *
 * @copyright   (c) PageMaster Team
 * @link        http://code.zikula.org/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @version     $ Id $
 * @package     Zikula_3rdParty_Modules
 * @subpackage  pagemaster
 */

require_once('system/pnForm/plugins/function.pnformdropdownlist.php');

class pmformpubinput extends pnFormDropdownList
{
    var $columnDef = 'I';
    var $title     = _PAGEMASTER_PLUGIN_PUBLICATION;

    function getFilename()
    {
        return __FILE__; // FIXME: may be found in smarty's data???
    }

    static function postRead($data, $field)
    {
        // TODO [Finish this]
        if (!empty($field['typedata'])) {
            list($tid, $filter, $join, $joinfields, $orderby) = explode(';', $field['typedata']);
        }
        return NULL;    
    }

    function load($render)
    {
        $pubfields = $render->pnFormEventHandler->pubfields;

        if (array_key_exists($this->id, $pubfields)) {
            list($tid, $filter, $join, $joinfields, $orderby) = explode(';', $pubfields[$this->id]['typedata']);
        }

        $pubfields_pub = getPubFields($tid);
        $pubarr = pnModAPIFunc('pagemaster', 'user', 'pubList',
                               array('tid'                => $tid,
                                     'countmode'          => 'no',
                                     'filter'             => $filter,
                                     'pubfields'          => $pubfields_pub,
                                     'orderby'            => $orderby,
                                     'checkPerm'          => true,
                                     'handlePluginFields' => false));

        $titleField = getTitleField($pubfields_pub);

        $items = array();
        $items[] = array('text' => '- - -',
                         'value' => '');

        foreach ($pubarr['publist'] as $pub ) {
            $items[] = array('text'  => $pub[$titleField],
                             'value' => $pub['core_pid']);
        }
        $this->items = $items;

        parent::load($render);
    }

    static function getSaveTypeDataFunc($field)
    {
        $saveTypeDataFunc = 'function saveTypeData()
                             {
                                 $(\'typedata\').value = $F(\'pmplugin_pubtid\')+\';\'+$F(\'pmplugin_pubfilter\')+\';\'+$F(\'pmplugin_pubjoin\')+\';\'+$F(\'pmplugin_pubjoinfields\')+\';\'+$F(\'pmplugin_puborderbyfield\');  
                                 closeTypeData();
                             }';

        return $saveTypeDataFunc;
    }

    static function getTypeHtml($field, $render)
    {
        $vars = explode(';', $render->_tpl_vars['typedata']);

        $tid         = $vars[0];
        $filter      = $vars[1];
        $join        = $vars[2];
        $join_fields = $vars[3];
        $orderby_field = $vars[4];

        if ($join == 'on') {
            $checked = 'checked="checked"';
        } else {
            $checked = '';
        }

        $pubtypes = DBUtil::selectObjectArray('pagemaster_pubtypes');

        $html = '<div class="pn-formrow">
                   <label for="pmplugin_pubtid">'._PAGEMASTER_PUBLICATION.':</label><br />
                   <select id="pmplugin_pubtid" name="pmplugin_pubtid">';

        foreach ($pubtypes as $pubtype) {
            if ($pubtype['tid'] == $tid) {
                $selected = 'selected="selected"';
            } else {
                $selected = '';
            }
            $html .= '<option value="'.$pubtype['tid'].'" '.$selected.' >'.$pubtype['title'].'</option>';
        }

        $html .= '  </select>
                  </div>
                  <div class="pn-formrow">
                    <label for="pmplugin_pubfilter">'._PAGEMASTER_PUBFILTER.':</label><br />
                    <input type="text" id="pmplugin_pubfilter" name="pmplugin_pubfilter" value="'.$filter.'" />
                  </div>
                  <div class="pn-formrow">
                    <label for="pmplugin_pubjoin">'._PAGEMASTER_PUBJOIN.':</label>
                    <input type="checkbox" id="pmplugin_pubjoin" name="pmplugin_pubjoin" '.$checked.' />
                  </div>
                  <div class="pn-formrow">
                    <label for="pmplugin_pubjoinfields">'._PAGEMASTER_PUBJOINFIELDS.':</label><br />
                    <input type="text" id="pmplugin_pubjoinfields" name="pmplugin_pubjoinfields" value="'.$join_fields.'" >
                  </div>
                  <div class="pn-formrow">
                    <label for="pmplugin_puborderbyfield">'._PAGEMASTER_PUBORDERBY.':</label><br />
                    <input type="text" id="pmplugin_puborderbyfield" name="pmplugin_puborderbyfield" value="'.$orderby_field.'" >
                  </div>';

        return $html;
    }
}
