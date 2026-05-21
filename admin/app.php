<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();
$adminUser  = htmlspecialchars($_SESSION['admin_username'] ?? 'Admin');
$csrfToken  = csrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Rosali Admin</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&family=Playfair+Display:wght@400;600&display=swap" rel="stylesheet"/>
<script src="https://unpkg.com/react@18.3.1/umd/react.development.js" integrity="sha384-hD6/rw4ppMLGNu3tX5cjIb+uRZ7UkRJ6BPkLpg4hAu/6onKUg4lLsHAs9EBPT82L" crossorigin="anonymous"></script>
<script src="https://unpkg.com/react-dom@18.3.1/umd/react-dom.development.js" integrity="sha384-u6aeetuaXnQ38mYT8rp6sbXaQe3NL9t+IBXmnYxwkUI2Hw4bsp2Wvmx4yRQF1uAm" crossorigin="anonymous"></script>
<script src="https://unpkg.com/@babel/standalone@7.29.0/babel.min.js" integrity="sha384-m08KidiNqLdpJqLq95G/LEi8Qvjl/xUYll3QILypMoQ65QorJ9Lvtp2RXYGBFj1y" crossorigin="anonymous"></script>
<!-- Admin uses fixed dark theme. shared.jsx is loaded only so its THEME_CSS block can be probed by the Colors tab. -->
<script>window.ROSALI = { theme:'rosa', lang:'en', images:{}, content:{}, layout:{}, colors:{}, pageVisibility:{}, splatEnabled:false };</script>
<script type="text/babel" src="../shared.jsx"></script>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;font-family:'DM Sans',sans-serif}
body{background:oklch(14% 0.018 250);color:oklch(92% 0.010 240);min-height:100vh}
::-webkit-scrollbar{width:5px}::-webkit-scrollbar-track{background:oklch(18% 0.018 250)}
::-webkit-scrollbar-thumb{background:oklch(42% 0.18 22);border-radius:3px}
a{color:inherit;text-decoration:none}
button{cursor:pointer;font-family:'DM Sans',sans-serif}
input,textarea,select{font-family:'DM Sans',sans-serif}
input[type=color]{padding:0;border:none;cursor:pointer;border-radius:4px;overflow:hidden}
</style>
</head>
<body>
<div id="root"></div>
<script type="text/babel">
const { useState, useEffect, useRef } = React;

const ADMIN_USER  = <?= json_encode($adminUser) ?>;
const CSRF_TOKEN  = <?= json_encode($csrfToken) ?>;

const T = {
  bg:'oklch(14% 0.018 250)', bg2:'oklch(18% 0.020 250)', bg3:'oklch(22% 0.022 250)',
  border:'oklch(30% 0.022 250)', fg:'oklch(92% 0.010 240)', muted:'oklch(58% 0.015 240)',
  accent:'oklch(62% 0.18 22)', accentDark:'oklch(42% 0.18 22)',
  green:'oklch(62% 0.16 148)', yellow:'oklch(78% 0.15 84)', red:'oklch(60% 0.20 25)',
};

/* ── API helpers ── */
async function apiGet(key) {
  try {
    const r = await fetch(`api/data.php?key=${encodeURIComponent(key)}`);
    const d = await r.json();
    return d.value || null;
  } catch { return null; }
}
async function apiSet(key, value) {
  try {
    await fetch('api/data.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN},
      body: JSON.stringify({key, value}),
    });
  } catch {}
}

/* ── Static data ── */
const DEFAULT_PAGES = [
  {id:'home',    label:'Home',           file:'../index.php',   order:1},
  {id:'rooms',   label:'Rooms',          file:'../rooms.php',   order:2},
  {id:'events',  label:'Events',         file:'../events.php',  order:3},
  {id:'cafe',    label:'Rosa De 5 Café', file:'../cafe.php',    order:4},
  {id:'gallery', label:'Gallery',        file:'../gallery.php', order:5},
  {id:'tourism', label:'Tourism',        file:'../tourism.php', order:6},
  {id:'contact', label:'Contact',        file:'../contact.php', order:7},
];

const MEDIA_CATEGORIES = [
  {id:'general',   label:'General'},
  {id:'rooms',     label:'Rooms'},
  {id:'gallery',   label:'Gallery'},
  {id:'events',    label:'Events'},
  {id:'cafe',      label:'Café'},
  {id:'room_tour', label:'Room Tour (3D)'},
];

/* Slot catalog — every named placeholder on the front-end. Slot keys are
   derived from the original RosaliImg label via slotKey(); admins pick the
   friendly label and the assignment is saved as assigned_to='slot:<key>'. */
const SLOT_DEFS = [
  // Home
  {label:'hero — hotel garden entrance / aerial view at golden hour', friendly:'Hero — Garden Entrance / Aerial',       page:'Home'},
  {label:'about — garden gazebo / tropical flowers',                  friendly:'About — Gazebo / Tropical Flowers',     page:'Home'},
  // Rooms
  {label:'rooms hero — garden villa exterior / bungalow cluster aerial', friendly:'Rooms — Hero / Villa Exterior',      page:'Rooms'},
  ...['wooden','oriental','vip','superior','standard'].flatMap(k => [
    {label:`${k} room — main interior / bedroom`, friendly:`Room: ${k} — Main / Bedroom`, page:'Rooms'},
    {label:`${k} room — bathroom`,                friendly:`Room: ${k} — Bathroom`,        page:'Rooms'},
    {label:`${k} room — terrace / outdoor area`,  friendly:`Room: ${k} — Terrace`,         page:'Rooms'},
    {label:`${k} — thumbnail`,                    friendly:`Room: ${k} — Thumbnail`,       page:'Rooms'},
  ]),
  // Events
  {label:'events hero — dream garden wedding / meeting setup aerial', friendly:'Events — Hero',                         page:'Events'},
  {label:'meeting — jasmine room setup / seminar arrangement',        friendly:'Events — Meeting Room Setup',           page:'Events'},
  {label:'wedding — dream garden ceremony / floral setup / night lighting', friendly:'Events — Wedding Ceremony',       page:'Events'},
  ...['jasmine meeting room','tulip meeting room','lavender meeting room','the dream garden','main restaurant hall',
      'ruang rapat jasmine','ruang rapat tulip','ruang rapat lavender','aula restoran utama']
     .map(h => ({label:`hall — ${h}`, friendly:`Hall — ${h}`, page:'Events'})),
  // Café
  {label:'cafe hero — Rosa De 5 interior / barista / specialty coffee setup', friendly:'Café — Hero',                    page:'Café'},
  {label:'cafe — interior ambiance / cozy seating area',              friendly:'Café — Interior',                       page:'Café'},
  {label:'cafe — barista making pour over coffee',                    friendly:'Café — Barista',                        page:'Café'},
  {label:'cafe — garden outdoor seating',                             friendly:'Café — Garden Seating',                 page:'Café'},
  {label:'cafe — Rosa De 5 interior / specialty coffee / slow bar',   friendly:'Café — Specialty Coffee',               page:'Café'},
  // Tourism
  ...['pasir putih beach','ijen crater','baluran national park','colonial heritage sites','taman nasional meru betiri','local markets & batik',
      'pantai pasir putih','kawah ijen','taman nasional baluran','warisan kolonial','tn meru betiri','pasar & batik lokal']
     .map(s => ({label:`tourism — ${s}`, friendly:`Tourism — ${s}`, page:'Tourism'})),
  // Contact
  {label:'map — Google Maps embed Jl. PB Sudirman 52 Situbondo', friendly:'Contact — Map',                              page:'Contact'},
  {label:'hotel — front exterior / signage at road',             friendly:'Contact — Front Exterior',                   page:'Contact'},
];

/* Mirror of shared.jsx slotKey() — must stay in sync. */
function adminSlotKey(label){
  return label.replace(/[^a-z0-9]/gi,'_').slice(0,50).toLowerCase();
}
const SLOTS = SLOT_DEFS.map(d => ({...d, key: adminSlotKey(d.label)}));
const SLOTS_BY_PAGE = SLOTS.reduce((acc, s) => {
  (acc[s.page] = acc[s.page] || []).push(s);
  return acc;
}, {});

const THEME_VARS = [
  {key:'--bg',        label:'Background',       group:'Backgrounds'},
  {key:'--bg2',       label:'Background Alt',   group:'Backgrounds'},
  {key:'--fg',        label:'Text',             group:'Text'},
  {key:'--fg-muted',  label:'Muted Text',       group:'Text'},
  {key:'--accent',    label:'Accent',           group:'Accents'},
  {key:'--accent-lt', label:'Accent Light',     group:'Accents'},
  {key:'--border',    label:'Border',           group:'Other'},
  {key:'--nav-bg',    label:'Nav / Footer BG',  group:'Other'},
  {key:'--nav-fg',    label:'Nav / Footer Text',group:'Other'},
];

const THEMES       = ['garden','boutique','javanese','rosa','coastal','batik'];
const THEME_LABELS = {garden:'🌿 Garden',boutique:'🏛 Boutique',javanese:'✨ Javanese',rosa:'🌹 Rosa',coastal:'🌊 Coastal',batik:'🔷 Batik'};

