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
        echo 'hi';
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

        $toCheck = array(
            'first_name',
            'last_name',
            'email',
            'street',
            'city',
            'state',
            'zip'
        );

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

        Session::setFlash('donation_token', $new_token);

        return URL::redirect($next.'?donation='.$new_token);

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