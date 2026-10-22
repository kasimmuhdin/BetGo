<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\Page;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $about = <<<'MD'
BetGo is a property listing platform for Addis Ababa. We connect owners and agents with people who want to rent or buy a home, an office, land or a commercial space.

## Why we built it

Finding a place often means informal brokers, social media posts and word of mouth. That leads to outdated listings, unclear prices and fake adverts. BetGo adds two checks to fix this:

- **Verified owners and agents.** Everyone who posts is checked with their national ID before they can publish.
- **Reviewed listings.** Every listing is reviewed by our team before it goes live.

## What you can do on BetGo

- Search and filter listings by area, property type, price and bedrooms.
- Save the properties you like and come back to them later.
- Message the owner or agent directly and get a notification when they reply.
- If you own or manage property, publish listings with photos and an exact map location.

Searching, saving and messaging are free. Owners and agents pay to publish. See the [pricing page](/pricing) for the plans.

## Questions?

Visit our [contact page](/contact) or read the [frequently asked questions](/faq).
MD;

        $contact = <<<'MD'
Questions about your account, a listing or a payment? We are happy to help.

For payment problems, please include the **transaction reference** from your payment receipt, so we can find it quickly.

You may also find your answer in the [frequently asked questions](/faq).
MD;

        $privacy = <<<'MD'
This policy explains what information BetGo collects and how we use it.

## Information we collect

- **Account details:** your name, email address and phone number. If you sign in with Google, we also receive your Google profile name, email address and photo.
- **Verification details:** owners and agents give us a national ID number, and agents also give a TIN, so our team can verify them.
- **Listings and photos** that you publish.
- **Messages** you send to other users through BetGo.
- **Payment records:** the plan you bought, the amount and the payment reference. Payments are processed by Chapa and Stripe. We never see or store your card number or mobile money PIN.
- **Technical data:** cookies that keep you logged in and protect our forms.

## How we use it

We use your information to run BetGo: to create your account, verify owners and agents, review and publish listings, deliver messages and notifications, process payments and keep the site secure.

## Who can see it

- Other users can see the listings you publish and your name when you message them.
- Our administrators can see your account and verification details to review your request.
- Payment providers receive the details needed to process a payment.

We do not sell your personal information.

## Your choices

You can ask us to correct or delete your account and information at any time through our [contact page](/contact).

## Changes

We may update this policy. The "last updated" date at the top of this page shows when it last changed.
MD;

        $terms = <<<'MD'
By using BetGo you agree to these terms.

## Using BetGo

- Give accurate information when you register and keep your password private.
- You are responsible for everything that happens under your account.
- Do not use BetGo for fraud, harassment or anything illegal.

## Listings

- Owners and agents must only publish properties they own or are allowed to advertise, with accurate prices, photos and details.
- Every listing is reviewed before it is published. We may reject, unpublish or remove any listing that is inaccurate, misleading or breaks these terms.

## Accounts and verification

Owners and agents must be verified before they can publish. We may refuse, suspend or close accounts that give false information or misuse the service.

## Plans and payments

Plans and prices are shown on the [pricing page](/pricing) before you pay. A plan lets you publish the number of listings, for the period, shown on that plan.

## Agreements between users

BetGo helps renters, buyers, owners and agents find each other. Rental and sale agreements are made directly between those people, and BetGo is not a party to them. Please visit a property and check the details before you pay anyone.

## Changes

We may update these terms. If you keep using BetGo after a change, you accept the new terms.
MD;

        $pages = [
            ['slug' => 'about', 'title' => 'About us', 'meta_description' => 'BetGo connects verified owners and agents with people looking to rent or buy in Addis Ababa.', 'content' => $about],
            ['slug' => 'contact', 'title' => 'Contact us', 'meta_description' => 'Get in touch with the BetGo team about your account, a listing or a payment.', 'content' => $contact],
            ['slug' => 'privacy', 'title' => 'Privacy Policy', 'meta_description' => 'What information BetGo collects and how we use it.', 'content' => $privacy],
            ['slug' => 'terms', 'title' => 'Terms of Service', 'meta_description' => 'The terms for using BetGo as a renter, buyer, owner or agent.', 'content' => $terms],
        ];

        foreach ($pages as $page) {
            Page::firstOrCreate(['slug' => $page['slug']], $page);
        }

        if (Faq::count() === 0) {
            $faqs = [
                ['Is BetGo free to use?', 'Yes for renters and buyers: searching, saving and messaging cost nothing. Owners and agents pay to publish listings. See the [pricing page](/pricing) for plans.'],
                ['How do I know a listing is real?', 'Every listing is reviewed by our team before it is published, and owners and agents are verified with their national ID. We still recommend visiting the property in person before you pay anything.'],
                ['How do I contact an owner or agent?', 'Create a free account, then send them a message about the property. You will get a notification when they reply.'],
                ['How do I list my property?', 'Register, request owner or agent status with your national ID and wait for approval. Then choose a plan, add your photos and details, and submit the listing for review.'],
                ['How long does review take?', 'Our team reviews each request and listing in the order it arrives. You will get a notification as soon as a decision is made.'],
                ['Why was my listing rejected?', 'When we reject a listing we send you the reason. Fix the problem, then save and submit the listing again for review.'],
                ['How can I pay?', 'Plans are paid through Chapa (telebirr, CBE Birr, Dashen Bank and other supported channels) or by card through Stripe.'],
                ['I paid but my plan is not showing.', 'Payments are normally confirmed within seconds. If yours is not, contact us with your transaction reference and we will check it.'],
            ];

            foreach ($faqs as $i => [$question, $answer]) {
                Faq::create(['question' => $question, 'answer' => $answer, 'sort_order' => $i + 1, 'is_published' => true]);
            }
        }
    }
}
