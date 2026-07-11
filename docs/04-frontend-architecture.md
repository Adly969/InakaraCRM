# Frontend Architecture
## INAKARA CRM — Permanent Frontend Engineering Standard

**Status:** Binding — Subordinate to `PROJECT_CONSTITUTION.md`, `01-product-rules.md`, `02-design-principles.md`, `03-design-system.md`
**Version:** 1.0.0
**Stack:** Laravel 13, React 19, Inertia.js, TypeScript, Vite, Tailwind CSS v4, shadcn/ui, Radix UI, React Hook Form, Zod, TanStack Table, Recharts, Lucide Icons
**Scope:** This document defines frontend architecture, structure, and engineering standards only. It contains no code, no components, and no UI implementation.

---

## Table of Contents

1. [Architecture Goals & Core Principles](#1-architecture-goals--core-principles)
2. [Folder Structure](#2-folder-structure)
3. [Feature-Based Architecture](#3-feature-based-architecture)
4. [Shared Components](#4-shared-components)
5. [Layout Architecture](#5-layout-architecture)
6. [Routing](#6-routing)
7. [Page Architecture](#7-page-architecture)
8. [Form Architecture](#8-form-architecture)
9. [State Management](#9-state-management)
10. [API Layer](#10-api-layer)
11. [Permission Rendering](#11-permission-rendering)
12. [Table Architecture](#12-table-architecture)
13. [Chart Architecture](#13-chart-architecture)
14. [Loading Strategy](#14-loading-strategy)
15. [Notification](#15-notification)
16. [Modal Standard](#16-modal-standard)
17. [Responsive Rules](#17-responsive-rules)
18. [Performance Rules](#18-performance-rules)
19. [Accessibility](#19-accessibility)
20. [Animation Rules](#20-animation-rules)
21. [Naming Convention](#21-naming-convention)
22. [Coding Rules](#22-coding-rules)
23. [Dependency Rules](#23-dependency-rules)
24. [Future Scalability](#24-future-scalability)
25. [Glossary](#25-glossary)
26. [References](#26-references)

---

## 1. Architecture Goals & Core Principles

The frontend architecture exists to keep INAKARA CRM maintainable by a growing team, over many years, across many industries, without accumulating disorder. Every structural decision serves one of these principles:

| Principle | Meaning |
|---|---|
| **Scalable** | Adding a new feature (module, page, or entire industry vertical) must not require restructuring existing code. |
| **Reusable** | Common patterns (tables, forms, dialogs) are built once as shared building blocks, never duplicated per feature. |
| **Predictable** | Any engineer, human or AI, can locate and understand code by knowing the convention, not by exploring. |
| **Maintainable** | Code is structured so that fixing or extending one feature carries minimal risk of breaking another. |
| **Feature-First** | The codebase is organized primarily around business capabilities (Leads, Deals, Invoices), not technical layers. |
| **No Duplicated Code** | Shared logic and UI are always extracted to shared locations; copy-paste across features is prohibited. |
| **Composition Over Inheritance** | Complex UI and behavior are built by composing small, focused units, never through deep inheritance chains. |
| **Single Responsibility** | Every file, component, and hook does exactly one thing, and does it clearly. |

---

## 2. Folder Structure

The following structure is the permanent, binding layout for the `resources/` frontend source tree.

```
resources/
  js/
    components/
      ui/                 → shadcn/ui primitives (button, input, dialog, etc.)
      layout/              → App shell components (sidebar shell, topbar shell)
      shared/               → Cross-feature, reusable business-agnostic components
        forms/
        tables/
        charts/
        dialogs/
        cards/
      dashboard/            → Dashboard-specific composition components
      crm/                  → CRM-domain shared components used across multiple modules
        sidebar/
        navbar/

    modules/                → Feature-first business modules (see Section 3)
      dashboard/
      lead/
      customer/
      quotation/
      order/
      production/
      inventory/
      delivery/
      invoice/
      report/
      analytics/
      settings/

    pages/                  → Inertia page entry points, mirroring modules
      dashboard/
      lead/
      customer/
      quotation/
      order/
      production/
      inventory/
      delivery/
      invoice/
      report/
      analytics/
      settings/

    hooks/                  → Global, cross-feature hooks
    services/               → API/service layer abstractions (see Section 10)
    lib/                    → Framework-level helpers (Inertia config, query client, etc.)
    types/                  → Global/shared TypeScript types
    constants/              → Global constants (enums, status maps, route names)
    config/                 → App-level configuration (navigation config, permission config)
    utils/                  → Pure, generic utility functions
    contexts/               → Global React Context providers (see Section 9)
```

**Rule:** No file is placed at the root of `components/`, `modules/`, or `pages/` without belonging to one of the defined subfolders. Any new top-level folder requires an explicit amendment to this document.

---

## 3. Feature-Based Architecture

Every business capability is implemented as a self-contained module under `modules/`. Each module owns its own internal structure:

```
modules/lead/
  components/     → Lead-specific components (LeadCard, LeadStatusBadge, etc.)
  hooks/          → Lead-specific hooks (useLeadFilters, useLeadAssignment)
  types/          → Lead-specific TypeScript types
  services/       → Lead-specific API/service functions
  pages/          → (optional, if not using the top-level pages/ mirror — see note below)
```

**Why Feature-First:** A furniture-specific concern (e.g., custom order specification) and a generic CRM concern (e.g., contact management) must be able to evolve independently. Organizing by feature — Lead, Customer, Quotation — rather than by technical layer (all components together, all hooks together) means an engineer working on Quotation logic only ever needs to open the `quotation/` module, never search the entire codebase.

**Isolation Rule:** Every module (`lead/`, `customer/`, `quotation/`, etc.) is self-contained. A module may depend on `components/shared/`, `hooks/` (global), `services/`, `lib/`, `types/` (global), `constants/`, and `utils/` — but never directly on another module's internals (see Section 23).

**Note on `pages/` duplication:** The top-level `pages/` tree mirrors `modules/` because Inertia requires page components to resolve from a predictable page-root path. `pages/lead/*.tsx` files remain thin — they compose components from `modules/lead/components/` rather than containing business logic themselves.

---

## 4. Shared Components

Shared components are the reusable vocabulary of the entire application, used by every module without duplication.

| Category | Examples | Rule |
|---|---|---|
| **Primitives (`components/ui/`)** | Button, Input, Select, Textarea, Checkbox, Switch, Badge, Avatar, Tooltip | Sourced from shadcn/ui and Radix UI, styled per `03-design-system.md`; never modified per-feature. |
| **Composite Shared (`components/shared/`)** | DataTable, FormField, Dialog wrapper, Breadcrumb, Search, Pagination, Loading, Skeleton, EmptyState, Card variants | Built once on top of primitives, consumed by every module needing that pattern. |
| **Layout Shared (`components/layout/`, `components/crm/`)** | Sidebar shell, Topbar shell, Navigation groups | Own the app shell structure defined in `03-design-system.md` Section 9. |

**Rule:** If two or more modules need visually or behaviorally identical UI, it is built once in `components/shared/` — it is never implemented twice inside separate modules.

---

## 5. Layout Architecture

| Layout | Purpose |
|---|---|
| **AppLayout** | The default authenticated shell: sidebar, topbar, content area. Used by all CRM modules. |
| **AuthLayout** | Minimal, centered layout for login, password reset, and related auth pages. |
| **DashboardLayout** | Extends AppLayout with dashboard-specific structural regions (KPI row, widget grid) per `03-design-system.md` Section 20. |
| **SettingsLayout** | Extends AppLayout with a settings-specific secondary navigation (tabs or a settings sub-menu). |
| **PublicLayout** | Reserved for any non-authenticated, non-app page (e.g., public status pages), kept minimal and separate from the authenticated shell. |

All layouts are composed from the same shared `components/layout/` building blocks; no layout redefines the sidebar or topbar independently.

---

## 6. Routing

Routing is server-driven through Laravel and resolved by Inertia; the frontend's responsibility is to provide a predictable page component for every route.

| Route Group | Page Root |
|---|---|
| **Dashboard** | `pages/dashboard/` |
| **CRM Modules** | `pages/lead/`, `pages/customer/`, `pages/quotation/`, `pages/order/`, `pages/production/`, `pages/inventory/`, `pages/delivery/`, `pages/invoice/` |
| **Reports & Analytics** | `pages/report/`, `pages/analytics/` |
| **Settings** | `pages/settings/` |
| **Auth** | `pages/auth/` (login, register if applicable, password reset) |
| **Profile** | `pages/profile/` |
| **Error Pages** | `pages/errors/` (403, 404, 500, and equivalent) |

**Rule:** Every Inertia route resolves to exactly one page component, matching the folder structure above 1:1. No route resolves to a page outside its corresponding domain folder.

---

## 7. Page Architecture

Every list/index page in the application follows the same structural composition, in the same order, so users and engineers can predict any page's layout:

1. **Header** — page title, primary page-level action (e.g., "New Lead").
2. **Toolbar** — search, view options, secondary actions.
3. **Filters** — status, owner, date range, and other business filters (per `01-product-rules.md` Global Rules).
4. **Data** — the primary table or content region.
5. **Pagination** — positioned consistently at the bottom of the data region.

Detail/edit pages follow an analogous, consistent structure: header (with breadcrumb), tabbed or sectioned content, and a consistent action area. No page introduces a bespoke structural pattern outside these two templates without an explicit, documented exception.

---

## 8. Form Architecture

All forms are built using **React Hook Form** for form state and **Zod** for schema validation, composed as follows:

1. **Schema Definition:** Each form's validation rules are defined as a Zod schema, colocated with the module that owns the form (e.g., `modules/lead/types/lead-schema.ts`).
2. **Form Binding:** React Hook Form is configured with the Zod schema as its resolver, ensuring validation logic is defined once and never duplicated between client-side checks and type definitions.
3. **Field-Level Validation:** Validation errors surface inline, at the field level, consistent with `03-design-system.md` Section 11.
4. **Submission Flow:** On submit, the form is validated client-side first (via Zod); only a valid payload is sent to the server. Server-side validation errors (Laravel) are mapped back into the same React Hook Form error state, so the user sees one consistent error experience regardless of validation source.
5. **Reusability:** Common field patterns (text field with label, select with async options) are built once in `components/shared/forms/` and composed into every module's forms.

**Rule:** No form manages validation manually outside Zod, and no form duplicates validation logic between the frontend schema and inline component logic.

---

## 9. State Management

State is managed at the lowest sufficient level; global state libraries are avoided by default.

| State Type | When Used |
|---|---|
| **Local State (`useState`/`useReducer`)** | UI-only state scoped to a single component (e.g., a dropdown's open/closed state). |
| **Props** | Passing data and callbacks down a shallow component tree within a single page or feature. |
| **Context** | Cross-cutting concerns needed by many components without prop-drilling (e.g., current user, permission set, active workspace) — used sparingly and only for genuinely global concerns. |
| **Server State (Inertia Props)** | The primary source of business data. Data fetched by the Laravel controller and passed via Inertia props is treated as the source of truth for page content, avoiding redundant client-side data fetching. |
| **Server State (mutations)** | Form submissions and actions use Inertia's request/visit mechanism, relying on the server round-trip and fresh props rather than manual client-side cache synchronization. |

**Rule:** Redux or an equivalent global state library is not used unless a specific, documented cross-cutting need arises that Context and Inertia props genuinely cannot address — this requires an explicit architecture decision, not a default choice.

---

## 10. API Layer

Although Inertia removes the need for a traditional REST/JSON API layer on most pages, the frontend still maintains clear separation of concerns for anything beyond direct page props:

| Layer | Responsibility |
|---|---|
| **Server (Laravel Controllers)** | Own the source of truth for data and business logic; return Inertia responses. |
| **Actions** | Discrete, named client-side functions representing a single user intent (e.g., `assignLead`, `sendQuotation`), wrapping the corresponding Inertia visit/form submission. |
| **Services (`services/`, module `services/`)** | Encapsulate any client-side logic that prepares, transforms, or calls out around a business operation — kept thin, since business logic itself lives server-side per `PROJECT_CONSTITUTION.md`. |
| **Transformers** | Shape and normalize server-provided data into the exact structure a component or hook expects, isolating components from raw payload shape changes. |
| **Validation** | Client-side validation lives in Zod schemas (Section 8); server-side validation remains authoritative and is always assumed to be re-checked by Laravel regardless of client checks. |

**Rule:** Components never call Inertia or fetch directly inline; they call a named Action or Service function, keeping data-access logic testable and consistent.

---

## 11. Permission Rendering

All navigation, buttons, pages, and actions render conditionally based on the authenticated user's role and permission set (Owner, Sales Manager, Sales, Finance, Warehouse, Production, Customer Service, and any future role per `01-product-rules.md` Section 8).

- Permission data is provided by the server (via Inertia shared props) and consumed through a global Context/hook (e.g., a `usePermission` pattern), never hardcoded per component.
- Navigation items (sidebar, topbar) are filtered centrally, from `config/` navigation configuration cross-referenced with the user's permissions — not through scattered conditional checks across individual components.
- Buttons and actions that a user's role cannot perform are hidden, not merely disabled, unless visibility itself is informative (e.g., showing a disabled "Approve" button with an explanatory tooltip for a Sales user).
- **Rule:** No component contains a hardcoded role check such as `if (role === "sales")`; all checks resolve through the shared permission utility so that role/permission logic changes in exactly one place.

---

## 12. Table Architecture

All data tables are built on **TanStack Table**, wrapped in a single shared `DataTable` component (`components/shared/tables/`) that every module consumes rather than reimplementing table logic.

The shared DataTable standard supports:

- Sorting, per `03-design-system.md` Section 12.
- Global and column-level search.
- Filtering, including status, owner, and date-range filters consistent with `01-product-rules.md` Global Rules.
- Pagination, using a consistent page-size and navigation pattern.
- Column visibility toggling.
- Export, respecting the same access and business rules as the live data (per `01-product-rules.md` Rule 97).
- Bulk actions, appearing contextually when rows are selected.

**Rule:** No module implements its own bespoke table component; module-specific behavior (custom columns, custom row actions) is configured through the shared DataTable's composition API, not through a parallel implementation.

---

## 13. Chart Architecture

All charts are built using **Recharts**, restricted to the chart types defined in `03-design-system.md` Section 13.

- **Size:** Charts use a small, standardized set of container sizes (e.g., dashboard KPI chart, full-width report chart) rather than arbitrary per-page dimensions.
- **Spacing:** Chart container padding and margin follow the global spacing scale (`03-design-system.md` Section 3).
- **Tooltip:** A single shared tooltip presentation is used across all chart instances for visual and behavioral consistency.
- **Legend:** Legends follow the placement and usage rules defined in `03-design-system.md` Section 13.5.
- **Reusability:** Common chart configurations (e.g., a standard revenue line chart) are wrapped as shared chart components in `components/shared/charts/`, configured per use case rather than rebuilt.

---

## 14. Loading Strategy

- **Default:** Skeleton loading states are used for all content-heavy views (tables, cards, detail pages), matching the shape of the content they precede, per `03-design-system.md` Section 6/15.
- **Spinner:** Reserved strictly for short, indeterminate, in-place operations (e.g., a button's submit-in-progress state) — never used as a full-page loading pattern.
- **Rule:** Any view expected to take longer than a near-instant response must show a Skeleton, not a blank screen or an unexplained delay.

---

## 15. Notification

All toast-style notifications use **Sonner**, standardized as follows:

| Type | Usage |
|---|---|
| **Success** | Confirms a completed action (e.g., "Lead created"). |
| **Error** | Reports a failed action with a clear, actionable message. |
| **Warning** | Communicates a non-blocking but important condition. |
| **Info** | Communicates a neutral system message. |

**Rule:** Notification copy, duration, and placement are standardized centrally (a single notification utility wrapping Sonner); modules never configure Sonner directly and independently.

---

## 16. Modal Standard

All dialogs (built on Radix UI/shadcn Dialog primitives) follow one consistent standard:

- **Width:** A small, fixed set of modal width variants (e.g., small, medium, large), never arbitrary per-instance widths.
- **Padding:** Consistent internal padding drawn from the global spacing scale.
- **Action:** The primary action button is consistently positioned (e.g., bottom-right), using the primary button variant.
- **Cancel:** A cancel/dismiss action is always present and consistently positioned relative to the primary action.
- **Escape:** All modals close on the Escape key unless the action is a critical, irreversible confirmation requiring explicit button interaction.
- **Click Outside:** Modals close on outside click by default, with the same critical-action exception as Escape behavior.

---

## 17. Responsive Rules

The application is **desktop-first**. There is no separate mobile layout; the interface adapts.

- **Desktop:** Full sidebar, full grid, maximum density, per `03-design-system.md` Section 17.
- **Sidebar Collapse:** The sidebar collapses to an icon-only or overlay state at reduced widths, rather than being replaced by a different navigation pattern.
- **Tablet:** Grid and table columns adapt per the responsive priorities defined in `03-design-system.md`.
- **Mobile:** The same components and pages adapt down to a single-column, prioritized-content presentation — no parallel "mobile version" of any page is built or maintained.

**Rule:** Every component must be built to adapt responsively by default; a component that only works at desktop width is not considered complete.

---

## 18. Performance Rules

- **Lazy Loading:** Route-level page components are lazy-loaded by default, so users only download code for the module they are actively using.
- **Dynamic Import:** Heavy, infrequently-used components (e.g., complex chart libraries, rich text editors) are dynamically imported at the point of use.
- **Memoization:** Expensive computations and components with expensive re-render costs are memoized deliberately — not applied blanket-wide, to avoid unnecessary complexity.
- **Code Splitting:** Module boundaries (Section 3) naturally align with code-splitting boundaries, keeping each module's bundle independent.
- **Suspense:** React Suspense boundaries are used around lazy-loaded and asynchronous content, paired with the Skeleton loading strategy (Section 14).

---

## 19. Accessibility

Frontend implementation adheres to the accessibility standards defined in `03-design-system.md` Section 18:

- Full keyboard operability for every interactive component.
- Visible, consistent focus rings on all focusable elements.
- Correct semantic structure and ARIA attributes for custom components built on Radix UI primitives (which provide strong accessibility defaults that must not be overridden or stripped).
- Contrast compliance for all text and meaningful UI elements.

**Rule:** Any custom component built on top of a Radix UI primitive must preserve, not override, the primitive's built-in accessibility behavior.

---

## 20. Animation Rules

- Animation durations fall within a short, consistent range (approximately 150–250ms), consistent with `03-design-system.md` Section 16.
- Animation is applied only to communicate state change (open/close, expand/collapse, hover/focus feedback) — never for decorative effect.
- A single, consistent easing curve is used system-wide.
- Motion respects reduced-motion user preferences at all times.

---

## 21. Naming Convention

| Category | Convention | Example |
|---|---|---|
| **Folder** | kebab-case | `modules/lead/`, `components/shared/tables/` |
| **File (component)** | PascalCase | `LeadCard.tsx`, `DataTable.tsx` |
| **File (hook)** | camelCase, prefixed `use` | `useLeadFilters.ts` |
| **File (type)** | kebab-case, suffixed `-types` or `-schema` | `lead-types.ts`, `lead-schema.ts` |
| **File (utility)** | kebab-case | `format-currency.ts` |
| **File (constant)** | kebab-case | `lead-status.ts` |
| **Component** | PascalCase, noun-first | `LeadStatusBadge`, `QuotationTable` |
| **Hook** | camelCase, `use` prefix, verb or noun-based | `useLeadAssignment`, `usePermission` |
| **Type/Interface** | PascalCase | `Lead`, `LeadStatus`, `CreateLeadPayload` |
| **Utility function** | camelCase, verb-first | `formatCurrency`, `parseDealValue` |
| **Constant** | SCREAMING_SNAKE_CASE for fixed values; PascalCase for enum-like objects | `LEAD_STATUS`, `DealStage` |

This convention is applied uniformly across every module; no module defines its own alternate convention.

---

## 22. Coding Rules

- **File Size:** No file should exceed approximately 300 lines if it can reasonably be decomposed further; exceeding this is a signal to extract sub-components, hooks, or helper functions.
- **Component Size:** Components are kept small and focused on a single visual/behavioral responsibility; large, multi-purpose components are decomposed.
- **Reusability:** Any pattern used in two or more places is extracted to a shared location (Section 4) rather than duplicated.
- **No Large Logic in JSX:** Business logic, data transformation, and complex conditionals are extracted into hooks or utility functions; JSX contains only presentational composition and minimal inline expressions.

---

## 23. Dependency Rules

- **Feature Isolation:** A module under `modules/` may not import directly from another module's internal `components/`, `hooks/`, or `services/` folders. Cross-feature needs are met by extracting the shared concern into `components/shared/`, global `hooks/`, `services/`, or `types/`.
- **Allowed Imports:** Any module may import from `components/ui/`, `components/shared/`, `components/layout/`, `hooks/` (global), `services/` (global), `lib/`, `types/` (global), `constants/`, `config/`, and `utils/`.
- **Disallowed Imports:** Direct imports such as `modules/lead/components/*` being imported inside `modules/customer/` are not permitted. If Lead and Customer genuinely need to share a component, it belongs in `components/shared/`.
- **Layout/Page Boundary:** Layouts and pages may import from modules, but modules never import from `pages/` or `layouts/`, preventing circular dependency risk.

This rule enforces the isolation principle from Section 3 structurally, not just by convention.

---

## 24. Future Scalability

This architecture is designed to support INAKARA CRM's growth into Multi-Branch, Multi-Warehouse, Multi-Company, and full SaaS operation without a frontend rewrite, because:

- **Feature-first modules scale horizontally.** Adding "Branch" or "Warehouse" as a new business concept means adding a new module under `modules/`, following the exact same internal structure as every existing module — never restructuring existing ones.
- **Shared components are already abstracted from business content.** The `DataTable`, form components, and chart wrappers used for Leads today will serve Branches, Warehouses, or any future entity without modification.
- **Permission rendering is centralized.** Introducing new roles (e.g., a Branch Manager) or new permission dimensions (e.g., branch-scoped visibility) requires only extending the central permission configuration, not touching individual components (Section 11).
- **State management stays server-driven.** Because Inertia props remain the primary source of business data, scaling to multi-tenant or multi-company contexts is primarily a server/data-layer concern, not a frontend state-management rewrite.
- **Dependency isolation prevents architectural erosion.** Because modules cannot silently depend on each other's internals (Section 23), the codebase cannot accumulate the kind of tangled coupling that typically forces a rewrite as an application grows.

Any future architectural change that would violate this document must be proposed as a formal amendment, evaluated against `PROJECT_CONSTITUTION.md`, before implementation.

---

## 25. Glossary

| Term | Definition |
|---|---|
| **Module** | A self-contained, feature-first folder under `modules/` owning all frontend logic for one business capability. |
| **Shared Component** | A reusable, business-agnostic component consumed by multiple modules. |
| **Action** | A named client-side function representing a single user intent, wrapping an Inertia visit or form submission. |
| **Server State** | Business data owned and provided by the Laravel backend via Inertia props, treated as the source of truth. |
| **Permission Rendering** | The pattern of conditionally rendering UI based on centrally resolved role/permission data. |

## 26. References

- `PROJECT_CONSTITUTION.md` — supreme authority.
- `01-product-rules.md` — business rules, roles, and workflow this architecture must support.
- `02-design-principles.md` — design philosophy this architecture must express.
- `03-design-system.md` — token and component standards this architecture implements structurally.
- `.ai/05-backend-rules.md` *(future document)* — defines the Laravel-side counterpart to this frontend architecture.

---

*End of frontend-architecture.md — Version 1.0.0*
