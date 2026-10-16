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

use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\Internet;
use function file_put_contents;
use function md5;
use function sys_get_temp_dir;
use const DIRECTORY_SEPARATOR;

class FetchPackAsyncTask extends AsyncTask
{
	private const string TLS_KEY_ON_COMPLETION = "completion";

	private string $url;
	private string $tempPath;
	private ?string $errorMessage = null;
	private int $httpCode = 0;

	public function __construct(string $url, \Closure $onCompletion)
	{
		$this->url = $url;
		$this->tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . md5($url) . ".zip";
		$this->storeLocal(self::TLS_KEY_ON_COMPLETION, $onCompletion);
	}

	public function onRun() : void
	{
		$result = Internet::getURL($this->url);

		if ($result === null) {
			$this->errorMessage = "Timeout/Error";
			return;
		}

		$this->httpCode = $result->getCode();
		if ($this->httpCode !== 200) {
			$this->errorMessage = (string) $this->httpCode;
			return;
		}

		if (file_put_contents($this->tempPath, $result->getBody()) === false) {
			$this->errorMessage = "Failed to save downloaded resource pack.";
		}
	}

	public function onCompletion() : void
	{
		$onCompletion = $this->fetchLocal(self::TLS_KEY_ON_COMPLETION);
		if ($onCompletion !== null) {
			($onCompletion)($this->tempPath, $this->httpCode, $this->errorMessage);
		}
	}
}
