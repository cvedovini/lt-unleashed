=== LT Unleashed ===
Author: Claude Vedovini
Contributors: cvedovini
Donate link: http://paypal.me/vdvn
Tags: widget,shortcode,librarything,book
Requires at least: 3.0
Tested up to: 4.9
Stable tag: 1.1.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html


== Description ==

That plugin offers shortcodes and widgets so you can display any public
LibraryThing books collection in WordPress.


**[books username="username"]**

Displays the books of the given LibraryThing user. This shortcode only uses the
LibraryThing API and will work even if you don't provide an AWS key pair.

Optional attributes are:

- `count`: Maximum number of books to show (default 20)
- `sort_by`: Sort order, either `entry_REV` or `random`
- `template`: Template to use to render the list of books. Either `cover` or `list`
- `tags`: Comma separated list of tags


**[book isbn="isbn"]**

Displays the book with the given ISBN number. This shortcode uses the Amazon
Product Advertising API and will not work if you don't provide an AWS key pair.
Also, not all ISBN can be found on all Amazon stores so you will have to carefully
choose which store to use in the settings.

Optional attributes are:

- `template`: Template to use to render the list of books. Only `book-single`


**Customization**

You can add new template or customize the existing ones by adding your own
version in a folder named `librarything` in the `wp-content` folder on your
server or in your theme folder. The name of the template file id the name of
the template with the `.php` extension (e.g: the `covers` template file name
is `covers.php`).


**Disclaimer**

This plugin is not endorsed by LibraryThing, it has not been developed by
the LibraryThing team and its developer is not related in any manner to the
LibraryThing team.

Image Credits: [University of Scranton Weinberg Memorial Library (cropped)](https://www.flickr.com/photos/universityofscrantonlibrary/3479643071/)<br>
License: [Creative Commons Attribution 2.0 Generic](https://creativecommons.org/licenses/by/2.0/)

== Installation ==

This plugin follows the [standard WordPress installation
method](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins):

1. Upload the `librarything-unleashed` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. You can now use the widget and the `[books]` shortcode

Optionally, if you want to show book details in single book pages or use
the `[book]` shortcode, you will need the following:

1. Go to the [Amazon AWS security credentails management page](https://console.aws.amazon.com/iam/home?rw_useCurrentProtocol=1#security_credential)
and create a new key/secret pair
1. Enter the pair on the plugin's options page
1. Choose or create a new page to support displaying single books
1. Save the options
1. You can now use the `[book]` shortcode and are hosting single book pages
instead of sending visitors to LibraryThing

Also, if you have your own LibraryThing API key you can use it by adding the
following line in your `wp-config.php` file:

`
define('LIBRARYTHING_API_KEY', 'your key here');
`

This is optional, by default the plugin does not use any key. To find your
LibraryThing API key check [this page](http://www.librarything.com/services/keys.php).


== Changelog ==

= Version 1.1.1 =
- Fixing unclosed <div> tag in signature

= Version 1.1 =
- Similarities on a single book page are now linked locally instead of going 
directly to Amazon

= Version 1.0.5 =
- Protect from AWS API returning inconsistent data structures 

= Version 1.0.4 =
- Fixing a bug that makes some books look like they were not found by the AWS API 

= Version 1.0.3 =
- Fixing book base in settings being partially taken into consideration
- Automatically converting cover sources to their HTTPS version when WordPress uses HTTPS

= Version 1.0.2 =
- Fixing a bug that prevented the book shortcode to work in certain circumstances.
- Changed the book-single template to use a table and display multiple authors.

= Version 1.0.1 =
- Preparing for language packs

= Version 1.0 =
- Initial release.


== Screenshots ==

1. Output of the `[books]` shortcode with the `covers` template (widget in the right sidebar)
2. Output of the `[books]` shortcode with the `list` template
3. Example of a book with a review when using the `list`template
4. Single book page. Also, output of the `[book]` shortcode with the `book-single` template


== Privacy Policy ==

This plugin does not collect any personal information from your visitors.
