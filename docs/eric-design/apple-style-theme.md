# Eric Grocy Apple-Style Theme Design

## Goal

Make the customized Grocy instance feel modern, calm, minimal, and Apple-like across the whole product while keeping Grocy's original Blade, Bootstrap 4, jQuery, DataTables, and backend architecture intact.

This is a redesign of an existing product UI, not a frontend rewrite.

## Design Read

Existing product UI redesign for personal home-management use, with an Apple-style minimalist language. The implementation should lean toward a CSS-first theme with selective Blade/JS adjustments only where CSS cannot produce acceptable interaction quality.

Dial values:

- Design variance: 5
- Motion intensity: 3
- Visual density: 5

The interface is a daily-use tool, so clarity and reliability are more important than visual drama.

## Recommended Direction

Use a layered approach:

1. Global CSS theme first.
2. Small shared layout refinements second.
3. Page-specific interaction fixes only when needed.

Do not rewrite the frontend as a standalone SPA. A rewrite would duplicate Grocy's client logic and make future upstream merges expensive.

## Architecture

### Theme Entry

Add a dedicated theme stylesheet in the fork, for example:

```text
public/css/eric_apple_theme.css
```

Load it after Grocy's built-in CSS and page styles, ideally near the existing `custom_css.html` load point in `views/layout/default.blade.php`.

This gives the theme a stable place in the fork and avoids relying only on runtime-mounted `custom_css.html`.

### Runtime Override

Keep Grocy's existing `GROCY_DATAPATH/custom_css.html` support. It remains useful for small deployment-specific overrides without rebuilding the image.

Recommended order:

1. Vendor CSS
2. Bootstrap CSS
3. Grocy CSS
4. Page styles
5. Eric Apple theme CSS
6. Runtime `custom_css.html`

### Scope Boundaries

Allowed in first version:

- CSS variables and theme tokens
- Global typography, background, cards, buttons, inputs, tables, modals, toasts, sidebar, navbar
- DataTables visual polish
- Mobile spacing and touch-target improvements
- Small Blade changes only if a component cannot be styled cleanly by CSS

Not allowed in first version:

- Replacing Bootstrap
- Introducing React, Vue, Tailwind, or shadcn
- Changing URLs, routes, API contracts, or permissions
- Rebuilding all screens as custom pages
- Complex animation systems
- Large information architecture changes

## Visual Language

### Theme

Use a light-first Apple-like theme.

The visual base should feel close to iOS/macOS settings and productivity apps:

- Soft light gray app background
- White elevated surfaces
- Thin translucent borders
- Subtle depth, not heavy shadows
- Calm blue or green accent
- Large readable controls
- Consistent rounded corners

Night mode can be handled later. The first version should make the light theme excellent.

### Color Tokens

Use semantic CSS variables instead of scattering colors:

```css
:root {
  --eric-bg: #f5f5f7;
  --eric-surface: rgba(255, 255, 255, 0.86);
  --eric-surface-solid: #ffffff;
  --eric-border: rgba(0, 0, 0, 0.08);
  --eric-text: #1d1d1f;
  --eric-muted: #6e6e73;
  --eric-accent: #007aff;
  --eric-accent-soft: rgba(0, 122, 255, 0.12);
  --eric-danger: #ff3b30;
  --eric-success: #34c759;
}
```

The accent should be used consistently. Avoid Bootstrap's default saturated `primary/success/danger/warning` visual language except where semantic state is important.

### Typography

Use the system Apple-style font stack:

```css
font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "PingFang SC", "Noto Sans CJK SC", "Segoe UI", sans-serif;
```

Do not introduce decorative serif fonts. This is a product UI, not a marketing page.

### Shape

Use one consistent radius system:

- Small controls: 10px-12px
- Cards and modals: 16px-20px
- Pills and compact buttons: full radius

Avoid mixed Bootstrap square controls and random rounded cards.

### Motion

Motion should be restrained:

- Hover color/background transitions
- Active press feedback using slight translate/scale
- Modal fade/scale only if it does not fight Bootstrap

No scroll hijacking, no large animated backgrounds, no decorative motion.

## Component Targets

### App Shell

Improve:

- Body background
- Fixed top nav
- Sidebar navigation
- Active page state
- Sidebar icons and text rhythm
- Mobile collapsed navigation

