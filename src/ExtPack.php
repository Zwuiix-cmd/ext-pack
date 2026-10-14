<?php

/*
 *
 * Licensed under the Apache License, Version 2.0.
 *
 * You may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * @author Zwuiix
 * @link https://github.com/Zwuiix-cmd/ext-pack
 *
 */

namespace zwuiix\extpack;

use pocketmine\resourcepacks\ResourcePack;
use pocketmine\resourcepacks\ResourcePackManager;
use pocketmine\Server;
use pocketmine\utils\Internet;
use ReflectionClass;
use Symfony\Component\Filesystem\Path;
use Throwable;
use function array_merge;
use function basename;
use function file_put_contents;
use function is_dir;
use function is_file;
use function md5;
use function parse_url;
use function sys_get_temp_dir;
use function unlink;
use const DIRECTORY_SEPARATOR;

class ExtPack
{
	/**
	 * @param string $path
	 * @param string $contentKey
	 * @return ResourcePack|null
	 */
	public static function fromPath(string $path, string $contentKey = "") : ?ResourcePack
	{
		$server = Server::getInstance();
		$manager = $server->getResourcePackManager();
		$logger = $server->getLogger();

		$path = Path::canonicalize($path);

		if (!is_file($path) && !is_dir($path)) {
			$logger->error("ExtPack: Cannot load resource pack, path does not exist: \"$path\"");
			return null;
		}

		try {
			$refClass = new ReflectionClass(ResourcePackManager::class);

			/** @var ResourcePack $pack */
			$pack = $refClass->getMethod("loadPackFromPath")->invoke($manager, $path);
		} catch (Throwable $e) {
			$logger->error("ExtPack: Failed to parse resource pack at \"$path\" -> " . $e->getMessage());
			return null;
		}

		if ($contentKey !== "") {
			$manager->setPackEncryptionKey($pack->getPackId(), $contentKey);
			$logger->debug("ExtPack: Applied encryption key to pack \"{$pack->getPackName()}\" ({$pack->getPackId()})");
		}

		$manager->setResourceStack(array_merge($manager->getResourceStack(), [$pack]));
		$logger->info("ExtPack: Successfully loaded resource pack \"{$pack->getPackName()}\" (v{$pack->getPackVersion()})");
		return $pack;
	}

	/**
	 * @param string $url
	 * @param string $contentKey
	 * @return ResourcePack|null
	 */
	public static function fromUrl(string $url, string $contentKey = "") : ?ResourcePack
	{
		$server = Server::getInstance();
		$manager = $server->getResourcePackManager();
		$logger = $server->getLogger();

		$parsedUrl = parse_url($url);
		$filename = basename($parsedUrl['path'] ?? 'pack.zip');
		$host = $parsedUrl['host'] ?? 'remote URL';

		$logger->info("ExtPack: Fetching resource pack \"$filename\" from $host...");
		$result = Internet::getURL($url);

		if ($result === null || $result->getCode() !== 200) {
			$code = $result !== null ? $result->getCode() : "Timeout/Error";
			$logger->error("ExtPack: Failed to fetch resource pack from URL (HTTP Code: $code).");
			return null;
		}

		$path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . md5($url) . ".zip";
		if (file_put_contents($path, $result->getBody()) === false) {
			$logger->error("ExtPack: Failed to save downloaded resource pack.");
			return null;
		}

		try {
			$refClass = new ReflectionClass(ResourcePackManager::class);

			/** @var ResourcePack $tmp */
			$pack = $refClass->getMethod("loadPackFromPath")->invoke($manager, $path);
		} catch (Throwable $e) {
			$logger->error("ExtPack: Failed to parse resource pack from \"$url\" -> " . $e->getMessage());
			@unlink($path);
			return null;
		}

		if ($contentKey !== "") {
			$manager->setPackEncryptionKey($pack->getPackId(), $contentKey);
			$logger->debug("ExtPack: Applied encryption key to pack \"{$pack->getPackName()}\" ({$pack->getPackId()})");
		}

		$manager->setResourceStack(array_merge($manager->getResourceStack(), [$pack]));
		$logger->info("ExtPack: Successfully loaded resource pack \"{$pack->getPackName()}\" (v{$pack->getPackVersion()}) from URL");
		return $pack;
	}
}
