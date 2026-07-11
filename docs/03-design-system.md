# 03. Design System
## INAKARA CRM — Permanent Design System Specification

**Status:** Binding — Subordinate to `PROJECT_CONSTITUTION.md`, `01-product-rules.md`, `02-design-principles.md`
**Version:** 1.0.0
**Scope:** This document is the single source of truth for every visual and interaction standard in INAKARA CRM: tokens, typography, spacing, components, layout, and system-level standards. It defines specifications only — no CSS, Tailwind, React, HTML, or implementation code. Numeric scales given here are design specifications to be implemented later, not code.

---

## Table of Contents

1. [Design Tokens](#1-design-tokens)
2. [Typography System](#2-typography-system)
3. [Spacing System](#3-spacing-system)
4. [Border Radius System](#4-border-radius-system)
5. [Border System](#5-border-system)
6. [Shadow System](#6-shadow-system)
7. [Icon System](#7-icon-system)
8. [Grid System](#8-grid-system)
9. [Layout System](#9-layout-system)
10. [Component Library](#10-component-library)
11. [Form Standards](#11-form-standards)
12. [Table Standards](#12-table-standards)
13. [Chart Standards](#13-chart-standards)
14. [Navigation System](#14-navigation-system)
15. [Feedback System](#15-feedback-system)
16. [Motion System](#16-motion-system)
17. [Responsive System](#17-responsive-system)
18. [Accessibility Standards](#18-accessibility-standards)
19. [Naming Convention](#19-naming-convention)
20. [Dashboard Standards](#20-dashboard-standards)
21. [Anti-Patterns](#21-anti-patterns)
22. [Future Scalability](#22-future-scalability)
23. [Glossary](#23-glossary)
24. [References](#24-references)

---

## 1. Design Tokens

Tokens are the atomic values from which every visual decision in the product is composed. No component may use a raw, one-off value outside this token set.

### 1.1 Primary & Accent Colors

| Token | Purpose | Usage Rule |
|---|---|---|
| `color.accent` | The single brand accent (soft blue) | Used only for primary actions, active/selected states, and key interactive highlights. Never used for large background areas. |
| `color.accent.subtle` | A low-intensity tint of the accent | Used for subtle active-state backgrounds (e.g., selected sidebar item background). |

### 1.2 Neutral Colors

| Token | Purpose |
|---|---|
| `color.neutral.0` (white) | Primary surface background. |
| `color.neutral.50–100` | Secondary surface, subtle section backgrounds. |
| `color.neutral.200–300` | Borders, dividers. |
| `color.neutral.400–500` | Disabled text, placeholder text, quiet icons. |
| `color.neutral.600–700` | Secondary text, labels. |
| `color.neutral.800–900` | Primary text, headings. |

Neutrals form the overwhelming majority of the interface's visual weight, consistent with the minimal color philosophy.

### 1.3 Background Colors

| Token | Purpose |
|---|---|
| `bg.page` | Base page background (neutral 0 or 50). |
| `bg.surface` | Cards, panels, table surfaces. |
| `bg.surface.raised` | Elevated surfaces (modals, popovers) — same hue family, distinguished by shadow, not color. |
| `bg.sidebar` | Sidebar background — a quiet neutral, distinct enough from `bg.page` to anchor navigation without competing with content. |

### 1.4 Border Colors

| Token | Purpose |
|---|---|
| `border.default` | Standard component and container borders. |
| `border.subtle` | Low-emphasis dividers within a section. |
| `border.strong` | Reserved for rare cases requiring stronger separation (e.g., active input focus outline companion). |

### 1.5 Text Colors

| Token | Purpose |
|---|---|
| `text.primary` | Headings, primary values, primary content. |
| `text.secondary` | Labels, supporting content. |
| `text.tertiary` | Captions, metadata, timestamps. |
| `text.disabled` | Disabled or inactive text. |
| `text.on-accent` | Text placed on top of the accent color (e.g., primary button label). |

### 1.6 Status Colors

| Token | Meaning | Usage Rule |
|---|---|---|
| `status.success` (green) | Positive outcome, completed, paid. | Text, icon, or small indicator — never large background fills. |
| `status.warning` (yellow) | Attention needed, approaching deadline. | Same restrained usage as success. |
| `status.error` (red) | Failure, overdue, blocked. | Same restrained usage. |
| `status.info` (blue) | Neutral informational state, distinct from the brand accent when necessary. | Used sparingly to avoid confusion with `color.accent`. |

### 1.7 Chart Colors

A constrained palette derived from the accent and status tokens, extended only as far as necessary (typically 4–6 hues) for multi-series charts. Chart colors never introduce new hues outside this constrained set (see Section 13).

### 1.8 Surface Colors

Surfaces are always a neutral token (Section 1.3); no surface is ever colored using the accent or status tokens.

### 1.9 Overlay Colors

| Token | Purpose |
|---|---|
| `overlay.scrim` | Semi-transparent neutral-dark layer behind modals/drawers, used only to focus attention on the foreground element. |

### 1.10 Hover, Disabled, Focus, and Selection Colors

| Token | Purpose |
|---|---|
| `state.hover` | A subtle neutral or accent-tinted shift applied on hover, never a strong color change. |
| `state.disabled` | Reduced-contrast neutral applied consistently to any disabled element. |
| `state.focus` | A visible, consistent accent-colored ring applied to focused interactive elements. |
| `state.selected` | `color.accent.subtle` background, used consistently for selected rows, items, and tabs. |

---

## 2. Typography System

### 2.1 Type Roles

| Role | Purpose | Relative Scale | Weight |
|---|---|---|---|
| **Display** | Rare, large-scale introductory text (e.g., empty-state headline). | Largest | Semibold |
| **Heading (H1–H3)** | Page titles and major section titles. | Large → Medium | Semibold |
| **Subheading** | Section titles within a page. | Medium | Medium |
| **Body** | Default reading and content text. | Base | Regular |
| **Caption** | Metadata, timestamps, helper text. | Small | Regular |
| **Label** | Field labels, table column headers. | Small | Medium |
| **Button** | Action text inside buttons. | Base/Small | Medium |
| **Table** | Table cell content. | Base/Small | Regular (values), Medium (key values) |
| **Input** | Text typed or displayed inside form fields. | Base | Regular |

### 2.2 Font Hierarchy

A single typeface family is used throughout the product for consistency; hierarchy is achieved through the scale and weight combinations above, never through mixing multiple typefaces.

### 2.3 Reading Rhythm

Heading-to-body spacing, body line height, and paragraph spacing are standardized so that any block of text — a form description, a report summary, a note — reads with the same rhythm anywhere in the product.

### 2.4 Text Weight

Only two to three weights are used system-wide (e.g., Regular, Medium, Semibold). Additional weights are not introduced, keeping hierarchy legible and consistent.

### 2.5 Text Scale

The type scale is a small, fixed set of sizes (approximately 6–8 steps from Caption to Display). No component may use a font size outside this defined scale.

---

## 3. Spacing System

### 3.1 Spacing Scale

A single base unit (4px-equivalent) governs all spacing, expressed as a fixed multiple sequence (e.g., 4, 8, 12, 16, 24, 32, 48, 64). All padding, margin, and gap values in the product are drawn exclusively from this scale.

### 3.2 Application Rules

| Context | Rule |
|---|---|
| **Padding** | Internal spacing within a component; consistent per component type (e.g., all buttons of the same size share the same padding). |
| **Margin** | External spacing between sibling components; determined by relationship (closely related elements use smaller values, unrelated sections use larger values). |
| **Gap** | Spacing within flex/grid arrangements (e.g., between form fields, between cards); standardized per context type. |
| **Section Spacing** | The largest steps in the scale, used to separate major page sections. |
| **Component Spacing** | Mid-range steps, used between distinct but related components. |
| **Card Spacing** | Internal card padding uses a consistent mid-to-large step; content within a card never touches its edge. |
| **Form Spacing** | Consistent gap between form fields and between field groups (field groups use a larger gap than individual fields within a group). |
| **Table Spacing** | Cell padding is standardized per table density mode (see Section 12). |
| **Grid Spacing** | Consistent gap between grid items (e.g., dashboard KPI cards), matching the section/component spacing scale. |

---

## 4. Border Radius System

| Token | Scale Position | Usage |
|---|---|---|
| `radius.xs` | Smallest | Small controls: checkboxes, badges, tags. |
| `radius.sm` | Small | Inputs, buttons, small components. |
| `radius.md` | Medium | Cards, panels, dropdowns — the most commonly used radius. |
| `radius.lg` | Large | Modals, drawers, larger surfaces. |
| `radius.xl` | Largest | Reserved for rare, large-scale containers; used sparingly to avoid an overly "soft" enterprise feel. |

Radius values remain small and restrained across the entire scale — soft enough to feel modern, never large enough to feel playful or consumer-oriented.

---

## 5. Border System

- **Border Width:** A single standard hairline width is used for the vast majority of borders; a slightly heavier width is reserved for rare emphasis cases (e.g., focus ring companion border).
- **Border Colors:** Drawn exclusively from `border.default` and `border.subtle` (Section 1.4); accent or status colors are never used for standard structural borders.
- **Border Hierarchy:** `border.subtle` separates minor elements (table rows); `border.default` separates major containers (cards, panels, sidebar).
- **Dividers:** Used only where spacing alone cannot sufficiently communicate separation; always hairline, always `border.subtle`.
- **Section Separators:** Prefer spacing over visible dividers; when a divider is used, it spans the full logical width of the section.
- **Card Borders:** A single hairline `border.default`, paired with `radius.md`, and never combined with a heavy shadow (Section 6).
- **Input Borders:** A single hairline border in its default state, transitioning to a `state.focus` ring on focus, and a status color on error.
- **Table Borders:** Row separation uses `border.subtle`; the table container itself may use `border.default`. Vertical column borders are avoided by default, relying on spacing for column separation.

---

## 6. Shadow System

### 6.1 Shadow Philosophy

Shadows exist to indicate elevation — that an element is temporarily floating above the page — not to decorate static content. Shadows are used sparingly and are always very light.

### 6.2 Shadow Scale

| Token | Usage |
|---|---|
| `shadow.none` | Default state for static content (cards, panels) — flat by default. |
| `shadow.xs` | Optional, extremely subtle elevation for static cards where separation from background is genuinely needed. |
| `shadow.sm` | Dropdowns, tooltips. |
| `shadow.md` | Popovers, floating panels. |
| `shadow.lg` | Modals, dialogs — the strongest shadow in the system, still restrained relative to consumer-app conventions. |

Static, permanently visible content (cards, tables, sidebar) should default to `shadow.none` or `shadow.xs`, relying on borders and background contrast instead. Shadow intensity increases only for transient, temporarily-elevated UI.

---

## 7. Icon System

- **Icon Library:** A single, consistent icon set is used throughout the product (per the approved frontend stack, Lucide-family icons); icons are never mixed from multiple stylistic sources.
- **Preferred Style:** Line-based (stroke) icons, not filled or duotone, consistent with the flat, minimal visual philosophy.
- **Stroke Width:** A single consistent stroke width is used across all icon instances and sizes.
- **Sizes:** A small fixed set of icon sizes (e.g., small, default, large) aligned to the spacing and type scale, never arbitrary sizing.
- **Spacing:** Icon-to-text spacing is standardized (a fixed step from the spacing scale) wherever icons accompany labels.
- **Usage Rules:** Icons support and reinforce text labels; icons are not used as the sole indicator of meaning for any critical action (Section 18, color/icon independence).
- **Forbidden Usage:** Decorative icons with no functional meaning; oversized icons used for visual emphasis; colorful or multi-color icon sets; duplicate icons representing different meanings in different contexts.

---

## 8. Grid System

| Context | Column Structure | Notes |
|---|---|---|
| **Desktop** | 12-column grid | Full information density; multi-column layouts for dashboards and detail pages. |
| **Laptop** | 12-column grid, narrower container | Same structure as desktop with adjusted container width and spacing. |
| **Tablet** | Reduced to a simplified column structure (e.g., 6–8 columns) | Multi-column layouts collapse into fewer, wider columns. |
| **Mobile** | Single-column | All grid-based layouts stack vertically; grid is effectively suspended in favor of a linear flow. |

**Container Width:** A maximum content width is defined for reading-oriented pages (forms, detail views) to preserve comfort; data-dense views (tables, pipelines) may use the full available width.

**Responsive Behavior:** Grid columns collapse progressively (12 → 8 → 6 → 1) rather than in a single abrupt jump, preserving layout stability across intermediate viewport sizes.

---

## 9. Layout System

- **Sidebar:** Fixed-width, persistent, collapsible; contains only primary navigation (Section 14).
- **Topbar:** Fixed-height, persistent; contains search, workspace context, and user account access only.
- **Content Area:** The remaining viewport area; owns all page-specific layout decisions within the container width rules of Section 8.
- **Page Header:** A consistent structural region at the top of the content area containing the page title, primary page-level actions, and (where relevant) tabs or breadcrumb.
- **Page Body:** The main working area beneath the page header; layout within this region (single column, split view, table-first) is determined per page type, always within the spacing and grid rules above.
- **Sticky Areas:** Table headers (Section 12) and, where appropriate, page headers on long scrolling pages remain visible during scroll.
- **Scroll Areas:** Scroll is contained to the content area by default; sidebar and topbar remain fixed, preserving constant navigational orientation.
- **Workspace Layout:** Where multiple workspaces/industries exist, the overall sidebar/topbar/content structure remains identical across workspaces — only the content and navigation items change.

---

## 10. Component Library

Every component below is defined as a standard, not an implementation. All components must comply with the tokens and systems defined in Sections 1–9.

| Component | Standard |
|---|---|
| **Buttons** | A small set of variants (primary, secondary, ghost, destructive) and sizes; primary uses `color.accent`, all others remain neutral until hovered/focused. |
| **Inputs** | Consistent height per size variant, hairline border, `radius.sm`, clear focus and error states. |
| **Textarea** | Same visual language as inputs; resize behavior defined per use case. |
| **Select** | Visually identical to inputs, with a consistent trigger affordance. |
| **Checkbox / Radio** | Small, precise, consistent alignment with adjacent labels. |
| **Switch** | Used only for immediate, binary settings; never used as a substitute for a checkbox in forms requiring explicit submission. |
| **Badge** | Small, low-emphasis label for categorical metadata (non-status). |
| **Tag** | Similar to badge, used for user-defined or filterable labels. |
| **Avatar** | Circular or consistently-shaped small identity marker; consistent sizing scale. |
| **Tooltip** | Brief, low-elevation (`shadow.sm`), appears on hover/focus with minimal delay. |
| **Popover** | Lightweight floating panel (`shadow.md`) for contextual actions or detail. |
| **Dropdown** | Consistent trigger and panel styling with Select and Popover. |
| **Accordion** | Used to progressively disclose secondary content; collapsed by default when content is non-essential. |
| **Tabs** | Used only to divide views of the same record/context (per Section 11 of `02-design-principles.md`). |
| **Breadcrumb** | Used on any page more than one level deep in hierarchy. |
| **Pagination** | Standard control for paged tables/lists; always paired with a visible total count where feasible. |
| **Date Picker / Calendar** | Consistent token usage; today and selected states use `color.accent`. |
| **Search** | Consistent placement and styling across global and in-page search instances. |
| **Command Palette** | A single, consistent fast-access pattern available globally. |
| **Toast** | Brief, non-blocking, auto-dismissing; positioned consistently across the product. |
| **Dialog / Modal** | Reserved for focused, blocking interactions; uses `shadow.lg` and `overlay.scrim`. |
| **Drawer** | Used for detail or edit panels that benefit from retaining page context behind them. |
| **Empty State** | Consistent structure: brief explanation plus, where relevant, a primary action. |
| **Skeleton** | Preferred loading pattern for content-heavy views; matches the shape of the content it precedes. |
| **Loading / Progress** | Used for indeterminate or determinate operations respectively; always paired with clear context. |
| **Charts** | See Section 13. |
| **Cards** | See `02-design-principles.md` Section 8; standard padding, `radius.md`, `shadow.none`/`xs`. |
| **Tables** | See Section 12. |
| **Stat Cards / KPI Widgets** | A constrained, consistent structure: label, primary value, optional trend indicator — never more than one chart element per stat card. |
| **Timeline / Activity Feed** | Chronological, consistent entry structure (actor, action, timestamp); consistent regardless of which module it appears in. |
| **Notification Panel** | Consistent grouping (e.g., by recency or type) and consistent entry structure with the Activity Feed. |

---

## 11. Form Standards

- **Label Position:** Labels are positioned consistently above their field (top-aligned) across the entire product.
- **Validation:** Performed inline, at the field level, on a consistent trigger (e.g., on blur or on submit, applied uniformly).
- **Error:** Communicated via `status.error`, an icon, and explicit message text beneath the field.
- **Helper Text:** Positioned beneath the field, using `text.tertiary`, shown only when necessary.
- **Success State:** Used sparingly, typically only for fields with asynchronous validation (e.g., availability checks).
- **Required Field:** Marked with a consistent, minimal indicator; required is the assumed default in business-critical forms unless marked optional.
- **Optional Field:** Explicitly labeled "optional" rather than marking every required field, when optional fields are the minority.
- **Disabled State:** Reduced contrast, no pointer affordance, consistent across all field types.
- **Readonly State:** Visually distinct from both editable and disabled states — content is legible and selectable but clearly not editable.
- **Grouping:** Related fields are grouped under a shared section label, using Section 3's form spacing rules.
- **Section Layout:** Long forms are broken into clearly labeled sections rather than presented as a single undifferentiated block.

---

## 12. Table Standards

- **Density:** A default "comfortable-dense" mode is standard; an optional compact mode may be offered for power users, but both draw from the same spacing tokens.
- **Header:** Uses `text.secondary`/Label typography, sticky by default on scroll.
- **Row:** Consistent height per density mode; separated by `border.subtle`.
- **Hover:** A subtle neutral background shift (`state.hover`) on row hover.
- **Sorting:** Indicated by a consistent icon and visual state on the active column header.
- **Filtering:** A consistent filter affordance positioned directly above the table, never buried in a secondary menu for primary filters.
- **Search:** Positioned consistently relative to filters, typically to their left.
- **Pagination:** Positioned consistently at the bottom of the table, showing current range and total.
- **Bulk Action:** A contextual action bar appears only when rows are selected, replacing or supplementing the default toolbar.
- **Selection:** A checkbox column, left-most, consistent across all tables that support selection.
- **Sticky Header:** Always enabled for tables exceeding one viewport height.
- **Expandable Row:** Used only when a row genuinely has meaningful nested detail; expansion uses the same spacing/typography rules as the parent table.
- **Column Resize:** Optional, power-user feature; when present, follows a consistent interaction pattern across all tables.
- **Column Visibility:** Optional per-table configuration; default visible columns are chosen by business priority (Section 5 of `02-design-principles.md`).

---

## 13. Chart Standards

### 13.1 Allowed Chart Types

| Type | Use Case |
|---|---|
| **Line** | Trends over time (e.g., revenue over months). |
| **Bar** | Comparison across discrete categories. |
| **Area** | Cumulative or volume trends over time, used sparingly to avoid visual heaviness. |
| **Donut** | Proportional breakdown with a small number of categories (used more often than Pie for its center-label capability). |
| **Pie** | Used only when Donut's center label is not needed; otherwise Donut is preferred. |
| **Heatmap** | Density/frequency patterns (e.g., activity by day/hour), used sparingly. |
| **KPI (single value)** | The single most common chart-adjacent element on dashboards; a number with optional trend. |
| **Sparkline** | Compact inline trend indicator paired with a KPI, used only where full-chart context is unnecessary. |

### 13.2 Rules for Chart Usage

- A chart is used only when a numeric summary or table would be genuinely harder to interpret than the visual.
- No chart type outside the allowed list above may be introduced without amending this document.
- Each view uses a limited number of charts (per dashboard density philosophy in Section 20).

### 13.3 Color Usage

Charts use only the constrained chart color set (Section 1.7); status-meaning colors (green/red/yellow) are reserved for genuinely positive/negative/attention series, never assigned arbitrarily to neutral categories.

### 13.4 Label & Legend Rules

Labels are shown directly on data where space allows (reducing reliance on a separate legend); legends are used only for charts with more series than can be clearly labeled directly, and are positioned consistently (e.g., below or beside the chart).

### 13.5 Gridline Rules

Gridlines are minimal, low-contrast (`border.subtle`), and used only to the extent they aid value estimation — never as a decorative grid.

---

## 14. Navigation System

| Element | Standard |
|---|---|
| **Sidebar** | Primary, persistent navigation; grouped by module; collapsible. |
| **Topbar** | Global search, workspace context, account access. |
| **Secondary Navigation** | Used within a module for sub-sections, presented as Tabs or a secondary list, never a second sidebar. |
| **Tabs** | Divide views of a single record/context only. |
| **Breadcrumb** | Present on any page more than one level deep. |
| **Quick Search** | Always accessible from the topbar. |
| **Command Palette** | Global keyboard-accessible action/navigation shortcut. |
| **Workspace Switcher** | Positioned at the top of the sidebar or topbar, consistently, when multiple workspaces exist. |

---

## 15. Feedback System

| Type | Standard |
|---|---|
| **Success** | `status.success`, brief, non-intrusive (typically a Toast). |
| **Warning** | `status.warning`, used for non-blocking but important notices (typically a Banner or Alert). |
| **Error** | `status.error`, used for blocking or failed operations (Toast for transient errors, inline for form errors, Dialog for critical failures). |
| **Info** | `status.info`, used for neutral system messages. |
| **Toast** | Transient, auto-dismissing, stacked consistently in one screen position. |
| **Banner** | Persistent, page-level or section-level notice; dismissible where appropriate. |
| **Alert** | Inline, contextual notice tied to a specific section or record. |
| **Dialog** | Blocking, used for confirmation or critical information requiring explicit acknowledgment. |
| **Confirmation** | Required for destructive or hard-to-reverse actions only (per `02-design-principles.md` Section 13). |
| **Loading** | Skeleton preferred for content; spinner reserved for short, indeterminate actions (e.g., button submit state). |
| **Retry** | Any failed asynchronous operation offers a clear, immediate retry affordance. |

---

## 16. Motion System

- **Animation Philosophy:** Motion clarifies state change only; it is never decorative (per `02-design-principles.md` Section 17).
- **Duration:** Short, consistent durations are used across the system — fast enough to feel immediate, categorized roughly into "micro" (hover/focus feedback) and "standard" (panel/modal transitions), with micro shorter than standard.
- **Easing:** A single, consistent easing curve family is used for entrances/exits, avoiding bouncy or exaggerated easing.
- **Hover:** Near-instant, no easing delay perceptible to the user.
- **Loading:** Steady, non-erratic motion (e.g., a calm skeleton shimmer), never a distracting or fast-looping animation.
- **Modal:** A brief, consistent fade/scale entrance and exit, paired with the `overlay.scrim`.
- **Dropdown:** A brief fade/slide consistent with Popover and Tooltip timing.
- **Sidebar:** Collapse/expand uses a brief, smooth width transition.
- **Accordion:** Expand/collapse uses a brief height transition, long enough to be perceptible, short enough to never feel slow.
- **Transition Rules:** Transitions are applied consistently to the same interaction type everywhere it occurs (all dropdowns behave identically, all modals behave identically).
- **Micro Interaction:** Reserved for meaningful confirmations (e.g., a checkbox check, a save confirmation) — never applied purely decoratively.
- **Motion Accessibility:** All motion respects reduced-motion preferences, replacing animation with an immediate state change when required.

---

## 17. Responsive System

| Context | Behavior |
|---|---|
| **Desktop** | Full grid (Section 8), full sidebar, maximum density. |
| **Laptop** | Full grid with adjusted container width; sidebar remains fully expanded by default. |
| **Tablet** | Sidebar defaults to collapsed; grid reduces column count; tables prioritize primary columns. |
| **Mobile** | Single-column layout; sidebar becomes an accessible overlay rather than a persistent panel; tables convert to a card-per-record or prioritized-column pattern. |

**Breakpoint Philosophy:** Breakpoints are defined around content and layout needs (when a grid or table genuinely stops working) rather than specific device dimensions, ensuring durability as device sizes evolve.

**Responsive Priorities:** Primary information and primary actions are preserved at every breakpoint; secondary and supporting information is deferred first (per `02-design-principles.md` Section 5).

**Hide vs. Collapse Strategy:** Navigational and structural elements (sidebar) collapse into an accessible alternate form (icon-only, overlay) rather than being hidden entirely; purely supplementary content (secondary metadata columns) may be hidden outright at the smallest breakpoints, with access still available on demand (e.g., via row expansion).

---

## 18. Accessibility Standards

- **Contrast:** All text and meaningful UI elements meet strong contrast standards against their background token, verified per token pairing, not left to visual judgment alone.
- **Keyboard Navigation:** Every component in Section 10 must be fully operable via keyboard, with a logical, predictable tab order.
- **Focus Ring:** `state.focus` is applied consistently and visibly to every interactive element when keyboard-focused.
- **ARIA Philosophy:** Components follow standard, predictable interaction patterns so that semantic roles and states are correctly conveyed to assistive technology.
- **Screen Readers:** Structural elements (headings, table headers, form labels) carry correct semantic meaning, not purely visual styling.
- **Touch Targets:** Interactive elements maintain a minimum comfortable touch target size, even in the desktop-first, dense default layout.
- **Color Independence:** Status and meaning are always paired with text or iconography; color is never the sole carrier of information (Section 1.6).

---

## 19. Naming Convention

| Category | Convention | Example Pattern |
|---|---|---|
| **Components** | PascalCase, noun-based, describing what it is, not what it does | `StatCard`, `DataTable`, `ConfirmDialog` |
| **Icons** | kebab-case, describing the depicted concept | `arrow-right`, `trash`, `check-circle` |
| **Tokens** | dot-notation, category-first | `color.accent`, `spacing.md`, `radius.sm` |
| **Layouts** | PascalCase, suffixed with `Layout` | `DashboardLayout`, `AuthLayout` |
| **Sections** | descriptive, kebab-case for identifiers | `page-header`, `sidebar-nav` |
| **Variants** | lowercase, descriptive of the variation | `primary`, `secondary`, `compact`, `default` |
| **Pages** | PascalCase, matching the business object plus purpose | `LeadListPage`, `CustomerDetailPage` |
| **Modules** | PascalCase, matching the business capability | `Leads`, `Deals`, `Invoicing` |

Naming is always business/domain-descriptive first, never purely visual (e.g., `PrimaryButton` describing role, not `BlueButton` describing appearance).

---

## 20. Dashboard Standards

- **Dashboard Composition:** A fixed structural pattern — KPI row, followed by a small number of supporting charts/lists, followed by an activity/task region — applied consistently across all role-based dashboards (Section 12 of `01-product-rules.md`).
- **KPI Placement:** KPIs occupy the top of the dashboard, in a single row where possible, using the Stat Card component.
- **Chart Placement:** Charts follow KPIs, never precede them; no more than a small, defined number of charts per dashboard.
- **Recent Activity:** Positioned as a secondary column or lower section; concise by default with a link to full history.
- **Quick Action:** A small, deliberately chosen set, positioned near the page header or as part of the KPI row context.
- **Tables:** Any table on a dashboard (e.g., "My open deals") is a reduced, summary view linking to the full table elsewhere.
- **Notifications:** Accessible from the topbar, not embedded as a large dashboard section, to avoid duplicating the Recent Activity region.
- **Pipeline:** Where shown on a dashboard, presented as a compact summary (e.g., stage counts/values), with the full interactive pipeline view living in its own dedicated page.
- **Cards:** Used for KPI and summary groupings only, never as a wrapper for the entire dashboard content indiscriminately.
- **Density:** Dashboards favor a small number of meaningful elements, consistent with `02-design-principles.md` Section 12.
- **Information Hierarchy:** Every dashboard element's position is justified by business priority for that specific role, never by default template convenience.

---

## 21. Anti-Patterns

In addition to the anti-patterns defined in `02-design-principles.md` Section 18, this Design System explicitly prohibits:

- Introducing a new color outside the defined token set for any reason, including one-off feature requests.
- Introducing a new spacing, radius, or shadow value outside the defined scales.
- Mixing multiple icon styles or multiple typefaces.
- Using a chart type outside the allowed list in Section 13.
- Creating a new component variant that duplicates an existing component's purpose.
- Applying strong shadows or borders to static, permanently-visible content.
- Building dashboard or table layouts that do not follow Sections 12 and 20.
- Any component that cannot be operated via keyboard.
- Any status communicated by color alone, without text or icon reinforcement.

---

## 22. Future Scalability

This Design System is built to remain the single source of truth for many years and across many industries because:

- **Tokens are abstracted from content.** `color.accent`, `spacing.md`, and `radius.sm` carry no industry-specific meaning; a Manufacturing or Healthcare deployment uses the identical token set as the Furniture deployment.
- **Components are defined by function, not by vertical.** A `DataTable` or `StatCard` behaves identically whether it displays furniture orders, patient records, or logistics shipments — only the data bound to it changes.
- **The constrained chart, color, and component sets prevent drift.** Because new tokens and chart types require an explicit amendment to this document (Section 21), the system cannot silently accumulate inconsistency as new teams or industries are onboarded.
- **The naming convention is domain-agnostic in structure** (business capability plus purpose), allowing new modules (e.g., `Patients`, `Shipments`, `Contracts`) to be named consistently without inventing new conventions.
- **Responsive and accessibility standards are defined once, centrally**, so every future module inherits full responsive and accessibility compliance without redefinition.

Any proposed exception to this Design System — a new token, a new component, a new chart type — must be evaluated against `PROJECT_CONSTITUTION.md` and `02-design-principles.md` before being added, and must be added here, in this single source of truth, before implementation begins.

---

## 23. Glossary

| Term | Definition |
|---|---|
| **Token** | The smallest named design decision (a specific color, spacing, or radius value) referenced by all higher-level components. |
| **Component** | A defined, reusable UI standard composed of tokens (e.g., Button, Table, Card). |
| **Density** | The configured tightness of spacing within a repeating structure such as a table. |
| **Elevation** | The visual "height" of a floating element, expressed through the shadow scale. |
| **Breakpoint** | A defined viewport threshold at which layout behavior changes. |
| **Variant** | A defined alternate presentation of a component (e.g., a button's primary vs. secondary variant). |

## 24. References

- `PROJECT_CONSTITUTION.md` — supreme authority.
- `01-product-rules.md` — business context and role-based dashboard/report requirements.
- `02-design-principles.md` — the philosophy this Design System operationalizes into concrete tokens and standards.
- `.ai/04-frontend-rules.md` *(future document)* — will define how these standards are structurally implemented in the React/TypeScript codebase.

---

*End of 03-design-system.md — Version 1.0.0*
