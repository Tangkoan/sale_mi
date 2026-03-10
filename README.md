# 🍜 SaleMi - Mee Ngav POS System

**SaleMi** is a specialized Point of Sale (POS) application designed specifically for managing a "Mee Ngav" (Cockle Noodle) shop. The system focuses on streamlining the ordering process and managing staff responsibilities through a secure backend.

## 🌟 Key Highlight: Role-Based Management
The core of this system is its **Role-Based Access Control (RBAC)**. Users login to different dashboards based on their assigned roles:
- **Admin/Owner:** Full access to sales reports, inventory management, and employee oversight.
- **Employee:** Access restricted to order processing, billing, and specific daily tasks based on their responsibility.

## 🛠️ Tech Stack
- **Backend:** [Laravel](https://laravel.com/)
- **Frontend:** Blade Templates & Tailwind CSS
- **Database:** MySQL
- **Authentication:** Laravel Breeze / Custom Auth (for RBAC)

## ✨ Features
- **Smart Login:** Redirects users to specific modules based on their role.
- **Order Management:** Quickly process orders for different types of "Mee Ngav" and beverages.
- **Employee Responsibility Tracking:** Ensures that employees only access the tools they need for their specific job.
- **Daily Sales Overview:** Simple yet effective tracking of the shop's performance.

## 📸 Screenshots
*(Tip: You can add a screenshot of your login page or dashboard here to make it look even better!)*

## 🚀 Installation

1. **Clone the repository:**
   ```bash
   git clone [https://github.com/Tangkoan/sale_mi.git](https://github.com/Tangkoan/sale_mi.git)

   

1. Floder Project
    Controllers/Admin សម្រាប់ដាក់ Controller របស់ Admin form

    views/components គឺសម្រាប់ដាក់ File ដែលប្រកាសប្រើរួម
        toast.blad.php
        text-input.blade.php

    view/partials សម្រាប់ដាក់ header/sidebar/dashboard.blade.php(content របស់dashbaord)

    view/admin/dashbaord.blade.php គឺជា master layout របស់ project ចំពោះ style គឺនៅក្នុងហ្នឹងច្រើនដើម្បីយកមកប្រើ
