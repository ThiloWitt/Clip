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

/**
 * Generic form Plugin
 * Loads the disired plugin from fieldtype definition
 * @author kundi
 * @param $args['fieldname']
 * @param generic
 */
function smarty_function_genericformplugin($params, &$render)
{
    $id = $params['id'];
    $tid = $render->pnFormEventHandler->tid;

    if (!$id) {
        return 'Required parameter [id] not provided in smarty_function_genericformplugin';
    }

    if (!$tid) {
        return 'tid not extractable from pnRender Object in smarty_function_genericformplugin';
    }

    $pubfields = getPubFields($tid);
    $pluginclass = $pubfields[$id]['fieldplugin'];
    Loader::LoadClass($pluginclass,'modules/pagemaster/classes/FormPlugins');
    $plugin = new $pluginclass;

    //read settings in pubfields, if set by template ignore settings in pubfields
    if (!isset($params['mandatory'])){
        $params['mandatory'] = $pubfields[$id]['ismandatory'];
    }

    if (!isset($params['maxLength'])){
        $params['maxLength'] = $pubfields[$id]['fieldmaxlength'];
    }

    return $render->pnFormRegisterPlugin($plugin, $params);
}