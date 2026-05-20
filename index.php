<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/seo.php';
@include_once 'includes/tracker.php';
$theme = getActiveTheme();
$lang  = getActiveLang();
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<?php seoMeta('home'); ?>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300&family=Outfit:wght@300;400;500;600&family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Lato:wght@300;400;700&display=swap" rel="stylesheet"/>
<script src="https://unpkg.com/react@18.3.1/umd/react.development.js" integrity="sha384-hD6/rw4ppMLGNu3tX5cjIb+uRZ7UkRJ6BPkLpg4hAu/6onKUg4lLsHAs9EBPT82L" crossorigin="anonymous"></script>
<script src="https://unpkg.com/react-dom@18.3.1/umd/react-dom.development.js" integrity="sha384-u6aeetuaXnQ38mYT8rp6sbXaQe3NL9t+IBXmnYxwkUI2Hw4bsp2Wvmx4yRQF1uAm" crossorigin="anonymous"></script>
<script src="https://unpkg.com/@babel/standalone@7.29.0/babel.min.js" integrity="sha384-m08KidiNqLdpJqLq95G/LEi8Qvjl/xUYll3QILypMoQ65QorJ9Lvtp2RXYGBFj1y" crossorigin="anonymous"></script>
</head>
<body class="theme-<?= $theme ?>">
<?php require __DIR__ . '/includes/front_init.php'; ?>
<div id="root"></div>
<script type="text/babel" src="shared.jsx"></script>
<script type="text/babel">
const { useState, useEffect } = React;
const { RosaliImg, RosaliNav, RosaliFooter, RosaliBtn, RosaliLabel, RosaliWaFab, initRosali, useResponsive, getUrlTheme, setUrlTheme, RC, getLayoutPref, applyColorOverrides } = window;

const C = {
  en:{
    hero:{ sup:"Situbondo · East Java · Indonesia", title:"Experience\nLush Garden\nRetreat",
      sub:"Modern traditional villas, exotic tropical gardens, and legendary Indonesian hospitality.", cta:"Book via WhatsApp" },
    about:{ label:"Our Story", title:"A Garden Oasis in the City Center",
      body:"Rosali Hotel is Situbondo's finest boutique hotel — wrapped in cascading tropical gardens, exotic flowers, and birdsong. In the heart of the city, yet a world away.",
      chips:["24-Hour Reception","Tropical Gardens","Garden Café","Free Parking","Free WiFi","Breakfast Included"] },
    rooms:{ label:"Rooms & Villas", title:"Where You'll Stay", sub:"5 unique room categories across lush garden clusters.", cta:"See All Rooms",
      list:[
        {name:"The Wooden House",tag:"Signature",desc:"Iconic teak villa deep in the garden."},
        {name:"The Orientals",tag:"Premium",desc:"Antique furnishings & garden views."},
        {name:"The VIPs",tag:"VIP",desc:"Spacious suite, ideal for families."},
      ]},
    events:{ label:"Events", title:"Host Your Event\nWith Us",
      sub:"From corporate meetings to dream weddings — beautifully hosted.", cta:"Learn More" },
    cafe:{ label:"Rosa De 5 Café", title:"Specialty Coffee\n& Garden Dining",
      sub:"A café within the hotel garden. Slow bar coffee, great food, open late.", hours:"Sun–Thu 09:00–23:00  ·  Fri–Sat 09:00–24:00", cta:"Visit the Café" },
    tourism:{ label:"Tourism", title:"Explore East Java",
      sub:"15 min from Pasir Putih Beach · Near Ijen Crater · Baluran National Park", cta:"See Attractions" },
    contact:{ cta:"Get in Touch" },
  },
  id:{
    hero:{ sup:"Situbondo · Jawa Timur · Indonesia", title:"Rasakan\nKedamaian\nTaman Tropis",
      sub:"Villa modern tradisional, taman tropis eksotis, dan keramahan Indonesia yang tulus.", cta:"Pesan via WhatsApp" },
    about:{ label:"Tentang Kami", title:"Oase Taman di Pusat Kota",
      body:"Rosali Hotel adalah hotel butik terbaik di Situbondo — dikelilingi taman tropis yang rimbun, bunga eksotis, dan kicauan burung. Di tengah kota, namun terasa seperti dunia lain.",
      chips:["Resepsi 24 Jam","Taman Tropis","Garden Café","Parkir Gratis","WiFi Gratis","Sarapan Termasuk"] },
    rooms:{ label:"Kamar & Villa", title:"Tempat Anda Menginap", sub:"5 kategori kamar unik di kluster taman yang rimbun.", cta:"Lihat Semua Kamar",
      list:[
        {name:"The Wooden House",tag:"Signature",desc:"Villa kayu jati ikonik di tengah taman."},
        {name:"The Orientals",tag:"Premium",desc:"Furnitur antik & pemandangan taman."},
        {name:"The VIPs",tag:"VIP",desc:"Suite luas, cocok untuk keluarga."},
      ]},
    events:{ label:"Acara", title:"Gelar Acara Anda\nBersama Kami",
      sub:"Dari rapat bisnis hingga pernikahan impian — digelar dengan indah.", cta:"Selengkapnya" },
    cafe:{ label:"Rosa De 5 Café", title:"Kopi Specialty\n& Santap di Taman",
      sub:"Café di dalam taman hotel. Slow bar coffee, makanan lezat, buka hingga malam.", hours:"Min–Kam 09:00–23:00  ·  Jum–Sab 09:00–24:00", cta:"Kunjungi Café" },
    tourism:{ label:"Wisata", title:"Jelajahi Jawa Timur",
      sub:"15 menit ke Pantai Pasir Putih · Dekat Kawah Ijen · Taman Nasional Baluran", cta:"Lihat Destinasi" },
    contact:{ cta:"Hubungi Kami" },
  }
};

