# Hidden Posts

[![Support Level](https://img.shields.io/badge/support-active-green.svg)](#support-level)
[![CS & Lint](https://github.com/Automattic/hidden-posts/actions/workflows/cs-lint.yml/badge.svg)](https://github.com/Automattic/hidden-posts/actions/workflows/cs-lint.yml)
[![Run PHPUnit](https://github.com/Automattic/hidden-posts/actions/workflows/integrations.yml/badge.svg)](https://github.com/Automattic/hidden-posts/actions/workflows/integrations.yml)
[![GPLv2 License](https://img.shields.io/github/license/Automattic/hidden-posts.svg)](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
[![Compatible to WordPress version](https://plugintests.com/plugins/hidden-posts/wp-badge.svg)](https://plugintests.com/plugins/hidden-posts/latest)
[![Compatible to PHP version](https://plugintests.com/plugins/hidden-posts/php-badge.svg)](https://plugintests.com/plugins/hidden-posts/latest)
[![Downloads](https://img.shields.io/wordpress/plugin/dt/hidden-posts.svg)](https://wordpress.org/plugins/hidden-posts/)
[![Plugin Version](https://img.shields.io/wordpress/plugin/v/hidden-posts.svg)](https://wordpress.org/plugins/hidden-posts/)

**Contributors:** automattic, betzster, batmoo  
**Tags:** posts  
**Requires at least:** 3.8  
**Tested up to:** 3.8  
**Stable tag:** 0.1  
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

The opposite of sticky posts.

## Description

Hide up to 100 specified posts from the homepage of your site.

If you'd like to check out the code and contribute, [join us on GitHub](https://github.com/Automattic/hidden-posts). Pull requests, issues, and plugin recommendations are more than welcome!

## Installation

1. Upload the `hidden-posts` folder to your plugins directory (e.g. `/wp-content/plugins/`)
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Hide posts from the post edit screen

## Usage

Once the plugin is activated, a new "Hide Post" checkbox will appear on the post editing screen. When the checkbox is selected and the post is saved, the post's ID is added to a list of posts to hide. This list is stored in the site's options table.

Hidden posts are only excluded from the main query run on the site's homepage, and only for logged-out users. Single post displays and other queries are not affected.

## Frequently Asked Questions

### Why are there no FAQs besides this one?

Because you haven't asked one yet.

## Changelog

### 0.1
* Initial Release
