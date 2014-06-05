<?php
/**
 * Giving Impact Statamic Plugin
 *
 * @author      Mike Joseph <mikej@mod-lab.com>
 * @copyright   Minds On Design Lab
 * @link        http://mod-lab.com
 */

require_once dirname(__FILE__)."/givingimpact-php/MODL/GivingImpact.php";

class Plugin_givingimpact extends Plugin {

    protected $api_handle   = null;

    private $limit = 10;
    private $offset = 0;
    private $sort = 'created_at';
    private $dir = 'asc';
    private $status = 'active';
    private $related = false;

    private $max_limit = 100;

    private $user_agent     = 'Statamic_AddOn';
    private $private_key    = false;
    private $public_key     = false;

    /**
     * Campaigns method
     *
     * @return string
     */
    public function campaigns() {
        $token = $this->fetchParam('campaign', false);

        $limit = $this->limit();
        $offset = $this->offset();
        $sort = $this->sort();

        if( $token ) {
            $campaigns = $this->gi()->campaign
                ->fetch($token);
            $campaigns = array($campaigns);
        } else {
            $campaigns = $this->gi()->campaign
                ->limit($limit)
                ->offset($offset)
                ->sort($sort)
                ->fetch();
        }

        $content = $this->content;
        $out = array();

        $campaigns = $this->prefix_tags('campaign', json_decode(json_encode($campaigns), true));

        foreach( $campaigns as $campaign ) {
            $out[] = Parse::template($content, $campaign);
        }

        return implode('', $out);
    }

    /**
     * Opportunities method
     *
     * @return string
     */
    public function opportunities() {
        $campaign_token = $this->fetchParam('campaign', false);

        $token = $this->fetchParam('opportunity', false);

        $limit = $this->limit();
        $offset = $this->offset();
        $sort = $this->sort();
        $related = $this->related();

        if( $token ) {
            $opportunities = $this->gi()->opportunity
                ->related($related)
                ->fetch($token);
            $opportunities = array($opportunities);
        } else {
            $opportunities = $this->gi()->campaign
                ->fetch($campaign_token)
                ->opportunities
                ->limit($limit)
                ->offset($offset)
                ->sort($sort)
                ->related($related)
                ->fetch();
        }

        $content = $this->content;
        $out = array();

        $opportunities = $this->prefix_tags('opportunity', json_decode(json_encode($opportunities), true));

        foreach( $opportunities as $opportunity ) {
            $out[] = Parse::template($content, $opportunity);
        }

        return implode('', $out);
    }

    /**
     * Donations method
     *
     * @return string
     */
    public function donations() {
        $campaign_token = $this->fetchParam('campaign', false);
        $opportunity_token = $this->fetchParam('opportunity', false);

        $token = $this->fetchParam('donation', false);

        $limit = $this->limit();
        $offset = $this->offset();
        $sort = $this->sort();
        $related = $this->related();

        if( $token ) {
            $donations = $this->gi()->donation
                ->fetch($token);
            $donations = array($donations);
        } else {

            if( $campaign_token ) {
                $donations = $this->gi()
                    ->campaign
                    ->related($related)
                    ->fetch($campaign_token)
                    ->donations;
            } else {
                $donations = $this->gi()
                    ->opportunity
                    ->fetch($opportunity_token)
                    ->donations;
            }

            $donations = $donations
                ->limit($limit)
                ->offset($offset)
                ->sort($sort)
                ->related($related)
                ->fetch();
        }

        $content = $this->content;
        $out = array();

        $donations = $this->prefix_tags('donation', json_decode(json_encode($donations), true));

        foreach( $donations as $donation ) {
            $out[] = Parse::template($content, $donation);
        }

        return implode('', $out);
    }

    public function supporters() {

        $token = $this->fetchParam('supporter', false);

        $limit = $this->limit();
        $offset = $this->offset();
        $sort = $this->sort();
        $related = $this->related();

        if( $token ) {
            $supporters = $this->gi()->supporter
                ->related($related)
                ->fetch($token);
            $supporters = array($supporters);
        } else {
            $supporters = $this->gi()
                ->supporter
                ->limit($limit)
                ->offset($offset)
                ->sort($sort)
                ->related($related)
                ->fetch();
        }

        $content = $this->content;
        $out = array();

        $supporters = $this->prefix_tags('supporter', json_decode(json_encode($supporters), true));

        foreach( $supporters as $supporter ) {
            $out[] = Parse::template($content, $supporter);
        }

        return implode('', $out);
    }

    private function gi() {
        if( $this->api_handle ) {
            return $this->api_handle;
        }

        $this->private_key =  $this->fetchConfig('_private_key');
        $this->public_key = $this->fetchConfig('_public_key');

        $this->api_handle = new \MODL\GivingImpact($this->user_agent, $this->private_key);

        return $this->api_handle;
    }

    private function related() {
        return $this->fetchParam('related', $this->related);
    }

    private function limit() {
        return $this->fetchParam('limit', $this->limit);
    }

    private function offset() {
        return $this->fetchParam('offset', $this->offset);
    }

    private function sort() {
        $sort = str_replace(
            'campaign_', '', $this->fetchParam('sort', $this->sort)
        );

        switch( $this->fetchParam('status', false) ) {
            case 'active':
            case 'inactive':
            case 'both':
                $status = $this->fetchParam('status', false);
                break;
            default:
                $status = $this->status;
        }

        $dir = $this->dir;
        if( strpos($sort, '|') !== false ) {
            $temp = explode('|', $sort);

            $sort = $temp[0];
            if( $temp[1] == 'desc' || $temp[1] == 'asc' ) {
                $dir = $temp[1];
            }
        }

        return $sort;
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