const CONTENT_FIELDS = [
  {id:'general', label:'General', fields:[
    {key:'hotel_name',    label:'Hotel Name',         hint:'Shown in nav & page title',                      dflt:'Rosali Hotel'},
    {key:'wa_number',     label:'WhatsApp Number',    hint:'e.g. 6287851515500 — no + or spaces',            dflt:'6287851515500'},
    {key:'phone',         label:'Phone',              hint:'Front-desk reception number',                    dflt:'+62 (0)338 676 323'},
    {key:'email',         label:'Email',                                                                     dflt:'rosalihotel@gmail.com'},
    {key:'address',       label:'Address',            multi:true,                                            dflt:'Jl. PB Sudirman 52\n68312 Situbondo, Jawa Timur'},
    {key:'instagram',     label:'Instagram Handle',   hint:'Without @',                                      dflt:'rosalihotel'},
    {key:'facebook_url',  label:'Facebook URL'},
    {key:'cafe_hours_en', label:'Café Hours (EN)',    hint:'e.g. Sun–Thu 09:00–23:00 · Fri–Sat 09:00–24:00', dflt:'Sun–Thu 09:00–23:00 · Fri–Sat 09:00–24:00'},
    {key:'cafe_hours_id', label:'Café Hours (ID)',                                                           dflt:'Min–Kam 09:00–23:00 · Jum–Sab 09:00–24:00'},
  ]},
  {id:'home', label:'Home', fields:[
    /* Hero */
    {key:'hero_sup_en',          label:'Hero Tagline (EN)',          hint:'Location line above title',       dflt:'Situbondo · East Java · Indonesia'},
    {key:'hero_sup_id',          label:'Hero Tagline (ID)',                                                  dflt:'Situbondo · Jawa Timur · Indonesia'},
    {key:'hero_title_en',        label:'Hero Title (EN)',            multi:true, hint:'Use \\n for line breaks', dflt:'Experience\nLush Garden\nRetreat'},
    {key:'hero_title_id',        label:'Hero Title (ID)',            multi:true,                             dflt:'Rasakan\nKedamaian\nTaman Tropis'},
    {key:'hero_sub_en',          label:'Hero Subtitle (EN)',         multi:true,                             dflt:'Modern traditional villas, exotic tropical gardens, and legendary Indonesian hospitality.'},
    {key:'hero_sub_id',          label:'Hero Subtitle (ID)',         multi:true,                             dflt:'Villa modern tradisional, taman tropis eksotis, dan keramahan Indonesia yang tulus.'},
    {key:'hero_cta_en',          label:'Hero CTA Button (EN)',                                               dflt:'Book via WhatsApp'},
    {key:'hero_cta_id',          label:'Hero CTA Button (ID)',                                               dflt:'Pesan via WhatsApp'},
    /* About */
    {key:'about_label_en',       label:'About Section Label (EN)',                                          dflt:'Our Story'},
    {key:'about_label_id',       label:'About Section Label (ID)',                                          dflt:'Tentang Kami'},
    {key:'about_title_en',       label:'About Title (EN)',                                                  dflt:'A Garden Oasis in the City Center'},
    {key:'about_title_id',       label:'About Title (ID)',                                                  dflt:'Oase Taman di Pusat Kota'},
    {key:'about_body_en',        label:'About Body (EN)',            multi:true,                             dflt:'Rosali Hotel is Situbondo\'s finest boutique hotel — wrapped in cascading tropical gardens, exotic flowers, and birdsong. In the heart of the city, yet a world away.'},
    {key:'about_body_id',        label:'About Body (ID)',            multi:true,                             dflt:'Rosali Hotel adalah hotel butik terbaik di Situbondo — dikelilingi taman tropis yang rimbun, bunga eksotis, dan kicauan burung. Di tengah kota, namun terasa seperti dunia lain.'},
    {key:'about_chips_en',       label:'About Amenity Chips (EN)',   hint:'Comma-separated',                dflt:'24-Hour Reception, Tropical Gardens, Garden Café, Free Parking, Free WiFi, Breakfast Included'},
    {key:'about_chips_id',       label:'About Amenity Chips (ID)',   hint:'Comma-separated',                dflt:'Resepsi 24 Jam, Taman Tropis, Garden Café, Parkir Gratis, WiFi Gratis, Sarapan Termasuk'},
    /* Rooms section on home page */
    {key:'home_rooms_label_en',  label:'Rooms Section Label (EN)',                                          dflt:'Rooms & Villas'},
    {key:'home_rooms_label_id',  label:'Rooms Section Label (ID)',                                          dflt:'Kamar & Villa'},
    {key:'home_rooms_title_en',  label:'Rooms Section Title (EN)',                                          dflt:'Where You\'ll Stay'},
    {key:'home_rooms_title_id',  label:'Rooms Section Title (ID)',                                          dflt:'Tempat Anda Menginap'},
    {key:'home_rooms_sub_en',    label:'Rooms Section Subtitle (EN)',                                       dflt:'5 unique room categories across lush garden clusters.'},
    {key:'home_rooms_sub_id',    label:'Rooms Section Subtitle (ID)',                                       dflt:'5 kategori kamar unik di kluster taman yang rimbun.'},
    {key:'home_rooms_cta_en',    label:'Rooms CTA Button (EN)',                                             dflt:'See All Rooms'},
    {key:'home_rooms_cta_id',    label:'Rooms CTA Button (ID)',                                             dflt:'Lihat Semua Kamar'},
    /* Café section on home page */
    {key:'home_cafe_label_en',   label:'Café Section Label (EN)',                                           dflt:'Rosa De 5 Café'},
    {key:'home_cafe_label_id',   label:'Café Section Label (ID)',                                           dflt:'Rosa De 5 Café'},
    {key:'home_cafe_title_en',   label:'Café Section Title (EN)',    multi:true,                            dflt:'Specialty Coffee\n& Garden Dining'},
    {key:'home_cafe_title_id',   label:'Café Section Title (ID)',    multi:true,                            dflt:'Kopi Specialty\n& Santap di Taman'},
    {key:'home_cafe_sub_en',     label:'Café Section Subtitle (EN)',                                        dflt:'A café within the hotel garden. Slow bar coffee, great food, open late.'},
    {key:'home_cafe_sub_id',     label:'Café Section Subtitle (ID)',                                        dflt:'Café di dalam taman hotel. Slow bar coffee, makanan lezat, buka hingga malam.'},
    {key:'home_cafe_cta_en',     label:'Café CTA Button (EN)',                                              dflt:'Visit the Café'},
    {key:'home_cafe_cta_id',     label:'Café CTA Button (ID)',                                              dflt:'Kunjungi Café'},
    /* Events section on home page */
    {key:'home_events_label_en', label:'Events Section Label (EN)',                                         dflt:'Events'},
    {key:'home_events_label_id', label:'Events Section Label (ID)',                                         dflt:'Acara'},
    {key:'home_events_title_en', label:'Events Section Title (EN)',  multi:true,                            dflt:'Host Your Event\nWith Us'},
    {key:'home_events_title_id', label:'Events Section Title (ID)',  multi:true,                            dflt:'Gelar Acara Anda\nBersama Kami'},
    {key:'home_events_sub_en',   label:'Events Section Subtitle (EN)',                                      dflt:'From corporate meetings to dream weddings — beautifully hosted.'},
    {key:'home_events_sub_id',   label:'Events Section Subtitle (ID)',                                      dflt:'Dari rapat bisnis hingga pernikahan impian — digelar dengan indah.'},
    {key:'home_events_cta_en',   label:'Events CTA Button (EN)',                                            dflt:'Learn More'},
    {key:'home_events_cta_id',   label:'Events CTA Button (ID)',                                            dflt:'Selengkapnya'},
    /* Tourism section on home page */
    {key:'home_tourism_label_en',label:'Tourism Section Label (EN)',                                        dflt:'Tourism'},
    {key:'home_tourism_label_id',label:'Tourism Section Label (ID)',                                        dflt:'Wisata'},
    {key:'home_tourism_title_en',label:'Tourism Section Title (EN)',                                        dflt:'Explore East Java'},
    {key:'home_tourism_title_id',label:'Tourism Section Title (ID)',                                        dflt:'Jelajahi Jawa Timur'},
    {key:'home_tourism_sub_en',  label:'Tourism Section Subtitle (EN)',                                     dflt:'15 min from Pasir Putih Beach · Near Ijen Crater · Baluran National Park'},
    {key:'home_tourism_sub_id',  label:'Tourism Section Subtitle (ID)',                                     dflt:'15 menit ke Pantai Pasir Putih · Dekat Kawah Ijen · Taman Nasional Baluran'},
    {key:'home_tourism_cta_en',  label:'Tourism CTA Button (EN)',                                           dflt:'See Attractions'},
    {key:'home_tourism_cta_id',  label:'Tourism CTA Button (ID)',                                           dflt:'Lihat Destinasi'},
    /* Contact strip */
    {key:'home_contact_cta_en',  label:'Contact CTA Button (EN)',                                           dflt:'Get in Touch'},
    {key:'home_contact_cta_id',  label:'Contact CTA Button (ID)',                                           dflt:'Hubungi Kami'},
  ]},
  {id:'rooms', label:'Rooms', fields:[
    /* Page hero */
    {key:'rooms_hero_sup_en',        label:'Page Tagline (EN)',                                             dflt:'Accommodation'},
    {key:'rooms_hero_sup_id',        label:'Page Tagline (ID)',                                             dflt:'Akomodasi'},
    {key:'rooms_hero_title_en',      label:'Page Title (EN)',            multi:true,                        dflt:'Rooms &\nVillas'},
    {key:'rooms_hero_title_id',      label:'Page Title (ID)',            multi:true,                        dflt:'Kamar &\nVilla'},
    {key:'rooms_hero_sub_en',        label:'Page Subtitle (EN)',                                            dflt:'Five unique categories across lush garden clusters. Contact us for availability & rates.'},
    {key:'rooms_hero_sub_id',        label:'Page Subtitle (ID)',                                            dflt:'Lima kategori unik di kluster taman yang rimbun. Hubungi kami untuk ketersediaan & harga.'},
    /* Room: The Wooden House */
    {key:'room_wooden_name_en',      label:'Wooden House — Name (EN)',                                     dflt:'The Wooden House'},
    {key:'room_wooden_name_id',      label:'Wooden House — Name (ID)',                                     dflt:'The Wooden House'},
    {key:'room_wooden_desc_en',      label:'Wooden House — Description (EN)', multi:true,                  dflt:'Our most iconic villa — crafted from natural teak wood, immersed deep in the garden. Features a private terrace with garden views, traditional Javanese architecture meets modern comfort.'},
    {key:'room_wooden_desc_id',      label:'Wooden House — Description (ID)', multi:true,                  dflt:'Villa paling ikonik kami — dibuat dari kayu jati alami, terbenam jauh di dalam taman. Teras pribadi dengan pemandangan taman, arsitektur Jawa tradisional bertemu kenyamanan modern.'},
    /* Room: The Orientals */
    {key:'room_oriental_name_en',    label:'Orientals — Name (EN)',                                        dflt:'The Orientals'},
    {key:'room_oriental_name_id',    label:'Orientals — Name (ID)',                                        dflt:'The Orientals'},
    {key:'room_oriental_desc_en',    label:'Orientals — Description (EN)',    multi:true,                  dflt:'East-meets-West interiors with antique furnishings, batik textiles, and private garden views. A curated experience blending Indonesian heritage with boutique comfort.'},
    {key:'room_oriental_desc_id',    label:'Orientals — Description (ID)',    multi:true,                  dflt:'Interior Timur-Barat dengan furnitur antik, tekstil batik, dan pemandangan taman pribadi. Pengalaman terkurasi memadukan warisan Indonesia dengan kenyamanan butik.'},
    /* Room: The VIPs */
    {key:'room_vip_name_en',         label:'VIPs — Name (EN)',                                             dflt:'The VIPs'},
    {key:'room_vip_name_id',         label:'VIPs — Name (ID)',                                             dflt:'The VIPs'},
    {key:'room_vip_desc_en',         label:'VIPs — Description (EN)',         multi:true,                  dflt:'Our most spacious accommodation — a full VIP suite with separate living room, dining area, and premium furnishings. Ideal for families or extended stays.'},
    {key:'room_vip_desc_id',         label:'VIPs — Description (ID)',         multi:true,                  dflt:'Akomodasi paling luas kami — suite VIP penuh dengan ruang tamu terpisah, area makan, dan furnitur premium. Ideal untuk keluarga atau menginap panjang.'},
    /* Room: The Superiors */
    {key:'room_superior_name_en',    label:'Superiors — Name (EN)',                                        dflt:'The Superiors'},
    {key:'room_superior_name_id',    label:'Superiors — Name (ID)',                                        dflt:'The Superiors'},
    {key:'room_superior_desc_en',    label:'Superiors — Description (EN)',    multi:true,                  dflt:'Comfortable superior rooms with modern amenities, garden-facing windows, and a private outdoor sitting area. Perfect balance of value and comfort.'},
    {key:'room_superior_desc_id',    label:'Superiors — Description (ID)',    multi:true,                  dflt:'Kamar superior nyaman dengan fasilitas modern, jendela menghadap taman, dan area duduk luar pribadi. Keseimbangan sempurna antara nilai dan kenyamanan.'},
    /* Room: Standard */
    {key:'room_standard_name_en',    label:'Standard — Name (EN)',                                         dflt:'Standard Rooms'},
    {key:'room_standard_name_id',    label:'Standard — Name (ID)',                                         dflt:'Kamar Standar'},
    {key:'room_standard_desc_en',    label:'Standard — Description (EN)',     multi:true,                  dflt:'Clean, cozy, and well-appointed standard rooms. Everything you need for a comfortable stay — perfect for transit travelers and business guests.'},
    {key:'room_standard_desc_id',    label:'Standard — Description (ID)',     multi:true,                  dflt:'Kamar standar yang bersih, nyaman, dan tertata baik. Semua yang Anda butuhkan untuk menginap nyaman — sempurna untuk tamu transit dan bisnis.'},
    /* Misc labels */
    {key:'rooms_includes_label_en',  label:'"Room Includes" Label (EN)',                                   dflt:'Room Includes'},
    {key:'rooms_includes_label_id',  label:'"Room Includes" Label (ID)',                                   dflt:'Fasilitas Kamar'},
    {key:'rooms_rate_title_en',      label:'Rate Box Title (EN)',                                          dflt:'Best Rate — Contact Us'},
    {key:'rooms_rate_title_id',      label:'Rate Box Title (ID)',                                          dflt:'Harga Terbaik — Hubungi Kami'},
    {key:'rooms_rate_body_en',       label:'Rate Box Body (EN)',              multi:true,                  dflt:'We offer personalized rates based on duration, season & group size. Chat with us for the best deal.'},
    {key:'rooms_rate_body_id',       label:'Rate Box Body (ID)',              multi:true,                  dflt:'Kami menawarkan harga personal berdasarkan durasi, musim & ukuran grup. Chat kami untuk penawaran terbaik.'},
    {key:'rooms_cta_btn_en',         label:'CTA Button (EN)',                                              dflt:'Ask About This Room'},
    {key:'rooms_cta_btn_id',         label:'CTA Button (ID)',                                              dflt:'Tanya Tentang Kamar Ini'},
  ]},
  {id:'events', label:'Events', fields:[
    /* Page hero */
    {key:'events_hero_sup_en',       label:'Page Tagline (EN)',                                             dflt:'Events & Venues'},
    {key:'events_hero_sup_id',       label:'Page Tagline (ID)',                                             dflt:'Acara & Venue'},
    {key:'events_hero_title_en',     label:'Page Title (EN)',            multi:true,                        dflt:'Host Your\nEvent Here'},
    {key:'events_hero_title_id',     label:'Page Title (ID)',            multi:true,                        dflt:'Gelar Acara\nAnda di Sini'},
    {key:'events_hero_sub_en',       label:'Page Subtitle (EN)',                                            dflt:'From corporate meetings to dream weddings — beautifully hosted with modern AV and full catering.'},
    {key:'events_hero_sub_id',       label:'Page Subtitle (ID)',                                            dflt:'Dari rapat bisnis hingga pernikahan impian — digelar dengan indah, AV modern, dan katering lengkap.'},
    /* Type tabs */
    {key:'events_tab_meeting_en',    label:'Meetings Tab Label (EN)',                                       dflt:'Meetings & Seminars'},
    {key:'events_tab_meeting_id',    label:'Meetings Tab Label (ID)',                                       dflt:'Rapat & Seminar'},
    {key:'events_tab_wedding_en',    label:'Weddings Tab Label (EN)',                                       dflt:'Weddings & Parties'},
    {key:'events_tab_wedding_id',    label:'Weddings Tab Label (ID)',                                       dflt:'Pernikahan & Pesta'},
    /* WhatsApp message templates */
    {key:'events_msg_meeting_en',    label:'WA Message — Meetings (EN)', multi:true,                       dflt:'Hello, I would like to inquire about meeting room packages at Rosali Hotel.'},
    {key:'events_msg_meeting_id',    label:'WA Message — Meetings (ID)', multi:true,                       dflt:'Halo, saya ingin menanyakan paket ruang rapat di Rosali Hotel.'},
    {key:'events_msg_wedding_en',    label:'WA Message — Weddings (EN)', multi:true,                       dflt:'Hello, I would like to inquire about wedding packages at Rosali Hotel.'},
    {key:'events_msg_wedding_id',    label:'WA Message — Weddings (ID)', multi:true,                       dflt:'Halo, saya ingin menanyakan paket pernikahan di Rosali Hotel.'},
    /* Hall 1 */
    {key:'hall_jasmine_name_en',     label:'Hall 1 Name (EN)',                                              dflt:'Jasmine Meeting Room'},
    {key:'hall_jasmine_name_id',     label:'Hall 1 Name (ID)',                                              dflt:'Ruang Rapat Jasmine'},
    {key:'hall_jasmine_cap_en',      label:'Hall 1 Capacity (EN)',                                         dflt:'20–40 pax'},
    {key:'hall_jasmine_cap_id',      label:'Hall 1 Capacity (ID)',                                         dflt:'20–40 orang'},
    /* Hall 2 */
    {key:'hall_tulip_name_en',       label:'Hall 2 Name (EN)',                                              dflt:'Tulip Meeting Room'},
    {key:'hall_tulip_name_id',       label:'Hall 2 Name (ID)',                                              dflt:'Ruang Rapat Tulip'},
    {key:'hall_tulip_cap_en',        label:'Hall 2 Capacity (EN)',                                         dflt:'30–60 pax'},
    {key:'hall_tulip_cap_id',        label:'Hall 2 Capacity (ID)',                                         dflt:'30–60 orang'},
    /* Hall 3 */
    {key:'hall_lavender_name_en',    label:'Hall 3 Name (EN)',                                              dflt:'Lavender Meeting Room'},
    {key:'hall_lavender_name_id',    label:'Hall 3 Name (ID)',                                              dflt:'Ruang Rapat Lavender'},
    {key:'hall_lavender_cap_en',     label:'Hall 3 Capacity (EN)',                                         dflt:'40–80 pax'},
    {key:'hall_lavender_cap_id',     label:'Hall 3 Capacity (ID)',                                         dflt:'40–80 orang'},
    /* Hall 4 */
    {key:'hall_garden_name_en',      label:'Hall 4 Name (EN)',                                              dflt:'The Dream Garden'},
    {key:'hall_garden_name_id',      label:'Hall 4 Name (ID)',                                              dflt:'The Dream Garden'},
    {key:'hall_garden_cap_en',       label:'Hall 4 Capacity (EN)',                                         dflt:'100–300 pax'},
    {key:'hall_garden_cap_id',       label:'Hall 4 Capacity (ID)',                                         dflt:'100–300 orang'},
    /* Hall 5 */
    {key:'hall_restaurant_name_en',  label:'Hall 5 Name (EN)',                                              dflt:'Main Restaurant Hall'},
    {key:'hall_restaurant_name_id',  label:'Hall 5 Name (ID)',                                              dflt:'Aula Restoran Utama'},
    {key:'hall_restaurant_cap_en',   label:'Hall 5 Capacity (EN)',                                         dflt:'50–150 pax'},
    {key:'hall_restaurant_cap_id',   label:'Hall 5 Capacity (ID)',                                         dflt:'50–150 orang'},
  ]},
  {id:'cafe', label:'Café', fields:[
    /* Page hero */
    {key:'cafe_hero_sup_en',         label:'Page Tagline (EN)',                                             dflt:'Within Rosali Hotel · Situbondo'},
    {key:'cafe_hero_sup_id',         label:'Page Tagline (ID)',                                             dflt:'Di Dalam Rosali Hotel · Situbondo'},
    {key:'cafe_hero_title_en',       label:'Page Title (EN)',            multi:true,                        dflt:'Rosa De 5 Café'},
    {key:'cafe_hero_title_id',       label:'Page Title (ID)',            multi:true,                        dflt:'Rosa De 5 Café'},
    {key:'cafe_hero_sub_en',         label:'Page Subtitle (EN)',                                            dflt:'Specialty coffee · Slow bar · Garden ambiance · Open late'},
    {key:'cafe_hero_sub_id',         label:'Page Subtitle (ID)',                                            dflt:'Kopi specialty · Slow bar · Suasana taman · Buka hingga malam'},
    {key:'cafe_page_cta_en',         label:'CTA Button (EN)',                                               dflt:'Reserve a Table'},
    {key:'cafe_page_cta_id',         label:'CTA Button (ID)',                                               dflt:'Reservasi Meja'},
    /* Menu category names */
    {key:'cafe_cat1_name_en',        label:'Category 1 Name (EN)',                                         dflt:'Specialty Coffee'},
    {key:'cafe_cat1_name_id',        label:'Category 1 Name (ID)',                                         dflt:'Kopi Specialty'},
    {key:'cafe_cat2_name_en',        label:'Category 2 Name (EN)',                                         dflt:'Food & Bites'},
    {key:'cafe_cat2_name_id',        label:'Category 2 Name (ID)',                                         dflt:'Makanan & Camilan'},
    {key:'cafe_cat3_name_en',        label:'Category 3 Name (EN)',                                         dflt:'Drinks & Juice'},
    {key:'cafe_cat3_name_id',        label:'Category 3 Name (ID)',                                         dflt:'Minuman & Jus'},
  ]},
  {id:'tourism', label:'Tourism', fields:[
    /* Page hero */
    {key:'tourism_hero_sup_en',      label:'Page Tagline (EN)',                                             dflt:'East Java Tourism'},
    {key:'tourism_hero_sup_id',      label:'Page Tagline (ID)',                                             dflt:'Wisata Jawa Timur'},
    {key:'tourism_hero_title_en',    label:'Page Title (EN)',            multi:true,                        dflt:'Explore\nEast Java'},
    {key:'tourism_hero_title_id',    label:'Page Title (ID)',            multi:true,                        dflt:'Jelajahi\nJawa Timur'},
    {key:'tourism_hero_sub_en',      label:'Page Subtitle (EN)',                                            dflt:'From Rosali Hotel, East Java\'s finest natural and cultural attractions are at your doorstep.'},
    {key:'tourism_hero_sub_id',      label:'Page Subtitle (ID)',                                            dflt:'Dari Rosali Hotel, destinasi alam dan budaya terbaik Jawa Timur ada di depan pintu Anda.'},
    {key:'tourism_page_cta_en',      label:'CTA Button (EN)',                                               dflt:'Plan a Trip'},
    {key:'tourism_page_cta_id',      label:'CTA Button (ID)',                                               dflt:'Rencanakan Perjalanan'},
    /* Spot 1 — Pasir Putih */
    {key:'spot_pasir_name_en',       label:'Spot 1 Name (EN)',                                              dflt:'Pasir Putih Beach'},
    {key:'spot_pasir_name_id',       label:'Spot 1 Name (ID)',                                              dflt:'Pantai Pasir Putih'},
    {key:'spot_pasir_tag_en',        label:'Spot 1 Tag (EN)',                                               dflt:'Beach'},
    {key:'spot_pasir_tag_id',        label:'Spot 1 Tag (ID)',                                               dflt:'Pantai'},
    {key:'spot_pasir_dist_en',       label:'Spot 1 Distance (EN)',                                          dflt:'~15 min drive'},
    {key:'spot_pasir_dist_id',       label:'Spot 1 Distance (ID)',                                          dflt:'~15 mnt berkendara'},
    {key:'spot_pasir_desc_en',       label:'Spot 1 Description (EN)',   multi:true,                         dflt:'The most popular beach near Situbondo. White sand, calm turquoise waters, and fresh seafood warungs. Great for sunrise walks.'},
    {key:'spot_pasir_desc_id',       label:'Spot 1 Description (ID)',   multi:true,                         dflt:'Pantai paling populer dekat Situbondo. Pasir putih, air tosca tenang, dan warung seafood segar. Cocok untuk jalan pagi hari.'},
    /* Spot 2 — Ijen */
    {key:'spot_ijen_name_en',        label:'Spot 2 Name (EN)',                                              dflt:'Ijen Crater'},
    {key:'spot_ijen_name_id',        label:'Spot 2 Name (ID)',                                              dflt:'Kawah Ijen'},
    {key:'spot_ijen_tag_en',         label:'Spot 2 Tag (EN)',                                               dflt:'Nature'},
    {key:'spot_ijen_tag_id',         label:'Spot 2 Tag (ID)',                                               dflt:'Alam'},
    {key:'spot_ijen_dist_en',        label:'Spot 2 Distance (EN)',                                          dflt:'~2.5 hr drive'},
    {key:'spot_ijen_dist_id',        label:'Spot 2 Distance (ID)',                                          dflt:'~2,5 jam berkendara'},
    {key:'spot_ijen_desc_en',        label:'Spot 2 Description (EN)',   multi:true,                         dflt:'World-famous for its electric-blue fire phenomenon visible only at night. A UNESCO-recognized sulphuric crater lake surrounded by lush jungle.'},
    {key:'spot_ijen_desc_id',        label:'Spot 2 Description (ID)',   multi:true,                         dflt:'Terkenal di dunia karena fenomena api biru listrik yang hanya terlihat di malam hari. Danau kawah belerang yang diakui UNESCO, dikelilingi hutan lebat.'},
    /* Spot 3 — Baluran */
    {key:'spot_baluran_name_en',     label:'Spot 3 Name (EN)',                                              dflt:'Baluran National Park'},
    {key:'spot_baluran_name_id',     label:'Spot 3 Name (ID)',                                              dflt:'Taman Nasional Baluran'},
    {key:'spot_baluran_tag_en',      label:'Spot 3 Tag (EN)',                                               dflt:'Wildlife'},
    {key:'spot_baluran_tag_id',      label:'Spot 3 Tag (ID)',                                               dflt:'Satwa'},
    {key:'spot_baluran_dist_en',     label:'Spot 3 Distance (EN)',                                          dflt:'~1 hr drive'},
    {key:'spot_baluran_dist_id',     label:'Spot 3 Distance (ID)',                                          dflt:'~1 jam berkendara'},
    {key:'spot_baluran_desc_en',     label:'Spot 3 Description (EN)',   multi:true,                         dflt:'Called Indonesia\'s Africa, Baluran features savanna grasslands, wildlife (deer, buffalo, peacocks), and pristine coastline all in one place.'},
    {key:'spot_baluran_desc_id',     label:'Spot 3 Description (ID)',   multi:true,                         dflt:'"Afrika-nya Indonesia", Baluran memiliki padang sabana, satwa liar (rusa, kerbau, merak), dan pantai perawan — semuanya dalam satu tempat.'},
    /* Spot 4 — Colonial */
    {key:'spot_colonial_name_en',    label:'Spot 4 Name (EN)',                                              dflt:'Colonial Heritage Sites'},
    {key:'spot_colonial_name_id',    label:'Spot 4 Name (ID)',                                              dflt:'Warisan Kolonial'},
    {key:'spot_colonial_tag_en',     label:'Spot 4 Tag (EN)',                                               dflt:'Culture'},
    {key:'spot_colonial_tag_id',     label:'Spot 4 Tag (ID)',                                               dflt:'Budaya'},
    {key:'spot_colonial_dist_en',    label:'Spot 4 Distance (EN)',                                          dflt:'10–30 min'},
    {key:'spot_colonial_dist_id',    label:'Spot 4 Distance (ID)',                                          dflt:'10–30 mnt'},
    {key:'spot_colonial_desc_en',    label:'Spot 4 Description (EN)',   multi:true,                         dflt:'Situbondo is home to historic Dutch colonial sugar mills, classic tram tracks, and heritage buildings — a unique tropical-European landscape.'},
    {key:'spot_colonial_desc_id',    label:'Spot 4 Description (ID)',   multi:true,                         dflt:'Situbondo menyimpan pabrik gula kolonial Belanda, jalur trem bersejarah, dan bangunan warisan — lanskap tropis-Eropa yang unik.'},
    /* Spot 5 — Meru Betiri */
    {key:'spot_meru_name_en',        label:'Spot 5 Name (EN)',                                              dflt:'Taman Nasional Meru Betiri'},
    {key:'spot_meru_name_id',        label:'Spot 5 Name (ID)',                                              dflt:'TN Meru Betiri'},
    {key:'spot_meru_tag_en',         label:'Spot 5 Tag (EN)',                                               dflt:'Nature'},
    {key:'spot_meru_tag_id',         label:'Spot 5 Tag (ID)',                                               dflt:'Alam'},
    {key:'spot_meru_dist_en',        label:'Spot 5 Distance (EN)',                                          dflt:'~3 hr drive'},
    {key:'spot_meru_dist_id',        label:'Spot 5 Distance (ID)',                                          dflt:'~3 jam berkendara'},
    {key:'spot_meru_desc_en',        label:'Spot 5 Description (EN)',   multi:true,                         dflt:'A remote rainforest national park home to rare green turtles, leopards, and pristine jungle beaches. One of Java\'s most biodiverse areas.'},
    {key:'spot_meru_desc_id',        label:'Spot 5 Description (ID)',   multi:true,                         dflt:'Taman nasional hutan hujan terpencil yang menjadi rumah bagi penyu hijau langka, macan tutul, dan pantai hutan perawan. Salah satu area paling beragam hayati di Jawa.'},
    /* Spot 6 — Markets */
    {key:'spot_market_name_en',      label:'Spot 6 Name (EN)',                                              dflt:'Local Markets & Batik'},
    {key:'spot_market_name_id',      label:'Spot 6 Name (ID)',                                              dflt:'Pasar & Batik Lokal'},
    {key:'spot_market_tag_en',       label:'Spot 6 Tag (EN)',                                               dflt:'Culture'},
    {key:'spot_market_tag_id',       label:'Spot 6 Tag (ID)',                                               dflt:'Budaya'},
    {key:'spot_market_dist_en',      label:'Spot 6 Distance (EN)',                                          dflt:'5 min walk'},
    {key:'spot_market_dist_id',      label:'Spot 6 Distance (ID)',                                          dflt:'5 mnt jalan kaki'},
    {key:'spot_market_desc_en',      label:'Spot 6 Description (EN)',   multi:true,                         dflt:'Situbondo has a vibrant traditional market scene and local batik workshops. Pick up unique hand-dyed fabrics and local delicacies.'},
    {key:'spot_market_desc_id',      label:'Spot 6 Description (ID)',   multi:true,                         dflt:'Situbondo memiliki pasar tradisional yang ramai dan workshop batik lokal. Temukan kain celup tangan unik dan oleh-oleh khas.'},
  ]},
  {id:'contact', label:'Contact', fields:[
    /* Page hero */
    {key:'contact_hero_sup_en',      label:'Page Tagline (EN)',                                             dflt:'Contact & Promo'},
    {key:'contact_hero_sup_id',      label:'Page Tagline (ID)',                                             dflt:'Kontak & Promo'},
    {key:'contact_hero_title_en',    label:'Page Title (EN)',            multi:true,                        dflt:'Find Us.\nBook Us.\nStay With Us.'},
    {key:'contact_hero_title_id',    label:'Page Title (ID)',            multi:true,                        dflt:'Temukan Kami.\nPesan.\nMenginaplah.'},
    /* Promo 1 */
    {key:'promo1_title_en',          label:'Promo 1 Title (EN)',                                            dflt:'Weekend Getaway Package'},
    {key:'promo1_title_id',          label:'Promo 1 Title (ID)',                                            dflt:'Paket Weekend Getaway'},
    {key:'promo1_badge_en',          label:'Promo 1 Badge (EN)',                                            dflt:'Special'},
    {key:'promo1_badge_id',          label:'Promo 1 Badge (ID)',                                            dflt:'Spesial'},
    {key:'promo1_desc_en',           label:'Promo 1 Description (EN)',   multi:true,                        dflt:'2 nights in Superior Room + breakfast for 2 + late checkout. Perfect for couples.'},
    {key:'promo1_desc_id',           label:'Promo 1 Description (ID)',   multi:true,                        dflt:'2 malam di Kamar Superior + sarapan untuk 2 + late checkout. Sempurna untuk pasangan.'},
    {key:'promo1_cta_en',            label:'Promo 1 CTA Button (EN)',                                       dflt:'Ask About This Promo'},
    {key:'promo1_cta_id',            label:'Promo 1 CTA Button (ID)',                                       dflt:'Tanya Promo Ini'},
    /* Promo 2 */
    {key:'promo2_title_en',          label:'Promo 2 Title (EN)',                                            dflt:'Meeting Package (Full Day)'},
    {key:'promo2_title_id',          label:'Promo 2 Title (ID)',                                            dflt:'Paket Meeting (Full Day)'},
    {key:'promo2_badge_en',          label:'Promo 2 Badge (EN)',                                            dflt:'Corporate'},
    {key:'promo2_badge_id',          label:'Promo 2 Badge (ID)',                                            dflt:'Korporat'},
    {key:'promo2_desc_en',           label:'Promo 2 Description (EN)',   multi:true,                        dflt:'Meeting room + coffee breaks + lunch + parking. Starting from 20 pax.'},
    {key:'promo2_desc_id',           label:'Promo 2 Description (ID)',   multi:true,                        dflt:'Ruang rapat + coffee break + makan siang + parkir. Mulai dari 20 orang.'},
    {key:'promo2_cta_en',            label:'Promo 2 CTA Button (EN)',                                       dflt:'Get Package Details'},
    {key:'promo2_cta_id',            label:'Promo 2 CTA Button (ID)',                                       dflt:'Dapatkan Detail Paket'},
    /* Promo 3 */
    {key:'promo3_title_en',          label:'Promo 3 Title (EN)',                                            dflt:'Wedding Package'},
    {key:'promo3_title_id',          label:'Promo 3 Title (ID)',                                            dflt:'Paket Pernikahan'},
    {key:'promo3_badge_en',          label:'Promo 3 Badge (EN)',                                            dflt:'New'},
    {key:'promo3_badge_id',          label:'Promo 3 Badge (ID)',                                            dflt:'Baru'},
    {key:'promo3_desc_en',           label:'Promo 3 Description (EN)',   multi:true,                        dflt:'Dream Garden or indoor hall + catering + decoration + accommodation for bride & groom.'},
    {key:'promo3_desc_id',           label:'Promo 3 Description (ID)',   multi:true,                        dflt:'Dream Garden atau aula indoor + katering + dekorasi + akomodasi untuk pengantin.'},
    {key:'promo3_cta_en',            label:'Promo 3 CTA Button (EN)',                                       dflt:'Plan Your Wedding'},
    {key:'promo3_cta_id',            label:'Promo 3 CTA Button (ID)',                                       dflt:'Rencanakan Pernikahan'},
  ]},
];

