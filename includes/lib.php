<?php
/*
 * @license http://www.gnu.org/licenses/gpl-3.0.html  GNU GPL v3
 */

namespace timereporting;

/**
 * @return array<int, list{name: string, categories: array}>  E.g. $projects[$pid]['categories'][$cid]['time']
 */
function getProjectTimeByCategory($projectId, string $after, string $before): array
{
    $timerows = readCategoriesTime($projectId, $after, $before);
    $projects = [];
    foreach ($timerows as $row) {
        $id = (int) $row['id'];
        if (!isset($projects[$id])) {
            $projects[$id] = [
                'name' => $row['name'],
                'categories' => [],
            ];
        }
        $cid = (int) $row['category_id'];
        if (!isset($projects[$id]['categories'][$cid])) {
            if ($cid > 0) {
                $name = $row['category_name'];
            } else {
                $name = "-";
            }
            $projects[$id]['categories'][$cid] = [
                'name' => $name,
                'notes' => (int) $row['used_notes'],
                'tickets' => (int) $row['used_tickets'],
                'time' => (int) $row['used_time'],
            ];
        }
    }
    return $projects;
}

/**
 * @return array<int, list{name: string, users: array}>  E.g. $projects[$pid]['user'][$uid]['time']
 */
function getProjectTimeByUser($projectId, string $after, string $before): array
{
    $timerows = readUsersTime($projectId, $after, $before);
    $projects = [];
    foreach ($timerows as $row) {
        $id = (int) $row['id'];
        if (!isset($projects[$id])) {
            $projects[$id] = [
                'name' => $row['name'],
                'users' => [],
            ];
        }
        $uid = (int) $row['user_id'];
        if (!isset($projects[$id]['users'][$uid])) {
            if ($uid > 0) {
                $name = $row['user_name'];
            } else {
                $name = "-";
            }
            $projects[$id]['users'][$uid] = [
                'name' => $name,
                'notes' => (int) $row['used_notes'],
                'tickets' => (int) $row['used_tickets'],
                'time' => (int) $row['used_time'],
            ];
        }
    }
    return $projects;
}

/**
 * Array of id, name, parent_id, timeused, tt.timecredit
 *
 * @internal
 * @param int $projectId
 * @return IteratorAggregate
 */
function readCategoriesTime($projectId, string $after, string $before)
{
    $ids = getIds($projectId);
    $condition = getCondition($after, $before);
    $sql = <<<EOSQL
        SELECT
            p.id, p.name,
            c.id AS category_id, c.name AS category_name,
            SUM(bn.time_tracking) AS used_time,
            COUNT(DISTINCT b.id) AS used_tickets,
            COUNT(DISTINCT bn.id) AS used_notes
        FROM {project} p
            LEFT JOIN {category} c ON p.id = c.project_id
            LEFT JOIN {bug} b ON b.project_id = p.id AND b.category_id = c.id
            LEFT JOIN {bugnote} bn ON bn.bug_id = b.id
        WHERE p.id IN ($ids) $condition
        GROUP BY p.id, c.id
        ORDER BY p.name, c.name
        EOSQL;
    return db_query($sql);
}

/**
 * Array of id, name, parent_id, timeused, tt.timecredit
 *
 * @internal
 * @param int $projectId
 * @return IteratorAggregate
 */
function readUsersTime($projectId, string $after, string $before)
{
    $ids = getIds($projectId);
    $condition = getCondition($after, $before);
    $sql = <<<EOSQL
        SELECT
            p.id, p.name,
            u.id AS user_id, u.realname AS user_name,
            SUM(bn.time_tracking) AS used_time,
            COUNT(DISTINCT b.id) AS used_tickets,
            COUNT(DISTINCT bn.id) AS used_notes
        FROM {project} p
            LEFT JOIN {bug} b ON b.project_id = p.id
            LEFT JOIN {bugnote} bn ON bn.bug_id = b.id
            LEFT JOIN {user} u ON bn.reporter_id = u.id
        WHERE p.id IN ($ids) $condition
        GROUP BY p.id, u.id
        ORDER BY p.name, used_time DESC, used_notes DESC
        EOSQL;
    return db_query($sql);
}

/**
 * @internal
 */
function getIds($projectId)
{
    if (empty($projectId) || $projectId == ALL_PROJECTS) {
        $projects = current_user_get_accessible_projects();
    } else {
        $projects = [(int) $projectId];
    }
    return join(',', $projects);
}

/**
 * @internal
 */
function getCondition(string $after, string $before)
{
    if ($after && $before) {
        $start = \DateTimeImmutable::createFromFormat('Y-m-d H:i', "$after 00:00")->getTimestamp();
        $end = \DateTimeImmutable::createFromFormat('Y-m-d H:i', "$before 23:59")->getTimestamp();
        return "AND bn.date_submitted BETWEEN $start AND $end";
    }
    if ($after) {
        $start = \DateTimeImmutable::createFromFormat('Y-m-d H:i', "$after 00:00")->getTimestamp();
        return "AND bn.date_submitted > $start";
    }
    if ($before) {
        $end = \DateTimeImmutable::createFromFormat('Y-m-d H:i', "$before 23:59")->getTimestamp();
        return "AND bn.date_submitted < $end";
    }
    return "";
}
