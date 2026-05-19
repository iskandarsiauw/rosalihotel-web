<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Galeri — Rosali Hotel Situbondo</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300&family=Outfit:wght@300;400;500;600&family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Lato:wght@300;400;700&display=swap" rel="stylesheet"/>
<script src="https://unpkg.com/react@18.3.1/umd/react.development.js" integrity="sha384-hD6/rw4ppMLGNu3tX5cjIb+uRZ7UkRJ6BPkLpg4hAu/6onKUg4lLsHAs9EBPT82L" crossorigin="anonymous"></script>
<script src="https://unpkg.com/react-dom@18.3.1/umd/react-dom.development.js" integrity="sha384-u6aeetuaXnQ38mYT8rp6sbXaQe3NL9t+IBXmnYxwkUI2Hw4bsp2Wvmx4yRQF1uAm" crossorigin="anonymous"></script>
<script src="https://unpkg.com/@babel/standalone@7.29.0/babel.min.js" integrity="sha384-m08KidiNqLdpJqLq95G/LEi8Qvjl/xUYll3QILypMoQ65QorJ9Lvtp2RXYGBFj1y" crossorigin="anonymous"></script>
</head>
<body><div id="root"></div>
<script type="text/babel" src="shared.jsx"></script>
<script type="text/babel">
const { useState, useEffect } = React;
const { RosaliImg, RosaliNav, RosaliFooter, RosaliLabel, RosaliWaFab, initRosali, useResponsive, getUrlTheme, setUrlTheme } = window;

const CATS = {
  en:['All','Hotel & Garden','Rooms & Villas','Rosa De 5 Café','Events & Weddings'],
  id:['Semua','Hotel & Taman','Kamar & Villa','Rosa De 5 Café','Acara & Pernikahan']
};

const PHOTOS = [
  {cat:'Hotel & Garden', label:'hotel — aerial garden view overhead', wide:true},
  {cat:'Hotel & Garden', label:'hotel — main entrance & lobby'},
  {cat:'Hotel & Garden', label:'hotel — tropical garden pathway'},
  {cat:'Rooms & Villas', label:'room — wooden house exterior & terrace', wide:true},
  {cat:'Hotel & Garden', label:'hotel — garden gazebo & flowers'},
  {cat:'Rooms & Villas', label:'room — orientals interior bedroom'},
  {cat:'Rooms & Villas', label:'room — VIP suite living area'},
  {cat:'Rosa De 5 Café', label:'cafe — barista pour over coffee', wide:true},
  {cat:'Rooms & Villas', label:'room — superior room view'},
  {cat:'Rosa De 5 Café', label:'cafe — interior seating area'},
  {cat:'Rosa De 5 Café', label:'cafe — garden outdoor tables'},
  {cat:'Events & Weddings', label:'events — dream garden wedding ceremony', wide:true},
  {cat:'Hotel & Garden', label:'hotel — garden sunset golden hour'},
  {cat:'Events & Weddings', label:'events — jasmine meeting room setup'},
  {cat:'Events & Weddings', label:'events — wedding reception table'},
  {cat:'Rosa De 5 Café', label:'cafe — specialty coffee closeup'},
  {cat:'Hotel & Garden', label:'hotel — musholla in garden'},
  {cat:'Rooms & Villas', label:'room — standard room neat & clean'},
];

