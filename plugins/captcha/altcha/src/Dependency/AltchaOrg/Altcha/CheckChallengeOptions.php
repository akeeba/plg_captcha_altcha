<?php

declare(strict_types=1);

namespace Akeeba\Plugin\Captcha\Altcha\Dependency\AltchaOrg\Altcha;

use Akeeba\Plugin\Captcha\Altcha\Dependency\AltchaOrg\Altcha\Hasher\Algorithm;

class CheckChallengeOptions extends BaseChallengeOptions
{
    public function __construct(
        Algorithm $algorithm,
        string $salt,
        int $number,
    ) {
        parent::__construct($algorithm, self::DEFAULT_MAX_NUMBER, null, $salt, $number, []);
    }
}
