# 06. Database Rules
## INAKARA CRM — Permanent Database Standards

**Status:** Binding — Subordinate to `PROJECT_CONSTITUTION.md`, `01-product-rules.md`, `02-design-principles.md`, `03-design-system.md`, `frontend-architecture.md`, `backend-architecture.md`
**Version:** 1.0.0
**Stack:** Laravel 13, PHP 8.3+, MySQL 8+
**Scope:** This document defines database standards and decision rules only. It contains no SQL, no table definitions, no migrations, and no ERD.

---

## Table of Contents

1. [Database Philosophy](#1-database-philosophy)
2. [Naming Convention](#2-naming-convention)
3. [Table Standards](#3-table-standards)
4. [Primary Key Rules](#4-primary-key-rules)
5. [Foreign Key Rules](#5-foreign-key-rules)
6. [Relationship Standards](#6-relationship-standards)
7. [Normalization Rules](#7-normalization-rules)
8. [Indexing Standards](#8-indexing-standards)
9. [Data Integrity](#9-data-integrity)
10. [Audit Strategy](#10-audit-strategy)
11. [Soft Delete Strategy](#11-soft-delete-strategy)
12. [Status Strategy](#12-status-strategy)
13. [Master Data Strategy](#13-master-data-strategy)
14. [Transaction Data Strategy](#14-transaction-data-strategy)
15. [Multi-Company Strategy](#15-multi-company-strategy)
16. [Performance Strategy](#16-performance-strategy)
17. [Security Strategy](#17-security-strategy)
18. [Import & Export Strategy](#18-import--export-strategy)
19. [Backup & Recovery](#19-backup--recovery)
20. [Future Scalability](#20-future-scalability)
21. [Anti-Patterns](#21-anti-patterns)
22. [Best Practices](#22-best-practices)
23. [Decision Guidelines](#23-decision-guidelines)
24. [Glossary](#24-glossary)
25. [References](#25-references)

---

## 1. Database Philosophy

**Why the database must be scalable.** INAKARA CRM's data volume grows on three axes simultaneously: transaction volume (more Leads, Deals, Invoices over time), organizational scale (more branches, warehouses, companies), and industry breadth (new verticals beyond furniture). The schema must be designed so growth on any axis is absorbed by adding rows and, occasionally, new tables — never by redesigning existing ones.

**Why consistency is important.** A CRM is only trustworthy if its data can be reconciled — a Deal's value must always match its Quotation, an Invoice's total must always be derivable from its line items. Structural consistency (naming, typing, relationship patterns) is what allows this business-level consistency to be enforced reliably across a growing schema.

**Why normalization matters.** Normalization prevents the same fact from being stored in two places and drifting out of sync — a direct requirement of `01-product-rules.md` Rule 78 ("financial totals... must always be derivable from the underlying transactional records, never manually overridden"). A normalized schema is also easier to extend, since new attributes are added to the correct owning table rather than bolted onto an unrelated one.

**When denormalization is allowed.** Denormalization is permitted only as a deliberate, documented performance decision — for example, storing a computed total on a Sales Order for fast list-view rendering, provided the value is always recalculated from source data on every relevant write and never manually editable. Denormalization is never used as a shortcut to avoid a proper relationship or join.

**Future-proof strategy.** The schema separates three conceptual layers at all times: **Core** (industry-agnostic entities: contacts, companies, deals, quotations, orders, invoices, payments), **Master Data** (configurable reference data), and **Vertical Extension** (industry-specific attributes and tables, such as furniture Bill of Materials). This separation, detailed further in `14-multi-industry-strategy.md`, is what allows new industries to be onboarded without altering the Core.

---

## 2. Naming Convention

| Object | Convention | Example |
|---|---|---|
| **Database** | snake_case, project-prefixed | `inakara_crm` |
| **Table** | snake_case, plural | `leads`, `sales_orders`, `invoice_items` |
| **Column** | snake_case, singular, descriptive | `first_name`, `total_amount`, `due_date` |
| **Foreign Key Column** | singular referenced table name + `_id` | `customer_id`, `sales_order_id` |
| **Index** | `idx_{table}_{column(s)}` | `idx_leads_status`, `idx_invoices_customer_id_status` |
| **Unique Index** | `uniq_{table}_{column(s)}` | `uniq_invoices_invoice_number` |
| **Foreign Key Constraint** | `fk_{table}_{referenced_table}` | `fk_deals_customers` |
| **Pivot Table** | both singular model names, alphabetically ordered, snake_case | `role_user`, `permission_role` |
| **View** | `vw_{descriptive_name}` | `vw_customer_outstanding_balance` |
| **Trigger** | `trg_{table}_{event}` | `trg_invoices_before_update` |
| **Constraint (check)** | `chk_{table}_{rule}` | `chk_payments_amount_positive` |

**Rule:** No table, column, or constraint is named ambiguously or abbreviated beyond common, unambiguous convention (e.g., `qty` for quantity is acceptable; invented abbreviations are not). Naming is always business-domain-descriptive, matching the vocabulary used in `01-product-rules.md`.

---

## 3. Table Standards

Every business table follows a consistent structural baseline:

| Element | Standard |
|---|---|
| **Primary Key** | Present on every table (Section 4). |
| **Foreign Key** | Explicit, constrained, and indexed wherever a relationship exists (Section 5). |
| **Nullable** | A column is nullable only when the business rule genuinely allows the fact to be unknown or inapplicable; nullable is never used as a default convenience. |
| **Default Value** | Defined explicitly wherever a sensible business default exists (e.g., a status column defaulting to its initial lifecycle state). |
| **Timestamp** | Every table includes standard creation and update timestamps. |
| **Soft Delete** | Present on every core business table per Section 11. |
| **UUID** | Used only where an externally-exposed, non-guessable identifier is required (Section 4); not used as a default replacement for the primary key. |
| **Created By** | Present on every business table, recording the acting user (Section 10). |
| **Updated By** | Present on every business table, recording the last acting user (Section 10). |
| **Deleted By** | Present on every soft-deletable table, recording who performed the deletion (Section 10, 11). |
| **Status Columns** | Present on every table representing a lifecycle-bearing entity, governed by Section 12. |
| **Ordering** | An explicit ordering/position column is used wherever user-defined manual ordering is a business requirement (e.g., quotation line items); otherwise natural (date/id-based) ordering is relied upon. |
| **Visibility** | Any table supporting configurable visibility/scoping (e.g., branch- or company-scoped records) carries an explicit ownership column per Section 15, never inferred implicitly. |

---

## 4. Primary Key Rules

| Type | When Used |
|---|---|
| **BIGINT (auto-increment)** | The default primary key type for all internal tables. Auto-incrementing integers are efficient for indexing, joins, and internal relationships, and are used unless a specific reason requires otherwise. |
| **UUID** | Used only where a globally unique, non-sequential, externally-exposed identifier is required — for example, a reference shared with an external system, a public-facing document link, or a future multi-tenant scenario where ID predictability is a concern. When used, UUID is typically stored as a secondary indexed column alongside the internal BIGINT primary key, rather than replacing it, to preserve internal join performance. |
| **Auto Increment** | The mechanism underlying the standard BIGINT primary key; relied upon for all internal, non-externally-exposed identification. |

**Rule:** A table does not use UUID as its sole primary key without an explicit, documented reason; the default and expected pattern across the schema is BIGINT auto-increment.

---

## 5. Foreign Key Rules

- **Relationship Naming:** Foreign key columns are always named after the singular referenced entity plus `_id` (Section 2), regardless of the relationship's business label, keeping the schema self-documenting.
- **Cascade:** Used only where the child record has no independent business meaning without its parent (e.g., Quotation line items cascading with their Quotation). Cascade delete is never applied to core business objects that must be retained per the soft-delete and audit requirements (Sections 10–11).
- **Restrict:** The default behavior for relationships involving core business objects — a Customer cannot be deleted while referenced by a Deal, consistent with `01-product-rules.md` Rule 14.
- **Set Null:** Used only where the relationship is genuinely optional and the child record retains meaning without its parent (e.g., an optional referring-employee link).
- **No Action:** Used interchangeably with Restrict where the database engine's default behavior is sufficient; the business intent (prevent orphaning core data) is what matters, not the specific mechanism.
- **Best Practices:** Every foreign key is indexed (Section 8); every foreign key relationship is intentional and documented in the owning module's data model notes, never introduced implicitly through naming convention alone.

---

## 6. Relationship Standards

| Relationship | Usage |
|---|---|
| **One-to-One** | Used sparingly, only where an entity has a distinct, optional, or access-controlled extension of itself (e.g., a Customer's confidential financial profile stored separately from its general profile). |
| **One-to-Many** | The most common relationship in the schema — a Customer has many Deals, a Sales Order has many Invoices, a Quotation has many line items. |
| **Many-to-Many** | Used where two entities can each relate to multiple instances of the other (e.g., Users to Roles, Products to Categories where a product may belong to multiple categories) — always implemented through a pivot table. |
| **Polymorphic** | Used deliberately and sparingly, where a single concept (e.g., Activity Timeline entries, Attachments, Notes) must relate to multiple different core entity types without duplicating the table per entity type. |
| **Self-Relationship** | Used where an entity relates to another instance of itself (e.g., an Employee's reporting manager, a Lead merge referencing another Lead). |
| **Pivot Tables** | Used for every Many-to-Many relationship; pivot tables may carry their own additional attributes (e.g., a role assignment's effective date) when the relationship itself has business-meaningful metadata. |

**Decision Rule:** A relationship type is chosen based on the actual business cardinality defined in `01-product-rules.md`, never based on implementation convenience — for example, Rule 12 ("A Customer may have multiple Deals") mandates One-to-Many between Customer and Deal, not a denormalized repeated structure.

---

## 7. Normalization Rules

| Level | Requirement |
|---|---|
| **1NF** | Every column holds a single, atomic value; repeating groups (e.g., multiple phone numbers in one column) are always split into a related table. |
| **2NF** | Every non-key attribute depends on the whole primary key, not part of it — relevant primarily to composite-keyed tables such as certain pivot tables carrying additional attributes. |
| **3NF** | Every non-key attribute depends only on the primary key, not on another non-key attribute — preventing, for example, storing a Customer's name redundantly on every Invoice row instead of referencing the Customer. |
| **BCNF** | Applied where multiple candidate keys exist and 3NF alone is insufficient to eliminate anomalies — evaluated case-by-case for tables with complex uniqueness requirements. |

**When Denormalization Is Acceptable:** Denormalization is acceptable only for: (a) computed, cached aggregate values that are always regenerated from source data on write (never independently editable), explicitly to satisfy a documented performance need (Section 16); or (b) point-in-time snapshots that must remain historically accurate even if source data later changes — for example, an Invoice retaining its own copy of pricing at issuance, per `01-product-rules.md` Rule 81, since the business rule explicitly requires historical documents to remain unchanged. This is a deliberate exception, not a general license to duplicate data.

---

## 8. Indexing Standards

| Index Type | Usage |
|---|---|
| **Primary Index** | Automatically present on every primary key. |
| **Unique Index** | Applied to any column or column combination that must be globally or contextually unique — e.g., Invoice number (Rule 56), a Customer's primary contact method combination used for duplicate detection. |
| **Composite Index** | Applied wherever queries filter or sort by a combination of columns together (e.g., status plus owner, per the Global Rules filter requirements in `01-product-rules.md` Section 14), ordered with the most selective/most-frequently-filtered column first. |
| **Fulltext Index** | Applied to columns supporting free-text search where the Global Rules (Section 14 of `01-product-rules.md`) require search capability (e.g., Customer name, Lead notes), where MySQL's fulltext capability provides a meaningfully better experience than a simple LIKE query. |
| **Search Index** | A general term covering any index specifically added to support a defined search or filter feature; every such index is justified by an actual, documented query pattern, never spec­ulatively added. |

**Performance Considerations:** Every foreign key is indexed by default (Section 5). Every column used in a `WHERE`, `JOIN`, or `ORDER BY` clause in a recurring, business-critical query is evaluated for indexing. Over-indexing is avoided — indexes carry write-performance cost, so each index must be justified by an actual query need, not added preemptively.

---

## 9. Data Integrity

- **Referential Integrity:** Enforced at the database level through foreign key constraints wherever a relationship exists (Section 5) — application-level checks alone are not considered sufficient for core business relationships.
- **Unique Constraints:** Enforced at the database level for any value the business rules require to be unique (Invoice numbers, Quotation numbers per Rule 99, primary contact method combinations for duplicate prevention).
- **Check Constraints:** Used to enforce simple, invariant business rules at the database level where practical (e.g., a payment amount must be non-negative), complementing — never replacing — application-level validation (`backend-architecture.md` Section 8).
- **Validation Philosophy:** The database is the last line of defense for data integrity, not the first. Form Request and Service-layer validation (`backend-architecture.md` Sections 5, 8) is the primary enforcement point for business rules; database constraints exist to guarantee integrity even in the face of an application-layer bug or a future direct-access scenario.
- **Consistency Rules:** Any two tables representing related financial facts (e.g., Invoice total and its line items' sum) must be reconcilable by query at any time — the schema never allows these to structurally diverge without an explicit, intentional business reason (such as a documented rounding rule).

---

## 10. Audit Strategy

Every core business table implements the following fields and concepts, directly supporting `01-product-rules.md` Section 11 and `backend-architecture.md` Section 18:

| Concept | Definition |
|---|---|
| **Created By** | The user who created the record, recorded on every business table at creation, never null for user-originated records. |
| **Updated By** | The user who most recently modified the record, updated on every write. |
| **Deleted By** | The user who performed a soft delete, recorded alongside the soft-delete timestamp (Section 11). |
| **Activity Tracking** | The mechanism (typically a dedicated Activity Log table, per `01-product-rules.md` Section 10) recording business-meaningful events on Leads, Deals, and Customers, separate from technical audit data. |
| **Audit Trail** | A dedicated, append-only Audit Log table capturing who/when/where/what/before/after for every change to a core business object, per `01-product-rules.md` Section 11 — structurally separate from the business tables themselves so it can never be altered by ordinary business operations. |
| **Revision History** | Applied specifically where the business rules require full historical versions to be retained, not just a change log — most notably Quotation revisions (Rule 24) — implemented as a dedicated versioned table, not as overwritten rows. |
| **Business History** | The aggregate, queryable view of a Customer's or Deal's full lifecycle, assembled from the combination of Activity Tracking, Audit Trail, and the core transactional tables themselves — never a separately duplicated summary that could drift from source data. |

---

## 11. Soft Delete Strategy

- **When Soft Delete Is Mandatory:** On every core business table — Lead, Customer, Deal, Quotation, Sales Order, Invoice, Payment, Product, and equivalent — per `01-product-rules.md` Rule 79 and the Global Rules in Section 14. This is non-negotiable for any table representing a record with financial, contractual, or historical significance.
- **When Force Delete Is Allowed:** Only for genuinely transient, non-business-significant data (e.g., expired password-reset tokens, temporary cache-backed records) that carries no audit, financial, or historical relevance. Force delete is never applied to any table listed under Section 14 (Transaction Data).
- **Archive Strategy:** Archiving is treated as a distinct, explicit status/state applied on top of soft-delete — a record may be soft-deleted (removed from active operational views) while remaining fully queryable for reporting and audit, consistent with the Global Rules definition of Archive in `01-product-rules.md` Section 14.
- **Restore Strategy:** Restoring a soft-deleted or archived record is itself an audited event (Section 10), performed only by an authorized role, and never silently reverses any business-state changes that occurred while the record was inactive.

---

## 12. Status Strategy

- **Enum vs. Lookup Table:** A simple, fixed, rarely-changing status set with no additional metadata (e.g., a binary active/inactive flag) may use a database enum or constrained string column. Any status set that is business-configurable, industry-variable, or carries additional metadata (e.g., a color, an order/sequence, a description) is implemented as a proper Lookup/Reference table instead, never hardcoded as an enum.
- **Reference Table:** Used for all pipeline stages (`01-product-rules.md` Section 7), Lead statuses, and any other status set that may need to be extended or reordered as the business or industry evolves — directly supporting the multi-industry vision.
- **Lifecycle:** Every status-bearing table's valid transitions are documented alongside the table's data model notes, mirroring the pipeline stage rules in `01-product-rules.md` Section 7, so the database structure and the business rule documentation never diverge.
- **Business State:** The "current status" of a record is always a single, unambiguous column value (per Global Rules), even when a full history of prior statuses is separately retained in an Activity/Audit table.
- **Best Practice:** No status value is ever represented as a "magic" unexplained code (e.g., an unlabeled integer `3`); every status value is either a clearly named enum case or a row in a Reference table with a human-readable label.

---

## 13. Master Data Strategy

Master data — Products, Categories, Units, Customers, Employees, Roles, Branches, Companies, Warehouses, Settings — is governed by the following standards:

- **Stability:** Master data changes far less frequently than transactional data and is structured for reuse across many transactions, never duplicated per transaction.
- **Governance:** Each master data type has a single, clearly defined owning table; transactional tables reference master data by foreign key, never by copying descriptive fields, except where a point-in-time snapshot is explicitly required (Section 7).
- **Extensibility:** Master data tables (particularly Products and Categories) are designed with the multi-industry vision in mind — structured generically enough that a "Product" can represent a furniture item today and a service, medical supply, or construction material in a future vertical, without schema change (see `14-multi-industry-strategy.md`).
- **Hierarchical Data:** Where master data is naturally hierarchical (Categories, organizational structures), a self-relationship pattern (Section 6) is used rather than a fixed number of hardcoded hierarchy levels.
- **Settings:** System and business configuration (tax rates, numbering formats, currency defaults) is stored in a dedicated, key-based Settings structure rather than scattered across unrelated tables, supporting the caching strategy in `backend-architecture.md` Section 20.

---

## 14. Transaction Data Strategy

Transactional tables — Lead, Quotation, Sales Order, Invoice, Payment, Delivery, Production, Inventory movement, Activity Log, Audit Log — follow these rules:

- **Immutability Where Appropriate:** Once issued, Invoices and confirmed Sales Orders are structurally treated as immutable — corrections occur through new records (Credit Notes, Change Orders) rather than in-place edits, directly reflecting `01-product-rules.md` Rules 36–37 and 55.
- **Historical Accuracy:** Every transactional table retains enough information to reconstruct its exact state at any point in its lifecycle, whether through revisioning (Quotations), immutability (Invoices), or a complete Audit Trail (Section 10).
- **Full Traceability:** Every transactional record links back, through its foreign keys, to the complete originating chain (Lead → Deal → Quotation → Sales Order → Invoice → Payment), so any record's full business context can always be reconstructed through joins, never through denormalized copies.
- **Append-Heavy Design:** Activity Log and Audit Log tables are designed as append-only — rows are inserted, never updated or deleted, reflecting their role as a permanent record.
- **Volume Planning:** Because these tables grow unboundedly over time, their indexing (Section 8) and archiving strategy (Section 11, Section 16) are planned explicitly for long-term volume from the outset, not revisited only when performance degrades.

---

## 15. Multi-Company Strategy

The schema is designed from the outset to support Multiple Companies, Multiple Branches, and Multiple Warehouses, even though the first deployment may operate as a single company:

- **Data Isolation:** Every company- or branch-scoped table carries an explicit ownership column (e.g., a company/branch reference) rather than relying on implicit context; queries are always scoped explicitly, never assumed.
- **Shared Master Data:** Certain master data (e.g., a global Product catalog, system-wide Settings defaults) may be shared across companies/branches by design, while other master data (e.g., branch-specific inventory) is explicitly scoped — this distinction is made deliberately per data type, never left ambiguous.
- **Company Ownership:** Every transactional record traces back to exactly one owning Company and, where relevant, one Branch, ensuring reporting and access control (per `01-product-rules.md` Section 8 role responsibilities) can always be correctly scoped.
- **Extensibility:** Because this ownership structure is established from the first schema version, expanding from single-company to multi-company operation is a matter of populating and enforcing existing columns more strictly — never a structural migration of historical data.

---

## 16. Performance Strategy

| Technique | Application |
|---|---|
| **Indexing** | Applied per Section 8, directly aligned with actual query patterns. |
| **Pagination** | Every list-oriented query is paginated by default, consistent with `backend-architecture.md` Section 19. |
| **Chunk** | Used for any batch database operation processing large row counts (bulk updates, bulk exports). |
| **Cursor** | Used for memory-efficient, single-pass processing of large result sets. |
| **Batch Insert / Update** | Used for bulk import operations (Section 18) rather than row-by-row writes, to reduce transaction overhead. |
| **Archive** | High-volume, append-only tables (Activity Log, Audit Log) are planned for periodic archiving of older data into cold storage or archive tables, keeping active tables performant, without ever losing the underlying historical record. |
| **Partitioning** | Considered for very high-volume tables (e.g., Audit Log at enterprise scale) once volume projections justify it — evaluated as a deliberate, data-driven decision, not applied preemptively. |
| **Query Optimization** | Every recurring, business-critical query (dashboards, reports) is reviewed against actual execution plans as part of ongoing engineering practice, not assumed correct by default. |

---

## 17. Security Strategy

- **Sensitive Data:** Personally identifiable information (contact details, addresses) and financial information (payment references, credit terms) are identified explicitly and handled with elevated care throughout the schema and application layer.
- **Encryption:** Sensitive fields warranting protection beyond standard access control (e.g., stored payment references, sensitive personal identifiers) are encrypted at rest using Laravel's built-in encryption mechanisms.
- **Hashing:** Credentials (passwords) are always hashed, never encrypted or stored in plain text, consistent with `backend-architecture.md` Section 29.
- **Personal Information:** Customer and employee personal data is scoped and access-controlled consistent with each role's business responsibility (`01-product-rules.md` Section 8) — for example, Warehouse staff do not require access to a Customer's financial history.
- **Access Control:** Database-level access (application database user privileges) follows least-privilege principles, separate from and in addition to the application-level RBAC defined in `backend-architecture.md` Section 9.
- **Data Ownership:** Every sensitive record's ownership (Section 15) is unambiguous, supporting correct access scoping as the platform grows to multi-company operation.
- **Compliance Considerations:** Schema design anticipates future data-protection compliance needs (e.g., data export/erasure requests) by ensuring personal data is centralized and traceable rather than duplicated unpredictably across the schema.

---

## 18. Import & Export Strategy

- **Bulk Import:** Follows the queued, validated pattern defined in `backend-architecture.md` Section 22; the database layer supports this through batch-insert-friendly table design (avoiding overly restrictive constraints that would force row-by-row processing without cause).
- **Excel / CSV Import:** Imported data is staged and validated before being committed to core business tables, using the same validation rules as manual entry (`01-product-rules.md` Rule 96).
- **Rollback:** Import operations affecting multiple rows are wrapped in a transaction (`backend-architecture.md` Section 12) so a failed batch does not leave partially-imported, inconsistent data.
- **Validation:** Uniqueness and referential integrity constraints (Sections 8–9) are the final safeguard against invalid imported data, in addition to application-level validation.
- **Duplicate Detection:** Import processes rely on the same unique constraints and lookup patterns used for manual duplicate detection (`01-product-rules.md` Rule 5), never a separate, inconsistent mechanism.
- **Export Standards:** Exported data is always drawn through the same access-controlled queries used elsewhere in the application (`01-product-rules.md` Rule 97) — never through a direct, unrestricted database dump.

---

## 19. Backup & Recovery

- **Backup Strategy:** Regular, automated backups are maintained at a frequency appropriate to the business's tolerance for data loss, covering the full database including Audit and Activity Log tables.
- **Restore Strategy:** Restore procedures are documented and periodically verified, ensuring backups are genuinely usable in a real recovery scenario, not merely created and untested.
- **Retention Policy:** Backup retention duration is defined explicitly, balancing storage cost against the business and, where applicable, compliance need to recover historical states.
- **Disaster Recovery:** A defined recovery time objective and recovery point objective guide backup frequency and infrastructure choices, reviewed as the business's reliance on the system grows.
- **Database Migration Strategy:** Schema migrations (Laravel migrations) are applied through a controlled, versioned, reversible-where-possible process; destructive migrations against production data always follow a backup-first discipline.

---

## 20. Future Scalability

The database strategy anticipates the following growth paths without requiring a fundamental redesign:

- **Database Sharding:** The Multi-Company data isolation model (Section 15) provides a natural sharding boundary (by company) if horizontal scaling is eventually required.
- **Read Replica:** Reporting and analytics workloads (Section 13 of `01-product-rules.md`) are structured to be servable from a read replica, since they are read-heavy and tolerant of minor replication lag.
- **Caching:** Aggregate and reference data caching (`backend-architecture.md` Section 20) reduces load on the primary database for frequently-read, infrequently-changed data.
- **Queue:** Write-heavy, non-time-critical operations (imports, exports, notification-triggered writes) are decoupled from the primary request path via Queue (`backend-architecture.md` Section 14), smoothing database load.
- **Reporting Database:** As reporting complexity grows, a dedicated reporting/analytics data store (updated via replication or ETL from the primary operational database) is a planned future option, kept viable by the primary schema's normalized, well-referenced structure.
- **Analytics Database:** Longer-term trend and cross-industry analytics may eventually warrant a dedicated analytical store; the Core/Master Data/Vertical Extension separation (Section 1) ensures such a store can be built without re-engineering the operational schema.
- **Future SaaS Strategy:** The combination of explicit multi-company ownership (Section 15), consistent auditability (Section 10), and normalized master/transaction separation together form the foundation required for a future multi-tenant SaaS deployment model.

---

## 21. Anti-Patterns

The following are explicitly prohibited across the entire database, without exception:

- **Duplicated Data:** Storing the same fact in two tables without an explicit, documented reason (Section 7); every duplication risk is resolved through proper relationships instead.
- **Business Logic in the Database:** Complex business rules (pipeline transition rules, discount approval logic) are never implemented as stored procedures or triggers encoding business decisions — that logic belongs exclusively in the Service layer (`backend-architecture.md` Section 5). Triggers are limited to structural integrity concerns only, if used at all.
- **Random Naming:** Any table, column, or constraint name that does not follow Section 2's convention.
- **Inconsistent Foreign Keys:** A relationship implemented without a proper constraint, or a foreign key column not following the naming convention.
- **Nullable Abuse:** Marking columns nullable by default rather than as a deliberate business decision (Section 3).
- **Missing Indexes:** Any foreign key or frequently-filtered column left unindexed (Section 8).
- **Unnecessary JSON Columns:** Using a JSON column to store data that has a clear, stable relational structure, purely to avoid schema design work; JSON is reserved for genuinely unstructured or highly variable data (e.g., certain industry-specific extension attributes, per the multi-industry strategy) where a relational structure would be premature or impractical.
- **Storing Calculated Values Without Justification:** Any denormalized/calculated column not covered by the exception in Section 7.
- **Magic Status Values:** Any unexplained numeric or coded status value not backed by a named enum or Reference table (Section 12).
- **Orphan Records:** Any child record left referencing a deleted or nonexistent parent, which the Foreign Key rules in Section 5 and 9 are specifically designed to prevent.

---

## 22. Best Practices

- Use consistent naming across every table, column, and constraint (Section 2).
- Apply proper indexing aligned with real query patterns (Section 8).
- Enforce foreign key integrity at the database level, not application level alone (Section 9).
- Apply soft delete to every core business table (Section 11).
- Maintain complete audit logging for every business-critical change (Section 10).
- Wrap multi-table writes in transactions (`backend-architecture.md` Section 12).
- Design relationships to reflect actual business cardinality, never implementation convenience (Section 6).
- Keep tables small and focused on a single entity concept, relying on relationships rather than wide, multi-purpose tables.
- Design every new table with the future multi-industry and multi-company vision in mind (Sections 13, 15, 20).

---

## 23. Decision Guidelines

When a future developer or AI agent needs to create a new table, the following sequence must be followed:

1. **Naming:** Apply Section 2's convention exactly — plural snake_case table name, singular snake_case columns, standard foreign key naming.
2. **Classify the Table:** Determine whether it is Core, Master Data, or Vertical Extension (Section 1, and `14-multi-industry-strategy.md`) — this determines whether it belongs in the industry-agnostic core schema or an isolated extension.
3. **Define the Primary Key:** Default to BIGINT auto-increment (Section 4) unless a documented UUID need exists.
4. **Define Relationships:** Identify true business cardinality from `01-product-rules.md` before choosing One-to-One, One-to-Many, Many-to-Many, or Polymorphic (Section 6); add and index every foreign key (Section 5, 8).
5. **Add Audit Fields:** `created_by`, `updated_by`, `deleted_by`, standard timestamps, and soft-delete columns, unless the table is explicitly transient (Section 10, 11).
6. **Add Status Fields (if applicable):** Determine whether a simple enum or a full Reference table is appropriate (Section 12), based on whether the status set is fixed and simple or configurable/extensible.
7. **Apply Indexing:** Add indexes for every foreign key and every column expected to be filtered, sorted, or searched per the corresponding module's requirements in `01-product-rules.md` Section 14 (Global Rules).
8. **Design for Scalability:** Confirm the table carries explicit company/branch ownership if it is company- or branch-scoped (Section 15), and confirm it does not introduce a furniture-specific assumption into the Core layer (Section 1, and Section 15 of `01-product-rules.md`).
9. **Validate Against This Document:** Before implementation, confirm the proposed table does not violate any rule in Sections 1–22 above; any genuine exception is documented and justified, never silently introduced.

This sequence is the permanent, standard path for all future schema growth in INAKARA CRM.

---

## 24. Glossary

| Term | Definition |
|---|---|
| **Core** | The industry-agnostic tables representing universal CRM concepts (contacts, deals, orders, invoices). |
| **Master Data** | Stable, reusable reference data (products, categories, branches) referenced by transactional records. |
| **Vertical Extension** | Industry-specific tables or attributes layered on top of the Core without modifying it. |
| **Soft Delete** | Marking a record as deleted via a timestamp/flag rather than physically removing it. |
| **Audit Trail** | An immutable, append-only record of who changed what, when, and why. |
| **Denormalization** | Deliberately storing derived or duplicated data for a documented performance or historical-accuracy reason. |
| **Pivot Table** | A table implementing a Many-to-Many relationship between two entities. |

## 25. References

- `PROJECT_CONSTITUTION.md` — supreme authority.
- `01-product-rules.md` — business rules and cardinalities this schema must enforce.
- `backend-architecture.md` — the Repository/Model layer that consumes this schema.
- `.ai/14-multi-industry-strategy.md` *(future document)* — expands on the Core/Master Data/Vertical Extension separation introduced in Section 1.

---

*End of 06-database-rules.md — Version 1.0.0*
