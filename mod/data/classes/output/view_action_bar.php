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

namespace mod_data\output;

use moodle_url;
use templatable;
use renderable;

/**
 * Renderable class for the action bar elements in the view pages in the database activity.
 *
 * @package    mod_data
 * @copyright  2021 Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class view_action_bar implements templatable, renderable {

    /** @var int $id The database module id. */
    private $id;

    /** @var \url_select $urlselect The URL selector object. */
    private $urlselect;

    /** @var bool $hasentries Whether entries exist. */
    private $hasentries;

    /** @var int $numentries Number of entries made by the user. */
    private $numentries;

    /** @var int $maxentries Maximum number of entries allowed. */
    private $maxentries;

    /**
     * The class constructor.
     *
     * @param int $id The database module id.
     * @param \url_select $urlselect The URL selector object.
     * @param bool $hasentries Whether entries exist.
     * @param int|null $numentries Number of entries made by the user.
     * @param int|null $maxentries Maximum number of entries allowed.
     */
    public function __construct(int $id, \url_select $urlselect, bool $hasentries,
            ?int $numentries = null, ?int $maxentries = null) {
        $this->id = $id;
        $this->urlselect = $urlselect;
        $this->hasentries = $hasentries;
        $this->numentries = $numentries;
        $this->maxentries = $maxentries;
    }

    /**
     * Export the data for the mustache template.
     *
     * @param \renderer_base $output The renderer to be used to render the action bar elements.
     * @return array
     */
    public function export_for_template(\renderer_base $output): array {
        global $PAGE;

        $showaddentry = true;
        if (!has_capability('mod/data:manageentries', $PAGE->context)) {
            if ($this->maxentries > 0 && $this->numentries >= $this->maxentries) {
                $showaddentry = false;
            }
        }

        $data = [
            'urlselect' => $this->urlselect->export_for_template($output),
        ];

        if ($showaddentry) {
            $addentrylink = new moodle_url('/mod/data/edit.php', ['d' => $this->id, 'backto' => $PAGE->url->out(false)]);
            $addentrybutton = new \single_button($addentrylink, get_string('add', 'mod_data'), 'get', true);
            $data['addentrybutton'] = $addentrybutton->export_for_template($output);
        }

        if (has_capability('mod/data:manageentries', $PAGE->context)) {
            $importentrieslink = new moodle_url('/mod/data/import.php',
                ['d' => $this->id, 'backto' => $PAGE->url->out(false)]);
            $importentriesbutton = new \single_button($importentrieslink,
                get_string('importentries', 'mod_data'), 'get', false);
            $data['importentriesbutton'] = $importentriesbutton->export_for_template($output);
        }

        if (has_capability(DATA_CAP_EXPORT, $PAGE->context) && $this->hasentries) {
            $exportentrieslink = new moodle_url('/mod/data/export.php',
                ['d' => $this->id, 'backto' => $PAGE->url->out(false)]);
            $exportentriesbutton = new \single_button($exportentrieslink, get_string('exportentries', 'mod_data'),
                'get', false);
            $data['exportentriesbutton'] = $exportentriesbutton->export_for_template($output);
        }

        return $data;
    }
}