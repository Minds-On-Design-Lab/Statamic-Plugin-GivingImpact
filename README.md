Giving Impact for Statamic
==========================

## Overview

An Statamic plugin to interact with Giving Impact &trade;. Giving Impact is an online fundraising platform driven by a thoughtful API to allow designers and developers to deliver customized online donation experiences for Non-profits easily, affordable, and flexibly.

For more about Giving Impact and to view our full documentation and learning reasources please visit [givingimpact.com](http://givingimpact.com)

### Module Credits

**Developed By:** Minds On Design Lab - http://mod-lab.com<br />
**Version:** 1.0<br />
**Copyright:** Copyright &copy; 2010-2014 Minds On Design Lab<br />
**License:** Licensed under the MIT license - Please refer to LICENSE

## Requirements

* PHP 5
* cURL
* Statamic
* A [Giving Impact](http://givingimpact.com) account.
    * Supports v2 of API
* Tested in Statamic 1.7.8

### Menu

* [Install](#install)
* [Campaigns](#campaigns)
* [Opportunities](#opportunities)
* [Donations](#donations)
* [Donation Checkout](#donation-checkoupt)
* [Opportunity Form](#opportunity-form)
* [Hooks](#hooks)

### Install

1. Download the [archive](https://github.com/Minds-On-Design-Lab/Statamic-Plugin-GivingImpact/archive/master.zip)
2. Drop `_add_ons/givingimpact` into your Statmic install's `_add_ons` directory
3. Copy `_config/_add_ons/givingimpact/givingimpact.yaml` to your Statmic install's `_config` directory
4. Add your [GivingImpact](http://givingimpact.com) public and private API keys to `givingimpact.yaml`

### Campaigns

    {{givingimpact:campaigns}} Content {{/givingimpact:campaigns}}

#### Optional Parameters

| Parameter | Data Type | Description | Default |
| ------------ |:-------------|:-------------|:-------------|
| campaign | STRING | Unique campaign id_token. If provided will only return that campaign's data.  If not used, then will return multiple campaigns. | |
| limit | INT | Limits the number of results returned. | 10 |
| offset | INT | Number of results to skip, useful for pagination. | 0 |
| sort | STRING | Property to sort results by. Also accepts a direction preceded by a pipe, e.g. sort="created_at&#124;desc"| gi_created_at |
| status | STRING | Campaign status, "active", "inactive" or "both". | active |

#### Single Variables

| Variable        | Description|
| ------------- |:-------------|
| {{campaign_id_token}} | Unique API token and id for the campaign |
| {{campaign_status}} | Returns `true` or `false` depending on whether the campaign is active or not. |
| {{campaign_title}} | Title of the campaign |
| {{campaign_description}} | Brief campaign description |
| {{campaign_donation_url}} | URL to the hosted donation landing and processing pages. |
| {{campaign_donation_target}} | Target donation amount (signed float). |
| {{campaign_donation_minimum}} | Minimum donation value accepted. |
| {{campaign_donation_total}} | Current donation total (signed float). |
| {{campaign_enable_donation_levels}} | Returns `true` or `false` depending on whether the Campaign has Donation Levels enabled or not. |
| {{campaign_total_donations}} | Current total number of donations. |
| {{campaign_has_giving_opportunities}} | Returns `true` or `false` depending on whether the Campaign has Giving Opportunities or not. |
| {{campaign_total_opportunities}} | Current total number of Giving Opportunities. |
| {{campaign_share_url}} | URL to the hosted share page. Useful to offer social network sharing of the campaign using campaign data. Offers basic tracking of shares reported as part of campaign analytics within the Giving Impact dashboard as well as can be tracked in Google Analytics if a profile ID has been added to campaign. |
| {{campaign_shares_fb}} | Total number of Facebook likes for this campaign made through the Giving Impact share feature. |
| {{campaign_shares_twitter}} | Total number of Tweets made for this campaign made through the Giving Impact share feature. |
| {{campaign_image_url}} | URL to campaign image. Image is hosted with Giving Impact. Image is served via HTTPS.|
| {{campaign_thumb_url}} | URL to campaign thumbnail image. Image is hosted with Giving Impact. Image is served via HTTPS.|
| {{campaign_youtube_id}} | YouTube ID for campaign video. |
| {{campaign_hash_tag}} | Twitter hashtag for the campaign. |
| {{campaign_analytics_id}} | Google Analytics Profile ID for the Campaign. |
| {{campaign_campaign_color}} | Campaign accent color.  |
| {{campaign_header_font_color}} | Campaign accent color.  |
| {{campaign_display_donation_target}} | Returns `true` or `false` for the campaign preference to show or hide the target donation amount. Useful to use as a conditional around the `{{campaign_donation_target}}` variable to respect this preference. |
| {{campaign_display_donation_total}} | Returns `true` or `false` for the campaign preference to show or hide the current donation total. Useful to use as a conditional around the `{{campaign_donation_total}}` variable to respect this preference. |

#### Variable Pairs

##### Campaign Fields

This is a collection of custom fields that are entered when creating and updating a child opportunity

    {{campaign_fields}}
        ...
    {{/campaign_fields}}

The following are available in this tag pair:

| Variable        | Description|
| ------------- |:-------------|
| {{campaign_fields_field_id}} | Returns a unique identifier for the custom field |
| {{campaign_fields_field_type}} | Returns the type of field (dropdown, text, ...) |
| {{campaign_fields_field_label}} | Returns the label of the field |
| {{campaign_fields_response}} | Returns the donor's response if entered |
| {{campaign_fields_status}} | Returns `true` or `false` depending on whether the field is currently set to active or not |
| {{campaign_fields_required}} | Returns `true` or `false` depending on whether the field is currently required |


#### Donation Levels

If Campaign is utilizing donation levels the following provide data about them.

    {{campaign_donation_levels}}
        ...
    {{/campaign_donation_levels}}

The following are available in this tag pair:

| Variable        | Description|
| ------------- |:-------------|
| {{donation_levels_level_id}} | Returns a unique identifier for the level |
| {{donation_levels_amount}} | Returns decimal for the monetary value for the level |
| {{donation_levels_label}} | Returns the string label/description for the level |
| {{donation_levels_position}} | Ordering value of level |


### Opportunities

    {{givingimpact:opportunities opportunity="{{id_token}}"}} Content {{/givingimpact:opportunities}}

#### Required Parameters

You need to provide a campaign id_token **or** opportunity id_token **or** a supporter.

* A campaign token will generate a list of children opportunities.
* An opportunity token will return the single opportunity.

| Parameter | Data Type | Description |
| ------------ |:-------------|:-------------|
| campaign | STRING | Parent campaign token. This is used to display the list of Giving Opportunities associated with a specific campaign. |
| opportunity | STRING | Unique giving opportunity token. This is used to display a single specific Giving Opportunity. |
| supporter | STRING | Can be unique supporter token OR supporter email address. This is used to display a list of Giving Opportunities for a supporter. |

##### Optional Parameters

The following are used to modify the returned list of giving opportunities when a parent campaign is specified.

| Parameter | Data Type | Description | Default |
| ------------ |:-------------|:-------------|:-------------|
| limit | INT | Limits the number of results returned. | 10 |
| offset | INT | Number of results to skip, useful for pagination. | 0 |
| sort | STRING | Property to sort results by. Also accepts a direction preceded by a pipe, e.g. sort="created_at&#124;desc"| gi_created_at |
| status | STRING | Campaign status, "active", "inactive" or "both". | active |
| related | BOOLEAN | Entering "true" will make available the `{gi_campaign}{/gi_campaign}` tag pair with a full set of variables related to the opportunity's parent campaign.  | false |

#### Single Variables

| Variable        | Description|
| ------------- |:-------------|
| {{opportunity_id_token}} | Unique API token and id for the Giving Opportunity. |
| {{opportunity_status}} | Returns `true` or `false` depending on whether the Giving Opportunity is active or not. |
| {{opportunity_title}} | Title of the Giving Opportunity |
| {{opportunity_description}} | Brief Giving Opportunity description |
| {{opportunity_donation_url}} | URL to the hosted donation landing and processing pages. |
| {{opportunity_donation_target}} | Target donation amount (signed float). |
| {{opportunity_donation_total}} | Current donation total (signed float). |
| {{opportunity_total_donations}} | Current total number of donations. |
| {{opportunity_share_url}} | URL to the hosted share page. Useful to offer social network sharing of the Giving Opportunity using Giving Opportunity data. Offers basic tracking of shares reported as part of campaign analytics within the Giving Impact dashboard. |
| {{opportunity_shares_fb}} | Total number of Facebook likes for this Giving Opportunity made through the Giving Impact share feature. |
| {{opportunity_shares_twitter}} | Total number of Tweets made for this Giving Opportunity made through the Giving Impact share feature. |
| {{opportunity_image_url}} |  URL to Giving Opportunity image. Image is hosted with Giving Impact. Image is served via HTTPS.|
| {{opportunity_thumb_url}} |  URL to Giving Opportunity thumbnail image. Image is hosted with Giving Impact. Image is serverd via HTTPS.|
| {{opportunity_youtube_id}} | YouTube ID for Giving Opportunity video. |

#### Variable Pairs

##### Related Campaign

If the parameter `related="true"` is added to the tag the following tag pair becomes available. Please note how within your tag pair the syntax used for campaign fields is different.

    {{opportunity_campaign}}
        {{campaign_id_token}}
        {{campaign_status}}
        ...
        All variables returned by the campaign tag above will be available here
    {{/opportunity_campaign}}

##### Campaign Responses

This is a collection of responses to the custom campaign fields defined by the parent campaign:

    {{opportunity_campaign_responses}}
        ...
    {{/opportunity_campaign_responses}}

The following is available in this tag pair:

| Variable        | Description|
| ------------- |:-------------|
| {{campaign_responses_field_id}} | Returns a unique identifier for the custom field |
| {{campaign_responses_field_type}} | Returns the type of field (dropdown, text, ...) |
| {{campaign_responses_field_label}} | Returns the label of the field |
| {{campaign_responses_response}} | Returns the donor's response if entered |
| {{campaign_responses_status}} | Returns `true` or `false` depending on whether the field is currently set to active or not |
| {{campaign_responses_required}} | Returns `true` or `false` depending on whether the field is currently required |

### Donations

    {{givingimpact:donations campaign="{id_token}"}} Content {{/givingimpact:donations}}

#### Parameters

##### Required Parameters

You need to provide a campaign token, opportunity token **or** dondation token **or** a supporter.

- A campaign token will generate a list of donations within the campaign, including those made through any children opportunities.
- An opportunity token will return a list of donations for the specified opportunity only.
- A donation token will return only the associated donation record data.


| Parameter | Data Type | Description |
| ------------ |:-------------|:-------------|
| campaign  | STRING | Parent campaign id_token |
| opportunity | STRING | Specfic opportunity id_token |
| donation | STRING | Specfic donation id_token |
| supporter | STRING | Can be unique supporter token OR supporter email address. This is used to display a list of donations for a supporter. |

##### Optional Parameters

| Parameter | Data Type | Description | Default |
| ------------ |:-------------|:-------------|:-------------|
| limit | INT | Limits the number of results returned. | 10 |
| offset | INT | Number of results to skip, useful for pagination. | 0 |
| sort | STRING | Property to sort results by. Also accepts a direction preceded by a pipe, e.g.    sort="gi_created_at&#124;desc"| gi_created_at |

#### Single Variables

| Variable        | Description|
| ------------- |:-------------|
| {{donation_id_token}} | Unique API token and id for the donation. |
| {{donation_donation_date}} | Timestamp of donation date and time. |
| {{donation_campaign}} OR {{donation_opportunity}} | Unique API token for campaign OR opportunity that the donation is most directly associated with.|
| {{donation_first_name}} | Donor first name |
| {{donation_last_name}} | Donor last name |
| {{donation_billing_address1}} | Donor address |
| {{donation_billing_city}} | Donor city |
| {{donation_billing_state}} | Donor State |
| {{donation_billing_postal_code}} | Donor zip code |
| {{donation_billing_country}} | Donor country |
| {{donation_donation_total}} | Amount donated (signed float) |
| {{donation_donation_level}} | The donation level selected if campaign is configured with donation levels. |
| {{donation_contactl}} | Returns `true` or `false` depending on whether the donor requested to be opted out of follow/up email communications.|
| {{donation_email_address}} | Donor email address unless donor has 'opted out' of receiving follow-up communications. |
| {{donation_offline}} |  Returns `true` or `false` depending on whether the donation was recorded offline (manually) or not. |
| {{donation_twitter_share}} | Returns `true` or `false` depending if the user shared the Campaign or Giving Opportunity with a tweet following their donation using the Giving Impact share available on donation confirmation page. |
| {{donation_fb_share}} | Returns `true` or `false` depending if the user shared the Campaign or Giving Opportunity with a Facebook Like following their donation using the Giving Impact share available on donation confirmation page. |

#### Variable Pairs

##### Custom Responses

    {{donation_custom_responses}}{{/donation_custom_responses}}

The following variables are available within this tag pair.

| Variable        | Description|
| ------------- |:-------------|
| {{custom_responses_field_id}} | Returns a unique identifier for the custom field |
| {{custom_responses_field_type}} | Returns the type of field (dropdown, text, ...) |
| {{custom_responses_field_label}} | Returns the label of the field |
| {{custom_responses_response}} | Returns the donor's response if entered |
| {{custom_responses_status}} | Returns `true` or `false` depending on whether the field is currently set to active or not |

#### Donation Form

    {{givingimpact:donate_form
      opportunity="######"
      id="donate-form"
    }}

    ... form content

    {{/givingimpact:donate_form}}

###### Required Parameters

| Parameter | Data Type | Description |
| ------------ |:-------------|:-------------|
| id  | STRING | The id added to form tag. PLEASE NOTE that it is critical that the id in the Javascript tag matches that in the form tag |
| campaign **or** opportunity | STRING | id_token for either the Campaign **or** Giving Opportunity donation is towards. |

###### Optional Parameters

| Parameter | Data Type | Description | Default |
| ------------ |:-------------|:-------------|:-------------|
| return | STRING | a return URL that supports `{{path=foo/bar}}` | returns to template of form |
| class | STRING | CSS class applied to `<form>` ||

#### Validation and Required Fields

##### Required Form Fields

The following must be submitted otherwise your request will display an error.

* first_name
* last_name
* email
* contact
* street
* city
* state
* country
* zip
* donation_amount OR donation_level_id
* card - Card token provided by Stripe

##### Validation and Error Handling

In the event of a data entry or card error, the user will be returned to your form and the `{{form_error}}{{/form_error}}` tag pair will be available. By looping through the tag pair, you'll be able to display the validation errors:

    {{givingimpact:donate_form}}

        {{ if form_errors }}
            Aww nuts!
            {{form_errors}}
                {{error}}
            {{/form_errors}}
        {{endif}}

        ...

    {{/givingimpact:donation_form}}

You may use the following variables to repopulate the form upon return from validation error.

* `{{value_first_name}}`
* `{{value_last_name}}`
* `{{value_email}}`
* `{{value_street}}`
* `{{value_city}}`
* `{{value_state}}`
* `{{value_zip}}`
* `{{value_donation_amount}}`

#### Returned Data

On successful submission and processing of form data, the API and module will return the new donations unique token. This value are returned in two ways.

1. The `donation_token` will be dynamically added as a GET parameter **return** parameter detailed above.
2. If you return to the same template that contains the form tag, you may use the `{{donation}}{{/donation}}` tag pair to get donation information.

#### Campaign Example Donation Checkout Form

Using the built-in `{{givingimpact:donate_form}}` tag pair, you can easily create a new form with all the necessary information.

Additionally, the `{{givingimpact:donate_js}}` tag includes the Giving Impact javascript wrapper that makes Stripe integration that much easier by including the standard Stripe javascript, adding error handling and validation.

The following is an example of a **Campaign** checkout Form. Please note that all Campaign data as detailed above is available within the form opening and closing tags. You can see examples of this in both the donation levels and custom donation fields areas.

    {{givingimpact:donate_form campaign="{{campaign_token}}" return="/path/to/return"}}

    <fieldset>
      <legend>Donation</legend>
        <label class="required">Donation Amount:</label>

        <input type="text" name="donation_amount" value="{{value_donation_amount}}" />

    </fieldset>
    <fieldset>
      <legend>Donor Information</legend>
        <label class="required">First Name:</label>
        <input type="text" name="first_name" value="{{value_first_name}}" />

        <label class="required">Last Name:</label>
        <input type="text" name="last_name" value="{{value_last_name}}" />

        <label class="required">Email:</label>
        <input type="text" name="email" value="{{value_email}}" />
        <label id="may_contact"><input type="checkbox" value="1" name="contact" id="may_contact" checked /> You may contact me with future updates</label>

    </fieldset>
    <fieldset>
      <legend>Payment Information</legend>
        <label class="required">Address:</label>
        <input type="text" name="street" value="{{value_street}}" placeholder="Street Address" />
        <input type="text" name="city" value="{{value_city}}" placeholder="City" />
        <input type="text" name="state" value="{{value_state}}" placeholder="State" />
        <input type="text" name="zip" value="{{value_zip}}" placeholder="Zip" />

        <label class="required">CC Number:</label>
        <input type="text" name="cc_number" placeholder="1234 5679 9012 3456" />

        <label class="required">CVC:</label>
        <input type="text" name="cc_cvc" placeholder="Security code" />

        <label class="required">CC EXP:</label>
        <input type="text" name="cc_exp" placeholder="MM / YYYY" />

    </fieldset>

    <input type="submit" value="Donate" id="process-donation" class="button radius" />
    {{/givingimpact:donate_form}}

    <!-- donate_js provides automatic Stripe integration and formatting, along with Stripe error handling -->
    {{givingimpact:donate_js}}

#### Opportunity Form

    {{givingimpact:opportunity_form
      campaign="######"
    }}

    ... form content

    {{/givingimpact:opportunity_form}}

###### Required Parameters

| Parameter | Data Type | Description |
| ------------ |:-------------|:-------------|
| campaign | STRING | id_token for the Campaign the Giving Opportunity is towards. |

###### Optional Parameters

| Parameter | Data Type | Description | Default |
| ------------ |:-------------|:-------------|:-------------|
| return | STRING | a return URL that supports `{{path=foo/bar}}` | returns to template of form |

#### Validation and Required Fields

##### Form Fields

* title (REQUIRED)
* description (REQUIRED)
* target
* youtube
* hash_tag
* analytics_id
* image (a "file" form element for Opportunity logo)
* fields (see Campaign Fields below)

##### Validation and Error Handling

In the event of a data entry or API error, the user will be returned to your form and the `{{form_error}}{{/form_error}}` tag pair will be available. By looping through the tag pair, you'll be able to display the validation errors:

    {{givingimpact:opportunity}}

        {{ if form_errors }}
            Aww nuts!
            {{form_errors}}
                {{error}}
            {{/form_errors}}
        {{endif}}

        ...

    {{/givingimpact:opportunity}}

You may use the following variables to repopulate the form upon return from validation error.

* `{{value_title}}`
* `{{value_description}}`
* `{{value_target}}`
* `{{value_youtube}}`
* `{{value_hash_tag}}`
* `{{value_analytics_id}}`

Additionally, the form exposes the parent `{{campaign}}` object along with `{{campaign_campaign_fields}}` for custom field generation.

#### Returned Data

On successful submission and processing of form data, the API and module will return the new opportunity's unique token. This value are returned in two ways.

1. The `opportunity_token` will be dynamically added as a GET parameter **return** parameter detailed above.
2. If you return to the same template that contains the form tag, you may use the `{{opportunity}}{{/opportunity}}` tag pair to get donation information.

#### Campaign Example Giving Opportunity Form

Using the built-in `{{givingimpact:opportunity_form}}` tag pair, you can easily create a new form with all the necessary information.

    {{givingimpact:opportunity_form campaign="fa0d0220e0"}}

        {{ if form_errors }}
            Aww nuts!
            {{form_errors}}
                {{error}}
            {{/form_errors}}
        {{endif}}

        {{campaign}}
            Create new Giving Opportunity for {{campaign_title}}
        {{/campaign}}

        {{opportunity}}
            Awesome! Your Giving Opportunity, {{opportunity_title}} has been created!
        {{/opportunity}}

        <label>Title</label>
        <input type="text" name="title" value="{{value_title}}" />

        <label>Description</label>
        <textarea name="description" rows="5" cols="50">{{value_description}}</textarea>

        <label>YouTube Video ID</label>
        <input type="text" name="youtube" value="{{value_youtube}}" />

        <label>Hash Tag</label>
        <input type="text" name="hash_tag" value="{{value_hash_tag}}" />

        <label>Google Analytics Tracking ID</label>
        <input type="text" name="analytics_id" value="{{value_analytics_id}}" />

        <label>Donation Target</label>
        <input type="text" name="target" value="{{value_target}}" />

        <label>Campaign Logo</label>
        <input type="file" name="image" />

        {{campaign_campaign_fields}}
            {{if campaign_fields_status}}
                <label>{{campaign_fields_field_label}}</label>
                {{if campaign_fields_field_type == "dropdown"}}
                    <select name="fields[{{campaign_fields_field_id}}]">
                        {{campaign_fields_options}}
                            <option value="{{value}}">{{value}}</option>
                        {{/campaign_fields_options}}
                    </select>
                {{else}}
                    <input type="text" name="fields[{{campaign_fields_field_id}}]" />
                {{endif}}
                <br />
            {{endif}}
        {{/campaign_campaign_fields}}

        <input type="submit" value="Create Opportunity" />

    {{/givingimpact:opportunity_form}}

### Hooks

##### givingimpact__before_opportunity

The `before_opportunity` hook is fired _before_ a new opportunity is created via the opportunity form. This `call` hook emits an array with the following information

| Parameter | Type | Description |
| ------------ |:-------------|:-------------|
| title | STRING | Opportunity title |
| description | STRING | User description |
| youtube | STRING | YouTube video id |
| hash_tag | STRING | Campaign hashtag |
| target | INT | Campaign donation target |


##### givingimpact__after_opportunity

The `after_opportunity` hook is fired _after_ a new opportunity is created via the opportunity form. This `call` hook emits an array with the following information

| Parameter | Type | Description |
| ------------ |:-------------|:-------------|
| opportunity_token | STRING | The id token of the new opportunity |
| opportunity | OBJECT | The new opportunity object |

##### givingimpact__before_donation

The `before_donation` hook is fired _before_ a new donation is created via the donate form. This `call` hook emits an array with the following information

| Parameter | Type | Description |
| ------------ |:-------------|:-------------|
| first_name | STRING | Donor first name |
| last_name | STRING | Donor last name |
| email | STRING | Donor email |
| street | STRING | Donor street address |
| city | STRING | Donor city |
| state | STRING | Donor state/province |
| zip | STRING | Donor zip |
| donation_level | STRING | Donation level, if applicable |
| donation_level_id | INT | Donation level unique ID, if applicable |
| donation_amount | INT | Donation amount |
| contact | BOOLEAN | Whether the donor wants to be contacted |


##### givingimpact__after_donation

The `after_donation` hook is fired _after_ a new donation is created via the donate form. This `call` hook emits an array with the following information

| Parameter | Type | Description |
| ------------ |:-------------|:-------------|
| donation_token | STRING | The id token of the new donation |
| donation | OBJECT | The new donation object |

