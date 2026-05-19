// shared.jsx — loaded by every Rosali page via <script type="text/babel" src="shared.jsx">
// All content (theme, lang, content overrides, layout prefs, color overrides, media slot URLs,
// page visibility) is injected server-side as window.ROSALI by includes/front_init.php.
// This script does NOT touch localStorage for any content data — only for language preference.

const { useState, useEffect, useRef } = React;

/* ── ROSALI data (injected by PHP) ─────────────────────────── */
const R = window.ROSALI || {
  theme:'rosa', lang:'id', images:{}, content:{}, layout:{}, colors:{},
  pageVisibility:{}, pageOrder:[], splatEnabled:false,
};

/* URL theme is preview-only — never written, never persisted. */
function getUrlTheme(){
  try{ return new URLSearchParams(window.location.search).get('theme'); }catch{ return null; }
}
function setUrlTheme(_t){ /* no-op */ }

/* ── Responsive hook ─────────────────────────────────────── */
function useResponsive(){
  const [w,setW]=useState(()=>window.innerWidth);
  useEffect(()=>{
    const fn=()=>setW(window.innerWidth);
    window.addEventListener('resize',fn);
    return()=>window.removeEventListener('resize',fn);
  },[]);
  return { isMobile: w<=768, isTablet: w<=1024, width: w };
}

/* ── Theme CSS ───────────────────────────────────────────── */
const THEME_CSS = `
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
a{text-decoration:none;color:inherit}
button{cursor:pointer}
img{display:block;width:100%;height:100%;object-fit:cover}

.theme-garden{
  --bg:oklch(97% 0.012 115);--bg2:oklch(93% 0.018 125);
  --fg:oklch(18% 0.055 148);--fg-muted:oklch(44% 0.04 138);
  --accent:oklch(37% 0.13 148);--accent-lt:oklch(54% 0.11 142);
  --accent2:oklch(65% 0.12 78);--border:oklch(85% 0.018 125);
  --card:oklch(99% 0.006 110);--nav-bg:oklch(30% 0.07 148);--nav-fg:oklch(96% 0.01 105);
  --font-d:'Playfair Display',Georgia,serif;--font-b:'DM Sans',sans-serif;
}
.theme-boutique{
  --bg:oklch(98.5% 0.005 65);--bg2:oklch(94% 0.010 62);
  --fg:oklch(14% 0.022 58);--fg-muted:oklch(46% 0.018 58);
  --accent:oklch(51% 0.09 162);--accent-lt:oklch(64% 0.07 158);
  --accent2:oklch(57% 0.14 30);--border:oklch(88% 0.012 62);
  --card:oklch(100% 0 0);--nav-bg:oklch(28% 0.025 58);--nav-fg:oklch(96% 0.005 60);
  --font-d:'Cormorant Garamond',Georgia,serif;--font-b:'Outfit',sans-serif;
}
.theme-javanese{
  --bg:oklch(11% 0.024 58);--bg2:oklch(16% 0.030 56);
  --fg:oklch(92% 0.020 82);--fg-muted:oklch(63% 0.018 78);
  --accent:oklch(73% 0.16 76);--accent-lt:oklch(82% 0.12 80);
  --accent2:oklch(62% 0.11 34);--border:oklch(24% 0.028 58);
  --card:oklch(15% 0.024 60);--nav-bg:oklch(18% 0.022 55);--nav-fg:oklch(93% 0.020 82);
  --font-d:'Libre Baskerville',Georgia,serif;--font-b:'Lato',sans-serif;
}
.theme-rosa{
  --bg:oklch(98% 0.008 15);--bg2:oklch(94% 0.016 12);
  --fg:oklch(16% 0.045 20);--fg-muted:oklch(44% 0.035 18);
  --accent:oklch(42% 0.18 22);--accent-lt:oklch(56% 0.16 20);
  --accent2:oklch(38% 0.14 148);--border:oklch(86% 0.022 14);
  --card:oklch(99.5% 0.004 10);--nav-bg:oklch(28% 0.06 20);--nav-fg:oklch(96% 0.008 15);
  --font-d:'Playfair Display',Georgia,serif;--font-b:'DM Sans',sans-serif;
}
.theme-coastal{
  --bg:oklch(97.5% 0.010 220);--bg2:oklch(92% 0.018 215);
  --fg:oklch(14% 0.040 230);--fg-muted:oklch(45% 0.030 222);
  --accent:oklch(44% 0.15 232);--accent-lt:oklch(60% 0.12 228);
  --accent2:oklch(68% 0.14 72);--border:oklch(84% 0.020 218);
  --card:oklch(100% 0 0);--nav-bg:oklch(26% 0.042 232);--nav-fg:oklch(96% 0.010 220);
  --font-d:'Cormorant Garamond',Georgia,serif;--font-b:'Outfit',sans-serif;
}
.theme-batik{
  --bg:oklch(10% 0.035 268);--bg2:oklch(15% 0.042 264);
  --fg:oklch(93% 0.016 76);--fg-muted:oklch(64% 0.020 72);
  --accent:oklch(76% 0.18 68);--accent-lt:oklch(84% 0.14 72);
  --accent2:oklch(60% 0.14 312);--border:oklch(22% 0.040 266);
  --card:oklch(13% 0.036 268);--nav-bg:oklch(16% 0.030 268);--nav-fg:oklch(93% 0.016 76);
  --font-d:'Libre Baskerville',Georgia,serif;--font-b:'Lato',sans-serif;
}
body{font-family:var(--font-b);background:var(--bg);color:var(--fg);transition:background .4s,color .4s;min-height:100vh}
::-webkit-scrollbar{width:5px}
::-webkit-scrollbar-track{background:var(--bg2)}
::-webkit-scrollbar-thumb{background:var(--accent);border-radius:3px}
`;

