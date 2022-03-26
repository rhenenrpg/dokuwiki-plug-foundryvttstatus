<?php
/**
 * FoundryVTT Status Plugin: shows the status of a FoundryVTT instance
 * that is behind the same reverse proxy as this wiki
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Martijn Sanders <m.e.sanders@alumnus.utwente.nl>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

use dokuwiki\Utf8\PhpString;

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_foundryvttstatus extends DokuWiki_Syntax_Plugin {

    function getType(){ return 'substition'; }
    function getSort() {
        //Execute before html mode
        return 189;
    }

    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('<foundryvttstatus[^>]*>', $mode, 'plugin_foundryvttstatus');
    }

    function handle($match, $state, $pos, Doku_Handler $handler) {
        $return = $this->_getDefaultOptions();
        $return['pos'] = $pos;
        $return['mes'] = $pos;
        $return['match'] = $match;

        dbglog('handle '.print_r($return,true));
        dbglog('match >'.$match.'<');
        $match = PhpString::substr($match, strlen("<foundryvttstatus"), -1);
        $match .= ' ';
        dbglog('match >'.$match.'<');
        $this->checkSimpleStringArgument($match, $return['route'], $this, 'route');
        $this->checkSimpleStringArgument($match, $return['port'], $this, 'port');
        dbglog('return '.print_r($return,true));
        return $return;
    }

    private function _getDefaultOptions() {
        return array(
            'route' => 'vtttest',
            'port' => '30001'
        );
    }

    function render($mode, Doku_Renderer $renderer, $data) {
        $renderer->nocache(); //disable cache
        dbglog('syntax_plugin_foundryvttstatus data '.print_r($data,true));
        // $renderer->doc .= '<div>'.print_r($data,true).'</div>';
        global $foundrypage;
        global $USERINFO;
        if(!isset($USERINFO)) {
            dbglog("syntax_plugin_foundryvttstatus case unauthicated user - do nothing");
            return TRUE;
        }
        if(!in_array('x'.$data['route'], $USERINFO['grps'])) {
            dbglog("syntax_plugin_foundryvttstatus case user not in ACL - do nothing");
            return TRUE;
        }
        dbglog('syntax_plugin_foundryvttstatus $USERINFO '.print_r($USERINFO,true));
        $context = stream_context_create(["http"=>["timeout"=>5]]);
        $posturl = 'http://foundryvtt.lan:'.$data['port'].'/'.$data['route'].'/join';
        dbglog('calling >'.$posturl.'<');
        $payload = json_encode(['action' => 'announce-discord-user', 'username' => $_SERVER['REMOTE_USER']]);
        dbglog('payload '.$payload);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $posturl);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, 
		           [ 'Content-Type: application/json', 
                 'mes-api-key: VERYSECRETKEY',
                 'Content-Length: '.strlen($payload)]);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1); /* expected responstime < 1 msec */
        curl_setopt($ch, CURLOPT_HEADER, FALSE); 
        $foundrypage = curl_exec($ch);
        if(curl_errno($ch))
        {
            dbglog("syntax_plugin_foundryvttstatus ERROR ".curl_error($ch));
            $info = curl_getinfo($ch);
            dbglog("syntax_plugin_foundryvttstatus curl_getinfo ".print_r($info,true));
        }
        curl_close($ch);
        dbglog('syntax_plugin_foundryvttstatus fetched foundrypage '.$foundrypage);
        // dbglog('syntax_plugin_foundryvttstatus $_SERVER '.print_r($_SERVER,true));
        $retjson = json_decode($foundrypage, true);
        if(is_array($retjson) && (json_last_error() == JSON_ERROR_NONE)) {
            dbglog('syntax_plugin_foundryvttstatus case valid jason');
            $renderer->doc .= '<div>Active Game: ';
            $renderer->doc .= '<a target="_blank" href="/'.$data['route'].'/join"><b>'.$retjson['worldtitle'].'</b></a>';
            $renderer->doc .= '<div style="display: none;">if login does not happen automatically, ';
            $renderer->doc .= 'select user <b><div id="'.$data['route'].'.fvttusername">'.$_SERVER['REMOTE_USER'].'</div></b>';
            $renderer->doc .= 'and use password <b><div id="'.$data['route'].'.fvttpassword">'.$retjson['password'].'</div></b>';
            $renderer->doc .= '</div>';
            return TRUE;
        }
        if(!$foundrypage or strstr($foundrypage, '<h1>No Active Game</h1>')) {
            dbglog('syntax_plugin_foundryvttstatus case No Active Game');
            $renderer->doc .= '<div><b>There is no active game on '.$data['route'].'.</b></div>';
            return TRUE;
        }
        $renderer->doc .= '<div><b>FoundryVTT is shut down on '.$data['route'].' or an error occurred.</b></div>';
        return TRUE;
    }

    function checkSimpleStringArgument(&$match, &$varAffected, $plugin, $argumentName) {
        $pattern = '/\s'.$argumentName.' *= *\"([^\"]*)\"'.'/i';
        dbglog('pattern '.$pattern);
        dbglog(preg_match($pattern, $match, $found));
        if(preg_match($pattern, $match, $found)) {
            $varAffected = $found[1];
            $match       = str_replace($found[0], ' ', $match);
        }
    }

}
// vim:ts=4:sw=4:et:enc=utf-8:
