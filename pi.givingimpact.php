<?php

require_once dirname(__FILE__)."/givingimpact-php/MODL/GivingImpact.php";

class Plugin_givingimpact extends Plugin {

    protected $api_handle   = null;

    private $user_agent     = 'Statamic_AddOn';
    private $private_key    = false;
    private $public_key     = false;

    private function gi() {
        if( $this->api_handle ) {
            return $this->api_handle;
        }

        $this->private_key =  $this->fetchConfig('_private_key');
        $this->public_key = $this->fetchConfig('_public_key');

        $this->api_handle = new \MODL\GivingImpact($this->user_agent, $this->private_key);

        return $this->api_handle;
    }

    public function campaigns() {

        $campaigns = $this->gi()->campaign
            ->limit(5)
            ->fetch();

        $content = $this->content;
        $out = array();

        $campaigns = $this->prefix_tags('campaign', json_decode(json_encode($campaigns), true));

        foreach( $campaigns as $campaign ) {
            $out[] = Parse::template($content, $campaign);
        }

        return implode('', $out);
    }

    /**
     * Prefixes tags returned to template
     *
     * @param string $pfx
     * @param array  $data data returned from API
     *
     * @return array
     *
     * @access protected
     * @final
     */
    protected function prefix_tags($pfx, $data, $recurse = false) {
        $out = array();

        foreach( $data as $item ) {
            $row = array();

            foreach( $item as $k => $v ) {

                if( is_array($v) ) {
                    reset($v);
                    if( is_int(key($v)) ) {
                        $row[$pfx.'_'.$k] = $this->prefix_indexed($k, $v);
                    } else {
                        $row[$pfx.'_'.$k] = $this->prefix_assoc($k, $v);
                    }
                } else {
                    $row[$pfx.'_'.$k] = $v;
                }
            }

            $out[] = $row;
        }
        return $out;
    }

    /**
     * prefixes indexed arrays
     * @param  string $pfx
     * @param  array $data
     * @return array
     */
    protected function prefix_indexed($pfx, $data) {
        $out = array();
        if( is_array(reset($data)) ) {
            foreach( $data as $i => $item ) {
                $row = array();
                foreach( $item as $k => $v ) {
                    if( is_array($v) ) {
                        reset($v);
                        if( is_int(key($v)) ) {
                            $row[$pfx.'_'.$k] = $this->prefix_indexed($k, $v);
                        } else {
                            $row[$pfx.'_'.$k] = $this->prefix_assoc($k, $v);
                        }
                    } else {
                        $row[$pfx.'_'.$k] = $v;
                    }
                }
                $out[] = $row;
            }

            return $out;
        } else {
            // return $data;
            $out = array();
            foreach( $data as $i ) {
                $out[] = array('value' => $i);
            }

            return $out;
        }
    }

    /**
     * prefixes associative arrays
     * @param  string $pfx
     * @param  array $data
     * @return array
     */
    protected function prefix_assoc($pfx, $data) {
        $out = array();
        foreach( $data as $k => $v ) {
            if( is_array($v) ) {
                reset($v);
                if( is_int(key($v)) ) {
                    $out[$pfx.'_'.$k] = $this->prefix_indexed($k, $v);
                } else {
                    $out[$pfx.'_'.$k] = $this->prefix_assoc($k, $v);
                }
            } else {
                $out[$pfx.'_'.$k] = $v;
            }
        }

        return array($out);
    }

}