/* ── Default nav pages (labels per locale) ─────────────── */
const NAV_PAGES = {
  en:[
    {key:'home',   label:'Home',         href:'index.php'},
    {key:'rooms',  label:'Rooms',         href:'rooms.php'},
    {key:'events', label:'Events',        href:'events.php'},
    {key:'cafe',   label:'Rosa De 5 Café',href:'cafe.php'},
    {key:'gallery',label:'Gallery',       href:'gallery.php'},
    {key:'tourism',label:'Tourism',       href:'tourism.php'},
    {key:'contact',label:'Contact',       href:'contact.php'},
  ],
  id:[
    {key:'home',   label:'Beranda',       href:'index.php'},
    {key:'rooms',  label:'Kamar',         href:'rooms.php'},
    {key:'events', label:'Acara',         href:'events.php'},
    {key:'cafe',   label:'Rosa De 5 Café',href:'cafe.php'},
    {key:'gallery',label:'Galeri',        href:'gallery.php'},
    {key:'tourism',label:'Wisata',        href:'tourism.php'},
    {key:'contact',label:'Kontak',        href:'contact.php'},
  ]
};

function navHref(href){ return href; }

/* Build nav by filtering defaults by visibility, then reordering per admin pageOrder if present. */
function getActivePages(lang){
  const defaults = NAV_PAGES[lang] || NAV_PAGES.id;
  const vis = R.pageVisibility || {};
  const visible = defaults.filter(p => vis[p.key] !== false);

  const order = Array.isArray(R.pageOrder) ? R.pageOrder : [];
  if (!order.length) return visible;

  const orderMap = new Map(order.map((o, i) => [o.id, i]));
  return [...visible].sort((a, b) => {
    const ai = orderMap.has(a.key) ? orderMap.get(a.key) : 999;
    const bi = orderMap.has(b.key) ? orderMap.get(b.key) : 999;
    return ai - bi;
  });
}

