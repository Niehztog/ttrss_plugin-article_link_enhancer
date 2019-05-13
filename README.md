Auto resolve HTTP redirects in feed links (TinyTiny RSS Plugin)
======================

[Tiny Tiny RSS](http://www.tt-rss.org) plugin to **resolve HTTP redirects in article links**.

## How does it work?
Before storing new article contents in the database, this plugin issues an HTTP call to the article's link location, resolving all HTTP redirects. In case it is successfull, stores the last redirect location as the new articles link.

## Installation

 * Unpack the [zip-File](https://github.com/Niehztog/ttrss_plugin-resolve_redirects/archive/master.zip)
 * Move the folder "resolve_redirects" to your plugins directory
 * Enable "resolve_redirects" in TT-RSS's `config.php` file

Please report any problems you might encounter using github's [issue tracker](https://github.com/Niehztog/ttrss_plugin-resolve_redirects/issues).

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
