# 07. API Standards
## INAKARA CRM — Permanent API Standards

**Status:** Binding — Subordinate to `PROJECT_CONSTITUTION.md`, `01-product-rules.md`, `02-design-principles.md`, `03-design-system.md`, `frontend-architecture.md`, `backend-architecture.md`, `06-database-rules.md`
**Version:** 1.0.0
**Scope:** This document defines API standards only. It contains no endpoints, no routes, no controllers, no requests, no resources, and no code. Every current and future API surface — Inertia-driven, REST, mobile, public, partner, or webhook — must comply with this document.

---

## Table of Contents

1. [API Philosophy](#1-api-philosophy)
2. [API Architecture](#2-api-architecture)
3. [Endpoint Naming Convention](#3-endpoint-naming-convention)
4. [HTTP Method Standards](#4-http-method-standards)
5. [Request Standards](#5-request-standards)
6. [Response Standards](#6-response-standards)
7. [Error Handling Standards](#7-error-handling-standards)
8. [Pagination Standards](#8-pagination-standards)
9. [Filtering Standards](#9-filtering-standards)
10. [Sorting Standards](#10-sorting-standards)
11. [Search Standards](#11-search-standards)
12. [Authentication Standards](#12-authentication-standards)
13. [Authorization Standards](#13-authorization-standards)
14. [Validation Standards](#14-validation-standards)
15. [File Upload Standards](#15-file-upload-standards)
16. [Export & Import API Standards](#16-export--import-api-standards)
17. [Notification API Standards](#17-notification-api-standards)
18. [Webhook Standards](#18-webhook-standards)
19. [API Versioning Strategy](#19-api-versioning-strategy)
20. [Performance Standards](#20-performance-standards)
21. [Security Standards](#21-security-standards)
22. [Logging & Monitoring](#22-logging--monitoring)
23. [Integration Standards](#23-integration-standards)
24. [Future Scalability](#24-future-scalability)
25. [Anti-Patterns](#25-anti-patterns)
26. [Best Practices](#26-best-practices)
27. [Decision Guide](#27-decision-guide)
28. [Glossary](#28-glossary)
29. [References](#29-references)

---

## 1. API Philosophy

**Why APIs must be consistent.** INAKARA CRM will eventually be consumed by more than one client — the current Inertia-driven React frontend, and in the future, mobile apps, partner integrations, and possibly public API consumers. If each surface is designed inconsistently, every new consumer must relearn the system's conventions from scratch, multiplying integration cost and defect risk. Consistency at the API layer is what lets the platform grow consumers without growing complexity proportionally.

**Why predictability matters.** A developer (or an AI coding agent) working against INAKARA CRM's API should be able to correctly guess an endpoint's shape, an error's structure, or a pagination format without reading bespoke documentation for every single resource. Predictability turns integration work from investigation into application of a known pattern.

**Why versioning is important.** As the business and its data model evolve — new statuses, new fields, new modules — API consumers must be protected from silent breaking changes. Versioning is the mechanism that allows the platform to evolve internally while giving external and future consumers a stable contract to build against.

**Why backward compatibility is prioritized.** Breaking an existing API contract breaks every consumer depending on it — including, eventually, mobile apps that cannot be force-updated instantly and partner integrations outside INAKARA's direct control. Backward compatibility is treated as the default expectation; breaking changes are exceptional, deliberate, and always versioned (Section 19), never silent.

---

## 2. API Architecture

| Surface | Purpose |
|---|---|
| **Internal API (Inertia-driven)** | The current, primary interface between the Laravel backend and the React frontend, serving full page data via Inertia responses. Treated as an API surface subject to these standards, even though it is not a traditional JSON REST API. |
| **Public API** | A future, formally versioned REST API exposed for external consumers (e.g., customers integrating their own systems), requiring the strictest stability and documentation guarantees. |
| **Private API** | Internal-only REST endpoints (e.g., serving a future mobile app built by the same organization) — held to full REST standards but not necessarily exposed publicly or documented externally. |
| **Partner API** | A scoped, credentialed API surface for specific third-party integration partners (e.g., an ERP or accounting integration), with access limited to the specific resources that partnership requires. |
| **Webhook** | Outgoing (INAKARA CRM notifying external systems of events) and incoming (external systems notifying INAKARA CRM) event-driven integration channels, per Section 18. |
| **Future Mobile API** | A REST API surface purpose-built for first-party mobile apps (Flutter/React Native), sharing the same underlying Service layer (`backend-architecture.md` Section 5) as the web application. |
| **Future SaaS API** | The eventual multi-tenant-aware API surface supporting the platform's SaaS evolution (`PROJECT_CONSTITUTION.md` Section 14), built on the same standards defined here, scoped by Company/Branch (`06-database-rules.md` Section 15). |

**Architectural Principle:** Every surface above, regardless of protocol or consumer, is a thin layer over the same Service layer defined in `backend-architecture.md`. No API surface reimplements business logic; each surface only adapts the same underlying business operations to a different request/response shape.

---

## 3. Endpoint Naming Convention

- **Resources:** Named as plural nouns representing the business object (per `06-database-rules.md` Section 2 table naming), never verbs — a resource path names *what*, not *what to do*.
- **Nested Resources:** Used only where a genuine ownership/containment relationship exists (e.g., a Sales Order's line items are nested under that Sales Order); nesting is avoided where the relationship is merely associative rather than owning, to prevent overly deep, brittle paths.
- **Pluralization:** Resource path segments are always plural, applied consistently across every module, matching the plural table naming convention already established in `06-database-rules.md` Section 2.
- **Action Endpoints:** Reserved for operations that do not map cleanly to a standard CRUD verb (e.g., approving a Quotation, converting a Lead to a Deal) — expressed as a clear, business-meaningful action segment on the resource, used only when Section 4's standard HTTP methods genuinely cannot express the operation.
- **Special Endpoints:** Endpoints serving cross-cutting concerns (search, export, bulk actions) follow a consistent, predictable pattern applied identically across every module offering that capability — a developer who has used search on one resource should immediately understand how search works on any other.
- **Version Prefix:** Every REST API path is prefixed with an explicit version segment (Section 19), applied consistently from the first version onward, never retrofitted later.

This section defines naming conventions only; no literal endpoint paths are prescribed here — module-specific endpoint definitions are produced during feature implementation, following these conventions.

---

## 4. HTTP Method Standards

| Method | Usage |
|---|---|
| **GET** | Retrieves a resource or collection; never causes a side effect or state change. Always safe to call repeatedly. |
| **POST** | Creates a new resource, or triggers an action endpoint (Section 3) that does not fit a standard CRUD verb. |
| **PUT** | Replaces a resource's full representation; used only where a complete replacement is the genuine intent, since partial updates are more common in this domain. |
| **PATCH** | Applies a partial update to a resource; the default choice for standard "edit" operations, consistent with how most CRM record updates behave (updating specific fields, not replacing the whole record). |
| **DELETE** | Performs a soft delete (per `06-database-rules.md` Section 11) on a resource; never a hard delete for core business data through a standard API call. |
| **OPTIONS** | Used for CORS preflight and capability discovery where relevant to future public/partner API consumers. |
| **HEAD** | Used where a consumer needs to check resource existence or metadata without retrieving the full body. |

**Rule:** An endpoint's HTTP method always matches its actual effect; a GET request never mutates data, and a mutating operation is never exposed via GET, regardless of implementation convenience.

---

## 5. Request Standards

- **Headers:** Every request carries standard identification headers (content type, accept, authentication credential per Section 12); custom headers are introduced only for a clearly justified cross-cutting concern (e.g., an idempotency key, below).
- **Authentication:** Every request to a protected resource carries valid authentication credentials, per Section 12; unauthenticated requests to protected resources are rejected consistently.
- **Authorization:** Every request is subject to authorization checks per Section 13, independent of authentication — being logged in does not imply being permitted.
- **Validation:** Every request body is validated per Section 14 before any business logic executes.
- **Pagination:** List requests accept consistent pagination parameters, per Section 8.
- **Sorting:** List requests accept consistent sorting parameters, per Section 10.
- **Searching:** List requests accept a consistent search parameter where search is supported, per Section 11.
- **Filtering:** List requests accept consistent filter parameters, per Section 9.
- **Date Format:** All dates and timestamps are transmitted in a single, consistent, unambiguous standard format (ISO 8601), regardless of the consumer's locale.
- **Timezone:** All timestamps are transmitted in a consistent reference timezone (UTC) at the API layer; presentation-layer localization to the user's timezone happens at the client, consistent with `06-database-rules.md` Section 3 (Timestamp) and `01-product-rules.md` Global Rules.
- **Locale:** Where localization affects response content (e.g., currency formatting, translated labels), locale is determined consistently (via an explicit parameter or authenticated user preference), never inferred ambiguously.
- **Idempotency:** State-changing requests that are safe to retry (e.g., a payment recording action) support idempotency keys where the business risk of duplicate execution warrants it, preventing duplicate business records from network retries.

---

## 6. Response Standards

A single, unified response philosophy applies across every API surface:

| Response Type | Standard |
|---|---|
| **Success** | A consistent envelope indicating success, containing the requested resource or collection data. |
| **Validation Error** | A consistent, field-keyed structure identifying exactly which fields failed validation and why, directly consumable by the frontend's form error handling (`frontend-architecture.md` Section 8). |
| **Business Error** | A consistent structure carrying a business-meaningful error code and message (e.g., "Quotation has expired"), distinct from generic system errors, per `backend-architecture.md` Section 16. |
| **Authorization Error** | A consistent structure and status code (403) indicating the request was authenticated but not permitted. |
| **Not Found** | A consistent structure and status code (404), used only for genuinely missing resources, never conflated with authorization denial. |
| **Server Error** | A consistent, non-technical structure and status code (500) that never leaks internal implementation detail to the consumer. |
| **Pagination Response** | A consistent metadata structure (Section 8) accompanying every paginated collection, applied identically across every module. |
| **Empty Response** | Used consistently (e.g., for successful DELETE operations) with a clear, predictable status code, never an ambiguous empty body without status context. |
| **File Response** | A consistent content-type and disposition standard for downloadable files (exports, PDFs), per Section 15/16. |
| **Streaming Response** | Used deliberately for large exports or long-running data transfers, with a consistent approach to progress/completion signaling. |

**Consistency Requirement:** The exact same response envelope shape is used for the same response type across every resource and every module. A consumer that has learned the response shape for one resource can correctly predict the shape for any other resource in the system, without exception.

---

## 7. Error Handling Standards

- **HTTP Status Codes:** Used correctly and consistently — 2xx for success, 4xx for client-caused errors (validation, authorization, not found), 5xx for server-caused errors — never repurposed for convenience.
- **Business Exceptions:** Raised as specific, named exceptions in the Service layer (`backend-architecture.md` Section 16) and translated consistently into the Business Error response shape (Section 6), always with a business-meaningful message.
- **Validation Exceptions:** Raised by Form Request validation (`backend-architecture.md` Section 8) and translated consistently into the Validation Error response shape.
- **Authentication Failures:** Result in a consistent 401 response, distinct from authorization failures (403).
- **Authorization Failures:** Result in a consistent 403 response, per Section 6.
- **Unexpected Errors:** Always logged with full context (Section 22) and always surfaced to the consumer as a generic, safe message — internal stack traces, query details, or system paths are never included in any API response, in any environment reachable by real consumers.
- **Logging Strategy:** Every error, regardless of type, is logged with sufficient context to diagnose it, per `backend-architecture.md` Section 18, without ever including logged content in the response itself.
- **User-Friendly Error Messages:** Every error message returned to a consumer is written to be understandable by the actual audience of that API surface (an end user via the frontend, or a developer integrating a partner API), never a raw technical or database-level message.

---

## 8. Pagination Standards

- **Page-Based Pagination:** The default pagination strategy for standard list views (matching typical CRM table browsing patterns), using consistent page number and page size parameters across every module.
- **Cursor Pagination:** Used specifically for very large or frequently-changing datasets (e.g., a high-volume Activity Log or Audit Log listing) where page-based pagination's performance degrades at depth or where result consistency during concurrent writes matters more than arbitrary page jumping.
- **Large Dataset Strategy:** For datasets large enough that even cursor pagination is insufficient for a consumer's need (e.g., a full data export), the Export mechanism (Section 16) is used instead of paginated listing.
- **Metadata:** Every paginated response includes consistent metadata — current page, page size, total count (where feasible to compute efficiently), and indicators for next/previous availability — applied identically across every module.
- **Navigation Philosophy:** Pagination metadata is designed so a consumer never needs to guess whether more data exists; availability of further pages is always explicit in the response.

---

## 9. Filtering Standards

- **Simple Filters:** Single-field, exact-match filters (e.g., filter Leads by status) follow a consistent parameter naming pattern across every module.
- **Advanced Filters:** Multi-condition filters are expressed through a consistent, predictable structure rather than a bespoke syntax per module.
- **Multiple Filters:** Combining more than one filter is always additive (AND logic) by default and consistently documented per endpoint when OR logic is genuinely required.
- **Range Filters:** Numeric and date ranges (e.g., Deal value between X and Y) use a consistent parameter pattern (e.g., a defined minimum/maximum parameter pair) across every module supporting range filtering.
- **Date Filters:** Follow the same date format and timezone standard defined in Section 5.
- **Status Filters:** Reference the authoritative status definitions established in `06-database-rules.md` Section 12, never ad hoc string matching against inconsistent status representations.
- **Relationship Filters:** Filtering by a related resource (e.g., Deals belonging to a specific Customer) follows a consistent parameter pattern, distinguishing clearly between filtering by a related resource's ID versus a related resource's attribute.
- **Performance Considerations:** Every supported filter corresponds to an indexed query path (per `06-database-rules.md` Section 8); filters are not exposed on the API surface faster than the underlying schema can support them efficiently.

---

## 10. Sorting Standards

- **Single Sorting:** The default case — a consistent parameter pattern specifying one field and direction.
- **Multi-Column Sorting:** Supported where genuinely useful (e.g., sort by status then by date), using a consistent, ordered parameter structure.
- **Default Sorting:** Every list endpoint defines an explicit, sensible default sort order (typically most-recent-first or business-priority order) so that omitting a sort parameter never produces an arbitrary or unstable order.
- **Performance Considerations:** Sortable fields correspond to indexed columns (per `06-database-rules.md` Section 8); sorting is never exposed on unindexed, expensive-to-sort fields without a documented performance justification.

---

## 11. Search Standards

- **Global Search:** A single, cross-module search capability (matching the Command Palette / Quick Search pattern in `frontend-architecture.md` Section 6) with a consistent request/response pattern.
- **Module Search:** Search scoped to a single resource type (e.g., searching only Customers), following the same consistent search parameter pattern as global search.
- **Full-Text Search:** Used where the underlying column is indexed for full-text search (per `06-database-rules.md` Section 8), for genuinely free-text fields (names, notes, descriptions).
- **Keyword Search:** Simpler substring/keyword matching used where full-text indexing is not warranted for a given field.
- **Search Performance Philosophy:** Search is never implemented as an unindexed, full-table scan on production data; any field exposed for search is backed by an appropriate index or search-optimized structure.

---

## 12. Authentication Standards

- **Laravel Fortify:** The standard authentication mechanism for the current, primary Inertia-driven web application, handling login, password reset, and related flows consistently with `backend-architecture.md` Section 29.
- **Session Authentication:** The default authentication mode for the current Inertia application, appropriate for a first-party, same-origin web client.
- **Future API Tokens:** Recommended for future first-party mobile and private API consumers — issued per authenticated user, scoped, and revocable, without requiring a full OAuth flow for internal consumers.
- **Future OAuth:** Recommended specifically for future Partner API and Public API consumers, where a third party (not INAKARA CRM itself) needs delegated, scoped access on behalf of a user or organization.
- **Future JWT Considerations:** May be adopted for stateless API authentication where session-based authentication is impractical (e.g., a future microservice or mobile offline-first scenario); if adopted, token expiry, refresh, and revocation strategy must be explicitly defined before use, never introduced as an afterthought.

**Recommended Approach:** The platform begins with session authentication for its single first-party client, and layers token-based authentication (API tokens, then OAuth for partners) incrementally as new consumer types are genuinely introduced — authentication complexity is added only when a real consumer requires it, per Section 1's philosophy.

---

## 13. Authorization Standards

- **Role-Based Access Control:** Every API request is authorized against the acting user's resolved role and permissions, per `backend-architecture.md` Section 9 and Section 10 — never bypassed for any surface, including future public/partner APIs.
- **Permission Checks:** Enforced consistently regardless of which API surface (Inertia, REST, partner) the request arrives through, since all surfaces share the same underlying Service and Policy layer (Section 2).
- **Policy Integration:** Record-level authorization (e.g., can this user access this specific Customer) is always enforced via Policy classes, per `backend-architecture.md` Section 9, applied identically across every API surface.
- **Ownership Rules:** Where a business rule defines explicit ownership (e.g., a Lead's assigned owner, per `01-product-rules.md` Section 5.4), API authorization enforces that ownership consistently, never relying on the frontend to hide unauthorized data as its only protection.
- **Company Isolation:** Every API request is implicitly scoped to the acting user's Company (per `06-database-rules.md` Section 15), preventing cross-company data exposure regardless of API surface.
- **Branch Isolation:** Similarly scoped by Branch where branch-level access restrictions apply.
- **Warehouse Isolation:** Similarly scoped by Warehouse for Inventory and Delivery-related endpoints.

**Rule:** Authorization is enforced server-side, on every request, regardless of what the requesting client claims or displays — the API never trusts client-side permission filtering as a security boundary.

---

## 14. Validation Standards

- **Validation Philosophy:** Every request is validated at multiple, complementary layers — request-shape validation, business-rule validation, and database-level integrity — each catching a different class of error, consistent with `backend-architecture.md` Section 8 and `06-database-rules.md` Section 9.
- **Request Validation:** Structural validation (required fields, types, formats) occurs first, before any business logic executes, via Form Request classes.
- **Business Validation:** Business-rule validation (e.g., a Quotation cannot be approved if expired, per `01-product-rules.md` Rule 23) occurs in the Service layer, after structural validation passes.
- **Database Validation:** Final integrity guarantees (uniqueness, referential integrity) are enforced at the database layer per `06-database-rules.md` Section 9, as the last line of defense.
- **Cross-Module Validation:** Where a request's validity depends on data in another module (e.g., creating a Sales Order requires an approved Quotation from the Quotation module), this validation is explicitly performed in the Service layer, with a clear, business-meaningful error if the cross-module condition is not met — never assumed or skipped for convenience.

---

## 15. File Upload Standards

| File Type | Standard |
|---|---|
| **Image** | Validated for file type, maximum size, and reasonable dimension constraints before acceptance. |
| **PDF** | Validated for file type and maximum size; used for both business document attachments and generated outputs (Section 16, `backend-architecture.md` Section 23). |
| **Excel** | Validated for file type and maximum size before being queued for import processing (Section 16). |
| **Document (general)** | Restricted to an explicit allow-list of accepted formats; arbitrary or unrecognized file types are always rejected. |

- **Maximum Size:** Defined explicitly per file type/context (e.g., a smaller limit for avatar images, a larger limit for bulk import files), never left unbounded.
- **Allowed Formats:** Defined explicitly per upload context; the API never accepts a file purely by trusting its declared content type without server-side verification.
- **Validation Rules:** Applied consistently through Form Request/Rule classes (`backend-architecture.md` Section 8), never bypassed for any upload endpoint.
- **Storage Strategy:** All uploads are persisted through Laravel's Storage abstraction (`backend-architecture.md` Section 21), keeping the API's storage backend swappable without changing the API contract.

---

## 16. Export & Import API Standards

- **Bulk Export:** Export requests are accepted, queued (Section 20), and the consumer is given a way to track progress and retrieve the completed file rather than blocking on a long-running synchronous request.
- **Bulk Import:** Import requests follow the same queued pattern, applying identical validation to manually entered data (`01-product-rules.md` Rule 96, `06-database-rules.md` Section 18).
- **Queue Processing:** Both export and import operations are processed asynchronously by default for any non-trivial dataset size, consistent with `backend-architecture.md` Section 14.
- **Progress Tracking:** A consistent mechanism (e.g., a status endpoint or database-notification-driven update) allows the consumer to check the state of a long-running export/import job.
- **Error Reporting:** Import failures are reported back with row-level detail wherever feasible, following the consistent error response shape (Section 6), never as an opaque overall failure.
- **Rollback Considerations:** A failed import batch is fully rolled back per `06-database-rules.md` Section 18, never left partially applied.

---

## 17. Notification API Standards

| Channel | Standard |
|---|---|
| **Email** | Delivered via Laravel Notification's mail channel, per `backend-architecture.md` Section 15. |
| **Database Notification** | Delivered via Laravel Notification's database channel, surfaced through a consistent in-app notification retrieval pattern. |
| **Future WhatsApp** | Reserved as a future channel extension, designed to slot into the existing Notification abstraction without restructuring existing notification classes. |
| **Future SMS** | Similarly reserved as a future channel extension. |
| **Future Push Notification** | Reserved for future mobile app support, following the same Notification abstraction. |
| **Future Webhook** | Reserved for notifying external partner systems of relevant events, per Section 18. |

**Rule:** Every notification type is defined once, per `01-product-rules.md` Section 9, and delivered consistently regardless of channel — channel is a delivery detail, not a reason to duplicate notification logic.

---

## 18. Webhook Standards

- **Incoming Webhooks:** Accepted only from explicitly registered, trusted sources; every incoming webhook is authenticated and validated before triggering any internal business logic, routed through the same Service layer as any other request (Section 2).
- **Outgoing Webhooks:** Dispatched asynchronously (queued, per `backend-architecture.md` Section 14) following defined domain Events (`backend-architecture.md` Section 13), never blocking the originating business operation on an external system's response time.
- **Retry Strategy:** Outgoing webhook delivery failures are retried with a defined backoff strategy and a defined maximum retry count, after which the failure is logged and surfaced for manual review rather than retried indefinitely.
- **Signature Verification:** Every outgoing webhook payload is signed, allowing the receiving system to verify authenticity; every incoming webhook is required to provide a verifiable signature before being trusted.
- **Security:** Webhook endpoints are treated as public-facing attack surface and are subject to the full security standards in Section 21, including strict input validation regardless of the claimed source.
- **Logging:** Every webhook attempt (incoming and outgoing), including failures, is logged with sufficient detail to diagnose integration issues, per Section 22.
- **Failure Handling:** A webhook failure never silently drops a business event — failed deliveries remain visible and, where relevant, retryable or manually re-triggerable.

---

## 19. API Versioning Strategy

- **Version Naming:** REST API versions are identified explicitly in the URL path (Section 3), using a simple, incrementing scheme, so version identification never depends on ambiguous header inspection alone for primary routing.
- **Deprecation Policy:** A deprecated API version is announced with a clearly communicated support end date before removal; consumers are never abruptly cut off without notice.
- **Backward Compatibility:** Within a given major version, changes are additive only (new optional fields, new endpoints) — no existing field is removed, renamed, or repurposed, and no existing required behavior changes, without incrementing the version.
- **Migration Strategy:** When a breaking change is genuinely necessary, it is introduced as a new version, with the prior version continuing to operate through its deprecation window, giving consumers a defined migration period.
- **Future Version Support:** At most a small, defined number of versions are supported concurrently at any time, balancing consumer stability against the maintenance cost of supporting many parallel contracts indefinitely.

---

## 20. Performance Standards

| Concern | Standard |
|---|---|
| **Caching** | Applied per `backend-architecture.md` Section 20 — aggregate, configuration, and reference data may be cached at the API layer; transactional business data is not cached in a way that risks staleness. |
| **Compression** | Response compression is enabled by default for API responses of meaningful size. |
| **Lazy Loading** | List and detail endpoints return only the data genuinely needed by the requesting view by default, with related data available on demand (e.g., via explicit include parameters) rather than always deeply nested. |
| **Efficient Queries** | Every endpoint's underlying query is designed to avoid N+1 patterns, per `backend-architecture.md` Section 19. |
| **Batch Operations** | Bulk actions (e.g., bulk status update across multiple records) are exposed as a single batch endpoint rather than requiring the consumer to issue many individual requests. |
| **Rate Limiting** | Applied per consumer/credential, protecting the platform from both accidental and malicious excessive request volume, per Section 21. |
| **Timeout Strategy** | Long-running operations are never handled via an indefinitely-blocking synchronous request; they are queued (Section 16) with a progress-tracking pattern instead. |

---

## 21. Security Standards

- **HTTPS Only:** Every API surface is served exclusively over HTTPS; unencrypted access is never permitted, in any environment reachable by real consumers.
- **CSRF:** Enforced for the session-authenticated Inertia surface per `backend-architecture.md` Section 29; token-authenticated API surfaces rely on token security (Section 12) rather than CSRF tokens, appropriate to their authentication model.
- **Rate Limiting:** Applied to all authentication endpoints and any endpoint vulnerable to abuse (search, export), per Section 20.
- **Input Sanitization:** Every input is validated and sanitized per Section 14, regardless of which API surface it arrives through.
- **Output Escaping:** All output is escaped appropriately for its consumption context, preventing injection into any downstream rendering context.
- **Sensitive Data Handling:** Sensitive fields (per `06-database-rules.md` Section 17) are never included in a response unless the requesting user's role and permissions explicitly entitle them to that data.
- **Token Security:** Any issued API token or credential (Section 12) is stored securely, transmitted only over HTTPS, and revocable on demand.
- **Encryption:** Sensitive data in transit is protected by HTTPS; sensitive data at rest follows `06-database-rules.md` Section 17.
- **API Abuse Prevention:** Beyond basic rate limiting, anomalous usage patterns (e.g., rapid sequential access to many distinct customer records) are logged and available for security review (Section 22).

---

## 22. Logging & Monitoring

| Concern | Standard |
|---|---|
| **Request Logging** | Every API request is logged with sufficient context (endpoint, consumer identity, timestamp) to reconstruct usage history. |
| **Error Logging** | Every error, at every layer, is logged per `backend-architecture.md` Section 18, without exposing logged detail in the response itself. |
| **Performance Logging** | Slow requests are logged with enough detail (endpoint, duration, query count where feasible) to identify performance regressions before they become incidents. |
| **Audit Logging** | Every API-driven change to a core business object triggers the same Audit Log mechanism defined in `06-database-rules.md` Section 10, regardless of which API surface initiated the change. |
| **API Usage Analytics** | Aggregate usage patterns (which endpoints, how often, by which consumer type) are tracked to inform future capacity planning and prioritization. |
| **Monitoring Philosophy** | The API is monitored for availability, error rate, and latency continuously, with alerting thresholds defined proactively rather than discovered reactively after an incident. |

---

## 23. Integration Standards

| Integration | Standard |
|---|---|
| **Payment Gateway** | Integrated through a dedicated Service abstraction, keeping payment-provider-specific logic isolated from the core Payment business rules in `01-product-rules.md` Section 4.9, so the provider can change without altering core payment logic. |
| **WhatsApp API** | Integrated as a Notification channel extension (Section 17), never as a bespoke, parallel messaging pathway outside the standard notification system. |
| **ERP** | Integrated via a dedicated integration module, using the Partner API pattern (Section 2) or a scheduled/event-driven sync, with clear ownership of which system is the source of truth for each shared data field. |
| **Accounting Software** | Integrated similarly to ERP, with Invoice and Payment data (`01-product-rules.md` Section 4.8–4.9) as the primary shared domain, respecting the immutability rules in `06-database-rules.md` Section 14. |
| **Marketplace** | Integrated as a Partner API consumer or provider, scoped strictly to the data that integration genuinely requires. |
| **Google Services** | Used where relevant (e.g., calendar sync, maps for delivery) via dedicated Service abstractions, isolated from core business logic. |
| **AI Services** | Integrated via a dedicated Service abstraction, with clear boundaries on what data is sent to external AI providers, respecting the sensitive data handling standards in Section 21. |
| **Future Integrations** | Any future integration follows the same pattern — a dedicated Service abstraction, isolated from core business logic, exposed through the Partner API or Webhook standards defined in this document, never bypassing the Service layer (`backend-architecture.md` Section 5). |

---

## 24. Future Scalability

These standards are designed to support the platform's growth into Multi-Company, Multi-Branch, Multi-Warehouse, SaaS, Microservices, Public API, Mobile Applications, and AI Agent consumers without changing the underlying API philosophy, because:

- **Every surface shares one Service layer.** Whether a request arrives via Inertia, REST, or a future microservice boundary, it is ultimately handled by the same Service classes (`backend-architecture.md` Section 5), so adding a new consumer type never means reimplementing business logic.
- **Authorization is already multi-tenant-aware.** Company, Branch, and Warehouse isolation (Section 13) is built into the authorization model from the outset, so SaaS-scale multi-tenancy is a natural extension, not a redesign.
- **Versioning is defined before it is needed.** Because Section 19 establishes versioning discipline from the platform's first REST endpoint, public API consumers and mobile apps can be onboarded without a painful "introduce versioning" migration later.
- **Webhook and integration patterns are already standardized.** New partner integrations, ERP connections, or AI agent consumers plug into the same Webhook (Section 18) and Partner API (Section 2) patterns already defined, rather than requiring bespoke integration architecture each time.
- **Microservice-readiness is structural, not aspirational.** Because business logic already lives in a clean Service layer decoupled from the HTTP/Inertia delivery mechanism, extracting a specific module (e.g., Invoicing) into an independent service in the future is a boundary-drawing exercise, not a rewrite.

---

## 25. Anti-Patterns

- **Inconsistent response formats.** Different resources returning differently-shaped success or error envelopes forces every consumer to write resource-specific parsing logic, defeating the purpose of a unified API standard.
- **Business logic inside controllers.** Duplicating or fragmenting business rules across API surfaces (rather than the shared Service layer) risks the same operation behaving differently depending on which surface handled it — a direct violation of `backend-architecture.md` Section 4.
- **Random endpoint naming.** Inconsistent naming forces consumers to memorize exceptions rather than apply a predictable pattern, increasing integration time and defect risk.
- **Different pagination structures across modules.** Forces every consumer to write per-module pagination handling instead of one reusable pattern.
- **Returning sensitive data.** Exposing data a role should not see (Section 13, Section 21) is a direct security and business-rule violation, regardless of whether the frontend happens to hide it from view.
- **Using incorrect HTTP methods.** Misusing GET for mutations breaks caching assumptions and idempotency expectations that both browsers and API tooling rely on.
- **Ignoring validation.** Skipping any layer of Section 14's validation risks corrupt or inconsistent data entering the system, undermining the data integrity guarantees in `06-database-rules.md` Section 9.
- **Ignoring authorization.** Trusting client-side checks alone is a critical security failure; every request must be authorized server-side regardless of surface.
- **Breaking backward compatibility.** Silently changing an existing contract breaks every consumer depending on it, including consumers the platform team may not be directly aware of.
- **Hardcoded values.** Hardcoding status codes, role names, or configuration directly in API logic (rather than referencing the authoritative definitions in `06-database-rules.md` Section 12 and `backend-architecture.md` Section 10) creates drift between the API and the rest of the system.
- **Undocumented APIs.** An API surface without a documented, current contract cannot be safely integrated against or safely evolved, and inevitably degrades into tribal knowledge.

---

## 26. Best Practices

- Consistent response envelopes across every endpoint and every module.
- Clear, predictable, convention-driven endpoint naming.
- Correct HTTP method usage matching actual request effect.
- Strict, layered validation on every request.
- Role-based, server-side authorization enforced on every request, regardless of surface.
- Comprehensive logging across request, error, performance, and audit dimensions.
- Deliberate, disciplined versioning from the first REST endpoint onward.
- Design decisions made with future consumers (mobile, partner, AI agent) in mind, even while only the Inertia surface currently exists.
- Every API-facing business rule traceable back to `01-product-rules.md`, never invented ad hoc at the API layer.

---

## 27. Decision Guide

When a future developer (human or AI) needs to design a new API endpoint or surface, the following sequence is the permanent, authoritative decision path:

1. **Identify the Consumer:** Determine which API surface (Section 2) this endpoint belongs to — internal Inertia, future REST, partner, or webhook — since this determines authentication (Section 12) and versioning (Section 19) requirements.
2. **Name the Resource:** Apply Section 3's naming convention, checking consistency against existing, similar endpoints before finalizing.
3. **Choose the HTTP Method:** Apply Section 4 based on the endpoint's actual effect, not implementation convenience.
4. **Define the Request Shape:** Apply Section 5, including pagination/filtering/sorting parameters if the endpoint returns a collection (Sections 8–11).
5. **Define the Response Shape:** Apply Section 6's unified envelope for every possible outcome (success, validation error, business error, authorization error, not found).
6. **Define Validation:** Apply Section 14's layered validation, tracing every business validation rule back to `01-product-rules.md`.
7. **Define Authorization:** Apply Section 13, confirming the specific role/permission and ownership/scoping rules that govern this endpoint.
8. **Consider Performance:** Apply Section 20 — confirm the endpoint's underlying query is efficient and, if it returns a large or slow dataset, confirm it should be queued/paginated rather than synchronous.
9. **Consider Security:** Apply Section 21, confirming no sensitive data is exposed beyond what the endpoint's authorized consumers should see.
10. **Document the Contract:** The endpoint's request/response contract is documented clearly enough that a future consumer (internal or external) can integrate against it without needing to read the implementation.
11. **Validate Against Anti-Patterns:** Before finalizing, the new endpoint is checked against Section 25 to confirm no prohibited pattern has been introduced.

This sequence is the permanent, authoritative decision path for all future API work on INAKARA CRM.

---

## 28. Glossary

| Term | Definition |
|---|---|
| **Envelope** | The consistent outer response structure wrapping every API response, regardless of resource. |
| **Idempotency** | The property that repeating the same request produces the same result without unintended duplicate side effects. |
| **Partner API** | A scoped, credentialed API surface for specific third-party integration partners. |
| **Webhook** | An event-driven integration mechanism where the platform notifies (or is notified by) an external system of a relevant occurrence. |
| **Deprecation Window** | The defined period during which a deprecated API version continues to operate before removal. |
| **Scoping** | Restricting API-accessible data to the acting user's Company, Branch, or Warehouse context. |

## 29. References

- `PROJECT_CONSTITUTION.md` — supreme authority.
- `01-product-rules.md` — the business rules every API-exposed operation ultimately enforces.
- `backend-architecture.md` — the Service/Controller layers that implement these standards.
- `06-database-rules.md` — the schema and data-integrity foundation these standards expose safely.
- `frontend-architecture.md` — the primary current consumer of the Internal API surface.

---

*End of 07-api-standards.md — Version 1.0.0*
