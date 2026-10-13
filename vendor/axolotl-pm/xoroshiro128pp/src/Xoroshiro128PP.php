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

use function gmp_add;
use function gmp_init;
use function gmp_intval;
use function gmp_or;
use function gmp_sign;
use function gmp_xor;

final class Xoroshiro128PP{

	/** floor(2^64 / phi); the odd stride SplitMix64 walks a seed by to produce the second half of the state */
	private const GOLDEN_RATIO_64 = "0x9e3779b97f4a7c15";
	/** floor(2^64 * frac(sqrt 2)); mixed into the seed so that seed 0 does not produce a degenerate state */
	private const SILVER_RATIO_64 = "0x6a09e667f3bcc909";

	private \GMP $lo;
	private \GMP $hi;

	/**
	 * Takes the two halves of the state directly. Both are read as unsigned 64-bit values, so negative ints are
	 * their two's complement.
	 */
	public function __construct(int $seedLo, int $seedHi){
		$lo = Int64::fromSigned($seedLo);
		$hi = Int64::fromSigned($seedHi);
		if(gmp_sign(gmp_or($lo, $hi)) === 0){
			//an all-zero state is a fixed point of the recurrence, so it would emit nothing but zeroes forever
			$lo = gmp_init(self::GOLDEN_RATIO_64);
			$hi = gmp_init(self::SILVER_RATIO_64);
		}
		$this->lo = $lo;
		$this->hi = $hi;
	}

	/**
	 * Expands a single 64-bit seed into the 128-bit state, the way Minecraft does.
	 */
	public static function fromSeed(int $seed) : self{
		$lo = Int64::mask(gmp_xor(Int64::fromSigned($seed), gmp_init(self::SILVER_RATIO_64)));
		$hi = Int64::mask(gmp_add($lo, gmp_init(self::GOLDEN_RATIO_64)));

		return new self(
			SplitMix64::mixStafford13(Int64::toSigned($lo)),
			SplitMix64::mixStafford13(Int64::toSigned($hi))
		);
	}

	private function next() : \GMP{
		$result = Int64::mask(gmp_add(Int64::rotl(Int64::mask(gmp_add($this->lo, $this->hi)), 17), $this->lo));

		$t = gmp_xor($this->hi, $this->lo);
		$this->lo = Int64::mask(gmp_xor(gmp_xor(Int64::rotl($this->lo, 49), $t), Int64::shl($t, 21)));
		$this->hi = Int64::rotl($t, 28);

		return $result;
	}

	/**
	 * Returns the next 64 bits, reinterpreted as a signed PHP int.
	 */
	public function nextLong() : int{
		return Int64::toSigned($this->next());
	}

	/**
	 * Returns the next value scaled into [0, 1).
	 *
	 * Only the top 24 bits are used, because a float's mantissa cannot hold more than that anyway and the high bits
	 * of this generator are the better distributed ones.
	 */
	public function nextFloat() : float{
		return gmp_intval(Int64::shr($this->next(), 40)) * (2.0 ** -24);
	}
}
