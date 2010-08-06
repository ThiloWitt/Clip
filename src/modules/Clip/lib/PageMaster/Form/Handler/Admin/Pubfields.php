<?php
/**
 * PageMaster
 *
 * @copyright   (c) PageMaster Team
 * @link        http://code.zikula.org/pagemaster/
 * @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package     Zikula_3rdParty_Modules
 * @subpackage  pagemaster
 */

/**
 * Form handler to update publication fields.
 */
class PageMaster_Form_Handler_Admin_Pubfields extends Form_Handler
{
    var $tid;
    var $id;
    var $returnurl;

    /**
     * Initialize function
     */
    function initialize($view)
    {
        $tid = FormUtil::getPassedValue('tid');
        $id  = FormUtil::getPassedValue('id');

        // validation check
        if (empty($tid) || !is_numeric($tid)) {
            $view->setErrorMsg($this->__f('Error! %s not set.', 'tid'));
            return $view->redirect(ModUtil::url('PageMaster', 'admin'));
        }
        $this->tid = $tid;

        $tableObj = Doctrine_Core::getTable('PageMaster_Model_Pubfield');

        if (!empty($id)) {
            $this->id = $id;
            $pubfield = $tableObj->find($id);

            $view->assign('field', $pubfield->toArray());
        }

        // stores the return URL and the item URL
        if (empty($this->returnurl)) {
            $adminurl = ModUtil::url('PageMaster', 'admin');
            $this->returnurl = System::serverGetVar('HTTP_REFERER', $adminurl);
        }

        $pubfields = $tableObj->selectCollection("tid = '$tid'", 'lineno', -1, -1, 'name');

        $view->assign('pubfields', $pubfields)
             ->assign('tid', $tid);

        return true;
    }

    /**
     * Command handler
     */
    function handleCommand($view, &$args)
    {
        if ($args['commandName'] == 'cancel') {
            return $view->redirect($this->returnurl);
        }

        $data = $view->getValues();

        // creates and fill a Pubfield instance
        $pubfield = new PageMaster_Model_Pubfield();
        if (!empty($this->id)) {
            $pubfield->assignIdentifier($this->id);
        }
        $pubfield->fromArray($data['field']);

        // fill default data
        $plugin = PageMaster_Util::getPlugin($pubfield->fieldplugin);

        $pubfield->tid = (int)$this->tid;
        $pubfield->fieldtype = $plugin->columnDef;

        $this->returnurl = ModUtil::url('PageMaster', 'admin', 'pubfields',
                                        array('tid' => $pubfield->tid));

        // handle the commands
        switch ($args['commandName'])
        {
            // create a field
            case 'create':
                if (!$view->isValid()) {
                    return false;
                }

                $tableObj = Doctrine_Core::getTable('PageMaster_Model_Pubfield');

                // check that the name is unique
                $pubfield->name = str_replace("'", '', $pubfield->name);
                $submittedname = DataUtil::formatForStore($pubfield->name);
                if (empty($this->id)) {
                    $where = "name = '$submittedname' AND tid = '{$pubfield->tid}'";
                } else {
                    $where = "id <> '{$this->id}' AND name = '$submittedname' AND tid = '{$pubfield->tid}'";
                }

                $nameUnique = (int)$tableObj->selectFieldFunction('id', 'COUNT', $where);
                if ($nameUnique > 0) {
                    $plugin = $view->getPluginById('name');
                    $plugin->setError($this->__('Another field already has this name.'));
                    return false;
                }

                // check that the new name is not another publication property
                if (empty($this->id)) {
                    $pubClass = 'PageMaster_Model_Pubdata'.$this->tid;
                    $pubObj   = new $pubClass();
                    if (isset($pubObj[$pubfield->name])) {
                        $plugin = $view->getPluginById('name');
                        $plugin->setError($this->__('The provided name is reserved for the publication standard fields.'));
                        return false;
                    }
                }

                // reset any other title field if this one is enabled
                if ($pubfield->istitle == true) {
                    $tableObj->createQuery()
                             ->update()
                             ->set('istitle', '0')
                             ->where('tid = ?', $pubfield->tid)
                             ->execute();
                }

                // force a titlefield
                $max_line = (int)$tableObj->selectFieldFunction('lineno', 'MAX', 'tid = '.$pubfield->tid);
                if ($max_line == 0) {
                    $pubfield->istitle = true;
                }

                // create/edit status messages
                if (empty($this->id)) {
                    $pubfield->lineno = $max_line + 1;
                    LogUtil::registerStatus($this->__('Done! Field created.'));
                } else {
                    LogUtil::registerStatus($this->__('Done! Field updated.'));
                }
                $pubfield->save();
                break;

            // delete the field
            case 'delete':
                if ($pubfield->delete()) {
                    LogUtil::registerStatus($this->__('Done! Field deleted.'));
                } else {
                    return LogUtil::registerError($this->__('Error! Deletion attempt failed.'));
                }
                break;
        }

        return $view->redirect($this->returnurl);
    }
}
