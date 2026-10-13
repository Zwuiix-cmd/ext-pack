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

use pocketmine\resourcepacks\json\Manifest;
use pocketmine\resourcepacks\ResourcePack;
use pocketmine\resourcepacks\ResourcePackException;
use function implode;

class UrlResourcePack implements ResourcePack
{
	/**
	 * @param string   $url
	 * @param Manifest $manifest
	 * @param string   $sha256
	 * @param int      $size
	 */
	public function __construct(
		protected string $url,
		protected Manifest $manifest,
		protected string $sha256,
		protected int $size,
	) {
	}

	/**
	 * @return string
	 */
	public function getPackName() : string
	{
		return $this->manifest->header->name;
	}

	/**
	 * @return string
	 */
	public function getPackVersion() : string
	{
		return implode(".", $this->manifest->header->version);
	}

	/**
	 * @return string
	 */
	public function getPackId() : string
	{
		return $this->manifest->header->uuid;
	}

	/**
	 * @return int
	 */
	public function getPackSize() : int
	{
		return $this->size;
	}

	/**
	 * @param bool $cached
	 * @return string
	 */
	public function getSha256(bool $cached = true) : string
	{
		return $this->sha256;
	}

	/**
	 * @param int $start
	 * @param int $length
	 * @return string
	 */
	public function getPackChunk(int $start, int $length) : string
	{
		throw new ResourcePackException("Cannot read chunks from a CDN UrlResourcePack.");
	}

	/**
	 * @return string
	 */
	public function getUrl() : string
	{
		return $this->url;
	}
}