const LAYOUT_SECTIONS = [
  {key:'home_about_flip',  label:'Home — About Section',  options:['Image Right (default)','Image Left'], values:['normal','flip']},
  {key:'home_cafe_flip',   label:'Home — Café Section',   options:['Text Left (default)','Text Right'],   values:['normal','flip']},
  {key:'home_events_flip', label:'Home — Events Section', options:['Image Left (default)','Image Right'], values:['normal','flip']},
  {key:'home_rooms_cols',  label:'Home — Rooms Grid',     options:['3 Columns (default)','2 Columns'],    values:['3','2']},
];

const IS = {
  width:'100%', background:'oklch(12% 0.015 250)', border:'1px solid oklch(32% 0.022 250)',
  borderRadius:5, padding:'9px 12px', color:T.fg, fontSize:13, outline:'none',
};

/* ── Shared components ── */
function Btn({children, onClick, small, secondary, danger, disabled, type}) {
  const bg     = danger ? T.red : secondary ? 'transparent' : T.accent;
  const border = (secondary || danger) ? `1px solid ${danger ? T.red : T.border}` : 'none';
  const color  = secondary ? T.muted : 'white';
  return (
    <button onClick={onClick} disabled={disabled} type={type||'button'} style={{
      background:bg, border, color,
      padding: small ? '5px 10px' : '9px 16px',
      borderRadius:5, fontSize: small ? 11 : 13, fontWeight:500,
      whiteSpace:'nowrap', opacity: disabled ? .45 : 1, transition:'opacity .15s',
    }}
    onMouseEnter={e => !disabled && (e.currentTarget.style.opacity = '.8')}
    onMouseLeave={e => !disabled && (e.currentTarget.style.opacity = '1')}
    >{children}</button>
  );
}

