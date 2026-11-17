# Redirect to landing

Plugin WordPress (FireWeb) care redirecționează traficul public al site-ului către o pagină de landing, cu excepția utilizatorilor care au un rol configurat să ocolească redirecționarea.

Versiune: **1.1**
Autor: [FireWeb](https://fireweb.ro)  
Licență: [GPL-2.0-or-later](LICENSE)

## Ce face

După activare, pluginul se agață de acțiunea `template_redirect` și, dacă redirecționarea este pornită:

- citește URL-ul de destinație (doar `http`/`https`, validat) și tipul HTTP (302 temporar sau 301 permanent);
- nu redirecționează utilizatorii al căror rol este selectat la **Role Bypass** (implicit `administrator`);
- evită un loop comparând path-ul curent cu path-ul destinației (normalizat);
- sare peste admin, AJAX, cron și REST.

Utilizare tipică: site în mentenanță, campanie, sau orice situație în care vizitatorii trebuie trimiși pe o landing page, iar echipa internă rămâne pe site.

## Teste

Teste unitare PHPUnit (fără WordPress live), cu stub-uri WP:

```
composer install
composer test
```

Acoperă sanitizarea URL/roluri/tip, normalizarea path-ului anti-loop, bypass pe rol, skip admin/AJAX/cron/REST, fluxul `fwb_redirect_to_landing()` (fără `exit` real: `wp_safe_redirect` aruncă o excepție de test) și salvarea din admin (nonce, `update_option`, sanitizare).

## Cerințe

- WordPress (plugin clasic, fără dependențe extra)
- Permisiune `manage_options` pentru ecranul de setări

## Instalare

1. Copiază folderul `fwb-redirect-to-landing` în `wp-content/plugins/`.
2. În **Plugins**, activează **Redirect to landing**.
3. Deschide **Settings → Redirect to landing**.

Structura din repo:

```
redirect-to-landing/
├── LICENSE
├── README.md
└── fwb-redirect-to-landing/
    ├── fwb-redirect-to-landing.php   # fișier principal
    └── index.php                     # protecție la listarea directorului
```

## Configurare

În pagina de setări:

| Câmp | Descriere |
|------|-----------|
| **Enable Redirect** | Activează / dezactivează redirecționarea. |
| **Redirection Type** | `302` (temporar, implicit) sau `301` (permanent). |
| **Role Bypass** | Roluri WordPress care **nu** sunt redirecționate (selecție multiplă). |
| **Redirect Url** | Destinația, ex. `https://example.com/landing`. Trebuie să fie un URL `http`/`https` valid. |

Salvarea folosește nonce CSRF. Setările se salvează în opțiunile WordPress:

- `fwb_redirect_to_landing_enable`
- `fwb_redirect_to_landing_url`
- `fwb_redirect_type`
- `fwb_redirect_to_landing_rolebypass`

## Comportament

- Redirecționarea rulează pe frontend, la hook-ul `template_redirect`, prin `wp_safe_redirect()`.
- Utilizatorii neautentificați nu au roluri, deci sunt redirecționați (dacă pluginul e activat).
- Dacă path-ul paginii curente coincide cu path-ul destinației, redirecționarea este omisă (protecție anti-loop).
- Funcția `fwb_redirect_to_landing()` este pluggable: poate fi redefinită dintr-un alt plugin/theme dacă este declarată înainte de încărcarea acestui plugin.

## Note

- Folosește **302** cât timp testezi; **301** poate fi cache-uit de browsere și motoare de căutare.
- Destinațiile interne pe același path ca request-ul curent nu declanșează redirect.
- Doar utilizatorii cu `manage_options` au acces la setări.
