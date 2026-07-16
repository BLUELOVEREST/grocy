# Product Property Templates Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add parent-product-scoped property templates and child-product property values to Eric Grocy.

**Architecture:** Add two database tables and a small service/controller pair for custom API endpoints. Extend the existing product form with a template editor for root products and a value editor for child products, leaving Grocy global userfields unchanged.

**Tech Stack:** PHP 8.5, Slim routes/controllers, LessQL database access, SQLite migrations, Blade templates, jQuery.

---

### Task 1: Database Model

**Files:**
- Create: `migrations/0257.sql`

- [ ] Create `product_property_definitions` with `parent_product_id`, `name`, `label`, `type`, `unit`, `options`, `input_required`, `sort_number`, and `row_created_timestamp`.
- [ ] Create `product_property_values` with `product_id`, `property_definition_id`, `value`, and `row_created_timestamp`.
- [ ] Add indexes and cleanup triggers for product deletion and definition deletion.

### Task 2: Backend API

**Files:**
- Create: `services/ProductPropertyTemplatesService.php`
- Create: `controllers/ProductPropertiesController.php`
- Modify: `routes.php`

- [ ] Implement service methods to list and replace template definitions for a parent product.
- [ ] Implement service methods to list and replace values for a product.
- [ ] Register four API routes for templates and values.
- [ ] Validate property types against `text`, `number`, `select`, and `checkbox`.

### Task 3: Product Form UI

**Files:**
- Modify: `views/productform.blade.php`
- Modify: `public/viewjs/productform.js`

- [ ] Add a property template section for products without a parent product.
- [ ] Add a product properties section for products with a parent product.
- [ ] Load template definitions when the selected parent product changes.
- [ ] Save template definitions after saving a root product.
- [ ] Save property values after saving a child product.

### Task 4: Verification

**Files:**
- Verify changed PHP, JS, and SQL files.

- [ ] Run `php -l` for new/changed PHP files.
- [ ] Run `node --check public/viewjs/productform.js`.
- [ ] Run `git diff --check`.
- [ ] Commit the changes.
