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
<title>Kontak & Promo — Rosali Hotel Situbondo</title>
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
const { RosaliImg, RosaliNav, RosaliFooter, RosaliBtn, RosaliLabel, RosaliWaFab, initRosali, useResponsive, getUrlTheme, setUrlTheme } = window;

const PROMOS = {
  en:[
    {title:'Weekend Getaway Package',badge:'Special',
      desc:'2 nights in Superior Room + breakfast for 2 + late checkout. Perfect for couples.',
      cta:'Ask About This Promo'},
    {title:'Meeting Package (Full Day)',badge:'Corporate',
      desc:'Meeting room + coffee breaks + lunch + parking. Starting from 20 pax.',
      cta:'Get Package Details'},
    {title:'Wedding Package',badge:'New',
      desc:'Dream Garden or indoor hall + catering + decoration + accommodation for bride & groom.',
      cta:'Plan Your Wedding'},
  ],
  id:[
    {title:'Paket Weekend Getaway',badge:'Spesial',
      desc:'2 malam di Kamar Superior + sarapan untuk 2 + late checkout. Sempurna untuk pasangan.',
      cta:'Tanya Promo Ini'},
    {title:'Paket Meeting (Full Day)',badge:'Korporat',
      desc:'Ruang rapat + coffee break + makan siang + parkir. Mulai dari 20 orang.',
      cta:'Dapatkan Detail Paket'},
    {title:'Paket Pernikahan',badge:'Baru',
      desc:'Dream Garden atau aula indoor + katering + dekorasi + akomodasi untuk pengantin.',
      cta:'Rencanakan Pernikahan'},
  ]
};