/* ── Image — DB-driven, no upload UI ─────────────────────── */
/* `label` is the slot identifier. The PHP layer maps slot → media URL via
   the media table (assigned_to = 'slot:<key>'). When no media is assigned,
   render the labelled placeholder pattern. */
function slotKey(label){
  return label.replace(/[^a-z0-9]/gi,'_').slice(0,50).toLowerCase();
}

function RosaliImg({label, h=300, style={}}){
  const key = slotKey(label);
  const src = R.images[key] || null;
  const isVid = src && /\.mp4($|\?)/i.test(src);

  return(
    <div style={{width:'100%',height:h,position:'relative',overflow:'hidden',...style}}>
      {src ? (
        isVid
          ? <video src={src} autoPlay loop muted playsInline style={{width:'100%',height:'100%',objectFit:'cover'}}/>
          : <img src={src} alt={label} style={{width:'100%',height:'100%',objectFit:'cover'}} loading="lazy"/>
      ) : (
        <>
          <svg width="100%" height="100%" style={{position:'absolute',inset:0}} xmlns="http://www.w3.org/2000/svg">
            <defs><pattern id={key} x="0" y="0" width="24" height="24" patternUnits="userSpaceOnUse" patternTransform="rotate(40)">
              <rect width="12" height="24" fill="var(--border)" opacity="0.55"/></pattern></defs>
            <rect width="100%" height="100%" fill={`url(#${key})`}/>
          </svg>
          <div style={{position:'absolute',inset:0,display:'flex',flexDirection:'column',
            alignItems:'center',justifyContent:'center',gap:8}}>
            <span style={{fontFamily:'monospace',fontSize:11,color:'var(--fg-muted)',
              textAlign:'center',padding:'6px 11px',background:'var(--bg)',borderRadius:3,
              opacity:.85,maxWidth:'85%',lineHeight:1.4}}>{label}</span>
          </div>
        </>
      )}
    </div>
  );
}

