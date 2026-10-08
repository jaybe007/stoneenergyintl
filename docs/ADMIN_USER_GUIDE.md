# STONE ENERGY INT'L LTD — Administrator User Guide

Welcome to the **STONE ENERGY INT'L LTD** Custom Enterprise CMS. This guide explains how to manage your corporate website, product catalog, project portfolio, RFQ pipeline, and site settings without writing code.

---

## 1. Accessing the Admin Portal

* **URL:** `http://localhost/stoneenergyintl/admin/` or `https://yourdomain.com/admin/`
* **Default Credentials:**
  * **Username:** `admin`
  * **Password:** `AdminPassword2026!`
* **Security Notice:** The system includes automatic login throttling. Entering incorrect passwords more than 5 times results in a temporary 15-minute security lockout.

---

## 2. Administrator Roles & Permissions

The CMS enforces granular Role-Based Access Control (RBAC):

1. **SUPER ADMIN:**
   * Full, unrestricted control over the entire website, site settings, database backups, audit logs, and administrative user accounts.
2. **ADMIN:**
   * Manages RFQs, contact messages, products, projects, services, blog posts, media library, and site settings.
3. **EDITOR:**
   * Can create, edit, and publish content (services, products, projects, blog posts, pages, and media) but cannot modify system settings or manage users.
4. **CONTENT MANAGER:**
   * Focuses on publishing articles, updating project photos, and adding new product catalogue items.

---

## 3. Managing Request for Quotes (RFQs)

The RFQ system is a core commercial feature of the website.

1. Navigate to **Request for Quotes** in the sidebar.
2. View incoming RFQs categorized by status badges (`NEW`, `UNDER REVIEW`, `QUOTATION PREPARED`, `SENT`, `NEGOTIATION`, `APPROVED`, `COMPLETED`, `CANCELLED`).
3. Click **Review &rarr;** to open any RFQ:
   * Inspect customer contact details, industry, and quantity.
   * Read the detailed technical project description.
   * Download any client-attached specification PDF or drawing.
   * Update the status dropdown.
   * Add internal commercial notes.
   * Check **"Send email notification to client"** to automatically dispatch a status update email.
   * Click **"Print RFQ Sheet"** for physical tender folders or estimator review meetings.

---

## 4. Managing the Product Catalogue

1. Navigate to **Products**.
2. Click **"+ Add New Product"** or click **Edit** on an existing item:
   * **Product Name:** e.g., *Industrial High-Pressure Flanged Ball Valves*
   * **Item SKU:** e.g., *SEI-OG-001*
   * **Category:** Select from the 9 core sector categories.
   * **Short Summary:** Brief 1-2 sentence description shown on product cards.
   * **Full Description:** Detailed product narrative.
   * **Technical Specifications:** Enter specifications separated with double pipes `||` (e.g., `Material: ASTM Carbon Steel||Pressure: ANSI 300#||Sizes: 2" - 12"`).
   * **Availability:** Choose between *In Stock*, *Available on Order*, or *Procured on Demand*.
   * **Featured:** Select *Yes* to showcase the item on the homepage.
   * **Product Image:** Upload JPG, PNG, or WEBP photo.
3. Note: The website does not display fixed retail prices; visitors are provided with a direct **"Request a Quote"** button.

---

## 5. Managing the Project Portfolio

1. Navigate to **Projects Portfolio**.
2. Click **"+ Add New Project"**:
   * **Title:** e.g., *Commercial Office Complex Renovation & Facility Upgrade*
   * **Location:** e.g., *Ibadan, Oyo State*
   * **Client Organization:** Enter the client name, or use `[ADD CLIENT NAME]` until approved for public release.
   * **Scope of Work:** Enter deliverables separated with `||` (e.g., `Civil masonry||Structural reinforcement||Electrical upgrade`).
   * **Execution Status:** *Upcoming*, *Ongoing*, or *Completed*.
   * **Featured Project Image:** Upload high-resolution photo.

---

## 6. Updating Corporate Site Settings

Navigate to **Site Settings** in the sidebar. The settings are organized into tabs:

* **General & Legal:** Update Company Legal Name, Motto, Tagline, CAC Number placeholder, RC Number placeholder, and Logo paths.
* **Headquarters & Contact:** Update physical office address in Ibadan, primary & secondary phone hotlines, corporate emails, and business hours.
* **WhatsApp Widget:** Enable or disable the floating button, set the WhatsApp business phone number, and customize the pre-filled customer message.
* **Social Networks:** Update links for LinkedIn, Facebook, X/Twitter, Instagram, and YouTube.
* **SMTP Email Dispatch:** Configure your mail server (Host, Port, Username, Password, TLS/SSL) to deliver automated RFQ notifications.
* **SEO & Analytics:** Add your Google Analytics 4 ID (`G-XXXXXXXXXX`), Google Tag Manager ID, and Search Console verification tag.

---

## 7. Editing Homepage & About Narrative

Navigate to **Homepage & About CMS**:
* **Hero Section:** Edit the primary headline, subtitle narrative, button text, and button destination URLs.
* **Section Toggles:** Turn individual homepage sections on or off with simple checkboxes.
* **About Content:** Edit the Company Overview, Mission Statement, Vision Statement, Core Values list, and Management Message.

---

## 8. Managing Administrative Users (Super Admin Only)

1. Navigate to **Admin Users**.
2. Click **"+ Create New Administrator"**:
   * Enter full name, username, corporate email, and assign an RBAC role.
   * Set a strong password (minimum 8 characters).
3. To reset a staff member's password, click **Edit** on their account and type a new password into the password field.

---

## 9. Security Best Practices

* **Passwords:** Use strong passwords combining letters, numbers, and symbols.
* **Staff Offboarding:** When an employee leaves, set their account status to *Inactive* or *Suspended* rather than sharing credentials.
* **Audit Trail:** Periodically inspect the **Audit Trail** to monitor administrative changes and logins.
