# Changelog

This changelog documents only the two most recent published GitHub releases. Each entry is based on the changes between its release tag and the immediately preceding published release. Changes made later on `master` are excluded.

## 1.0.2 - 2026-10-15

### Changed

- Updated the GitHub Actions CI database service from PostgreSQL 16 to PostgreSQL 17.
- Updated the MariaDB CI service from version 10 to 11.4, including its initialization environment variables and health-check configuration.
- Added the plugin icon image at `pix/icon.png` and reorganized the SVG logo asset as `pix/monologo.svg`.
- Updated the plugin release identifier from `1.0.1` to `1.0.2` and its build number from `2026101200` to `2026101500`.

No functional changes to the mastery-practice activity were identified between the published `1.0.1` and `1.0.2` tags.

[Compare 1.0.1...1.0.2](https://github.com/EduardoKrausME/moodle-mod_masterypractice/compare/1.0.1...1.0.2)

## 1.0.1 - 2026-10-12

### Changed

- Updated the PHP 8.3 CI matrix entry from `MOODLE_502_STABLE` (Moodle 5.2) to `MOODLE_503_STABLE` (Moodle 5.3); the Moodle 4.5 CI entry was retained.
- Hardened the existing GitHub release workflow against shell injection by passing the commit message through an environment variable instead of interpolating the message directly into shell commands.
- Updated the plugin release identifier from `1.0.0` to `1.0.1` and its build number from `2026100600` to `2026101200`.

No functional changes to the mastery-practice activity were identified between the published `1.0.0` and `1.0.1` tags.

[Compare 1.0.0...1.0.1](https://github.com/EduardoKrausME/moodle-mod_masterypractice/compare/1.0.0...1.0.1)
