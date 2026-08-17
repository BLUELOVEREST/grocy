# Eric Dashboard Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a new Eric Dashboard entry to Grocy that pixel-matches the approved Stitch direction while leaving all existing Grocy pages and workflows intact.

**Architecture:** Implement the dashboard as an additive Blade page at `/eric-dashboard`. Server-side data provides lightweight summaries and links; client-side JavaScript handles product search and navigation using existing Grocy routes/APIs.

**Tech Stack:** PHP Slim routes/controllers, Blade templates, Bootstrap 4, Font Awesome, existing Grocy JavaScript helpers.

---

### Task 1: Reference Capture

**Files:**
- Reference: `docs/stitch/operational-dashboard-overview.html`
- Reference: Stitch screen `45c7e13c3b464fc5a6231a2fbf9fb9ce`

- [x] **Step 1: Download Stitch HTML**

Run:

```bash
curl -L "<stitch-html-download-url>" -o docs/stitch/operational-dashboard-overview.html
```

Expected: `docs/stitch/operational-dashboard-overview.html` exists and contains the generated dashboard HTML.

### Task 2: Add Page Route And Data

**Files:**
- Modify: `routes.php`
- Modify: `controllers/SystemController.php`

- [ ] **Step 1: Add `/eric-dashboard` route**

Add a GET route in the authenticated UI route group that calls `SystemController::EricDashboard`.

- [ ] **Step 2: Add controller method**

Create `EricDashboard()` to render `ericdashboard` with these data sets:

```php
[
    'shoppingLists' => $this->DB->shopping_lists_view()->orderBy('name', 'COLLATE NOCASE'),
    'shoppingListItems' => $this->DB->uihelper_shopping_list()->orderBy('product_name', 'COLLATE NOCASE')->limit(8),
    'missingProducts' => StockService::GetInstance()->GetMissingProducts(),
    'stockAlerts' => $this->DB->uihelper_stock_current_overview()->where('is_in_stock_or_below_min_stock = 1')->limit(8),
    'recentStockTransactions' => $this->DB->uihelper_stock_journal()->orderBy('row_created_timestamp', 'DESC')->limit(6)
]
```

### Task 3: Add Isolated Dashboard View

**Files:**
- Create: `views/ericdashboard.blade.php`
- Create: `public/viewjs/ericdashboard.js`

- [ ] **Step 1: Create Blade layout**

Build sections for global product search, shopping list, stock alerts, quick actions, and recent activity. Use only links/forms that point to existing Grocy routes.

- [ ] **Step 2: Create JavaScript behavior**

Implement product search on Enter or search button click. Fetch existing `/api/objects/products?query[]=active=1` data, filter locally by product name, and render clickable results linking to `/product/{id}`.

### Task 4: Add Sidebar Entry

**Files:**
- Modify: `views/layout/default.blade.php`

- [ ] **Step 1: Add top-level nav item**

Add an `Eric Dashboard` nav item before stock overview. Mark it active when `$viewName == 'ericdashboard'`.

### Task 5: Verify

**Files:**
- Verify: `routes.php`
- Verify: `controllers/SystemController.php`
- Verify: `views/ericdashboard.blade.php`
- Verify: `public/viewjs/ericdashboard.js`

- [ ] **Step 1: Syntax check PHP**

Run:

```bash
php -l controllers/SystemController.php
php -l routes.php
```

Expected: no syntax errors.

- [ ] **Step 2: Compare with Stitch reference**

Open the page locally and compare against `docs/stitch/operational-dashboard-overview.html` for layout, color, spacing, and module structure.
