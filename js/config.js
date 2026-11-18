/* RT FASHION HUB – site settings. Edit values here; no other file needs changing. */
window.RT_CONFIG = {
  brand: "RT FASHION HUB",
  tagline: "Style That Defines You.",
  email: "contact@rtfashionhub.com",
  phone: "+91 78619 76128",
  phoneHref: "+917861976128",
  location: "Vadodara, Gujarat, India",
  address: "Opp. Sumandeep College, Near Suzlon Company, Vadodara, Gujarat 391760, India",
  hours: "Monday to Saturday: 10:00 AM – 6:00 PM",

  /* Your repository. This project had no .git folder, so no remote URL could be read.
     Paste your real URL (e.g. "https://github.com/<user>/<repo>"); the footer button
     stays hidden until this is set. */
  githubUrl: "",

  /* Social links – leave "" to hide an icon. */
  social: { instagram: "", facebook: "", whatsapp: "https://wa.me/917861976128" },

  /* Pricing rules */
  currency: "₹",
  gstRate: 0.05,          // 5% GST shown separately at checkout
  shippingFee: 0,         // flat delivery charge in INR (edit when you decide)

  /* Prices in products-data.js are placeholders until you edit them.
     Set to false once real prices are entered to hide the site-wide notice. */
  showPlaceholderPriceNotice: true,

  /* Form services. Leave "" until you connect one – the site then says so honestly.
     Example: a Formspree endpoint "https://formspree.io/f/xxxxxx" */
  contactEndpoint: "",
  newsletterEndpoint: "",

  /* Payment gateway (Razorpay etc.) needs a backend + keys. Not configured. */
  onlinePaymentsEnabled: false
};
