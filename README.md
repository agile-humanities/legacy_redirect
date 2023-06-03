# Legacy Redirect


This module redirects URLs from Islandora 7 to Islandora 2, assuming the site name stays the same.
ie `example.com/islandora/object/islandora:100` will resolve to  `example.com/node/17`, if
the node with a nid of 17 contains a reference field with the value of islandora:100

The module will redirect, and return a status of 302 to alert web crawlers the address has changed.
Users will be shown a message telling them to update bookmarks.

Site admins can configure the reference field, the message to be shown on redirect, as well a redirect on 404s.

## Configuration
You can set the following configuration at `/admin/config/islandora/legacy_redirect`:
- Pid reference field
  - The field as defined in your content type that holds the reference PID (defaults to `field_pid`)
- Redirect message
  - The message to be shown to users on redirect
- Page not found url.
  - The URL to redirect when no match can be found.

## Maintainers

Current maintainer:

* [Alan Stanley](https://github.com/ajstanley)
