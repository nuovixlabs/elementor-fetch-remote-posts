# Elementor Fetch Remote Posts

Elementor Fetch Remote Posts is a WordPress plugin that allows you to fetch and display posts from external WordPress sites using Elementor templates.

## Description

This plugin adds a new Elementor widget that enables you to:

- Fetch posts from any WordPress site with a public REST API
- Display these posts using custom Elementor templates
- Filter posts by category
- Set the number of posts to display

## Installation

1. Upload the `elementor-fetch-remote-posts` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Use the 'Remote Posts' widget in your Elementor editor

## Usage

1. Create a new Elementor template for how you want to display each remote post
2. Edit a page with Elementor
3. Find the 'Remote Posts' widget and drag it into your layout
4. In the widget settings, enter the URL of the remote WordPress site
5. Set the number of posts you want to display
6. Optionally, enter a category slug to filter posts
7. Select the Elementor template you created for displaying the posts
8. Save and publish your page

## Translations

Elementor Fetch Remote Posts is translation-ready. Here's how you can contribute a translation:

1. Download [Poedit](https://poedit.net/), a free software for creating and editing translations.
2. Open the `elementor-fetch-remote-posts.pot` file from the `languages` folder in Poedit.
3. Create a new translation and start translating the strings.
4. Save your translation file in the `languages` folder with the naming convention `elementor-fetch-remote-posts-{LOCALE}.po` (for example, `elementor-fetch-remote-posts-fr_FR.po` for French).
5. Poedit will automatically generate the corresponding .mo file.

If you create a translation, please consider sharing it with the community by submitting a pull request or contacting the plugin author.

Currently available translations:
- English (default)
- [Add your translations here as they become available]

We appreciate any contribution to translate the plugin into more languages!

## Frequently Asked Questions

**Q: Does this plugin work with any WordPress site?**
A: The remote site must have the WordPress REST API enabled and publicly accessible.

**Q: How often are the remote posts updated?**
A: The posts are fetched every time the page is loaded. A daily cleanup is scheduled to remove old fetched posts from your database.

## Changelog

### 1.0.1
* Initial release
* Translation ready

## License

This project is licensed under the GNU General Public License v2.0 or later - see the [LICENSE](LICENSE) file for details.

## Author

Rakesh Mandal - [https://rakeshmandal.com](https://rakeshmandal.com)