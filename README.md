# Aimax – YouTube Channel Scraper Admin

A premium WordPress plugin designed to manage and scrape YouTube channel data directly from your WP Admin Dashboard. It leverages a powerful Python backend to fetch video details efficiently and securely.

## 🚀 Overview

The **YouTube Channel Scraper** provides a seamless interface for WordPress administrators to track multiple YouTube channels and extract their video information (Title, URL, Channel Name, and High-Res Thumbnails). The plugin uses a background processing approach to ensure that high-volume scraping tasks don't slow down your website.

## ✨ Key Features

- **Channel Management**: Easily add or remove YouTube channels by their Channel ID.
- **Background Scraping**: Runs scraping tasks in the background using Python, allowing you to continue other tasks.
- **Incremental Extraction**: Only fetches and adds new videos, saving time and resources.
- **Real-time Status Tracking**: Monitor the progress of your scraping tasks (Scraping, Completed, or Error) directly from the dashboard.
- **JSON Storage**: All scraped data is stored in organized JSON files within your WordPress upload directory, making it easy to use for custom front-end displays or data analysis.
- **Premium Admin UI**: A clean, responsive dashboard integrated into the WordPress admin area.

## 🛠️ Technical Stack

- **Frontend**: WordPress Admin API, HTML5, CSS3, JavaScript (AJAX).
- **Backend Logic**: PHP (plugin framework & session management).
- **Scraper Engine**: Python 3.x with `yt-dlp` for robust data extraction.
- **Data Format**: JSON.

## 📋 Prerequisites

Before installing the plugin, ensure your server meets the following requirements:

1. **WordPress**: Version 5.0 or higher.
2. **Python**: Version 3.10 or higher installed on the server.
3. **yt-dlp**: The Python library `yt-dlp` must be installed (`pip install yt-dlp`).
4. **JWT Authentication**: The [JWT Authentication for WP REST API](https://wordpress.org/plugins/jwt-authentication-for-wp-rest-api/) plugin is recommended for full functionality.

## ⚙️ Installation & Setup

### 1. Plugin Installation
- Upload the `youtube-channel-scraper` folder to your `/wp-content/plugins/` directory.
- Activate the plugin through the 'Plugins' menu in WordPress.

### 2. Python Environment Setup
Install the required Python library on your server:
```bash
pip install yt-dlp
```

### 3. Configure Python Path
Out of the box, the plugin is configured for a specific Windows environment. **You must update the Python path** in `youtube-channel-scraper.php` to match your server's Python installation.

Open `youtube-channel-scraper.php` and locate line 100:
```php
$python = '"C:\\Path\\To\\Your\\python.exe"'; // Update this to your system python path
```
*On Linux/Ubuntu, it is typically `/usr/bin/python3`.*

## 📖 How to Use

1. Navigate to **YouTube Scraper** in your WordPress Admin Sidebar.
2. **Add a Channel**: Enter a human-readable name and the 24-character YouTube Channel ID (e.g., `UCFNjYL2tt8lqQwPRbFhfpGA`).
3. **Run Scraper**: Click "Scrape Now" on any saved channel.
4. **Monitor**: The status will update to "⏳ Scraping in progress...". Once finished, it will change to "✅ Scraping completed!".
5. **View Data**: The scraped JSON files are stored in `wp-content/uploads/youtube_scraper/`.

## 📁 File Structure

```text
youtube-channel-scraper/
├── admin/
│   └── dashboard.php      # Admin UI structure
├── css/
│   └── admin-style.css    # Styling for the dashboard
├── js/
│   └── scraper.js         # AJAX logic and UI interactions
├── scraper.py             # Core Python scraping script
├── youtube-channel-scraper.php # Main plugin entry point
└── README.md              # Documentation
```

## ⚠️ Important Notes

- **Channel ID**: Ensure you use the actual Channel ID (starting with `UC`), not the handle or username.
- **Permissions**: The plugin creates a directory in `wp-content/uploads/`. Ensure your web server has write permissions for this folder.
- **Shell Execution**: Your server must allow the PHP `shell_exec` and `popen` functions to run the Python script.

## 📄 License

This project is licensed under the MIT License.

---
*Developed with ❤️ by A.DEV*
