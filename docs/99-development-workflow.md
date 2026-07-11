# INAKARA CRM Development Workflow

## Mandatory Workflow

Every development task MUST follow this process.

---

## Step 1 - Read Context

Before doing anything:

- Read PROJECT_CONSTITUTION.md
- Read every document inside /docs
- Read Figma reference
- Read current source code
- Understand previous implementation

Never assume requirements.

---

## Step 2 - Analyze

Determine:

- current sprint
- completed modules
- dependencies
- possible risks

If something is unclear, ask first.

---

## Step 3 - Planning

Before writing code:

Produce

- Implementation Plan
- Files affected
- Database changes
- API changes
- UI changes
- Risks

Wait for approval.

---

## Step 4 - Implementation

Implement incrementally.

Never create unnecessary abstraction.

Reuse existing components.

Follow Ponytail principles.

Follow all architecture documents.

---

## Step 5 - Self Review

After implementation:

Review

- duplicated code
- unnecessary files
- architecture violations
- security issues
- performance issues
- accessibility
- consistency

Fix problems before continuing.

---

## Step 6 - Testing

Always run

php artisan test

npm run build

vendor/bin/pint

TypeScript type checking

Fix every failure.

---

## Step 7 - Completion

Generate

- Summary
- Modified files
- Breaking changes
- Suggested commit message

Stop.

Never continue to the next sprint automatically.