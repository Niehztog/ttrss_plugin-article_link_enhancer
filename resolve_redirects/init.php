<?php
class resolve_redirects extends Plugin {

    private $host;

    function about() {
        return array(
            1.0,
            "Resolve HTTP redirects in article links",
            "Niehztog",
            true
        );
    }

    function init($host) {
        $this->host = $host;

        $host->add_hook($host::HOOK_ARTICLE_FILTER, $this);
        $host->add_hook($host::HOOK_PREFS_EDIT_FEED, $this);
        $host->add_hook($host::HOOK_PREFS_SAVE_FEED, $this);
    }

    function hook_prefs_edit_feed($feed_id) {
        print "<header>".__("Resolve HTTP Redirects")."</header>";
        print "<section>";

        $enabled_feeds = $this->host->get($this, "enabled_feeds");
        if (!is_array($enabled_feeds)) $enabled_feeds = array();

        $key = array_search($feed_id, $enabled_feeds);
        $checked = $key !== FALSE ? "checked" : "";

        print "<fieldset>";

        print "<label class='checkbox'><input dojoType='dijit.form.CheckBox' type='checkbox' id='resolve_redirects_enabled'
			name='resolve_redirects_enabled' $checked>&nbsp;".__('Resolve HTTP Redirects')."</label>";

        print "</fieldset>";

        print "</section>";
    }

    function hook_prefs_save_feed($feed_id) {
        $enabled_feeds = $this->host->get($this, "enabled_feeds");
        if (!is_array($enabled_feeds)) $enabled_feeds = array();

        $enable = checkbox_to_sql_bool($_POST["resolve_redirects_enabled"]);
        $key = array_search($feed_id, $enabled_feeds);

        if ($enable) {
            if ($key === FALSE) {
                array_push($enabled_feeds, $feed_id);
            }
        } else {
            if ($key !== FALSE) {
                unset($enabled_feeds[$key]);
            }
        }

        $this->host->set($this, "enabled_feeds", $enabled_feeds);
    }

    function hook_article_filter($article) {

        $enabled_feeds = $this->host->get($this, "enabled_feeds");
        if (!is_array($enabled_feeds)) return $article;

        $key = array_search($article["feed"]["id"], $enabled_feeds);
        if ($key === FALSE) return $article;

        return $this->process_article($article);


    }

    function process_article($article) {

        $cleanUrl = $this->getRedirectUrl($article['link']);
        if($cleanUrl === false ) {
            return $article;
        }

        $urlParsed = parse_url($article['link']);
        $article['link'] = $urlParsed['scheme'] . '://' . $urlParsed['host'] . $cleanUrl;

        return $article;
    }

    function api_version() {
        return 2;
    }

    private function getRedirectUrl ($url) {
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

}
?>
