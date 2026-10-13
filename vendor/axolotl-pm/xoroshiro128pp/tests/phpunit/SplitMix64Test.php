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

use PHPUnit\Framework\TestCase;

class SplitMix64Test extends TestCase{

	/**
	 * Expected values come from an independent implementation of the finaliser, so a shared misreading of the
	 * algorithm cannot pass. Reproducing them exactly is the requirement: a generator that merely looks random
	 * would still desync from the game it is meant to mirror.
	 *
	 * @return \Generator<string, array{int, int}>
	 */
	public static function referenceValues() : \Generator{
		yield "zero maps to zero" => [0, 0];
		yield "one" => [1, 6238072747940578789];
		yield "two" => [2, -2606959012126976886];
		yield "golden ratio" => [-7046029254386353131, -2152535657050944081];
		yield "silver ratio" => [7640891576956012809, 3847398142028685078];
		yield "all bits set" => [-1, -5417735806833148549];
		yield "arbitrary" => [81985529216486895, -5566351399199633108];
	}

	/**
	 * @dataProvider referenceValues
	 */
	public function testMatchesReferenceImplementation(int $seed, int $expected) : void{
		self::assertSame($expected, SplitMix64::mixStafford13($seed));
	}

	public function testIsDeterministic() : void{
		self::assertSame(SplitMix64::mixStafford13(12345), SplitMix64::mixStafford13(12345));
	}

	/**
	 * The mixer has to be a bijection, otherwise distinct seeds could collapse onto one state and two different
	 * positions would produce identical "random" results.
	 */
	public function testDistinctSeedsStayDistinct() : void{
		$seen = [];
		for($seed = -50; $seed <= 50; $seed++){
			$seen[] = SplitMix64::mixStafford13($seed);
		}
		self::assertSame($seen, array_values(array_unique($seen)));
	}
}
