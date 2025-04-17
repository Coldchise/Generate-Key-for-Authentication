This project provides a simple admin interface for generating and managing access keys for clients. These keys can be used for authentication and are valid for a period of 30 days. The system is designed with PHP and MySQL and includes both admin and client-facing pages.

## Features

Admin Panel: 
  - Admin can generate new keys, which are stored in a MySQL database with an expiration date of 30 days.
  - Admin can delete expired or unused keys from the database.
  - Admin can view a list of all generated keys, their expiration date, and current status (active or expired).
  
Client Access:
  - Clients enter their access key on a page (`index.php`).
  - The system verifies whether the key is valid, unexpired, and unused.
  - Once validated, the key is marked as used and the client is granted access.

## Programming Language

- PHP
- CSS
- MySQL/MariaDB
- A web server like Apache or Nginx
