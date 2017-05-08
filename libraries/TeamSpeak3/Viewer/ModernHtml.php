<?php

class Teamspeak3_Viewer_ModernHtml extends TeamSpeak3_Viewer_Html
{
    /**
     * Available icons.
     *
     * @var array
     */
    protected $icons = [
        'channel_flag_default',
        'channel_flag_moderated',
        'channel_flag_music',
        'channel_flag_password',
        'channel_full',
        'channel_open',
        'channel_pass',
        'client_away',
        'client_cc_idle',
        'client_cc_talk',
        'client_idle',
        'client_mic_disabled',
        'client_mic_muted',
        'client_priority',
        'client_snd_disabled',
        'client_snd_muted',
        'client_talk',
        'client_talker',
        'group_icon_0',
        'group_icon_100',
        'group_icon_200',
        'group_icon_300',
        'group_icon_500',
        'group_icon_600',
        'server_full',
        'server_open',
        'server_pass',
    ];

    /**
     * A pre-defined pattern used to display a node in a TeamSpeak 3 viewer.
     *
     * @var string
     */
    protected $pattern = "<li id='%0' class='%1' data-summary='%2'><span class='%3' title='%4'>%5 %6</span><span class='%7'>%8%9</span></li>\n";


    /**
     * Returns the code needed to display a node in a TeamSpeak 3 viewer.
     *
     * @param  TeamSpeak3_Node_Abstract $node
     * @param  array $siblings
     * @return string
     */
    public function fetchObject(TeamSpeak3_Node_Abstract $node, array $siblings = array())
    {
        $this->currObj = $node;
        $this->currSib = $siblings;

        $args = array(
            $this->getContainerIdent(),
            $this->getContainerClass(),
            $this->getContainerSummary(),
            $this->getCorpusClass(),
            $this->getCorpusTitle(),
            $this->getCorpusIcon(),
            $this->getCorpusName(),
            $this->getSuffixClass(),
            $this->getSuffixIcon(),
            $this->getSuffixFlag(),
        );

        // Handle server
        $pattern = $this->pattern;
        if($this->currObj instanceof TeamSpeak3_Node_Server) {

            $pattern = str_replace('<li', '<div', $pattern);
            $pattern = str_replace('</li>', '</div>', $pattern);
        }

        return TeamSpeak3_Helper_String::factory($pattern)->arg($args);
    }

    protected function getSuffixFlag()
    {
        if(!$this->currObj instanceof TeamSpeak3_Node_Client) return;

        if($this->currObj["client_country"])
        {
            return $this->getFlag($this->currObj["client_country"]);
        }
    }

    /**
     * Returns a HTML img tag which can be used to display the status icon for a
     * TeamSpeak_Node_Abstract object.
     *
     * @return string
     */
    protected function getCorpusIcon()
    {
        if($this->currObj instanceof TeamSpeak3_Node_Channel && $this->currObj->isSpacer()) return;

        $icon = $this->currObj->getIcon();

        if(! in_array($icon, $this->icons)) {
            $icon = 'group_icon_0';
        }

        return $this->getIcon($icon);
    }

