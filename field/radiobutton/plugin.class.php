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
require_once($CFG->dirroot.'/mod/survey/field/radiobutton/lib.php');

class surveyfield_radiobutton extends surveyitem_base {

    /*
     * $surveyid = the id of the survey
     */
    // public $surveyid = 0;

    /*
     * $itemid = the ID of the survey_item record
     */
    // public $itemid = 0;

    /*
     * $pluginid = the ID of the survey_radiobutton record
     */
    public $pluginid = 0;

    /*******************************************************************/

    /*
     * $options = list of options in the form of "$value SURVEY_VALUELABELSEPARATOR $label"
     */
    public $options = '';

    /*
     * $options_sid
     */
    public $options_sid = null;

    /*
     * $defaultoption
     */
    public $defaultoption = SURVEY_INVITATIONDEFAULT;

    /*
     * $labelother = the text label for the optional option "other" in the form of "$value SURVEY_OTHERSEPARATOR $label"
     */
    public $labelother = '';

    /*
     * $defaultvalue = the value of the field when the form is initially displayed.
     */
    public $defaultvalue = '';

    /*
     * $adjustment = the orientation of the list of options.
     */
    public $adjustment = 0;

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
        $this->type = SURVEY_FIELD;
        $this->plugin = 'radiobutton';

        $this->flag = new stdclass();
        $this->flag->issearchable = true;
        $this->flag->ismatchable = true;
        $this->flag->useplugintable = true;

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
        $fieldlist = array('content', 'options', 'labelother', 'defaultvalue');
        $this->item_builtin_string_load_support($fieldlist);

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

        // drop empty rows and trim edging rows spaces from each textarea field
        $fieldlist = array('options');
        survey_clean_textarea_fields($record, $fieldlist);

        $this->item_custom_fields_to_db($record);

        // multilang save support for builtin survey
        // whether executed, the 'content' field is ALWAYS handled
        $this->item_builtin_string_save_support($record, $fieldlist);

