<?php

if (!defined(''))
{
    define(LAN_HELP_TITLE, "Help");
    define(LAN_HELP_BUG, "Bugs");
    define(LAN_HELP_LINK, "For help with this plugin");
    define(LAN_HELP_BUGS, "To report a bug in this plugin");
    define(LAN_HELP_VERSIONTEXT, "A newer version is available");
    define(LAN_HELP_VERSION, "Github");

}
$helpObj = new eversion();
$helplink_text=$helpObj->runHelp();
$ns->tablerender(LAN_HELP_TITLE, $helplink_text, 'hduhelp');
class eversion
{
    function __construct()
    {
        $this->plugname = basename(__dir__ );
        $this->name = "e107:plugins:" . $this->plugname;
        $this->thisDay = date('z') + 3;
        $this->lastRemoteCheck = e107::pref($this->plugname, 'lastRemoteCheck');
   //     print_a($this->thisDay);
   //     print_a($this->lastRemoteCheck);
        $this->localVersion = e107::pref($this->plugname, 'localVersion');
        $this->remoteVersion = e107::pref($this->plugname, 'remoteVersion');
    }
    public function runHelp()
    {
        $this->getLocal();
        if ($this->thisDay != $this->lastRemoteCheck)
        {
            $this->getRemote();
        }
        $this->saveSettings();
        $helplink_text = "<div style='width=100%;margin:0 auto;text-align: center;' >";
        $helplink_text .= $this->buttonHelp();
        $helplink_text .= $this->buttonBugs();

        $this->result = version_compare($this->remoteVersion, $this->localVersion);
        if ($this->result === 1)
        {
            $helplink_text .= $this->buttonVersion();

        }
        $helplink_text .= "</div>";
        return $helplink_text;
    }
    private function getRemote()
    {
        $remoteFile = file_get_contents('https://raw.githubusercontent.com/G4HDU-plugins/' . $this->plugname . '/master/plugin.xml');
        $remotePosn = strpos($remoteFile, 'version="', 40) + 9;
        $remoteEnding = strpos($remoteFile, "\"", $remotePosn);
        $this->remoteVersion = substr($remoteFile, $remotePosn, $remoteEnding - $remotePosn);
        //    print_a($this->remoteVersion);
    }
    private function getLocal()
    {
        $localFile = file_get_contents('plugin.xml');
        $localPosn = strpos($localFile, 'version="', 40) + 9;
        $localEnding = strpos($localFile, "\"", $localPosn);
        $this->localVersion = substr($localFile, $localPosn, $localEnding - $localPosn);


    }
    private function saveSettings()
    {
        $settings = e107::getConfig($this->plugname);
        $settings->setPref('lastRemoteCheck', $this->thisDay);
        $settings->setPref('localVersion', $this->localVersion);
        $settings->setPref('remoteVersion', $this->remoteVersion);
        $settings->save(false, true, false);
    }
    private function buttonHelp()
    {
        $retval = LAN_HELP_LINK . "<br>
    <a href='http://manual.keal.me.uk/doku.php?id=e107:plugins:{$this->plugname}' id='HelpHelp' target='_blank'>                    
        <button type='button' class='btn btn-info' style='font-size:14px;color:white;'>
            <i class='fa fa-info' aria-hidden='true'></i> " . LAN_HELP_TITLE . "
        </button>
    </a>";
        return $retval;
    }
    private function buttonBugs()
    {
        $retval = "<br><br>" . LAN_HELP_BUGS . "<br>
    <a href='https://github.com/G4HDU-plugins/{$this->plugname}/issues' id='HelpBugs' target='_blank'>                    
        <button type='button' class='btn btn-info' style='font-size:14px;color:white;'>
       <i class='fa fa-bug' aria-hidden='true'></i> " . LAN_HELP_BUG . "
        </button>
    </a>";
        return $retval;
    }
    private function buttonVersion()
    {
        $retval = "<br><br>" . LAN_HELP_VERSIONTEXT . "<br>
    <a href='https://github.com/G4HDU-plugins/{$this->plugname}/tree/master' id='HelpVersion' target='_blank'>                    
        <button type='button' class='btn btn-info' style='font-size:14px;color:white;'>
       <i class='fa fa-download' aria-hidden='true'></i> " . LAN_HELP_VERSION . "
        </button>
    </a>";
        return $retval;
    }

}
