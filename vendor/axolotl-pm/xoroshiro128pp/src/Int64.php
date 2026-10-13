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

use function gmp_and;
use function gmp_cmp;
use function gmp_div_q;
use function gmp_init;
use function gmp_intval;
use function gmp_mul;
use function gmp_or;
use function gmp_pow;
use function gmp_sub;

/**
 * Unsigned 64-bit arithmetic with wraparound.
 *
 * @internal
 */
final class Int64{

	private const MASK64 = "0xffffffffffffffff";
	private const MAX_SIGNED = "0x7fffffffffffffff";

	public static function mask(\GMP $value) : \GMP{
		return gmp_and($value, gmp_init(self::MASK64));
	}

	/**
	 * Logical (zero-filling) shift right, as opposed to PHP's `>>`, which propagates the sign bit.
	 */
	public static function shr(\GMP $value, int $bits) : \GMP{
		return gmp_div_q(self::mask($value), gmp_pow(2, $bits));
	}

	public static function shl(\GMP $value, int $bits) : \GMP{
		return self::mask(gmp_mul($value, gmp_pow(2, $bits)));
	}

	public static function rotl(\GMP $value, int $bits) : \GMP{
		return self::mask(gmp_or(self::shl($value, $bits), self::shr($value, 64 - $bits)));
	}

	/**
	 * Reinterprets a PHP int as an unsigned 64-bit value, so a negative input becomes its two's complement.
	 */
	public static function fromSigned(int $value) : \GMP{
		return self::mask(gmp_init($value));
	}

	/**
	 * Reinterprets an unsigned 64-bit value as a PHP int, wrapping anything above PHP_INT_MAX to negative the way a
	 * two's complement 64-bit integer does.
	 */
	public static function toSigned(\GMP $value) : int{
		$masked = self::mask($value);
		if(gmp_cmp($masked, gmp_init(self::MAX_SIGNED)) > 0){
			$masked = gmp_sub($masked, gmp_pow(2, 64));
		}
		return gmp_intval($masked);
	}
}
