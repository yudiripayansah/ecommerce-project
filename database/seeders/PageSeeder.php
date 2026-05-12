<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'title'            => 'About Us',
                'handle'           => 'about',
                'visibility'       => 'visible',
                'published_at'     => now()->subDays(10),
                'meta_title'       => 'About Us — Our Story',
                'meta_description' => 'Learn about our mission, our passion for footwear, and the team behind our brand.',
                'content'          => <<<HTML
<h2>Our Story</h2>
<p>Founded with a passion for premium footwear, we believe that the right pair of shoes can change how you move through the world. From performance running shoes to everyday lifestyle sneakers, our curated selection represents the finest in footwear craftsmanship and innovation.</p>

<h2>Our Mission</h2>
<p>We're on a mission to connect people with shoes that genuinely fit their lives — not just their feet. That means carrying a wide range of styles, sizes, and price points, and backing every purchase with expert guidance and outstanding service.</p>

<h2>Why Us?</h2>
<ul>
  <li><strong>Authenticity</strong> — Every product we sell is 100% authentic, sourced directly from authorized brand distributors.</li>
  <li><strong>Expert Curation</strong> — Our team tests and hand-selects every model in our catalog.</li>
  <li><strong>Inclusive Sizing</strong> — We stock wide fits, extended sizes, and specialty lasts so everyone can find their perfect match.</li>
  <li><strong>Sustainability</strong> — We're committed to reducing our footprint by prioritizing brands that use recycled materials and ethical manufacturing.</li>
</ul>

<h2>Our Team</h2>
<p>We're a team of sneaker enthusiasts, runners, and designers who eat, sleep, and breathe footwear. From our buyers who travel the globe scouting the latest releases to our customer service team who knows every product inside out — we're here to help you step right.</p>

