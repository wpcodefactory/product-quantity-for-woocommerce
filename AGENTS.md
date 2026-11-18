---
applyTo: "**"
---

# Quantity input regression rules

## Cart quantity must always be visible

A recurring support issue (reported 4 times by the same customer): after adding a product to the cart, the quantity cell is blank on the classic cart page. It happened whenever the product's min quantity equals its max (e.g. min == stock, since WooCommerce 10.1+ derives the input max from stock, and when the plugin itself sets min == max).

Keep these guards in place — do not remove or weaken them:

1. `WPFMMSQ_Core::set_quantity_input_args()` in `includes/class-wpfmmsq-core.php`: on `is_cart()` when `(float) $args['max_value'] === (float) $args['min_value']`, set `min_value = 0` and `readonly = true` so WooCommerce core / the HTML5 template render a **visible readonly** input.
2. `includes/templates/global/quantity-input.php`: the locked branch `( $max_value && $min_value === $max_value ) || $readonly` must render a **visible readonly input** on `is_cart()`. Only non-cart pages (product/loop) may render the hidden div.
3. The options `wpfmmsq_min_hide_qty_input_on_{product_page|cart|loop}_if_stock_matches_min`: `'yes'` means enabled/hide. `set_quantity_input_args()` must compare `'yes' === get_option(...)` — this was inverted before 5.4.6, which silently disabled the feature (and the later fix made it actually run).

## Regression checklist for any change to quantity input logic

Before finishing changes to `set_quantity_input_args()`, the quantity templates (`includes/templates/global/quantity-*.php`), or the min/max/step script data, test locally:

1. Simple product: stock 6 (manage stock, no backorders), per-product min 6, decimal quantities enabled.
   - Product page: quantity locked at 6; customers cannot pick above stock.
   - Adding 6 succeeds; adding 6.5 is rejected and the cart stays empty.
   - Classic cart (`/cart-classic/`) shows a visible readonly "6" — never a blank cell.
2. Variable product with a variation stock 6 / min 6: available variation data and the `wpfmmsq_update_get_input_options` AJAX response must return `min=6, max=6, default=6`.
3. A product with min < stock keeps a normal editable quantity input on both product page and cart.

## How to reproduce

- Use a WooCommerce dev environment with WooCommerce 10.1+ (so the quantity input max defaults to the stock quantity) and a **classic cart page** — the Cart/Checkout block cart doesn't render the classic quantity cell.
- Create a simple product (admin UI or `wp post create --post_type=product ...`), then set the meta: `_manage_stock yes`, `_stock 6`, `_stock_status instock`, `_wpfmmsq_min 6`, with decimal quantities enabled in the plugin settings.
- Add quantity 6 to the cart and check the classic cart quantity cell: it must show a visible readonly "6", never a blank cell. Attempting to add 6.5 must be rejected and leave the cart empty.
- Delete the test products after testing.
