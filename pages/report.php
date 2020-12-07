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
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($project['categories'] as $row) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                    echo "<td>" . db_minutes_to_hhmm($row['time']) . "</td>";
                    echo "<td>" . $row['tickets'] . "</td>";
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

<?php
if (isset($info['description'])) {
    echo "<h2>Détails</h2>";
    echo '<div id="project-info">'
    . nl2br($info['description']) // no filter, but on purpose!
    . "</div>\n";
}
?>

<?php
if (isset($info['id']) && timeaccount\canCreditTime($info['id'])) {
    $credit = ($info['timecredit'] ? db_minutes_to_hhmm($info['timecredit']) : '');
    ?>
    <h2>Administation du crédit de temps</h2>
    <div class="form-container">
        <form method="post" action="<?= plugin_page('update-project') ?>">
            <fieldset>
                <legend><?= htmlspecialchars($info['name']) ?></legend>

                <input type="hidden" name="project_id" value="<?= $info['id'] ?>" />

                <div class="field-container">
                    <label><span>Crédit de temps</span></label>
                    <span class="input">
                        <input type="text" name="timecredit" value="<?= $credit ?>" />
                        (hh:mm ou hh.h)
                    </span>
                    <span class="label-style"></span>
                </div>

                <div class="field-container">
                    <label><span>Commentaire public</span><br />(HTML brut + nl2br)</label>
                    <span class="input">
                        <textarea cols="74" rows="12" name="description"><?= htmlspecialchars($info['description']) ?></textarea>
                    </span>
                    <span class="label-style"></span>
                </div>

                <span class="submit-button">
                    <button type="submit" class="button">Enregistrer</button>
                </span>
            </fieldset>
        </form>
    </div>
    <?php
}
?>

<?php
html_page_bottom();