function App(){
  const [theme]=useState(()=>getUrlTheme()||localStorage.getItem('rosali_theme')||'rosa');
  const [lang,setLang]=useState(()=>localStorage.getItem('rosali_lang')||'id');
  const [active,setActive]=useState(0);
  const [lightbox,setLightbox]=useState(null);
  const { isMobile } = useResponsive();
  initRosali(theme,lang);
  useEffect(()=>{ setUrlTheme(theme); },[theme]);
  const en=lang==='en';
  const cats=CATS[lang];

  const filtered = active===0 ? PHOTOS : PHOTOS.filter(p=>{
    const enCats=CATS.en; const idCats=CATS.id;
    return p.cat===enCats[active]||p.cat===idCats[active];
  });

  return(
    <div className={`theme-${theme}`} style={{minHeight:'100vh'}}>
      <RosaliNav lang={lang} setLang={l=>{setLang(l);localStorage.setItem('rosali_lang',l)}} current="gallery" theme={theme}/>

      {/* Header */}
      <div style={{paddingTop:100,paddingBottom:40,textAlign:'center',
        padding:'100px clamp(20px,6vw,96px) 48px',background:'var(--bg)'}}>
        <RosaliLabel>{en?'Gallery':'Galeri'}</RosaliLabel>
        <h1 style={{fontFamily:'var(--font-d)',fontSize:'clamp(32px,5vw,64px)',color:'var(--fg)',marginBottom:12}}>
          {en?'Glimpses of Rosali':'Sekilas Rosali'}
        </h1>
        <p style={{fontFamily:'var(--font-b)',fontSize:15,color:'var(--fg-muted)',marginBottom:36}}>
          {en?'Click any photo to upload your own — or browse our collection below.'
            :'Klik foto manapun untuk upload milik Anda — atau jelajahi koleksi kami di bawah.'}
        </p>
        {/* Filter tabs */}
        <div style={{display:'flex',gap:4,justifyContent:'center',flexWrap:'wrap'}}>
          {cats.map((c,i)=>(
            <button key={i} onClick={()=>setActive(i)} style={{
              background:active===i?'var(--accent)':'var(--bg2)',
              color:active===i?'var(--bg)':'var(--fg)',
              border:'none',padding:'9px 18px',borderRadius:2,cursor:'pointer',
              fontFamily:'var(--font-b)',fontSize:12,fontWeight:active===i?600:400,transition:'all .2s'}}>
              {c}
            </button>
          ))}
        </div>
      </div>

      {/* MASONRY GRID */}
      <section style={{background:'var(--bg)',padding:'0 clamp(20px,6vw,96px) clamp(48px,6vw,88px)'}}>
        <div style={{
          display:'grid',
          gridTemplateColumns:isMobile?'1fr':'repeat(3,1fr)',
          gridAutoRows:'220px',
          gap:4,
        }}>
          {filtered.map((p,i)=>(
            <div key={`${active}-${i}`}
              style={{
                gridColumn:(!isMobile && p.wide)?'span 2':'span 1',
                gridRow:'span 1',
                overflow:'hidden',borderRadius:2,cursor:'pointer',
                transition:'transform .25s, box-shadow .25s',
              }}
              onMouseEnter={e=>{e.currentTarget.style.transform='scale(1.01)';e.currentTarget.style.boxShadow='0 8px 32px rgba(0,0,0,0.18)'}}
              onMouseLeave={e=>{e.currentTarget.style.transform='none';e.currentTarget.style.boxShadow='none'}}
            >
              <RosaliImg label={p.label} h="100%" style={{height:'100%'}}/>
            </div>
          ))}
        </div>
      </section>

      {/* Upload tip banner */}
      <div style={{background:'var(--bg2)',padding:'20px clamp(20px,6vw,96px)',
        display:'flex',alignItems:'center',gap:12,
        fontFamily:'var(--font-b)',fontSize:13,color:'var(--fg-muted)'}}>
        <span style={{fontSize:20}}>💡</span>
        <span>{en?'Tip: Click any placeholder above to upload your real hotel photos. They\'ll be saved in this browser.'
          :'Tip: Klik placeholder di atas untuk upload foto hotel Anda. Foto akan tersimpan di browser ini.'}</span>
      </div>

      <RosaliFooter lang={lang}/>
      <RosaliWaFab/>
    </div>
  );
}
ReactDOM.createRoot(document.getElementById('root')).render(<App/>);
</script>
</body></html>
