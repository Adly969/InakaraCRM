# 08. Security Rules
## INAKARA CRM — Permanent Security Standards

**Status:** Binding — Subordinate to `PROJECT_CONSTITUTION.md`, `01-product-rules.md`, `02-design-principles.md`, `03-design-system.md`, `frontend-architecture.md`, `backend-architecture.md`, `06-database-rules.md`, `07-api-standards.md`
**Version:** 1.0.0
**Stack:** Laravel 13, PHP 8.3+, React 19, Inertia.js, MySQL, Spatie Permission, Laravel Fortify, Queue, Notification, Laravel Excel, DomPDF
**Scope:** This document defines security standards only. It contains no middleware, no policies, no authentication implementation, no authorization implementation, and no security code. Every current and future feature must comply with this document.

---

## Table of Contents

1. [Security Philosophy](#1-security-philosophy)
2. [Authentication Standards](#2-authentication-standards)
3. [Authorization Standards](#3-authorization-standards)
4. [User Account Security](#4-user-account-security)
5. [Session Security](#5-session-security)
6. [CSRF Protection](#6-csrf-protection)
7. [XSS Protection](#7-xss-protection)
8. [SQL Injection Protection](#8-sql-injection-protection)
9. [Input Validation Standards](#9-input-validation-standards)
10. [File Upload Security](#10-file-upload-security)
11. [Sensitive Data Protection](#11-sensitive-data-protection)
12. [Audit Log Standards](#12-audit-log-standards)
13. [Activity Log Standards](#13-activity-log-standards)
14. [API Security](#14-api-security)
15. [Permission Security](#15-permission-security)
16. [Data Isolation](#16-data-isolation)
17. [Security Logging](#17-security-logging)
18. [Backup Security](#18-backup-security)
19. [Import & Export Security](#19-import--export-security)
20. [Notification Security](#20-notification-security)
21. [Rate Limiting Strategy](#21-rate-limiting-strategy)
22. [Error Handling Security](#22-error-handling-security)
23. [Secret Management](#23-secret-management)
24. [Compliance Considerations](#24-compliance-considerations)
25. [Future Security Strategy](#25-future-security-strategy)
26. [Security Anti-Patterns](#26-security-anti-patterns)
27. [Security Best Practices](#27-security-best-practices)
28. [Security Decision Guide](#28-security-decision-guide)
29. [Glossary](#29-glossary)
30. [References](#30-references)

---

## 1. Security Philosophy

**Security First.** INAKARA CRM stores the operational and financial lifeblood of the businesses that use it — customer relationships, pricing, and payment history. Security is treated as a foundational requirement of every feature, not a later hardening pass, consistent with `PROJECT_CONSTITUTION.md` Section 10.

**Least Privilege.** Every user, role, and integration is granted only the access genuinely required for its business responsibility (per `01-product-rules.md` Section 8), and nothing more. Access is additive and explicit, never broad-by-default.

**Defense in Depth.** No single control is trusted as the sole safeguard. Validation, authorization, database constraints, and logging each independently reduce risk, so that the failure of any one layer does not result in a full compromise.

**Secure by Default.** Every new feature, table, and endpoint starts in its most restrictive, safest configuration; access is opened up deliberately and explicitly, never assumed open until proven otherwise.

**Fail Secure.** When a system component fails, is uncertain, or encounters an unexpected state, it denies access or halts the operation rather than defaulting to permissive behavior.

**Zero Trust Mindset.** No request is trusted merely because it originates from an authenticated session, an internal network, or a previously-verified client. Every request is independently authenticated and authorized on its own merits (per Section 2 and Section 3), every time.

**Why Enterprise Software Must Prioritize Security.** INAKARA CRM's credibility as an enterprise platform depends on customers trusting it with commercially sensitive data. A security failure does not just cost data — it costs the trust that makes the CRM viable as a business system at all. Security discipline is therefore treated as inseparable from product quality, not a separate concern.

---

## 2. Authentication Standards

- **Session Authentication:** The standard authentication mechanism for the primary Inertia-driven web application, managed through Laravel Fortify, consistent with `07-api-standards.md` Section 12.
- **Password Policy:** A minimum complexity standard (length, character variety) is enforced at account creation and password change; passwords are checked against common/breached password lists where feasible, rather than relying on complexity rules alone.
- **Login Rules:** Failed login attempts are rate-limited (Section 21) and tracked; repeated failures against a single account trigger increasing friction (delay, temporary lockout) rather than unlimited retry.
- **Password Reset:** Reset requests are handled through a time-limited, single-use token sent to the verified account email, never through a mechanism that reveals whether a given email is registered in a way that aids account enumeration.
- **Remember Me:** Where supported, a "remember me" capability uses a separate, long-lived, securely-stored token distinct from the standard session, revocable independently of the standard session.
- **Session Lifetime:** A defined maximum session lifetime is enforced regardless of activity, ensuring a session cannot persist indefinitely even if never explicitly logged out.
- **Session Expiration:** Sessions expire automatically after the defined lifetime or idle period (Section 5), requiring re-authentication.
- **Multi-Device Login Strategy:** Multiple concurrent sessions across devices are permitted by default for normal business use (a user working from desktop and mobile), while remaining independently visible and revocable per device/session (Section 5).
- **Future MFA Support:** The authentication architecture is designed to accommodate multi-factor authentication as an additional, optional (or role-mandated, e.g., for Owner/Finance) verification step, without restructuring the existing authentication flow when introduced.

---

## 3. Authorization Standards

- **Role-Based Access Control (RBAC):** The primary authorization model, implemented through Spatie Permission, governing what each role (per `01-product-rules.md` Section 8 and `backend-architecture.md` Section 10) can access and perform.
- **Permission-Based Access:** Beneath roles, discrete named permissions govern specific capabilities, allowing fine-grained control and custom role composition beyond the fixed role list as the business evolves.
- **Policies:** Record-level authorization (e.g., can this specific user modify this specific Lead) is always enforced through Policy classes, per `backend-architecture.md` Section 9, applied consistently across every API surface (`07-api-standards.md` Section 13).
- **Ownership Rules:** Where business rules define explicit record ownership (e.g., a Lead's assigned Sales owner, per `01-product-rules.md` Rule 9), authorization enforces that ownership as part of the access decision, not merely as a UI convenience.
- **Branch Isolation:** Users are authorized only for data within their assigned Branch scope where branch-level restriction applies, per `06-database-rules.md` Section 15.
- **Company Isolation:** Users are authorized only for data within their assigned Company, forming the outermost isolation boundary in the current and future multi-company architecture.
- **Warehouse Isolation:** Users with Warehouse-scoped roles (e.g., Gudang) are authorized only for their relevant Warehouse's Inventory and Delivery data.
- **Dynamic Permissions:** Permission checks always resolve against the live, centrally-managed permission configuration; permissions are never hardcoded into application logic (see Section 26).
- **Rule:** No component, page, or endpoint determines access by inspecting a role name string directly (e.g., checking for the literal string "sales"); all authorization resolves through the centrally defined permission system, ensuring role/permission changes take effect everywhere at once.

---

## 4. User Account Security

- **Password Complexity:** Enforced at creation and change per Section 2's Password Policy, applied consistently across every account creation pathway (self-registration if applicable, admin-created accounts, import-created accounts).
- **Password Hashing:** Every password is hashed using Laravel's standard, secure hashing algorithm; passwords are never stored, logged, transmitted, or displayed in plain or reversible form, at any layer of the system.
- **Account Activation:** New accounts follow a defined activation flow (e.g., email verification or admin approval) before gaining full access, preventing unverified or unauthorized account use.
- **Email Verification:** Required before an account can perform sensitive actions, ensuring the platform has a verified communication channel for security-relevant notices (password reset, security alerts).
- **Inactive Users:** Accounts inactive beyond a defined business threshold are flagged for review; inactivity itself is not an automatic deactivation trigger but is surfaced for administrative decision.
- **Locked Accounts:** Accounts are locked automatically after repeated failed authentication attempts (Section 2) and can be locked manually by an authorized administrator; locked accounts require explicit administrative or verified self-service unlock.
- **Account Recovery:** Recovery flows (password reset, locked account unlock) always require verification through a channel the legitimate account owner controls, never a channel that could be manipulated by an attacker with only partial account knowledge.

---

## 5. Session Security

- **Session Timeout:** A maximum session duration is enforced regardless of activity (Section 2).
- **Idle Timeout:** A shorter idle-based timeout logs out sessions with no activity for a defined period, reducing exposure from an unattended, authenticated device.
- **Session Regeneration:** The session identifier is regenerated at authentication (login) and at any privilege change, preventing session fixation.
- **Logout:** Explicit logout immediately and fully invalidates the session server-side, not merely clearing client-side state.
- **Force Logout:** An authorized administrator (or the user themselves, from another session) can force-terminate a specific active session — used, for example, when a device is lost or an account is suspected compromised.
- **Concurrent Session Policy:** Multiple concurrent sessions are permitted by default (Section 2), with each session independently visible and revocable; a stricter single-session policy may be applied to specific high-privilege roles (e.g., Owner, Finance) if the business later requires it.
- **Session Invalidation:** All active sessions for an account are invalidated automatically on password change and on account lock, ensuring a compromised credential cannot continue to be used via an already-established session.

---

## 6. CSRF Protection

- **CSRF Protection Philosophy:** Every state-changing request from the session-authenticated Inertia application is protected against cross-site request forgery, since session-based authentication is inherently vulnerable to CSRF without this protection.
- **Token Validation:** Laravel's built-in CSRF token mechanism is enforced on every state-changing request from the web application, verified before any Controller logic executes.
- **Trusted Origins:** Cross-origin request handling (CORS) is configured explicitly and restrictively, permitting only genuinely trusted origins (e.g., a future first-party mobile app's API domain), never a permissive wildcard for authenticated endpoints.
- **Secure Form Submissions:** Every form-driven mutation in the application relies on this CSRF protection as a baseline safeguard, in addition to the authorization checks in Section 3.

---

## 7. XSS Protection

- **Input Validation:** Every input is validated for expected type and format (Section 9) before storage, reducing the surface area for malicious payloads to persist.
- **Output Escaping:** All dynamic content rendered to the browser is escaped by default through React/Inertia's standard rendering behavior; raw, unescaped HTML injection is never used for user- or externally-supplied content.
- **HTML Rendering:** Any feature that must render user-supplied HTML (e.g., a rich text note) passes that content through an explicit, deliberate sanitization step before rendering — raw HTML is never rendered directly from stored user input.
- **Rich Text Strategy:** Rich text fields (if introduced) are sanitized both at storage (removing genuinely dangerous content) and at render (escaping/sanitizing again on output), applying defense in depth (Section 1) rather than relying on a single sanitization point.
- **Stored XSS:** Prevented primarily through the sanitization strategy above, ensuring malicious scripts cannot be persisted and later served to other users.
- **Reflected XSS:** Prevented by never rendering unescaped request parameters directly back into a page response.
- **DOM XSS:** Prevented by avoiding unsafe client-side patterns that inject unsanitized data into the DOM outside of React's standard, escaping rendering path.

---

## 8. SQL Injection Protection

- **Prepared Statements:** All database access uses parameter binding, whether through Eloquent or the query builder, ensuring user input is never concatenated directly into a SQL string.
- **ORM Usage:** Eloquent is the default and preferred means of data access, per `backend-architecture.md` Section 6–7, inherently applying parameter binding.
- **Query Builder:** Used for more complex queries not well-suited to Eloquent, always via parameterized methods, never via raw string concatenation of user input.
- **Validation:** Input validation (Section 9) provides an additional layer of defense, ensuring malformed or unexpected input is rejected before it reaches any query.
- **Unsafe Query Prevention:** Raw SQL queries are avoided by default; where genuinely necessary for performance or complex reporting, raw queries are always parameterized and subject to explicit review, never accepting unescaped user input directly into raw SQL.

---

## 9. Input Validation Standards

- **Server-Side Validation:** The authoritative validation layer for every request, performed via Form Request classes (`backend-architecture.md` Section 8), never skipped or assumed satisfied by client-side checks alone.
- **Client-Side Validation:** Provided for user experience (immediate feedback via React Hook Form/Zod, per `frontend-architecture.md` Section 8), but never trusted as a security boundary — every request is re-validated server-side regardless of client-side outcome.
- **Sanitization:** Input is normalized and cleaned of unexpected or dangerous content (e.g., trimming, encoding normalization) as part of the validation pipeline, before business logic processes it.
- **Normalization:** Data formats (dates, phone numbers, currency) are normalized to a consistent internal representation at the point of entry, preventing downstream inconsistency and validation gaps.
- **Business Validation:** Beyond structural correctness, business-rule validation (per `01-product-rules.md`) is enforced in the Service layer, ensuring data is not just well-formed but business-valid.
- **Database Validation:** Final structural and referential integrity is enforced at the database layer (`06-database-rules.md` Section 9) as the last line of defense against invalid data, regardless of how it was submitted.

---

## 10. File Upload Security

- **Allowed File Types:** Every upload context defines an explicit allow-list of accepted file types; files are validated by actual content inspection where feasible, not solely by file extension or declared MIME type, which can be spoofed.
- **Maximum Size:** Every upload context defines an explicit maximum file size, preventing both storage abuse and denial-of-service risk from oversized uploads.
- **Virus Scanning Strategy:** Uploaded files, particularly those later shared or downloaded by other users, are scanned for malicious content where the business risk and infrastructure justify it (e.g., documents attached to Customer records), before being made available to other users.
- **File Naming:** Uploaded files are renamed to a system-generated, unpredictable identifier upon storage, never trusting the original client-provided filename for storage paths, preventing path traversal and naming collision risks.
- **Storage Location:** Files are stored through Laravel's Storage abstraction (`backend-architecture.md` Section 21), in a location not directly, predictably guessable or browsable from outside the application.
- **Private vs. Public Files:** Business documents (invoices, quotations, customer attachments) are stored as private by default, requiring authenticated, authorized access to retrieve; only content explicitly intended for public access (if any) is stored publicly.
- **Download Authorization:** Every file download request is subject to the same authorization checks (Section 3) as viewing the record the file belongs to — a file's URL, even if guessed or leaked, does not itself grant access without proper authorization.

---

## 11. Sensitive Data Protection

- **Personally Identifiable Information (PII):** Customer and employee contact details, identification information, and similar personal data are treated as sensitive by default, consistent with `06-database-rules.md` Section 17.
- **Customer Data:** Access is scoped to roles with a genuine business need (per `01-product-rules.md` Section 8); Customer Service, for example, accesses history necessary for service resolution without necessarily accessing full financial negotiation detail reserved for Sales/Finance.
- **Employee Data:** Access is restricted to Owner, Admin, and relevant management roles, never broadly visible across all roles by default.
- **Financial Data:** Pricing, discount, invoice, and payment data is restricted per role responsibility (Section 3), with Finance and Owner holding the broadest financial visibility.
- **Encryption:** Highly sensitive fields (e.g., any stored payment credential, should the system ever store one directly rather than via a payment gateway token) are encrypted at rest, per `06-database-rules.md` Section 17.
- **Masking:** Where a role needs partial visibility of a sensitive value without full access (e.g., a partially masked payment reference), masking is applied consistently rather than granting full access for partial need.
- **Data Visibility Rules:** Every sensitive field's visibility is explicitly mapped to the roles genuinely entitled to see it (Section 15), never left as an unreviewed default of "visible to all authenticated users."

---

## 12. Audit Log Standards

Every audit log entry, for every change to a core business object, captures:

| Field | Purpose |
|---|---|
| **Who** | The specific, identified user who performed the action — never a shared or ambiguous account. |
| **When** | The exact timestamp of the action. |
| **Where** | The originating context — IP address and, where feasible, device/browser identification (below). |
| **What Changed** | The specific field(s) or state transition affected. |
| **Previous Value** | The value before the change. |
| **New Value** | The value after the change. |
| **IP Address** | Captured for every audited action, supporting investigation of suspicious patterns. |
| **Device** | Captured where feasible, aiding investigation of multi-device or anomalous access patterns. |
| **Browser** | Captured where feasible, alongside device information. |
| **Company** | The Company context the action occurred within, per `06-database-rules.md` Section 15. |
| **Branch** | The Branch context the action occurred within, where applicable. |

This is consistent with and extends `01-product-rules.md` Section 11 and `06-database-rules.md` Section 10; audit log entries are immutable and retained indefinitely, per Section 18 (Backup) retention alignment.

---

## 13. Activity Log Standards

The following events are always recorded in the business-facing Activity Log or Security Log (Section 17), as appropriate:

- User login and logout.
- Create, Update, and Delete (soft delete) actions on core business objects.
- Approval actions (e.g., Quotation approval, discount exception approval).
- Export operations, including what data was exported and by whom.
- Import operations, including outcome and any validation failures.
- Role changes (a user's assigned role being modified).
- Permission changes (a role's or user's specific permissions being modified).

**Rule:** Role and permission changes are treated as especially sensitive activity log events, always attributed to a specific administrative user and always visible for security review, since they directly affect the platform's access control posture.

---

## 14. API Security

Applied consistently across every API surface defined in `07-api-standards.md` Section 2:

- **Authentication:** Enforced on every request to a protected resource, per Section 2.
- **Authorization:** Enforced on every request, per Section 3, regardless of surface.
- **Rate Limiting:** Applied per Section 21, protecting every API surface from abuse.
- **HTTPS:** Enforced exclusively across every API surface, with no unencrypted access permitted.
- **Input Validation:** Enforced per Section 9, on every request regardless of surface.
- **Response Filtering:** Every response is filtered to include only data the requesting user's role and permissions entitle them to see (Section 11, Section 15), never relying on the frontend to hide unauthorized fields.
- **Token Management:** Future API tokens and OAuth credentials (`07-api-standards.md` Section 12) are issued, stored, and revoked securely, with a defined expiry and rotation strategy.
- **API Abuse Prevention:** Anomalous usage patterns are logged and monitored (Section 17), beyond baseline rate limiting.

---

## 15. Permission Security

- **Menu Visibility:** Navigation items are filtered centrally based on the resolved permission set (`frontend-architecture.md` Section 11), never displayed and then merely disabled if inaccessible.
- **Button Visibility:** Action buttons for operations a user cannot perform are hidden by default, consistent with `02-design-principles.md` Section 13's disabled-state guidance, except where a disabled-but-visible state is itself informative.
- **Page Access:** Every page-level route enforces server-side authorization independently of frontend navigation filtering — a hidden menu item is a UX convenience, not a security boundary.
- **Action Access:** Every mutating action is independently authorized server-side, per Section 3, regardless of what the frontend displays.
- **Record Ownership:** Access to individual records respects ownership rules (Section 3) beyond broad role-level access, where the business rule requires it.
- **Data Visibility:** Row-level and column-level data visibility both respect the role/permission model — a user permitted to see a list of Deals may still be restricted from certain sensitive fields within those records (below).
- **Field-Level Permissions:** Where a specific field is more sensitive than the record as a whole (e.g., cost price versus sale price), field-level visibility is enforced explicitly and independently of record-level access.

---

## 16. Data Isolation

- **Company Isolation:** The outermost data boundary; no query, export, report, or API response ever returns data belonging to a different Company than the acting user's context, per `06-database-rules.md` Section 15.
- **Branch Isolation:** Enforced beneath Company isolation for roles and data scoped to a specific Branch.
- **Warehouse Isolation:** Enforced for Inventory- and Delivery-related data, scoped to the relevant Warehouse.
- **User Ownership:** The finest-grained isolation layer, enforced per Section 3's ownership rules, for data explicitly owned by an individual user (e.g., an assigned Lead).
- **Future SaaS Tenant Isolation:** The Company-level isolation boundary already established is the direct foundation for full multi-tenant SaaS isolation; no architectural change is required to reach tenant-level isolation, only the formalization of Company as the tenant boundary, per `06-database-rules.md` Section 20.

---

## 17. Security Logging

Distinct from the business-facing Activity Log (Section 13), the Security Log specifically captures:

- **Authentication Failures:** Every failed login attempt, with sufficient context (account attempted, IP, timestamp) to detect brute-force or credential-stuffing patterns.
- **Authorization Failures:** Every denied access attempt (403), particularly repeated denials against the same resource, which may indicate probing or privilege escalation attempts.
- **Suspicious Activity:** Anomalous patterns — unusual access volume, access outside normal patterns, repeated failed actions — flagged for review.
- **System Errors:** Security-relevant system errors (e.g., failed encryption/decryption operations) are logged distinctly from routine application errors.
- **Security Events:** Password changes, MFA changes (once introduced), account lockouts, and forced logouts are all logged as discrete security events.
- **Critical Actions:** High-impact actions (role changes, permission changes, bulk data export, account deletion) are logged with elevated visibility for security review, per Section 13.

---

## 18. Backup Security

- **Backup Encryption:** Backups containing business and personal data are encrypted at rest, consistent with the sensitivity of the data they contain (Section 11).
- **Backup Retention:** Retention duration is defined explicitly, balancing recovery needs against storage cost and future compliance obligations (Section 24), per `06-database-rules.md` Section 19.
- **Restore Authorization:** Restoring a backup, or restoring individual records from a backup, is restricted to explicitly authorized administrative roles and is itself an audited action (Section 12).
- **Backup Storage:** Backups are stored separately from the primary production environment, reducing the risk that a single compromise affects both live data and its recovery source.
- **Disaster Recovery:** Recovery procedures are documented and periodically tested, ensuring backup security controls do not themselves prevent legitimate, timely recovery when genuinely needed.

---

## 19. Import & Export Security

- **Import Authorization:** Restricted to roles explicitly permitted to bulk-create or bulk-modify the relevant business data, per Section 3.
- **Export Authorization:** Restricted similarly; export access does not automatically follow from read access to a resource, since export represents a higher-risk data exposure event (mass extraction) than viewing individual records.
- **Sensitive Data Masking:** Exports respect the same field-level visibility rules as the live application (Section 15); a role that cannot see a sensitive field in the UI does not receive it in an export.
- **Audit Logging:** Every import and export operation is logged per Section 12/13, including who performed it, what data was involved, and the outcome.
- **Bulk Operation Limits:** Import and export operations are subject to defined size/volume limits and rate limiting (Section 21), preventing both accidental system strain and deliberate mass data exfiltration.

---

## 20. Notification Security

- **Email Verification:** Sensitive notifications (password reset, security alerts) are sent only to verified email addresses, per Section 4.
- **Sensitive Email Content:** Notification content avoids including highly sensitive data (e.g., full financial detail, credentials) directly in email bodies, minimizing exposure if an email account is compromised; notifications instead direct the user to view sensitive detail within the authenticated application.
- **Notification Visibility:** In-app (database channel) notifications respect the same role/permission visibility rules as the underlying data they reference.
- **Future WhatsApp:** When introduced, subject to the same sensitive-content-minimization principle as email.
- **Future Push Notification:** Similarly subject to the same content-minimization principle, given push notifications may be visible on a locked device screen.

---

## 21. Rate Limiting Strategy

| Action | Rationale |
|---|---|
| **Login Attempts** | Prevents brute-force and credential-stuffing attacks against user accounts. |
| **Password Reset** | Prevents abuse of the reset flow for account enumeration or harassment (repeated reset emails). |
| **API Requests** | Protects overall platform availability and fairness across consumers, per `07-api-standards.md` Section 20. |
| **Export** | Prevents mass, rapid data extraction beyond normal business usage patterns. |
| **Import** | Prevents system strain from excessive or automated bulk import attempts. |
| **Search** | Protects against search being used as a vector for excessive, expensive database load. |
| **Bulk Actions** | Prevents abuse of bulk-update/bulk-action endpoints to cause unintended mass changes or system strain. |

Rate limits are defined per action type and, where relevant, per user/role, with limits tuned to allow genuine business usage patterns while meaningfully constraining abuse.

---

## 22. Error Handling Security

The following are never exposed to any API or application response, in any environment reachable by real users:

- **SQL Errors:** Raw database error messages, which may reveal schema structure or query logic.
- **Stack Traces:** Internal execution detail that reveals implementation structure.
- **Server Paths:** File system paths that reveal server structure or configuration.
- **Secrets:** API keys, credentials, or tokens, under any circumstance, in any error output.
- **Internal IDs:** Where an internal identifier's exposure could aid enumeration or probing attacks, exposure is limited to what genuinely serves the consumer's need.
- **Sensitive Configuration:** Environment or infrastructure configuration detail.

Instead, every error surfaces a clear, user-friendly, business-appropriate message (per `07-api-standards.md` Section 7), while full technical detail is captured only in secure, internal logging (Section 17), never in the response itself.

---

## 23. Secret Management

- **Environment Variables:** All environment-specific configuration and secrets are managed through environment variables, never hardcoded into application source code, per Section 26.
- **API Keys:** Third-party API keys are stored as environment configuration, scoped to the minimum necessary privilege for their integration purpose, and rotated periodically or immediately upon suspected exposure.
- **SMTP:** Email delivery credentials are managed as environment configuration, never committed to source control.
- **Third-Party Credentials:** Every external integration credential (payment gateway, WhatsApp, ERP, etc., per `07-api-standards.md` Section 23) follows the same environment-based, non-committed storage standard.
- **Encryption Keys:** The application's core encryption key is managed with the highest sensitivity, distinct from ordinary configuration, with a defined rotation and recovery strategy.
- **Storage Philosophy:** No secret of any kind is ever committed to source control, logged, or exposed in any client-facing response or error message; secrets exist only in secured environment configuration and, where applicable, a dedicated secrets management system as the platform scales.

---

## 24. Compliance Considerations

- **Data Privacy:** Customer and employee personal data is handled with privacy-by-design principles — collected only where genuinely needed for a business purpose, and access-restricted per Section 11 and Section 15.
- **Customer Confidentiality:** Commercial terms, pricing, and negotiation history are treated as confidential business information, access-restricted per role responsibility.
- **Retention Policy:** Data retention duration is defined explicitly per data category, balancing legitimate business/audit need against the principle of not retaining sensitive data longer than necessary.
- **Data Deletion Policy:** Where a legitimate deletion request must be honored (e.g., regulatory requirement in a future market or industry), the platform's soft-delete-by-default approach (`06-database-rules.md` Section 11) is understood to require a distinct, deliberate hard-deletion capability for genuine compliance-driven erasure, separate from routine soft delete.
- **Audit Requirements:** The Audit Log (Section 12) is designed to satisfy typical enterprise audit expectations — complete, immutable, and attributable — from the outset, rather than retrofitted when a specific compliance requirement arises.
- **Future Compliance Readiness:** As the platform expands into regulated industries (e.g., Healthcare, per `PROJECT_CONSTITUTION.md` Section 14), the existing data isolation (Section 16), audit (Section 12), and access control (Section 3) foundations are designed to support additional, industry-specific compliance requirements as an extension, not a redesign.

---

## 25. Future Security Strategy

This security architecture is designed to remain valid as INAKARA CRM expands into Multi-Company, SaaS, REST API, Mobile Apps, AI Integration, External Integrations, and a Public API, without requiring a security redesign, because:

- **Isolation is already layered and hierarchical.** Company, Branch, Warehouse, and User-ownership isolation (Section 16) form a structure that scales naturally into full multi-tenant SaaS isolation.
- **Authorization is centralized and permission-driven**, not hardcoded (Section 3), so new consumer types (mobile, partner, AI agent) inherit the same authorization guarantees as the current web application without reimplementation.
- **API security standards are already defined** (Section 14, `07-api-standards.md`), so REST, Public API, and Mobile API surfaces are built against an existing, proven security contract rather than an ad hoc one.
- **Secret management and audit logging are infrastructure-level concerns**, already designed to scale with additional integrations (Section 23) without redesign.
- **AI Integration is explicitly bounded by existing sensitive data rules** (Section 11), ensuring that as AI services are introduced (`07-api-standards.md` Section 23), what data may be sent to external AI providers is governed by the same access and sensitivity standards already established, not a new, separately-negotiated policy.

---

## 26. Security Anti-Patterns

- **Hardcoded passwords.** Any credential embedded directly in code is permanently exposed to anyone with source access and cannot be rotated without a code change — a fundamental violation of Section 23.
- **Hardcoded API keys.** Same risk as above, extended to third-party integration credentials.
- **Plain text passwords.** Storing, logging, or transmitting passwords unhashed makes every account instantly compromised the moment any storage or log is exposed.
- **Disabling CSRF.** Removing CSRF protection for convenience reopens the application to cross-site request forgery on every state-changing action.
- **Ignoring authorization.** Relying on hidden UI elements alone, without server-side enforcement, means any user capable of crafting a direct request bypasses all access control.
- **Ignoring validation.** Skipping server-side validation because client-side validation "already handled it" assumes a trustworthy client, which is never a safe assumption.
- **Public file storage for sensitive documents.** Storing invoices, quotations, or customer attachments in a publicly accessible location means anyone with the URL — guessed, leaked, or indexed — can access confidential business data.
- **Returning sensitive data in APIs.** Including fields a role should not see "just in case the frontend needs it later" exposes data the moment any consumer inspects the raw response.
- **Using raw SQL without necessity.** Increases SQL injection risk and bypasses the safety guarantees of the ORM/query builder for no genuine benefit.
- **Trusting client-side validation.** Assumes the request always originates from the legitimate, unmodified frontend, which is never guaranteed.
- **Exposing stack traces.** Reveals internal implementation detail that materially aids an attacker in crafting further attacks.
- **Exposing `.env` data.** Directly exposes every secret the environment configuration holds (Section 23), a catastrophic, full-system compromise.
- **Logging passwords.** Even temporarily, in debug or error logs, defeats the purpose of secure hashing and creates a plain-text exposure point.
- **Ignoring audit logs.** Skipping audit logging for "minor" operations undermines the completeness guarantee the Audit Log depends on (Section 12), and the operation later deemed "minor" is often precisely the one under investigation.
- **Weak session management.** Failing to regenerate session identifiers, enforce timeouts, or invalidate sessions on password change leaves authenticated sessions exploitable well beyond their intended lifetime.

---

## 27. Security Best Practices

- Apply the Principle of Least Privilege to every role, permission, and integration credential.
- Default every new feature, table, and endpoint to its most restrictive, Secure-by-Default configuration.
- Apply strong, layered validation (client convenience, server authority, database final guarantee) to every input.
- Apply encryption to genuinely sensitive data at rest, and enforce HTTPS for all data in transit.
- Apply Audit Logging consistently and completely across every core business object and critical action.
- Apply Role-Based Access Control consistently, resolved dynamically, never hardcoded.
- Apply Defense in Depth — never rely on a single control as the sole safeguard for any risk.
- Apply Secure Session Management — regeneration, timeout, and invalidation handled consistently.
- Apply proper File Security — private by default, unpredictable naming, authorized access only.
- Apply Continuous Monitoring — security logs (Section 17) are reviewed as an ongoing practice, not only after an incident.

---

## 28. Security Decision Guide

When a future developer (human or AI) builds a new feature, module, or integration, the following sequence is the permanent, authoritative security decision path:

1. **Authentication Required?** Any feature accessing non-public data or performing any mutation requires authentication — the default assumption is "authentication required" unless a feature is explicitly, deliberately public.
2. **Authorization Required?** Every feature accessing or mutating a specific resource requires an explicit authorization check (Section 3) — the default assumption is "authorization required," scoped to the specific role/permission/ownership rule that governs that resource, per `01-product-rules.md`.
3. **Audit Logs Mandatory?** Any feature that creates, updates, or deletes a core business object (per `06-database-rules.md` Section 1's Core Table definition) requires audit logging (Section 12) — this is never optional for core business data.
4. **Encryption Necessary?** Any feature handling data classified as sensitive (Section 11) — PII, financial data, credentials — requires encryption at rest and enforced HTTPS in transit; features handling only non-sensitive, already-public-appropriate data do not require additional encryption beyond standard transport security.
5. **Rate Limiting Applicable?** Any feature exposing an endpoint vulnerable to abuse — authentication, search, export, import, bulk actions, or any publicly reachable endpoint — requires rate limiting (Section 21).
6. **Securing a New Module:** A new business module (e.g., a new industry-specific vertical module, per `PROJECT_CONSTITUTION.md` Section 6) is secured by applying Sections 2–16 in full — authentication, authorization, isolation, and audit logging are never considered optional "for now" additions to be layered in later.
7. **Securing a Future Integration:** Any new external integration (Section 25, `07-api-standards.md` Section 23) is evaluated against Section 23 (Secret Management), Section 14 (API Security), and Section 11 (Sensitive Data Protection) before implementation begins, ensuring the integration inherits the platform's existing security posture rather than introducing a parallel, weaker one.

This sequence is the permanent, authoritative security decision path for all future development on INAKARA CRM.

---

## 29. Glossary

| Term | Definition |
|---|---|
| **RBAC** | Role-Based Access Control; the model governing how roles map to permissions. |
| **PII** | Personally Identifiable Information; data that can identify a specific individual. |
| **CSRF** | Cross-Site Request Forgery; an attack tricking an authenticated user's browser into making an unintended request. |
| **XSS** | Cross-Site Scripting; an attack injecting malicious script into content viewed by other users. |
| **Defense in Depth** | A security approach layering multiple independent controls so no single failure results in full compromise. |
| **Fail Secure** | A design principle where failure states default to denying access rather than permitting it. |
| **Data Isolation** | Restricting data visibility and access to its correct Company, Branch, Warehouse, or ownership scope. |

## 30. References

- `PROJECT_CONSTITUTION.md` — supreme authority; Section 10 establishes security-by-default as a constitutional principle.
- `01-product-rules.md` — role definitions and business rules this security model enforces.
- `backend-architecture.md` — Section 9 (Authorization) and Section 29 (Security Standard) implemented in code per this document's standards.
- `06-database-rules.md` — Section 15 (Multi-Company Strategy) and Section 17 (Security Strategy) this document extends.
- `07-api-standards.md` — Section 12–14 and Section 21 this document extends with full security rationale.

---

*End of 08-security-rules.md — Version 1.0.0*
