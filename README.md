bitcoin-tips
============

Another fork of the WordPress Bitcoin Tips plugin. See http://terk.co/wordpress-bitcoin-tips-plugin/

## Features added by this fork

### Improved Privacy

The plugin does not need the google charts API any more. It uses a PHP library, which is included in the `lib` folder.
It needs the gd PHP library.

This enhances the privacy of website visitors.

### [bitcointips] Shortcode

The `[bitcointips]` shortcode can be used on any post or page to display the tip box. The shortcode supports one shortcode attribute called `output`:

 - `[bitcointips output="address"]` will show unique Bitcoin tip address for the current post
 - `[bitcointips output="qrcode"]` will show unique Bitcoin QR code for the current post
 - `[bitcointips output="stats"]` will show tip stats for the current post

### 'Show Tip Box' option

There is a new checkbox in `Settings -> Bitcoin Tips` called `Show Tip Box`. When enabled (the default), the tip box is automaticlly inserted into the bottom of all posts.

With this new option, you can disable this behavior. This allows you to design your own tip box or manually insert the tip box into certain posts using the shortcode.

### Thank you

If you like this plugin, please donate to the original plugin author: `1EDKfULtvuSpHGLSg7eZM38G24v4NNR3va`

If you like raamdev's contributions, you can donate to: `1DoiUUnCYhK8uuQzK6YvSfrkVSotEKzm46`

If you like my contributions, you can donate to me here: `1Fya8UEzYMVGkv1S2j9bGUcqAHCTmHaxn7`

## Changelog

### 2013-12-15

- Added a qr-code library, to run independently from google's API. This enhances the privacy of website visitors.

### 2013-06-19

- Added `[bitcointips]` shortcode
- Added `Show Tip Box` settings option to enable/disable automatic insertion of tip box widget
- Replaced `file_get_contents()` function with cURL, which is better supported (some web hosts disable `file_get_contents()` for security reasons)
