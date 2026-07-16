# Product Property Templates Design

## Goal

Add parent-product-scoped property templates to Eric Grocy so a product type can define which attributes its child products should fill, without changing Grocy's global userfields behavior.

## Current Model

Grocy's `products` entity already contains stock-related defaults such as product group, parent product, quantity units, locations, best-before settings, min stock, label settings, quick consume/open amounts, and stock behavior flags.

Grocy `userfields` are entity-scoped. A userfield added to `products` appears for all products. This remains useful for global product extensions, but it does not solve product-type-specific attributes such as screw diameter/length.

Grocy parent products are regular products used for stock aggregation and substitution. They do not currently define child-product attribute templates.

## New Model

Add two new tables:

- `product_property_definitions`: fields defined by a parent product for its child products.
- `product_property_values`: values filled by a concrete child product for those definitions.

A property definition belongs to a parent product. A property value belongs to a child product and references one definition.

Parent product examples:

```text
parent product = 螺丝
property definitions = 头型, 槽型, 直径, 长度, 材质
```

Child product examples:

```text
child product = 平头十字螺丝 M4*10
property values = 平头, 十字, M4, 10mm, 不锈钢
```

## Boundaries

- Do not remove or repurpose Grocy userfields.
- Do not make every product show every property template field.
- Do not implement automatic inheritance of property values.
- Do not implement multi-level parent products.
- Do not change stock aggregation semantics.

## Product Form Behavior

For root/parent-capable products, show a "Property template" section where the user can define fields for child products.

For child products, show a "Product properties" section populated from the selected parent product's template. Values are saved against the child product.

When creating a child product and selecting a parent product, the form should load that parent's property template dynamically.

## Field Types

Initial supported field types:

- text
- number
- select
- checkbox

Each definition stores:

- machine name
- label
- type
- optional unit
- optional select options, one per line
- required flag
- sort order

## API

Add lightweight custom API endpoints instead of overloading `userfields`:

- `GET /api/product-property-templates/{parentProductId}`
- `PUT /api/product-property-templates/{parentProductId}`
- `GET /api/product-properties/{productId}`
- `PUT /api/product-properties/{productId}`

## Success Criteria

- Existing product userfields still work globally.
- Parent products can define template fields.
- Child products can fill values for the parent's template fields.
- Template definitions are not values and are not copied into child products.
- Product deletion cleans up related template definitions and values.
