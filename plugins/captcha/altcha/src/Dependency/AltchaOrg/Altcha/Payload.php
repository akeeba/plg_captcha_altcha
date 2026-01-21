<?php

declare(strict_types=1);

namespace Akeeba\Plugin\Captcha\Altcha\Dependency\AltchaOrg\Altcha;

use Akeeba\Plugin\Captcha\Altcha\Dependency\AltchaOrg\Altcha\Hasher\Algorithm;

class Payload
{
    public function __construct(
        public readonly Algorithm $algorithm,
        public readonly string $challenge,
        public readonly int $number,
        public readonly string $salt,
        public readonly string $signature,
    ) {
    }
}
