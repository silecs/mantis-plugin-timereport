<?php
/*
 * @license http://www.gnu.org/licenses/gpl-3.0.html  GNU GPL v3
 */

namespace timereporting;

/**
 * Array of id, name, parent_id, timeused, tt.timecredit
 *
 * @param int $projectId
 * @return IteratorAggregate
 */
function readCategoriesTime($projectId)
{
    if (empty($projectId) || $projectId == ALL_PROJECTS) {
        $projects = current_user_get_accessible_projects();
    } else {
        $projects = [(int) $projectId];
    }
    $ids = join(',', $projects);

    $sql = "SELECT p.id, p.name, c.id AS category_id, c.name AS category_name, SUM(bn.time_tracking) AS used_time, COUNT(b.id) AS used_tickets
            FROM {project} p
            LEFT JOIN {category} c ON p.id = c.project_id
            LEFT JOIN {bug} b ON b.project_id = p.id AND b.category_id = c.id
            LEFT JOIN {bugnote} bn ON bn.bug_id = b.id
            WHERE p.id IN ($ids)
            GROUP BY p.id, c.id
            ORDER BY p.name, c.name";

    return db_query($sql);
}

/**
 * If the parameter contains no ':', then it implies a suffix ':00'.
 *
 * @param string $hhmm
 * @return integer
 */
function convertHhmmToMinutes($hhmm)
{
    $m = [];
    if (ctype_digit($hhmm)) {
        return 60 * ((int) $hhmm);
    } else if (preg_match('/^(\d+):(\d\d?)$/', $hhmm, $m)) {
        return $m[1] * 60 + $m[2];
    } else if (preg_match('/^(\d+\.\d\d?)$/', $hhmm, $m)) {
        return (int) (((float) $m[1]) * 60);
    } else {
        // invalid format
        return 0;
    }
}
