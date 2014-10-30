<?php
/**
 * Giving Impact Statamic Plugin
 *
 * @author      Mike Joseph <mikej@mod-lab.com>
 * @copyright   Minds On Design Lab
 * @link        http://mod-lab.com
 */

require_once dirname(__FILE__)."/givingimpact-php/MODL/GivingImpact.php";

class Hooks_givingimpact extends Hooks {

    protected $api_handle   = null;

    private $user_agent     = 'Statamic_AddOn';
    private $private_key    = false;
    private $public_key     = false;

    public function givingimpact__post_opportunity() {
        $token          = Request::post('c');
        $title          = Request::post('title');
        $description    = Request::post('description');
        $youtube        = Request::post('youtube');
        $target         = Request::post('target');
        $captcha        = Request::post('captcha');

        $return_path    = base64_decode(Request::post('rtp'));

        // $related = $this->EE->input->post('related', false);
        $related = true;

        $next   = Request::post('NXT');
        $notify = Request::post('NTF');

        if( strpos($next, '/') !== 0 && strpos($next, 'http') !== 0 ) {
            $next = Path::clean(Path::resolve($next));
        }

        // if( $notify && !valid_email($notify) ) {
        //     $notify = false;
        // }
        //

        $this->runHook('before_opportunity', 'call', null, array(
            'title'             => $title,
            'description'       => $description,
            'youtube'           => $youtube,
            'target'            => $target
        ));

        if( !$token || !$title || !$description ) {
            $errors = array();
            if( !$token ) {
                $errors[] = 'Campaign token is required';
            }
            if( !$title ) {
                $errors[] = 'Title is required';
            }
            if( !$description ) {
                $errors[] = 'Description is required';
            }

            Session::setFlash('formvals', serialize(array(
                'title'             => $title,
                'description'       => $description,
                'youtube'           => $youtube,
                'target'            => $target,
                'errors'            => $this->prep_errors($errors)
            )));

            return URL::redirect($return_path, 301);
        }


        $obj = $this->gi()->campaign
            ->fetch($token);

        $campaign_responses = array();

        if( array_key_exists('campaign_fields', $obj) && is_array(Request::post('fields')) ) {

            $responses = Request::post('fields');

            $errors = array();

            foreach( $obj->campaign_fields as $f ) {
                if( $f->required && $f->status && !$responses[$f->field_id] ) {
                    $errors[] = $f->field_label.' is required';
                    break;
                }

                if( !array_key_exists($f->field_id, $responses) ) {
                    continue;
                }
                $item = new stdClass;
                $item->response             = $responses[$f->field_id];
                $item->campaign_field_id    = $f->field_id;

                $campaign_responses[] = $item;
            }

            if( count($errors) ) {
                Session::setFlash('formvals', serialize(array(
                    'title'             => $title,
                    'description'       => $description,
                    'status'            => $status,
                    'youtube'           => $youtube,
                    'target'            => $target,
                    'errors'            => $this->prep_errors($errors)
                )));

                return URL::redirect($return_path, 301);
            }
        }

        // pack it

        $opp = $this->gi()->opportunity;

        $opp->campaign_token    = $token;
        $opp->title             = $title;
        $opp->description       = $description;
        $opp->status            = 1;
        $opp->campaign_responses= $campaign_responses;
        $opp->donation_target   = $target ? $target : 0;

        if( $youtube ) {
            $opp->youtube_id = $youtube;
        }

        if( $_FILES && array_key_exists('image', $_FILES) ) {
            $image = $_FILES['image'];

            if( !$image['error'] ) {
                $raw = base64_encode(file_get_contents($image['tmp_name']));
                $type = $image['type'];

                $opp->image_file = $raw;
                $opp->image_type = $type;
            }
        }

        $result = $opp->create();

        $new_token = $result->id_token;

        $this->runHook('after_opportunity', 'call', null, array(
            'opportunity_token' => $new_token,
            'opportunity'       => $result
        ));

        Session::setFlash('opportunity_token', $new_token);

        return URL::redirect($next.'?opportunity='.$new_token);
    }