Desired feel:

- Sidebar as a quiet translucent rail
- Active item as a soft accent pill
- Top nav visually lighter and less Bootstrap-like

### Buttons

Normalize Bootstrap buttons:

- Primary: filled accent, high contrast
- Secondary: soft gray surface
- Danger: red text or red filled only for destructive confirmation
- Icon buttons: circular or pill, at least 36px touch target

Buttons need clear hover, focus, disabled, and active states.

### Forms

Improve:

- Inputs, selects, textareas, comboboxes, bootstrap-select
- Focus ring
- Labels
- Validation states
- Quantity/date picker controls

Desired feel:

- Slightly larger controls
- Soft filled input background
- Blue focus ring
- Errors visible but not visually aggressive

### Tables and DataTables

Grocy relies heavily on tables. First version should make them calmer without changing behavior.

Improve:

- Header typography
- Row spacing
- Hover state
- Zebra striping only if subtle
- DataTables filter/search controls
- Pagination buttons
- Responsive overflow

Avoid heavy borders on every cell.

### Cards and Panels

Normalize cards and page panels:

- White translucent surface
- Thin border
- Soft shadow
- Larger radius
- Consistent spacing

### Modals

Improve Bootstrap modals:

- Rounded large panel
- Softer backdrop
- Cleaner header/footer separation
- Better button spacing
- Mobile full-width behavior only when needed

### Toasts and Alerts

Make toasts and alerts less Bootstrap-like:

- Softer corners
- Semantic left border or icon
- Reduced color fill
- Better spacing

### Shopping List and Stock Pages

Do not redesign their structure in the global-theme phase, but ensure they benefit from:

- Better table/card readability
- Clearer action buttons
- Less cramped forms
- Better mobile tap targets

If CSS alone is insufficient, these become second-phase targeted page refinements.

## Implementation Phases

### Phase 1: Global Theme Foundation

Deliverables:

- `public/css/eric_apple_theme.css`
- Layout includes this CSS after Grocy/page styles
- CSS variables
- Global typography/background
- Buttons/forms/tables/modals/nav/sidebar polish

Success criteria:

- Most Grocy pages look visually unified immediately
- No business behavior changes
- No API changes
- Existing pages remain usable on desktop and mobile

### Phase 2: High-Frequency Page Polish

Target pages:

- Shopping list
- Shopping list item edit
- Shopping list item stock intake
- Stock overview
- Products
- Product edit

Deliverables:

- Page-specific CSS sections
- Minimal Blade adjustments only where required
- Mobile density improvements

Success criteria:

- Daily shopping and stock flows feel significantly less cramped
- Important actions are easier to find
- No regression to Grocy upstream functionality

### Phase 3: Interaction Refinement

Targets:

- Modal behavior
- Form focus flow
- Empty states
- Error states
- Confirmation dialogs
- Touch interactions

Deliverables:

- Small JS enhancements if necessary
- No new frontend framework

Success criteria:

- Common actions require fewer awkward clicks
- Mobile use is stable
- No hidden keyboard/focus traps

## Testing Plan

Manual smoke test these pages after Phase 1:

- Login
- Shopping list
- Add/edit shopping list item
- Add item to stock
- Stock overview
- Product list
- Product edit
- Quantity units
- Locations
- Settings
- API key management

Check viewports:

- Desktop 1440px
- Tablet 768px
- Mobile 390px

Check states:

- Normal
- Hover/focus
- Disabled
- Validation error
- Modal open
- DataTables pagination/search

## Risks

### Bootstrap Specificity

Bootstrap 4 and plugin CSS may require careful selector specificity. Avoid `!important` unless no stable alternative exists.

### Upstream Merge Cost

Keep customizations isolated in one CSS file and minimal layout includes. Avoid large Blade rewrites.

### Plugin Components

DataTables, bootstrap-select, combobox, date picker, Summernote, and FullCalendar each have their own styles. First version should cover common states, but some plugin-specific refinements may need follow-up.

### Night Mode

Grocy has internal night mode CSS. The first version should focus on light mode. Night mode should be a separate phase to avoid half-polished dual themes.

## Decision

Proceed with CSS-first full-site visual redesign plus selective template/JS refinements. Do not rewrite the frontend.
