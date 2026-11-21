<?php

/**
 * Suggested wording for the website's pages, written as DRAFTS for Drezwel from how the shop and this system work.
 * They are used until the manager saves their own wording in Manager > Website pages.
 *
 * The text is Markdown (# headings, **bold**, - lists, [links](url)). These placeholders are filled in automatically:
 * {business} {phone} {email} {address} {hours} {website} {shop} {contact} {track} {delivery} {returns}
 *
 * The owner should read every page and adjust the terms (especially returns, exchanges and the privacy page) before relying on them.
 */
return [

    'about_text' => <<<'MD'
At **{business}** we are dedicated to giving fashion lovers quality clothing with style. We are dealers in fashion and fashion accessories based in Accra, Ghana.

From jeans, shirts and streetwear to jackets, hoodies and accessories, every piece is chosen to give you a look that stands out and a fit that feels right.

## How we serve you

- **Shop online or call us.** Order on this website any time, or call {phone}.
- **Delivered to your door.** We deliver by rider in Accra and send orders to other regions by station bus or parcel office. You can also pick up from us.
- **Pay when it arrives.** Pay on delivery in cash or Mobile Money, or pay online with Mobile Money when you order.
- **Wholesale.** Buying for your shop? Ask about our wholesale prices.

## Visit or contact us

{address}

Phone: {phone} · Email: {email}
MD,

    'delivery_text' => <<<'MD'
## How an order reaches you

1. **Place your order** on this website, or call us on {phone}.
2. **Choose how you want it** (regular delivery, express delivery or pick up), then your region and town. The delivery fee is shown in your order summary before you confirm.
3. **We confirm your order** by phone or SMS and prepare it.
4. **Delivery.** A rider brings it to your door and marks it delivered. We then send you a short **SMS to confirm**. If you did not receive your order, call {phone} straight away.
5. **Pay.** Regular delivery in Accra can be paid on delivery. Everything else is paid now with Mobile Money (see below).

## Delivery options

You choose one of three at checkout, whichever region you are in:

- **Regular delivery.** Delivered **48 to 72 hours from the time you place your order**. In Accra a rider brings it to your door. Other regions are sent by station bus or parcel office.
- **Express delivery.** Delivered the **same day in Accra** and **within 24 hours outside Accra**. Outside Accra, express orders go to the station, so **GH₵ 30** is added to the delivery fee to transport your goods there.
- **Pick up.** Collect your order from our shop. There is no delivery fee. Call {phone} first to confirm it is ready.

## Delivery areas and fees

We deliver across Ghana. Fees depend on your region and town and are shown at checkout, so there are no surprises.

- **Accra and nearby towns:** delivered to your door by our riders.
- **Other regions:** sent by station bus or parcel office. We tell you where to collect it, and the delivery fee covers the transport.
- **Pick up:** collect from us at no delivery cost.

**Regular delivery** takes **48 to 72 hours from the time you place your order**. **Express delivery** is the same day in Accra and 24 hours outside Accra. We will call you if anything changes.

## Ways to pay

- **Pay on delivery.** Available for **regular delivery within Accra** only. Pay the rider in **cash or Mobile Money** when your order arrives. Please check your items first.
- **Pay now with Mobile Money.** Required for **express delivery, pick up, and any order outside Accra**. Choose "Pay now with Mobile Money" at checkout (MTN, Telecel or AirtelTigo). You get an approval prompt on your phone: enter your MoMo PIN to pay. If the prompt does not appear, dial *170# (MTN) or *110# (Telecel and AirtelTigo), choose My Wallet, then My Approvals.

We never ask for your card details or MoMo PIN by phone, SMS or WhatsApp.

## Track your order

[Track your order]({track}) at any time with your order reference and phone number, or sign in with your phone number to see all your orders.

## Need help?

Call {phone} or [contact us]({contact}). We are happy to help.
MD,

    'policy_returns' => <<<'MD'
We want you to love what you buy. If something is not right, this is how we put it right.

## Check your order on delivery

Please open and check your order **in front of the rider**. If an item is wrong, damaged or not what you ordered, tell the rider and call us on {phone} **the same day** so we can fix it.

## Exchanges

You can exchange an item for a different size, colour or style when:

- you bring it back within **3 days** of receiving it,
- it is **unworn, unwashed and has its tags**, and
- you have your order reference or receipt.

Bring the item to us or call {phone} to arrange for a rider to collect it. We will work out the difference: if the new item costs more you pay the difference, and if it costs less we refund the difference to you. The new item is subject to availability.

## Refunds

- If we sent you the **wrong item**, or an item that was **damaged or faulty**, we will exchange it or refund you in full, including the delivery fee.
- Refunds are made by **Mobile Money or cash**, usually within 3 working days of us receiving the item.
- Because items are chosen for fit and style, we do not give cash refunds for a change of mind. We are always happy to exchange.

## What cannot be returned

- Items that have been worn, washed, altered or damaged after delivery
- Items without their tags
- Underwear, socks and other hygiene items
- Items marked "final sale" on the product page

## Selection or trial orders

If you ordered on a selection or trial basis, keep the items you want and hand the rest back to the rider at the time of delivery. You pay only for what you keep.

## Delivery fees on returns

If you exchange or return an item because you changed your mind, the original delivery fee is not refundable. If the return is because of our error, we cover it.

Questions? Call {phone} or [contact us]({contact}).
MD,

    'policy_terms' => <<<'MD'
By placing an order with **{business}** you agree to these terms. Please read them with our [delivery and payment]({delivery}) information and our [returns and exchanges policy]({returns}).

## 1. Orders

An order is accepted once we have confirmed it with you, usually by phone or SMS. We may decline or cancel an order, for example if an item is out of stock, the price was shown incorrectly, or we cannot reach you. If we cancel an order you have already paid for, you will be refunded in full.

## 2. Prices

Prices are in Ghana cedis (GH₵). The delivery fee for your region and town is shown at checkout and added to your total. We try to keep prices accurate, but if a mistake is found we will tell you before your order is processed.

## 3. Payment

You can pay on delivery in cash or Mobile Money, or pay online with Mobile Money when you order. Goods remain ours until they are paid for in full.

## 4. Delivery

We deliver to the address you give us. Delivery times are estimates, not guarantees. Please give a correct address and a phone number we can reach, because the rider will call you. If we cannot reach you or deliver after reasonable attempts, we may cancel the order.

## 5. Delivery confirmation

When your rider delivers your order they mark it as delivered and we send you an SMS. If you did not receive the order, call us on {phone} straight away.

## 6. Returns and exchanges

Our returns and exchanges policy forms part of these terms.

## 7. Product information

We show our products as accurately as we can. Colours may look slightly different on different screens. Sizes and fit follow the size shown on each product.

## 8. Your account and information

You must give correct details when ordering. We use your information as described in our privacy policy.

## 9. Liability

We are responsible for the goods we sell and deliver. We are not liable for delays or failures caused by things outside our control, such as bad weather, road conditions or network outages.

## 10. Changes

We may update these terms from time to time. The version on this page applies to orders placed after it is published.

## 11. Contact

{business}, {address}. Phone: {phone}. Email: {email}.
MD,

    'policy_privacy' => <<<'MD'
**{business}** respects your privacy. This page explains what information we collect, why, and how we look after it.

## What we collect

- **Your order details:** your name, phone number, delivery address and what you ordered.
- **Payment information:** whether an order was paid and how (cash or Mobile Money). When you pay online, the payment is handled by our payment provider, Speso. We never see or store your MoMo PIN.
- **Messages you send us** through the contact form, by phone or on WhatsApp.
- **Basic website usage:** such as the pages you visit, to help the website work and improve.

## How we use it

- to process, deliver and confirm your orders, including calling you and sending SMS updates,
- to answer your questions and handle exchanges or returns,
- to keep our business records, and
- to send you news or offers, only if you agreed to receive them. You can ask us to stop at any time.

## Who we share it with

We share only what is needed to serve you: our delivery riders (your name, phone number and address), our payment and SMS providers, and where the law requires it. **We do not sell your personal information.**

## How long we keep it

We keep order records for as long as we need them for our accounts and to help you with exchanges or complaints, and as the law requires.

## Keeping it safe

Our systems are protected with passwords and restricted access, and only our team can see customer records. No method of storing or sending information is completely secure, but we take care to protect yours.

## Your choices

You can ask us to show you the information we hold about you, correct it, or delete it. You can also ask us to stop sending you messages. Contact us on {phone} or {email}.

## Changes

We may update this policy from time to time. The latest version is always on this page.
MD,

    'faq_text' => <<<'MD'
### How can I place an order?
Browse the shop, choose your size and colour, add the items to your cart and go to checkout. Choose your region and town, enter your details and confirm. You can also order by phone on {phone}.

### What payment methods do you accept?
You can **pay on delivery** (cash or Mobile Money) for regular delivery within Accra. Express delivery, pick up and orders outside Accra are **paid now with Mobile Money** (MTN, Telecel or AirtelTigo) when you check out.

### How long does delivery take?
Regular delivery takes 48 to 72 hours from the time you place your order. Express delivery is the same day in Accra and 24 hours outside Accra. Other regions are sent by station bus or parcel office. We will call you if anything changes.

### How much is delivery?
The fee depends on your region and town and is shown at checkout before you confirm your order.

### Which areas do you deliver to?
All regions in Ghana. Accra and nearby towns get a rider to their door. Other regions receive their order by station bus or parcel office. You can also pick up from us.

### Can I track my order?
Yes. [Track your order]({track}) with your order reference and phone number, or sign in with your phone number to see all your orders.

### How will I know my order was delivered?
When your rider hands over your order they mark it delivered, and we send you an SMS. If you did not receive it, call {phone} straight away.

### Can I return or exchange an item?
Yes, you can exchange an unworn item with its tags within 3 days. Read our full returns and exchanges policy for details.

### Do you sell wholesale?
Yes. [Contact us]({contact}) with what you need and we will send you our wholesale prices.

### Is my personal information safe?
Yes. We only use your details to process and deliver your order, we never sell them, and we never ask for your MoMo PIN. Read our privacy policy for more.

### How do I contact you?
Call {phone}, email {email}, or use the [contact page]({contact}). We are at {address}.
MD,

    'shop_intro_text' => <<<'MD'
## The Best Online Fashion Store in Ghana

Welcome to **{business}**, your home for quality fashion in Ghana. We are dealers in fashion and fashion accessories, with **jeans, shirts, streetwear, jackets, hoodies, sneakers and accessories** for men and women. Shop online any time, get it **delivered to your door anywhere in Ghana**, and **pay on delivery** or with **Mobile Money**.

## Jeans and Denim for Every Style

Looking for the perfect pair? Explore our [jeans]({shop}?category=Jeans) in baggy, straight, slim-fit and cargo styles, in washes from classic blue to black and grey. Every pair is chosen for quality, comfort and a fit that looks great.

## Shirts, Tees and Streetwear

Build your look with our [shirts]({shop}?category=Shirts), round neck tees and streetwear pieces. Whether you want something clean for the office or something bold for the weekend, you will find it in our collection.

## Sneakers and Shoes

Step out in style with our [sneakers]({shop}?category=Sneakers) and [shoes]({shop}?category=Shoes). Comfortable, durable and made to be seen.

## Jackets and Hoodies

Stay sharp in any weather with our [jackets]({shop}?category=Jackets) and hoodies, from lightweight layers to warm statement pieces.

## Shop with Confidence

- **Delivery across Ghana:** a rider to your door in Accra, and station bus or parcel office to other regions. The fee for your town is shown before you pay.
- **Pay on delivery:** check your items first, then pay in cash or Mobile Money.
- **Easy exchanges:** wrong size? Exchange it within 3 days.
- **Wholesale available:** buying for your shop? [Ask about wholesale]({contact}).

Not sure what to pick? Call us on {phone} and we will help you choose.
MD,
];
