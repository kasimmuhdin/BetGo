# BetGo – Property Rental and Sales Platform

**Developed by:** Kasim Muhdin  
**Student ID:** CTC-951-26  
**Role:** Full-Stack Developer  
**Project Date:** October 2026
video description on videodescription file in the root folder

## Overview

**BetGo** is a web-based property rental and sales platform designed to connect property owners and agents with people looking for residential properties.

The platform primarily focuses on residential property rentals, including houses, apartments, villas, and similar types of properties. Although BetGo also supports property sales, its main objective is to make finding and renting residential properties easier, faster, and more accessible.

Property owners and agents can publish listings with photos, descriptions, pricing, and other relevant details. Renters and buyers can browse, search, and explore available properties before contacting the owner or agent directly.

BetGo aims to reduce the time and unnecessary expenses associated with traditional property searching by allowing users to view property details before arranging physical visits.

## Key Features

- **Property Listings:** Owners and agents can create, edit, and manage residential property listings for rent or sale.
- **Property Discovery:** Visitors can browse and search available properties without creating an account.
- **User Authentication:** Secure registration, login, and email verification.
- **Role-Based Access Control:** Separate permissions for guests, general users, owners, agents, and administrators.
- **Owner and Agent Verification:** Users must submit identification information and receive administrator approval before becoming property providers.
- **Property Approval:** Administrators review submitted listings before they become publicly available.
- **Media Management:** Owners and agents can upload, organize, and reuse property photos through a dedicated media manager.
- **Real-Time Messaging:** Users can send inquiries and communicate with property owners and agents without refreshing the page.
- **Saved Listings:** Authenticated users can save properties for future reference.
- **Subscription and Listing Plans:** Owners and agents can purchase plans to unlock listing privileges.
- **Multiple Payment Gateways:** Supports Chapa payments in Ethiopian Birr (ETB) and Stripe payments in US Dollars (USD).
- **Subscription Management:** Supports recurring payments, renewals, cancellations, and subscription management.
- **Admin Dashboard:** Administrators can manage users, verification requests, property listings, public pages, FAQs, and dashboard statistics.
- **Responsive Design:** Provides a user-friendly experience across desktop and mobile devices.

## Technology Stack

| Technology | Purpose |
|---|---|
| PHP | Backend programming language |
| Laravel | Backend framework and application architecture |
| MySQL | Database management |
| Blade | Server-side templating |
| Alpine.js | Frontend interactivity |
| Tailwind CSS | UI styling and responsive design |
| Laravel Breeze | Authentication |
| Laravel Reverb | Real-time messaging using WebSockets |
| Laravel Cashier | Stripe subscription and payment management |
| Stripe | International card payments |
| Chapa | Ethiopian payment integration |
| Chart.js | Dashboard statistics and data visualization |
| Vite | Frontend asset bundling |

## System Architecture

BetGo follows a server-rendered Laravel architecture.

- **Backend:** Laravel handles application logic, routing, validation, authentication, database operations, and payment processing.
- **Frontend:** Blade renders application views, while Alpine.js handles interactive UI components.
- **Real-Time Communication:** Laravel Reverb enables instant messaging through WebSockets.
- **Database:** MySQL stores user accounts, properties, conversations, messages, payments, subscriptions, and other application data.
- **Payment Processing:** Stripe and Chapa handle payment transactions through secure webhook integrations.

## Payment Integration

BetGo supports two payment providers to accommodate different users.

**Chapa**
- Supports payments in Ethiopian Birr (ETB).
- Integrates with local payment channels, including Telebirr and CBE Birr.
- Verifies transactions before granting listing privileges.

**Stripe**
- Supports payments in US Dollars (USD).
- Handles one-time payments and recurring subscriptions.
- Integrates with Laravel Cashier for subscription management.
- Processes payment confirmations and renewals through webhooks.

The payment system uses unique transaction references and idempotent webhook processing to prevent duplicate payment records and listing grants.

## Security and Reliability

- Password hashing and secure authentication.
- Role-based access control.
- Email verification.
- Administrator approval for owners, agents, and property listings.
- CSRF protection for application forms.
- Webhook signature verification for Stripe and Chapa.
- Payment verification before granting listing privileges.
- Idempotent payment processing to prevent duplicate transactions.
- Environment-based configuration for sensitive credentials.
- HTTPS support for production deployments.

## Main Application Modules

1. Authentication and User Management
2. Owner and Agent Verification
3. Property Listing Management
4. Property Media Management
5. Property Search and Discovery
6. Saved Properties
7. Real-Time Messaging
8. Plans and Subscription Management
9. Stripe and Chapa Payment Integration
10. Administrative Dashboard
11. Public Pages and FAQ Management

## Future Enhancements

- Dedicated Android and iOS applications.
- Automated national ID and TIN verification.
- Direct payments between property seekers and property owners or agents.

## Developer

**Kasim Muhdin**  
Full-Stack Developer | Software Engineering Student

**Project:** BetGo – Property Rental and Sales Platform  
**Student ID:** CTC-951-26