function App(){
  const [theme]=useState('<?= $theme ?>');
  const [lang,setLang]=useState(()=>localStorage.getItem('rosali_lang')||'id');
  const [form,setForm]=useState({name:'',phone:'',type:'stay',msg:''});
  const { isMobile } = useResponsive();
  initRosali(theme,lang);
  useEffect(()=>{ localStorage.setItem('rosali_lang',lang); },[lang]);
  const en=lang==='en';
  const promos=PROMOS[lang];

  const sendWa=()=>{
    const types={stay:en?'Room Booking':'Pemesanan Kamar',
      event:en?'Event Inquiry':'Pertanyaan Acara',
      cafe:en?'Café Reservation':'Reservasi Café'};
    const txt = en
      ?`Hello Rosali Hotel,\n\nName: ${form.name}\nPhone: ${form.phone}\nInquiry type: ${types[form.type]}\n\nMessage: ${form.msg}\n\nThank you.`
      :`Halo Rosali Hotel,\n\nNama: ${form.name}\nTelepon: ${form.phone}\nJenis pertanyaan: ${types[form.type]}\n\nPesan: ${form.msg}\n\nTerima kasih.`;
    window.open(`https://wa.me/6287851515500?text=${encodeURIComponent(txt)}`,'_blank');
  };

  const input={
    width:'100%',background:'var(--bg)',border:'1px solid var(--border)',
    color:'var(--fg)',borderRadius:2,padding:'11px 14px',
    fontFamily:'var(--font-b)',fontSize:14,outline:'none',
    transition:'border-color .2s'
  };

  return(
    <div className={`theme-${theme}`} style={{minHeight:'100vh'}}>
      <RosaliNav lang={lang} setLang={l=>{setLang(l);localStorage.setItem('rosali_lang',l)}} current="contact" theme={theme}/>

      {/* Header */}
      <div style={{paddingTop:120,paddingBottom:60,textAlign:'center',
        padding:'120px clamp(20px,6vw,96px) 60px',background:'var(--bg)'}}>
        <RosaliLabel>{en?'Contact & Promo':'Kontak & Promo'}</RosaliLabel>
        <h1 style={{fontFamily:'var(--font-d)',fontSize:'clamp(32px,5vw,64px)',
          lineHeight:1.05,color:'var(--fg)',whiteSpace:'pre-line'}}>
          {en?'Find Us.\nBook Us.\nStay With Us.':'Temukan Kami.\nPesan.\nMenginaplah.'}
        </h1>
      </div>

      {/* CONTACT + MAP */}
      <section style={{background:'var(--bg)',padding:'0 clamp(20px,6vw,96px) clamp(48px,6vw,88px)',
        display:'grid',gridTemplateColumns:isMobile?'1fr':'1fr 1fr',gap:'clamp(32px,5vw,72px)'}}>

        {/* Info */}
        <div>
          <div style={{display:'grid',gridTemplateColumns:isMobile?'1fr':'1fr 1fr',gap:24,marginBottom:36}}>
            {[
              {lbl:en?'Address':'Alamat', val:'Jl. PB Sudirman 52\n68312 Situbondo, Jawa Timur'},
              {lbl:en?'Phone':'Telepon', val:'+62 (0)338 676 323\n+62 338 678 672–674'},
              {lbl:'WhatsApp', val:'+62 878 5151 5500'},
              {lbl:'Email', val:'rosalihotel@gmail.com'},
              {lbl:en?'Reception':'Resepsi', val:en?'Open 24 hours':'Buka 24 jam'},
              {lbl:en?'Check-in / out':'Check-in / out', val:'14:00 / 12:00'},
            ].map(r=>(
              <div key={r.lbl}>
                <div style={{fontFamily:'var(--font-b)',fontSize:10,letterSpacing:'0.15em',
                  textTransform:'uppercase',color:'var(--accent)',marginBottom:4}}>{r.lbl}</div>
                <div style={{fontFamily:'var(--font-b)',fontSize:13,color:'var(--fg)',lineHeight:1.6,whiteSpace:'pre-line'}}>{r.val}</div>
              </div>
            ))}
          </div>

          {/* Quick contact buttons */}
          <div style={{display:'flex',gap:10,flexWrap:'wrap',marginBottom:36}}>
            <RosaliBtn text={en?'Chat on WhatsApp':'Chat WhatsApp'} style={{fontSize:13}}/>
            <a href="mailto:rosalihotel@gmail.com" style={{
              display:'inline-flex',alignItems:'center',gap:8,
              border:'1px solid var(--accent)',color:'var(--accent)',
              padding:'11px 20px',borderRadius:2,fontFamily:'var(--font-b)',
              fontSize:13,letterSpacing:'0.04em',transition:'background .2s'}}
              onMouseEnter={e=>e.currentTarget.style.background='var(--bg2)'}
              onMouseLeave={e=>e.currentTarget.style.background='transparent'}
            >✉ Email Us</a>
          </div>

          {/* Inquiry Form → WhatsApp */}
          <div style={{background:'var(--bg2)',borderRadius:4,padding:'24px',border:'1px solid var(--border)'}}>
            <div style={{fontFamily:'var(--font-d)',fontSize:18,color:'var(--fg)',marginBottom:18}}>
              {en?'Send Us a Message':'Kirim Pesan'}
            </div>
            <div style={{display:'flex',flexDirection:'column',gap:10}}>
              <input placeholder={en?'Your Name':'Nama Anda'} value={form.name}
                onChange={e=>setForm({...form,name:e.target.value})}
                style={input}
                onFocus={e=>e.target.style.borderColor='var(--accent)'}
                onBlur={e=>e.target.style.borderColor='var(--border)'}/>
              <input placeholder={en?'Phone / WhatsApp':'Telepon / WhatsApp'} value={form.phone}
                onChange={e=>setForm({...form,phone:e.target.value})}
                style={input}
                onFocus={e=>e.target.style.borderColor='var(--accent)'}
                onBlur={e=>e.target.style.borderColor='var(--border)'}/>
              <select value={form.type} onChange={e=>setForm({...form,type:e.target.value})}
                style={{...input,appearance:'none'}}>
                <option value="stay">{en?'Room Booking':'Pemesanan Kamar'}</option>
                <option value="event">{en?'Event / Meeting':'Acara / Rapat'}</option>
                <option value="cafe">{en?'Café Reservation':'Reservasi Café'}</option>
              </select>
              <textarea placeholder={en?'Your message...':'Pesan Anda...'} value={form.msg}
                onChange={e=>setForm({...form,msg:e.target.value})}
                rows={4} style={{...input,resize:'vertical'}}
                onFocus={e=>e.target.style.borderColor='var(--accent)'}
                onBlur={e=>e.target.style.borderColor='var(--border)'}/>
              <button onClick={sendWa} style={{
                background:'var(--accent)',color:'var(--bg)',border:'none',
                padding:'13px',borderRadius:2,fontFamily:'var(--font-b)',
                fontSize:14,fontWeight:600,transition:'opacity .2s',
                display:'flex',alignItems:'center',justifyContent:'center',gap:8}}
                onMouseEnter={e=>e.currentTarget.style.opacity='.82'}
                onMouseLeave={e=>e.currentTarget.style.opacity='1'}
              >
                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                {en?'Send via WhatsApp':'Kirim via WhatsApp'}
              </button>
            </div>
          </div>
        </div>

        {/* Map + photo */}
        <div style={{display:'flex',flexDirection:'column',gap:3}}>
          <RosaliImg label="map — Google Maps embed Jl. PB Sudirman 52 Situbondo" h={340} style={{borderRadius:2}}/>
          <RosaliImg label="hotel — front exterior / signage at road" h={200} style={{borderRadius:2}}/>
        </div>
      </section>

      {/* PROMOS */}
      <section style={{background:'var(--bg2)',padding:'clamp(48px,6vw,88px) clamp(20px,6vw,96px)'}}>
        <RosaliLabel>{en?'Current Promotions':'Promo Saat Ini'}</RosaliLabel>
        <h2 style={{fontFamily:'var(--font-d)',fontSize:'clamp(24px,3vw,42px)',color:'var(--fg)',marginBottom:36}}>
          {en?'Special Offers':'Penawaran Spesial'}
        </h2>
        <div style={{display:'grid',gridTemplateColumns:isMobile?'1fr':'repeat(3,1fr)',gap:3}}>
          {promos.map((p,i)=>(
            <div key={i} style={{background:'var(--card)',borderRadius:2,padding:'24px',
              border:'1px solid var(--border)',display:'flex',flexDirection:'column',gap:12}}>
              <span style={{display:'inline-block',background:'var(--accent)',color:'var(--bg)',
                padding:'3px 10px',borderRadius:2,fontSize:10,fontWeight:600,
                letterSpacing:'0.1em',textTransform:'uppercase',fontFamily:'var(--font-b)',
                alignSelf:'flex-start'}}>{p.badge}</span>
              <h4 style={{fontFamily:'var(--font-d)',fontSize:20,color:'var(--fg)'}}>{p.title}</h4>
              <p style={{fontFamily:'var(--font-b)',fontSize:13,color:'var(--fg-muted)',lineHeight:1.7,flex:1}}>{p.desc}</p>
              <RosaliBtn text={p.cta}
                href={`https://wa.me/6287851515500?text=${encodeURIComponent(en?`Hello, I'm interested in the ${p.title}.`:`Halo, saya tertarik dengan ${p.title}.`)}`}
                style={{justifyContent:'center',fontSize:12,padding:'10px'}}/>
            </div>
          ))}
        </div>
      </section>

      <RosaliFooter lang={lang}/>
      <RosaliWaFab/>
    </div>
  );
}
ReactDOM.createRoot(document.getElementById('root')).render(<App/>);
</script>
</body></html>
