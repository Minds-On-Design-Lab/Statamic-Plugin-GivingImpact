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

    public function donate_js() {
        $apiUrl = $this->gi()->endpoint();
        $publicKey =  $this->fetchConfig('_public_key');

        $formId = $this->fetchParam('id') ? $this->fetchParam('id') : 'donate-form';

$out = <<<END
<script type="text/javascript" src="{$apiUrl}/v2/checkout?key={$publicKey}"></script>
<script>
    (function(\$) {
        \$(function() {

            $('#{$formId}').submit(function(e) {
                if( $(this).find('input[name="token"]').length >= 1 ) {
                    return;
                }

                e.preventDefault();
                var \$this = \$(this).find('input[type="submit"]');

                \$this.val('Processing...');
                \$this.attr('disabled', true);

                GIAPI.checkout({
                    'card':     \$('[name="cc_number"]').val(),
                    'cvc':      \$('[name="cc_cvc"]').val(),
                    'month':    \$('[name="cc_exp"]').val().substr(0,2),
                    'year':     \$('[name="cc_exp"]').val().substr(5,4),
                }, function(token) {
                    if( \$('#_carderr').length >= 1 ) {
                        \$('#_carderr').remove();
                    }

                    if( !token ) {
                        \$('[name="cc_number"]').addClass('error');
                        \$('<span class="radius alert label" id="_carderr">Your card was not accepted</span>').insertAfter(\$('[name="cc_number"]'));
                        \$this.val('Donate');
                        \$this.attr('disabled', false);
                        return;
                    }
                    // the card token is returned, append to form and submit
                    \$('#donate-form').append($('<input type="hidden" value="'+token+'" name="token" />'));
                    \$('#donate-form').submit();
                });
            })
        });
    })(jQuery);
</script>
END;

        return $out;

    }

    public function donate_form() {
        $tagdata = $this->content;

        // if( $this->fetchParam('opportunity') ) {
        //     $opportunities = $this->gi()->opportunity
        //         ->related(1)
        //         ->fetch($this->fetchParam('opportunity'));
        // } else {
        //     $campaigns = $this->gi()->campaign
        //         ->related(1)
        //         ->fetch($this->fetchParam('campaign'));
        // }

        $vars = array(
            'value_first_name'  => false,
            'value_last_name'   => false,
            'value_email'       => false,
            'value_street'      => false,
            'value_city'        => false,
            'value_state'       => false,
            'value_zip'         => false,
            'value_donation_amount'     => false,
            'value_donation_level'      => false,
            'valud_donation_level_id'   => false
        );

        if( Session::getFlash('formvals') ) {
            $vals = unserialize(Session::getFlash('formvals'));
            $errors = $vals['errors'];
            unset($vals['errors']);
            if( $vals && count($vals) ) {
                foreach( $vals as $k => $v ) {
                    $vars['value_'.$k] = $v;
                }
            }
            $vars['form_errors'] = $errors;
        }
        if( Session::getFlash('donation_token') ) {
            $donation = $this->gi()->donation
                ->fetch(Session::getFlash('donation_token'));

            $vars['donation'] = $this->prefix_tags('donation', json_decode(json_encode(array($donation)), true));
        }

        $tagdata = Parse::template($tagdata, $vars);

        $tag_start = sprintf(
            '<form method="POST" action="%s" id="%s" class="%s" enctype="multi">',
            URL::format(Config::getSiteRoot().'TRIGGER/givingimpact/post_donation'),
            'donate-form',
            $this->fetchParam('class')
        );

        $h = '<input type="hidden" name="%s" value="%s" />';
        $tag_start .= sprintf($h, 't', $this->fetchParam('campaign'));
        $tag_start .= sprintf($h, 'ot', $this->fetchParam('opportunity'));
        $tag_start .= sprintf($h, 'rtp', base64_encode(URL::getCurrent(true)));

        // If return parameter is used, add to hidden_fields

        if($this->fetchParam('return', false) ) {
            $tag_start .= sprintf($h, 'NXT', $this->fetchParam('return'));
        } else {
            $tag_start .= sprintf($h, 'NXT', URL::getCurrent(true));
        }

        // If notify parameter is user, add to hidden_fields

        if ($this->fetchParam('notify', false)) {
            $tag_start .= sprintf($h, 'NTF', $this->fetchParam('notify'));
        }

        // Create form wrapper

        $content = $tag_start . $tagdata . '</form>';

        return $content;
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