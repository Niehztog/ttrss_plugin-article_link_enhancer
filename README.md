Enhanced processing of article links in Tiny Tiny RSS (Plugin)
==============================================================

[Tiny Tiny RSS](http://www.tt-rss.org) plugin
Features:
 * resolve HTTP redirects in article links
 * extract Tags from article links by regular expressions
 * prefix extracted tags
 * remove all other existing tags

## How does it work?
Before storing new article contents in the database, this plugin issues an HTTP call to the article's link location, resolving all HTTP redirects. In case it is successfull, stores the last redirect location as the new articles link.
Furthermore can be configured to extract and add article tags from the link (either the original one or the resolved one).

## Installation

 * Unpack the [zip-File](https://github.com/Niehztog/ttrss_plugin-article_link_enhancer/archive/master.zip)
 * Move the folder `"article_link_enhancer"` to your plugins directory
 * Enable the `article_link_enhancer` plugin in the Tiny Tiny RSS Preferences and reload.
 * In order to activate and customize the plugin for a feed, right click the Feed, select `"Edit Feed"` and then `"Plugins"`. Here you can choose to resolve the redirects, enter a regular expression for tag extraction (don't forget to have a capture group in there, for example `/{1}([a-z]+)/{1}`) and set a prefix.
 
**Caution:** *Enabling redirect resolving slightly increases feeds update times.*````

Please report any problems you might encounter using github's [issue tracker](https://github.com/Niehztog/ttrss_plugin-article_link_enhancer/issues).

## Legal

Copyright Niehztog

>    This program is free software: you can redistribute it and/or modify
>    it under the terms of the GNU General Public License as published by
>    the Free Software Foundation, either version 3 of the License, or
>    (at your option) any later version.
>
>    This program is distributed in the hope that it will be useful,
>    but WITHOUT ANY WARRANTY; without even the implied warranty of
>    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
>    GNU General Public License for more details.
>
>    You should have received a copy of the GNU General Public License
>    along with this program.  If not, see <http://www.gnu.org/licenses/>.
