<?php

declare(strict_types=1);

namespace Akeeba\Plugin\Captcha\Altcha\Dependency\AltchaOrg\Altcha;

defined('_JEXEC') || die;

class ServerSignatureVerification
{
    public function __construct(
        public readonly bool $verified,
        public readonly ?ServerSignatureVerificationData $data,
    ) {
    }
}