    public function givingimpact__post_donation() {

        $token              = Request::post('t');
        $opportunity_token  = Request::post('ot', false);

        $return_path        = base64_decode(Request::post('rtp'));

        $first_name         = Request::post('first_name');
        $last_name          = Request::post('last_name');
        $email              = Request::post('email');
        $contact            = Request::post('contact', false);
        $street             = Request::post('street');
        $city               = Request::post('city');
        $state              = Request::post('state');
        $zip                = Request::post('zip');
        $donation_level     = Request::post('donation_level');
        $donation_level_id  = Request::post('donation_level_id');
        $donation_amount    = Request::post('donation_amount');

        $captcha            = Request::post('captcha');

        $card               = Request::post('token');

        $next = Request::post('NXT');
        $notify = Request::post('NTF');

        if( strpos($next, '/') !== 0 && strpos($next, 'http') !== 0 ) {
            $next = Path::clean(Path::resolve($next));
        }

        $toCheck = array(
            'first_name',
            'last_name',
            'email',
            'street',
            'city',
            'state',
            'zip'
        );

        $this->runHook('before_donation', 'call', null, array(
            'first_name'        => $first_name,
            'last_name'         => $last_name,
            'email'             => $email,
            'street'            => $street,
            'city'              => $city,
            'state'             => $state,
            'zip'               => $zip,
            'donation_level'    => $donation_level,
            'donation_level_id' => $donation_level_id,
            'donation_amount'   => $donation_amount,
            'contact'           => $contact
        ));


        // if( $notify && !valid_email($notify) ) {
        //     $notify = false;
        // }

        $errors = array();
        foreach( $toCheck as $v ) {
            if( !Request::post($v) ) {
                $error[] = str_replace('_', ' ', $v).' is required';
            }
        }
        if( !$token && !$opportunity_token ) {
            $errors[] = 'Campaign or Opportunity token is required';
        }
        if( !$donation_level_id && !$donation_amount ) {
            $errors[] = 'You did not specify a donation amount';
        }
        if( !$card ) {
            $errors[] = 'Could not process credit card';
        }

        if( !filter_var($email, FILTER_VALIDATE_EMAIL) ) {
            $errors[] = 'Please enter a valid email address';
        }
        if( $donation_amount && $donation_amount != floor($donation_amount) ) {
            $errors[] = 'Please enter a whole dollar amount';
        }

        if( count($errors) ) {

            Session::setFlash('formvals', serialize(array(
                'first_name'        => $first_name,
                'last_name'         => $last_name,
                'email'             => $email,
                'street'            => $street,
                'city'              => $city,
                'state'             => $state,
                'zip'               => $zip,
                'donation_level'    => $donation_level,
                'donation_level_id' => $donation_level_id,
                'donation_amount'   => $donation_amount,
                'contact'           => $contact,
                'errors'            => $this->prep_errors($errors)
            )));

            return URL::redirect($return_path, 301);
        }

        if( $token && strlen($token) ) {
            $obj = $this->gi()->campaign
                ->fetch($token);
        } else {
            $obj = $this->gi()->opportunity
                ->related(1)
                ->fetch($opportunity_token);

            $obj = $obj->campaign;
        }

        $custom_responses = array();
        if( array_key_exists('custom_fields', $obj) && is_array(Request::post('fields')) ) {

            $responses = Request::post('fields');

            $errors = array();

            foreach( $obj['custom_fields'] as $f ) {
                if( $f['required'] && $f['status'] && !$responses[$f['field_id']] ) {
                    $errors['fields['.$f['field_id'].']'] = $f['field_label'].' is required';
                    break;
                }

                if( !array_key_exists($f['field_id'], $responses) ) {
                    continue;
                }

                $custom_responses[$f['field_id']] = $responses[$f['field_id']];
            }

            if( count($errors) ) {
                Session::setFlash('formvals', serialize(array(
                    'first_name'        => $first_name,
                    'last_name'         => $last_name,
                    'email'             => $email,
                    'street'            => $street,
                    'city'              => $city,
                    'state'             => $state,
                    'zip'               => $zip,
                    'donation_level'    => $donation_level,
                    'donation_level_id' => $donation_level_id,
                    'donation_amount'   => $donation_amount,
                    'contact'           => $contact,
                    'errors'            => $this->prep_errors($errors)
                )));

                return URL::redirect($return_path, 301);
            }
        }

        // pack it
        $donation = $obj->donation;

        $donation->first_name        = $first_name;
        $donation->last_name         = $last_name;
        $donation->contact           = false;
        $donation->email_address     = $email;
        $donation->billing_address1  = $street;
        $donation->billing_city      = $city;
        $donation->billing_state     = $state;
        $donation->billing_postal_code = $zip;
        $donation->billing_country   = 'US';
        $donation->donation_total    = $donation_amount;
        $donation->donation_level_id = $donation_level_id;
        $donation->custom_responses  = $custom_responses;
        $donation->card              = $card;
        $donation->contact           = $contact ? $contact : 0;

        $result = $donation->create();

        $new_token = $result->id_token;

        $this->runHook('after_donation', 'call', null, array(
            'donation_token' => $new_token,
            'donation'       => $result
        ));

        Session::setFlash('donation_token', $new_token);

        return URL::redirect($next.'?donation='.$new_token);

    }

    private function gi() {
        if( $this->api_handle ) {
            return $this->api_handle;
        }

        $this->private_key =  $this->fetchConfig('_private_key');
        $this->public_key = $this->fetchConfig('_public_key');
        $this->end_point = $this->fetchConfig('_end_point', false);

        $this->api_handle = new \MODL\GivingImpact($this->user_agent, $this->private_key, $this->end_point);

        return $this->api_handle;
    }

    /**
     * Creates a multidimensional array with 'error': 'error message' so
     * it can be properly parsed as template varluables
     * @param  array $errors
     * @return array
     */
    private function prep_errors($errors) {
        $out = array();
        foreach( $errors as $error ) {
            $out[] = array('error' => $error);
        }

        return $out;
    }
}