const TWEAK_DEFAULTS = /*EDITMODE-BEGIN*/{
  "theme": "<?= $theme ?>",
  "lang": "<?= $lang ?>"
}/*EDITMODE-END*/;

function App(){
  const init = initRosali(TWEAK_DEFAULTS.theme, TWEAK_DEFAULTS.lang);
  const [theme,setTheme]=useState(TWEAK_DEFAULTS.theme);
  const [lang,setLang]=useState(()=>localStorage.getItem('rosali_lang')||TWEAK_DEFAULTS.lang);
  const [tweaks,setTweaks]=useState(false);
  const { isMobile, isTablet } = useResponsive();
  const c=C[lang];
  const aboutFlip   = getLayoutPref('home_about_flip','normal')==='flip';
  const cafeFlip    = getLayoutPref('home_cafe_flip','normal')==='flip';
  const eventsFlip  = getLayoutPref('home_events_flip','normal')==='flip';
  const roomsCols   = getLayoutPref('home_rooms_cols','3');

  useEffect(()=>{
    localStorage.setItem('rosali_lang',lang);
  },[lang]);

  useEffect(()=>{
    const fn=e=>{
      if(e.data?.type==='__activate_edit_mode') setTweaks(true);
      if(e.data?.type==='__deactivate_edit_mode') setTweaks(false);
    };
    window.addEventListener('message',fn);
    window.parent.postMessage({type:'__edit_mode_available'},'*');
    return()=>window.removeEventListener('message',fn);
  },[]);

  const setThemeAndSave = t=>{
    setTheme(t);
    window.parent.postMessage({type:'__edit_mode_set_keys',edits:{theme:t}},'*');
  };
  const setLangAndSave = l=>{
    setLang(l); localStorage.setItem('rosali_lang',l);
    window.parent.postMessage({type:'__edit_mode_set_keys',edits:{lang:l}},'*');
  };

  return(
    <div className={`theme-${theme}`} style={{minHeight:'100vh'}}>
      <RosaliNav lang={lang} setLang={setLangAndSave} current="home" theme={theme}/>

      {/* HERO */}
      <section style={{position:'relative',height:'100vh',minHeight:620,overflow:'hidden',
        display:'flex',alignItems:'flex-end',padding:'0 clamp(20px,6vw,96px) clamp(48px,6vw,88px)'}}>
        <RosaliImg label="hero — hotel garden entrance / aerial view at golden hour" h="100%"
          style={{position:'absolute',inset:0,height:'100%'}}/>
        <div style={{position:'absolute',inset:0,
          background:'linear-gradient(to top,oklch(9% 0.04 150/0.88) 0%,oklch(9% 0.04 150/0.22) 55%,transparent 100%)'}}/>
        <div style={{position:'relative',zIndex:2,maxWidth:820}}>
          <p style={{fontFamily:'var(--font-b)',fontSize:12,letterSpacing:'0.22em',
            textTransform:'uppercase',color:'oklch(80% 0.1 80)',marginBottom:18}}>{RC('hero_sup_'+lang, c.hero.sup)}</p>
          <h1 style={{fontFamily:'var(--font-d)',fontSize:'clamp(48px,8vw,100px)',
            lineHeight:1.02,color:'oklch(97% 0.01 100)',fontWeight:700,
            whiteSpace:'pre-line',marginBottom:22}}>{RC('hero_title_'+lang, c.hero.title)}</h1>
          <p style={{fontFamily:'var(--font-b)',fontSize:16,color:'oklch(90% 0.015 100)',
            maxWidth:440,lineHeight:1.75,marginBottom:38,opacity:.9}}>{RC('hero_sub_'+lang, c.hero.sub)}</p>
          <RosaliBtn text={RC('hero_cta_'+lang, c.hero.cta)} style={{fontSize:14,padding:'15px 30px'}}/>
        </div>
      </section>

      {/* ABOUT */}
      <section style={{padding:'clamp(56px,8vw,104px) clamp(20px,6vw,96px)',
        display:'grid',gridTemplateColumns:isMobile?'1fr':'1fr 1fr',gap:'clamp(36px,6vw,80px)',
        alignItems:'center',background:'var(--bg)'}}>
        <div>
          <RosaliLabel>{RC('about_label_'+lang, c.about.label)}</RosaliLabel>
          <h2 style={{fontFamily:'var(--font-d)',fontSize:'clamp(26px,3.5vw,44px)',
            lineHeight:1.18,marginBottom:18,color:'var(--fg)'}}>{RC('about_title_'+lang, c.about.title)}</h2>
          <p style={{fontFamily:'var(--font-b)',fontSize:15,lineHeight:1.85,
            color:'var(--fg-muted)',marginBottom:28}}>{RC('about_body_'+lang, c.about.body)}</p>
          <div style={{display:'flex',flexWrap:'wrap',gap:8}}>
            {(RC('about_chips_'+lang,'')?RC('about_chips_'+lang,'').split(',').map(s=>s.trim()).filter(Boolean):c.about.chips).map(h=>(
              <span key={h} style={{background:'var(--bg2)',color:'var(--accent)',
                border:'1px solid var(--border)',padding:'5px 12px',fontSize:11,
                letterSpacing:'0.05em',borderRadius:2,fontFamily:'var(--font-b)',fontWeight:500}}>{h}</span>
            ))}
          </div>
        </div>
        <RosaliImg label="about — garden gazebo / tropical flowers" h={440} style={{borderRadius:2}}/>
      </section>

      {/* ROOMS PREVIEW */}
      <section style={{background:'var(--bg2)',padding:'clamp(56px,8vw,104px) clamp(20px,6vw,96px)'}}>
        <div style={{display:'flex',justifyContent:'space-between',alignItems:'flex-end',marginBottom:44,flexWrap:'wrap',gap:12}}>
          <div>
            <RosaliLabel>{RC('home_rooms_label_'+lang, c.rooms.label)}</RosaliLabel>
            <h2 style={{fontFamily:'var(--font-d)',fontSize:'clamp(26px,3.5vw,44px)',color:'var(--fg)'}}>{RC('home_rooms_title_'+lang, c.rooms.title)}</h2>
          </div>
          <a href="rooms.php" style={{fontFamily:'var(--font-b)',fontSize:13,color:'var(--accent)',
            letterSpacing:'0.05em',fontWeight:500,borderBottom:'1px solid var(--accent)',paddingBottom:2,
            transition:'opacity .2s'}}
            onMouseEnter={e=>e.currentTarget.style.opacity='.7'}
            onMouseLeave={e=>e.currentTarget.style.opacity='1'}
          >{RC('home_rooms_cta_'+lang, c.rooms.cta)} →</a>
        </div>
        <div style={{display:'grid',gridTemplateColumns:isMobile?'1fr':isTablet?'repeat(2,1fr)':`repeat(${roomsCols},1fr)`,gap:3}}>
          {[['wooden','Signature'],['oriental','Premium'],['vip','VIP']].map(([rk,rtag],i)=>{
            const r = c.rooms.list[i];
            const rName = RC('room_'+rk+'_name_'+lang, r.name);
            const rDesc = RC('room_'+rk+'_desc_'+lang, r.desc);
            return (
            <a key={rk} href="rooms.php" style={{background:'var(--card)',borderRadius:2,
              overflow:'hidden',display:'block',transition:'transform .25s'}}
              onMouseEnter={e=>e.currentTarget.style.transform='translateY(-4px)'}
              onMouseLeave={e=>e.currentTarget.style.transform='none'}
            >
              <RosaliImg label={`room — ${rk} interior`} h={220}/>
              <div style={{padding:'18px 16px'}}>
                <span style={{fontSize:9,letterSpacing:'0.14em',textTransform:'uppercase',
                  color:'var(--accent)',fontFamily:'var(--font-b)',fontWeight:600}}>{rtag}</span>
                <h4 style={{fontFamily:'var(--font-d)',fontSize:18,color:'var(--fg)',margin:'6px 0 8px'}}>{rName}</h4>
                <p style={{fontFamily:'var(--font-b)',fontSize:12,color:'var(--fg-muted)',lineHeight:1.6}}>{rDesc}</p>
              </div>
            </a>
            );
          })}
        </div>
      </section>

      {/* CAFE TEASER */}
      <section style={{background:'var(--bg)',padding:'clamp(56px,8vw,104px) clamp(20px,6vw,96px)',
        display:'grid',gridTemplateColumns:isMobile?'1fr':'1fr 1fr',gap:'clamp(36px,6vw,80px)',alignItems:'center'}}>
        <div>
          <RosaliLabel>{RC('home_cafe_label_'+lang, c.cafe.label)}</RosaliLabel>
          <h2 style={{fontFamily:'var(--font-d)',fontSize:'clamp(26px,3.5vw,44px)',
            lineHeight:1.15,color:'var(--fg)',marginBottom:16,whiteSpace:'pre-line'}}>{RC('home_cafe_title_'+lang, c.cafe.title)}</h2>
          <p style={{fontFamily:'var(--font-b)',fontSize:15,color:'var(--fg-muted)',lineHeight:1.8,marginBottom:14}}>{RC('home_cafe_sub_'+lang, c.cafe.sub)}</p>
          <p style={{fontFamily:'var(--font-b)',fontSize:12,color:'var(--accent)',
            letterSpacing:'0.06em',marginBottom:28}}>{RC('cafe_hours_'+lang, c.cafe.hours)}</p>
          <a href="cafe.php" style={{display:'inline-flex',alignItems:'center',gap:6,
            fontFamily:'var(--font-b)',fontSize:13,color:'var(--accent)',fontWeight:500,
            borderBottom:'1px solid var(--accent)',paddingBottom:2}}>
            {RC('home_cafe_cta_'+lang, c.cafe.cta)} →
          </a>
        </div>
        <RosaliImg label="cafe — Rosa De 5 interior / specialty coffee / slow bar" h={400} style={{borderRadius:2}}/>
      </section>

      {/* EVENTS TEASER */}
      <section style={{background:'var(--bg2)',padding:'clamp(56px,8vw,104px) clamp(20px,6vw,96px)',
        display:'grid',gridTemplateColumns:isMobile?'1fr':'1fr 1fr',gap:'clamp(36px,6vw,80px)',alignItems:'center'}}>
        <RosaliImg label="events — wedding garden / jasmine meeting room setup" h={400} style={{borderRadius:2}}/>
        <div>
          <RosaliLabel>{RC('home_events_label_'+lang, c.events.label)}</RosaliLabel>
          <h2 style={{fontFamily:'var(--font-d)',fontSize:'clamp(26px,3.5vw,44px)',
            lineHeight:1.15,color:'var(--fg)',marginBottom:16,whiteSpace:'pre-line'}}>{RC('home_events_title_'+lang, c.events.title)}</h2>
          <p style={{fontFamily:'var(--font-b)',fontSize:15,color:'var(--fg-muted)',lineHeight:1.8,marginBottom:28}}>{RC('home_events_sub_'+lang, c.events.sub)}</p>
          <a href="events.php" style={{display:'inline-flex',alignItems:'center',gap:6,
            fontFamily:'var(--font-b)',fontSize:13,color:'var(--accent)',fontWeight:500,
            borderBottom:'1px solid var(--accent)',paddingBottom:2}}>
            {RC('home_events_cta_'+lang, c.events.cta)} →
          </a>
        </div>
      </section>

      {/* TOURISM STRIP */}
      <section style={{background:'var(--bg)',padding:'clamp(56px,8vw,104px) clamp(20px,6vw,96px)',textAlign:'center'}}>
        <RosaliLabel>{RC('home_tourism_label_'+lang, c.tourism.label)}</RosaliLabel>
        <h2 style={{fontFamily:'var(--font-d)',fontSize:'clamp(26px,3.5vw,44px)',color:'var(--fg)',marginBottom:12}}>{RC('home_tourism_title_'+lang, c.tourism.title)}</h2>
        <p style={{fontFamily:'var(--font-b)',fontSize:14,color:'var(--fg-muted)',marginBottom:28,lineHeight:1.6}}>{RC('home_tourism_sub_'+lang, c.tourism.sub)}</p>
        <a href="tourism.php" style={{display:'inline-flex',alignItems:'center',gap:6,margin:'0 auto',
          fontFamily:'var(--font-b)',fontSize:13,color:'var(--accent)',fontWeight:500,
          borderBottom:'1px solid var(--accent)',paddingBottom:2}}>
          {RC('home_tourism_cta_'+lang, c.tourism.cta)} →
        </a>
      </section>

      <RosaliFooter lang={lang}/>
      <RosaliWaFab/>

      {/* TWEAKS */}
      {tweaks&&(
        <div style={{position:'fixed',bottom:88,right:24,zIndex:300,width:254,
          background:'var(--card)',border:'1px solid var(--border)',borderRadius:8,
          boxShadow:'0 8px 40px rgba(0,0,0,0.2)',padding:18,fontFamily:'var(--font-b)'}}>
          <div style={{fontWeight:600,fontSize:12,marginBottom:14,color:'var(--fg)',letterSpacing:'0.1em',textTransform:'uppercase'}}>Tweaks</div>
          <div style={{fontSize:10,textTransform:'uppercase',letterSpacing:'0.14em',color:'var(--fg-muted)',marginBottom:8}}>Tema Visual</div>
          {[
            ['garden','🌿 Garden Sanctuary','Hijau & krem hangat'],
            ['boutique','🏛 Modern Boutique','Sage & terracotta'],
            ['javanese','✨ Javanese Luxury','Gelap & emas'],
            ['rosa','🌹 Rosa','Merah mawar & hijau hutan'],
            ['coastal','🌊 Coastal Java','Biru laut & pasir'],
            ['batik','🔷 Midnight Batik','Navy gelap & emas royal'],
          ].map(([id,name,desc])=>(
            <button key={id} onClick={()=>setThemeAndSave(id)} style={{
              width:'100%',textAlign:'left',background:theme===id?'var(--accent)':'var(--bg2)',
              color:theme===id?'var(--bg)':'var(--fg)',border:'none',borderRadius:4,
              padding:'9px 11px',marginBottom:5,cursor:'pointer',transition:'all .18s'}}>
              <div style={{fontWeight:600,fontSize:12}}>{name}</div>
              <div style={{fontSize:10,opacity:.7,marginTop:2}}>{desc}</div>
            </button>
          ))}
          <div style={{height:1,background:'var(--border)',margin:'12px 0'}}/>
          <div style={{fontSize:10,textTransform:'uppercase',letterSpacing:'0.14em',color:'var(--fg-muted)',marginBottom:8}}>Bahasa</div>
          <div style={{display:'flex',gap:5}}>
            {['en','id'].map(l=>(
              <button key={l} onClick={()=>setLangAndSave(l)} style={{
                flex:1,background:lang===l?'var(--accent)':'var(--bg2)',
                color:lang===l?'var(--bg)':'var(--fg)',border:'none',borderRadius:4,
                padding:'8px',cursor:'pointer',fontSize:12,fontWeight:600,transition:'all .18s'
              }}>{l==='en'?'🇬🇧 EN':'🇮🇩 ID'}</button>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('root')).render(<App/>);
</script>
</body>
</html>
