# aFile

This is an application that offers simple cloud storage-like possibilities for your own web server. It's mostly just something I work on in my free time.

![Screenshot](https://f.tthe.se/dl/lmv38/816bd9f9d02636318335ba116dc43b49b7ceba8e "Screen shot of the main view")

#### Features

* Upload and structure files into directories
* Encryption of all files uploaded
* Sharing of files with optional password protection
* Advanced search functionality
* Color formatting of code for popular programming languages
* Create, edit and view plain text and Markdown files
* View images as a gallery
* Save URLs as "bookmark files"

#### Installation

_Coming soon_

#### The encryption, explained

Each user in the installation has an encryption key associated with it.
This key is saved in the database, in turn encrypted with the user's password (which is only saved hashed).
When the user signs in with their password, the encryption key is decrypted and saved in a cookie on the client for the duration of the session.
Because the key is stored in a cookie and sent with every request, aFile should only be used with HTTPS.