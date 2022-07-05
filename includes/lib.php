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
function readCategoriesTime($projectId, string $after, string $before)
{
    if (empty($projectId) || $projectId == ALL_PROJECTS) {
        $projects = current_user_get_accessible_projects();
    } else {
        $projects = [(int) $projectId];
    }
    $ids = join(',', $projects);

    if ($after && $before) {
        $start = \DateTimeImmutable::createFromFormat('Y-m-d H:i', "$after 00:00")->getTimestamp();
        $end = \DateTimeImmutable::createFromFormat('Y-m-d H:i', "$before 23:59")->getTimestamp();
        $condition = "AND bn.date_submitted BETWEEN $start AND $end";
    } elseif ($after) {
        $start = \DateTimeImmutable::createFromFormat('Y-m-d H:i', "$after 00:00")->getTimestamp();
        $condition = "AND bn.date_submitted > $start";
    } elseif ($before) {
        $end = \DateTimeImmutable::createFromFormat('Y-m-d H:i', "$before 23:59")->getTimestamp();
        $condition = "AND bn.date_submitted < $end";
    } else {
        $condition = "";
    }

    $sql = "SELECT p.id, p.name, c.id AS category_id, c.name AS category_name,
                SUM(bn.time_tracking) AS used_time, COUNT(DISTINCT b.id) AS used_tickets, COUNT(DISTINCT bn.id) AS used_notes
            FROM {project} p
            LEFT JOIN {category} c ON p.id = c.project_id
            LEFT JOIN {bug} b ON b.project_id = p.id AND b.category_id = c.id
            LEFT JOIN {bugnote} bn ON bn.bug_id = b.id
            WHERE p.id IN ($ids) $condition
            GROUP BY p.id, c.id
            ORDER BY p.name, c.name";

    return db_query($sql);
}
