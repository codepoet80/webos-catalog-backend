# webos-catalog-backend
PHP Back-end for webOS App Catalog restoration project

For now, Apache needs to be configured not to be case-sensitive:
https://keystoneit.wordpress.com/2007/02/19/making-apache-case-insensitive/

You'll also need mb_internal_encoding:
https://stackoverflow.com/questions/1216274/unable-to-call-the-built-in-mb-internal-encoding-method

Use the WebService/config.php to point to the subdomains that provide the requisite parts (metadata and app packages)
