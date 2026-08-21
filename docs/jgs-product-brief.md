# Jones General Store Website and Management System

**Application:** `jonesgeneralstore.ca`  
**Framework:** Waaseyaa `v0.1.0-alpha.296`  
**Local runtime:** PHP 8.5 + SQLite  
**Deployment target:** Raspberry Pi, after local acceptance

## Product shape

One Waaseyaa application serves two distinct surfaces:

1. a public Jones General Store website; and
2. an authenticated company-management surface for JGS projects and records.

Public content and private operating data may share entity infrastructure, but
private data must never be exposed through public routes, generic listings, or
the public API.

## Public baseline

Current facts confirmed from the JGS Facebook page on August 20, 2026:

- store name: Jones General Store - Sagamok
- address: 4027 Espaniel Street, Sagamok Anishnawbek
- phone: (705) 865-1633
- hours: 9 AM–10 PM daily
- gas station: full service and follows store hours
- public assortment: groceries, convenience items, tobacco products, women's
  clothing, gifts, wrapping supplies, cards, cold drinks, and snacks
- public updates and current fuel prices are posted to Facebook

The Facebook page lists postal code `P0P 1P0`, while project documents use
`P0P 2L0`. The website omits the postal code until the correct value is
confirmed.

## Company management baseline

Jones General Store is the company. Its work is organized into projects rather
than one large task list.

Initial projects:

- Gas Station Operations and Completion
- Store Expansion and Storage
- Electronic Inventory and POS Modernization
- Ontario Lottery Application
- Site Cleanup and Property Plan
- Quick Wins
- Candle Line

The August 20 conversation with Charlotte adds these current discovery items:

- Witztech POS was considered; the quote was expensive and a promised demo link
  or meeting was never delivered.
- A small storage addition is being planned for pop, chips, chocolate, and other
  back stock.
- Charlotte does not maintain a fixed upgrade list yet; new needs are identified
  as store work continues. The dashboard therefore needs a low-friction inbox.

## Roles

- Marilyn Jones — owner and final approver
- Charlotte Southwind — store manager
- Russell Jones — modernization and expansion coordinator

## First management release

- company home showing priorities, decisions, blockers, and recent updates
- project portfolio
- project workspace with milestones, tasks, risks, decisions, records, and
  activity history
- role-focused work queues
- capture inbox for ideas, issues, documents, and requests
- restricted record metadata for receipts, Z-tapes, invoices, permits, and
  sensitive documents

Stale task state from the previous Vue dashboard must not be imported as current
truth. Old files remain evidence and can be linked after review.

## Deployment boundary

Local development uses SQLite and the single-process Waaseyaa server for basic
testing. Raspberry Pi deployment will use the committed FrankenPHP/Caddy setup,
HTTPS, authenticated admin access, backups, and explicit environment secrets.
No public deployment occurs until the local public pages and private access
boundaries are reviewed.

## Alpha.296 local-platform notes

- `site:init` currently assumes POSIX `fsync()` and path semantics. A temporary
  vendor-only Windows compatibility guard was required for initialization.
  Linux on the Raspberry Pi does not require it. Reapply or upstream the fix if
  the governed site contract must be regenerated on Windows after a Composer
  reinstall.
- The generated `SiteGoldenPathTest` checks the POSIX executable bit on
  `bin/maintenance/site-verify`; PHP reports that check as false on Windows.
  The script, architecture tests, recipe tests, and strict site doctor run
  successfully. The full generated gate must pass on Linux before deployment.
