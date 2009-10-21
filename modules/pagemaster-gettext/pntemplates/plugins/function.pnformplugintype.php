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

class pnFormPluginType extends pnFormDropdownList
{
    function getFilename()
    {
        return __FILE__; // FIXME: may be found in smarty's data???
    }

    function __construct()
    {
        $this->autoPostBack = true;
        $plugins = pagemasterGetPluginsOptionList();

        foreach ($plugins as $plugin) {
            $items[] = array (
                'text'  => $plugin['plugin']->title,
                'value' => $plugin['class']
            );
        }
        $this->items = $items;

        parent::__construct();
    }

    function render($render)
    {
        $dom = ZLanguage::getModuleDomain('pagemaster');
        $result = parent::render($render);
        $typeDataHtml = '';
        if (!empty($this->selectedValue) && !empty($this->items)) {
            if (!file_exists('javascript/livepipe/livepipe.js') || !file_exists('javascript/livepipe/livepipe.css') ||  !file_exists('javascript/livepipe/window.js')) {
                LogUtil::registerError(__("Javascript livepipe package was not found or it's incomplete. It's required for the plugin configuration modalbox. Please <a href=\"http://code.zikula.org/pagemaster/downloads\">download it</a> and copy into your site."), null, true);
            } else {
                PageUtil::addVar('javascript', 'javascript/livepipe/livepipe.js');
                PageUtil::addVar('javascript', 'javascript/livepipe/window.js');
                PageUtil::addVar('stylesheet', 'javascript/livepipe/livepipe.css');
            }
            $script =  "<script type=\"text/javascript\">\n//<![CDATA[\n";
            $plugin = getPlugin($this->selectedValue);
            if (method_exists($plugin, 'getTypeHtml'))
            {
                echo 1;
                if (method_exists($plugin, 'getSaveTypeDataFunc')) {
                    $script .= $plugin->getSaveTypeDataFunc($this);
                } else {
                    $script .= 'function saveTypeData(){ closeTypeData(); }';
                }
                // init functions for modalbox and unobtrusive buttons
                $script .= '
                function closeTypeData() {
                    pm_modalbox.close();
                }
                function pm_enablePluginConfig(){
                    $(\'saveTypeButton\').observe(\'click\', saveTypeData);
                    $(\'cancelTypeButton\').observe(\'click\', closeTypeData);
                    pm_modalbox = new Control.Modal($(\'showTypeButton\'), {
                        overlayOpacity: 0.6,
                        className: \'modal\',
                        fade: true,
                        iframeshim: false,
                        closeOnClick: false
                    });
                    $(document.body).insert($(\'typeDataDiv\'));
                }
                Event.observe( window, \'load\', pm_enablePluginConfig, false);
                ';

                $typeDataHtml  = '
                <a id="showTypeButton" href="#typeDataDiv"><img src="images/icons/extrasmall/utilities.gif" alt="' . __('Modify config') .'" /></a>
                <div id="typeDataDiv" class="modal">
                    <div>'.$plugin->getTypeHtml($this, $render).'</div>
                    <div>
                        <button type="button" id="saveTypeButton" name="saveTypeButton"><img src="images/icons/extrasmall/filesave.gif" alt="' . _SAVE . '" /></button>&nbsp;
                        <button type="button" id="cancelTypeButton" name="cancelTypeButton"><img src="images/icons/extrasmall/button_cancel.gif" alt="' . _CANCEL . '" /></button>
                    </div>
                </div>';
            } else {
                $script .= 'Event.observe( window, \'load\', function() { $(\'typedata\').hide(); }, false);';
            }
            $script .= "\n// ]]>\n</script>";
            PageUtil::setVar('rawtext', $script);
        }
        return $result . $typeDataHtml;
    }
}

function smarty_function_pnformplugintype($params, &$render) {
    return $render->pnFormRegisterPlugin('pnFormPluginType', $params);
}