# Changelog

All notable changes to this project will be documented in this file.

## [v2026.02.28] - 2026-02-28
### Changed
- Sync from production commit `a9450b93`.
- Migrations: removed unique constraints on `clientes` (`email`, `contato`, `bi`). Migration files made compatible with SQLite for tests.
- Tests: fixed migrations causing failures on in-memory SQLite; test suite now passes locally.

### Notes
- Branch: `production-sync` (created from production state).
- See PR/branch on GitHub: `production-sync`.
