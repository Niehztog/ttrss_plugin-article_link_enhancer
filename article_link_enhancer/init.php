<?php
class article_link_enhancer extends Plugin
{

    const SETTING_NAME_RESOLVE_REDIRECTS = 'enabled_feeds';
    const SETTING_NAME_REGEXP_TAGS = 'regexp_tags';
    const SETTING_NAME_TAG_PREFIX = 'tag_prefix';
    const SETTING_NAME_REPLACE_EXISTING = 'replace_tags';

    /**
     * @var PluginHost
     */
    private $host;

    function about()
    {
        return array(
            1.0,
            "Resolve HTTP redirects in article links",
            "Niehztog",
            false
        );
    }

    function init($host)
    {
        $this->host = $host;

        $host->add_hook($host::HOOK_ARTICLE_FILTER, $this);
        $host->add_hook($host::HOOK_PREFS_EDIT_FEED, $this);
        $host->add_hook($host::HOOK_PREFS_SAVE_FEED, $this);
    }

    function hook_prefs_edit_feed($feed_id)
    {
        print "<header>" . __("Article links") . "</header>";
        print "<section>";

        $this->render_resolve_redirects($feed_id);
        $this->render_regexp_tags($feed_id);
        $this->render_tag_prefix($feed_id);
        $this->render_replace_existing($feed_id);

        print "</section>";
    }

    function hook_prefs_save_feed($feed_id)
    {
        $this->save_resolve_redirects($feed_id);
        $this->save_regexp_tags($feed_id);
        $this->save_tag_prefix($feed_id);
        $this->save_replace_existing($feed_id);
    }

    function hook_article_filter($article)
    {
        $resolve_redirects = $this->read_setting(self::SETTING_NAME_RESOLVE_REDIRECTS);
        $regexp_tags = $this->read_setting(self::SETTING_NAME_REGEXP_TAGS);
        $tag_prefix = empty($regexp_tags) ? array() : $this->read_setting(self::SETTING_NAME_TAG_PREFIX);
        $replace_existing = empty($regexp_tags) ? array() : $this->host->get($this, self::SETTING_NAME_REPLACE_EXISTING);

        $feedId = $article["feed"]["id"];
        $resolve_redirects_value = array_search($feedId, $resolve_redirects);
        $replace_existing_value = array_search($feedId, $replace_existing);

        $article = $resolve_redirects_value === false ? $article : $this->handle_resolve_redirects($article);
        $article = empty($regexp_tags[$feedId]) ? $article : $this->handle_regexp_tags($article, $regexp_tags[$feedId], !empty($tag_prefix[$feedId]) ? $tag_prefix[$feedId] : '', $replace_existing_value !== false);

        return $article;
    }

    function handle_resolve_redirects($article)
    {
        $cleanUrl = $this->getRedirectUrl($article['link']);
        if ($cleanUrl === false) {
            return $article;
        }

        $urlParsed = parse_url($article['link']);
        $article['link'] = $urlParsed['scheme'] . '://' . $urlParsed['host'] . $cleanUrl;

        return $article;
    }

    function handle_regexp_tags($article, $pattern, $tag_prefix, $replace_existing)
    {
        $link = $article['link'];
        $hits = preg_match_all('~' . $pattern . '~', $link, $matches);
        if($hits == 0 || count($matches) == 1) {
            return $article;
        }

        //array_shift($matches);
        if($replace_existing) {
            $article['tags'] = array();
        }
        foreach($matches[1] as $match) {
            array_push($article['tags'], $tag_prefix . $match);
        }
        $article['tags'] = array_unique($article['tags']);

        return $article;
    }

    function api_version()
    {
        return 2;
    }

    private function getRedirectUrl($url)
    {
        stream_context_set_default(array(
            'http' => array(
                'method' => 'HEAD'
            )
        ));
        $headers = get_headers($url, 1);
        if ($headers !== false && isset($headers['Location'])) {
            return is_array($headers['Location']) ? array_pop($headers['Location']) : $headers['Location'];
        }
        return false;
    }

    /**
     * @param $feed_id
     */
    private function render_resolve_redirects($feed_id)
    {
        $resolve_redirects = $this->read_setting(self::SETTING_NAME_RESOLVE_REDIRECTS);

        $key = array_search($feed_id, $resolve_redirects);
        $checked = $key !== FALSE ? "checked" : "";

        print "<fieldset>";

        print "<label class='checkbox'><input dojoType='dijit.form.CheckBox' type='checkbox' id='resolve_redirects_enabled'
			name='resolve_redirects_enabled' $checked>&nbsp;" . __('Resolve HTTP Redirects') . "</label>";

        print "</fieldset>";
    }

