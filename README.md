# ঢাকেবির জার্সি হাউজ (Dhakebir Jersey House): Laravel Project Prompt

> **ব্যবহার:** এই ফাইলটি প্রজেক্ট রুটে `AGENTS.md` (অথবা `.github/copilot-instructions.md`) নামে রাখুন। তারপর AI এজেন্টকে বলুন: **"Read AGENTS.md and start Phase 1."** একবারে সব ফেজ না চেয়ে ধাপে ধাপে এগোন।

---

## 0. ROLE & BRAND

You are a senior Laravel developer and e-commerce architect. Build a complete, production-ready online store for **ঢাকেবির জার্সি হাউজ (Dhakebir Jersey House)**, which sells university-branded jerseys in various designs in Bangladesh.

- Business model: single-seller store (NOT multi-vendor)
- Language: Bangla + English (default: Bangla)
- Currency: BDT ৳
- Main audience: university students, mostly on mobile phones
- Brand name must appear in: header/logo, footer, invoice, emails, SMS sender text, SEO titles
- Brand colors / logo: configurable from admin settings (use placeholders until provided)

---

## 1. TECH STACK

- Laravel 11, PHP 8.3, MySQL 8, Redis (cache/queue)
- Frontend: Blade + Tailwind CSS + Alpine.js (or Livewire 3), Vite
- Admin: Filament v3
- Auth: Laravel Breeze; roles via `spatie/laravel-permission`
- Media: `spatie/laravel-medialibrary` (WebP, thumbnails)
- PDF invoices: `barryvdh/laravel-dompdf`; Excel: `maatwebsite/excel`
- Testing: Pest; Code style: Laravel Pint
- Architecture: Service/Repository pattern, Form Requests, Policies, API Resources, Events/Listeners

---

## 2. ROLES

Super Admin, Admin, Order Manager, Customer, Guest

---

## 3. PRODUCT CATALOG (JERSEY-SPECIFIC)

- **Categories:** Football, Cricket, Polo/T-shirt, Training/Sports, Winter/Hoodie, Limited Edition, Alumni, Fan Wear
- **Collections/Tags:** Batch (e.g., 23rd Batch), Department, Club, Event, Season, Tournament (e.g., Inter-University Cup)
- **Each product:** multiple design images (front/back), fabric type, GSM, fit (regular/slim), collar type, sleeve type, color options, SKU, price, discount, description
- **Variants:** Size (XS, S, M, L, XL, XXL, 3XL, Kids) × Color, with per-variant stock
- **Size chart:** per-product table (chest/length/shoulder in inch & cm) shown in a popup, plus a "How to measure" guide
- **Stock modes:** In Stock, Low Stock, Out of Stock, Pre-order (with estimated delivery date and pre-order close date)
- **Limited edition:** stock counter, "Only X left" badge
- **Reviews:** with photo upload

---

## 4. CUSTOMIZATION FEATURES (KEY FEATURE)

- Name & number printing on the product page:
  - Name (max 12 chars, uppercase), Number (0–99)
  - Extra charge configurable from admin
  - Live preview mockup of the back side (Alpine.js/canvas overlay on jersey image)
  - Font style choice (2–3 options), print color choice
- Optional sleeve/chest patch (Department logo, Batch year) as add-on
- Admin can enable/disable customization per product
- Custom data saved in `order_items` (JSON: name, number, font, color, patch)
- Production sheet export for admin (order-wise name/number/size list in Excel/PDF)

---

## 5. BULK / TEAM / BATCH ORDER

- **Team Order form:** one design, multiple rows (Name, Number, Size) in a table with add/remove rows and CSV upload
- **Bulk discount slabs** set from admin (e.g., 10+ pcs: 5%, 25+ pcs: 10%)
- **Batch/Department group campaign:** admin creates a campaign with a deadline; students join via link and order; admin sees campaign-wise totals
- **Quote request form** for 50+ pieces

---

## 6. CUSTOMER FRONTEND

- **Home:** hero slider (new jersey drop), pre-order countdown, featured designs, category/collection showcase, best sellers, student photo gallery (customers wearing jerseys), testimonials, Facebook/Instagram link
- **Listing:** filters (category, size, color, price, batch, department, availability), sorting, pagination
- **Product page:** gallery with zoom, front/back toggle, size selector, size chart, customization panel, stock status, reviews, related designs
- **Cart** (guest + logged-in), coupon, mini cart; **Wishlist**
- **Checkout:** guest checkout; division/district/upazila dropdowns; delivery charge by area (inside/outside Dhaka); **Campus Pickup** (free) with pickup point selection; order note
- **Student verification (optional):** upload student ID or use university email for student discount
- **Order tracking** by order number + phone; invoice PDF download
- **Customer dashboard:** orders, addresses, profile, wishlist
- **Auth:** phone/email registration, OTP (SMS), Google login
- **Static pages:** About, Contact, FAQ, Return/Exchange Policy (size exchange rules), Privacy, Terms
- WhatsApp / Messenger chat button

---

## 7. ADMIN PANEL

