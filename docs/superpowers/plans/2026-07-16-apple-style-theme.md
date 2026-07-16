# Apple Style Theme Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a full-site Apple-inspired visual theme to Eric Grocy without rewriting the Grocy frontend.

**Architecture:** Keep Grocy's Blade, Bootstrap 4, jQuery, and DataTables stack intact. Add one fork-owned CSS file loaded after Grocy page styles and before the runtime `custom_css.html`, so the fork has a stable theme layer while deployment-specific overrides still win.

**Tech Stack:** PHP Blade templates, Bootstrap 4 CSS classes, Grocy custom CSS pipeline, native CSS variables.

---

### Task 1: Add Fork Theme Stylesheet

**Files:**
- Create: `public/css/eric_apple_theme.css`

- [ ] **Step 1: Create theme token layer**

Create `public/css/eric_apple_theme.css` with CSS variables for background, surface, text, accent, borders, radius, focus, and shadow. Include a `body.night-mode` token override so enabling Grocy internal night mode does not produce unreadable mixed colors.

- [ ] **Step 2: Style global shell and navigation**

In the same file, override `body`, `#mainNav`, `.navbar-sidenav`, `.nav-link`, `.content-wrapper`, and responsive menu rules. Keep dimensions compatible with `grocy_menu_layout.css`; only change visual treatment, spacing, shadows, colors, and transitions.

- [ ] **Step 3: Style common Bootstrap components**

In the same file, override `.card`, `.btn`, `.form-control`, `.custom-select`, `.dropdown-menu`, `.modal-content`, `.alert`, `.badge`, `.table`, `.dataTables_wrapper`, and pagination. Use one radius system and one blue accent color.

- [ ] **Step 4: Add accessibility and motion guards**

Add strong focus-visible states, `prefers-reduced-motion` handling, `prefers-reduced-transparency` fallback, and mobile compacting rules.

### Task 2: Load Theme in Grocy Layout

**Files:**
- Modify: `views/layout/default.blade.php`

- [ ] **Step 1: Insert stylesheet include**

Add this after `@stack('pageStyles')` and before `custom_css.html`:

```blade
<link href="{{ $U('/css/eric_apple_theme.css?v=', true) }}{{ $version }}"
	rel="stylesheet">
```

- [ ] **Step 2: Preserve user override order**

Keep the existing `custom_css.html` include after the new theme stylesheet so per-install customization can override the fork theme.

### Task 3: Verify and Commit

**Files:**
- Verify: `views/layout/default.blade.php`
- Verify: `public/css/eric_apple_theme.css`

- [ ] **Step 1: Run Blade syntax check**

Run: `php -l views/layout/default.blade.php`
Expected: `No syntax errors detected in views/layout/default.blade.php`

- [ ] **Step 2: Run whitespace diff check**

Run: `git diff --check`
Expected: no output and exit code `0`.

- [ ] **Step 3: Confirm theme include exists**

Run: `rg -n "eric_apple_theme" views/layout/default.blade.php public/css/eric_apple_theme.css`
Expected: one layout include and CSS file matches.

- [ ] **Step 4: Commit**

Run:

```bash
git add docs/superpowers/plans/2026-07-16-apple-style-theme.md views/layout/default.blade.php public/css/eric_apple_theme.css
git commit -m "feat: add apple style grocy theme"
```
