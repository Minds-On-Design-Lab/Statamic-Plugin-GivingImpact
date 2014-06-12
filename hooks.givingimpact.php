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

    public function givingimpact__post_opportunity() {
        echo 'hi';
    }

}