    /**
     * @param $feed_id
     */
    private function render_regexp_tags($feed_id)
    {
        $regexp_tags = $this->read_setting(self::SETTING_NAME_REGEXP_TAGS);
        $reg_exp = isset($regexp_tags[$feed_id]) ? $regexp_tags[$feed_id] : '';

        print "<fieldset>";

        print "<label style=\"text-align: left;\">RegExp for extracting tags:</label>";

        print "<input dojoType=\"dijit.form.ValidationTextBox\"
			 required=\"false\" id=\"filterDlg_regExp\"
			 style=\"font-size : 16px; width : 15em;\"
			 name=\"reg_exp_tags\" value=\"$reg_exp\"/>";

        print "<div dojoType=\"dijit.Tooltip\" connectId=\"filterDlg_regExp\" position=\"below\">
			" . __("Regular expression, without outer delimiters (i.e. slashes)") . "
		</div>";

        print "</fieldset>";
    }

    /**
     * @param $feed_id
     */
    private function render_tag_prefix($feed_id)
    {
        $tag_prefixes = $this->read_setting(self::SETTING_NAME_TAG_PREFIX);
        $tag_prefix = isset($tag_prefixes[$feed_id]) ? $tag_prefixes[$feed_id] : '';

        print "<fieldset>";

        print "<label style=\"text-align: left;\">Tag prefix:</label>";

        print "<input dojoType=\"dijit.form.ValidationTextBox\"
			 required=\"false\" id=\"filterDlg_tagPrefix\"
			 style=\"font-size : 16px; width : 15em;\"
			 name=\"tag_prefix\" value=\"$tag_prefix\"/>";

        print "<div dojoType=\"dijit.Tooltip\" connectId=\"filterDlg_tagPrefix\" position=\"below\">
			" . __("Prefix for extracted article tags, will be prepended to each tag") . "
		</div>";

        print "</fieldset>";
    }

    /**
     * @param $feed_id
     */
    private function render_replace_existing($feed_id)
    {
        $replace_existing = $this->read_setting(self::SETTING_NAME_REPLACE_EXISTING);

        $key = array_search($feed_id, $replace_existing);
        $checked = $key !== FALSE ? "checked" : "";

        print "<fieldset>";

        print "<label class='checkbox'><input dojoType='dijit.form.CheckBox' type='checkbox' id='replace_existing_enabled'
			name='replace_existing_enabled' $checked>&nbsp;" . __('Replace existing tags') . "</label>";

        print "</fieldset>";
    }

    /**
     * @param $feed_id
     */
    private function save_resolve_redirects($feed_id)
    {
        $resolve_redirects = $this->host->get($this, self::SETTING_NAME_RESOLVE_REDIRECTS);
        if (!is_array($resolve_redirects)) $resolve_redirects = array();

        $enable = checkbox_to_sql_bool($_POST["resolve_redirects_enabled"]??'');
        $key = array_search($feed_id, $resolve_redirects);

        if ($enable) {
            if ($key === FALSE) {
                array_push($resolve_redirects, $feed_id);
            }
        } else {
            if ($key !== FALSE) {
                unset($resolve_redirects[$key]);
            }
        }

        $this->host->set($this, self::SETTING_NAME_RESOLVE_REDIRECTS, $resolve_redirects);
    }

    /**
     * @param $feed_id
     */
    private function save_regexp_tags($feed_id)
    {
        $regexp = $_POST["reg_exp_tags"];
        if(!$this->is_valid_regex($regexp)) {
            return;
        }

        $regexp_tags = $this->read_setting(self::SETTING_NAME_REGEXP_TAGS);

        if (!empty($regexp)) {
            $regexp_tags[$feed_id] = $regexp;
        } else {
            unset($regexp_tags[$feed_id]);
        }

        $this->host->set($this, self::SETTING_NAME_REGEXP_TAGS, $regexp_tags);
    }

    /**
     * @param $feed_id
     */
    private function save_tag_prefix($feed_id)
    {
        $tag_prefixes = $this->read_setting(self::SETTING_NAME_TAG_PREFIX);

        $tag_prefix = clean($_POST["tag_prefix"]);

        if (!empty($tag_prefix)) {
            $tag_prefixes[$feed_id] = $tag_prefix;
        } else {
            unset($tag_prefixes[$feed_id]);
        }

        $this->host->set($this, self::SETTING_NAME_TAG_PREFIX, $tag_prefixes);
    }

    /**
     * @param $feed_id
     */
    private function save_replace_existing($feed_id)
    {
        $replace_existing = $this->host->get($this, self::SETTING_NAME_REPLACE_EXISTING);
        if (!is_array($replace_existing)) $replace_existing = array();

        $enable = checkbox_to_sql_bool($_POST["replace_existing_enabled"]);
        $key = array_search($feed_id, $replace_existing);

        if ($enable) {
            if ($key === FALSE) {
                array_push($replace_existing, $feed_id);
            }
        } else {
            if ($key !== FALSE) {
                unset($replace_existing[$key]);
            }
        }

        $this->host->set($this, self::SETTING_NAME_REPLACE_EXISTING, $replace_existing);
    }

    /**
     * @return array|bool
     */
    private function read_setting($name)
    {
        $value = $this->host->get($this, $name);
        if (!is_array($value)) $value = array();
        return $value;
    }

    private function is_valid_regex($pattern)
    {
        return is_int(@preg_match('~'.$pattern.'~', ''));
    }

}
?>