/* ── Nav ─────────────────────────────────────────────────── */
function RosaliNav({lang,setLang,current,theme}){
  const [sc,setSc]=useState(false);
  const [open,setOpen]=useState(false);
  const { isMobile } = useResponsive();
  useEffect(()=>{
    const fn=()=>setSc(window.scrollY>55);
    window.addEventListener('scroll',fn); return()=>window.removeEventListener('scroll',fn);
  },[]);
  useEffect(()=>{ if(!isMobile) setOpen(false); },[isMobile]);
  const pages=getActivePages(lang);
  const alwaysSolid = !['home'].includes(current);
  const solid = sc||alwaysSolid||open;
  return(
    <>
      <nav style={{position:'fixed',top:0,left:0,right:0,zIndex:200,height:64,
        background:solid?'var(--nav-bg)':'transparent',
        backdropFilter:solid?'blur(14px)':'none',
        transition:'background .35s,box-shadow .35s',
        boxShadow:solid?'0 1px 0 rgba(255,255,255,0.05)':'none',
        padding:'0 clamp(14px,4vw,60px)',display:'flex',alignItems:'center',
        justifyContent:'space-between',gap:12}}>
        <a href={navHref('index.php')} style={{display:'flex',alignItems:'center',gap:10,flexShrink:0}}>
          <img src="logo.png" alt="Rosali Hotel" style={{height:36,width:'auto',display:'block'}}/>
          <span style={{fontFamily:'var(--font-d)',fontSize:17,fontWeight:600,
            color:'var(--nav-fg)',letterSpacing:'0.08em',whiteSpace:'nowrap'}}>{RC('hotel_name','Rosali Hotel')}</span>
        </a>

        {!isMobile&&(
          <div style={{display:'flex',alignItems:'center',gap:'clamp(10px,1.6vw,22px)',flexWrap:'wrap'}}>
            {pages.map(p=>(
              <a key={p.key} href={navHref(p.href)} style={{fontFamily:'var(--font-b)',fontSize:11,fontWeight:500,
                letterSpacing:'0.09em',textTransform:'uppercase',color:'var(--nav-fg)',
                opacity:current===p.key?1:.65,
                borderBottom:current===p.key?'1px solid var(--accent)':'1px solid transparent',
                paddingBottom:2,transition:'opacity .2s'}}
                onMouseEnter={e=>e.currentTarget.style.opacity='1'}
                onMouseLeave={e=>e.currentTarget.style.opacity=current===p.key?'1':'.65'}
              >{p.label}</a>
            ))}
          </div>
        )}

        <div style={{display:'flex',alignItems:'center',gap:9,flexShrink:0}}>
          <button onClick={()=>setLang(lang==='en'?'id':'en')} style={{
            background:'none',border:'1px solid var(--nav-fg)',color:'var(--nav-fg)',
            borderRadius:2,padding:'4px 10px',fontSize:10,fontWeight:500,
            letterSpacing:'0.1em',fontFamily:'var(--font-b)',opacity:.7,cursor:'pointer'}}>
            {lang==='en'?'🇮🇩 ID':'🇬🇧 EN'}</button>
          {!isMobile&&<RosaliBtn text={lang==='en'?'Book Now':'Pesan'} style={{padding:'7px 14px',fontSize:10}}/>}
          {isMobile&&(
            <button onClick={()=>setOpen(o=>!o)} style={{
              background:'none',border:'none',color:'var(--nav-fg)',cursor:'pointer',
              padding:'6px',display:'flex',flexDirection:'column',gap:5,justifyContent:'center'}}>
              <span style={{display:'block',width:22,height:2,background:'var(--nav-fg)',
                borderRadius:2,transition:'transform .25s, opacity .25s',
                transform:open?'translateY(7px) rotate(45deg)':'none'}}/>
              <span style={{display:'block',width:22,height:2,background:'var(--nav-fg)',
                borderRadius:2,transition:'opacity .25s',
                opacity:open?0:1}}/>
              <span style={{display:'block',width:22,height:2,background:'var(--nav-fg)',
                borderRadius:2,transition:'transform .25s, opacity .25s',
                transform:open?'translateY(-7px) rotate(-45deg)':'none'}}/>
            </button>
          )}
        </div>
      </nav>

      {isMobile&&(
        <div style={{
          position:'fixed',top:64,left:0,right:0,zIndex:199,
          background:'var(--nav-bg)',
          maxHeight:open?'100vh':'0',overflow:'hidden',
          transition:'max-height .35s cubic-bezier(.4,0,.2,1)',
          boxShadow:open?'0 8px 32px rgba(0,0,0,0.3)':'none',
        }}>
          <div style={{padding:'16px 0 24px'}}>
            {pages.map(p=>(
              <a key={p.key} href={navHref(p.href)} onClick={()=>setOpen(false)}
                style={{display:'block',fontFamily:'var(--font-b)',fontSize:14,fontWeight:500,
                  letterSpacing:'0.08em',textTransform:'uppercase',color:'var(--nav-fg)',
                  opacity:current===p.key?1:.7,padding:'14px clamp(14px,4vw,60px)',
                  borderLeft:current===p.key?'3px solid var(--accent)':'3px solid transparent',
                  transition:'opacity .2s,background .2s'}}
                onMouseEnter={e=>e.currentTarget.style.background='rgba(255,255,255,0.06)'}
                onMouseLeave={e=>e.currentTarget.style.background='transparent'}
              >{p.label}</a>
            ))}
            <div style={{padding:'16px clamp(14px,4vw,60px) 0'}}>
              <RosaliBtn text={lang==='en'?'Book Now':'Pesan'} style={{width:'100%',justifyContent:'center',padding:'13px'}}/>
            </div>
          </div>
        </div>
      )}
    </>
  );
}

