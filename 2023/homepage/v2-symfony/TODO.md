# TODO

- create admin pages to manage pages
  - remember to recalculate path when parent changes
    - dispatch "document updated" event and update path in event handler
- configure authentication for admin panel
- create console commands to manage users
- create admin pages to manage users
- create admin page to manage basic website settings (title etc)
- create admin page to manage theme settings (color palette etc)
- create basic website layout (HTML + Tailwind CSS + settings from DB)
- create admin pages to manage layout fragments (menu, header, footer, etc)
- create basic set of page templates (article, news, contact, etc)
- configure admin to use fancy HTML editor for page content

# entities and contracts

- namespace `Symfony`:
  - `RouteNames` (for controller routes)
- namespace `Entity`:
  - interface `TimestampableInterface`:
    - properties `createdAt`, `updatedAt`, `deletedAt`
  - interface `RouteInterface` (represents URL path):
    - properties: `slug`, `parent`, `combinedPath`
  - interface `PageInterface` (represents generic page):
    - properties: `title`, `contentType`, `content`
  - interface `FileInterface` (represents downloadable file):
    - properties: `name`, `contentType`, `storagePath`
  - interface `PointerInterface` (represents redirection to a different route):
    - properties: `target`
