<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/*
 * This is a one-line short description of the file
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package   mod_survey
 * @copyright 2013 kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') OR die();

require_once($CFG->dirroot.'/mod/survey/itembase.class.php');
require_once($CFG->dirroot.'/mod/survey/field/textarea/lib.php');

class surveyfield_textarea extends surveyitem_base {

    /*
     * $surveyid = the id of the survey
     */
    // public $surveyid = 0;

    /*
     * $itemid = the ID of the survey_item record
     */
    // public $itemid = 0;

    /*
     * $pluginid = the ID of the survey_textarea record
     */
    public $pluginid = 0;

    /*******************************************************************/

    /*
     * $useeditor = does the item use html editor?.
     */
    public $useeditor = true;

    /*
     * $arearows = number or rows of the text area?
     */
    public $arearows = 0;

    /*
     * $areacols = number or columns of the text area?
     */
    public $areacols = 0;

    /*
     * $minlength = the minimum allowed text length
     */
    public $minlength = '0';

    /*
     * $maxlength = the maximum allowed text length
     */
    public $maxlength = '1024';

    /*
     * $context = context as it is always required to dial with editors
     */
    private $context;

    /*
     * $flag = features describing the object
     */
    public $flag;

    /*
     * $item_form_requires = list of fields I will see in the form
     * public $item_form_requires;
     */

    /*******************************************************************/

    /*
     * Class constructor
     *
     * If itemid is provided, load the object (item + base + plugin) from database
     *
     * @param int $itemid. Optional survey_item ID
     */
    public function __construct($itemid=0) {
        global $PAGE;

        $cm = $PAGE->cm;

        $this->type = SURVEY_FIELD;
        $this->plugin = 'textarea';

        $this->flag = new stdclass();
        $this->flag->issearchable = true;
        $this->flag->ismatchable = false;
        $this->flag->useplugintable = true;

        // if the item is constructed at survey instance creation
        // (this happen if a builtin survey is requested)
        // $cm does not exist
        if (isset($cm)) {
            $this->context = context_module::instance($cm->id);
        }

        if (!empty($itemid)) {
            $this->item_load($itemid);
        }
    }

    /*
     * item_load
     * @param $itemid
     * @return
     */
    public function item_load($itemid) {
        // Do parent item loading stuff here (surveyitem_base::item_load($itemid)))
        parent::item_load($itemid);

        // multilang load support for builtin survey
        // whether executed, the 'content' field is ALWAYS handled
        $this->item_builtin_string_load_support();

        $this->item_custom_fields_to_form();
    }

    /*
     * item_save
     * @param $record
     * @return
     */
    public function item_save($record) {
        // //////////////////////////////////
        // Now execute very specific plugin level actions
        // //////////////////////////////////

        // set custom fields value as defined for this field
        $this->item_custom_fields_to_db($record);

        // do preliminary actions on $record values corresponding to fields type checkbox
        $checkboxes = array('useeditor');
        foreach ($checkboxes as $checkbox) {
            if (!isset($record->{$checkbox})) {
                $record->{$checkbox} = 0;
            }
        }

        // multilang save support for builtin survey
        // whether executed, the 'content' field is ALWAYS handled
        $this->item_builtin_string_save_support($record);

        // Do parent item saving stuff here (surveyitem_base::item_save($record)))
        return parent::item_save($record);
    }

    /*
     * item_custom_fields_to_form
     * add checkboxes selection for empty fields
     * @param
     * @return
     */
    public function item_custom_fields_to_form() {
        // 1. special management for fields equipped with "free" checkbox
        $fieldlist = $this->item_fields_with_free_checkbox();
        foreach ($fieldlist as $field) {
            if (!isset($this->{$field})) {
                $this->{$field.'_check'} = 1; // may not even exist
            } else {
                $this->{$field.'_check'} = 0; // may not even exist
            }
        }

        // 2. special management for composite fields
        // nothing to do: they don't exist in this plugin

        // 3. special management for defaultvalue
        // nothing to do: defaultvalue doesn't need any further care
    }

    /*
     * item_custom_fields_to_db
     * sets record field to store the correct value to db for the age custom item
     * @param $record
     * @return
     */
    public function item_custom_fields_to_db($record) {
        // 1. special management for fields equipped with "free" checkbox
        $fieldlist = $this->item_fields_with_free_checkbox();
        foreach ($fieldlist as $field) {
            if (isset($record->{$field.'_check'})) { // may not even exist
                $record->{$field} = null;
            }
        }

        // 2. special management for composite fields
        // nothing to do: they don't exist in this plugin

        // 3. special management for defaultvalue
        // nothing to do: defaultvalue doesn't need any further care
    }

    /*
     * item_parent_content_format_validation
     * checks whether the user input format in the "parentcontent" field is correct
     * @param $parentcontent
     * @return
     */
    public function item_parent_content_format_validation($parentcontent) {
        // $this->flag->ismatchable = false
        // this method is never called
    }

    /*
     * item_parent_content_content_validation
     * checks whether the user input content in the "parentcontent" field is correct
     * @param $parentcontent
     * @return
     */
    public function item_parent_content_content_validation($parentcontent) {
        // $this->flag->ismatchable = false
        // this method is never called
    }

    /*
     * item_parent_content_encode_value
     * starting from the user input, this function stores to the db the value as it is stored during survey submission
     * this method manages the $parentcontent of its child item, not its own $parentcontent
     * (take care: here we are not submitting a survey but we are submitting an item)
     * @param $parentcontent
     * @return
     */
    public function item_parent_content_encode_value($parentcontent) {
        // $this->flag->ismatchable = false
        // this method is never called
    }

    /*
     * item_fields_with_free_checkbox
     * get the list of composite fields
     * @param
     * @return
     */
    public function item_fields_with_free_checkbox() {
        return array('maxlength');
    }

    /*
     * item_get_hard_info
     * @param
     * @return
     */
    public function item_get_hard_info() {

        if (!empty($this->minlength)) {
            $a = $this->minlength;
            if (!empty($this->maxlength)) {
                $a .= get_string('and', 'surveyfield_textarea').$this->maxlength;
                $hardinfo = get_string('hasminmaxlength', 'surveyfield_textarea', $a);
            } else {
                $hardinfo = get_string('hasminlength', 'surveyfield_textarea', $a);
            }
        } else {
            if (!empty($this->maxlength)) {
                $a = $this->maxlength;
                $hardinfo = get_string('hasmaxlength', 'surveyfield_textarea', $a);
            } else {
                $hardinfo = '';
            }
        }

        return $hardinfo;
    }

    /*
     * item_list_constraints
     * @param
     * @return list of contraints of the plugin in text format
     */
    public function item_list_constraints() {
        return 'item_list_constraints method is still under construction for '.$this->plugin;
    }

    /*
     * item_parent_validate_child_constraints
     * @param
     * @return status of child relation
     */
    public function item_parent_validate_child_constraints($childvalue) {
        return 'item_parent_validate_child_constraints needs refinements in plugin: '.$this->plugin;
    }

    /*
     * userform_mform_element
     * @param $mform
     * @return
     */
    public function userform_mform_element($mform, $survey, $canaccessadvancedform, $parentitem=null, $searchform=false) {
        // this plugin has $this->flag->issearchable = false; so it will never be part of a search form
        // TODO: make issearchable true

        $elementnumber = $this->customnumber ? $this->customnumber.': ' : '';
        $elementlabel = $this->extrarow ? '&nbsp;' : $elementnumber.strip_tags($this->content);

        if (!empty($this->useeditor)) {
            $fieldname = SURVEY_ITEMPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid.'_editor';
            $editoroptions = array('trusttext' => true, 'subdirs' => true, 'maxfiles' => EDITOR_UNLIMITED_FILES);
            $mform->addElement('editor', $fieldname, $elementlabel, null, $editoroptions);
            $mform->setType($fieldname, PARAM_CLEANHTML);
        } else {
            $fieldname = SURVEY_ITEMPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid;
            $textareaoptions = array('maxfiles' => 0, 'maxbytes' => 0, 'trusttext' => false);
            $mform->addElement('textarea', $fieldname, $this->content, array('wrap' => 'virtual', 'rows' => $this->arearows, 'cols' => $this->areacols, 'class' => 'smalltext'));
            $mform->setType($fieldname, PARAM_TEXT);
        }

        $maybedisabled = $this->userform_can_be_disabled($survey, $canaccessadvancedform, $parentitem);
        if ($this->required && (!$searchform) && (!$maybedisabled)) {
            // $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
            $mform->addRule($fieldname, get_string('required'), 'nonempty_rule', $mform);
            $mform->_required[] = $fieldname;
        }
    }

    /*
     * item_get_plugin_values
     * @param $pluginstructure
     * @param $pluginsid
     * @return
     */
    public function item_get_plugin_values($pluginstructure, $pluginsid) {
        $values = parent::item_get_plugin_values($pluginstructure, $pluginsid);

        // just a check before assuming all has been done correctly
        $errindex = array_search('err', $values, true);
        if ($errindex !== false) {
            throw new moodle_exception('$values[\''.$errindex.'\'] of survey_'.$this->plugin.' was not properly managed');
        }

        return $values;
    }

    /*
     * userform_mform_validation
     * @param $data, &$errors, $survey
     * @return
     */
    public function userform_mform_validation($data, &$errors, $survey, $canaccessadvancedform, $parentitem=null) {
        // useless: empty values are checked in Server Side Validation in submissions_form.php
        // if (empty($data[$fieldname])) {
        //     $errors[$fieldname] = get_string('required');
        //     return;
        // }
    }

    /*
     * userform_get_parent_disabilitation_info
     * from child_parentcontent defines syntax for disabledIf
     * @param: $child_parentcontent
     * @return
     */
    public function userform_get_parent_disabilitation_info($child_parentcontent) {
        // $this->flag->ismatchable = false
        // this method is never called
    }

    /*
     * userform_child_is_allowed_dynamic
     * from parentcontent defines whether an item is supposed to be active (not disabled) in the form so needs validation
     * ----------------------------------------------------------------------
     * this function is called when $survey->newpageforchild == false
     * that is the current survey lives in just one single web page
     * ----------------------------------------------------------------------
     * Am I geting submitted data from $fromform or from table 'survey_userdata'?
     *     - if I get it from $fromform or from $data[] I need to use userform_child_is_allowed_dynamic
     *     - if I get it from table 'survey_userdata'   I need to use survey_child_is_allowed_static
     * ----------------------------------------------------------------------
     * @param: $parentcontent, $parentsubmitted
     * @return
     */
    public function userform_child_is_allowed_dynamic($child_parentcontent, $data) {
        // $this->flag->ismatchable = false
        // this method is never called
    }

    /*
     * userform_save
     * starting from the info set by the user in the form
     * I define the info to store in the db
     * @param $itemdetail, $olduserdata
     * @return
     */
    public function userform_save($itemdetail, $olduserdata) {
        $fieldname = SURVEY_ITEMPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid;

        if (!empty($this->useeditor)) {
            $olduserdata->{$fieldname.'_editor'} = $itemdetail['editor'];

            $editoroptions = array('trusttext' => true, 'subdirs' => false, 'maxfiles' => -1, 'context' => $this->context);
            $olduserdata = file_postupdate_standard_editor($olduserdata, $fieldname, $editoroptions, $this->context, 'mod_survey', SURVEY_ITEMCONTENTFILEAREA, $olduserdata->id);
            $olduserdata->content = $olduserdata->{$fieldname};
        } else {
            $olduserdata->content = null;
        }
    }

    /*
     * this method is called from survey_set_prefill (in locallib.php) to set $prefill at user form display time
     * (defaults are set in userform_mform_element)
     *
     * userform_set_prefill
     * @param $olduserdata
     * @return
     */
    public function userform_set_prefill($olduserdata) {
        $prefill = array();

        if ($olduserdata) { // $olduserdata may be boolean false for not existing data
            if (!empty($olduserdata->content)) {
                $fieldname = SURVEY_ITEMPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid;

                if (!empty($this->useeditor)) {
                    $editoroptions = array('trusttext' => true, 'subdirs' => true, 'maxfiles' => EDITOR_UNLIMITED_FILES, 'context' => $this->context);
                    $olduserdata->contentformat = FORMAT_HTML;
                    $olduserdata = file_prepare_standard_editor($olduserdata, 'content', $editoroptions, $this->context, 'mod_survey', SURVEY_ITEMCONTENTFILEAREA, $olduserdata->id);

                    $prefill[$fieldname.'_editor'] = $olduserdata->content_editor;
                } else {
                    $prefill[$fieldname] = $olduserdata->content;
                }
            // } else {
                // nothing was set
                // do not accept defaults but overwrite them
            }
        } // else use item defaults

        return $prefill;
    }

    /*
     * userform_mform_element_is_group
     * returns true if the useform mform element for this item id is a group and false if not
     * @param
     * @return
     */
    public function userform_mform_element_is_group() {
        // $this->flag->ismatchable = false
        // this method is never called
    }
}