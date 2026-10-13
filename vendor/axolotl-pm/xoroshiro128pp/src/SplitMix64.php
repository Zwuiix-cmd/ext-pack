<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\xoroshiro128pp;

use function gmp_init;
use function gmp_mul;
use function gmp_xor;

/**
 * The SplitMix64 finaliser, in the variant Stafford numbered 13.
 *
 * On its own it is a bijection that scatters the bits of a single 64-bit value. Xoroshiro128++ uses it to turn one
 * seed into a full 128-bit state, since feeding the raw seed in directly would leave the first few outputs
 * correlated with it.
 */
final class SplitMix64{

	private const STAFFORD_1 = "0xbf58476d1ce4e5b9";
	private const STAFFORD_2 = "0x94d049bb133111eb";

	public static function mixStafford13(int $seed) : int{
		$z = Int64::fromSigned($seed);
		$z = Int64::mask(gmp_mul(gmp_xor($z, Int64::shr($z, 30)), gmp_init(self::STAFFORD_1)));
		$z = Int64::mask(gmp_mul(gmp_xor($z, Int64::shr($z, 27)), gmp_init(self::STAFFORD_2)));
		return Int64::toSigned(gmp_xor($z, Int64::shr($z, 31)));
	}
}
