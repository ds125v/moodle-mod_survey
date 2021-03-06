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

class survey_searchform extends moodleform {

    function definition() {
        global $CFG, $DB;

        $mform = $this->_form;

        $cmid = $this->_customdata->cmid;
        $survey = $this->_customdata->survey;
        $canaccessadvancedform = $this->_customdata->canaccessadvancedform;

        $sql = survey_fetch_items_seeds($canaccessadvancedform, true);
        $params = array('surveyid' => $survey->id);
        // in the search form the page is not relevant

        $itemseeds = $DB->get_recordset_sql($sql, $params);

        $context = context_module::instance($cmid);

        foreach ($itemseeds as $itemseed) {
            // is the current item matching the parent value?
            $item = survey_get_item($itemseed->id, $itemseed->type, $itemseed->plugin);

            if (isset($item->extrarow) && $item->extrarow) {
                $elementnumber = $item->customnumber ? $item->customnumber.':' : '';

                $output = file_rewrite_pluginfile_urls($item->content, 'pluginfile.php', $context->id, 'mod_survey', SURVEY_ITEMCONTENTFILEAREA, $item->itemid);
                $mform->addElement('static', $item->type.'_'.$item->itemid.'_extrarow', $elementnumber, $output, array('class' => 'indent-'.$item->indent)); // here I  do not strip tags to content
            }

            $item->userform_mform_element($mform, $survey, $canaccessadvancedform, null, true);

            if ($fullinfo = $item->item_get_full_info(true)) {
                $mform->addElement('static', $item->type.'_'.$item->itemid.'_info', get_string('note', 'survey'), $fullinfo);
            }
        }
        $itemseeds->close();

        // -------------------------------------------------------------------------------
        // buttons
        // $this->add_action_buttons(true, get_string('search'));
        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('search'));
        $buttonarray[] = $mform->createElement('cancel', 'cancel', get_string('findall', 'survey'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
    }

    // function validation($data, $files) {
    // }
}