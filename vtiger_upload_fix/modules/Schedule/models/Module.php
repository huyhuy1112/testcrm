<?php
class Schedule_Module_Model extends Vtiger_Module_Model {
    
    /**
     * Get module icon HTML
     */
    public function getModuleIcon() {
        $title = vtranslate('Schedule', 'Schedule');
        return "<i class='fa fa-calendar-check-o' title='$title'></i>";
    }
}
?>