    /**
     * Returns the HTML img tags which can be used to display the various icons for a
     * TeamSpeak_Node_Server object.
     *
     * @return string
     */
    protected function getSuffixIconServer()
    {
        $html = "";

        if($this->currObj["virtualserver_icon_id"])
        {
            if(!$this->currObj->iconIsLocal("virtualserver_icon_id") && $this->ftclient)
            {
                if(!isset($this->cacheIcon[$this->currObj["virtualserver_icon_id"]]))
                {
                    $download = $this->currObj->transferInitDownload(rand(0x0000, 0xFFFF), 0, $this->currObj->iconGetName("virtualserver_icon_id"));

                    if($this->ftclient == "data:image")
                    {
                        $download = TeamSpeak3::factory("filetransfer://" . $download["host"] . ":" . $download["port"])->download($download["ftkey"], $download["size"]);
                    }

                    $this->cacheIcon[$this->currObj["virtualserver_icon_id"]] = $download;
                }
                else
                {
                    $download = $this->cacheIcon[$this->currObj["virtualserver_icon_id"]];
                }

                if($this->ftclient == "data:image")
                {
                    $html .= $this->getImage("data:" . TeamSpeak3_Helper_Convert::imageMimeType($download) . ";base64," . base64_encode($download), "Server Icon", null, FALSE);
                }
                else
                {
                    $html .= $this->getImage($this->ftclient . "?ftdata=" . base64_encode(serialize($download)), "Server Icon", null, FALSE);
                }
            }
            elseif(in_array($this->currObj["virtualserver_icon_id"], $this->cachedIcons))
            {
                $html .= $this->getIcon('group_icon_' . $this->currObj['virtualserver_icon_id'], "Server Icon");
            }
        }

        return $html;
    }

    /**
     * Returns the HTML img tags which can be used to display the various icons for a
     * TeamSpeak_Node_Channel object.
     *
     * @return string
     */
    protected function getSuffixIconChannel()
    {
        if($this->currObj instanceof TeamSpeak3_Node_Channel && $this->currObj->isSpacer()) return;

        $html = "";

        if($this->currObj["channel_flag_default"])
        {
            $html .= $this->getIcon('channel_flag_default', 'Default Channel');
        }

        if($this->currObj["channel_flag_password"])
        {
            $html .= $this->getIcon('channel_flag_password', 'Password-protected');
        }

        if($this->currObj["channel_codec"] == TeamSpeak3::CODEC_CELT_MONO || $this->currObj["channel_codec"] == TeamSpeak3::CODEC_OPUS_MUSIC)
        {
            $html .= $this->getIcon('channel_flag_music', 'Music Codec');
        }

        if($this->currObj["channel_needed_talk_power"])
        {
            $html .= $this->getIcon('channel_flag_moderated', 'Moderated');
        }

        if($this->currObj["channel_icon_id"])
        {
            if(!$this->currObj->iconIsLocal("channel_icon_id") && $this->ftclient)
            {
                if(!isset($this->cacheIcon[$this->currObj["channel_icon_id"]]))
                {
                    $download = $this->currObj->getParent()->transferInitDownload(rand(0x0000, 0xFFFF), 0, $this->currObj->iconGetName("channel_icon_id"));

                    if($this->ftclient == "data:image")
                    {
                        $download = TeamSpeak3::factory("filetransfer://" . $download["host"] . ":" . $download["port"])->download($download["ftkey"], $download["size"]);
                    }

                    $this->cacheIcon[$this->currObj["channel_icon_id"]] = $download;
                }
                else
                {
                    $download = $this->cacheIcon[$this->currObj["channel_icon_id"]];
                }

                if($this->ftclient == "data:image")
                {
                    $html .= $this->getImage("data:" . \TeamSpeak3_Helper_Convert::imageMimeType($download) . ";base64," . base64_encode($download), "Channel Icon", null, FALSE);
                }
                else
                {
                    $html .= $this->getImage($this->ftclient . "?ftdata=" . base64_encode(serialize($download)), "Channel Icon", null, FALSE);
                }
            }
            elseif(in_array($this->currObj["channel_icon_id"], $this->cachedIcons))
            {
                $html .= $this->getIcon('group_icon_' . $this->currObj['channel_icon_id'], 'Channel Icon');
            }
        }

        return $html;
    }

