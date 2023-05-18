<?php
/*
 * @license http://www.gnu.org/licenses/gpl-3.0.html  GNU GPL v3
 */

require_once dirname(__DIR__) . '/includes/lib.php';

access_ensure_project_level(config_get('view_summary_threshold'));

$projectId = helper_get_current_project();
$after = $_GET['after'] ?? $_GET['start'] ?? '';
if (!preg_match('/^\d{4}-\d\d-\d\d$/', $after)) {
	$after = '';
}
$before = $_GET['before'] ?? $_GET['end'] ?? '';
if (!preg_match('/^\d{4}-\d\d-\d\d$/', $before)) {
	$before = '';
}
$timerows = \timereporting\readCategoriesTime($projectId, $after, $before);

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

layout_page_header("Rapport d'utilisation du temps");
layout_page_begin();

$filter = summary_get_filter();
print_summary_menu('TimeReporting/report', $filter);
?>

<h1>
    Rapport du temps consommé
</h1>

<div id="time-per-project" class="summary-container">
    <h2>Temps par catégories</h2>
    <?php
    foreach ($projects as $pid => $project) {
        echo "<h3>" . htmlspecialchars($project['name']) . "</h3>\n";
        $total = [0, 0, 0];
        ?>
        <table class="table table-bordered table-condensed table-hover table-striped" style="max-width: 70em">
            <thead>
                <tr class="row-category">
                    <th>Catégorie</th>
                    <th class="align-right">Temps consacré</th>
                    <th class="align-right">#tickets</th>
                    <th class="align-right">#notes</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($project['categories'] as $row) {
                    $total[0] += $row['time'];
                    $total[1] += $row['tickets'];
                    $total[2] += $row['notes'];
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                    echo '<td class="column-num">' . db_minutes_to_hhmm($row['time']) . "</td>";
                    echo '<td class="column-num">' . $row['tickets'] . "</td>";
                    echo '<td class="column-num">' . $row['notes'] . "</td>";
                    echo "</tr>\n";
                }
                ?>
                <tr>
                    <th>Total</th>
                    <td class="column-num"><?= db_minutes_to_hhmm($total[0]) ?></td>
                    <td class="column-num"><?= $total[1] ?></td>
                    <td class="column-num"><?= $total[2] ?></td>
                </tr>
            </tbody>
        </table>
    <?php
    }
    ?>
    <p>
        Les durées sont exprimées sous la forme <em>hh:mm</em> (heures et minutes).
    </p>

    <div class="panel panel-default">
        <div class="panel-heading">
            Limiter ce tableau à une période (limites incluses)
        </div>
        <div class="panel-body">
            <form method="GET" class="form-horizontal">
                <input type="hidden" name="page" value="TimeReporting/report" />
                <div class="form-group">
                    <label class="control-label col-sm-2" for="after">À partir de</label>
                    <div class="col-sm-10"><input type="date" name="after" value="<?= $after ?>" class="form-control" /></div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2" for="before">Jusqu'à</label>
                    <div class="col-sm-10"><input type="date" name="before" value="<?= $before ?>" class="form-control" /></div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10"><button type="submit" class="btn btn-primary">Afficher</button></div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
layout_page_end();
