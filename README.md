# CacheWiper Pro

**CacheWiper Pro** is a powerful WordPress plugin that allows you to clear multiple layers of cache with a single click or using a specific route. Designed for developers and site administrators who need a fast and efficient solution to manage caches.

---

## Key Features

- Complete cleanup of **WP Rocket** cache.
- Object cache cleanup (**Memcached**, **Redis**, etc.).
- Server cache cleanup (**Varnish**, **Nginx**, etc.).
- **CDN** cache cleanup (Cloudflare, StackPath, etc.).
- Forces browser cache cleanup.
- Secure endpoint for integration with **cron jobs** or automation tools.

---

## Installation

1. Download the plugin from [GitHub](https://github.com/yourusername/plugin-name).
2. Extract the ZIP file and upload the folder to the `/wp-content/plugins/` directory of your WordPress site.
3. Activate the plugin in the WordPress admin panel.
4. Configure the plugin options in **CacheWiper Pro**.

---

## How to Use

### Manual Cleanup

1. Access the WordPress admin panel.
2. Navigate to **CacheWiper Pro** in the sidebar menu.
3. Click **CacheWiper Pro**.

### Automated Cleanup (via Endpoint)

Use the following endpoint to integrate with cron jobs or automation tools:

```
http://yoursite.com/?clear_total_cache=true&password=YOUR_PASSWORD
```

---

## Security

- The cache cleanup endpoint is protected by a configurable password.
- Make sure to use a strong password and keep it secure.

---

## Settings

- **Security Password**: Protect the cache cleanup endpoint.
- **CDN Zone ID, API Key, and Email**: Configure to clear CDN caches like Cloudflare.
- **Server Command**: Insert custom commands to clear server caches like Varnish or Nginx.

---

## License

This plugin is licensed under **GPLv2**. Feel free to use, modify, and distribute.

---

## Contributions

Contributions are welcome! Follow the steps below:

1. Fork the repository.
2. Create a branch for your feature (`git checkout -b feature/new-feature`).
3. Commit your changes (`git commit -m 'Add new feature'`).
4. Push to the branch (`git push origin feature/new-feature`).
5. Open a Pull Request.

---

## Contact

If you have any questions or feedback, feel free to contact me at [pedromarquesnoot@oulook.com](mailto:pedromarquesnoot@oulook.com).

### Version 1.0

- Initial release of the plugin.
