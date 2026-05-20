<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/tracker.php';
$theme = getActiveTheme();
$lang  = getActiveLang();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Wisata — Rosali Hotel Situbondo</title>
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
const { RosaliImg, RosaliNav, RosaliFooter, RosaliBtn, RosaliLabel, RosaliPageHero, RosaliWaFab, initRosali, useResponsive, getUrlTheme, setUrlTheme, RC } = window;

const SPOTS = {
  en:[
    {name:'Pasir Putih Beach',tag:'Beach',dist:'~15 min drive',
      desc:'The most popular beach near Situbondo. White sand, calm turquoise waters, and fresh seafood warungs. Great for sunrise walks.',
      tips:['Best time: early morning','Bring cash for food stalls','Snorkeling gear available for rent']},
    {name:'Ijen Crater',tag:'Nature',dist:'~2.5 hr drive',
      desc:'World-famous for its electric-blue fire phenomenon visible only at night. A UNESCO-recognized sulphuric crater lake surrounded by lush jungle.',
      tips:['Start hike at 1:00 AM for blue fire','Bring gas mask & warm jacket','Guided tours bookable via hotel']},
    {name:'Baluran National Park',tag:'Wildlife',dist:'~1 hr drive',
      desc:"Called Indonesia's Africa, Baluran features savanna grasslands, wildlife (deer, buffalo, peacocks), and pristine coastline all in one place.",
      tips:['Best at dawn or dusk','Entrance fee applies','4WD recommended for inner trails']},
    {name:'Colonial Heritage Sites',tag:'Culture',dist:'10–30 min',
      desc:'Situbondo is home to historic Dutch colonial sugar mills, classic tram tracks, and heritage buildings — a unique tropical-European landscape.',
      tips:['Besuki Sugar Mill is the highlight','Walking-friendly in town center','Great for photography']},
    {name:'Taman Nasional Meru Betiri',tag:'Nature',dist:'~3 hr drive',
      desc:'A remote rainforest national park home to rare green turtles, leopards, and pristine jungle beaches. One of Java\'s most biodiverse areas.',
      tips:['Requires permits & guide','Best combined with overnight stay','June–August turtle nesting season']},
    {name:'Local Markets & Batik',tag:'Culture',dist:'5 min walk',
      desc:'Situbondo has a vibrant traditional market scene and local batik workshops. Pick up unique hand-dyed fabrics and local delicacies.',
      tips:['Pasar Mimbaan — best for fresh produce','Hotel gift shop sells local batik','Early morning for best market experience']},
  ],
  id:[
    {name:'Pantai Pasir Putih',tag:'Pantai',dist:'~15 mnt berkendara',
      desc:'Pantai paling populer dekat Situbondo. Pasir putih, air tosca tenang, dan warung seafood segar. Cocok untuk jalan pagi hari.',
      tips:['Waktu terbaik: pagi hari','Bawa uang tunai untuk warung','Alat snorkeling tersedia untuk disewa']},
    {name:'Kawah Ijen',tag:'Alam',dist:'~2,5 jam berkendara',
      desc:'Terkenal di dunia karena fenomena api biru listrik yang hanya terlihat di malam hari. Danau kawah belerang yang diakui UNESCO, dikelilingi hutan lebat.',
      tips:['Mulai pendakian pukul 01:00 untuk api biru','Bawa masker gas & jaket hangat','Tur berpemandu bisa dipesan lewat hotel']},
    {name:'Taman Nasional Baluran',tag:'Satwa',dist:'~1 jam berkendara',
      desc:'"Afrika-nya Indonesia", Baluran memiliki padang sabana, satwa liar (rusa, kerbau, merak), dan pantai perawan — semuanya dalam satu tempat.',
      tips:['Terbaik saat fajar atau senja','Tiket masuk berlaku','Kendaraan 4WD disarankan untuk jalur dalam']},
    {name:'Warisan Kolonial',tag:'Budaya',dist:'10–30 mnt',
      desc:'Situbondo menyimpan pabrik gula kolonial Belanda, jalur trem bersejarah, dan bangunan warisan — lanskap tropis-Eropa yang unik.',
      tips:['Pabrik Gula Besuki adalah yang paling menarik','Cocok untuk jalan kaki di pusat kota','Bagus untuk fotografi']},
    {name:'TN Meru Betiri',tag:'Alam',dist:'~3 jam berkendara',
      desc:'Taman nasional hutan hujan terpencil yang menjadi rumah bagi penyu hijau langka, macan tutul, dan pantai hutan perawan. Salah satu area paling beragam hayati di Jawa.',
      tips:['Memerlukan izin & pemandu','Terbaik digabung dengan menginap','Juni–Agustus musim bertelur penyu']},
    {name:'Pasar & Batik Lokal',tag:'Budaya',dist:'5 mnt jalan kaki',
      desc:'Situbondo memiliki pasar tradisional yang ramai dan workshop batik lokal. Temukan kain celup tangan unik dan oleh-oleh khas.',
      tips:['Pasar Mimbaan — terbaik untuk produk segar','Toko hadiah hotel menjual batik lokal','Pagi hari untuk pengalaman pasar terbaik']},
  ]
};

