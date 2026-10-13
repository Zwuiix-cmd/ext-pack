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

use muqsit\simplepackethandler\SimplePacketHandler;
use pocketmine\event\Listener;
use pocketmine\network\mcpe\handler\ResourcePacksPacketHandler;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\ResourcePacksInfoPacket;
use pocketmine\network\mcpe\protocol\types\resourcepacks\ResourcePackInfoEntry;
use pocketmine\plugin\Plugin;
use pocketmine\resourcepacks\ResourcePack;
use pocketmine\Server;
use function array_filter;

class PackPacketHooker implements Listener
{
	/** @var bool */
	private static bool $isRegistered = false;

	/**
	 * @return bool
	 */
	public static function isRegistered() : bool
	{
		return self::$isRegistered;
	}

	/**
	 * @param Plugin $registrant
	 * @return void
	 */
	public static function register(Plugin $registrant) : void
	{
		if (self::$isRegistered) {
			throw new \RuntimeException("Event listener is already registered by another plugin.");
		}

		$interceptor = SimplePacketHandler::createMonitor($registrant, false);
		$interceptor->monitorOutgoing(function (ResourcePacksInfoPacket $pk, NetworkSession $target) : void {
			$packManager = Server::getInstance()->getResourcePackManager();
			foreach ($pk->resourcePackEntries as $entry) {
				$pack = $packManager->getPackById($entry->getContentId());
				if ($pack instanceof UrlResourcePack) {
					$property = new \ReflectionProperty(ResourcePackInfoEntry::class, 'cdnUrl');
					$property->setValue($entry, $pack->getUrl());
				}
			}

			$handler = $target->gethandler();
			if ($handler instanceof ResourcePacksPacketHandler) {
				$property = new \ReflectionProperty(ResourcePacksPacketHandler::class, 'resourcePacksById');
				$property->setValue($handler, array_filter($property->getValue($handler), fn(ResourcePack $pack) => !$pack instanceof UrlResourcePack));
			}
		});
		self::$isRegistered = true;
	}
}