function SaveBtn({onSave, saved}) {
  return (
    <button onClick={onSave} style={{
      background: saved ? T.green : T.accent, border:'none', color:'white',
      padding:'9px 20px', borderRadius:6, fontSize:13, fontWeight:600,
      transition:'background .3s', cursor:'pointer', flexShrink:0,
    }}>{saved ? '✓ Saved!' : 'Save'}</button>
  );
}

/* ── Sidebar ── */
function Sidebar({tab, setTab, pageCount}) {
  const items = [
    {id:'overview',  icon:'◈', label:'Overview'},
    {id:'analytics', icon:'⌁', label:'Analytics'},
    {id:'pages',     icon:'⊞', label:'Pages', badge: pageCount},
    {id:'media',    icon:'⬤', label:'Media'},
    {id:'colors',   icon:'◉', label:'Colors'},
    {id:'content',  icon:'≡', label:'Content'},
    {id:'seo',      icon:'⚲', label:'SEO'},
    {id:'layout',   icon:'⊟', label:'Layout'},
    {id:'settings', icon:'⚙', label:'Settings'},
  ];
  return (
    <aside style={{
      width:210, flexShrink:0, background:T.bg2, borderRight:`1px solid ${T.border}`,
      display:'flex', flexDirection:'column', minHeight:'100vh', position:'sticky', top:0,
    }}>
      <div style={{padding:'20px 18px 16px', borderBottom:`1px solid ${T.border}`}}>
        <div style={{display:'flex', alignItems:'center', gap:10}}>
          <div style={{width:30, height:30, borderRadius:6, background:T.accentDark,
            display:'flex', alignItems:'center', justifyContent:'center',
            fontSize:13, color:'white', fontWeight:700}}>R</div>
          <div>
            <div style={{fontSize:13, fontWeight:600, color:T.fg}}>Rosali Hotel</div>
            <div style={{fontSize:10, color:T.muted}}>Admin Panel</div>
          </div>
        </div>
      </div>

      <nav style={{flex:1, padding:'10px 8px', display:'flex', flexDirection:'column', gap:1}}>
        {items.map(it => (
          <button key={it.id} onClick={() => setTab(it.id)} style={{
            display:'flex', alignItems:'center', gap:9, padding:'9px 11px',
            borderRadius:6, border:'none', cursor:'pointer',
            background: tab === it.id ? T.bg3 : 'transparent',
            color:      tab === it.id ? T.fg  : T.muted,
            textAlign:'left', fontSize:13, fontWeight: tab === it.id ? 500 : 400,
            transition:'all .15s', width:'100%',
          }}>
            <span style={{fontSize:13, opacity:.75}}>{it.icon}</span>
            <span style={{flex:1}}>{it.label}</span>
            {it.badge != null && (
              <span style={{background:T.border, borderRadius:10, padding:'1px 7px',
                fontSize:10, color:T.muted}}>{it.badge}</span>
            )}
          </button>
        ))}
      </nav>

      <div style={{padding:'14px 18px', borderTop:`1px solid ${T.border}`,
        display:'flex', flexDirection:'column', gap:6}}>
        <a href="../index.php" target="_blank" style={{fontSize:11, color:T.muted,
          display:'flex', alignItems:'center', gap:6, transition:'color .15s'}}
          onMouseEnter={e => e.currentTarget.style.color = T.fg}
          onMouseLeave={e => e.currentTarget.style.color = T.muted}
        >← View Website</a>
        <a href="logout.php" style={{fontSize:11, color:T.muted, transition:'color .15s'}}
          onMouseEnter={e => e.currentTarget.style.color = T.red}
          onMouseLeave={e => e.currentTarget.style.color = T.muted}
        >Sign Out</a>
      </div>
    </aside>
  );
}

/* ── Overview ── */
function TabOverview({pages, visibility}) {
  const [stats, setStats] = useState(null);
  useEffect(() => {
    fetch('api/overview.php').then(r => r.json()).then(setStats).catch(() => {});
  }, []);

  const visibleCount = pages.filter(p => visibility[p.id] !== false).length;
  const hiddenCount  = pages.length - visibleCount;

  const cards = [
    {label:'Total Pages',     value: pages.length,        color:T.accent},
    {label:'Visible in Nav',  value: visibleCount,        color:T.green},
    {label:'Hidden Pages',    value: hiddenCount,         color:T.muted},
    {label:'Media Files',     value: stats ? stats.gallery : '…', color:T.yellow},
    {label:'Rooms',           value: stats ? stats.rooms : '…',    color:T.accent},
    {label:'Unread Messages', value: stats ? stats.messages : '…', color:T.yellow},
    {label:'Events',          value: stats ? stats.events : '…',   color:T.muted},
  ];

  return (
    <div>
      <h2 style={{fontFamily:'Playfair Display', fontSize:24, marginBottom:6, color:T.fg}}>Overview</h2>
      <p style={{color:T.muted, fontSize:13, marginBottom:28}}>
        Welcome back, {ADMIN_USER}. Here's your website at a glance.
      </p>

      <div style={{display:'grid', gridTemplateColumns:'repeat(auto-fit,minmax(150px,1fr))',
        gap:12, marginBottom:32}}>
        {cards.map(s => (
          <div key={s.label} style={{background:T.bg2, border:`1px solid ${T.border}`,
            borderRadius:8, padding:'18px 20px'}}>
            <div style={{fontSize:28, fontWeight:600, color:s.color, marginBottom:4}}>{s.value}</div>
            <div style={{fontSize:11, color:T.muted}}>{s.label}</div>
          </div>
        ))}
      </div>

      <h3 style={{fontSize:13, fontWeight:600, color:T.fg, marginBottom:12,
        letterSpacing:'0.06em', textTransform:'uppercase'}}>Quick Links</h3>
      <div style={{display:'flex', flexWrap:'wrap', gap:8}}>
        {pages.map(p => (
          <a key={p.id} href={p.file} target="_blank"
            style={{padding:'8px 14px', border:`1px solid ${T.border}`,
              borderRadius:6, fontSize:12, color:T.muted, transition:'all .15s'}}
            onMouseEnter={e => { e.currentTarget.style.borderColor = T.accent; e.currentTarget.style.color = T.accent; }}
            onMouseLeave={e => { e.currentTarget.style.borderColor = T.border; e.currentTarget.style.color = T.muted; }}
          >{p.label} ↗</a>
        ))}
      </div>
    </div>
  );
}

