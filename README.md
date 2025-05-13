# 🎥 Twitch to M3U

A lightweight and developer-friendly PHP script to retrieve the **direct `.m3u8` HLS stream URL** for any live Twitch channel. It leverages Twitch's public GraphQL API to generate a valid token and signature for authenticated playback.

---

## ✨ Features

- 🔐 Generates **secure** stream access tokens and signatures via Twitch's GQL API
- 🖥️ Supports **browser** and **CLI** usage
- 🌐 Returns result as:
  - HTTP redirect (for browsers)
  - JSON (if `format=json` is passed)
- ✅ Input validation included
- 📦 No dependencies beyond built-in PHP cURL

---

## ⚙️ Requirements

- PHP 7.0+
- `php-curl` enabled

---

## 🚀 Usage

### 🌍 Web (GET)

Upload `script.php` to your web server and access it like so:

```bash
GET /script.php?channel=shroud
```

This will respond with a `Location` redirect to the `.m3u8` stream.

Want JSON instead?

```bash
GET /script.php?channel=shroud&format=json
```

#### Optional: Set `Content-Type: application/json` header for JSON output

---

### 💻 CLI

Run from terminal:

```bash
php script.php channel=shroud
```

To get JSON output:

```bash
php script.php channel=shroud format=json
```

---

## 📥 Parameters

| Name       | Required | Description                                      |
|------------|----------|--------------------------------------------------|
| `channel`  | ✅ Yes    | Twitch channel name (e.g., `shroud`)             |
| `format`   | ❌ No     | Use `json` for JSON output, otherwise redirects |

---

## 🧪 Example JSON Output

```json
{
  "success": true,
  "channel": "shroud",
  "url": "https://usher.ttvnw.net/api/channel/hls/shroud.m3u8?...&sig=...&token=..."
}
```

On error:

```json
{
  "success": false,
  "error": "Channel not found or offline"
}
```

---

## 🧼 Notes

- Only works if the channel is **currently live**
- Output URL is valid for direct streaming (e.g. with `ffmpeg`, `VLC`, or in web players)
- Twitch may update their GQL schema or validation mechanisms at any time

---

## 📄 License

MIT License

---

## 🙌 Credits

- Originally authored by [toxiicdev.net](https://toxiicdev.net)
- Maintained & cleaned up for open source by contributors
