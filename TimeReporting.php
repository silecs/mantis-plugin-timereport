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
        $this->description = "Plugin that display reports about the used time.";
        $this->page = 'report';

        $this->version = '1.0';
        $this->requires = [
            'MantisCore' => '1.3.0, < 2.0',
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
            'EVENT_MENU_SUMMARY' => 'onMenuSummary',
        ];
    }

    /**
     * Add entries to the menu on the page "Summary".
     *
     * @return array
     */
    public function onMenuSummary()
    {
        return [
            '<a href="' . plugin_page('report') . '">Tableaux du temps passé</a>',
        ];
    }
}
