<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/seo.php';
@include_once 'includes/tracker.php';
$theme        = getActiveTheme();
$lang         = getActiveLang();
$splatEnabled = isSplatEnabled();

/* Splat assignments per room: slot key = 'room_<key>_splat' */
$roomKeys     = ['wooden','oriental','vip','superior','standard'];
$roomSplats   = [];
if ($splatEnabled) {
  foreach ($roomKeys as $rk) {
    $m = mediaForSlot('room_' . $rk . '_splat');
    if ($m) $roomSplats[$rk] = $m['url'];
  }
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<?php seoMeta('rooms'); ?>
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
<?php if ($splatEnabled && $roomSplats): ?>
<!-- gsplat.js loaded only when at least one room has a splat assigned -->
<script type="module" src="https://cdn.jsdelivr.net/npm/@mkkellogg/gaussian-splats-3d@0.4.6/build/gaussian-splats-3d.module.min.js" defer></script>
<?php endif; ?>
<script type="text/babel">
const { useState, useEffect, useRef } = React;
const { RosaliImg, RosaliNav, RosaliFooter, RosaliBtn, RosaliLabel, RosaliPageHero, RosaliWaFab, initRosali, useResponsive, getUrlTheme, setUrlTheme, RC } = window;
const ROOM_SPLATS = <?= json_encode($roomSplats) ?>;
const SPLAT_ENABLED = <?= $splatEnabled ? 'true' : 'false' ?>;

const ROOMS = [
  { key:'wooden', tag:'Signature', nameEn:'The Wooden House', nameId:'The Wooden House',
    descEn:'Our most iconic villa — crafted from natural teak wood, immersed deep in the garden. Features a private terrace with garden views, traditional Javanese architecture meets modern comfort.',
    descId:'Villa paling ikonik kami — dibuat dari kayu jati alami, terbenam jauh di dalam taman. Teras pribadi dengan pemandangan taman, arsitektur Jawa tradisional bertemu kenyamanan modern.',
    amenities:['King Bed','Private Terrace','Garden View','AC','Hot Shower','WiFi','TV','Mini Fridge'] },
  { key:'oriental', tag:'Premium', nameEn:'The Orientals', nameId:'The Orientals',
    descEn:'East-meets-West interiors with antique furnishings, batik textiles, and private garden views. A curated experience blending Indonesian heritage with boutique comfort.',
    descId:'Interior Timur-Barat dengan furnitur antik, tekstil batik, dan pemandangan taman pribadi. Pengalaman terkurasi memadukan warisan Indonesia dengan kenyamanan butik.',
    amenities:['King/Twin Bed','Private Terrace','Garden View','AC','Hot Shower','WiFi','TV','Minibar'] },
  { key:'vip', tag:'VIP', nameEn:'The VIPs', nameId:'The VIPs',
    descEn:'Our most spacious accommodation — a full VIP suite with separate living room, dining area, and premium furnishings. Ideal for families or extended stays.',
    descId:'Akomodasi paling luas kami — suite VIP penuh dengan ruang tamu terpisah, area makan, dan furnitur premium. Ideal untuk keluarga atau menginap panjang.',
    amenities:['King Bed','Living Room','Dining Area','AC','Hot Shower','WiFi','Smart TV','Minibar','Bathtub'] },
  { key:'superior', tag:'Superior', nameEn:'The Superiors', nameId:'The Superiors',
    descEn:'Comfortable superior rooms with modern amenities, garden-facing windows, and a private outdoor sitting area. Perfect balance of value and comfort.',
    descId:'Kamar superior nyaman dengan fasilitas modern, jendela menghadap taman, dan area duduk luar pribadi. Keseimbangan sempurna antara nilai dan kenyamanan.',
    amenities:['Queen Bed','Outdoor Sitting','Garden View','AC','Hot Shower','WiFi','TV'] },
  { key:'standard', tag:'Standard', nameEn:'Standard Rooms', nameId:'Kamar Standar',
    descEn:'Clean, cozy, and well-appointed standard rooms. Everything you need for a comfortable stay — perfect for transit travelers and business guests.',
    descId:'Kamar standar yang bersih, nyaman, dan tertata baik. Semua yang Anda butuhkan untuk menginap nyaman — sempurna untuk tamu transit dan bisnis.',
    amenities:['Queen/Twin Bed','AC','Hot Shower','WiFi','TV'] },
];

/* gsplat.js viewer — only rendered when SPLAT_ENABLED and a splat URL exists for the active room. */
function SplatViewer({url}){
  const ref = useRef(null);
  useEffect(()=>{
    let viewer;
    let cancelled = false;
    (async () => {
      try {
        const mod = await import('https://cdn.jsdelivr.net/npm/@mkkellogg/gaussian-splats-3d@0.4.6/build/gaussian-splats-3d.module.min.js');
        if (cancelled || !ref.current) return;
        viewer = new mod.Viewer({
          rootElement: ref.current,
          cameraUp: [0, -1, 0],
          initialCameraPosition: [0, 0, 5],
          initialCameraLookAt: [0, 0, 0],
          sharedMemoryForWorkers: false,
        });
        await viewer.addSplatScene(url, { showLoadingUI: true });
        if (!cancelled) viewer.start();
      } catch (e) { console.error('splat viewer failed:', e); }
    })();
    return () => { cancelled = true; try { viewer?.dispose?.(); } catch {} };
  }, [url]);
  return (
    <div style={{gridColumn:'span 2', marginTop:18}}>
      <div style={{fontFamily:'var(--font-b)',fontSize:11,letterSpacing:'0.12em',
        textTransform:'uppercase',color:'var(--accent)',marginBottom:10}}>3D Room Tour</div>
      <div ref={ref} style={{width:'100%',height:420,background:'#000',borderRadius:2,position:'relative',overflow:'hidden'}}/>
    </div>
  );
}

function App(){
  const [theme,setTheme]=useState('<?= $theme ?>');
  const [lang,setLang]=useState(()=>localStorage.getItem('rosali_lang')||'id');
  const [active,setActive]=useState(0);
  const { isMobile, isTablet } = useResponsive();
  initRosali(theme,lang);

  useEffect(()=>{ localStorage.setItem('rosali_lang',lang); },[lang]);

  const room = ROOMS[active];
  const en = lang==='en';

  const roomName = RC('room_'+room.key+'_name_'+lang, en?room.nameEn:room.nameId);
  const roomDesc = RC('room_'+room.key+'_desc_'+lang, en?room.descEn:room.descId);
  const ctaMsg = en
    ? `Hello, I'm interested in ${roomName}. Could you please share availability and rates?`
    : `Halo, saya tertarik dengan ${roomName}. Bisakah Anda berbagi ketersediaan dan harga?`;

  return(
    <div className={`theme-${theme}`} style={{minHeight:'100vh'}}>
      <RosaliNav lang={lang} setLang={l=>{setLang(l);localStorage.setItem('rosali_lang',l)}} current="rooms" theme={theme}/>

      <RosaliPageHero
        imgLabel="rooms hero — garden villa exterior / bungalow cluster aerial"
        sup={RC('rooms_hero_sup_'+lang, en?"Accommodation":"Akomodasi")}
        title={RC('rooms_hero_title_'+lang, en?"Rooms &\nVillas":"Kamar &\nVilla")}
        sub={RC('rooms_hero_sub_'+lang, en?"Five unique categories across lush garden clusters. Contact us for availability & rates.":"Lima kategori unik di kluster taman yang rimbun. Hubungi kami untuk ketersediaan & harga.")}
      />

      {/* ROOM BROWSER */}
      <section style={{background:'var(--bg)',padding:'clamp(48px,6vw,88px) clamp(20px,6vw,96px)'}}>
        {/* Tab bar */}
        <div style={{display:'flex',gap:3,marginBottom:44,flexWrap:'wrap'}}>
          {ROOMS.map((r,i)=>(
            <button key={i} onClick={()=>setActive(i)} style={{
              background:i===active?'var(--accent)':'var(--bg2)',
              color:i===active?'var(--bg)':'var(--fg)',
              border:'none',padding:'10px 18px',borderRadius:2,cursor:'pointer',
              fontFamily:'var(--font-b)',fontSize:12,fontWeight:i===active?600:400,
              transition:'all .2s'}}>
              {RC('room_'+r.key+'_name_'+lang, en?r.nameEn:r.nameId)}
            </button>
          ))}
        </div>

        {/* Room detail */}
        <div style={{display:'grid',gridTemplateColumns:isMobile?'1fr':'1fr 1fr',gap:'clamp(32px,5vw,72px)',alignItems:'start'}}>
          {/* Images */}
          <div style={{display:'grid',gridTemplateColumns:isMobile?'1fr':'1fr 1fr',gridTemplateRows:isMobile?'220px 140px':'300px 180px',gap:3}}>
            <RosaliImg label={`${room.key} room — main interior / bedroom`} h="100%"
              style={{height:'100%',gridColumn:'span 2',borderRadius:'2px 2px 0 0'}}/>
            <RosaliImg label={`${room.key} room — bathroom`} h="100%" style={{height:'100%'}}/>
            <RosaliImg label={`${room.key} room — terrace / outdoor area`} h="100%" style={{height:'100%'}}/>
          </div>

          {SPLAT_ENABLED && ROOM_SPLATS[room.key] && (
            <SplatViewer key={room.key} url={ROOM_SPLATS[room.key]}/>
          )}

          {/* Info */}
          <div style={{paddingTop:8}}>
            <span style={{fontSize:10,letterSpacing:'0.14em',textTransform:'uppercase',
              color:'var(--accent)',fontFamily:'var(--font-b)',fontWeight:600}}>{room.tag}</span>
            <h2 style={{fontFamily:'var(--font-d)',fontSize:'clamp(24px,3vw,40px)',
              color:'var(--fg)',margin:'10px 0 16px'}}>{roomName}</h2>
            <p style={{fontFamily:'var(--font-b)',fontSize:15,color:'var(--fg-muted)',
              lineHeight:1.8,marginBottom:28}}>{roomDesc}</p>

            {/* Amenities */}
            <div style={{marginBottom:32}}>
              <div style={{fontFamily:'var(--font-b)',fontSize:11,letterSpacing:'0.12em',
                textTransform:'uppercase',color:'var(--accent)',marginBottom:12}}>
                {RC('rooms_includes_label_'+lang, en?'Room Includes':'Fasilitas Kamar')}
              </div>
              <div style={{display:'flex',flexWrap:'wrap',gap:8}}>
                {room.amenities.map(a=>(
                  <span key={a} style={{background:'var(--bg2)',border:'1px solid var(--border)',
                    padding:'5px 11px',fontSize:12,borderRadius:2,
                    fontFamily:'var(--font-b)',color:'var(--fg)'}}>{a}</span>
                ))}
              </div>
            </div>

            {/* Rate hidden — contact CTA */}
            <div style={{background:'var(--bg2)',border:'1px solid var(--border)',
              borderRadius:4,padding:'20px 22px',marginBottom:24}}>
              <div style={{fontFamily:'var(--font-d)',fontSize:20,color:'var(--fg)',marginBottom:6}}>
                {RC('rooms_rate_title_'+lang, en?'Best Rate — Contact Us':'Harga Terbaik — Hubungi Kami')}
              </div>
              <p style={{fontFamily:'var(--font-b)',fontSize:13,color:'var(--fg-muted)',lineHeight:1.6}}>
                {RC('rooms_rate_body_'+lang, en?'We offer personalized rates based on duration, season & group size. Chat with us for the best deal.':'Kami menawarkan harga personal berdasarkan durasi, musim & ukuran grup. Chat kami untuk penawaran terbaik.')}
              </p>
            </div>

            <RosaliBtn
              text={RC('rooms_cta_btn_'+lang, en?'Ask About This Room':'Tanya Tentang Kamar Ini')}
              href={`https://wa.me/6287851515500?text=${encodeURIComponent(ctaMsg)}`}
              style={{width:'100%',justifyContent:'center',padding:'14px'}}
            />
          </div>
        </div>
      </section>

      {/* ALL ROOMS QUICK GRID */}
      <section style={{background:'var(--bg2)',padding:'clamp(48px,6vw,88px) clamp(20px,6vw,96px)'}}>
        <RosaliLabel>{en?'All Categories':'Semua Kategori'}</RosaliLabel>
        <h2 style={{fontFamily:'var(--font-d)',fontSize:'clamp(24px,3vw,40px)',color:'var(--fg)',marginBottom:36}}>
          {en?'Choose Your Room':'Pilih Kamar Anda'}
        </h2>
        <div style={{display:'grid',gridTemplateColumns:isMobile?'repeat(2,1fr)':isTablet?'repeat(3,1fr)':'repeat(5,1fr)',gap:3}}>
          {ROOMS.map((r,i)=>(
            <div key={i} onClick={()=>{setActive(i);window.scrollTo({top:200,behavior:'smooth'})}}
              style={{background:'var(--card)',borderRadius:2,overflow:'hidden',cursor:'pointer',
                transition:'transform .22s',border:i===active?'2px solid var(--accent)':'2px solid transparent'}}
              onMouseEnter={e=>e.currentTarget.style.transform='translateY(-3px)'}
              onMouseLeave={e=>e.currentTarget.style.transform='none'}
            >
              <RosaliImg label={`${r.key} — thumbnail`} h={120}/>
              <div style={{padding:'12px 10px'}}>
                <div style={{fontSize:9,letterSpacing:'0.12em',textTransform:'uppercase',
                  color:'var(--accent)',fontFamily:'var(--font-b)',fontWeight:600,marginBottom:4}}>{r.tag}</div>
                <div style={{fontFamily:'var(--font-d)',fontSize:14,color:'var(--fg)'}}>{en?r.nameEn:r.nameId}</div>
              </div>
            </div>
          ))}
        </div>
      </section>

      {/* CONTACT STRIP */}
      <section style={{background:'var(--accent)',padding:'48px clamp(20px,6vw,96px)',
        display:'flex',justifyContent:'space-between',alignItems:'center',flexWrap:'wrap',gap:20}}>
        <div>
          <h3 style={{fontFamily:'var(--font-d)',fontSize:'clamp(20px,2.5vw,32px)',color:'var(--bg)',marginBottom:8}}>
            {en?'Not sure which room?':'Belum yakin pilih kamar mana?'}
          </h3>
          <p style={{fontFamily:'var(--font-b)',fontSize:14,color:'var(--bg)',opacity:.8}}>
            {en?'Our team is happy to help you find the perfect fit.':'Tim kami siap membantu Anda menemukan yang sempurna.'}
          </p>
        </div>
        <RosaliBtn text={en?'Chat With Us':'Chat Sekarang'}
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
