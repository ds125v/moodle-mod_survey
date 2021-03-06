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

require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/mod/survey/itembase_form.php');
require_once($CFG->dirroot.'/mod/survey/field/checkbox/lib.php');

class survey_pluginform extends surveyitem_baseform {

    function definition() {
        // -------------------------------------------------------------------------------
        // acquisisco i valori per pre-definire i campi della form
        $item = $this->_customdata->item;

        // -------------------------------------------------------------------------------
        // comincio con la "sezione" comune della form
        parent::definition();

        // -------------------------------------------------------------------------------
        $mform = $this->_form;

        // ----------------------------------------
        // newitem::options_sid
        // ----------------------------------------
        $fieldname = 'options_sid';
        $mform->addElement('hidden', $fieldname, '');

        // ----------------------------------------
        // newitem::options
        // ----------------------------------------
        $fieldname = 'options';
        $mform->addElement('textarea', $fieldname, get_string($fieldname, 'surveyfield_checkbox'), array('wrap' => 'virtual', 'rows' => '10', 'cols' => '65'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_checkbox');
        $mform->addRule($fieldname, get_string($fieldname.'_err', 'surveyfield_checkbox'), 'required', null, 'client');
        $mform->setType($fieldname, PARAM_TEXT);

        // ----------------------------------------
        // newitem::labelother
        // ----------------------------------------
        $fieldname = 'labelother';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveyfield_checkbox'), array('maxlength' => '64', 'size' => '50'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_checkbox');
        $mform->setType($fieldname, PARAM_TEXT);

        // ----------------------------------------
        // newitem::defaultvalue
        // ----------------------------------------
        $fieldname = 'defaultvalue';
        $mform->addElement('textarea', $fieldname, get_string($fieldname, 'surveyfield_checkbox'), array('wrap' => 'virtual', 'rows' => '10', 'cols' => '65'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_checkbox');
        $mform->setType($fieldname, PARAM_TEXT);

        // ----------------------------------------
        // newitem::adjustment
        // ----------------------------------------
        $fieldname = 'adjustment';
        $options = array(SURVEY_HORIZONTAL => get_string('horizontal', 'surveyfield_checkbox'), SURVEY_VERTICAL => get_string('vertical', 'surveyfield_checkbox'));
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyfield_checkbox'), $options);
        $mform->addHelpButton($fieldname, $fieldname, 'surveyfield_checkbox');
        $mform->setType($fieldname, PARAM_INT);
        $mform->setDefault($fieldname, SURVEY_VERTICAL);

        $this->add_item_buttons();
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // -------------------------------------------------------------------------------
        // acquisisco i valori per pre-definire i campi della form
        $item = $this->_customdata->item;

        // clean inputs
        $clean_options = survey_textarea_to_array($data['options']);
        $clean_defaultvalue = survey_textarea_to_array($data['defaultvalue']);
        $clean_labelother = trim($data['labelother']);

        // costruisco il vettore $value ($label non mi interessa) a partire da $clean_options e $clean_labelother
        $values = array();

        foreach ($clean_options as $option) {
            if (strpos($option, SURVEY_VALUELABELSEPARATOR) === false) {
                $values[] = trim($option);
            } else {
                $pair = explode(SURVEY_VALUELABELSEPARATOR, $option);
                $values[] = $pair[0];
            }
        }
        if (!empty($clean_labelother)) {
            if (strpos($clean_labelother, SURVEY_OTHERSEPARATOR) === false) {
                $values[] = $clean_labelother;
            } else {
                $pair = explode(SURVEY_OTHERSEPARATOR, $clean_labelother);
                $values[] = $pair[1];
            }
        }

        // //////////////////////////////////////////////////////////////////////////////////////
        // first check
        // each item of default has to be among options item OR has to be == to otherlabel value
        // this also verify (helped by the second check) that the number of default is not gretr than the number of options
        // //////////////////////////////////////////////////////////////////////////////////////
        if (!empty($data['defaultvalue'])) {
            foreach ($clean_defaultvalue as $default) {
                if (!in_array($default, $values)) {
                    $errors['defaultvalue'] = get_string('defaultvalue_err', 'surveyfield_checkbox', $default);
                    break;
                }
            }
        }

        // //////////////////////////////////////////////////////////////////////////////////////
        // second check
        // each single option item has to be unique
        // each single default item has to be unique
        // //////////////////////////////////////////////////////////////////////////////////////
        $array_unique = array_unique($clean_options);
        if (count($clean_options) != count($array_unique)) {
            $errors['options'] = get_string('options_err', 'surveyfield_checkbox', $default);
        }
        $array_unique = array_unique($clean_defaultvalue);
        if (count($clean_defaultvalue) != count($array_unique)) {
            $errors['defaultvalue'] = get_string('defaultvalue_err', 'surveyfield_checkbox', $default);
        }

// print_object($errors);
// die;
        return $errors;
    }
}