const TAG_COLORS = {
  Beach:'oklch(60% 0.12 220)',Pantai:'oklch(60% 0.12 220)',
  Nature:'oklch(45% 0.12 148)',Alam:'oklch(45% 0.12 148)',
  Wildlife:'oklch(58% 0.12 75)',Satwa:'oklch(58% 0.12 75)',
  Culture:'oklch(55% 0.12 30)',Budaya:'oklch(55% 0.12 30)',
};

function App(){
  const [theme]=useState('<?= $theme ?>');
  const [lang,setLang]=useState(()=>localStorage.getItem('rosali_lang')||'id');
  const [open,setOpen]=useState(null);
  const { isMobile } = useResponsive();
  initRosali(theme,lang);
  useEffect(()=>{ localStorage.setItem('rosali_lang',lang); },[lang]);
  const en=lang==='en';
  const SPOT_KEYS = ['pasir','ijen','baluran','colonial','meru','market'];
  const spots = SPOTS[lang].map((s,i)=>({
    ...s,
    name: RC('spot_'+SPOT_KEYS[i]+'_name_'+lang, s.name),
    tag:  RC('spot_'+SPOT_KEYS[i]+'_tag_'+lang,  s.tag),
    dist: RC('spot_'+SPOT_KEYS[i]+'_dist_'+lang, s.dist),
    desc: RC('spot_'+SPOT_KEYS[i]+'_desc_'+lang, s.desc),
  }));

  return(
    <div className={`theme-${theme}`} style={{minHeight:'100vh'}}>
      <RosaliNav lang={lang} setLang={l=>{setLang(l);localStorage.setItem('rosali_lang',l)}} current="tourism" theme={theme}/>

      <RosaliPageHero
        imgLabel="tourism hero — Ijen blue fire / Baluran savanna / Pasir Putih beach"
        sup={RC('tourism_hero_sup_'+lang, en?'East Java Tourism':'Wisata Jawa Timur')}
        title={RC('tourism_hero_title_'+lang, en?'Explore\nEast Java':'Jelajahi\nJawa Timur')}
        sub={RC('tourism_hero_sub_'+lang, en?'From Rosali Hotel, East Java\'s finest natural and cultural attractions are at your doorstep.'
          :'Dari Rosali Hotel, destinasi alam dan budaya terbaik Jawa Timur ada di depan pintu Anda.')}
      />

      {/* DISTANCE STRIP */}
      <div style={{background:'var(--accent)',padding:'18px clamp(20px,6vw,96px)',
        display:'flex',gap:32,flexWrap:'wrap',alignItems:'center'}}>
        {(en?['15 min → Pasir Putih Beach','1 hr → Baluran National Park','2.5 hr → Ijen Crater','5 min → Colonial Heritage']
          :['15 mnt → Pantai Pasir Putih','1 jam → TN Baluran','2,5 jam → Kawah Ijen','5 mnt → Warisan Kolonial']).map(t=>(
          <span key={t} style={{fontFamily:'var(--font-b)',fontSize:12,color:'var(--bg)',
            letterSpacing:'0.06em',fontWeight:500}}>{t}</span>
        ))}
      </div>

      {/* SPOT GRID */}
      <section style={{background:'var(--bg)',padding:'clamp(48px,6vw,88px) clamp(20px,6vw,96px)'}}>
        <RosaliLabel>{en?'Attractions':'Destinasi'}</RosaliLabel>
        <h2 style={{fontFamily:'var(--font-d)',fontSize:'clamp(24px,3vw,42px)',color:'var(--fg)',marginBottom:40}}>
          {en?'What to See & Do':'Yang Bisa Dilihat & Dilakukan'}
        </h2>
        <div style={{display:'grid',gridTemplateColumns:isMobile?'1fr':'repeat(2,1fr)',gap:3}}>
          {spots.map((s,i)=>(
            <div key={i} style={{background:'var(--bg2)',borderRadius:2,overflow:'hidden',
              cursor:'pointer',transition:'transform .22s'}}
              onClick={()=>setOpen(open===i?null:i)}
              onMouseEnter={e=>e.currentTarget.style.transform='translateY(-3px)'}
              onMouseLeave={e=>e.currentTarget.style.transform='none'}
            >
              <RosaliImg label={`tourism — ${s.name.toLowerCase()}`} h={200}/>
              <div style={{padding:'20px 18px'}}>
                <div style={{display:'flex',justifyContent:'space-between',alignItems:'flex-start',marginBottom:8}}>
                  <span style={{fontSize:9,letterSpacing:'0.14em',textTransform:'uppercase',
                    color:TAG_COLORS[s.tag]||'var(--accent)',fontFamily:'var(--font-b)',fontWeight:700}}>{s.tag}</span>
                  <span style={{fontFamily:'var(--font-b)',fontSize:11,color:'var(--fg-muted)'}}>{s.dist}</span>
                </div>
                <h4 style={{fontFamily:'var(--font-d)',fontSize:20,color:'var(--fg)',marginBottom:8}}>{s.name}</h4>
                <p style={{fontFamily:'var(--font-b)',fontSize:13,color:'var(--fg-muted)',lineHeight:1.65}}>{s.desc}</p>
                {open===i&&(
                  <div style={{marginTop:16,paddingTop:16,borderTop:'1px solid var(--border)'}}>
                    <div style={{fontFamily:'var(--font-b)',fontSize:11,letterSpacing:'0.12em',
                      textTransform:'uppercase',color:'var(--accent)',marginBottom:10}}>
                      {en?'Tips':'Tips'}
                    </div>
                    {s.tips.map((t,j)=>(
                      <div key={j} style={{display:'flex',gap:8,marginBottom:6,
                        fontFamily:'var(--font-b)',fontSize:13,color:'var(--fg)'}}>
                        <span style={{color:'var(--accent)',flexShrink:0}}>→</span>{t}
                      </div>
                    ))}
                  </div>
                )}
                <div style={{marginTop:12,fontFamily:'var(--font-b)',fontSize:11,
                  color:'var(--accent)',letterSpacing:'0.06em'}}>
                  {open===i?(en?'▲ Less':'▲ Tutup'):(en?'▼ Travel tips':'▼ Tips perjalanan')}
                </div>
              </div>
            </div>
          ))}
        </div>
      </section>

      {/* ARRANGE TRIP STRIP */}
      <section style={{background:'var(--accent)',padding:'52px clamp(20px,6vw,96px)',
        display:'flex',justifyContent:'space-between',alignItems:'center',flexWrap:'wrap',gap:20}}>
        <div>
          <h3 style={{fontFamily:'var(--font-d)',fontSize:'clamp(20px,2.5vw,32px)',color:'var(--bg)',marginBottom:8}}>
            {en?'Need a Tour Guide?':'Perlu Pemandu Wisata?'}
          </h3>
          <p style={{fontFamily:'var(--font-b)',fontSize:14,color:'var(--bg)',opacity:.8}}>
            {en?'Our team can arrange guided tours to Ijen, Baluran, and beyond.'
              :'Tim kami dapat mengatur tur berpemandu ke Ijen, Baluran, dan sekitarnya.'}
          </p>
        </div>
        <RosaliBtn
          text={en?'Ask About Tours':'Tanya Tentang Tur'}
          href={`https://wa.me/6287851515500?text=${encodeURIComponent(en?'Hello, I would like to arrange a tour from Rosali Hotel.':'Halo, saya ingin mengatur tur dari Rosali Hotel.')}`}
          style={{background:'var(--bg)',color:'var(--accent)'}}
        />
      </section>

      <RosaliFooter lang={lang}/>
      <RosaliWaFab/>
    </div>
  );
}
ReactDOM.createRoot(document.getElementById('root')).render(<App/>);
</script>
</body></html>
