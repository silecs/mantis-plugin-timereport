<?php
/*
 * @license http://www.gnu.org/licenses/gpl-3.0.html  GNU GPL v3
 */

require_once dirname(__DIR__) . '/includes/lib.php';

access_ensure_project_level(config_get('view_summary_threshold'));

$projectId = helper_get_current_project();
$timerows = \timereporting\readCategoriesTime($projectId);
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
        $projects[$id]['categories'][$cid] = [
            'name' => $row['category_name'],
            'notes' => (int) $row['used_notes'],
            'tickets' => (int) $row['used_tickets'],
            'time' => (int) $row['used_time'],
        ];
    }
}

html_page_top();
?>

<h1>
    Rapport d'utilisation du temps
</h1>

<div id="time-per-project" class="summary-container">
    <h2>Temps par catégories</h2>
    <?php
    foreach ($projects as $pid => $project) {
        echo "<h3>" . htmlspecialchars($project['name']) . "</h3>\n";
        ?>
        <table>
            <thead>
                <tr class="row-category">
                    <th>Catégorie</th>
                    <th>Temps consacré</th>
                    <th>#tickets</th>
                    <th>#notes</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($project['categories'] as $row) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                    echo "<td>" . db_minutes_to_hhmm($row['time']) . "</td>";
                    echo "<td>" . $row['tickets'] . "</td>";
                    echo "<td>" . $row['notes'] . "</td>";
                    echo "</tr>\n";
                }
                ?>
            </tbody>
        </table>
    <?php
    }
    ?>
    <p>
        Les durées sont exprimées sous la forme <em>hh:mm</em> (heures et minutes).
    </p>
</div>