/* ── Footer ──────────────────────────────────────────────── */
function RosaliFooter({lang}){
  const pages=getActivePages(lang);
  return(
    <footer style={{background:'var(--nav-bg)',color:'var(--nav-fg)',
      padding:'26px clamp(14px,4vw,60px)',
      display:'flex',justifyContent:'space-between',alignItems:'center',flexWrap:'wrap',gap:14}}>
      <div style={{display:'flex',alignItems:'center',gap:10}}>
        <img src="logo.png" alt="Rosali Hotel" style={{height:48,width:'auto',display:'block',opacity:.9}}/>
        <div style={{fontFamily:'var(--font-b)',fontSize:10,opacity:.35,marginTop:3}}>
          © 2025 {RC('hotel_name','Rosali Hotel')} & Restaurant
        </div>
      </div>
      <div style={{display:'flex',gap:16,flexWrap:'wrap'}}>
        {pages.map(p=>(
          <a key={p.key} href={p.href} style={{fontFamily:'var(--font-b)',fontSize:10,
            letterSpacing:'0.1em',textTransform:'uppercase',opacity:.4,transition:'opacity .2s'}}
            onMouseEnter={e=>e.currentTarget.style.opacity='1'}
            onMouseLeave={e=>e.currentTarget.style.opacity='.4'}
          >{p.label}</a>
        ))}
      </div>
      <a href="admin/index.php" style={{fontFamily:'var(--font-b)',fontSize:9,opacity:.15,
        letterSpacing:'0.1em',textTransform:'uppercase',transition:'opacity .2s'}}
        onMouseEnter={e=>e.currentTarget.style.opacity='.7'}
        onMouseLeave={e=>e.currentTarget.style.opacity='.15'}
      >⚙ Admin</a>
    </footer>
  );
}

/* ── Button ──────────────────────────────────────────────── */
function waHref(){
  const num = RC('wa_number','6287851515500').replace(/\D/g,'') || '6287851515500';
  return `https://wa.me/${num}`;
}
function RosaliBtn({text,href,icon=true,style={}}){
  const target = href || waHref();
  return(
    <a href={target} target={target.startsWith('http')?'_blank':undefined} rel="noopener noreferrer"
      style={{display:'inline-flex',alignItems:'center',gap:8,background:'var(--accent)',
        color:'var(--bg)',padding:'12px 22px',borderRadius:2,fontFamily:'var(--font-b)',
        fontWeight:500,fontSize:13,letterSpacing:'0.04em',
        transition:'opacity .2s,transform .2s',cursor:'pointer',...style}}
      onMouseEnter={e=>{e.currentTarget.style.opacity='.82';e.currentTarget.style.transform='translateY(-1px)'}}
      onMouseLeave={e=>{e.currentTarget.style.opacity='1';e.currentTarget.style.transform='none'}}
    >
      {icon&&<svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>}
      {text}
    </a>
  );
}

/* ── Section Label ───────────────────────────────────────── */
function RosaliLabel({children}){
  return(
    <div style={{display:'inline-flex',alignItems:'center',gap:8,marginBottom:12}}>
      <span style={{width:20,height:1,background:'var(--accent)',display:'block'}}/>
      <span style={{fontFamily:'var(--font-b)',fontSize:11,fontWeight:500,
        letterSpacing:'0.18em',textTransform:'uppercase',color:'var(--accent)'}}>{children}</span>
    </div>
  );
}

