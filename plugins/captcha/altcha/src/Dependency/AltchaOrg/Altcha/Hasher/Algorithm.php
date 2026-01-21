<?php

declare(strict_types=1);

namespace Akeeba\Plugin\Captcha\Altcha\Dependency\AltchaOrg\Altcha\Hasher;

enum Algorithm: string
{
    case SHA1 = 'SHA-1';
    case SHA256 = 'SHA-256';
    case SHA512 = 'SHA-512';
}
