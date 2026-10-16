<h1 align="center">
    <img src="https://raw.githubusercontent.com/Zwuiix-cmd/remote-pack/refs/heads/main/icon.png" alt="Logo remote-pack" width="150" />
    <br>
    remote-pack
</h1>

<p align="center">
    <a><img src="https://img.shields.io/badge/php-^8.2.0-informational" alt="Version"></a>
    <a href="https://github.com/Zwuiix-cmd/remote-pack/releases"><img alt="GitHub release (latest SemVer)" src="https://img.shields.io/github/v/release/Zwuiix-cmd/remote-pack?label=release&sort=semver"></a>
    <a href="https://discord.gg/KCm6rUFXbJ"><img src="https://img.shields.io/badge/Discord-Join-7289da?logo=discord&logoColor=white" alt="Discord"></a>
</p>

<p align="center">
    <a href="https://github.com/Zwuiix-cmd/remote-pack/graphs/contributors">
        <img src="https://contrib.rocks/image?repo=Zwuiix-cmd/remote-pack&max=500" alt="Contributors" />
    </a>
</p>

---

**remote-pack** is a lightweight PocketMine-MP library designed to bypass default resource pack limitations. It allows developers to seamlessly load resource packs from custom external directories or directly from CDN URLs.

Instead of forcing server owners to place every pack inside the default `resource_packs` folder, remote-pack handles on-the-fly downloading, temporary storage, manifest parsing, and client injection automatically.

### Features
* **Load from Path:** Mount resource packs from any folder on your machine.
* **Load from URL:** Fetch and load zipped resource packs directly from a web CDN.
* **Bedrock Compliant:** Automatically computes the raw SHA256 hashes required by Minecraft Bedrock clients for remote packs.
* **Easy Injection:** Built-in packet interception to dynamically inject CDN URLs into the `ResourcePacksInfoPacket`.

---

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

## License
This project is licensed under the [Apache License 2.0](LICENSE).

---

<a href="https://www.star-history.com/?repos=Zwuiix-cmd%2Fremote-pack&type=timeline&logscale=&legend=top-left">
  <picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://api.star-history.com/svg?repos=Zwuiix-cmd/remote-pack&type=Date&theme=dark" />
    <source media="(prefers-color-scheme: light)" srcset="https://api.star-history.com/svg?repos=Zwuiix-cmd/remote-pack&type=Date" />
    <img alt="Star History Chart" src="https://api.star-history.com/svg?repos=Zwuiix-cmd/remote-pack&type=Date" />
  </picture>
</a>
