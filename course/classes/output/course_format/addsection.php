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

/**
 * Contains the default section course format output class.
 *
 * @package   core_course
 * @copyright 2020 Ferran Recio <ferran@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_course\output\course_format;

use core_course\course_format;
use renderable;
use templatable;
use moodle_url;
use stdClass;

/**
 * Base class to render a course add section buttons.
 *
 * @package   core_course
 * @copyright 2020 Ferran Recio <ferran@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class addsection implements renderable, templatable {

    /** @var course_format the course format class */
    protected $format;

    /**
     * Constructor.
     *
     * @param course_format $format the course format
     */
    public function __construct(course_format $format) {
        $this->format = $format;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return stdClass data context for a mustache template
     */
    public function export_for_template(\renderer_base $output): stdClass {

        $format = $this->format;
        $course = $format->get_course();
        $options = $format->get_format_options();

        $lastsection = $format->get_last_section_number();
        $maxsections = $format->get_max_sections();

        $data = new stdClass();

        // If no editor must be displayed, just retun an empty structure.
        if (!$format->show_editor()) {
            return $data;
        }

        $supportsnumsections = array_key_exists('numsections', $options);
        if ($supportsnumsections) {
            // Current course format has 'numsections' option, which is very confusing and we suggest course format
            // developers to get rid of it (see MDL-57769 on how to do it).

            if ($lastsection < $maxsections) {
                $data->increase = (object) [
                    'url' => new moodle_url(
                        '/course/changenumsections.php',
                        ['courseid' => $course->id, 'increase' => true, 'sesskey' => sesskey()]
                    ),
                ];
            }

            if ($course->numsections > 0) {
                $data->decrease = (object) [
                    'url' => new moodle_url(
                        '/course/changenumsections.php',
                        ['courseid' => $course->id, 'increase' => false, 'sesskey' => sesskey()]
                    ),
                ];
            }

        } else if (course_get_format($course)->uses_sections() && $lastsection < $maxsections) {
            // Current course format does not have 'numsections' option but it has multiple sections suppport.
            // Display the "Add section" link that will insert a section in the end.
            // Note to course format developers: inserting sections in the other positions should check both
            // capabilities 'moodle/course:update' and 'moodle/course:movesections'.

            if (get_string_manager()->string_exists('addsections', 'format_'.$course->format)) {
                $addstring = get_string('addsections', 'format_'.$course->format);
            } else {
                $addstring = get_string('addsections');
            }

            $params = ['courseid' => $course->id, 'insertsection' => 0, 'sesskey' => sesskey()];

            $singlesection = $this->format->get_section_number();
            if ($singlesection) {
                $params['sectionreturn'] = $singlesection;
            }

            $data->addsections = (object) [
                'url' => new moodle_url('/course/changenumsections.php', $params),
                'title' => $addstring,
                'newsection' => $maxsections - $lastsection,
            ];
        }

        if (count((array)$data)) {
            $data->showaddsection = true;
        }

        return $data;
    }
}