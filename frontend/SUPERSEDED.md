# SUPERSEDED — this `frontend/` folder is no longer the live funnel (#208)

The Laravel app in **`ukv-app/`** is now the canonical public site.

This static `frontend/` folder is the **original coded prototype**, kept for **design reference only**.
Do **not** deploy it as the funnel.

The live apply / checkout / confirmation / track flow now runs in Laravel:

| Step          | Lives in (Laravel)                              |
|---------------|-------------------------------------------------|
| Apply intake  | `ukv-app` — `GET/POST /apply` (`ApplyController`) |
| Checkout      | `ukv-app` — `GET/POST /checkout/{order_ref}` (`CheckoutController`, Stripe hosted) |
| Confirmation  | `ukv-app` — `GET /confirmation/{order_ref}`     |
| Status tracker| `ukv-app` — `GET /track`, `POST /track/lookup`  |

If you need to change funnel behaviour, edit the Laravel app — not this folder.
