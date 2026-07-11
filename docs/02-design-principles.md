# 02. Design Principles
## INAKARA CRM — Permanent Design Philosophy

**Status:** Binding — Subordinate to `PROJECT_CONSTITUTION.md`
**Version:** 1.0.0
**Scope:** This document defines design philosophy and principles only. It contains no components, no design system tokens, no Tailwind, no CSS, no React, no HTML, and no mockups. Every future UI page, screen, and component built for INAKARA CRM must be evaluated against this document before it is considered complete.

---

## Table of Contents

1. [Design Vision](#1-design-vision)
2. [Design Philosophy](#2-design-philosophy)
3. [Visual Personality](#3-visual-personality)
4. [Layout Philosophy](#4-layout-philosophy)
5. [Information Hierarchy](#5-information-hierarchy)
6. [Typography Philosophy](#6-typography-philosophy)
7. [White Space Philosophy](#7-white-space-philosophy)
8. [Card Philosophy](#8-card-philosophy)
9. [Form Philosophy](#9-form-philosophy)
10. [Table Philosophy](#10-table-philosophy)
11. [Navigation Philosophy](#11-navigation-philosophy)
12. [Dashboard Philosophy](#12-dashboard-philosophy)
13. [Interaction Philosophy](#13-interaction-philosophy)
14. [Accessibility Philosophy](#14-accessibility-philosophy)
15. [Responsive Philosophy](#15-responsive-philosophy)
16. [UX Principles](#16-ux-principles)
17. [Motion Philosophy](#17-motion-philosophy)
18. [Anti-Patterns](#18-anti-patterns)
19. [Future Scalability](#19-future-scalability)
20. [Glossary](#20-glossary)
21. [References](#21-references)

---

## 1. Design Vision

INAKARA CRM must look and feel like software a business trusts with its revenue. This vision rests on four convictions:

**Why minimal.** A CRM is used for hours every day by people whose job is not "using software" — it is selling, fulfilling, and collecting payment. Every unnecessary visual element is a tax on their attention. Minimalism is not an aesthetic preference here; it is a productivity requirement.

**Why enterprise software must avoid visual noise.** Noise — excess color, excess ornament, excess motion — forces the eye to work harder to find what matters. In a CRM, "what matters" changes every few seconds as a user scans a pipeline or a table. A noisy interface actively slows down the exact work the product exists to accelerate.

**Why productivity outranks decoration.** Decoration earns attention once, on first impression. Productivity earns trust every day, on the thousandth use. INAKARA CRM is judged on the thousandth use, not the first.

**Why consistency builds trust.** When a user recognizes a pattern once, they should never have to relearn it elsewhere in the product. A predictable interface signals a well-run, reliable business behind it — which matters enormously for software that customers, in turn, trust with commercial and financial data.

---

## 2. Design Philosophy

| Principle | Explanation |
|---|---|
| **Less but Better** | Every element must justify its presence. If removing an element does not reduce clarity, it must be removed. |
| **Function Before Decoration** | Visual style is only permitted when it improves comprehension, scanning speed, or task completion. Style for its own sake is not permitted. |
| **Content First** | The interface exists to present data and enable action on that data — not to showcase the interface itself. Chrome (borders, panels, ornament) must always recede behind content. |
| **Consistency Over Creativity** | The same type of information must always look and behave the same way, everywhere in the product. Novelty is not a virtue in enterprise software. |
| **Minimal Cognitive Load** | Users should never have to "figure out" a screen. Structure should be recognizable within a second of arrival. |
| **Fast Recognition** | Status, priority, and next action must be identifiable at a glance, primarily through position and typography, with color as a secondary signal. |
| **Predictable UI** | Interactions must behave the same way every time. A button in one part of the product behaves identically to a visually similar button elsewhere. |
| **Professional Appearance** | The interface must look credible enough to be shown to a customer, an auditor, or an investor without embarrassment. |
| **Enterprise First** | Design decisions favor the needs of daily, high-volume, professional use over casual or first-time delight. |
| **Scalable Design** | Every principle in this document must remain valid whether the screen shows 10 records or 100,000. |
| **Timeless Interface** | The design must avoid dependence on any single design trend (glassmorphism, neumorphism, heavy gradients) so it does not look dated within a year. |

---

## 3. Visual Personality

INAKARA CRM's interface personality is:

| Trait | Why |
|---|---|
| **Professional** | It is used to run a business; it must look like it belongs in one. |
| **Confident** | It does not over-explain itself with excessive labels, tooltips, or ornamentation — it presents information plainly, trusting the user's competence. |
| **Quiet** | It does not compete for attention with color or motion; it lets the user's data be the loudest thing on screen. |
| **Elegant** | Elegance here means economy — achieving clarity with the fewest visual elements possible. |
| **Modern** | Current typographic and spacing conventions (as seen in Linear, Attio, Stripe Dashboard) are followed, without chasing short-lived trends. |
| **Trustworthy** | Consistency and restraint signal that the system behind the interface is equally disciplined. |
| **Business Focused** | Every screen supports a business outcome (close a deal, collect a payment, ship an order) rather than existing for its own sake. |
| **Reliable** | Interfaces behave the same way every time; nothing is surprising. |
| **Premium** | Premium is communicated through precision — exact alignment, consistent spacing, careful typography — not through embellishment. |
| **Minimal** | Only what is necessary is shown, by default; further detail is available on demand, not forced onto the first view. |
| **Stable** | Layouts do not shift unexpectedly; loading and interaction states are handled predictably (see Section 13). |

---

## 4. Layout Philosophy

- **Grid:** All screens are built on a consistent underlying grid so that elements across different pages align predictably. The grid is a discipline, not a visual feature.
- **Containers:** Content is held within clearly bounded containers that establish a maximum reading and working width, preventing data from stretching uncomfortably wide on large monitors.
- **Sidebar:** The sidebar is the primary, persistent navigation anchor. It is simple, low-noise, and never competes visually with page content. It stays structurally stable across the entire application.
- **Top Navigation:** The top bar carries only contextual, page-level actions (search, current workspace, user context) — never primary navigation, which belongs in the sidebar.
- **Content Area:** The content area is the visual focus of every screen. Chrome around it (sidebar, topbar) must be visually quieter than the content itself at all times.
- **Page Width:** Page width is intentional, not accidental — wide enough for data-dense views (tables, pipelines), constrained enough for reading-heavy views (forms, detail panels) to remain comfortable.
- **Spacing:** Spacing follows a consistent scale applied uniformly across the product, so that rhythm feels identical from page to page.
- **Alignment:** All elements align to shared vertical and horizontal axes. Nothing floats arbitrarily.
- **Section Separation:** Sections are separated primarily through spacing and typographic hierarchy; borders and background shifts are used sparingly, only when spacing alone is insufficient.
- **Padding, Margin, Gap:** Internal, external, and between-element spacing are each treated as distinct, deliberate decisions — never left to default or arbitrary values.
- **Density:** Default density favors showing more real information (enterprise, data-heavy) over generous decorative spacing, while still preserving comfortable readability (see Section 7).
- **Vertical Rhythm:** Consistent vertical spacing between headings, content blocks, and sections creates a predictable reading cadence down every page.
- **Visual Balance:** No single region of a screen should visually dominate through weight (size, color, contrast) unless it represents the most important information on that screen.

---

## 5. Information Hierarchy

- **Primary Information:** The single most important fact on a screen (e.g., a deal's stage, an invoice's amount due) must be immediately identifiable through size, weight, and position — not through color alone.
- **Secondary Information:** Supporting facts (e.g., last activity date, assigned owner) are visually quieter — smaller, lower contrast — but still legible without effort.
- **Supporting Information:** Metadata (e.g., record ID, creation timestamp) is the quietest layer, available but never competing for attention.
- **Visual Emphasis:** Emphasis is achieved primarily through typographic weight and spatial priority (what appears first, largest, or most isolated), with color reserved for status meaning only (see Section 8 of `PROJECT_CONSTITUTION.md` design philosophy and Section 8 below).
- **User Eye Movement:** Layouts are designed assuming a top-to-bottom, left-to-right scanning pattern for primary content, with the sidebar as a stable peripheral anchor.
- **Dashboard Reading Pattern:** Dashboards are structured so the most business-critical KPI is the first thing encountered, followed by supporting trends, followed by actionable lists (see Section 12).
- **Scanning Behavior:** Because CRM users scan rather than read, repeated structures (tables, cards, lists) must place the same type of information in the same position every time.
- **Content Priority:** When space is constrained (smaller viewports, dense tables), primary information is preserved and secondary/supporting information is progressively hidden or deferred — never the reverse.

---

## 6. Typography Philosophy

- **Heading Hierarchy:** A small number of clearly distinct heading levels is used consistently to indicate page, section, and subsection structure. Headings are never used decoratively.
- **Body Text:** Body text is optimized for comfortable reading of business content — reports, notes, descriptions — at a size and weight that avoids eye strain during extended use.
- **Labels:** Field and column labels are visually quieter than the values they describe, since the value is what the user is scanning for.
- **Captions:** Captions and helper text are the smallest, quietest text in the interface, used only to clarify, never to carry primary meaning.
- **Buttons:** Button text is clear, concise, and action-oriented, with weight sufficient to signal interactivity without shouting.
- **Forms:** Form typography follows the same label/value hierarchy as tables, ensuring a user's eye can move fluidly between browsing data and editing it.
- **Tables:** Table typography prioritizes legibility at high density; numerical data is aligned and weighted for fast comparison across rows.
- **Line Height:** Line height is generous enough to prevent visual crowding in dense text blocks, but not so loose that related lines feel disconnected.
- **Letter Spacing:** Letter spacing remains close to natural defaults; it is adjusted only in small, uppercase, or all-caps labels where tightness would harm legibility.
- **Reading Comfort:** Typography choices are validated against extended, daily professional use — not first-glance impression.
- **Typography Consistency:** The same semantic text role (heading, label, value, caption) must look identical everywhere it appears in the product.

---

## 7. White Space Philosophy

Whitespace is treated as an active design tool, not empty leftover space. In enterprise software, whitespace is what allows density without clutter.

- **Internal Spacing:** Space within a single component (e.g., inside a card, inside a table cell) is calibrated to keep content legible without feeling cramped or bloated.
- **External Spacing:** Space between components and sections signals grouping and separation without requiring visible borders.
- **Content Breathing Room:** Every block of information has enough surrounding space that it can be perceived as a distinct unit at a glance.
- **Visual Grouping:** Related elements are placed closer together than unrelated elements; proximity itself communicates relationship, reducing the need for explicit dividers.
- **Reading Comfort:** Whitespace is tuned so that dense data (tables, pipelines) remains scannable rather than overwhelming, striking the balance between enterprise density and human comfort.

---

## 8. Card Philosophy

- **When to Use Cards:** Cards are used to group a self-contained, glanceable unit of related information (e.g., a single KPI, a single record summary) that benefits from visual isolation from surrounding content.
- **When Not to Use Cards:** Cards are not used as a default container for every piece of content. Lists, tables, and plain sections are preferred whenever content is sequential or tabular rather than discrete and self-contained.
- **Border Usage:** Borders are thin and low-contrast, used only where spacing alone cannot establish sufficient separation.
- **Radius:** Corner radius is small and consistent across all cards and containers — soft enough to feel modern, restrained enough to feel serious.
- **Shadow:** Shadows are very light and used sparingly, primarily to indicate elevation for transient elements (dropdowns, modals) rather than as a permanent decorative feature of static content.
- **Padding:** Card padding is generous enough to avoid crowding but consistent with the product's overall spacing scale.
- **Elevation:** Elevation (shadow, layering) is reserved for elements that are temporarily "above" the page (modals, popovers, tooltips) — not for static content, which should feel flat and settled.
- **Grouping:** Cards group information by business meaning, not by arbitrary visual convenience.
- **Avoiding Nested Cards:** A card must never contain another card. If a sub-grouping is required inside a card, it is achieved through spacing and typography, not through a nested bordered container.

---

## 9. Form Philosophy

- **Inputs:** Input fields are visually consistent, clearly bounded, and unambiguous about their editable state at all times.
- **Textarea:** Textareas follow the same visual language as single-line inputs, distinguished only by height and resize behavior where appropriate.
- **Select:** Select fields are visually indistinguishable from text inputs except for a clear affordance indicating a choice will be presented.
- **Checkbox / Radio:** Checkboxes and radio buttons are small, precise, and consistent in size and alignment with their labels.
- **Validation:** Validation is communicated inline, close to the field it concerns, never solely through a distant summary the user must hunt for.
- **Error States:** Errors are communicated primarily through clear message text and a subtle, consistent status color — never through color alone.
- **Success States:** Success is communicated briefly and quietly; forms should not celebrate loudly for routine, expected outcomes.
- **Disabled States:** Disabled fields are clearly, consistently distinguishable from editable fields through reduced contrast, never through color alone.
- **Helper Text:** Helper text is used only when a field's purpose or constraints are not self-evident from its label.
- **Placeholder:** Placeholder text is used to illustrate expected input format, never as a substitute for a proper label.
- **Field Grouping:** Related fields are grouped with proximity and, where helpful, a section label — following the same grouping logic as Section 7.

---

## 10. Table Philosophy

CRM work is fundamentally data-heavy, and tables are the primary workspace for most roles.

- **Tables:** Tables are the default presentation for any list of records with comparable attributes (leads, deals, invoices, customers).
- **Rows:** Rows have consistent height and clear, low-contrast separation — through spacing or a hairline divider, never heavy borders.
- **Columns:** Column order reflects business priority — the most decision-relevant column appears first, after any identifying column.
- **Density:** Default table density favors showing more rows over generous row height, consistent with the enterprise, data-first philosophy — while remaining comfortably scannable.
- **Sorting:** Any column representing a comparable value (date, amount, status priority) must be sortable, with the current sort state always visible.
- **Filtering:** Filtering is always available for the fields most relevant to that table's business use (status, owner, date range, per Global Rules in `01-product-rules.md`).
- **Search:** A search affordance is present for any table expected to grow beyond a single screen of results.
- **Pagination:** Large result sets are paginated or virtualized; tables never silently truncate data without indicating more exists.
- **Sticky Header:** Column headers remain visible while scrolling through long tables, so context is never lost.
- **Bulk Actions:** Where relevant, bulk selection and action affordances are available without cluttering the default (unselected) table state.
- **Hover:** Row hover provides a subtle, immediate indication of interactivity without visual noise.
- **Selection:** Selected rows are clearly, consistently distinguished from unselected rows.
- **Responsive Tables:** On constrained viewports, tables prioritize the columns representing primary information (Section 5) and progressively defer secondary columns, rather than shrinking all columns uniformly into illegibility.

---

## 11. Navigation Philosophy

- **Sidebar:** The sidebar presents the primary structure of the product (modules such as Leads, Deals, Customers, Reports) in a stable, low-noise list, always in the same position and order.
- **Topbar:** The topbar carries global, contextual tools — search, workspace context, account — and never primary navigation.
- **Breadcrumb:** Breadcrumbs are used wherever a user can be several levels deep in a hierarchy, so orientation is never lost.
- **Tabs:** Tabs are used to divide different views of the same record or context (e.g., a Customer's Overview, Activity, Invoices), never to separate unrelated modules.
- **Search:** Search is a first-class, always-accessible tool, not a secondary feature buried in a menu.
- **Command Palette:** A command palette (or equivalent fast-access pattern) is treated as a power-user accelerant, consistent with the efficiency-first philosophy of the product.
- **Navigation Groups:** Related modules are grouped within the sidebar to reduce the total number of top-level items a user must scan.
- **Collapsible Sidebar:** The sidebar can be collapsed to maximize content area for data-dense work, without losing access to core navigation.
- **Workspace Switching:** Where multiple workspaces, teams, or industries are supported (per the multi-industry vision), switching between them is fast, explicit, and never ambiguous about current context.

---

## 12. Dashboard Philosophy

- **KPIs:** The dashboard leads with a small number of the most business-critical KPIs for that role (per Section 12 of `01-product-rules.md`), never an exhaustive list of every available metric.
- **Charts:** Charts are used only where a trend or comparison is genuinely easier to understand visually than numerically; charts are simple, unadorned, and never decorative.
- **Activities:** Recent activity is surfaced as a concise, scannable list, not a dense feed requiring active reading.
- **Tasks:** Pending tasks/follow-ups are prioritized near the top of role-relevant dashboards, since they represent immediate required action.
- **Notifications:** Notifications are accessible but not intrusive; the dashboard does not compete with itself for the user's attention.
- **Pipeline:** Pipeline visualizations favor clarity of stage and volume over decorative complexity.
- **Calendar:** Calendar/schedule elements, where present, follow the same density and restraint principles as tables.
- **Quick Actions:** A small number of high-frequency actions may be surfaced directly on the dashboard, chosen deliberately, not exhaustively.
- **Recent Activity:** Recency-based lists are kept short by default, with a clear path to full history rather than an unbounded feed.
- **Information Priority:** Every dashboard element earns its position through business importance to that specific role — never through visual convenience.
- **Dashboard Density:** Dashboards favor a small number of meaningful, well-spaced elements over a large number of small, competing widgets.

---

## 13. Interaction Philosophy

- **Hover:** Hover states are subtle and immediate, confirming interactivity without visual disruption.
- **Focus:** Focus states are always visible and consistent, supporting both keyboard users and general usability.
- **Loading:** Loading states are calm and predictable — the interface should never appear frozen or ambiguous about whether an action is in progress.
- **Skeleton:** Skeleton loading is preferred over spinners for content-heavy views, preserving layout stability as data arrives.
- **Animation:** Animation is minimal, fast, and purposeful — used only to clarify a state change, never to entertain (see Section 17).
- **Transition:** Transitions between states are smooth but brief, avoiding any perception of sluggishness.
- **Dialogs:** Dialogs are reserved for actions that require focused attention or confirmation; they are not used for routine, low-stakes interactions.
- **Dropdowns:** Dropdowns are lightweight, fast to open, and dismiss predictably.
- **Toasts:** Toasts confirm the outcome of an action briefly and unobtrusively, without blocking further work.
- **Confirmation:** Destructive or hard-to-reverse actions always require explicit confirmation; routine actions never do.
- **Empty State:** Empty states explain clearly what will appear there and, where relevant, how to populate it — never left as an unexplained blank area.
- **Error State:** Error states explain what went wrong and what the user can do next, in plain business language.
- **Success State:** Success states are quiet and brief, confirming completion without unnecessary celebration.
- **Interaction Feedback:** Every user action receives some form of immediate, appropriate feedback — nothing happens silently.

---

## 14. Accessibility Philosophy

- **Keyboard Navigation:** Every interactive element must be reachable and operable via keyboard alone, consistent with enterprise and power-user usage patterns.
- **Contrast:** Text and meaningful UI elements meet strong contrast standards, ensuring readability under real-world office lighting and extended use.
- **Focus Ring:** A visible, consistent focus indicator is present on all interactive elements at all times when navigated via keyboard.
- **Touch Target:** Interactive elements are sized generously enough to be reliably operable, even though the product is desktop-first.
- **Readable Typography:** Type sizes and weights are chosen to remain legible for extended professional use, not just at first glance.
- **Accessible Components:** Interactive patterns (dropdowns, dialogs, tables) follow predictable, standard behavior expectations so assistive technology can interpret them correctly.
- **Color Independence:** No information is communicated by color alone; status and meaning are always reinforced by text, icon, or position.
- **Screen Reader Considerations:** Meaningful structure (headings, labels, table semantics) is preserved so the interface remains navigable and comprehensible through assistive technology.

---

## 15. Responsive Philosophy

INAKARA CRM is **desktop-first**, reflecting its primary use as a professional, workday tool, but must degrade gracefully across contexts.

- **Desktop:** The primary, fully-featured experience — maximum information density, full navigation, full table functionality.
- **Laptop:** Functionally equivalent to desktop, with layout adjustments to accommodate reduced width without sacrificing core density.
- **Tablet:** Navigation may consolidate (e.g., collapsible sidebar by default), and tables prioritize primary information (Section 5), but all core workflows remain usable.
- **Mobile:** Reserved for lightweight, high-frequency tasks (checking a lead, confirming a follow-up, viewing a customer summary) rather than full data-management work; density is reduced in favor of clarity and touch usability.

**What must remain consistent across all sizes:** visual personality (Section 3), typography roles (Section 6), color usage discipline (Constitution Section 7 and this document's color philosophy), and information hierarchy (Section 5). **What changes:** density, navigation presentation, and the depth of secondary information shown by default.

---

## 16. UX Principles

- **Minimize Clicks:** Frequent, high-value actions are reachable in the fewest possible steps.
- **Reduce Scrolling:** Critical information and actions are positioned within immediate view wherever practical, especially on dashboards and record summaries.
- **Reduce User Thinking:** Interfaces are structured so the correct next action is obvious, not something the user must deduce.
- **Predictable Navigation:** Once a user learns the product's navigation pattern once, it must hold true everywhere.
- **Fast Interactions:** Perceived speed is treated as a design requirement, not only a technical one — feedback must be immediate even when underlying processing takes time.
- **Easy Onboarding:** New users should be able to understand core navigation and primary workflows without extensive training, through consistency and clarity rather than guided tours.
- **Reduce Cognitive Load:** Every screen minimizes the number of decisions and the amount of information a user must hold in mind at once.
- **Encourage Productivity:** Every design decision is ultimately evaluated by whether it helps a professional user complete real business work faster and more accurately.

---

## 17. Motion Philosophy

Motion in INAKARA CRM exists only to clarify — never to decorate.

- **Subtlety:** Motion is small in scale and low in visual intensity.
- **Speed:** Transitions and animations are fast, calibrated to feel instantaneous rather than performative.
- **Purposefulness:** Every animation communicates a specific state change (something appearing, disappearing, reordering, loading) — motion without a clear communicative purpose is not used.
- **Hover Behavior:** Hover feedback is immediate, with no perceptible delay or lingering animation.
- **Loading Behavior:** Loading motion (skeletons, subtle progress indication) is calm and steady, never erratic or attention-grabbing.
- **Transition Philosophy:** Transitions between views or states are brief enough to never feel like an obstacle between the user and their next action.

---

## 18. Anti-Patterns

The following are explicitly prohibited across the entire product, without exception:

- Excessive or arbitrary use of color beyond the defined status and accent palette.
- Card-heavy layouts where cards are used as a default container rather than a deliberate grouping choice.
- Nested cards (a card inside a card) under any circumstance.
- Large, heavy, or multiple stacked shadows.
- Thick or high-contrast borders used decoratively.
- Random, non-systematic spacing values.
- Random, inconsistent corner radius values across components.
- Inconsistent typography — mixing type roles or scales arbitrarily across screens.
- Oversized or inconsistent icon sizing.
- Decorative gradients of any kind.
- Glassmorphism (frosted-glass, blur-heavy translucent panels).
- Neumorphism (soft-extruded, skeuomorphic shadow styling).
- Dashboard clutter — too many simultaneous widgets competing for attention.
- General visual noise — any element present without a clear functional purpose.
- Decorative illustrations or widgets that carry no business information.
- Marketing-style landing page layouts (hero sections, large decorative imagery) anywhere within the operational product.
- Unnecessary or purely decorative animation.

---

## 19. Future Scalability

This document is written to remain valid as INAKARA CRM expands beyond furniture into Manufacturing, Distribution, Retail, Construction, Logistics, Healthcare, Education, Automotive, and other industries, because:

- Every principle in this document is defined at the level of **information type** (primary/secondary/supporting, status, tabular data, forms) rather than **industry content**. A table is designed the same way whether it lists furniture orders or logistics shipments.
- The restrained, limited color philosophy is industry-agnostic by design — status meaning (success, warning, error, informational) is universal across every vertical this product may serve.
- Dashboard and navigation philosophy is defined around **roles and workflows** (Section 12, Section 11), not around furniture-specific concepts, allowing new industry modules to plug into the same navigational and dashboard logic defined here.
- Because decoration is deliberately minimized throughout, there is no industry-specific visual motif to "replace" when a new vertical is introduced — the interface remains equally appropriate for any professional, data-driven business context.
- Any future industry-specific visual need (e.g., a specialized visualization for a particular workflow) must be evaluated against this document's principles before being introduced, ensuring the platform's identity remains singular even as its use cases multiply.

---

## 20. Glossary

| Term | Definition |
|---|---|
| **Visual Noise** | Any interface element that draws attention without conveying necessary information. |
| **Status Color** | A color used exclusively to communicate a defined system state (e.g., success, warning, error), never for decoration. |
| **Density** | The amount of information shown per unit of screen space. |
| **Elevation** | The perceived visual "height" of an element above the base page, typically communicated through shadow. |
| **Primary Information** | The single most important fact a user needs from a given view. |
| **Anti-Pattern** | A design approach explicitly prohibited because it contradicts this document's principles. |

---

## 21. References

- `PROJECT_CONSTITUTION.md` — supreme authority; Section 7 (Design Philosophy) governs this document.
- `01-product-rules.md` — business context that this design philosophy must serve (roles, dashboards, workflow).
- `.ai/08-ui-ux-rules.md` *(future document)* — will translate these principles into concrete design tokens, spacing scale, and component-level standards.

---

*End of 02-design-principles.md — Version 1.0.0*
