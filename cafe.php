<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
$theme = getActiveTheme();
$lang  = getActiveLang();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Rosa De 5 Café — Rosali Hotel Situbondo</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300&family=Outfit:wght@300;400;500;600&family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Lato:wght@300;400;700&display=swap" rel="stylesheet"/>
<script src="https://unpkg.com/react@18.3.1/umd/react.development.js" integrity="sha384-hD6/rw4ppMLGNu3tX5cjIb+uRZ7UkRJ6BPkLpg4hAu/6onKUg4lLsHAs9EBPT82L" crossorigin="anonymous"></script>
<script src="https://unpkg.com/react-dom@18.3.1/umd/react-dom.development.js" integrity="sha384-u6aeetuaXnQ38mYT8rp6sbXaQe3NL9t+IBXmnYxwkUI2Hw4bsp2Wvmx4yRQF1uAm" crossorigin="anonymous"></script>
<script src="https://unpkg.com/@babel/standalone@7.29.0/babel.min.js" integrity="sha384-m08KidiNqLdpJqLq95G/LEi8Qvjl/xUYll3QILypMoQ65QorJ9Lvtp2RXYGBFj1y" crossorigin="anonymous"></script>
</head>
<body class="theme-<?= $theme ?>"><div id="root"></div>
<script type="text/babel" src="shared.jsx"></script>
<script type="text/babel">
const { useState, useEffect } = React;
const { RosaliImg, RosaliNav, RosaliFooter, RosaliBtn, RosaliLabel, RosaliWaFab, initRosali, useResponsive, getUrlTheme, setUrlTheme } = window;

const MENU = {
  en: [
    { cat:'Specialty Coffee', icon:'☕', items:[
      {name:'Single Origin Pour Over',desc:'Slow-drip precision, rotating origin'},
      {name:'Espresso',desc:'Classic double shot, rich crema'},
      {name:'Flat White',desc:'Silky microfoam, bold espresso'},
      {name:'Cold Brew',desc:'12-hour steep, smooth & refreshing'},
      {name:'V60 / Chemex',desc:'Pour-over methods, bright & clean'},
    ]},
    { cat:'Food & Bites', icon:'🍽️', items:[
      {name:'Nasi Goreng Spesial',desc:'Signature fried rice, house sambal'},
      {name:'Gorengan Platter',desc:'Assorted Indonesian fritters'},
      {name:'Toast & Eggs',desc:'Thick toast, soft egg, butter'},
      {name:'Mie Goreng',desc:'Wok-tossed noodles, garden herbs'},
    ]},
    { cat:'Drinks & Juice', icon:'🍹', items:[
      {name:'Fresh Fruit Juice',desc:'Seasonal local fruits'},
      {name:'Es Jeruk',desc:'Fresh-squeezed orange, ice'},
      {name:'Teh Tarik',desc:'Pulled milk tea, frothy'},
      {name:'Mineral Water',desc:'Still or sparkling'},
    ]},
  ],
  id: [
    { cat:'Kopi Specialty', icon:'☕', items:[
      {name:'Single Origin Pour Over',desc:'Seduhan presisi, origin berganti'},
      {name:'Espresso',desc:'Double shot klasik, crema kaya'},
      {name:'Flat White',desc:'Microfoam lembut, espresso kuat'},
      {name:'Cold Brew',desc:'Seduhan 12 jam, halus & menyegarkan'},
      {name:'V60 / Chemex',desc:'Metode pour-over, cerah & bersih'},
    ]},
    { cat:'Makanan & Camilan', icon:'🍽️', items:[
      {name:'Nasi Goreng Spesial',desc:'Nasi goreng khas, sambal rumahan'},
      {name:'Gorengan Platter',desc:'Aneka gorengan Indonesia'},
      {name:'Toast & Telur',desc:'Roti tebal, telur lembut, mentega'},
      {name:'Mie Goreng',desc:'Mi tumis, herba taman'},
    ]},
    { cat:'Minuman & Jus', icon:'🍹', items:[
      {name:'Jus Buah Segar',desc:'Buah lokal musiman'},
      {name:'Es Jeruk',desc:'Jeruk peras segar, es'},
      {name:'Teh Tarik',desc:'Teh susu tarik, berbuih'},
      {name:'Air Mineral',desc:'Masih atau berkarbonasi'},
    ]},
  ]
};

