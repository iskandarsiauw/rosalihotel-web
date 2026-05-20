<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/tracker.php';
$theme = getActiveTheme();
$lang  = getActiveLang();

/* Categories shown as filter tabs map admin media categories to display labels. */
$catLabels = [
  'en' => ['all'=>'All',   'general'=>'Hotel & Garden', 'rooms'=>'Rooms & Villas', 'cafe'=>'Rosa De 5 Café', 'events'=>'Events & Weddings', 'gallery'=>'Gallery'],
  'id' => ['all'=>'Semua', 'general'=>'Hotel & Taman',  'rooms'=>'Kamar & Villa',  'cafe'=>'Rosa De 5 Café', 'events'=>'Acara & Pernikahan','gallery'=>'Galeri'],
];

/* Pull every published image from the media table. */
global $pdo;
$photos = [];
try {
  $stmt = $pdo->query(
    "SELECT id, filename, original_name, category, file_type FROM media
     WHERE file_type = 'image' AND is_published = 1
     ORDER BY id DESC"
  );
  foreach ($stmt->fetchAll() as $r) {
    $r['url'] = mediaUrl($r['file_type'], $r['filename']);
    $photos[] = $r;
  }
} catch (PDOException) {}

/* Determine which categories actually have content so we don't render empty tabs. */
$activeCats = ['all' => true];
foreach ($photos as $p) $activeCats[$p['category'] ?? 'general'] = true;
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Galeri — Rosali Hotel Situbondo</title>
<meta name="description" content="Galeri foto Rosali Hotel: hotel, taman, kamar, café, dan acara di Situbondo."/>
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
const { RosaliNav, RosaliFooter, RosaliLabel, RosaliWaFab, initRosali, useResponsive } = window;

const PHOTOS    = <?= json_encode($photos) ?>;
const CAT_LABELS = <?= json_encode($catLabels) ?>;
const ACTIVE_CATS = <?= json_encode(array_keys($activeCats)) ?>;

function App(){
  const [theme]=useState('<?= $theme ?>');
  const [lang,setLang]=useState(()=>localStorage.getItem('rosali_lang')||'<?= $lang ?>');
  const [active,setActive]=useState('all');
  const { isMobile } = useResponsive();
  initRosali();
  useEffect(()=>{ localStorage.setItem('rosali_lang',lang); },[lang]);
  const en=lang==='en';

  const filtered = active==='all' ? PHOTOS : PHOTOS.filter(p => (p.category||'general') === active);

  return(
    <div className={`theme-${theme}`} style={{minHeight:'100vh'}}>
      <RosaliNav lang={lang} setLang={l=>{setLang(l);localStorage.setItem('rosali_lang',l)}} current="gallery" theme={theme}/>

      <div style={{paddingTop:100,paddingBottom:40,textAlign:'center',
        padding:'100px clamp(20px,6vw,96px) 48px',background:'var(--bg)'}}>
        <RosaliLabel>{en?'Gallery':'Galeri'}</RosaliLabel>
        <h1 style={{fontFamily:'var(--font-d)',fontSize:'clamp(32px,5vw,64px)',color:'var(--fg)',marginBottom:12}}>
          {en?'Glimpses of Rosali':'Sekilas Rosali'}
        </h1>
        <p style={{fontFamily:'var(--font-b)',fontSize:15,color:'var(--fg-muted)',marginBottom:36}}>
          {en?'Browse our collection of photos.':'Jelajahi koleksi foto kami.'}
        </p>
        {ACTIVE_CATS.length > 1 && (
          <div style={{display:'flex',gap:4,justifyContent:'center',flexWrap:'wrap'}}>
            {ACTIVE_CATS.map(c=>(
              <button key={c} onClick={()=>setActive(c)} style={{
                background:active===c?'var(--accent)':'var(--bg2)',
                color:active===c?'var(--bg)':'var(--fg)',
                border:'none',padding:'9px 18px',borderRadius:2,cursor:'pointer',
                fontFamily:'var(--font-b)',fontSize:12,fontWeight:active===c?600:400,transition:'all .2s'}}>
                {CAT_LABELS[lang][c] || c}
              </button>
            ))}
          </div>
        )}
      </div>

      <section style={{background:'var(--bg)',padding:'0 clamp(20px,6vw,96px) clamp(48px,6vw,88px)'}}>
        {filtered.length === 0 ? (
          <div style={{textAlign:'center',padding:'60px 20px',color:'var(--fg-muted)',
            fontFamily:'var(--font-b)',fontSize:14,background:'var(--bg2)',borderRadius:4}}>
            {en
              ? 'Photos are coming soon — please check back shortly.'
              : 'Foto akan segera tersedia — silakan periksa lagi nanti.'}
          </div>
        ) : (
          <div style={{
            display:'grid',
            gridTemplateColumns:isMobile?'1fr':'repeat(3,1fr)',
            gridAutoRows:'220px',
            gap:4,
          }}>
            {filtered.map((p,i)=>(
              <div key={p.id}
                style={{
                  gridColumn: (!isMobile && (i % 5 === 0)) ? 'span 2' : 'span 1',
                  gridRow:'span 1',
                  overflow:'hidden',borderRadius:2,
                  transition:'transform .25s, box-shadow .25s',
                }}
                onMouseEnter={e=>{e.currentTarget.style.transform='scale(1.01)';e.currentTarget.style.boxShadow='0 8px 32px rgba(0,0,0,0.18)'}}
                onMouseLeave={e=>{e.currentTarget.style.transform='none';e.currentTarget.style.boxShadow='none'}}
              >
                <img src={p.url} alt={p.original_name||''} loading="lazy"
                  style={{width:'100%',height:'100%',objectFit:'cover'}}/>
              </div>
            ))}
          </div>
        )}
      </section>

      <RosaliFooter lang={lang}/>
      <RosaliWaFab/>
    </div>
  );
}
ReactDOM.createRoot(document.getElementById('root')).render(<App/>);
</script>
</body></html>
