<?php

declare(strict_types=1);

namespace Akeeba\Plugin\Captcha\Altcha\Dependency\AltchaOrg\Altcha;

defined('_JEXEC') || die;

class Solution
{
    public function __construct(
        public readonly int $number,
        public readonly float $took,
    ) {
    }
}