        // Do parent item saving stuff here (surveyitem_base::item_save($record)))
        return parent::item_save($record);
    }

    /*
     * item_custom_fields_to_form
     * translates the date class property $fieldlist in $field.'_year' and $field.'_month'
     * @param
     * @return
     */
    public function item_custom_fields_to_form() {
        // 1. special management for fields equipped with "free" checkbox
        // nothing to do: they don't exist in this plugin

        // 2. special management for composite fields
        // nothing to do: they don't exist in this plugin

        // 3. special management for defaultvalue
        // nothing to do: defaultvalue doesn't need any further care
    }

    /*
     * item_custom_fields_to_db
     * sets record field to store the correct value to db for the date custom item
     * @param $record
     * @return
     */
    public function item_custom_fields_to_db($record) {
        // 1. special management for fields equipped with "free" checkbox
        // nothing to do: they don't exist in this plugin

        // 2. special management for composite fields
        // nothing to do: they don't exist in this plugin

        // 3. special management for defaultvalue
        if ($record->defaultoption != SURVEY_CUSTOMDEFAULT) {
            $record->defaultvalue = null;
        }
    }

    /*
     * item_generate_standard_default
     * sets record field to store the correct value to db for the date custom item
     * @param $record
     * @return
     */
    public function item_generate_standard_default() {
        $optionarray = survey_textarea_to_array($this->options);
        $firstoption = reset($optionarray);

        if (preg_match('/^(.*)'.SURVEY_VALUELABELSEPARATOR.'(.*)$/', $firstoption, $match)) { // do not warn: it can never be equal to zero
            // print_object($match);
            $default = $match[1];
        } else {
            $default = $firstoption;
        }

        return $default;
    }

    /*
     * item_parent_content_format_validation
     * checks whether the user input format in the "parentcontent" field is correct
     * @param $parentcontent
     * @return
     */
    public function item_parent_content_format_validation($parentcontent) {
    }

    /*
     * item_parent_content_content_validation
     * checks whether the user input content in the "parentcontent" field is correct
     * @param $parentcontent
     * @return
     */
    public function item_parent_content_content_validation($parentcontent) {
        // $format = get_string('parentformat', 'surveyfield_boolean'); // '[label]'
        $options = $this->item_complete_option_array();
        // clean parentcontent
        $parentcontent = trim($parentcontent);

        if (!array_key_exists($parentcontent, $options)) {
            // a missing $value is allowed only if the "other" option is foreseen
            if (!$this->labelother) {
                return (get_string('parentcontent_err', 'surveyfield_radiobutton', $parentcontent));
            }
        }
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
        return $parentcontent;
    }

    /*
     * item_list_constraints
     * @param
     * @return list of contraints of the plugin in text format
     */
    public function item_list_constraints() {
        $constraints = array();

        $valuelabel = $this->item_get_value_label_array('options');
        $optionstr = get_string('option', 'surveyfield_radiobutton');
        foreach ($valuelabel as $value => $label) {
            $constraints[] = $optionstr.': '.$value;
        }
        if (!empty($this->labelother)) {
            $constraints[] = get_string('labelother', 'surveyfield_radiobutton').': '.get_string('allowed', 'surveyfield_radiobutton');
        }

        return implode($constraints, '<br />');
    }

    /*
     * item_parent_validate_child_constraints
     * @param
     * @return status of child relation
     */
    public function item_parent_validate_child_constraints($childvalue) {
        $valuelabel = $this->item_get_value_label_array('options');

        $status = true;
        if (empty($this->labelother)) {
            $status = $status && array_key_exists($childvalue, $valuelabel);
        }

        return $status;
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
     * userform_mform_element
     * @param $mform
     * @return
     */
    public function userform_mform_element($mform, $survey, $canaccessadvancedform, $parentitem=null, $searchform=false) {
        $fieldname = SURVEY_ITEMPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid;

        $elementnumber = $this->customnumber ? $this->customnumber.': ' : '';
        $elementlabel = $this->extrarow ? '&nbsp;' : $elementnumber.strip_tags($this->content);

        $valuelabel = $this->item_get_value_label_array('options');
        if (($this->defaultoption == SURVEY_INVITATIONDEFAULT) && (!$searchform)) {
            $valuelabel = array(SURVEY_INVITATIONVALUE => get_string('choosedots')) + $valuelabel;
        }

        $class = array('class' => 'indent-'.$this->indent);
        $elementgroup = array();
        foreach ($valuelabel as $value => $label) {
            $elementgroup[] = $mform->createElement('radio', $fieldname, '', $label, $value, $class);
            $class = ($this->adjustment == SURVEY_VERTICAL) ? array('class' => 'indent-'.$this->indent) : '';
        }

        if (!empty($this->labelother)) {
            list($othervalue, $otherlabel) = $this->item_get_other();

            $class = ($this->adjustment == SURVEY_VERTICAL) ? array('class' => 'indent-'.$this->indent) : '';
            $elementgroup[] = $mform->createElement('radio', $fieldname, '', $otherlabel, 'other', $class);
            $elementgroup[] = $mform->createElement('text', $fieldname.'_text', '');

            $mform->disabledIf($fieldname.'_text', $fieldname, 'neq', 'other');
        }

        if ( (!$this->required) || $searchform ) {
            $check_label = ($searchform) ? get_string('star', 'survey') : get_string('noanswer', 'survey');
            $elementgroup[] = $mform->createElement('radio', $fieldname, '', $check_label, SURVEY_NOANSWERVALUE, $class);
        }

        if ($this->adjustment == SURVEY_VERTICAL) {
            if (!empty($this->labelother)) {
                // il -3 è dato da:
                //     -1 perché se ho n elementi, devo contare (n-1) separatori
                //     -1 perché il primo ha indice 0
                //     -1 perché l'ultimo lo aggiungo a mano dopo
                $separator = array_fill(0, count($elementgroup)-3, '<br />');
                $separator[] = ' ';
                $separator[] = '<br />';
            } else {
                $separator = '<br />';
            }
        } else { // SURVEY_HORIZONTAL
            $separator = ' ';
        }
        $mform->addGroup($elementgroup, $fieldname.'_group', $elementlabel, $separator, false);

        if (!$searchform) {
            $maybedisabled = $this->userform_can_be_disabled($survey, $canaccessadvancedform, $parentitem);
            if ($this->required && (!$maybedisabled)) {
                // $mform->addRule($fieldname.'_group', get_string('required'), 'required', null, 'client');
                $mform->addRule($fieldname.'_group', get_string('required'), 'nonempty_rule', $mform);
                $mform->_required[] = $fieldname.'_group';
            }

            switch ($this->defaultoption) {
                case SURVEY_CUSTOMDEFAULT:
                    $mform->setDefault($fieldname, $this->defaultvalue);
                    break;
                case SURVEY_INVITATIONDEFAULT:
                    $mform->setDefault($fieldname, SURVEY_INVITATIONVALUE);
                    break;
                case SURVEY_NOANSWERDEFAULT:
                    $mform->setDefault($fieldname, SURVEY_NOANSWERVALUE);
                    break;
                default:
                    debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $this->defaultoption = '.$this->defaultoption);
            }
        } else {
            $mform->setDefault($fieldname, SURVEY_NOANSWERVALUE); // free
        }
    }

    /*
     * userform_mform_validation
     * @param $data, &$errors, $survey
     * @return
     */
    public function userform_mform_validation($data, &$errors, $survey, $canaccessadvancedform, $parentitem=null) {
        $fieldname = SURVEY_ITEMPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid;

        if ( ($data[$fieldname] == 'other') && empty($data[$fieldname.'_text']) ) {
            $errors[$fieldname.'_text'] = get_string('required');
            return;
        }

        // I need to check value is different from SURVEY_INVITATIONVALUE even if it is not required
        if ($data[$fieldname] == SURVEY_INVITATIONVALUE) {
            $errors[$fieldname.'_group'] = get_string('uerr_optionnotset', 'surveyfield_radiobutton');
            return;
        }
    }

    /*
     * userform_get_parent_disabilitation_info
     * from child_parentcontent defines syntax for disabledIf
     * @param: $child_parentcontent
     * @return
     */
    public function userform_get_parent_disabilitation_info($child_parentcontent) {
        $fieldname = SURVEY_ITEMPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid;

        $disabilitationinfo = array();

        $mformelementinfo = new stdClass();
        $mformelementinfo->parentname = $fieldname;
        $mformelementinfo->operator = 'neq';
        $mformelementinfo->content = $child_parentcontent;
        $disabilitationinfo[] = $mformelementinfo;

        return $disabilitationinfo;
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
        $fieldname = SURVEY_ITEMPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid;

        // devo sapere come si chiamano gli mfrom element che corrispondono al contenuto di $child_parentcontent
        $valuelabel = $this->item_get_value_label_array('options');
        $valuelabel = array_keys($valuelabel);

        if (in_array($child_parentcontent, $valuelabel)) {
            $status = ($data[$fieldname] == $child_parentcontent);
        } else {
            $status = ($data[$fieldname] == 'other');
            $status = $status && ($data[$fieldname.'_text'] == $child_parentcontent);
        }

        return $status;
    }

    /*
     * userform_save
     * starting from the info set by the user in the form
     * I define the info to store in the db
     * @param $itemdetail, $olduserdata
     * @return
     */
    public function userform_save($itemdetail, $olduserdata) {
        if (isset($itemdetail['mainelement'])) {
            switch ($itemdetail['mainelement']) {
                case 'other':
                    $olduserdata->content = $itemdetail['text'];
                    break;
                case '':
                    $olduserdata->content = null;
                    break;
                default:
                    $olduserdata->content = $itemdetail['mainelement'];
                    break;
            }
            return;
        }

        throw new moodle_exception('unhandled return value from user submission');
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

        $fieldname = SURVEY_ITEMPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid;

        if ($olduserdata) { // $olduserdata may be boolean false for not existing data
            if (!empty($olduserdata->content)) {
                $valuelabel = $this->item_get_value_label_array('options');
                if (array_key_exists($olduserdata->content, $valuelabel)) {
                    $prefill[$fieldname] = $olduserdata->content;
                } else {
                    if ($olduserdata->content == SURVEY_NOANSWERVALUE) {
                        $prefill[$fieldname] = SURVEY_NOANSWERVALUE;
                    } else {
                        // deve per forza essere il valore di "other"
                        $prefill[$fieldname] = 'other';
                        $prefill[$fieldname.'_text'] = $olduserdata->content;
                    }
                }
            } else {
                // nothing was set
                // do not accept defaults but overwrite them
                // Ma se questa è una select, come può essere empty($olduserdata->content)? Ho selezionato la voce "Not answering"
                $prefill[$fieldname] = '';
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
        return true;
    }
}