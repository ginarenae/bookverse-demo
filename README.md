# BookVerse Demo

Ths is a technical test project integrating **WordPress (CPT + ACF)** with **WooCommerce + Stripe** done by Gina Renae on 31st October 2025.

## Overview
This plugin demonstrates:

- Automatic import of book data (from `books.csv`)
- Custom Post Type: `Book`
- ACF fields: Author, ISBN, Price, Linked Product ID
- Automatic creation + mapping of WooCommerce products
- “Buy with Stripe” button on each Book page
- Full Stripe Test Mode checkout flow

---

## Setup Instructions

### Requirements
- WordPress + WooCommerce installed locally (via XAMPP, MAMP, etc.)
- ACF plugin installed (free version)
- Stripe Payment Gateway for WooCommerce plugin installed
- WooCommerce test mode enabled

---

### Installation
1. Clone or copy this folder into  
   `/wp-content/plugins/bookverse-demo`
2. Activate **BookVerse Demo** plugin in WordPress Admin.
3. On activation:
   - Books from `books.csv` are imported automatically.
   - Matching WooCommerce products are created.
   - Each Book is linked to its WooCommerce product.

---

### Test the Flow
1. Go to **Books → All Books** (3 imported automatically).
2. Open one Book — view Author, ISBN, Price.
3. Click “Buy with Stripe”.
4. Product added to cart → Checkout → use test card:
5. Complete payment → see order confirmation page.

---

### Technical Notes
- CPT registration and import happen via `register_activation_hook`.
- Products mapped by **ISBN ↔ SKU**.
- All book data defined in `/books.csv`.
- “Buy with Stripe” button added via content filter on single Book pages.

---

### 📹 Loom Demo Includes
1. Plugin activation  
2. Book CPT populated automatically  
3. WooCommerce product mapping verified  
4. Stripe test checkout flow completed  

---

**Author:** Gina Renae \ www.ginarenae.dev 
**Time Spent:** 2 hours  
**Status:** Completed and Functional
