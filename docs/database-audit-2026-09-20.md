# Database audit — 20 September 2026

Reviewed the Eloquent models, Livewire components and Blade query paths, authentication/setup queries, scheduled commands, migrations, and SQLite configuration. Implemented the findings below and verified them against isolated SQLite databases. The live database was inspected read-only; the new index migration has **not** been applied to it.

## Findings and changes

| Area | Finding | Change |
| --- | --- | --- |
| Economy and savings | Calling computed methods directly bypassed Livewire's per-request property cache and repeatedly loaded entire collections. A representative render read incomes three times. | Read computed properties instead. Regression tests verify exactly one read each for incomes, expenses, savings, and users per economy render. |
| Child history | Completed chores and redemptions lazily loaded the same user for every row. Increasing each history from one to ten entries increased user queries from 3 to 21. | Attach the already authenticated child as the user relation. User queries remain constant as history grows. Parent eager loading is retained. |
| Admin users | Repeated `isMaster()` calls queried settings and the first user for individual rows and buttons. | Resolve master-user display flags once from the loaded collection and one setting read. Action authorization still uses the existing model checks. Tests cover case-insensitive configured email and a nonexistent configured master. |
| Dashboard | Queries ran for disabled modules; four separate queries fetched module flags; recent completion metrics scanned each task table twice. | Skip disabled modules, fetch module settings together, and aggregate related counters in one query. Fetch assignment templates only while their modal is open. |
| Productivity chart | Downloaded all completed timestamps, created Carbon objects, and filtered each collection eight times. | Aggregate eight weekly counters in SQL; only one aggregate row per enabled task type reaches PHP. Indexed timestamp ranges bound the work. |
| Monthly child statistics | Fallback accessors used month/year functions on timestamps. | Use start-inclusive, next-month-exclusive timestamp comparisons in accessors and aggregate scopes. Tests cover the December/January boundary. |
| Snapshot history | Both history screens loaded every snapshot's full JSON document; initial selection also loaded a complete snapshot just to obtain its ID. | Lists and charts select summary columns only. Load the full JSON document for the selected snapshot only; select the initial ID directly. All captures remain available in the charts. |
| Snapshot capture | Queried four extra sums after loading the same financial rows; one sum was unused. | Calculate totals from the captured rows. Five source-table reads replace nine. Existing transaction and one-time-expense cleanup remain intact. |
| Recurring chores | Per-child user and duplicate checks, date-function filtering, repeated template updates, and an unbounded template collection. | Read eligible child IDs once, check generated children once per due template using a timestamp range, stream templates in batches of 100, and update each template once in a transaction. Sequential reruns stay idempotent and approval requirements are preserved. |
| Reordering | Economy/savings fetched every record separately; many reorder operations committed individual writes. Shopping auto-sort also fetched an unused relationship. | Fetch records together for audited reorders, skip unchanged audited positions, and group writes in transactions. Remove the unused relationship fetch. Retain existing audit events and list scoping. |
| Setup and Google login | Counted users/admins solely to test existence; Google login also made a logically redundant count. | Use existence queries and remove the redundant check without changing first-user behavior. |
| Settings | Single-key reads hydrated entire models. | Select the value column directly. Add uncached `getMany()` for dashboard/sidebar flags; preserve defaults, encrypted secrets, and fresh reads following updates. |

