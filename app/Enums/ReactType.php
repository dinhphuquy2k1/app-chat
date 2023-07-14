<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class ReactType extends Enum
{
    const NONE = 0;
    const HAHA = 1;
    const SAD = 2;

}
