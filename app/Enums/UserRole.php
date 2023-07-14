<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class UserRole extends Enum
{
    /**
     * Sinh viên
     */
    const NUSER = 0;

    /**
     * Doanh nghiệp
     */
    const CUSER = 1;
}
