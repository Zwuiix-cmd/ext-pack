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

class Xoroshiro128PlusPlusTest extends TestCase{

	/**
	 * Every expected value below comes from an independent implementation of Xoroshiro128++ rather than from this
	 * one, so the test fails if the port drifts from the reference the game itself follows. Matching bit for bit is
	 * the actual requirement: these numbers are used to agree with a client that already computed them.
	 *
	 * @return \Generator<string, array{int, int, list<int>}>
	 */
	public static function rawStates() : \Generator{
		yield "small state" => [1, 2, [
			393217,
			669327710093319,
			1732421326133921491,
			-7051953992050424633,
			-8891291296936358940,
			3586421180005889563,
		]];
		yield "high bits set" => [-1, 1, [
			-1,
			-35734131965952,
			-1152402534833585527,
			1011031301013903998,
			-4433826628979058053,
			283823977746095602,
		]];
		yield "arbitrary" => [81985529216486895, -81985529216486896, [
			81985529216486894,
			-6887384918253362076,
			-3704330611534247336,
			-6724422987835305309,
			-251466190376645636,
			4870808178002823119,
		]];
	}

	/**
	 * @param list<int> $expected
	 *
	 * @dataProvider rawStates
	 */
	public function testRawStateMatchesReferenceImplementation(int $lo, int $hi, array $expected) : void{
		$random = new Xoroshiro128PP($lo, $hi);
		foreach($expected as $i => $want){
			self::assertSame($want, $random->nextLong(), "output $i");
		}
	}

	/**
	 * @return \Generator<string, array{int, list<int>}>
	 */
	public static function seeds() : \Generator{
		yield "zero" => [0, [
			3038984756725240190,
			-3694039286755638414,
			4633751808701151732,
			2160572957309072155,
		]];
		yield "one" => [1, [
			-1033667707219518978,
			6451672561743293322,
			-1821890263888393630,
			890086654470169703,
		]];
		yield "negative" => [-1, [
			-8676505878415342125,
			-868585888688873692,
			-6331679347063163302,
			-2068491455652362927,
		]];
		yield "arbitrary" => [42, [
			-4695948378737616609,
			7341713790291473579,
			-7542733514721318211,
			4888889476139319686,
		]];
	}

	/**
	 * @param list<int> $expected
	 *
	 * @dataProvider seeds
	 */
	public function testFromSeedMatchesReferenceImplementation(int $seed, array $expected) : void{
		$random = Xoroshiro128PP::fromSeed($seed);
		foreach($expected as $i => $want){
			self::assertSame($want, $random->nextLong(), "output $i");
		}
	}

	/**
	 * @return \Generator<string, array{int, list<float>}>
	 */
	public static function floatSeeds() : \Generator{
		yield "zero" => [0, [0.16474366188049316, 0.7997456789016724, 0.25119614601135254, 0.11712485551834106]];
		yield "one" => [1, [0.9439647197723389, 0.34974586963653564, 0.9012351036071777, 0.0482516884803772]];
		yield "negative" => [-1, [0.5296456217765808, 0.9529138207435608, 0.6567589640617371, 0.8878667950630188]];
		yield "arbitrary" => [42, [0.7454320788383484, 0.3979950547218323, 0.5911075472831726, 0.2650272250175476]];
	}

	/**
	 * @param list<float> $expected
	 *
	 * @dataProvider floatSeeds
	 */
	public function testNextFloatMatchesReferenceImplementation(int $seed, array $expected) : void{
		$random = Xoroshiro128PP::fromSeed($seed);
		foreach($expected as $i => $want){
			self::assertEqualsWithDelta($want, $random->nextFloat(), 1e-12, "output $i");
		}
	}

	/**
	 * An all-zero state is a fixed point of the recurrence, so it has to be replaced or the generator would return
	 * nothing but zeroes.
	 */
	public function testAllZeroStateIsReplaced() : void{
		$zero = new Xoroshiro128PP(0, 0);
		$fallback = new Xoroshiro128PP(-7046029254386353131, 7640891576956012809);

		$outputs = [];
		for($i = 0; $i < 4; $i++){
			$outputs[] = $zero->nextLong();
			self::assertSame($fallback->nextLong(), $outputs[$i]);
		}
		self::assertNotSame([0, 0, 0, 0], $outputs);
	}

	public function testNextFloatStaysInRange() : void{
		$random = Xoroshiro128PP::fromSeed(20260824);
		for($i = 0; $i < 5000; $i++){
			$value = $random->nextFloat();
			self::assertGreaterThanOrEqual(0.0, $value);
			self::assertLessThan(1.0, $value);
		}
	}

	public function testSameSeedGivesSameSequence() : void{
		$a = Xoroshiro128PP::fromSeed(777);
		$b = Xoroshiro128PP::fromSeed(777);
		for($i = 0; $i < 32; $i++){
			self::assertSame($a->nextLong(), $b->nextLong());
		}
	}
}