function App(){
  const [theme]=useState(()=>getUrlTheme()||localStorage.getItem('rosali_theme')||'rosa');
  const [lang,setLang]=useState(()=>localStorage.getItem('rosali_lang')||'id');
  const [tab,setTab]=useState(0);
  const { isMobile } = useResponsive();
  initRosali(theme,lang);
  useEffect(()=>{ setUrlTheme(theme); },[theme]);
  const en=lang==='en';
  const menu=MENU[lang];

  return(
    <div className={`theme-${theme}`} style={{minHeight:'100vh'}}>
      <RosaliNav lang={lang} setLang={l=>{setLang(l);localStorage.setItem('rosali_lang',l)}} current="cafe" theme={theme}/>

      {/* HERO — dark moody for cafe */}
      <section style={{position:'relative',height:'90vh',minHeight:560,overflow:'hidden',
        display:'flex',alignItems:'flex-end',padding:'0 clamp(20px,6vw,96px) clamp(48px,6vw,80px)'}}>
        <RosaliImg label="cafe hero — Rosa De 5 interior / barista / specialty coffee setup" h="100%"
          style={{position:'absolute',inset:0,height:'100%'}}/>
        <div style={{position:'absolute',inset:0,
          background:'linear-gradient(to top,rgba(6,4,2,0.94) 0%,rgba(6,4,2,0.3) 50%,transparent 100%)'}}/>
        <div style={{position:'relative',zIndex:2,maxWidth:700}}>
          <p style={{fontFamily:'var(--font-b)',fontSize:11,letterSpacing:'0.24em',
            textTransform:'uppercase',color:'oklch(72% 0.15 76)',marginBottom:16}}>
            {en?'Within Rosali Hotel · Situbondo':'Di Dalam Rosali Hotel · Situbondo'}
          </p>
          <h1 style={{fontFamily:'var(--font-d)',fontSize:'clamp(44px,7vw,90px)',
            lineHeight:1.02,color:'oklch(97% 0.01 100)',fontWeight:700,marginBottom:20}}>
            Rosa De 5<br/><em style={{fontStyle:'italic',fontWeight:400}}>Café</em>
          </h1>
          <p style={{fontFamily:'var(--font-b)',fontSize:16,color:'oklch(88% 0.015 100)',
            maxWidth:420,lineHeight:1.75,marginBottom:16,opacity:.9}}>
            {en?'Specialty coffee · Slow bar · Garden ambiance · Open late'
              :'Kopi specialty · Slow bar · Suasana taman · Buka hingga malam'}
          </p>
          <p style={{fontFamily:'var(--font-b)',fontSize:13,color:'oklch(72% 0.15 76)',
            letterSpacing:'0.06em',marginBottom:36}}>
            {en?'Sun–Thu 09:00–23:00  ·  Fri–Sat 09:00–24:00'
              :'Min–Kam 09:00–23:00  ·  Jum–Sab 09:00–24:00'}
          </p>
          <div style={{display:'flex',gap:14,flexWrap:'wrap'}}>
            <RosaliBtn
              text={en?'Reserve a Table':'Reservasi Meja'}
              href="https://wa.me/6285956799123"
              style={{fontSize:14,padding:'14px 28px',background:'oklch(72% 0.15 76)',color:'oklch(10% 0.02 60)'}}
            />
            <a href="https://www.instagram.com/rosade5cafe/" target="_blank" rel="noopener noreferrer"
              style={{display:'inline-flex',alignItems:'center',gap:8,
                border:'1px solid rgba(255,255,255,0.35)',color:'oklch(90% 0.01 100)',
                padding:'14px 24px',borderRadius:2,fontFamily:'var(--font-b)',fontSize:14,
                letterSpacing:'0.04em',transition:'background .2s'}}
              onMouseEnter={e=>e.currentTarget.style.background='rgba(255,255,255,0.08)'}
              onMouseLeave={e=>e.currentTarget.style.background='transparent'}
            >
              <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
              @rosade5cafe
            </a>
          </div>
        </div>
      </section>

      {/* ABOUT CAFE */}
      <section style={{background:'var(--bg)',padding:'clamp(48px,6vw,88px) clamp(20px,6vw,96px)',
        display:'grid',gridTemplateColumns:isMobile?'1fr':'1fr 1fr',gap:'clamp(32px,5vw,72px)',alignItems:'center'}}>
        <div>
          <RosaliLabel>{en?'About the Café':'Tentang Café'}</RosaliLabel>
          <h2 style={{fontFamily:'var(--font-d)',fontSize:'clamp(24px,3vw,42px)',
            lineHeight:1.18,color:'var(--fg)',marginBottom:18}}>
            {en?'Specialty Coffee\nIn a Garden Setting':'Kopi Specialty\ndi Tengah Taman'}
          </h2>
          <p style={{fontFamily:'var(--font-b)',fontSize:15,color:'var(--fg-muted)',lineHeight:1.85,marginBottom:20}}>
            {en?'Rosa De 5 Café sits within the lush grounds of Rosali Hotel, offering specialty coffee, a curated slow bar experience, and a food menu that keeps guests coming back. Whether you\'re here to work, meet, or simply enjoy the garden — we\'re open until late.'
              :'Rosa De 5 Café terletak di dalam taman rimbun Rosali Hotel, menawarkan kopi specialty, pengalaman slow bar yang terkurasi, dan menu makanan yang membuat tamu kembali lagi. Baik Anda datang untuk bekerja, bertemu, atau sekadar menikmati taman — kami buka hingga malam.'}
          </p>
          <div style={{display:'flex',flexWrap:'wrap',gap:8,marginBottom:28}}>
            {(en?['Specialty Coffee','Slow Bar','Garden Seating','WFC-Friendly','Fast WiFi','Open Late']
              :['Kopi Specialty','Slow Bar','Kursi Taman','WFC-Friendly','WiFi Cepat','Buka Malam']).map(c=>(
              <span key={c} style={{background:'var(--bg2)',color:'var(--accent)',
                border:'1px solid var(--border)',padding:'5px 12px',fontSize:11,
                borderRadius:2,fontFamily:'var(--font-b)',fontWeight:500}}>{c}</span>
            ))}
          </div>
          <div style={{display:'grid',gridTemplateColumns:'1fr 1fr',gap:16}}>
            {[
              {lbl:en?'Hours (Sun–Thu)':'Jam (Min–Kam)',val:'09:00 – 23:00'},
              {lbl:en?'Hours (Fri–Sat)':'Jam (Jum–Sab)',val:'09:00 – 24:00'},
              {lbl:'WhatsApp',val:'+62 859 5679 9123'},
              {lbl:'Instagram',val:'@rosade5cafe'},
            ].map(r=>(
              <div key={r.lbl}>
                <div style={{fontFamily:'var(--font-b)',fontSize:10,letterSpacing:'0.14em',
                  textTransform:'uppercase',color:'var(--accent)',marginBottom:3}}>{r.lbl}</div>
                <div style={{fontFamily:'var(--font-b)',fontSize:14,color:'var(--fg)'}}>{r.val}</div>
              </div>
            ))}
          </div>
        </div>
        <div style={{display:'grid',gridTemplateRows:'280px 200px',gap:3}}>
          <RosaliImg label="cafe — interior ambiance / cozy seating area" h="100%" style={{height:'100%',borderRadius:'2px 2px 0 0'}}/>
          <div style={{display:'grid',gridTemplateColumns:'1fr 1fr',gap:3}}>
            <RosaliImg label="cafe — barista making pour over coffee" h="100%" style={{height:'100%'}}/>
            <RosaliImg label="cafe — garden outdoor seating" h="100%" style={{height:'100%'}}/>
          </div>
        </div>
      </section>

      {/* MENU */}
      <section style={{background:'var(--bg2)',padding:'clamp(48px,6vw,88px) clamp(20px,6vw,96px)'}}>
        <div style={{textAlign:'center',marginBottom:40}}>
          <RosaliLabel>{en?'Our Menu':'Menu Kami'}</RosaliLabel>
          <h2 style={{fontFamily:'var(--font-d)',fontSize:'clamp(24px,3vw,42px)',color:'var(--fg)'}}>
            {en?'What We Serve':'Yang Kami Sajikan'}
          </h2>
        </div>
        {/* Category tabs */}
        <div style={{display:'flex',justifyContent:'center',gap:4,marginBottom:36,flexWrap:'wrap'}}>
          {menu.map((m,i)=>(
            <button key={i} onClick={()=>setTab(i)} style={{
              background:tab===i?'var(--accent)':'var(--card)',
              color:tab===i?'var(--bg)':'var(--fg)',
              border:'none',padding:'10px 20px',borderRadius:2,cursor:'pointer',
              fontFamily:'var(--font-b)',fontSize:13,fontWeight:tab===i?600:400,
              transition:'all .2s',display:'flex',alignItems:'center',gap:6}}>
              <span>{m.icon}</span>{m.cat}
            </button>
          ))}
        </div>
        <div style={{display:'grid',gridTemplateColumns:'repeat(auto-fill,minmax(260px,1fr))',gap:3}}>
          {menu[tab].items.map((item,i)=>(
            <div key={i} style={{background:'var(--card)',padding:'20px',borderRadius:2,
              border:'1px solid var(--border)',transition:'background .2s'}}
              onMouseEnter={e=>e.currentTarget.style.background='var(--bg2)'}
              onMouseLeave={e=>e.currentTarget.style.background='var(--card)'}
            >
              <div style={{fontFamily:'var(--font-d)',fontSize:17,color:'var(--fg)',marginBottom:6}}>{item.name}</div>
              <div style={{fontFamily:'var(--font-b)',fontSize:12,color:'var(--fg-muted)',lineHeight:1.6}}>{item.desc}</div>
            </div>
          ))}
        </div>
        <p style={{textAlign:'center',marginTop:24,fontFamily:'var(--font-b)',fontSize:12,color:'var(--fg-muted)'}}>
          {en?'* Menu may vary seasonally. Ask our barista for today\'s specials.'
            :'* Menu bisa berbeda tiap musim. Tanyakan barista kami untuk spesial hari ini.'}
        </p>
      </section>

      {/* GALLERY ROW */}
      <section style={{background:'var(--bg)',padding:'clamp(48px,6vw,88px) clamp(20px,6vw,96px)'}}>
        <RosaliLabel>{en?'Atmosphere':'Suasana'}</RosaliLabel>
        <h2 style={{fontFamily:'var(--font-d)',fontSize:'clamp(24px,3vw,40px)',color:'var(--fg)',marginBottom:32}}>
          {en?'The Rosa De 5 Experience':'Pengalaman Rosa De 5'}
        </h2>
          <div style={{display:'grid',gridTemplateColumns:isMobile?'1fr 1fr':'repeat(4,1fr)',gap:3}}>
          {['cafe latte art closeup','cafe garden view at night','cafe counter & menu board','cafe guests working / WFC'].map((l,i)=>(
            <RosaliImg key={i} label={`cafe — ${l}`} h={220} style={{borderRadius:2}}/>
          ))}
        </div>
      </section>

      {/* CTA */}
      <section style={{background:'var(--accent)',padding:'52px clamp(20px,6vw,96px)',
        display:'flex',justifyContent:'space-between',alignItems:'center',flexWrap:'wrap',gap:20}}>
        <div>
          <h3 style={{fontFamily:'var(--font-d)',fontSize:'clamp(20px,2.5vw,34px)',color:'var(--bg)',marginBottom:8}}>
            {en?'Visit Rosa De 5 Today':'Kunjungi Rosa De 5 Hari Ini'}
          </h3>
          <p style={{fontFamily:'var(--font-b)',fontSize:14,color:'var(--bg)',opacity:.8}}>
            {en?'Walk in anytime — or reserve a table via WhatsApp.':'Datang kapan saja — atau reservasi meja via WhatsApp.'}
          </p>
        </div>
        <RosaliBtn text={en?'Reserve a Table':'Reservasi Meja'}
          href="https://wa.me/6285956799123"
          style={{background:'var(--bg)',color:'var(--accent)'}}/>
      </section>

      <RosaliFooter lang={lang}/>
      <RosaliWaFab/>
    </div>
  );
}
ReactDOM.createRoot(document.getElementById('root')).render(<App/>);
</script>
</body></html>
