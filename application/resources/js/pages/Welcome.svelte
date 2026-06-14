<script lang="ts">
  import { Link, page } from '@inertiajs/svelte';
  import AppHead from '@/components/AppHead.svelte';
  import { toUrl } from '@/lib/utils';
  import { dashboard, login, register } from '@/routes';
  import { onMount } from 'svelte';
  import { initializeApp, getApps } from 'firebase/app';
  import { getAuth, createUserWithEmailAndPassword, signInWithEmailAndPassword, onAuthStateChanged, signOut, updateProfile } from 'firebase/auth';
  import { getFirestore, doc, setDoc, getDoc, updateDoc, serverTimestamp } from 'firebase/firestore';

  // ── Constants ──────────────────────────────────────────
  import { LOGO_URL, APP_NAME, WHATSAPP_URL, CONTACT_EMAIL, FIREBASE_CONFIG } from '@/constants/assets';

  const NAV_LINKS = [
    { id: 'nosotros',   label: 'Nosotros'    },
    { id: 'problema',   label: 'El Problema' },
    { id: 'beneficios', label: 'Beneficios'  },
    { id: 'contacto',   label: 'Contacto'    },
  ] as const;

  const HERO_STATS = [
    { val: '100%', lbl: 'Trazabilidad'       },
    { val: '0',    lbl: 'Descuadres'         },
    { val: '1',    lbl: 'Plataforma unificada'},
  ] as const;

  const FEATURES = [
    { icon: 'ti-building-store', title: 'Punto de Venta (POS)',      desc: 'Facturación rápida, gestión de turnos de caja, descuentos y devoluciones con registro auditado.',      tag: 'Módulo POS',        d: 0.00 },
    { icon: 'ti-credit-card',    title: 'Integración de Pagos',      desc: 'Efectivo, tarjetas, transferencias y billeteras digitales con conciliación automática.',                tag: 'Módulo Tesorería',  d: 0.08 },
    { icon: 'ti-package',        title: 'Inventarios Automatizados', desc: 'Código de barras, alertas de stock mínimo y actualización dinámica en cada movimiento.',               tag: 'Módulo Inventario', d: 0.16 },
    { icon: 'ti-chart-bar',      title: 'Reportes en Tiempo Real',   desc: 'Dashboards personalizables con datos actualizados para decisiones estratégicas.',                       tag: 'Módulo BI',         d: 0.24 },
    { icon: 'ti-fingerprint',    title: 'Trazabilidad Total',        desc: 'Cada movimiento registrado con origen, destino y responsable. Auditorías al alcance de un clic.',      tag: 'Módulo Auditoría',  d: 0.32 },
    { icon: 'ti-users',          title: 'Clientes y Proveedores',    desc: 'Arquitectura relacional que centraliza la información y elimina duplicidades.',                         tag: 'Módulo CRM',        d: 0.40 },
  ] as const;

  const PROBLEMS = [
    { num: '01', icon: 'ti-cash-off',    accent: 'red',    title: 'Cierres de caja sin control', desc: 'Errores acumulados sin detección. Los descuadres diarios comprometen el capital sin que el dueño lo sepa.' },
    { num: '02', icon: 'ti-receipt-off', accent: 'orange', title: 'Pagos fragmentados',           desc: 'Efectivo, transferencias y billeteras sin conciliación generan información financiera incompleta.'         },
    { num: '03', icon: 'ti-package-off', accent: 'blue',   title: 'Inventario fantasma',          desc: 'Stock desactualizado, ruptura de inventario y capital inmovilizado en productos que no rotan.'            },
    { num: '04', icon: 'ti-eye-off',   accent: 'green',  title: 'Decisiones a ciegas',          desc: 'Sin datos consolidados el empresario improvisa. Las decisiones estratégicas se basan en intuición.'        },
  ] as const;

  const VALUES = [
    { icon: 'ti-eye',          title: 'Transparencia',        desc: 'Visibilidad total en todos los procesos. Trazabilidad que refleja honestidad y rendición de cuentas.' },
    { icon: 'ti-bulb',         title: 'Innovación',           desc: 'Evolución continua respondiendo al mercado y los avances tecnológicos para generar valor real.'        },
    { icon: 'ti-heart',        title: 'Compromiso',           desc: 'El éxito del empresario es el éxito de la plataforma. El usuario es el centro del desarrollo.'        },
    { icon: 'ti-shield-check', title: 'Integridad Operativa', desc: 'Exactitud e integridad de datos garantizadas en todo momento. Confianza construida con precisión.'    },
  ] as const;

  const SECTORS = [
    'Farmacia / Droguería',
    'Supermercado / Tienda',
    'Restaurante / Cafetería',
    'Ropa y Calzado',
    'Ferretería / Materiales',
    'Papelería / Librería',
    'Otro',
  ] as const;

  const AUTH_ERRORS: Record<string, string> = {
    'auth/email-already-in-use': 'Ya existe una cuenta con ese correo. Usa la pestaña "Ya me registré".',
    'auth/invalid-email':        'Correo no válido.',
    'auth/weak-password':        'Contraseña muy débil.',
    'auth/user-not-found':       'No existe cuenta con ese correo.',
    'auth/wrong-password':       'Contraseña incorrecta.',
    'auth/invalid-credential':   'Credenciales incorrectas.',
  };

  // ── Props ──────────────────────────────────────────────
  let { canRegister = true }: { canRegister: boolean } = $props();
  const auth_page = $derived($page.props.auth);

  // ── Firebase init ──────────────────────────────────────
  const fbApp = getApps().length ? getApps()[0] : initializeApp(FIREBASE_CONFIG);
  const auth  = getAuth(fbApp);
  const db    = getFirestore(fbApp);

  // ── UI state ───────────────────────────────────────────
  let navScrolled = $state(false);
  let mobileOpen  = $state(false);
  let modalOpen   = $state(false);
  let activeTab   = $state<'registro' | 'login'>('registro');
  let confirmOpen = $state(false);

  let rg        = $state({ nombre: '', empresa: '', sector: '', tel: '', email: '', pass: '' });
  let rgLoading = $state(false);
  let rgMsg     = $state<{ text: string; type: 'error' | 'success' } | null>(null);

  let li        = $state({ email: '', pass: '' });
  let liLoading = $state(false);
  let liMsg     = $state<{ text: string; type: 'error' | 'success' } | null>(null);

  let confirm = $state({
    initials: '?', titulo: '', sub: '',
    nombre: '–', empresa: '–', sector: '–', tel: '–', email: '–', fecha: '–',
  });

  // ── Helpers ────────────────────────────────────────────
  function openModal(tab: 'registro' | 'login' = 'registro') {
    activeTab = tab; modalOpen = true; rgMsg = null; liMsg = null;
  }
  function closeModal() { modalOpen = false; rgMsg = null; liMsg = null; }

  function smoothScroll(e: MouseEvent, id: string) {
    e.preventDefault(); mobileOpen = false;
    document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  // ── Firebase actions ───────────────────────────────────
  async function doRegistro() {
    const { nombre, empresa, sector, tel, email, pass } = rg;
    if (!nombre || !empresa || !tel || !email || !pass) {
      rgMsg = { text: 'Completa los campos obligatorios (*)', type: 'error' }; return;
    }
    if (pass.length < 6) {
      rgMsg = { text: 'La contraseña debe tener mínimo 6 caracteres.', type: 'error' }; return;
    }
    rgLoading = true; rgMsg = null;
    try {
      const cred = await createUserWithEmailAndPassword(auth, email, pass);
      await updateProfile(cred.user, { displayName: nombre });
      await setDoc(doc(db, 'leads', cred.user.uid), {
        uid: cred.user.uid, nombre, empresa,
        sector: sector || 'No especificado',
        telefono: tel, email,
        estado: 'pendiente', origen: 'landing_web',
        createdAt: serverTimestamp(), ultimoAcceso: serverTimestamp(),
      });
    } catch (err: any) {
      rgMsg = { text: AUTH_ERRORS[err.code] ?? `Error: ${err.message}`, type: 'error' };
    } finally { rgLoading = false; }
  }

  async function doLogin() {
    const { email, pass } = li;
    if (!email || !pass) { liMsg = { text: 'Completa todos los campos.', type: 'error' }; return; }
    liLoading = true; liMsg = null;
    try {
      await signInWithEmailAndPassword(auth, email, pass);
    } catch (err: any) {
      liMsg = { text: AUTH_ERRORS[err.code] ?? `Error: ${err.message}`, type: 'error' };
    } finally { liLoading = false; }
  }

  async function doLogout() { await signOut(auth); confirmOpen = false; }

  // ── Lifecycle ──────────────────────────────────────────
  onMount(() => {
    const onScroll = () => { navScrolled = window.scrollY > 40; };
    window.addEventListener('scroll', onScroll, { passive: true });

    const obs = new IntersectionObserver(
      (entries) => entries.forEach(x => {
        if (x.isIntersecting) { x.target.classList.add('reveal-in'); obs.unobserve(x.target); }
      }),
      { threshold: 0.08, rootMargin: '0px 0px -20px 0px' }
    );
    document.querySelectorAll('.reveal').forEach(el => obs.observe(el));

    const unsub = onAuthStateChanged(auth, async (user) => {
      if (!user) { confirmOpen = false; return; }
      closeModal();
      let lead = { nombre: user.displayName ?? 'Usuario', empresa: '–', sector: '–', telefono: '–' };
      try {
        const snap = await getDoc(doc(db, 'leads', user.uid));
        if (snap.exists()) lead = { ...lead, ...(snap.data() as typeof lead) };
        await updateDoc(doc(db, 'leads', user.uid), { ultimoAcceso: serverTimestamp() });
      } catch (_) {}
      const esNuevo  = Date.now() - new Date(user.metadata.creationTime!).getTime() < 10_000;
      const firstName = lead.nombre.split(' ')[0];
      confirm = {
        initials: lead.nombre.split(' ').map((w: string) => w[0]).slice(0, 2).join('').toUpperCase(),
        titulo:   esNuevo ? `¡Gracias, ${firstName}!` : `Bienvenido de nuevo, ${firstName}`,
        sub:      esNuevo
          ? 'Tu solicitud de asesoría fue registrada. Te contactaremos pronto.'
          : 'Tu información está registrada en nuestro sistema de seguimiento.',
        nombre:  lead.nombre,
        empresa: lead.empresa,
        sector:  lead.sector ?? 'No especificado',
        tel:     lead.telefono ?? '–',
        email:   user.email ?? '–',
        fecha:   new Date(user.metadata.creationTime ?? Date.now())
          .toLocaleDateString('es-CO', { day: '2-digit', month: 'long', year: 'numeric' }),
      };
      confirmOpen = true;
    });

    return () => { window.removeEventListener('scroll', onScroll); unsub(); obs.disconnect(); };
  });
</script>

<AppHead title="Welcome"></AppHead>

<!-- ════ NAVBAR ════ -->
<nav class="fixed inset-x-0 top-0 z-50 flex h-[60px] items-center justify-between
            px-6 lg:px-10 transition-all duration-300
            bg-background/90 backdrop-blur-md border-b
            {navScrolled ? 'border-border' : 'border-transparent'}">

  <a href="#inicio" onclick={(e) => smoothScroll(e, 'inicio')}
     class="flex items-center gap-2.5 no-underline">
    <img src={LOGO_URL} alt={APP_NAME} class="h-6 w-auto" />
    <span class="brand-name">{APP_NAME}</span>
  </a>

  <ul class="hidden md:flex items-center gap-10 list-none m-0 p-0">
    {#each NAV_LINKS as { id, label }}
      <li>
        <a href="#{id}" onclick={(e) => smoothScroll(e, id)}
           class="text-sm text-muted-foreground hover:text-foreground transition-colors no-underline">
          {label}
        </a>
      </li>
    {/each}
  </ul>

  <div class="flex items-center gap-2">
    {#if auth_page?.user}
      <Link href={toUrl(dashboard())} class="btn-outline text-sm">Dashboard</Link>
    {:else}
      <Link href={toUrl(login())}    class="btn-ghost text-sm">Iniciar sesión</Link>
      {#if canRegister}
        <Link href={toUrl(register())} class="btn-primary text-sm">Registrarse</Link>
      {/if}
    {/if}
    <button onclick={() => (mobileOpen = !mobileOpen)} aria-label="Menú"
            class="md:hidden flex flex-col gap-[5px] p-2 ml-1 cursor-pointer bg-transparent border-0">
      <span class="w-5 h-px bg-foreground rounded block"></span>
      <span class="w-5 h-px bg-foreground rounded block"></span>
      <span class="w-5 h-px bg-foreground rounded block"></span>
    </button>
  </div>
</nav>

<!-- Mobile drawer -->
{#if mobileOpen}
  <div class="fixed inset-x-0 top-[60px] z-40 flex flex-col gap-5 border-b border-border
              bg-background px-6 py-6 md:hidden shadow-md">
    {#each NAV_LINKS as { id, label }}
      <a href="#{id}" onclick={(e) => smoothScroll(e, id)}
         class="text-sm text-muted-foreground hover:text-foreground no-underline transition-colors">
        {label}
      </a>
    {/each}
  </div>
{/if}

<!-- ════ HERO ════ -->
<section id="inicio" class="relative flex min-h-screen items-center justify-center
                             overflow-hidden px-6 pb-20 pt-28 bg-background">
  <div class="hero-grid absolute inset-0 pointer-events-none"></div>
  <div class="hero-fade absolute inset-0 pointer-events-none"></div>

  <div class="relative z-10 max-w-[760px] text-center">

    <div class="reveal inline-flex items-center gap-2 mb-8 px-4 py-1.5 rounded-full
                border border-primary/20 bg-primary/5">
      <span class="w-[5px] h-[5px] rounded-full bg-primary animate-pulse"></span>
      <span class="eyebrow-tag">ERP / POS · Plataforma para MiPyMEs</span>
    </div>

    <img src={LOGO_URL} alt={APP_NAME}
         class="reveal mx-auto mb-8 h-14" style="transition-delay:.05s" />

    <h1 class="reveal display-title mb-6" style="transition-delay:.1s">
      Control total,<br />
      <em class="text-primary not-italic">gestión inteligente</em>
    </h1>

    <p class="reveal text-base text-muted-foreground max-w-[500px] mx-auto mb-10 leading-relaxed font-light"
       style="transition-delay:.15s">
      Ventas, inventario, compras y finanzas unificados en una sola plataforma. Diseñada para que
      el comerciante tome decisiones con datos reales, en tiempo real.
    </p>

    <div class="reveal flex flex-wrap justify-center gap-3 mb-14" style="transition-delay:.2s">
      <button onclick={() => openModal()} class="btn-primary btn-lg">
        Solicitar asesoría gratuita →
      </button>
      <a href="#beneficios" onclick={(e) => smoothScroll(e, 'beneficios')} class="btn-outline btn-lg">
        Ver beneficios ↓
      </a>
    </div>

    <div class="reveal w-px h-10 bg-border mx-auto mb-12" style="transition-delay:.25s"></div>

    <div class="reveal flex overflow-hidden rounded-xl border border-border
                bg-card/70 backdrop-blur-sm shadow-sm max-w-[440px] mx-auto"
         style="transition-delay:.3s">
      {#each HERO_STATS as { val, lbl }, i}
        <div class="flex-1 py-5 px-3 text-center {i > 0 ? 'border-l border-border' : ''}">
          <div class="display-num text-primary leading-none mb-1">{val}</div>
          <div class="stat-label text-muted-foreground">{lbl}</div>
        </div>
      {/each}
    </div>

  </div>

  <div class="absolute bottom-8 left-1/2 -translate-x-1/2 z-10
              flex flex-col items-center gap-2 animate-bounce">
    <span class="eyebrow-tag text-muted-foreground">scroll</span>
    <span class="w-px h-7 bg-gradient-to-b from-muted-foreground/60 to-transparent"></span>
  </div>
</section>

<div class="h-px bg-border"></div>

<!-- ════ NOSOTROS ════ -->
<section id="nosotros" class="bg-muted py-24">
  <div class="section-container grid grid-cols-1 md:grid-cols-2 gap-16 md:gap-24 items-center">

    <div class="reveal">
      <span class="eyebrow">Quiénes somos</span>
      <h2 class="section-title">Impulsados por un propósito claro</h2>
      <p class="text-sm text-muted-foreground leading-relaxed font-light mt-3">
        Construimos tecnología que le devuelve el tiempo y la certeza a los empresarios que mueven
        la economía real.
      </p>
    </div>

    <div class="flex flex-col gap-4">
      {#each [
        { icon: 'ti-target-arrow', tag: 'Misión', delay: '.1s',
          title: 'Tecnología al alcance de cada empresario',
          desc:  'Proveer a pequeños y medianos comerciantes herramientas tecnológicas de alto nivel que simplifiquen su administración diaria y garanticen control transparente, confiable y auditable.' },
        { icon: 'ti-rocket', tag: 'Visión', delay: '.2s',
          title: 'Líderes en gestión digital para 2030',
          desc:  'Ser la plataforma líder en gestión comercial para MiPyMEs en la región, transformando negocios tradicionales en empresas digitales eficientes y financieramente saludables.' },
      ] as c}
        <div class="reveal card hover:-translate-y-0.5 transition-all duration-200"
             style="transition-delay:{c.delay}">
          <i class="ti {c.icon} text-2xl text-primary block mb-3"></i>
          <span class="module-tag mb-2 block">{c.tag}</span>
          <h3 class="card-title mb-2">{c.title}</h3>
          <p class="card-body">{c.desc}</p>
        </div>
      {/each}
    </div>

  </div>
</section>

<div class="h-px bg-border"></div>

<!-- ════ EL PROBLEMA ════ -->
<section id="problema" class="bg-background py-24">
  <div class="section-container">

    <div class="reveal max-w-[560px] mb-14">
      <span class="eyebrow">El Problema del Mercado</span>
      <h2 class="section-title">¿Por qué fracasan los negocios sin control?</h2>
      <p class="text-sm text-muted-foreground leading-relaxed font-light mt-3">
        La mayoría de las MiPyMEs opera con herramientas fragmentadas que generan pérdidas
        evitables cada día.
      </p>
    </div>

    <div class="reveal grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 divide-y sm:divide-y-0
                sm:divide-x divide-border rounded-xl overflow-hidden border border-border"
         style="transition-delay:.1s">
      {#each PROBLEMS as p}
        <div class="bg-card hover:bg-muted/60 transition-colors p-7">
          <span class="font-mono text-[0.6rem] tracking-widest text-muted-foreground block mb-5">
            {p.num}
          </span>
          <div class="w-10 h-10 rounded-lg flex items-center justify-center mb-4
                      {p.accent === 'red'    ? 'bg-red-100'    : ''}
                      {p.accent === 'orange' ? 'bg-orange-100' : ''}
                      {p.accent === 'blue'   ? 'bg-blue-100'   : ''}
                      {p.accent === 'green'  ? 'bg-primary/10' : ''}">
            <i class="ti {p.icon} text-lg
                      {p.accent === 'red'    ? 'text-red-700'    : ''}
                      {p.accent === 'orange' ? 'text-orange-700' : ''}
                      {p.accent === 'blue'   ? 'text-blue-700'   : ''}
                      {p.accent === 'green'  ? 'text-primary'    : ''}"></i>
          </div>
          <h3 class="card-title mb-2">{p.title}</h3>
          <p class="card-body">{p.desc}</p>
        </div>
      {/each}
    </div>

  </div>
</section>

<div class="h-px bg-border"></div>

<!-- ════ BENEFICIOS ════ -->
<section id="beneficios" class="bg-muted py-24">
  <div class="section-container">

    <div class="reveal mb-12">
      <span class="eyebrow">Funcionalidades Clave</span>
      <h2 class="section-title">Todo lo que necesitas en un solo sistema</h2>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
      {#each FEATURES as f}
        <div class="reveal card hover:-translate-y-0.5 hover:border-primary/30 transition-all duration-200"
             style="transition-delay:{f.d}s">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-11 h-11 rounded-lg bg-primary/10 border border-primary/15
                        flex items-center justify-center shrink-0">
              <i class="ti {f.icon} text-xl text-primary"></i>
            </div>
            <h3 class="card-title leading-snug">{f.title}</h3>
          </div>
          <p class="card-body">{f.desc}</p>
          <span class="module-tag mt-4 inline-block">{f.tag}</span>
        </div>
      {/each}
    </div>

  </div>
</section>

<div class="h-px bg-border"></div>

<!-- ════ VALORES ════ -->
<section id="valores" class="bg-background py-24">
  <div class="section-container">

    <div class="reveal mb-12">
      <span class="eyebrow">Filosofía Empresarial</span>
      <h2 class="section-title">Los valores que nos definen</h2>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-8">
      {#each VALUES as v, i}
        <div class="reveal text-center p-4" style="transition-delay:{i * 0.1}s">
          <i class="ti {v.icon} text-3xl text-primary block mb-4"></i>
          <h3 class="card-title mb-2">{v.title}</h3>
          <p class="card-body">{v.desc}</p>
        </div>
      {/each}
    </div>

  </div>
</section>

<div class="h-px bg-border"></div>

<!-- ════ CONTACTO ════ -->
<section id="contacto" class="bg-muted py-24">
  <div class="section-container">

    <div class="reveal text-center max-w-[560px] mx-auto mb-14">
      <span class="eyebrow">¿Listo para transformar tu negocio?</span>
      <h2 class="section-title">Agenda una asesoría gratuita</h2>
      <p class="text-sm text-muted-foreground leading-relaxed font-light mt-3">
        Un asesor de {APP_NAME} te contactará para entender tu operación y mostrarte cómo el
        sistema se adapta a tu negocio — sin compromisos.
      </p>
    </div>

    <div class="reveal grid grid-cols-1 sm:grid-cols-3 gap-5 max-w-[820px] mx-auto"
         style="transition-delay:.1s">

      <div class="card text-center">
        <i class="ti ti-clipboard-list text-3xl text-primary block mb-4"></i>
        <h3 class="card-title mb-2">Formulario rápido</h3>
        <p class="card-body mb-5">Deja tus datos y te contactamos en menos de 24 horas hábiles.</p>
        <button onclick={() => openModal('registro')} class="btn-primary w-full">
          Registrarme →
        </button>
      </div>

      <div class="card text-center">
        <i class="ti ti-brand-whatsapp text-3xl text-primary block mb-4"></i>
        <h3 class="card-title mb-2">WhatsApp directo</h3>
        <p class="card-body mb-5">Chatea con un asesor ahora mismo. Respuesta inmediata en horario comercial.</p>
        <a href={WHATSAPP_URL} target="_blank" rel="noopener" class="btn-outline w-full flex justify-center">
          Abrir WhatsApp ↗
        </a>
      </div>

      <div class="card text-center">
        <i class="ti ti-mail text-3xl text-primary block mb-4"></i>
        <h3 class="card-title mb-2">Correo electrónico</h3>
        <p class="card-body mb-5">Escríbenos con tu consulta y te respondemos con una propuesta personalizada.</p>
        <a href="mailto:{CONTACT_EMAIL}" class="btn-outline w-full flex justify-center">
          Enviar correo ↗
        </a>
      </div>

    </div>
  </div>
</section>

<!-- ════ FOOTER ════ -->
<footer class="border-t border-border bg-background py-12 px-8 text-center">
  <img src={LOGO_URL} alt={APP_NAME} class="mx-auto mb-3 h-8" />
  <p class="brand-name mb-1">{APP_NAME}</p>
  <p class="text-sm text-muted-foreground">Plataforma integral ERP/POS para MiPyMEs</p>
  <p class="mt-2 text-xs text-muted-foreground/50">© 2026 {APP_NAME} · Control Total, Gestión Inteligente</p>
</footer>

<!-- ════ MODAL ════ -->
{#if modalOpen}
  <!-- svelte-ignore a11y_click_events_have_key_events a11y_no_static_element_interactions -->
  <div class="fixed inset-0 z-[2000] flex items-center justify-center p-4
              bg-foreground/40 backdrop-blur-sm"
       onclick={(e) => { if (e.target === e.currentTarget) closeModal(); }}>

    <div class="modal-box relative w-full max-w-[440px]">

      <button onclick={closeModal} aria-label="Cerrar"
              class="absolute right-4 top-4 flex h-8 w-8 cursor-pointer items-center justify-center
                     rounded-full border-0 bg-transparent text-muted-foreground text-base
                     hover:bg-muted hover:text-foreground transition-all">
        ✕
      </button>

      <div class="flex items-center gap-2.5 mb-7">
        <img src={LOGO_URL} alt="" class="h-6" />
        <span class="brand-name">{APP_NAME}</span>
      </div>

      <!-- tabs -->
      <div class="flex rounded-lg border border-border bg-muted p-1 mb-7">
        {#each [['registro','Quiero asesoría'],['login','Ya me registré']] as [tab, lbl]}
          <button onclick={() => { activeTab = tab as typeof activeTab; rgMsg = null; liMsg = null; }}
                  class="flex-1 rounded-md py-2 text-sm font-medium transition-all cursor-pointer border-0
                         {activeTab === tab
                            ? 'bg-background text-foreground shadow-sm'
                            : 'bg-transparent text-muted-foreground hover:text-foreground'}">
            {lbl}
          </button>
        {/each}
      </div>

      {#if activeTab === 'registro'}

        <div class="field"><label class="field-label">Nombre completo *</label>
          <input type="text" class="field-input" bind:value={rg.nombre}
                 placeholder="Tu nombre completo" autocomplete="name" /></div>

        <div class="field"><label class="field-label">Empresa / Negocio *</label>
          <input type="text" class="field-input" bind:value={rg.empresa}
                 placeholder="Nombre de tu negocio" /></div>

        <div class="field"><label class="field-label">Sector</label>
          <select class="field-input" bind:value={rg.sector}>
            <option value="">Selecciona tu sector</option>
            {#each SECTORS as s}<option>{s}</option>{/each}
          </select></div>

        <div class="field"><label class="field-label">Teléfono / WhatsApp *</label>
          <input type="tel" class="field-input" bind:value={rg.tel}
                 placeholder="+57 300 000 0000" /></div>

        <div class="field"><label class="field-label">Correo electrónico *</label>
          <input type="email" class="field-input" bind:value={rg.email}
                 placeholder="correo@empresa.co" autocomplete="email" /></div>

        <div class="field"><label class="field-label">Contraseña *</label>
          <input type="password" class="field-input" bind:value={rg.pass}
                 placeholder="Mínimo 6 caracteres" autocomplete="new-password" /></div>

        <button onclick={doRegistro} disabled={rgLoading} class="btn-primary w-full mt-2 py-3">
          {rgLoading ? 'Por favor espera…' : 'Solicitar asesoría gratuita'}
        </button>
        {#if rgMsg}<p class="msg {rgMsg.type}">{rgMsg.text}</p>{/if}

      {:else}

        <div class="field"><label class="field-label">Correo electrónico</label>
          <input type="email" class="field-input" bind:value={li.email}
                 placeholder="correo@empresa.co" autocomplete="email" /></div>

        <div class="field"><label class="field-label">Contraseña</label>
          <input type="password" class="field-input" bind:value={li.pass}
                 placeholder="••••••••" autocomplete="current-password"
                 onkeydown={(e) => e.key === 'Enter' && doLogin()} /></div>

        <button onclick={doLogin} disabled={liLoading} class="btn-primary w-full mt-2 py-3">
          {liLoading ? 'Por favor espera…' : 'Ingresar'}
        </button>
        {#if liMsg}<p class="msg {liMsg.type}">{liMsg.text}</p>{/if}

      {/if}
    </div>
  </div>
{/if}

<!-- ════ CONFIRM SCREEN ════ -->
{#if confirmOpen}
  <div class="fixed inset-0 z-[1500] flex flex-col items-center justify-center
              overflow-y-auto bg-background p-8">
    <div class="confirm-card relative w-full max-w-[540px] overflow-hidden text-center">

      <div class="absolute inset-x-0 top-0 h-[3px] bg-primary"></div>

      <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center
                  rounded-full bg-primary font-mono text-2xl font-semibold text-primary-foreground">
        {confirm.initials}
      </div>

      <div class="inline-flex items-center gap-1.5 rounded-full border border-primary/20
                  bg-primary/8 px-3 py-1.5 mb-5">
        <span class="h-[5px] w-[5px] animate-pulse rounded-full bg-primary"></span>
        <span class="eyebrow-tag text-primary">Solicitud registrada</span>
      </div>

      <h2 class="display-sm text-foreground mb-2">{confirm.titulo}</h2>
      <p class="text-sm text-muted-foreground leading-relaxed font-light mb-7">{confirm.sub}</p>

      <div class="mb-6 rounded-xl border border-border bg-muted p-5 text-left">
        {#each [
          ['Nombre',     confirm.nombre],
          ['Empresa',    confirm.empresa],
          ['Sector',     confirm.sector],
          ['Teléfono',   confirm.tel],
          ['Correo',     confirm.email],
          ['Registrado', confirm.fecha],
        ] as [lbl, val], i}
          <div class="flex items-start justify-between gap-4 py-2.5
                      {i < 5 ? 'border-b border-border' : ''}">
            <span class="font-mono text-[0.6rem] tracking-wider uppercase text-muted-foreground shrink-0">
              {lbl}
            </span>
            <span class="text-sm text-foreground text-right">{val}</span>
          </div>
        {/each}
      </div>

      <div class="mb-7 rounded-xl border border-primary/15 bg-primary/8 p-5 text-left">
        <p class="text-sm text-foreground leading-relaxed">
          <i class="ti ti-phone-call text-primary align-middle mr-1.5"></i>
          Un asesor de <strong class="text-primary font-medium">{APP_NAME}</strong> se pondrá en
          contacto en las próximas <strong class="text-primary font-medium">24 horas hábiles</strong>
          al número y correo registrado para coordinar una demostración personalizada.
        </p>
      </div>

      <button onclick={doLogout} class="btn-outline text-muted-foreground hover:text-destructive
                                        hover:border-destructive transition-colors">
        Cerrar sesión
      </button>
    </div>
  </div>
{/if}

<style>
  /* ── Design-system tokens from app.css ── */

  /* Fonts loaded via AppHead */
  .brand-name {
    font-family: 'DM Mono', monospace;
    font-size: 0.72rem;
    font-weight: 500;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--primary);
  }

  /* Section wrapper */
  .section-container {
    max-width: 1080px;
    margin-inline: auto;
    padding-inline: 2rem;
  }

  /* Typography */
  .display-title {
    font-family: 'DM Serif Display', Georgia, serif;
    font-size: clamp(2.8rem, 6vw, 5rem);
    line-height: 1.08;
    font-weight: 400;
    letter-spacing: -0.01em;
    color: var(--foreground);
  }
  .display-num {
    font-family: 'DM Serif Display', Georgia, serif;
    font-size: 2rem;
  }
  .display-sm {
    font-family: 'DM Serif Display', Georgia, serif;
    font-size: 1.6rem;
    font-weight: 400;
  }
  .section-title {
    font-family: 'DM Serif Display', Georgia, serif;
    font-size: clamp(1.8rem, 3.5vw, 2.8rem);
    font-weight: 400;
    letter-spacing: -0.01em;
    color: var(--foreground);
    margin-bottom: 0.75rem;
  }
  .eyebrow {
    display: block;
    font-family: 'DM Mono', monospace;
    font-size: 0.65rem;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--primary);
    margin-bottom: 0.6rem;
  }
  .eyebrow-tag {
    font-family: 'DM Mono', monospace;
    font-size: 0.68rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--primary);
  }
  .stat-label {
    font-family: 'DM Mono', monospace;
    font-size: 0.68rem;
    letter-spacing: 0.03em;
  }
  .module-tag {
    font-family: 'DM Mono', monospace;
    font-size: 0.6rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--primary);
    background-color: color-mix(in srgb, var(--primary) 10%, transparent);
    border: 1px solid color-mix(in srgb, var(--primary) 20%, transparent);
    padding: 0.2rem 0.65rem;
    border-radius: 100px;
  }
  .card-title {
    font-family: 'DM Serif Display', Georgia, serif;
    font-size: 1.05rem;
    font-weight: 400;
    color: var(--foreground);
  }
  .card-body {
    font-size: 0.875rem;
    color: var(--muted-foreground);
    line-height: 1.75;
    font-weight: 300;
  }

  /* Cards */
  .card {
    background-color: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 1.75rem;
    box-shadow: 0 1px 3px rgba(0,0,0,.05);
  }

  /* Buttons */
  .btn-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    padding: 0.5rem 1.25rem;
    border-radius: var(--radius-md);
    background-color: var(--primary);
    color: var(--primary-foreground);
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: opacity 0.15s;
  }
  .btn-primary:hover:not(:disabled) { opacity: 0.88; }
  .btn-primary:disabled { opacity: 0.4; cursor: not-allowed; }

  .btn-outline {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    padding: 0.5rem 1.25rem;
    border-radius: var(--radius-md);
    background-color: transparent;
    color: var(--foreground);
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    border: 1px solid var(--border);
    cursor: pointer;
    transition: background-color 0.15s, border-color 0.15s;
  }
  .btn-outline:hover { background-color: var(--muted); border-color: var(--input); }

  .btn-ghost {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.5rem 1rem;
    border-radius: var(--radius-md);
    background-color: transparent;
    color: var(--muted-foreground);
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: color 0.15s, background-color 0.15s;
  }
  .btn-ghost:hover { color: var(--foreground); background-color: var(--muted); }

  .btn-lg { padding: 0.75rem 1.75rem; font-size: 0.95rem; }

  /* Modal */
  .modal-box {
    background-color: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 2.5rem;
    box-shadow: 0 20px 60px rgba(0,0,0,.12);
    animation: modalIn 0.2s ease;
  }
  @keyframes modalIn {
    from { opacity: 0; transform: scale(0.97) translateY(6px); }
    to   { opacity: 1; transform: scale(1)   translateY(0);    }
  }

  /* Confirm card */
  .confirm-card {
    background-color: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 3rem;
    box-shadow: 0 20px 60px rgba(0,0,0,.10);
  }

  /* Form */
  .field { display: flex; flex-direction: column; gap: 0.35rem; margin-bottom: 0.9rem; }
  .field-label {
    font-family: 'DM Mono', monospace;
    font-size: 0.65rem;
    font-weight: 500;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    color: var(--muted-foreground);
  }
  .field-input {
    width: 100%;
    padding: 0.6rem 1rem;
    border-radius: var(--radius-md);
    border: 1px solid var(--input);
    background-color: var(--background);
    color: var(--foreground);
    font-size: 0.9rem;
    outline: none;
    transition: border-color 0.15s;
  }
  .field-input::placeholder { color: var(--muted-foreground); opacity: 0.5; }
  .field-input:focus { border-color: var(--ring); }

  /* Messages */
  .msg {
    margin-top: 0.75rem;
    padding: 0.55rem 1rem;
    border-radius: var(--radius-md);
    font-size: 0.8rem;
    text-align: center;
  }
  .msg.error   {
    color: var(--destructive);
    background-color: color-mix(in srgb, var(--destructive) 10%, transparent);
    border: 1px solid color-mix(in srgb, var(--destructive) 20%, transparent);
  }
  .msg.success {
    color: var(--primary);
    background-color: color-mix(in srgb, var(--primary) 10%, transparent);
    border: 1px solid color-mix(in srgb, var(--primary) 20%, transparent);
  }

  /* Hero decorations */
  .hero-grid {
    background-image: radial-gradient(var(--border) 1px, transparent 1px);
    background-size: 28px 28px;
    opacity: 0.7;
  }
  .hero-fade {
    background: radial-gradient(ellipse 70% 70% at 50% 40%,
      transparent 35%, var(--background) 100%);
  }

  /* Scroll reveal */
  :global(.reveal) {
    opacity: 0;
    transform: translateY(16px);
    transition: opacity 0.5s ease, transform 0.5s ease;
  }
  :global(.reveal-in) {
    opacity: 1 !important;
    transform: none !important;
  }

  /* Mobile */
  @media (max-width: 768px) {
    .modal-box    { padding: 1.75rem; }
    .confirm-card { padding: 2rem 1.5rem; }
    .section-container { padding-inline: 1.25rem; }
  }
</style>