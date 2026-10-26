You are a senior Laravel developer and e-commerce architect. Build a complete,
production-ready, all-in-one e-commerce platform for the Bangladesh market
(multi-language: Bangla + English, currency: BDT ৳).

## 1. TECH STACK
- Laravel 11, PHP 8.3, MySQL 8, Redis (cache/queue/session)
- Frontend: Blade + Tailwind CSS + Alpine.js (or Livewire 3), Vite
- Auth: Laravel Breeze/Sanctum, roles & permissions via spatie/laravel-permission
- Media: spatie/laravel-medialibrary + image optimization (WebP)
- Admin panel: Filament v3 (or custom Blade admin)
- Queue: Redis + Horizon; Scheduler for cron jobs
- Testing: Pest/PHPUnit; Code style: Laravel Pint
- Follow Repository/Service pattern, Form Requests, Policies, Resources, Events/Listeners

## 2. USER ROLES
Super Admin, Admin, Manager, Staff/Order Handler, Vendor (optional multi-vendor), Customer, Guest

## 3. FRONTEND (CUSTOMER) FEATURES
- Home: hero slider, flash sale countdown, featured/new/best-selling products,
  category showcase, brand slider, banners, testimonials, newsletter
- Product listing: filters (category, brand, price range, rating, color, size,
  availability), sorting, pagination/infinite scroll, grid/list view
- Product details: image gallery + zoom, variants (size/color), stock status,
  price/discount, SKU, description, specifications, reviews & ratings,
  Q&A, related products, share buttons, add to wishlist/compare
- Smart search: live suggestions, search by name/SKU/category (Laravel Scout)
- Cart (guest + logged-in, persistent), mini cart, coupon apply
- Checkout: guest checkout, saved addresses, division/district/upazila
  dropdowns (Bangladesh), delivery charge by area, order notes, order summary
- Wishlist, product compare, recently viewed
- Customer dashboard: orders, order tracking, invoice download (PDF),
  returns/refund request, addresses, profile, wallet/reward points, reviews
- Auth: email/phone registration, OTP login (SMS), social login (Google/Facebook),
  forgot password
- Static pages: About, Contact, FAQ, Privacy Policy, Terms, Return Policy
- Blog with categories/tags (SEO content)
- Live chat / WhatsApp / Messenger button
- Multi-language (bn/en) and multi-currency ready

## 4. ADMIN PANEL FEATURES
- Dashboard: sales, orders, revenue charts, top products, low stock alerts,
  new customers, recent orders
- Product management: CRUD, variants, attributes, bulk import/export (Excel/CSV),
  multiple images, SEO fields, stock, SKU/barcode, cost price, tags
- Category (nested), Brand, Attribute, Unit management
- Order management: statuses (Pending, Confirmed, Processing, Shipped, Delivered,
  Cancelled, Returned, Refunded), status history, invoice/packing slip print,
  courier assignment, bulk actions, order notes
- Customer management, customer groups, block/unblock
- Inventory: stock in/out, stock history, low-stock notification, supplier & purchase
- Coupons/Discounts: percentage/fixed, min order, usage limit, expiry, flash sale
- Reviews & Q&A moderation
- Return/Refund management
- CMS: banners, sliders, pages, blog, menu builder, homepage section manager
- Reports: sales, profit, product, customer, tax, stock (export PDF/Excel)
- Staff & Role-Permission management, activity log (spatie/laravel-activitylog)
- Settings: site info, logo/favicon, SMTP, SMS gateway, payment, shipping,
  tax/VAT, SEO, social links, maintenance mode, backup

## 5. PAYMENT & SHIPPING (BANGLADESH)
- Payments: Cash on Delivery, bKash, Nagad, Rocket, SSLCommerz, Stripe/PayPal
  (optional); payment status tracking, webhook handling, partial/advance payment
- Courier integration: Pathao, Steadfast, RedX (API: booking, tracking, COD update)
- Delivery charge rules: inside/outside Dhaka, weight-based, free shipping threshold
- SMS: order confirmation/status via BulkSMSBD or similar; Email notifications

## 6. MARKETING & SEO
- SEO-friendly URLs/slugs, meta tags, Open Graph, sitemap.xml, robots.txt, schema.org
- Facebook Pixel + Conversions API, Google Analytics 4, Google Tag Manager
- Abandoned cart recovery (email/SMS), newsletter, referral & loyalty points
- Affiliate system (optional), product feed for Facebook/Google Merchant

## 7. DATABASE (core tables)
users, roles, permissions, addresses, categories, brands, products,
product_variants, attributes, attribute_values, product_images, carts, cart_items,
wishlists, orders, order_items, order_status_histories, payments, shipments,
coupons, reviews, questions, returns, refunds, stock_movements, suppliers,
purchases, banners, pages, blog_posts, settings, notifications, activity_logs,
divisions, districts, upazilas
- Use migrations, factories, seeders (demo data + BD location seeder)
- Proper indexes, foreign keys, soft deletes

## 8. SECURITY & PERFORMANCE
- CSRF, XSS, SQL injection protection, rate limiting, 2FA for admin
- Validation via Form Requests, authorization via Policies
- Eager loading (avoid N+1), query caching, Redis cache, image lazy loading
- CDN-ready assets, queue heavy tasks (email, SMS, PDF), daily DB backup
- Responsive, mobile-first design, PWA-ready, Core Web Vitals optimized
- Error logging (Sentry/Telescope in dev)

## 9. DELIVERY PLAN (build in phases, one at a time)
Phase 1: Project setup, auth, roles, admin layout, settings
Phase 2: Categories, brands, products, variants, media, inventory
Phase 3: Frontend home, listing, details, search, cart, wishlist
Phase 4: Checkout, orders, payments, invoices, notifications
Phase 5: Customer dashboard, reviews, returns, coupons
Phase 6: Courier/SMS integration, reports, CMS, blog, SEO
Phase 7: Testing, optimization, security hardening, deployment guide

## 10. OUTPUT RULES
- Show folder structure first, then code file by file with full file paths
- Write clean, commented, PSR-12 compliant code
- Provide artisan commands, .env variables, and run instructions each phase
- Ask me before moving to the next phase