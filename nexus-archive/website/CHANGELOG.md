# Version 1.0.0

- create interfaces and classes to represent leaderboard table as seen in game
- create Doctrine entities to persist these leaderboard tables in database
- implement general parser infrastructure to handle stored page views
- implement parser for Breath 4 final leaderboards
- implement a public page for browsing leaderboards
- create and apply basic UI theme/styling, based on Tailwind CSS
- some more internal code cleanups

# Version 0.5.0

- fix crash in token command on empty username input
- create basic admin panel
- convert all config files to PHP format
- general code cleanup and refactoring
- update installed dependencies to newer versions
- update Symfony recipes metadata, port changes to appropriate files
- start using Doctrine Migrations: create initial migration for existing tables

# Version 0.4.0

- remove unnecessary properties from NexusRawData

# Version 0.3.0

- remove NexusRequestLog entity and all related code (form, repository, etc)

# Version 0.2.0

- create new form to handle data format expected from browser extension

# Version 0.1.1

- fix parsing of JSON fields at cost of ignoring JSON syntax errors

# Version 0.1.0

- basic submit form to store request logs for further processing
- primitive authentication via access tokens
- simple console commands to create user accounts and access tokens
