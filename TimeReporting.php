<?php
/**
 * @license http://www.gnu.org/licenses/gpl-3.0.html  GNU GPL v3
 */

/**
 * TimeReporting is a Mantis plugin.
 *
 * @author François Gannaz <francois.gannaz@silecs.info>
 */
class TimeReportingPlugin extends MantisPlugin
{
    /**
     * Init the plugin attributes.
     */
    function register()
    {
        $this->name = 'Time Reporting';
        $this->description = "Plugin that displays reports about the used time.";
        $this->page = 'report';

        $this->version = '2.0';
        $this->requires = [
            'MantisCore' => '2.0.0',
        ];

        $this->author = 'François Gannaz / Silecs';
        $this->contact = 'francois.gannaz@silecs.info';
        $this->url = '';
    }

    /**
     * Declare hooks on Mantis events.
     *
     * @return array
     */
    public function hooks()
    {
        return [
            'EVENT_LAYOUT_RESOURCES' => 'addHtmlHeadContent',
            'EVENT_MENU_SUMMARY' => 'onMenuSummary',
        ];
    }

    /**
     * Add entries to the menu on the page "Summary".
     *
     * @return array
     */
    public function onMenuSummary(): array
    {
        return [
            '<a href="' . plugin_page('report') . '">Tableaux du temps</a>',
        ];
    }

    function addHtmlHeadContent(): string
    {
        return <<<EOHTML
<style>
    .column-num {
        font-family: monospace;
        text-align: right;
    }
</style>
EOHTML
        ;
    }
}