/* ── Pages ── */
function TabPages({pages, visibility, setVisibility, setPages, savePages}) {
  const [drag, setDrag] = useState(null);

  const toggle = id => {
    const u = {...visibility, [id]: !visibility[id]};
    setVisibility(u);
    apiSet('page_visibility', JSON.stringify(u));
  };
  const dragOver = (e, id) => {
    e.preventDefault();
    if (drag === id) return;
    const from = pages.findIndex(p => p.id === drag);
    const to   = pages.findIndex(p => p.id === id);
    if (from === -1 || to === -1) return;
    const arr  = [...pages];
    arr.splice(to, 0, arr.splice(from, 1)[0]);
    setPages(arr); savePages(arr);
  };

  return (
    <div>
      <div style={{marginBottom:24}}>
        <h2 style={{fontFamily:'Playfair Display', fontSize:24, color:T.fg, marginBottom:4}}>Pages</h2>
        <p style={{color:T.muted, fontSize:13}}>Drag to reorder · toggle visibility to show/hide from navigation. Hidden pages still exist but disappear from the front-end menu.</p>
      </div>

      <div style={{display:'flex', flexDirection:'column', gap:3}}>
        {pages.map((p, i) => {
          const visible = visibility[p.id] !== false;
          return (
            <div key={p.id} draggable
              onDragStart={() => setDrag(p.id)}
              onDragOver={e => dragOver(e, p.id)}
              style={{background:T.bg2, border:`1px solid ${T.border}`, borderRadius:7,
                padding:'12px 15px', display:'flex', alignItems:'center', gap:11,
                cursor:'grab', transition:'background .15s'}}
              onMouseEnter={e => e.currentTarget.style.background = T.bg3}
              onMouseLeave={e => e.currentTarget.style.background = T.bg2}
            >
              <span style={{color:T.muted, opacity:.35, fontSize:13, userSelect:'none'}}>⠿</span>
              <span style={{fontSize:11, color:T.muted, width:16, textAlign:'center'}}>{i + 1}</span>
              <div style={{flex:1}}>
                <span style={{fontSize:13, fontWeight:500, color:T.fg}}>{p.label}</span>
                <span style={{fontSize:11, color:T.muted, marginLeft:8, opacity:.6}}>{p.file}</span>
              </div>
              <span style={{fontSize:10, padding:'2px 8px', borderRadius:10,
                background: visible ? 'oklch(62% 0.16 148 / 0.15)' : 'oklch(60% 0.20 25 / 0.12)',
                color: visible ? T.green : T.red}}>{visible ? 'Visible' : 'Hidden'}</span>
              <div style={{display:'flex', gap:5}}>
                <Btn onClick={() => toggle(p.id)} small secondary>{visible ? 'Hide' : 'Show'}</Btn>
                <a href={p.file} target="_blank"><Btn small secondary>View ↗</Btn></a>
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
}

/* ── Media ── */
function TabMedia({splatEnabled}) {
  const [items,     setItems]     = useState([]);
  const [filter,    setFilter]    = useState('all');
  const [typeFilter,setTypeFilter]= useState('all');
  const [uploading, setUploading] = useState(false);
  const [uploadCat, setUploadCat] = useState('general');
  const fileRef = useRef(null);

  const reload = () => {
    fetch('api/media.php').then(r => r.json()).then(d => setItems(d.items || [])).catch(() => {});
  };
  useEffect(reload, []);

  const accept = splatEnabled
    ? 'image/jpeg,image/png,image/webp,video/mp4,.splat,.ksplat,.ply'
    : 'image/jpeg,image/png,image/webp,video/mp4';

  const onUploadClick = () => fileRef.current?.click();

  const onFiles = async ev => {
    const files = Array.from(ev.target.files || []);
    if (!files.length) return;
    setUploading(true);
    for (const f of files) {
      const fd = new FormData();
      fd.append('file', f);
      fd.append('category', uploadCat);
      fd.append('csrf_token', CSRF_TOKEN);
      try {
        const r = await fetch('api/upload.php', {
          method:'POST',
          headers:{'X-CSRF-Token': CSRF_TOKEN},
          body: fd
        });
        const d = await r.json();
        if (!d.success) alert(`${f.name}: ${d.error || 'upload failed'}`);
      } catch { alert(`${f.name}: upload failed`); }
    }
    ev.target.value = '';
    setUploading(false);
    reload();
  };

  const togglePublish = async it => {
    await fetch('api/media.php', {
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-Token': CSRF_TOKEN},
      body: JSON.stringify({id: it.id, is_published: it.is_published ? 0 : 1}),
    });
    reload();
  };

  const changeCat = async (it, cat) => {
    await fetch('api/media.php', {
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-Token': CSRF_TOKEN},
      body: JSON.stringify({id: it.id, category: cat}),
    });
    reload();
  };

  const assignSlot = async (it, slotKey) => {
    /* Empty selection clears the assignment; otherwise unassign any other item
       already using this slot (one image per slot) before assigning. */
    if (slotKey && slotKey !== '') {
      const occupant = items.find(m => m.id !== it.id && m.assigned_to === ('slot:' + slotKey));
      if (occupant) {
        await fetch('api/media.php', {
          method:'POST',
          headers:{'Content-Type':'application/json','X-CSRF-Token': CSRF_TOKEN},
          body: JSON.stringify({id: occupant.id, assigned_to: ''}),
        });
      }
    }
    await fetch('api/media.php', {
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-Token': CSRF_TOKEN},
      body: JSON.stringify({id: it.id, assigned_to: slotKey ? ('slot:' + slotKey) : ''}),
    });
    reload();
  };

  const remove = async it => {
    if (!confirm('Delete this file? This removes it from disk and DB.')) return;
    await fetch('api/media.php', {
      method:'DELETE',
      headers:{'Content-Type':'application/json','X-CSRF-Token': CSRF_TOKEN},
      body: JSON.stringify({id: it.id}),
    });
    reload();
  };

  const filtered = items.filter(it => {
    if (filter !== 'all' && it.category !== filter) return false;
    if (typeFilter !== 'all' && it.file_type !== typeFilter) return false;
    return true;
  });

  const formatBytes = b => {
    if (b < 1024) return b + ' B';
    if (b < 1024*1024) return (b/1024).toFixed(1) + ' KB';
    return (b/1024/1024).toFixed(1) + ' MB';
  };

  return (
    <div>
      <div style={{display:'flex', justifyContent:'space-between', alignItems:'flex-start', marginBottom:24}}>
        <div>
          <h2 style={{fontFamily:'Playfair Display', fontSize:24, color:T.fg, marginBottom:4}}>Media Library</h2>
          <p style={{color:T.muted, fontSize:13}}>
            Images (10 MB JPEG/PNG/WebP) · Videos (200 MB MP4){splatEnabled ? ' · 3D Splats (500 MB .ply/.splat/.ksplat)' : ''}
          </p>
        </div>
        <div style={{display:'flex', gap:8, alignItems:'center'}}>
          <select value={uploadCat} onChange={e => setUploadCat(e.target.value)} style={{...IS, width:140}}>
            {MEDIA_CATEGORIES.filter(c => splatEnabled || c.id !== 'room_tour').map(c => (
              <option key={c.id} value={c.id}>{c.label}</option>
            ))}
          </select>
          <Btn onClick={onUploadClick} disabled={uploading}>{uploading ? 'Uploading…' : '+ Upload'}</Btn>
          <input ref={fileRef} type="file" multiple accept={accept} onChange={onFiles} style={{display:'none'}}/>
        </div>
      </div>

      {/* Filters */}
      <div style={{display:'flex', gap:6, flexWrap:'wrap', marginBottom:10}}>
        <button onClick={() => setFilter('all')} style={chipStyle(filter==='all')}>All</button>
        {MEDIA_CATEGORIES.filter(c => splatEnabled || c.id !== 'room_tour').map(c => (
          <button key={c.id} onClick={() => setFilter(c.id)} style={chipStyle(filter===c.id)}>{c.label}</button>
        ))}
      </div>
      <div style={{display:'flex', gap:6, flexWrap:'wrap', marginBottom:24}}>
        {[['all','All Types'],['image','📷 Images'],['video','🎬 Videos'],...(splatEnabled?[['splat','🧊 3D Splats']]:[])].map(([k,l]) => (
          <button key={k} onClick={() => setTypeFilter(k)} style={chipStyle(typeFilter===k, true)}>{l}</button>
        ))}
      </div>

      {filtered.length === 0
        ? <div style={{padding:'40px 20px', textAlign:'center', color:T.muted, fontSize:13,
            background:T.bg2, border:`1px dashed ${T.border}`, borderRadius:8}}>
            No media yet. Click <strong>+ Upload</strong> to add files.
          </div>
        : <div style={{display:'grid', gridTemplateColumns:'repeat(auto-fill,minmax(200px,1fr))', gap:14}}>
            {filtered.map(it => (
              <div key={it.id} style={{background:T.bg2, border:`1px solid ${T.border}`,
                borderRadius:8, overflow:'hidden', opacity: it.is_published ? 1 : .55}}>
                <div style={{height:140, position:'relative', background:T.bg3}}>
                  {it.file_type === 'image'
                    ? <img src={it.url} alt={it.original_name} style={{width:'100%',height:'100%',objectFit:'cover'}}/>
                    : it.file_type === 'video'
                      ? <div style={{width:'100%',height:'100%',display:'flex',alignItems:'center',justifyContent:'center',fontSize:32,color:T.muted,background:'#000'}}>🎬</div>
                      : <div style={{width:'100%',height:'100%',display:'flex',alignItems:'center',justifyContent:'center',fontSize:36,background:'oklch(28% 0.06 250)'}}>🧊</div>
                  }
                  {!it.is_published && <span style={{position:'absolute',top:6,left:6,background:'rgba(0,0,0,0.7)',color:'white',fontSize:9,padding:'2px 6px',borderRadius:3}}>HIDDEN</span>}
                </div>
                <div style={{padding:'10px 12px'}}>
                  <div title={it.original_name} style={{fontSize:11, color:T.fg, marginBottom:3, overflow:'hidden', textOverflow:'ellipsis', whiteSpace:'nowrap'}}>{it.original_name}</div>
                  <div style={{fontSize:10, color:T.muted, marginBottom:8}}>{formatBytes(it.file_size_bytes)} · {it.file_type}</div>

                  {it.file_type !== 'splat' && (
                    <>
                      <label style={{fontSize:9, color:T.muted, textTransform:'uppercase', letterSpacing:'0.1em', marginBottom:2, display:'block'}}>Use at</label>
                      <select value={(it.assigned_to||'').startsWith('slot:') ? it.assigned_to.slice(5) : ''}
                        onChange={e => assignSlot(it, e.target.value)}
                        style={{...IS, padding:'4px 6px', fontSize:10, marginBottom:6}}>
                        <option value="">— Not assigned —</option>
                        {Object.entries(SLOTS_BY_PAGE).map(([pg, list]) => (
                          <optgroup key={pg} label={pg}>
                            {list.map(s => <option key={s.key} value={s.key}>{s.friendly}</option>)}
                          </optgroup>
                        ))}
                      </select>
                    </>
                  )}

                  <label style={{fontSize:9, color:T.muted, textTransform:'uppercase', letterSpacing:'0.1em', marginBottom:2, display:'block'}}>Category</label>
                  <select value={it.category} onChange={e => changeCat(it, e.target.value)}
                    style={{...IS, padding:'4px 6px', fontSize:10, marginBottom:6}}>
                    {MEDIA_CATEGORIES.map(c => <option key={c.id} value={c.id}>{c.label}</option>)}
                  </select>
                  <div style={{display:'flex', gap:5}}>
                    <Btn onClick={() => togglePublish(it)} small secondary>{it.is_published ? 'Hide' : 'Show'}</Btn>
                    <Btn onClick={() => remove(it)} small danger>✕</Btn>
                  </div>
                </div>
              </div>
            ))}
          </div>
      }
    </div>
  );
}

function chipStyle(active, small) {
  return {
    padding: small ? '4px 11px' : '5px 13px', borderRadius:20, cursor:'pointer',
    border:`1px solid ${active ? T.accent : T.border}`,
    background: active ? 'oklch(62% 0.18 22 / 0.13)' : 'transparent',
    color: active ? T.accent : T.muted, fontSize: small ? 11 : 12, fontWeight:500,
    transition:'all .15s',
  };
}

/* ── Colors ── */
function cssVarToHex(themeId, varName) {
  try {
    const probe = document.createElement('div');
    probe.className = `theme-${themeId}`;
    probe.style.cssText = 'position:absolute;opacity:0;pointer-events:none;width:1px;height:1px';
    document.body.appendChild(probe);
    const raw = getComputedStyle(probe).getPropertyValue(varName).trim();
    document.body.removeChild(probe);
    if (!raw) return '#808080';
    const conv = document.createElement('div');
    conv.style.cssText = `position:absolute;opacity:0;pointer-events:none;background-color:${raw}`;
    document.body.appendChild(conv);
    const rgb = getComputedStyle(conv).backgroundColor;
    document.body.removeChild(conv);
    const m = rgb.match(/[\d.]+/g);
    if (!m || m.length < 3) return '#808080';
    return '#' + [0,1,2].map(i => Math.round(parseFloat(m[i])).toString(16).padStart(2,'0')).join('');
  } catch { return '#808080'; }
}

function TabColors({activeTheme, setActiveTheme}) {
  const [overrides,   setOverrides]   = useState({});
  const [saved,       setSaved]       = useState(false);
  const [loading,     setLoading]     = useState(true);

  useEffect(() => {
    apiGet('rosali_color_overrides').then(ov => {
      if (ov) try { setOverrides(JSON.parse(ov)); } catch {}
      setLoading(false);
    });
  }, []);

  const getVal = (themeId, varName) => {
    if (overrides[themeId]?.[varName]) return overrides[themeId][varName];
    return cssVarToHex(themeId, varName);
  };

  const setVal = (varName, hex) => {
    const updated = {...overrides, [activeTheme]: {...(overrides[activeTheme] || {}), [varName]: hex}};
    setOverrides(updated);
    /* Live preview within the admin (front-end picks up on next save+reload) */
    let el = document.getElementById('rosali-color-overrides');
    if (!el) { el = document.createElement('style'); el.id = 'rosali-color-overrides'; document.head.appendChild(el); }
    let css = '';
    Object.entries(updated).forEach(([th, vars]) => {
      const entries = Object.entries(vars || {}).filter(([, v]) => v);
      if (entries.length) css += `.theme-${th}{${entries.map(([k,v]) => `${k}:${v}`).join(';')}}`;
    });
    el.textContent = css;
  };

  const doSave = async () => {
    await Promise.all([
      apiSet('active_theme', activeTheme),
      apiSet('rosali_color_overrides', JSON.stringify(overrides)),
    ]);
    setSaved(true); setTimeout(() => setSaved(false), 2000);
  };

  const resetTheme = () => {
    if (!confirm(`Reset all color overrides for ${THEME_LABELS[activeTheme]}?`)) return;
    const updated = {...overrides, [activeTheme]: {}};
    setOverrides(updated);
    apiSet('rosali_color_overrides', JSON.stringify(updated));
    const el = document.getElementById('rosali-color-overrides');
    if (el) {
      let css = '';
      Object.entries(updated).forEach(([th, vars]) => {
        const e = Object.entries(vars || {}).filter(([, v]) => v);
        if (e.length) css += `.theme-${th}{${e.map(([k,v]) => `${k}:${v}`).join(';')}}`;
      });
      el.textContent = css;
    }
  };

  const groups = [...new Set(THEME_VARS.map(v => v.group))];

  return (
    <div>
      <h2 style={{fontFamily:'Playfair Display', fontSize:24, color:T.fg, marginBottom:4}}>Colors & Theme</h2>
      <p style={{color:T.muted, fontSize:13, marginBottom:22}}>
        Select the active site theme, then customize its color palette. Saving updates the live website.
      </p>

      <div style={{display:'flex', gap:6, flexWrap:'wrap', marginBottom:28}}>
        {THEMES.map(th => (
          <button key={th} onClick={() => setActiveTheme(th)} style={{
            padding:'7px 14px', borderRadius:20, cursor:'pointer',
            border:`1px solid ${activeTheme === th ? T.accent : T.border}`,
            background: activeTheme === th ? 'oklch(62% 0.18 22 / 0.13)' : 'transparent',
            color: activeTheme === th ? T.accent : T.muted, fontSize:12, fontWeight:500,
            transition:'all .15s',
          }}>{THEME_LABELS[th]}{activeTheme === th && ' ●'}</button>
        ))}
      </div>

      {loading
        ? <div style={{color:T.muted, fontSize:13}}>Loading…</div>
        : groups.map(grp => (
          <div key={grp} style={{marginBottom:24}}>
            <div style={{fontSize:10, textTransform:'uppercase', letterSpacing:'0.14em',
              color:T.muted, marginBottom:10}}>{grp}</div>
            <div style={{display:'flex', flexDirection:'column', gap:3}}>
              {THEME_VARS.filter(v => v.group === grp).map(v => {
                const hex         = getVal(activeTheme, v.key);
                const isOverridden = !!(overrides[activeTheme]?.[v.key]);
                return (
                  <div key={v.key} style={{display:'flex', alignItems:'center', gap:12,
                    background:T.bg2, border:`1px solid ${T.border}`, borderRadius:6, padding:'10px 14px'}}>
                    <input type="color" value={hex} onChange={e => setVal(v.key, e.target.value)}
                      style={{width:36, height:28, borderRadius:4, border:`1px solid ${T.border}`,
                        background:'none', cursor:'pointer'}}/>
                    <div style={{flex:1}}>
                      <span style={{fontSize:13, color:T.fg, fontWeight:500}}>{v.label}</span>
                      <span style={{fontSize:10, color:T.muted, marginLeft:8, fontFamily:'monospace'}}>{v.key}</span>
                    </div>
                    <span style={{fontSize:10, fontFamily:'monospace', color:T.muted}}>{hex}</span>
                    {isOverridden && (
                      <span style={{fontSize:9, padding:'2px 7px', borderRadius:8,
                        background:'oklch(62% 0.18 22 / 0.15)', color:T.accent}}>custom</span>
                    )}
                  </div>
                );
              })}
            </div>
          </div>
        ))
      }

      <div style={{display:'flex', gap:10, justifyContent:'space-between', alignItems:'center', marginTop:8}}>
        <Btn onClick={resetTheme} secondary>Reset {THEME_LABELS[activeTheme]}</Btn>
        <SaveBtn onSave={doSave} saved={saved}/>
      </div>
    </div>
  );
}

/* ── Content ── */
function TabContent() {
  const [activePage, setActivePage] = useState('general');
  const [vals,       setVals]       = useState({});
  const [saved,      setSaved]      = useState(false);
  const [loading,    setLoading]    = useState(true);

  useEffect(() => {
    const defaults = {};
    CONTENT_FIELDS.forEach(pg => pg.fields.forEach(f => {
      if (f.dflt !== undefined) defaults[f.key] = f.dflt;
    }));
    fetch('api/data.php?batch=rc', {credentials:'same-origin'})
      .then(r => r.json())
      .then(data => {
        const dbData = Object.fromEntries(
          Object.entries(data || {}).filter(([, v]) => v !== '')
        );
        setVals({...defaults, ...dbData});
        setLoading(false);
      })
      .catch(() => { setVals(defaults); setLoading(false); });
  }, []);

  const doSave = async () => {
    const pg = CONTENT_FIELDS.find(p => p.id === activePage);
    if (!pg) return;
    for (const f of pg.fields) await apiSet('rc_' + f.key, vals[f.key] || '');
    setSaved(true); setTimeout(() => setSaved(false), 2000);
  };

  const pg = CONTENT_FIELDS.find(p => p.id === activePage);

  return (
    <div>
      <div style={{display:'flex', justifyContent:'space-between', alignItems:'flex-start', marginBottom:22}}>
        <div>
          <h2 style={{fontFamily:'Playfair Display', fontSize:24, color:T.fg, marginBottom:4}}>Content Editor</h2>
          <p style={{color:T.muted, fontSize:13}}>Fields are pre-filled with the site's current default text. Edit any field and save to override it across all matching pages.</p>
        </div>
        <SaveBtn onSave={doSave} saved={saved}/>
      </div>

      <div style={{display:'flex', gap:4, marginBottom:24, borderBottom:`1px solid ${T.border}`, paddingBottom:0}}>
        {CONTENT_FIELDS.map(p => (
          <button key={p.id} onClick={() => setActivePage(p.id)} style={{
            padding:'8px 16px', border:'none',
            borderBottom:`2px solid ${activePage === p.id ? T.accent : 'transparent'}`,
            background:'transparent', color: activePage === p.id ? T.fg : T.muted,
            fontSize:13, fontWeight: activePage === p.id ? 600 : 400,
            cursor:'pointer', transition:'all .15s', marginBottom:-1,
          }}>{p.label}</button>
        ))}
      </div>

      {loading
        ? <div style={{color:T.muted, fontSize:13}}>Loading…</div>
        : <div style={{display:'flex', flexDirection:'column', gap:18}}>
            {pg && pg.fields.map(f => (
              <div key={f.key}>
                <label style={{display:'block', fontSize:12, fontWeight:500, color:T.fg, marginBottom:3}}>{f.label}</label>
                {f.hint && <div style={{fontSize:11, color:T.muted, marginBottom:5}}>{f.hint}</div>}
                {f.multi
                  ? <textarea value={vals[f.key] || ''} rows={3}
                      onChange={e => setVals(v => ({...v, [f.key]: e.target.value}))}
                      placeholder="Enter text…" style={{...IS, resize:'vertical'}}/>
                  : <input value={vals[f.key] || ''}
                      onChange={e => setVals(v => ({...v, [f.key]: e.target.value}))}
                      placeholder="Enter text…" style={IS}/>
                }
              </div>
            ))}
          </div>
      }
    </div>
  );
}

/* ── Layout ── */
function TabLayout() {
  const [prefs,   setPrefs]   = useState({});
  const [saved,   setSaved]   = useState(false);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    Promise.all(LAYOUT_SECTIONS.map(s => apiGet('layout_' + s.key).then(v => ({k:s.key, v})))).then(results => {
      const out = {};
      results.forEach(({k, v}) => { if (v) out[k] = v; });
      setPrefs(out);
      setLoading(false);
    });
  }, []);

  const doSave = async () => {
    for (const s of LAYOUT_SECTIONS) {
      await apiSet('layout_' + s.key, prefs[s.key] || s.values[0]);
    }
    setSaved(true); setTimeout(() => setSaved(false), 2000);
  };

  return (
    <div>
      <div style={{display:'flex', justifyContent:'space-between', alignItems:'flex-start', marginBottom:22}}>
        <div>
          <h2 style={{fontFamily:'Playfair Display', fontSize:24, color:T.fg, marginBottom:4}}>Layout</h2>
          <p style={{color:T.muted, fontSize:13}}>Choose how key sections are arranged on the home page.</p>
        </div>
        <SaveBtn onSave={doSave} saved={saved}/>
      </div>

      {loading
        ? <div style={{color:T.muted, fontSize:13}}>Loading…</div>
        : <div style={{display:'flex', flexDirection:'column', gap:3}}>
            {LAYOUT_SECTIONS.map(s => (
              <div key={s.key} style={{background:T.bg2, border:`1px solid ${T.border}`,
                borderRadius:7, padding:'14px 16px', display:'flex',
                alignItems:'center', gap:16, flexWrap:'wrap'}}>
                <div style={{flex:1, minWidth:140}}>
                  <div style={{fontSize:13, fontWeight:500, color:T.fg}}>{s.label}</div>
                </div>
                <div style={{display:'flex', gap:4}}>
                  {s.options.map((opt, i) => {
                    const active = (prefs[s.key] || s.values[0]) === s.values[i];
                    return (
                      <button key={i} onClick={() => setPrefs(p => ({...p, [s.key]: s.values[i]}))} style={{
                        padding:'6px 14px', cursor:'pointer', fontSize:12, transition:'all .15s',
                        border:`1px solid ${active ? T.accent : T.border}`,
                        borderRadius:5,
                        background: active ? 'oklch(62% 0.18 22 / 0.13)' : 'transparent',
                        color: active ? T.accent : T.muted,
                      }}>{opt}</button>
                    );
                  })}
                </div>
              </div>
            ))}
          </div>
      }
    </div>
  );
}

/* ── SEO ── */
const SEO_PAGE_ORDER = ['home','rooms','events','cafe','gallery','tourism','contact'];
const SEO_PAGE_LABELS = {
  home:'Home', rooms:'Rooms', events:'Events', cafe:'Café',
  gallery:'Gallery', tourism:'Tourism', contact:'Contact',
};

function SeoField({label, hint, value, placeholder, multi, maxLen, onChange}) {
  const len = (value || '').length;
  const over = maxLen && len > maxLen;
  return (
    <div style={{marginBottom:12}}>
      <div style={{display:'flex', justifyContent:'space-between', alignItems:'baseline', marginBottom:5}}>
        <label style={{fontSize:11, fontWeight:500, color:T.fg, textTransform:'uppercase', letterSpacing:'0.04em'}}>{label}</label>
        {maxLen != null && value !== '' && (
          <span style={{fontSize:10, color: over ? T.red : T.muted}}>{len}/{maxLen}</span>
        )}
      </div>
      {multi
        ? <textarea value={value || ''} onChange={e => onChange(e.target.value)} placeholder={placeholder || ''}
            rows={2} style={{...IS, resize:'vertical', minHeight:54, lineHeight:1.5}}/>
        : <input type="text" value={value || ''} onChange={e => onChange(e.target.value)}
            placeholder={placeholder || ''} style={IS}/>
      }
      {hint && <div style={{fontSize:10, color:T.muted, marginTop:4}}>{hint}</div>}
    </div>
  );
}

function SeoPagePanel({page, defaults, overrides, onChange}) {
  const eff = {
    title_en:       overrides.title_en       || defaults.title_en,
    title_id:       overrides.title_id       || defaults.title_id,
    description_en: overrides.description_en || defaults.description_en,
    description_id: overrides.description_id || defaults.description_id,
    og_image:       overrides.og_image       || defaults.og_slot_url || '',
  };
  return (
    <div style={{background:T.bg2, border:`1px solid ${T.border}`, borderRadius:8, padding:20, marginBottom:14}}>
      <div style={{display:'flex', justifyContent:'space-between', alignItems:'center', marginBottom:14}}>
        <h3 style={{fontSize:15, color:T.fg, fontWeight:600}}>{SEO_PAGE_LABELS[page]}</h3>
        <a href={'../' + (page === 'home' ? 'index.php' : page + '.php')} target="_blank"
          style={{fontSize:11, color:T.muted}}>Preview ↗</a>
      </div>

      <div style={{display:'grid', gridTemplateColumns:'1fr 1fr', gap:14}}>
        <div>
          <div style={{fontSize:10, color:T.muted, marginBottom:8, textTransform:'uppercase', letterSpacing:'0.06em'}}>English</div>
          <SeoField label="Title" placeholder={defaults.title_en} value={overrides.title_en}
            hint="Browser tab & Google result heading. ~60 chars." maxLen={60}
            onChange={v => onChange('title_en', v)}/>
          <SeoField label="Meta description" placeholder={defaults.description_en} value={overrides.description_en} multi
            hint="Snippet shown in Google. ~155 chars." maxLen={160}
            onChange={v => onChange('description_en', v)}/>
        </div>
        <div>
          <div style={{fontSize:10, color:T.muted, marginBottom:8, textTransform:'uppercase', letterSpacing:'0.06em'}}>Bahasa Indonesia</div>
          <SeoField label="Title" placeholder={defaults.title_id} value={overrides.title_id}
            maxLen={60} onChange={v => onChange('title_id', v)}/>
          <SeoField label="Meta description" placeholder={defaults.description_id} value={overrides.description_id} multi
            maxLen={160} onChange={v => onChange('description_id', v)}/>
        </div>
      </div>

      <SeoField label="Keywords (optional)" value={overrides.keywords}
        hint="Comma-separated. Note: Google ignores meta keywords; left here for other crawlers."
        onChange={v => onChange('keywords', v)}/>

      <SeoField label="Open Graph image URL" value={overrides.og_image}
        placeholder={defaults.og_slot_url || '(no default image — set one in Media)'}
        hint="Image shown when this page is shared on social media. Defaults to the page's hero slot if set."
        onChange={v => onChange('og_image', v)}/>

      {eff.og_image && (
        <div style={{marginTop:6, marginBottom:12}}>
          <img src={eff.og_image} alt="" style={{maxWidth:240, maxHeight:130, borderRadius:5, border:`1px solid ${T.border}`}}/>
        </div>
      )}

      <label style={{display:'flex', alignItems:'center', gap:8, fontSize:12, color:T.muted, marginTop:6, cursor:'pointer'}}>
        <input type="checkbox" checked={overrides.noindex} onChange={e => onChange('noindex', e.target.checked)}/>
        Hide this page from search engines (noindex, nofollow)
      </label>

      {/* Live Google-result preview */}
      <div style={{marginTop:18, padding:'12px 14px', background:T.bg3, borderRadius:6, fontFamily:'Arial, sans-serif'}}>
        <div style={{fontSize:10, color:T.muted, marginBottom:6, textTransform:'uppercase', letterSpacing:'0.04em'}}>Search result preview</div>
        <div style={{color:'#8ab4f8', fontSize:14, marginBottom:2, lineHeight:1.3}}>{eff.title_id}</div>
        <div style={{color:'#9aa0a6', fontSize:11, marginBottom:4}}>rosalihotel.id › {(page === 'home' ? '' : page)}</div>
        <div style={{color:T.fg, fontSize:12, lineHeight:1.4, opacity:.85}}>{eff.description_id}</div>
      </div>
    </div>
  );
}

function TabSEO() {
  const [data,    setData]    = useState(null);
  const [savedAt, setSavedAt] = useState(0);
  const debouncers = useRef({});

  useEffect(() => {
    fetch('api/seo.php').then(r => r.json()).then(setData).catch(() => {});
  }, []);

  const saveSetting = (key, value) => {
    if (debouncers.current[key]) clearTimeout(debouncers.current[key]);
    debouncers.current[key] = setTimeout(() => {
      apiSet(key, typeof value === 'boolean' ? (value ? '1' : '0') : value);
      setSavedAt(Date.now());
    }, 350);
  };

  const setSiteUrl = v => {
    setData(d => ({...d, site_url: v}));
    saveSetting('seo_site_url', v);
  };

  const updateField = (pageId, field, value) => {
    setData(d => {
      const next = {...d, pages: {...d.pages}};
      next.pages[pageId] = {...next.pages[pageId],
        overrides: {...next.pages[pageId].overrides, [field]: value}};
      return next;
    });
    saveSetting(`seo_${pageId}_${field}`, value);
  };

  if (!data) {
    return (
      <div>
        <h2 style={{fontFamily:'Playfair Display', fontSize:24, color:T.fg, marginBottom:4}}>SEO</h2>
        <p style={{color:T.muted, fontSize:13}}>Loading…</p>
      </div>
    );
  }

  return (
    <div>
      <div style={{display:'flex', justifyContent:'space-between', alignItems:'flex-end', marginBottom:24, flexWrap:'wrap', gap:12}}>
        <div>
          <h2 style={{fontFamily:'Playfair Display', fontSize:24, color:T.fg, marginBottom:4}}>SEO</h2>
          <p style={{color:T.muted, fontSize:13, maxWidth:620}}>
            Override page titles, meta descriptions, and social-share images. Empty fields fall back to sensible hardcoded defaults. Changes save automatically.
          </p>
        </div>
        {savedAt > 0 && (
          <div style={{fontSize:11, color:T.green}}>✓ Saved</div>
        )}
      </div>

      {/* Site-wide */}
      <div style={{background:T.bg2, border:`1px solid ${T.border}`, borderRadius:8, padding:20, marginBottom:18}}>
        <div style={{fontSize:13, color:T.fg, fontWeight:600, marginBottom:10, textTransform:'uppercase', letterSpacing:'0.04em'}}>Site-wide</div>
        <SeoField label="Canonical site URL" value={data.site_url}
          placeholder="https://www.rosalihotel.id"
          hint="Used for canonical links, Open Graph URLs, sitemap.xml, and JSON-LD schema. Leave blank to derive from the current request."
          onChange={setSiteUrl}/>
        <div style={{display:'flex', gap:14, fontSize:11, color:T.muted, marginTop:4}}>
          <a href="../sitemap.xml" target="_blank" style={{color:T.muted}}>View sitemap.xml ↗</a>
          <a href="../robots.txt"  target="_blank" style={{color:T.muted}}>View robots.txt ↗</a>
        </div>
      </div>

      {/* Per page */}
      {SEO_PAGE_ORDER.map(id => data.pages[id] && (
        <SeoPagePanel key={id} page={id}
          defaults={data.pages[id].defaults}
          overrides={data.pages[id].overrides}
          onChange={(field, value) => updateField(id, field, value)}/>
      ))}
    </div>
  );
}

/* ── Settings ── */
function TabSettings({splatEnabled, setSplatEnabled}) {
  const [confirmTxt, setConfirmTxt] = useState('');
  const [stats,      setStats]      = useState(null);
  const [busy,       setBusy]       = useState(false);
  const [geoLocal,   setGeoLocal]   = useState(false);
  const [noindex,    setNoindex]    = useState(false);

  const refreshStats = () => fetch('api/settings-stats.php').then(r => r.json()).then(setStats).catch(() => {});

  useEffect(() => {
    refreshStats();
    apiGet('geo_local_enabled').then(v => setGeoLocal(v === '1'));
    apiGet('seo_noindex_site').then(v => setNoindex(v === '1'));
  }, []);

  const clearAll = async () => {
    if (confirmTxt !== 'RESET') { alert('Type RESET to confirm'); return; }
    setBusy(true);
    const r = await fetch('api/settings-clear.php', {
      method:'POST',
      headers:{'X-CSRF-Token': CSRF_TOKEN},
    });
    const d = await r.json();
    setBusy(false);
    if (d.success) {
      alert('All admin content cleared. Front-end will show defaults on next load.');
    } else {
      alert('Reset failed: ' + (d.error || 'unknown error'));
    }
    setConfirmTxt('');
    refreshStats();
  };

  const toggleSplat = async () => {
    const next = !splatEnabled;
    setSplatEnabled(next);
    await apiSet('splat_enabled', next ? '1' : '0');
  };

  const toggleGeoLocal = async () => {
    const next = !geoLocal;
    setGeoLocal(next);
    await apiSet('geo_local_enabled', next ? '1' : '0');
  };

  const toggleNoindex = async () => {
    const next = !noindex;
    setNoindex(next);
    await apiSet('seo_noindex_site', next ? '1' : '0');
  };

  const fmtBytes = b => {
    if (!b) return '0 B';
    if (b < 1024) return b + ' B';
    if (b < 1024*1024) return (b/1024).toFixed(1) + ' KB';
    if (b < 1024*1024*1024) return (b/1024/1024).toFixed(1) + ' MB';
    return (b/1024/1024/1024).toFixed(2) + ' GB';
  };

  return (
    <div>
      <h2 style={{fontFamily:'Playfair Display', fontSize:24, color:T.fg, marginBottom:4}}>Settings</h2>
      <p style={{color:T.muted, fontSize:13, marginBottom:28}}>Site features, storage info, and data management.</p>

      {/* Splat toggle */}
      <div style={{background:T.bg2, border:`1px solid ${T.border}`, borderRadius:8, padding:20, marginBottom:16,
        display:'flex', justifyContent:'space-between', alignItems:'center', gap:16, flexWrap:'wrap'}}>
        <div>
          <div style={{fontSize:14, fontWeight:600, color:T.fg, marginBottom:4}}>🧊 Enable 3D Room Tours</div>
          <p style={{fontSize:12, color:T.muted, lineHeight:1.6, maxWidth:520}}>
            Adds support for Gaussian Splat uploads (.ply from Polycam/KIRI/Luma, plus compressed .splat / .ksplat). When off, splat files cannot be uploaded and the gsplat.js viewer is never loaded on any page (saves bandwidth).
          </p>
        </div>
        <button onClick={toggleSplat} style={{
          width:54, height:28, borderRadius:14, border:'none',
          background: splatEnabled ? T.accent : T.bg3,
          position:'relative', cursor:'pointer', transition:'background .2s'
        }}>
          <span style={{position:'absolute', top:3, left: splatEnabled ? 28 : 3,
            width:22, height:22, borderRadius:'50%', background:'white',
            transition:'left .2s'}}/>
        </button>
      </div>

      {/* Local geo toggle */}
      <div style={{background:T.bg2, border:`1px solid ${T.border}`, borderRadius:8, padding:20, marginBottom:16,
        display:'flex', justifyContent:'space-between', alignItems:'center', gap:16, flexWrap:'wrap'}}>
        <div>
          <div style={{fontSize:14, fontWeight:600, color:T.fg, marginBottom:4}}>🌐 Resolve geo for local visits</div>
          <p style={{fontSize:12, color:T.muted, lineHeight:1.6, maxWidth:520}}>
            Dev convenience. When ON, analytics treats local / private-network visits as if they came from the server itself — ip-api.com is asked to resolve the server's public IP and the resulting country/city is shown in the dashboard. Turn OFF in production.
          </p>
        </div>
        <button onClick={toggleGeoLocal} style={{
          width:54, height:28, borderRadius:14, border:'none',
          background: geoLocal ? T.accent : T.bg3,
          position:'relative', cursor:'pointer', transition:'background .2s'
        }}>
          <span style={{position:'absolute', top:3, left: geoLocal ? 28 : 3,
            width:22, height:22, borderRadius:'50%', background:'white',
            transition:'left .2s'}}/>
        </button>
      </div>

      {/* Site-wide noindex (staging) */}
      <div style={{
        background: noindex ? 'oklch(60% 0.20 25 / 0.07)' : T.bg2,
        border: `1px solid ${noindex ? 'oklch(60% 0.20 25 / 0.4)' : T.border}`,
        borderRadius:8, padding:20, marginBottom:16,
        display:'flex', justifyContent:'space-between', alignItems:'center', gap:16, flexWrap:'wrap'
      }}>
        <div>
          <div style={{fontSize:14, fontWeight:600, color: noindex ? T.red : T.fg, marginBottom:4}}>
            🚫 Hide entire site from Google {noindex && '— ACTIVE'}
          </div>
          <p style={{fontSize:12, color:T.muted, lineHeight:1.6, maxWidth:560}}>
            Staging / testing flag. When ON, every page emits <code style={{color:T.fg}}>noindex, nofollow</code> and the site's <code style={{color:T.fg}}>robots.txt</code> tells all crawlers to stay out. Use this on testing.rosalihotel.id so it never competes with the production site in Google. <strong style={{color: noindex ? T.red : T.fg}}>Turn OFF in production</strong> — otherwise Google will not index your real site.
          </p>
        </div>
        <button onClick={toggleNoindex} style={{
          width:54, height:28, borderRadius:14, border:'none',
          background: noindex ? T.red : T.bg3,
          position:'relative', cursor:'pointer', transition:'background .2s'
        }}>
          <span style={{position:'absolute', top:3, left: noindex ? 28 : 3,
            width:22, height:22, borderRadius:'50%', background:'white',
            transition:'left .2s'}}/>
        </button>
      </div>

      {/* Storage stats */}
      <div style={{background:T.bg2, border:`1px solid ${T.border}`, borderRadius:8, padding:20, marginBottom:16}}>
        <div style={{fontSize:14, fontWeight:600, color:T.fg, marginBottom:12}}>Storage</div>
        {stats
          ? <>
              <div style={{display:'grid', gridTemplateColumns:'repeat(3,1fr)', gap:10, marginBottom:12}}>
                <StatBox label="Media records" value={stats.media} unit="files in DB"/>
                <StatBox label="Content overrides" value={stats.content} unit="text fields"/>
                <StatBox label="Color overrides" value={stats.colors} unit="themes customized"/>
              </div>
              {stats.disk && (
                <div style={{display:'grid', gridTemplateColumns:'repeat(3,1fr)', gap:10}}>
                  <StatBox label="Images on disk" value={stats.disk.images.files} unit={fmtBytes(stats.disk.images.bytes)}/>
                  <StatBox label="Videos on disk" value={stats.disk.videos.files} unit={fmtBytes(stats.disk.videos.bytes)}/>
                  <StatBox label="Splats on disk" value={stats.disk.splats.files} unit={fmtBytes(stats.disk.splats.bytes)}/>
                </div>
              )}
            </>
          : <div style={{color:T.muted, fontSize:13}}>Loading…</div>
        }
      </div>

      <div style={{background:'oklch(60% 0.20 25 / 0.07)',
        border:'1px solid oklch(60% 0.20 25 / 0.3)', borderRadius:8, padding:20}}>
        <div style={{fontSize:14, fontWeight:600, color:T.red, marginBottom:8}}>⚠ Reset All Content & Media</div>
        <p style={{fontSize:12, color:T.muted, marginBottom:14, lineHeight:1.6}}>
          Removes every uploaded media file (disk + DB), all content overrides, layout preferences, page visibility, and color customizations. The active theme is preserved. Type <strong style={{color:T.fg}}>RESET</strong> to confirm.
        </p>
        <div style={{display:'flex', gap:10}}>
          <input value={confirmTxt} onChange={e => setConfirmTxt(e.target.value)}
            placeholder="Type RESET" style={{...IS, width:130}}/>
          <Btn onClick={clearAll} danger disabled={busy}>{busy ? 'Resetting…' : 'Reset All'}</Btn>
        </div>
      </div>
    </div>
  );
}

/* ── Analytics ── */
const COUNTRY_FLAGS = {
  'Indonesia':'🇮🇩','Singapore':'🇸🇬','Malaysia':'🇲🇾','Australia':'🇦🇺','United States':'🇺🇸',
  'United Kingdom':'🇬🇧','Japan':'🇯🇵','South Korea':'🇰🇷','China':'🇨🇳','Germany':'🇩🇪',
  'France':'🇫🇷','Netherlands':'🇳🇱','India':'🇮🇳','Thailand':'🇹🇭','Vietnam':'🇻🇳',
  'Philippines':'🇵🇭','Hong Kong':'🇭🇰','Taiwan':'🇹🇼','Italy':'🇮🇹','Spain':'🇪🇸',
  'Canada':'🇨🇦','Saudi Arabia':'🇸🇦','United Arab Emirates':'🇦🇪','Russia':'🇷🇺','Brazil':'🇧🇷',
};

function aFetch(qs) {
  return fetch('api/analytics.php?' + qs).then(r => r.json()).catch(() => null);
}

function Skeleton({h}) {
  return <div style={{height:h||14, background:T.bg3, borderRadius:4, opacity:.5}}/>;
}

function LineChart({data, height}) {
  if (!data || data.length === 0) return null;
  const W = 800, H = height || 200, pad = {l:38, r:12, t:12, b:24};
  const max = Math.max(1, ...data.map(d => d.visits));
  const stepX = (W - pad.l - pad.r) / Math.max(1, data.length - 1);
  const yFor = v => pad.t + (H - pad.t - pad.b) * (1 - v / max);
  const xFor = i => pad.l + i * stepX;
  const path = data.map((d, i) => `${i ? 'L' : 'M'}${xFor(i).toFixed(1)},${yFor(d.visits).toFixed(1)}`).join(' ');
  const area = `${path} L${xFor(data.length-1).toFixed(1)},${(H-pad.b).toFixed(1)} L${xFor(0).toFixed(1)},${(H-pad.b).toFixed(1)} Z`;
  const gridLines = [0, 0.25, 0.5, 0.75, 1].map(g => yFor(max * g));
  const labelEvery = Math.ceil(data.length / 8);
  const fmtDate = s => { const [, m, d] = s.split('-'); return `${parseInt(d)}/${parseInt(m)}`; };
  return (
    <svg viewBox={`0 0 ${W} ${H}`} style={{width:'100%', height:'auto', display:'block'}}>
      {gridLines.map((y, i) => (
        <line key={i} x1={pad.l} y1={y} x2={W-pad.r} y2={y}
          stroke="oklch(30% 0.022 250)" strokeWidth="1" strokeDasharray="2,3"/>
      ))}
      {[0, 0.5, 1].map((g, i) => (
        <text key={i} x={pad.l - 6} y={yFor(max * (1 - g)) + 4} fontSize="9"
          fill="oklch(58% 0.015 240)" textAnchor="end">
          {Math.round(max * (1 - g))}
        </text>
      ))}
      <path d={area} fill="oklch(62% 0.18 22 / 0.15)"/>
      <path d={path} fill="none" stroke="oklch(62% 0.18 22)" strokeWidth="2"/>
      {data.map((d, i) => (
        <circle key={i} cx={xFor(i)} cy={yFor(d.visits)} r="2.5" fill="oklch(62% 0.18 22)">
          <title>{d.date}: {d.visits} visits</title>
        </circle>
      ))}
      {data.map((d, i) => (i % labelEvery === 0 || i === data.length - 1) && (
        <text key={'l'+i} x={xFor(i)} y={H - 6} fontSize="9"
          fill="oklch(58% 0.015 240)" textAnchor="middle">{fmtDate(d.date)}</text>
      ))}
    </svg>
  );
}

function PctBar({pct, color}) {
  return (
    <div style={{background:T.bg3, borderRadius:3, height:5, overflow:'hidden', flex:1}}>
      <div style={{background:color || T.accent, width:`${Math.max(2, pct)}%`, height:'100%',
        transition:'width .25s'}}/>
    </div>
  );
}

function Card({title, children, action}) {
  return (
    <div style={{background:T.bg2, border:`1px solid ${T.border}`, borderRadius:8, padding:20}}>
      <div style={{display:'flex', justifyContent:'space-between', alignItems:'center', marginBottom:14, gap:10}}>
        <div style={{fontSize:13, fontWeight:600, color:T.fg, letterSpacing:'0.04em', textTransform:'uppercase'}}>{title}</div>
        {action}
      </div>
      {children}
    </div>
  );
}

/* Date range helpers — `range` is {preset: 7|30|90|'custom', from, to, label} */
function isoDate(d) {
  const y = d.getFullYear(), m = String(d.getMonth()+1).padStart(2,'0'), dd = String(d.getDate()).padStart(2,'0');
  return `${y}-${m}-${dd}`;
}
function presetRange(days) {
  const to   = new Date();
  const from = new Date(); from.setDate(from.getDate() - (days - 1));
  return {preset: days, from: isoDate(from), to: isoDate(to), label: `Last ${days} days`};
}
function rangeQS(range) {
  return `from=${range.from}&to=${range.to}`;
}
function fmtRangeLabel(range) {
  if (range.preset && range.preset !== 'custom') return `Last ${range.preset} days`;
  return `${range.from} → ${range.to}`;
}

function DateRangeSelector({range, setRange, presets, allowCustom}) {
  const [editing, setEditing] = useState(false);
  const [draftFrom, setDraftFrom] = useState(range.from);
  const [draftTo,   setDraftTo]   = useState(range.to);

  const pickPreset = (d) => {
    setRange(presetRange(d));
    setEditing(false);
  };
  const applyCustom = () => {
    if (!draftFrom || !draftTo) return;
    const from = draftFrom < draftTo ? draftFrom : draftTo;
    const to   = draftFrom < draftTo ? draftTo   : draftFrom;
    setRange({preset:'custom', from, to, label: `${from} → ${to}`});
    setEditing(false);
  };

  return (
    <div style={{position:'relative', display:'flex', gap:6, flexWrap:'wrap'}}>
      {presets.map(p => (
        <button key={p} onClick={() => pickPreset(p)} style={{
          padding:'7px 14px', borderRadius:5, fontSize:12, fontWeight:500, cursor:'pointer',
          background: range.preset === p ? T.accent : 'transparent',
          border: `1px solid ${range.preset === p ? T.accent : T.border}`,
          color: range.preset === p ? 'white' : T.muted,
        }}>{p} days</button>
      ))}
      {allowCustom && (
        <button onClick={() => { setDraftFrom(range.from); setDraftTo(range.to); setEditing(v => !v); }} style={{
          padding:'7px 14px', borderRadius:5, fontSize:12, fontWeight:500, cursor:'pointer',
          background: range.preset === 'custom' ? T.accent : 'transparent',
          border: `1px solid ${range.preset === 'custom' ? T.accent : T.border}`,
          color: range.preset === 'custom' ? 'white' : T.muted,
        }}>
          {range.preset === 'custom' ? fmtRangeLabel(range) : 'Custom ▾'}
        </button>
      )}
      {editing && (
        <div style={{
          position:'absolute', top:'calc(100% + 6px)', right:0, zIndex:10,
          background:T.bg2, border:`1px solid ${T.border}`, borderRadius:8, padding:14,
          boxShadow:'0 8px 24px rgba(0,0,0,.4)', display:'flex', flexDirection:'column', gap:10, minWidth:260,
        }}>
          <div style={{fontSize:11, color:T.muted, textTransform:'uppercase', letterSpacing:'0.05em'}}>Custom range</div>
          <label style={{fontSize:11, color:T.muted, display:'flex', flexDirection:'column', gap:4}}>
            From
            <input type="date" value={draftFrom} max={isoDate(new Date())} onChange={e => setDraftFrom(e.target.value)}
              style={{...IS, padding:'7px 10px', fontSize:12}}/>
          </label>
          <label style={{fontSize:11, color:T.muted, display:'flex', flexDirection:'column', gap:4}}>
            To
            <input type="date" value={draftTo} max={isoDate(new Date())} onChange={e => setDraftTo(e.target.value)}
              style={{...IS, padding:'7px 10px', fontSize:12}}/>
          </label>
          <div style={{display:'flex', gap:6, justifyContent:'flex-end', marginTop:4}}>
            <Btn small secondary onClick={() => setEditing(false)}>Cancel</Btn>
            <Btn small onClick={applyCustom}>Apply</Btn>
          </div>
          <div style={{fontSize:10, color:T.muted, opacity:.8}}>Data retained for the last 730 days (2 years).</div>
        </div>
      )}
    </div>
  );
}

function TabAnalytics() {
  const [range,    setRange]    = useState(presetRange(7));
  const [recRange, setRecRange] = useState(presetRange(7));
  const [overview, setOverview] = useState(null);
  const [daily,    setDaily]    = useState(null);
  const [pages,    setPages]    = useState(null);
  const [countries,setCountries]= useState(null);
  const [devices,  setDevices]  = useState(null);
  const [browsers, setBrowsers] = useState(null);
  const [refs,     setRefs]     = useState(null);
  const [recent,   setRecent]   = useState(null);

  const loadMain = (r) => {
    const qs = rangeQS(r);
    aFetch('type=overview').then(setOverview);
    aFetch(`type=daily&${qs}`).then(setDaily);
    aFetch(`type=pages&${qs}`).then(setPages);
    aFetch(`type=countries&${qs}`).then(setCountries);
    aFetch(`type=devices&${qs}`).then(setDevices);
    aFetch(`type=browsers&${qs}`).then(setBrowsers);
    aFetch(`type=referrers&${qs}`).then(setRefs);
  };
  const loadRecent = (r) => {
    aFetch(`type=recent&${rangeQS(r)}&limit=100`).then(setRecent);
  };

  useEffect(() => { loadMain(range); },   [range]);
  useEffect(() => { loadRecent(recRange); }, [recRange]);
  useEffect(() => {
    const t = setInterval(() => { loadMain(range); loadRecent(recRange); }, 5 * 60 * 1000);
    return () => clearInterval(t);
  }, [range, recRange]);

  const todayDelta = (() => {
    if (!overview) return null;
    const t = overview.today, y = overview.yesterday;
    if (!y) return t > 0 ? '+new' : null;
    const pct = Math.round(((t - y) / y) * 100);
    return (pct >= 0 ? '+' : '') + pct + '% vs yesterday';
  })();

  const summary = [
    {label:"Today's Visits",     value: overview ? overview.today    : null, sub: todayDelta, color: T.accent},
    {label:"This Week",          value: overview ? overview.week     : null, color: T.green},
    {label:"This Month",         value: overview ? overview.month    : null, color: T.yellow},
    {label:"All Time",           value: overview ? overview.all_time : null, color: T.fg},
  ];

  const totalPages    = pages    ? pages.reduce((a, p) => a + p.visits, 0) : 0;
  const totalDev      = devices  ? (devices.mobile + devices.desktop + devices.tablet) : 0;
  const totalBrowsers = browsers ? Object.values(browsers).reduce((a, b) => a + b, 0) : 0;

  const browserOrder = ['Chrome','Safari','Firefox','Edge','Opera','Other'];
  const noDataYet = overview && overview.all_time === 0;

  return (
    <div>
      <div style={{display:'flex', justifyContent:'space-between', alignItems:'flex-end', marginBottom:24, flexWrap:'wrap', gap:12}}>
        <div>
          <h2 style={{fontFamily:'Playfair Display', fontSize:24, color:T.fg, marginBottom:4}}>Analytics</h2>
          <p style={{color:T.muted, fontSize:13}}>Visitor traffic, devices, and referrers. Auto-refreshes every 5 minutes.</p>
        </div>
        <DateRangeSelector range={range} setRange={setRange} presets={[7,30,90]} allowCustom/>
      </div>

      {noDataYet && (
        <div style={{background:T.bg2, border:`1px dashed ${T.border}`, borderRadius:8, padding:'30px 20px',
          textAlign:'center', color:T.muted, fontSize:13, marginBottom:24}}>
          No visitor data yet. Visit the front-end pages and analytics will appear here.
        </div>
      )}

      {/* Summary cards */}
      <div style={{display:'grid', gridTemplateColumns:'repeat(auto-fit,minmax(180px,1fr))', gap:12, marginBottom:20}}>
        {summary.map(s => (
          <div key={s.label} style={{background:T.bg2, border:`1px solid ${T.border}`, borderRadius:8, padding:'18px 20px'}}>
            <div style={{fontSize:28, fontWeight:600, color:s.color, marginBottom:4}}>
              {s.value == null ? <Skeleton h={26}/> : s.value.toLocaleString()}
            </div>
            <div style={{fontSize:11, color:T.muted}}>{s.label}</div>
            {s.sub && <div style={{fontSize:10, color:T.muted, opacity:.8, marginTop:4}}>{s.sub}</div>}
          </div>
        ))}
      </div>

      {/* Hero info row */}
      {overview && (overview.top_page_today || overview.top_country_today) && (
        <div style={{display:'flex', flexWrap:'wrap', gap:14, marginBottom:24}}>
          {overview.top_page_today && (
            <div style={{background:T.bg2, border:`1px solid ${T.border}`, borderRadius:8, padding:'10px 16px', fontSize:12, color:T.muted}}>
              <span style={{color:T.muted}}>Most visited today: </span>
              <strong style={{color:T.fg, fontWeight:600}}>{overview.top_page_today}</strong>
            </div>
          )}
          {overview.top_country_today && (
            <div style={{background:T.bg2, border:`1px solid ${T.border}`, borderRadius:8, padding:'10px 16px', fontSize:12, color:T.muted}}>
              <span style={{color:T.muted}}>Top country today: </span>
              <strong style={{color:T.fg, fontWeight:600}}>
                {COUNTRY_FLAGS[overview.top_country_today] || '🌐'} {overview.top_country_today}
              </strong>
            </div>
          )}
        </div>
      )}

      {/* Daily line chart */}
      <div style={{marginBottom:20}}>
        <Card title={`Daily Visits — ${fmtRangeLabel(range)}`}>
          {daily ? <LineChart data={daily}/> : <div style={{height:200}}><Skeleton h={200}/></div>}
        </Card>
      </div>

      {/* Two-column grid */}
      <div style={{display:'grid', gridTemplateColumns:'1fr 1fr', gap:16, marginBottom:20}}>
        {/* Pages */}
        <Card title="Pages">
          {!pages
            ? <Skeleton h={120}/>
            : pages.length === 0
              ? <div style={{color:T.muted, fontSize:12}}>No data for this period.</div>
              : (
                <div style={{display:'flex', flexDirection:'column', gap:10}}>
                  {pages.map(p => {
                    const pct = totalPages ? (p.visits / totalPages * 100) : 0;
                    return (
                      <div key={p.page}>
                        <div style={{display:'flex', justifyContent:'space-between', fontSize:12, marginBottom:4}}>
                          <span style={{color:T.fg}}>{p.label}</span>
                          <span style={{color:T.muted}}>
                            {p.visits.toLocaleString()} · {pct.toFixed(0)}% · {p.unique.toLocaleString()} uniq
                          </span>
                        </div>
                        <PctBar pct={pct}/>
                      </div>
                    );
                  })}
                </div>
              )
          }
        </Card>

        {/* Countries + Devices + Browsers stacked */}
        <div style={{display:'flex', flexDirection:'column', gap:16}}>
          <Card title="Top Countries">
            {!countries
              ? <Skeleton h={80}/>
              : countries.length === 0
                ? <div style={{color:T.muted, fontSize:12}}>No data for this period.</div>
                : (
                  <div style={{display:'flex', flexDirection:'column', gap:6}}>
                    {countries.map(c => (
                      <div key={c.country} style={{display:'flex', justifyContent:'space-between', alignItems:'center', fontSize:12}}>
                        <span style={{color:T.fg}}>{COUNTRY_FLAGS[c.country] || '🌐'} {c.country}</span>
                        <span style={{color:T.muted}}>{c.visits.toLocaleString()}</span>
                      </div>
                    ))}
                  </div>
                )
            }
          </Card>

          <Card title="Devices">
            {!devices
              ? <Skeleton h={40}/>
              : (() => {
                const items = [
                  {key:'mobile',  label:'Mobile',  v: devices.mobile,  color: T.accent},
                  {key:'desktop', label:'Desktop', v: devices.desktop, color: T.green},
                  {key:'tablet',  label:'Tablet',  v: devices.tablet,  color: T.yellow},
                ];
                if (totalDev === 0) return <div style={{color:T.muted, fontSize:12}}>No data.</div>;
                return (
                  <div style={{display:'flex', flexDirection:'column', gap:8}}>
                    {items.map(it => {
                      const pct = totalDev ? (it.v / totalDev * 100) : 0;
                      return (
                        <div key={it.key}>
                          <div style={{display:'flex', justifyContent:'space-between', fontSize:12, marginBottom:4}}>
                            <span style={{color:T.fg}}>{it.label}</span>
                            <span style={{color:T.muted}}>{pct.toFixed(0)}%</span>
                          </div>
                          <PctBar pct={pct} color={it.color}/>
                        </div>
                      );
                    })}
                  </div>
                );
              })()
            }
          </Card>

          <Card title="Browsers">
            {!browsers
              ? <Skeleton h={60}/>
              : totalBrowsers === 0
                ? <div style={{color:T.muted, fontSize:12}}>No data.</div>
                : (
                  <div style={{display:'flex', flexDirection:'column', gap:5}}>
                    {browserOrder.filter(b => (browsers[b] || 0) > 0).map(b => {
                      const v = browsers[b] || 0;
                      const pct = totalBrowsers ? (v / totalBrowsers * 100) : 0;
                      return (
                        <div key={b} style={{display:'flex', justifyContent:'space-between', alignItems:'center', fontSize:12}}>
                          <span style={{color:T.fg}}>{b}</span>
                          <span style={{color:T.muted}}>{pct.toFixed(0)}% · {v.toLocaleString()}</span>
                        </div>
                      );
                    })}
                  </div>
                )
            }
          </Card>
        </div>
      </div>

      {/* Referrers */}
      <Card title="Top Referrers">
        {!refs
          ? <Skeleton h={80}/>
          : refs.length === 0
            ? <div style={{color:T.muted, fontSize:12}}>No data for this period.</div>
            : (
              <table style={{width:'100%', borderCollapse:'collapse', fontSize:12}}>
                <thead>
                  <tr style={{textAlign:'left', color:T.muted, fontWeight:500, borderBottom:`1px solid ${T.border}`}}>
                    <th style={{padding:'8px 4px'}}>Referrer</th>
                    <th style={{padding:'8px 4px', textAlign:'right'}}>Visits</th>
                  </tr>
                </thead>
                <tbody>
                  {refs.map(r => (
                    <tr key={r.referrer} style={{borderBottom:`1px solid ${T.border}`}}>
                      <td style={{padding:'8px 4px', color: r.referrer === 'Direct' ? T.muted : T.fg}}>{r.referrer}</td>
                      <td style={{padding:'8px 4px', textAlign:'right', color:T.muted}}>{r.visits.toLocaleString()}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            )
        }
      </Card>

      {/* Recent visits — raw log with its own range selector */}
      <div style={{marginTop:20}}>
        <Card title={`Recent Visits — ${fmtRangeLabel(recRange)}`}
          action={<DateRangeSelector range={recRange} setRange={setRecRange} presets={[7,30]} allowCustom/>}>
          {!recent
            ? <Skeleton h={120}/>
            : recent.length === 0
              ? <div style={{color:T.muted, fontSize:12}}>No visits logged yet.</div>
              : (
                <div style={{overflowX:'auto'}}>
                  <table style={{width:'100%', borderCollapse:'collapse', fontSize:11}}>
                    <thead>
                      <tr style={{textAlign:'left', color:T.muted, fontWeight:500, borderBottom:`1px solid ${T.border}`}}>
                        <th style={{padding:'8px 6px'}}>Time</th>
                        <th style={{padding:'8px 6px'}}>Page</th>
                        <th style={{padding:'8px 6px'}}>IP</th>
                        <th style={{padding:'8px 6px'}}>Location</th>
                        <th style={{padding:'8px 6px'}}>Device</th>
                        <th style={{padding:'8px 6px'}}>Browser / OS</th>
                        <th style={{padding:'8px 6px'}}>Referrer</th>
                      </tr>
                    </thead>
                    <tbody>
                      {recent.map((r, i) => {
                        const loc = [r.city, r.country].filter(Boolean).join(', ') || '—';
                        const flag = r.country && COUNTRY_FLAGS[r.country] ? COUNTRY_FLAGS[r.country] + ' ' : '';
                        return (
                          <tr key={i} style={{borderBottom:`1px solid ${T.border}`}}>
                            <td style={{padding:'7px 6px', color:T.muted, whiteSpace:'nowrap'}}>{r.when}</td>
                            <td style={{padding:'7px 6px', color:T.fg}}>{r.page}</td>
                            <td style={{padding:'7px 6px', color:T.muted, fontFamily:'monospace'}}>{r.ip}</td>
                            <td style={{padding:'7px 6px', color: loc === '—' ? T.muted : T.fg}}>{flag}{loc}</td>
                            <td style={{padding:'7px 6px', color:T.muted}}>{r.device || '—'}</td>
                            <td style={{padding:'7px 6px', color:T.muted}}>{(r.browser || '—') + ' / ' + (r.os || '—')}</td>
                            <td style={{padding:'7px 6px', color: r.referrer ? T.fg : T.muted}}>{r.referrer || 'Direct'}</td>
                          </tr>
                        );
                      })}
                    </tbody>
                  </table>
                </div>
              )
          }
        </Card>
      </div>
    </div>
  );
}

function StatBox({label, value, unit}) {
  return (
    <div style={{padding:'12px 14px', background:T.bg3, borderRadius:6}}>
      <div style={{fontSize:20, fontWeight:600, color:T.accent}}>{value}</div>
      <div style={{fontSize:11, color:T.muted}}>{label}</div>
      {unit && <div style={{fontSize:10, color:T.muted, opacity:.7, marginTop:2}}>{unit}</div>}
    </div>
  );
}

/* ── Root App ── */
function App() {
  const [tab,           setTab]           = useState('overview');
  const [pages,         setPages]         = useState(DEFAULT_PAGES);
  const [visibility,    setVisibility]    = useState({});
  const [activeTheme,   setActiveTheme]   = useState('rosa');
  const [splatEnabled,  setSplatEnabled]  = useState(false);

  useEffect(() => {
    if (window.initRosali) window.initRosali();
    apiGet('admin_pages').then(v => {
      if (v) try {
        const arr = JSON.parse(v);
        if (Array.isArray(arr) && arr.length) setPages(arr);
      } catch {}
    });
    apiGet('page_visibility').then(v => {
      if (v) try { setVisibility(JSON.parse(v) || {}); } catch {}
    });
    apiGet('active_theme').then(v => { if (v) setActiveTheme(v); });
    apiGet('splat_enabled').then(v => { setSplatEnabled(v === '1'); });
  }, []);

  const savePages = p => apiSet('admin_pages', JSON.stringify(p));

  return (
    <div style={{display:'flex', minHeight:'100vh'}}>
      <Sidebar tab={tab} setTab={setTab} pageCount={pages.length}/>
      <main style={{flex:1, padding:'32px 36px', overflowY:'auto', maxHeight:'100vh', background:T.bg}}>
        {tab === 'overview'  && <TabOverview pages={pages} visibility={visibility}/>}
        {tab === 'analytics' && <TabAnalytics/>}
        {tab === 'pages'     && <TabPages pages={pages} visibility={visibility} setVisibility={setVisibility} setPages={setPages} savePages={savePages}/>}
        {tab === 'media'    && <TabMedia splatEnabled={splatEnabled}/>}
        {tab === 'colors'   && <TabColors activeTheme={activeTheme} setActiveTheme={setActiveTheme}/>}
        {tab === 'content'  && <TabContent/>}
        {tab === 'seo'      && <TabSEO/>}
        {tab === 'layout'   && <TabLayout/>}
        {tab === 'settings' && <TabSettings splatEnabled={splatEnabled} setSplatEnabled={setSplatEnabled}/>}
      </main>
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('root')).render(<App/>);
</script>
</body>
</html>