- **Dashboard:** sales, orders, revenue chart, best-selling designs, low-stock alerts, pending pre-orders, today's orders
- **Catalog:** product CRUD with variants, bulk import/export, image gallery, SEO fields; Category/Collection/Batch/Department/Size-chart management
- **Orders:** statuses (Pending, Confirmed, In Production, Ready, Shipped, Delivered, Cancelled, Returned), status history, invoice & packing slip print, bulk status update, order notes
- **Production management:** confirmed customized orders grouped by design/size for the printing vendor; export sheet
- **Pre-order management:** close date, batch production, notify customers on arrival
- **Inventory:** stock in/out history, low-stock notification
- **Coupons:** percentage/fixed, min order, expiry, usage limit, student-only coupon
- **Returns/Exchange** (size exchange) request management
- **Reviews** moderation
- **CMS:** banners, sliders, pages, blog, homepage section manager
- **Reports:** sales, product-wise, size-wise, customization orders; export Excel/PDF
- **Staff** roles & permissions, activity log
- **Settings:** site info, logo, SMTP, SMS gateway, payment, delivery charges, social links, SEO, maintenance mode

---

## 8. PAYMENT, SHIPPING, NOTIFICATIONS

- **Payments:** Cash on Delivery, bKash, Nagad, Rocket, SSLCommerz; advance payment option for customized/pre-order items (e.g., 50% advance, rest on delivery); payment status tracking and webhook handling
- **Courier:** Pathao / Steadfast API (booking, tracking, COD amount)
- **SMS** (BulkSMSBD or similar) + **Email:** order confirmation, status update, pre-order arrival, abandoned cart reminder

---

## 9. SEO & MARKETING

- SEO-friendly slugs, meta tags, Open Graph, `sitemap.xml`, schema.org Product
- Facebook Pixel + Conversions API, Google Analytics 4
- Referral/discount code for campus ambassadors
- Social share buttons, WhatsApp share for group orders

---

## 10. DATABASE (CORE TABLES)

`users, roles, permissions, addresses, categories, collections, batches, departments, products, product_variants, product_images, size_charts, customization_options, carts, cart_items, wishlists, orders, order_items, order_status_histories, payments, shipments, coupons, bulk_discount_slabs, team_orders, campaigns, campaign_orders, pre_orders, reviews, returns, stock_movements, banners, pages, blog_posts, settings, notifications, activity_logs, divisions, districts, upazilas, pickup_points`

- Migrations, factories, seeders (demo jerseys + Bangladesh location seeder)
- Proper indexes, foreign keys, soft deletes

---

## 11. SECURITY & PERFORMANCE

- CSRF/XSS/SQLi protection, rate limiting, 2FA for admin, strict file upload validation
- Eager loading (avoid N+1), Redis cache, lazy-loaded WebP images, queued emails/SMS/PDF
- Mobile-first responsive design
- Daily DB backup, error logging

---

## 12. DELIVERY PLAN (ONE PHASE AT A TIME)

| Phase | Scope |
|-------|-------|
| 1 | Setup, auth, roles, Filament admin, settings, location seeder |
| 2 | Categories, collections, products, variants, size chart, stock, pre-order |
| 3 | Frontend home, listing, product page, search, cart, wishlist |
| 4 | Customization (name/number preview), checkout, orders, payments, invoice |
| 5 | Team/bulk/batch orders, coupons, student discount, production sheet export |
| 6 | Customer dashboard, reviews, exchange, courier, SMS, reports, CMS, SEO |
| 7 | Testing, optimization, security, deployment guide (VPS/cPanel) |

**MVP = Phase 1 to 4.** Launch after Phase 4, then add the rest.

---

## 13. OUTPUT RULES

- Show the folder structure first, then code file by file with full paths
- Clean, commented, PSR-12 compliant code
- Provide artisan commands, `.env` variables, and run instructions for each phase
- **Ask me before moving to the next phase**

---

## 14. BUSINESS NOTES (FOR THE OWNER)

1. **লোগো অনুমতি:** ইউনিভার্সিটির লোগো বাণিজ্যিকভাবে ব্যবহারের আগে কর্তৃপক্ষ বা স্টুডেন্ট অ্যাফেয়ার্স অফিসের লিখিত অনুমতি নিন।
2. **প্রি-অর্ডার মডেল:** স্টক না রেখে অ্যাডভান্স পেমেন্টে ব্যাচ প্রোডাকশন করালে ঝুঁকি কমে।
3. **ডিজাইন ফাইল:** প্রতিটি জার্সির সামনে ও পেছনের মকআপ আলাদা PNG (transparent background) রেডি রাখুন, লাইভ নাম/নম্বর প্রিভিউয়ের জন্য লাগবে।
4. **পেমেন্ট/কুরিয়ার API:** bKash, SSLCommerz, Pathao, Steadfast এর অফিসিয়াল ডকুমেন্টেশন আলাদা করে AI-কে দিলে ইন্টিগ্রেশন নির্ভুল হবে।
5. **Phase শুরুর কমান্ড:** `Read AGENTS.md and start Phase 1.`