<h2>Get in Touch</h2>
<p>Have a question, suggestion, or just want to talk shoes? <a href="/pages/contact">Reach out to us</a> — we'd love to hear from you.</p>
HTML,
            ],
            [
                'title'            => 'Contact Us',
                'handle'           => 'contact',
                'visibility'       => 'visible',
                'published_at'     => now()->subDays(10),
                'meta_title'       => 'Contact Us',
                'meta_description' => 'Get in touch with our team. We\'re here to help with orders, sizing advice, and anything else you need.',
                'content'          => <<<HTML
<h2>We'd Love to Hear From You</h2>
<p>Whether you have a question about an order, need help finding the right size, or just want to share your latest kicks — our team is ready to help.</p>

<h2>Customer Support</h2>
<ul>
  <li><strong>Email:</strong> <a href="mailto:support@ourstore.com">support@ourstore.com</a></li>
  <li><strong>Phone:</strong> +62 21 1234 5678</li>
  <li><strong>Hours:</strong> Monday – Friday, 9:00 AM – 6:00 PM (WIB)</li>
</ul>

<h2>Order Inquiries</h2>
<p>For questions about an existing order — shipping status, returns, or exchanges — please have your order number ready when you contact us. We typically respond within one business day.</p>

<h2>Visit Us</h2>
<p>Our flagship store is open to the public if you prefer to browse in person:</p>
<address>
  <strong>Our Store</strong><br>
  Jl. Sudirman No. 123<br>
  Jakarta Pusat, DKI Jakarta 10220<br>
  Indonesia
</address>
<p><strong>Store Hours:</strong> Monday – Saturday, 10:00 AM – 9:00 PM</p>

<h2>Follow Us</h2>
<p>Stay up to date with new arrivals, exclusive drops, and behind-the-scenes content:</p>
<ul>
  <li><a href="#">Instagram</a></li>
  <li><a href="#">TikTok</a></li>
  <li><a href="#">YouTube</a></li>
</ul>
HTML,
            ],
            [
                'title'            => 'Privacy Policy',
                'handle'           => 'privacy-policy',
                'visibility'       => 'visible',
                'published_at'     => now()->subDays(10),
                'meta_title'       => 'Privacy Policy',
                'meta_description' => 'How we collect, use, and protect your personal information.',
                'content'          => <<<HTML
<p><em>Last updated: May 2026</em></p>

<p>Your privacy is important to us. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website or make a purchase.</p>

<h2>1. Information We Collect</h2>
<p>We may collect the following types of personal information:</p>
<ul>
  <li><strong>Identity Data:</strong> Name, username, or similar identifiers.</li>
  <li><strong>Contact Data:</strong> Email address, billing address, delivery address, and phone number.</li>
  <li><strong>Transaction Data:</strong> Details about payments and products you have purchased.</li>
  <li><strong>Technical Data:</strong> IP address, browser type, device identifiers, and cookies.</li>
  <li><strong>Usage Data:</strong> Information about how you use our website and services.</li>
</ul>

<h2>2. How We Use Your Information</h2>
<p>We use the information we collect to:</p>
<ul>
  <li>Process and fulfil your orders, including shipping and payment.</li>
  <li>Send you transactional emails (order confirmation, tracking updates).</li>
  <li>Improve our website, products, and customer experience.</li>
  <li>Send you marketing communications if you have opted in.</li>
  <li>Comply with legal obligations.</li>
</ul>

<h2>3. Sharing Your Information</h2>
<p>We do not sell your personal data. We may share your information with trusted third parties who assist in operating our website and conducting our business, such as payment processors and shipping providers, subject to confidentiality agreements.</p>

<h2>4. Cookies</h2>
<p>We use cookies to enhance your browsing experience, remember your cart, and analyse site traffic. You can control cookies through your browser settings. Disabling cookies may affect certain features of the site.</p>

<h2>5. Data Retention</h2>
<p>We retain your personal data only as long as necessary to fulfil the purposes for which it was collected, including satisfying legal, accounting, or reporting requirements.</p>

<h2>6. Your Rights</h2>
<p>Depending on your location, you may have the right to:</p>
<ul>
  <li>Access the personal data we hold about you.</li>
  <li>Request correction of inaccurate data.</li>
  <li>Request deletion of your data.</li>
  <li>Object to or restrict our processing of your data.</li>
</ul>
<p>To exercise any of these rights, please <a href="/pages/contact">contact us</a>.</p>

<h2>7. Security</h2>
<p>We implement appropriate technical and organisational measures to protect your personal data against unauthorised access, alteration, disclosure, or destruction.</p>

<h2>8. Changes to This Policy</h2>
<p>We may update this Privacy Policy from time to time. We will notify you of any material changes by posting the new policy on this page with an updated date.</p>

<h2>9. Contact</h2>
<p>If you have any questions about this Privacy Policy, please <a href="/pages/contact">contact us</a>.</p>
HTML,
            ],
            [
                'title'            => 'Terms and Conditions',
                'handle'           => 'terms-and-conditions',
                'visibility'       => 'visible',
                'published_at'     => now()->subDays(10),
                'meta_title'       => 'Terms and Conditions',
                'meta_description' => 'Please read our terms and conditions carefully before using our website or placing an order.',
                'content'          => <<<HTML
<p><em>Last updated: May 2026</em></p>

<p>By accessing our website or placing an order, you agree to be bound by the following terms and conditions. Please read them carefully.</p>

<h2>1. General</h2>
<p>These terms govern your use of our website and the purchase of products from us. We reserve the right to update these terms at any time. Continued use of the site after changes constitutes acceptance of the revised terms.</p>

<h2>2. Products and Pricing</h2>
<ul>
  <li>All product descriptions and prices are subject to change without notice.</li>
  <li>Prices are displayed in Indonesian Rupiah (IDR) and include applicable taxes unless stated otherwise.</li>
  <li>We reserve the right to refuse or cancel orders if a product is listed at an incorrect price due to a typographical error.</li>
</ul>

<h2>3. Orders and Payment</h2>
<ul>
  <li>Placing an order constitutes an offer to purchase. We reserve the right to accept or decline any order.</li>
  <li>Payment must be received in full before your order is dispatched.</li>
  <li>We accept major credit/debit cards and bank transfers. All transactions are processed securely.</li>
</ul>

<h2>4. Shipping and Delivery</h2>
<ul>
  <li>Estimated delivery times are provided at checkout and are not guaranteed.</li>
  <li>Risk of loss and title for items pass to you upon delivery.</li>
  <li>We are not responsible for delays caused by customs, courier services, or circumstances beyond our control.</li>
</ul>

<h2>5. Returns and Exchanges</h2>
<ul>
  <li>You may return unused, unworn items in original packaging within 30 days of receipt.</li>
  <li>Items must be returned with the original receipt or proof of purchase.</li>
  <li>Sale items are final sale and cannot be returned unless defective.</li>
  <li>To initiate a return, please <a href="/pages/contact">contact our support team</a>.</li>
</ul>

<h2>6. Intellectual Property</h2>
<p>All content on this website — including images, text, logos, and design — is our intellectual property or that of our licensors. Reproduction without written permission is prohibited.</p>

<h2>7. Limitation of Liability</h2>
<p>To the fullest extent permitted by law, we are not liable for any indirect, incidental, or consequential damages arising from your use of the website or products purchased from us.</p>

<h2>8. Governing Law</h2>
<p>These terms are governed by the laws of the Republic of Indonesia. Any disputes shall be subject to the exclusive jurisdiction of the courts of Jakarta.</p>

<h2>9. Contact</h2>
<p>For questions about these terms, please <a href="/pages/contact">contact us</a>.</p>
HTML,
            ],
        ];

        foreach ($pages as $data) {
            Page::firstOrCreate(
                ['handle' => $data['handle']],
                $data
            );
        }
    }
}
