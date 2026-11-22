# PRD: TabStack (Web-based TablePlus Clone)

## Problem

Developers need a lightweight, browser-based GUI to inspect and edit data in
MySQL/PostgreSQL/SQLite databases without installing a native client.

## In Scope (MVP)

- **Saved DB connections**: create/edit/delete connections for MySQL,
  PostgreSQL, SQLite. Credentials encrypted at rest. "Test Connection" action
  before saving.
- **Sidebar explorer**: tree view of databases/schemas/tables/columns for the
  active connection.
- **Unlimited browser tabs**: each tab is either (a) a paginated table data
  view, or (b) a raw SQL editor. Tabs persist per session.
- **Inline cell editing**: edit a cell in the table data view and commit the
  update directly to the row (via primary key).
- **Query execution timer**: show elapsed time for each SQL run.

## Out of Scope (for now)

- User roles / teams / multi-user permissions
- SSH tunneling
- ER diagrams
- CSV/SQL export
- Dark/light theme switcher

## Phases

### Phase 1 — Connections

- `database_connections` table (encrypted credential fields).
- CRUD UI for connections (Inertia pages).
- "Test Connection" endpoint: attempts a live connection, returns
  success/failure without saving.
- Tests: model casts encrypt/decrypt correctly; test-connection endpoint
  handles valid/invalid credentials.

### Phase 2 — Schema explorer

- Service layer to introspect schema (tables, columns, types) per driver.
- Sidebar component rendering the tree for the active connection.
- Endpoint returning schema tree as JSON.
- Tests: schema introspection returns expected structure for each driver.

### Phase 3 — Tabs: table data view & SQL editor

- Tab state (open tabs, active tab, tab type) persisted server-side per user
  session.
- Table data view: paginated SELECT with column headers, loaded via
  parameterized query builder.
- SQL editor tab: textarea + "Run" button, executes raw query via
  parameterized/bound execution, displays result grid and query execution
  timer.
- Tests: pagination correctness; SQL editor rejects unsafe execution paths;
  timer value present in response.

### Phase 4 — Inline cell editing

- Click-to-edit cell in table data view; PATCH endpoint updates one column on
  one row identified by primary key, using bound parameters.
- Optimistic UI update with rollback on failure.
- Tests: update endpoint only touches targeted row/column; rejects edits when
  no primary key is available; concurrent-edit conflict handled gracefully.
