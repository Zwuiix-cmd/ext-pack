# Xoroshiro128PP
![CI](https://github.com/axolotl-pm/Xoroshiro128PP/workflows/CI/badge.svg)

Xoroshiro128++ random number generator, made for use in PocketMine-MP.

## Usage

```php
use pocketmine\xoroshiro128pp\Xoroshiro128PP;

$random = Xoroshiro128PP::fromSeed($seed);
$random->nextLong();   // signed 64-bit
$random->nextFloat();  // [0, 1)
```

- `Xoroshiro128PP` - the generator. `fromSeed()` expands one 64-bit seed into the 128-bit state the way Minecraft does; the constructor takes the two halves of the state directly.
- `SplitMix64` - the finaliser (Stafford variant 13) that seeding is built on, exposed because position hashes often need it on its own.

## Requirements

- PHP 8.1 or newer, 64-bit
- `ext-gmp`