    /**
     * Returns the HTML img tags which can be used to display the various icons for a
     * TeamSpeak_Node_Client object.
     *
     * @return string
     */
    protected function getSuffixIconClient()
    {
        $html = "";

        if($this->currObj["client_is_priority_speaker"])
        {
            $html .= $this->getImage("client_priority.png", "Priority Speaker");
        }

        if($this->currObj["client_is_channel_commander"])
        {
            $html .= $this->getImage("client_cc.png", "Channel Commander");
        }

        if($this->currObj["client_is_talker"])
        {
            $html .= $this->getImage("client_talker.png", "Talk Power granted");
        }
        elseif($cntp = $this->currObj->getParent()->channelGetById($this->currObj["cid"])->channel_needed_talk_power)
        {
            if($cntp > $this->currObj["client_talk_power"])
            {
                $html .= $this->getImage("client_mic_muted.png", "Insufficient Talk Power");
            }
        }

        foreach($this->currObj->memberOf() as $group)
        {
            if(!$group["iconid"]) continue;

            $type = ($group instanceof TeamSpeak3_Node_Servergroup) ? "Server Group" : "Channel Group";

            if(!$group->iconIsLocal("iconid") && $this->ftclient)
            {
                if(!isset($this->cacheIcon[$group["iconid"]]))
                {
                    $download = $group->getParent()->transferInitDownload(rand(0x0000, 0xFFFF), 0, $group->iconGetName("iconid"));

                    if($this->ftclient == "data:image")
                    {
                        $download = TeamSpeak3::factory("filetransfer://" . $download["host"] . ":" . $download["port"])->download($download["ftkey"], $download["size"]);
                    }

                    $this->cacheIcon[$group["iconid"]] = $download;
                }
                else
                {
                    $download = $this->cacheIcon[$group["iconid"]];
                }

                if($this->ftclient == "data:image")
                {
                    $html .= $this->getImage("data:" . TeamSpeak3_Helper_Convert::imageMimeType($download) . ";base64," . base64_encode($download), $group . " [" . $type . "]", null, FALSE);
                }
                else
                {
                    $html .= $this->getImage($this->ftclient . "?ftdata=" . base64_encode(serialize($download)), $group . " [" . $type . "]", null, FALSE);
                }
            }
            elseif(in_array($group["iconid"], $this->cachedIcons))
            {
                $html .= $this->getIcon('group_icon_' . $group['iconid'], $group . ' [' . $type . ']');
            }
        }

        if($this->currObj["client_icon_id"])
        {
            if(!$this->currObj->iconIsLocal("client_icon_id") && $this->ftclient)
            {
                if(!isset($this->cacheIcon[$this->currObj["client_icon_id"]]))
                {
                    $download = $this->currObj->getParent()->transferInitDownload(rand(0x0000, 0xFFFF), 0, $this->currObj->iconGetName("client_icon_id"));

                    if($this->ftclient == "data:image")
                    {
                        $download = TeamSpeak3::factory("filetransfer://" . $download["host"] . ":" . $download["port"])->download($download["ftkey"], $download["size"]);
                    }

                    $this->cacheIcon[$this->currObj["client_icon_id"]] = $download;
                }
                else
                {
                    $download = $this->cacheIcon[$this->currObj["client_icon_id"]];
                }

                if($this->ftclient == "data:image")
                {
                    $html .= $this->getImage("data:" . TeamSpeak3_Helper_Convert::imageMimeType($download) . ";base64," . base64_encode($download), "Client Icon", null, FALSE);
                }
                else
                {
                    $html .= $this->getImage($this->ftclient . "?ftdata=" . base64_encode(serialize($download)), "Client Icon", null, FALSE);
                }
            }
            elseif(in_array($this->currObj["client_icon_id"], $this->cachedIcons))
            {
                $html .= $this->getImage("group_icon_" . $this->currObj["client_icon_id"] . ".png", "Client Icon");
            }
        }

        return $html;
    }

    protected function getSvg($name, $type, $title = null)
    {
        $name = strtolower($name);

        return
            '<svg class="'.$type.' '.$name.'" ' . (!is_null($title) ? 'title="'.$title.'"' : '') . '>
                <use xlink:href="#'.$type.'-'.$name.'"></use>
            </svg>';
    }

    protected function getFlag($country, $title = null)
    {
        return $this->getSvg($country, 'flag', $title);
    }

    protected function getIcon($name, $title = null)
    {
        return $this->getSvg($name, 'ts3', $title);
    }
}
