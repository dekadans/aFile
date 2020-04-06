# aFile

This is an application that offers simple cloud storage-like possibilities for your own web server. It's mostly just something I work on in my free time, but it's usable should anyone want to try it out.

I'm currently working on the next version, and this is no longer updated.

![Screenshot](https://f.tthe.se/dl/lmv38/816bd9f9d02636318335ba116dc43b49b7ceba8e "Screen shot of the main view")

#### Features

* Upload and structure files into directories
* Encryption of all files uploaded
* Sharing of files with optional password protection
* Advanced search functionality
* Create, edit and view plain text and Markdown files
* Color formatting of code for popular programming languages (as separate source files or part of Markdown)
* View images as a gallery
* Save URLs to websites as files

#### Installation

* Clone or download the code to your web server
* Set up a virtual host or similar to point to the webroot directory
* Run `install.sh` in a terminal, shell or Git Bash (on Windows)
* Run `php bin/afile.php add-user [your desired username]`
* Ready to use!
* _(Optional)_ In `config/config.ini` you can set some additional settings, as well as change what you entered during installation.
