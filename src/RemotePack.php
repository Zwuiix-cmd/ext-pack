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
 * @link https://github.com/Zwuiix-cmd/remote-pack
 *
 */

namespace zwuiix\remotepack;

use pocketmine\promise\Promise;
use pocketmine\promise\PromiseResolver;
use pocketmine\resourcepacks\ResourcePack;
use pocketmine\resourcepacks\ResourcePackManager;
use pocketmine\Server;
use ReflectionClass;
use Symfony\Component\Filesystem\Path;
use Throwable;
use function array_merge;
use function basename;
use function file_exists;
use function is_dir;
use function is_file;
use function parse_url;
use function unlink;

class RemotePack
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
	 * @return Promise
	 */
	public static function fromUrl(string $url, string $contentKey = "") : Promise
	{
		$server = Server::getInstance();
		$logger = $server->getLogger();

		$parsedUrl = parse_url($url);
		$filename = basename($parsedUrl['path'] ?? 'pack.zip');
		$host = $parsedUrl['host'] ?? 'remote URL';

		$logger->info("ExtPack: Fetching resource pack \"$filename\" from $host...");

		$resolver = new PromiseResolver();
		$server->getAsyncPool()->submitTask(new FetchPackAsyncTask($url, function (string $path, int $httpCode, ?string $errorMessage) use ($url, $contentKey, $resolver, $server, $logger) {
			$manager = $server->getResourcePackManager();

			if ($errorMessage !== null) {
				$logger->error("ExtPack: Failed to fetch resource pack from URL (HTTP Code: $errorMessage).");
				$resolver->reject();
				return;
			}

			if (!file_exists($path)) {
				$logger->error("ExtPack: Failed to save downloaded resource pack.");
				$resolver->reject();
				return;
			}

			try {
				$refClass = new ReflectionClass(ResourcePackManager::class);

				/** @var ResourcePack $pack */
				$pack = $refClass->getMethod("loadPackFromPath")->invoke($manager, $path);
			} catch (Throwable $e) {
				$logger->error("ExtPack: Failed to parse resource pack from \"$url\" -> " . $e->getMessage());
				@unlink($path);
				$resolver->reject();
				return;
			}

			if ($contentKey !== "") {
				$manager->setPackEncryptionKey($pack->getPackId(), $contentKey);
				$logger->debug("ExtPack: Applied encryption key to pack \"{$pack->getPackName()}\" ({$pack->getPackId()})");
			}

			$manager->setResourceStack(array_merge($manager->getResourceStack(), [$pack]));
			$logger->info("ExtPack: Successfully loaded resource pack \"{$pack->getPackName()}\" (v{$pack->getPackVersion()}) from URL");

			$resolver->resolve($pack);
		}));

		return $resolver->getPromise();
	}
}