/* ── Page Hero (sub-pages) ───────────────────────────────── */
function RosaliPageHero({imgLabel, sup, title, sub}){
  return(
    <section style={{position:'relative',height:'55vh',minHeight:380,overflow:'hidden',
      display:'flex',alignItems:'flex-end',padding:'0 clamp(14px,6vw,96px) clamp(36px,5vw,64px)'}}>
      <RosaliImg label={imgLabel} h="100%"
        style={{position:'absolute',inset:0,height:'100%'}}/>
      <div style={{position:'absolute',inset:0,
        background:'linear-gradient(to top,oklch(9% 0.04 150/0.88) 0%,oklch(9% 0.04 150/0.2) 60%,transparent 100%)'}}/>
      <div style={{position:'relative',zIndex:2,maxWidth:700}}>
        {sup&&<p style={{fontFamily:'var(--font-b)',fontSize:11,letterSpacing:'0.22em',
          textTransform:'uppercase',color:'oklch(80% 0.1 80)',marginBottom:14}}>{sup}</p>}
        <h1 style={{fontFamily:'var(--font-d)',fontSize:'clamp(36px,6vw,72px)',
          lineHeight:1.05,color:'oklch(97% 0.01 100)',fontWeight:700,marginBottom:14,whiteSpace:'pre-line'}}>{title}</h1>
        {sub&&<p style={{fontFamily:'var(--font-b)',fontSize:15,color:'oklch(90% 0.015 100)',
          lineHeight:1.7,opacity:.9}}>{sub}</p>}
      </div>
    </section>
  );
}

/* ── WA FAB ──────────────────────────────────────────────── */
function RosaliWaFab(){
  return(
    <a href={waHref()} target="_blank" rel="noopener noreferrer"
      style={{position:'fixed',bottom:26,right:26,zIndex:200,background:'#25D366',
        color:'#fff',borderRadius:'50%',width:52,height:52,
        display:'flex',alignItems:'center',justifyContent:'center',
        boxShadow:'0 4px 18px rgba(37,211,102,0.42)',transition:'transform .2s,box-shadow .2s'}}
      onMouseEnter={e=>{e.currentTarget.style.transform='scale(1.1)';e.currentTarget.style.boxShadow='0 6px 26px rgba(37,211,102,0.55)'}}
      onMouseLeave={e=>{e.currentTarget.style.transform='none';e.currentTarget.style.boxShadow='0 4px 18px rgba(37,211,102,0.42)'}}
    >
      <svg width="24" height="24" viewBox="0 0 24 24" fill="white"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
    </a>
  );
}

/* ── Init ────────────────────────────────────────────────── */
/* Theme & lang are already authoritative server-side. */
function initRosali(_defaultTheme, _defaultLang){
  if(!document.getElementById('rosali-theme-css')){
    const s = document.createElement('style');
    s.id = 'rosali-theme-css';
    s.textContent = THEME_CSS;
    document.head.appendChild(s);
  }
  applyColorOverrides();
  return { theme: R.theme, lang: R.lang };
}

/* ── Content override helper — reads from window.ROSALI.content ───── */
function RC(key, fallback=''){
  const v = R.content?.[key];
  return (v !== undefined && v !== null && v !== '') ? v : fallback;
}

/* ── Layout preference helper — reads from window.ROSALI.layout ───── */
function getLayoutPref(key, fallback=''){
  const v = R.layout?.[key];
  return v || fallback;
}

/* ── Color overrides — read from window.ROSALI.colors and inject CSS */
function applyColorOverrides(){
  let el=document.getElementById('rosali-color-overrides');
  if(!el){ el=document.createElement('style'); el.id='rosali-color-overrides'; document.head.appendChild(el); }
  try{
    const all = R.colors || {};
    let css='';
    Object.entries(all).forEach(([th,vars])=>{
      const entries=Object.entries(vars||{}).filter(([,v])=>v);
      if(entries.length) css+=`.theme-${th}{${entries.map(([k,v])=>`${k}:${v}`).join(';')}}`;
    });
    el.textContent=css;
  }catch{}
}

/* ── Export ──────────────────────────────────────────────── */
Object.assign(window, {
  RosaliImg, RosaliNav, RosaliFooter, RosaliBtn, RosaliLabel,
  RosaliPageHero, RosaliWaFab, NAV_PAGES, initRosali, useResponsive,
  getUrlTheme, setUrlTheme, RC, getLayoutPref, applyColorOverrides,
  slotKey,
});
