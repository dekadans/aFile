# aFile

This is an application that offers simple cloud storage-like possibilities for your own web server. It's mostly just something I work on in my free time, but it's usable should anyone want to try it out.

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
* _(Optional)_ Run `php bin/afile.php key [username] > key.txt` and save the resulting file somewhere safe and physically separate from the installation. If you forget your password you can then change it by running `php bin/afile.php password [username] --keypath=key.txt`
* _(Optional)_ In `config/config.ini` you can set some additional settings, as well as change what you entered during installation.

#### The encryption, explained

Each user in the installation has an encryption key associated with it.
This key is saved in the database, in turn encrypted with the user's password (which is only saved hashed).
When the user signs in with their password, the encryption key is decrypted and saved in a cookie on the client for the duration of the session.
Because the key is stored in a cookie and sent with every request, aFile should only be used with HTTPS.