Computed-property access follows [Livewire's request-cache semantics](https://livewire.laravel.com/docs/4.x/computed-properties). Index column ordering follows the actual filter and ordering combinations, checked with `EXPLAIN QUERY PLAN`; see [SQLite's query planner documentation](https://www.sqlite.org/queryplanner.html).

## Index migration

`database/migrations/2026_09_20_161100_add_query_indexes.php` adds 20 non-unique indexes and has a matching rollback. It does not rewrite rows, remove tables, or enforce new uniqueness constraints.

| Table | Indexed columns | Query supported |
| --- | --- | --- |
| audit_logs | created_at, id | Latest audit page with deterministic ordering |
| shopping_items | shopping_list_id, sort_order | Active list rows and next sort position |
| shopping_items | is_checked, shopping_list_id | Dashboard count and checked-item cleanup |
| todo_items | todo_id, sort_order | Ordered tasks and parent relationship |
| todo_items | is_done, due_date | Pending/overdue counts |
| todo_items | is_done, completed_at | Completion windows |
| todo_items | created_at, is_done | Recent-task completion aggregate |
| todo_item_tag | todo_tag_id, todo_item_id | Reverse tag lookup and tag deletion; the existing primary key covers the other direction |
| chores | user_id, is_completed, completed_at | Per-child monthly points/counts |
| chores | user_id, is_completed, created_at | Child active/completed history |
| chores | is_completed, completed_at | Household productivity |
| chores | is_completed, created_at | Parent active/completed history |
| chores | created_at, is_completed | Recent-task completion aggregate |
| chores | source_template_id, user_id, created_at | Recurring-generation checks and template relationship |
| chores | is_pending_approval | Parent approval queue |
| redemptions | user_id, created_at | Child redemption history and user relationship |
| redemptions | created_at | Parent redemption history |
| economy_snapshots | year, month, id | Monthly listing and scheduled existence check |
| savings_snapshots | created_at, id | Savings history listing |
| savings_snapshots | year, month | Scheduled existence check |

The different chore indexes serve distinct access patterns: a timestamp range after a user/status prefix cannot also efficiently serve a global creation-date range or a different timestamp ordering. All 20 representative plans use the intended indexes, with no temporary B-tree sort.

Indexes consume disk space and add maintenance work to inserts, updates, and deletes. They are concentrated on growing task/history tables. Small household finance tables remain unindexed for their full-table sums and displays: those queries intentionally read all rows, and adding an index to every column would not address that work. Existing primary/unique indexes for user IDs, email, settings keys, and tag names were retained.

The older `.agent/project_context.md` suggests rebuilding databases for schema changes. This audit uses an additive migration so existing installations can obtain the indexes without losing household data.

## Measurements

Initial read-only inspection of `database/database.sqlite` found approximately 0.97 MiB, four users, 1,791 audit rows, five tasks, and six snapshots of each type. Chores and shopping items were empty. Performance at that size is not a useful substitute for testing future growth, so a separate synthetic benchmark was used.

`php tests/Performance/database-indexes.php` creates an in-memory SQLite database with 50,000 chores, 50,000 audit rows, and 100 users. It runs the same queries before/after adding the indexes, checks that their results match, and reports median wall time over 15 executions. The selected child-history query includes an ID tie-breaker to make result comparison deterministic. No real household records are copied into this benchmark.

| Query | Without new indexes | With new indexes |
| --- | ---: | ---: |
| Latest audit page | 11.1336 ms | 0.0908 ms |
| Child completed history | 4.3721 ms | 0.0901 ms |
| Monthly child points | 4.2530 ms | 0.0524 ms |
| Recurring duplicate lookup | 4.4649 ms | 0.0522 ms |
| Completion-window count | 6.2177 ms | 0.2987 ms |

These are local synthetic read measurements, not production latency guarantees or a measurement of write overhead. Full results and query plans are in [database-index-benchmark-2026-09-20.json](database-index-benchmark-2026-09-20.json).

## Validation

- Baseline: 95 tests, 373 assertions passed.
- After changes: **108 tests, 490 assertions passed** using `php artisan test --compact`.
- Added 13 regression tests for query counts, history payload selection, disabled modules, date boundaries, fresh/encrypted settings, snapshot totals, recurring-generation batching/idempotency, master-user rendering, reorder auditing, index selection, and reversible migration without row changes.
- PHP formatting checked with Pint on the changed PHP files.
- Query-plan tests and synthetic performance measurements use SQLite. MySQL/PostgreSQL execution plans were not tested.

## Remaining considerations

1. **SQLite journaling:** read-only inspection reported `DELETE`, while project notes claim WAL. The connection configuration leaves journal mode, busy timeout, and synchronous settings unset. Evaluate WAL for the actual deployment filesystem and measure concurrent requests before changing these operational settings. This audit did not change live PRAGMAs or durability settings.
2. **Unbounded UI collections:** completed todo items, active shopping items, open chores, snapshot summary lists/charts, and admin users still load all matching rows. The largest snapshot JSON amplification is fixed, but very large histories will need pagination, archiving, or a chart-window decision that preserves navigation and drag-and-drop semantics.
3. **Audit pagination:** the new ordering index removes the large sort. Numbered pagination still counts all rows and uses offsets for deep pages. Cursor pagination or an archive/retention policy would require changing the current page-number UI or deciding which records to retain.
4. **Concurrent recurring generation:** batching reduces reads and makes each template's writes transactional. It does not provide a database uniqueness guarantee across simultaneous command/page-load invocations. The command still runs on Kids page mount as well as the schedule. A dedicated generation-day key plus a unique constraint, or coordinated locking, remains a separate correctness improvement; existing duplicates and cross-database behavior need explicit handling.
5. **Reorder write count:** transactions reduce commits, and audited paths skip unchanged positions, but reorders still perform individual updates. Large lists could benefit from chunked CASE updates; they must preserve list scoping and the existing audit contract.
6. **Small-table queries:** Google sign-in still uses `LOWER(email)` to accommodate legacy email casing; tiny household user counts do not justify an engine-specific expression index. Finance sort-order and foreign-key indexes likewise have limited benefit at the inspected row counts. JSON payer/assignee arrays are processed as small household collections; normalization would be appropriate if membership became a large SQL-filtered workload.
7. **Old migrations:** historical data conversions still contain per-row work. They were left immutable; the new index migration can be deployed independently after existing migrations. Large legacy imports should be rehearsed separately.

Deploy the index migration through the normal application migration step (`php artisan migrate --force` in production), after the usual backup. No `migrate:fresh` is needed. Index creation takes a SQLite write lock, so schedule that step around active use if the database has grown substantially. Application query improvements do not require a data conversion.
