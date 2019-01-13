<?php
/**
 * Multilingual Plugin: Simple multilanguage plugin
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Daniel Stonier <d.stonier@gmail.com>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'action.php');

class action_plugin_multilingual extends DokuWiki_Action_Plugin {

    /**
     * for th helper plugin
     */
    var $hlp = null;

    /**
     * Constructor. Load helper plugin
     */
    function action_plugin_multilingual(){
        $this->hlp =& plugin_load('helper', 'multilingual');
    }

    /**
     * return some info
     */
    function getInfo(){
        return confToHash(dirname(__FILE__).'/info.txt');
    }

    /**
     * Register the events
     */
    function register(&$controller) {
        if($this->getConf('start_redirect')) {
            $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'multilingual_start');
        }
        $controller->register_hook('DOKUWIKI_STARTED', 'BEFORE', $this, 'multilingual_ui');
    }

    function multilingual_start($event, $args) {
        global $conf;
        global $ACT;

        if ( $ACT == 'login' ) { 
            if (($_SERVER['REMOTE_USER']!=null)&&($_REQUEST['do']=='login')) {
                header('Location: doku.php?id='.$conf['lang'].':'.$conf['start']);
	        die();
	    }
        }
    }
    /**
     * Change the UI language depending on your browser's current selection.
     */
    function multilingual_ui(&$event, $args) {
        global $ID;
        global $lang;
        global $conf;
        global $INFO;

        $old_language = $conf['lang'];

        $user_settings = $this->getConf('user_settings');
        $pattern = '/'.$INFO['client'].'="(.*?)";/';
        $rebuild = false;
        if (preg_match($pattern, $user_settings, $matches) == 1) {
            $options = explode(';', $matches[1]);
            if (!empty($options[0])) {
                dbglog('Plugin multilangual: changing language for user "'.$INFO['client'].'" to "'.$options[0].'"');
                $conf['lang'] = $options[0];
                $rebuild = true;
            }
        }
        if ($rebuild == false && $this->getConf('use_browser_lang')) {
            $enabled_languages = preg_split("/ /", $this->getConf('enabled_langs') );
            $languages = preg_split("/,/", preg_replace('/\(;q=\d+\.\d+\)/i', '', getenv('HTTP_ACCEPT_LANGUAGE')));
            // Could use a check here against the dokuwiki's supported languages
            foreach ($languages as $language) {
                if (in_array($language, $enabled_languages)) {
                    $conf['lang'] = $language;
                    break;
                }
            }
        }
        if ($rebuild == true) {
            // Rebuild language array if necessary
            if ( $old_language != $conf['lang'] ) {
                init_lang($conf['lang']);
            }
        }
        return true;
    }
}

//Setup VIM: ex: et ts=4 enc=utf-8 :
